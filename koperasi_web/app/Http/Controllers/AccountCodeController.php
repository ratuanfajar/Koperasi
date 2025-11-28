<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\LedgerEntry;
use App\Models\TransactionItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AccountCodeController extends Controller
{
    public function index($step = 1)
    {
        $step = in_array($step, [1, 2, 3]) ? $step : 1;

        $result = session('prediction_result', null);
        $imageUrl = session('file_public_url', null);

        return view('dashboard.account-code-recommender', [
            'step' => (int) $step,
            'result' => $result, 
            'image_url' => $imageUrl
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB
        ]);

        if ($request->hasFile('document')) {
            
            $path = $request->file('document')->store('uploads', 'public');

            session(['file_to_process' => $path]);
            session(['file_public_url' => Storage::url($path)]);

            // edirect ke Step 2
            return redirect()->route('account-code-recommender.show', ['step' => 2])
                ->with('success', 'File berhasil di-upload.')
                ->with('file_path', $path); // Menyimpan path file di session
        }

        return redirect()->route('account-code-recommender.show', ['step' => 1])
            ->with('error', 'Gagal, tidak ada file yang dipilih.');
    }

    public function processImage(Request $request)
    {
        // Ambil path file dari session
        $path = session('file_to_process');
        if (!$path) {
            return response()->json(['status' => 'error', 'message' => 'File not found in session.'], 400);
        }

        $fullPath = Storage::disk('public')->path($path);
        $fileName = basename($path);

        try {
            // API Flask
            $response = Http::asMultipart()
                ->attach('file', file_get_contents($fullPath), $fileName)
                ->post('http://127.0.0.1:5000/analyze');

            if (!$response->successful()) {
                $errorMessage = $response->json('error', 'AI server returned an error.');
                return response()->json(['status' => 'error', 'message' => $errorMessage], 502);
            }
            
            $data = $response->json();

            if (isset($data['llm']) && is_array($data['llm'])) {
                
                session(['prediction_result' => $data['llm']]); 
                
                session()->forget('file_to_process');
                return response()->json(['status' => 'success']);

            } else {
                return response()->json(['status' => 'error', 'message' => 'LLM result key not found in response from AI server.'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function save(Request $request)
    {
    // [PERBAIKAN] Bungkus semua dalam try...catch
        try {
            // 1. Ambil data dari form
            $deskripsi = $request->input('deskripsi');
            $tanggal = $request->input('tanggal');
            $akunTerpilihKode = $request->input('selected_account');
            
            if ($akunTerpilihKode == 'manual') {
                $akunTerpilihKode = $request->input('manual_account_code');
                $akunTerpilihNama = $request->input('manual_account_name');
                
                if(empty($akunTerpilihKode) || empty($akunTerpilihNama)) {
                    // [PERBAIKAN] Kirim error sebagai JSON
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'Jika memilih manual, Kode Akun dan Nama Akun manual wajib diisi.'
                    ], 422); // 422 Unprocessable Entity
                }

            } else {
                $akunTerpilihNama = $request->input('selected_account_name');
            }

            $akunPembayaran = $request->input('payment_account_code', '111');
            $items_nama = $request->input('item_nama');
            $items_harga = $request->input('item_harga');
            $items_jumlah = $request->input('item_jumlah');
            
            // 2. Tentukan Nominal Total (Logika Anda sudah benar)
            $totalNominal = 0;
            if (is_array($items_nama) && !empty($items_nama)) {
                for ($i = 0; $i < count($items_nama); $i++) {
                    $totalNominal += (float)$items_harga[$i] * (int)$items_jumlah[$i];
                }
            } else {
                $totalNominal = (float)$request->input('nominal_total', 0);
            }
            
            $imageUrl = session('file_public_url');
            
            // 4. Buat ID & Kode Transaksi (Logika Anda sudah benar)
            $transactionGroupId = (string) Str::uuid();
            $periode = \Carbon\Carbon::parse($tanggal)->format('Ym');
            $prefix = 'JV-' . $periode . '-';
            $nextSequentialNumber = DB::table('ledger_entries')
                ->where('transaction_code', 'like', $prefix . '%')
                ->distinct('transaction_group_id')
                ->count('transaction_group_id') + 1;
            $transactionCode = $prefix . str_pad($nextSequentialNumber, 5, '0', STR_PAD_LEFT);

            // 5. Buat Entri Pertama (Debit)
            $entry1 = LedgerEntry::create([
                'transaction_group_id' => $transactionGroupId,
                'transaction_code' => $transactionCode,
                'date' => $tanggal,
                'account_code' => $akunTerpilihKode,
                'account_name' => $akunTerpilihNama,
                'debit' => $totalNominal,
                'credit' => 0,
                'description' => $deskripsi,
                'receipt_image_path' => $imageUrl,
            ]);

            // 6. Buat Entri Kedua (Kredit)
            LedgerEntry::create([
                'transaction_group_id' => $transactionGroupId,
                'transaction_code' => $transactionCode,
                'date' => $tanggal,
                'account_code' => $akunPembayaran,
                'account_name' => 'Kas', // TODO: Buat dinamis
                'debit' => 0,
                'credit' => $totalNominal,
                'description' => "Pembayaran untuk: " . $deskripsi,
                'receipt_image_path' => $imageUrl,
            ]);

            // 7. Simpan item-item
            if (is_array($items_nama) && !empty($items_nama)) {
                for ($i = 0; $i < count($items_nama); $i++) {
                    TransactionItem::create([
                        'ledger_entry_id' => $entry1->id,
                        'item_name' => $items_nama[$i],
                        'price' => $items_harga[$i],
                        'quantity' => $items_jumlah[$i],
                    ]);
                }
            }
            
            // 8. Bersihkan session
            session()->forget(['prediction_result', 'file_public_url', 'file_to_process']);
            
            // [PERBAIKAN] Kembalikan JSON sukses, bukan redirect
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            // [PERBAIKAN] Tangani error server
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}