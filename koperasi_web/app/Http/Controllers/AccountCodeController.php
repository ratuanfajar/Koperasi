<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Client\ConnectionException;
use App\Models\LedgerEntry;     
use App\Models\ChartOfAccount;  

class AccountCodeController extends Controller
{
    public function showDocument($filename)
    {
        if (!Storage::disk('private')->exists('uploads/' . $filename)) {
            abort(404);
        }
        return response()->file(storage_path('app/private/uploads/' . $filename));
    }

    public function index($step = 1)
    {
        $step = in_array($step, [1, 2, 3]) ? $step : 1;

        if ($step == 2 && !session()->has('file_to_process')) {
            return redirect()->route('account-code-recommender.show', ['step' => 1]);
        }
        if ($step == 3 && !session()->has('prediction_result')) {
            return redirect()->route('account-code-recommender.show', ['step' => 1]);
        }

        $result = session('prediction_result', null);
        $filePath = session('file_to_process', null);
        $imageUrl = null;

        if ($filePath) {
            $filename = basename($filePath);
            $imageUrl = route('document.show', ['filename' => $filename]);
        }

        // Ambil Data Master Akun
        $accounts = ChartOfAccount::orderBy('code')
                    ->get()
                    ->groupBy('category'); 
        
        $orderedCategories = ['Aset', 'Kewajiban', 'Modal', 'Pendapatan', 'Biaya'];
        $sortedAccounts = $accounts->sortBy(function ($item, $key) use ($orderedCategories) {
            return array_search($key, $orderedCategories);
        });

        return view('dashboard.account-code-recommender', [
            'step' => (int) $step,
            'result' => $result, 
            'image_url' => $imageUrl,
            'groupedAccounts' => $sortedAccounts 
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ], [
            'document.required' => 'Pilih file terlebih dahulu.',
            'document.image' => 'File harus berupa gambar.',
            'document.mimes' => 'Format salah. Gunakan JPG atau PNG.',
            'document.max' => 'Ukuran terlalu besar (Maks 10MB).',
        ]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('uploads', 'private');
            session(['file_to_process' => $path]);
            return redirect()->route('account-code-recommender.show', ['step' => 2])
                ->with('success', 'File berhasil di-upload.'); 
        }

        return redirect()->route('account-code-recommender.show', ['step' => 1])
            ->with('error', 'Gagal, tidak ada file yang dipilih.');
    }

    public function processImage(Request $request)
    {
        set_time_limit(0); 
        $path = session('file_to_process');
        if (!$path) return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan.'], 400);

        $fullPath = Storage::disk('private')->path($path);
        
        try {
            $response = Http::asMultipart()
                ->timeout(600)       
                ->connectTimeout(600) 
                ->attach('file', file_get_contents($fullPath), basename($path))
                ->post('http://127.0.0.1:5000/analyze');

            if (!$response->successful()) {
                return response()->json(['status' => 'error', 'message' => 'Server AI Error.'], 502);
            }

            $data = $response->json();

            $aiData = $data['llm'] ?? $data;

            if ($aiData) {
                session(['prediction_result' => $aiData]);
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Format respon AI tidak sesuai.'], 500);
            }

        } catch (ConnectionException $e) {
            return response()->json(['status' => 'error', 'message' => 'Koneksi AI Timeout.'], 504);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request)
    {
        DB::beginTransaction(); 
        try {
            // 1. Validasi
            $request->validate([
                'accounts'      => 'required|array',     
                'accounts.*'    => 'required',
                'debit'         => 'required|array',         
                'credit'        => 'required|array',     
                'jenis_bukti'   => 'required',
                'tanggal'       => 'required|date',
                'nomor_bukti'   => 'nullable|numeric|min:1',
                'description'   => 'nullable|string', 
            ]);

            // 2. Cek Balance
            $totalDebit = array_sum(array_map('floatval', $request->debit));
            $totalCredit = array_sum(array_map('floatval', $request->credit));

            if (abs($totalDebit - $totalCredit) > 1) {
                return response()->json(['status' => 'error', 'message' => 'Jurnal tidak seimbang (Unbalanced).'], 422);
            }

            if ($totalDebit == 0) {
                return response()->json(['status' => 'error', 'message' => 'Nominal transaksi tidak boleh 0.'], 422);
            }

            // 3. Generate Nomor Transaksi
            $prefix = $request->input('jenis_bukti'); 
            $tanggal = $request->input('tanggal');
            $periode = \Carbon\Carbon::parse($tanggal)->format('ym'); 
            
            $trxCode = "";

            if ($request->filled('nomor_bukti')) {
                $inputNumber = $request->input('nomor_bukti');
                
                // Format: BKK-2512-5 (Tanpa padding 005)
                $trxCode = "$prefix-$periode-$inputNumber";

                if (LedgerEntry::where('transaction_code', $trxCode)->exists()) {
                    return response()->json(['status' => 'error', 'message' => "Nomor Bukti $trxCode sudah digunakan."], 422);
                }

            } else {
                $searchPrefix = "$prefix-$periode-"; 
                $lengthPrefix = strlen($searchPrefix);

                $lastTrx = LedgerEntry::where('transaction_code', 'like', "$searchPrefix%")
                            ->whereRaw("transaction_code REGEXP '^{$searchPrefix}[0-9]+$'") 
                            ->orderByRaw('LENGTH(transaction_code) DESC')
                            ->orderBy('transaction_code', 'desc')
                            ->lockForUpdate()
                            ->first();
                
                $lastNo = 0;
                if ($lastTrx) {
                    $numberPart = substr($lastTrx->transaction_code, $lengthPrefix);
                    $lastNo = (int) $numberPart;
                }
                
                $newNo = $lastNo + 1;
            
                // Format menjadi: BKK-2512-1, BKK-2512-10, dst.
                $trxCode = "$searchPrefix$newNo";
            }

            // 4. Simpan ke Database
            $trxGroupId = (string) Str::uuid();
            $filePath = session('file_to_process');

            $accounts = $request->input('accounts');
            $debits = $request->input('debit');
            $credits = $request->input('credit');
            // $description = $request->input('description');

            foreach ($accounts as $index => $accountCode) {
                $debVal = (float) $debits[$index];
                $credVal = (float) $credits[$index];

                if ($debVal == 0 && $credVal == 0) continue;

                $accountName = ChartOfAccount::where('code', $accountCode)->value('name') ?? 'Unknown Account';

                LedgerEntry::create([
                    'transaction_group_id' => $trxGroupId,
                    'transaction_code'     => $trxCode,
                    'date'                 => $tanggal,
                    // 'description'          => $description,
                    'account_code'         => $accountCode,
                    'account_name'         => $accountName,
                    'debit'                => $debVal,
                    'credit'               => $credVal,
                    'receipt_image_path'   => $filePath,
                ]);
            }

            DB::commit(); 
            session()->forget(['prediction_result', 'file_to_process']);
            
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}