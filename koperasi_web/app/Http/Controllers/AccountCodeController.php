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
    public function showDocument($filename)
    {
        // Cek apakah file ada di disk private
        if (!Storage::disk('private')->exists('uploads/' . $filename)) {
            abort(404);
        }

        // Kembalikan file sebagai respons gambar
        return response()->file(storage_path('app/private/uploads/' . $filename));
    }

    public function index($step = 1)
    {
        $step = in_array($step, [1, 2, 3]) ? $step : 1;

        // 1. Jika user mencoba akses Step 2 (Processing) tapi belum upload file
        if ($step == 2 && !session()->has('file_to_process')) {
            // Tendang balik ke Step 1
            return redirect()->route('account-code-recommender.show', ['step' => 1]);
        }

        // 2. Jika user mencoba akses Step 3 (Result) tapi belum ada hasil AI
        if ($step == 3 && !session()->has('prediction_result')) {
            // Tendang balik ke Step 1
            return redirect()->route('account-code-recommender.show', ['step' => 1]);
        }

        $result = session('prediction_result', null);
        $filePath = session('file_to_process', null);
        $imageUrl = null;

        // Jika ada file, buat URL menggunakan Route khusus, bukan Storage::url
        if ($filePath) {
            $filename = basename($filePath);
            $imageUrl = route('document.show', ['filename' => $filename]);
        }

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
            
            $path = $request->file('document')->store('uploads', 'private');

            session(['file_to_process' => $path]);

            // redirect ke Step 2
            return redirect()->route('account-code-recommender.show', ['step' => 2])
                ->with('success', 'File berhasil di-upload.'); 
        }

        return redirect()->route('account-code-recommender.show', ['step' => 1])
            ->with('error', 'Gagal, tidak ada file yang dipilih.');
    }

    public function processImage(Request $request)
    {
        set_time_limit(0);

        // Ambil path file dari session
        $path = session('file_to_process');
        if (!$path) {
            return response()->json(['status' => 'error', 'message' => 'File not found in session.'], 400);
        }

        $fullPath = Storage::disk('private')->path($path);
        $fileName = basename($path);

        try {
            // API Flask
            $response = Http::asMultipart()
                ->timeout(120)     
                ->connectTimeout(120)
                ->attach('file', file_get_contents($fullPath), $fileName)
                ->post('http://127.0.0.1:5000/analyze');

            if (!$response->successful()) {
                $errorMessage = $response->json('error', 'AI server returned an error.');
                return response()->json(['status' => 'error', 'message' => $errorMessage], 502);
            }
            
            $data = $response->json();

            if (isset($data['llm']) && is_array($data['llm'])) {
                session(['prediction_result' => $data['llm']]); 
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Format respon AI tidak sesuai.'], 500);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Error khusus jika Flask mati atau Timeout
            return response()->json(['status' => 'error', 'message' => 'Koneksi ke AI Timeout. Coba gunakan gambar yang lebih kecil/jelas.'], 504);
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
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'Jika memilih manual, Kode Akun dan Nama Akun manual wajib diisi.'
                    ], 422);
                }

            } else {
                $akunTerpilihNama = $request->input('selected_account_name');
            }

            // [PERBAIKAN] Ubah default akun pembayaran ke 111 (Kas)
            $akunPembayaran = $request->input('payment_account_code', '111');
            $items_nama = $request->input('item_nama');
            $items_harga = $request->input('item_harga');
            $items_jumlah = $request->input('item_jumlah');
            
            // 2. Tentukan Nominal Total
            $totalNominal = 0;
            if (is_array($items_nama) && !empty($items_nama)) {
                for ($i = 0; $i < count($items_nama); $i++) {
                    $totalNominal += (float)$items_harga[$i] * (int)$items_jumlah[$i];
                }
            } else {
                $totalNominal = (float)$request->input('nominal_total', 0);
            }
            
            // [PERBAIKAN PENTING] Ambil PATH file, bukan URL
            $filePath = session('file_to_process'); 
            
            // 4. Buat ID & Kode Transaksi
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
                'receipt_image_path' => $filePath, // [PERBAIKAN] Gunakan $filePath
            ]);

            // 6. Buat Entri Kedua (Kredit)
            LedgerEntry::create([
                'transaction_group_id' => $transactionGroupId,
                'transaction_code' => $transactionCode,
                'date' => $tanggal,
                'account_code' => $akunPembayaran,
                'account_name' => 'Kas', // [PERBAIKAN] Nama akun Kas
                'debit' => 0,
                'credit' => $totalNominal,
                'description' => "Pembayaran untuk: " . $deskripsi,
                'receipt_image_path' => $filePath, // [PERBAIKAN] Gunakan $filePath
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
            
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}