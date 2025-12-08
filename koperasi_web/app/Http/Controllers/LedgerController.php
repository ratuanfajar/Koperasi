<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tentukan periode default
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        
        $dateRange = $request->input('date_range', $defaultDateRange);

        // 2. Buat query dasar
        $groupQuery = LedgerEntry::select('transaction_group_id', 
                                          DB::raw('MAX(date) as max_date'))
                                 ->groupBy('transaction_group_id');
        
        // 3. Filter tanggal
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $groupQuery->whereBetween('date', [$dates[0], $dates[1]]);
        }

        // 4. Paginasi
        $paginatedGroups = $groupQuery->orderBy('max_date', 'desc')->paginate(10);
        $groups = LedgerEntry::whereIn('transaction_group_id', $paginatedGroups->pluck('transaction_group_id'))
                             ->with('items')
                             ->orderBy('date', 'desc')
                             ->get()
                             ->groupBy('transaction_group_id');

        $accounts = DB::table('chart_of_accounts')->orderBy('code')->get();

        return view('dashboard.ledger', [
            'paginator' => $paginatedGroups,
            'groups' => $groups,
            'dateRange' => $dateRange,
            'accounts' => $accounts
        ]);
    }

    public function exportCsv(Request $request)
    {
        // 1. Setup Tanggal
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        $dateRange = $request->input('date_range', $defaultDateRange);

        // 2. Query Data
        $query = LedgerEntry::orderBy('date', 'asc') 
                            ->orderBy('created_at', 'asc'); 

        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $query->whereBetween('date', [$dates[0], $dates[1]]);
        }

        $entries = $query->get();
        
        if ($entries->isEmpty()) {
            return redirect()->route('ledger', $request->query())
                            ->with('error', 'Tidak ada data untuk diekspor pada filter ini.');
        }

        $fileName = 'jurnal_umum_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function() use ($entries) {
            $handle = fopen('php://output', 'w');
            
            // 3. Header CSV
            fputcsv($handle, [
                'Tanggal', 
                'No. Bukti Transaksi', 
                'Nama Akun', 
                'Pos Ref (Kode)', 
                'Debit', 
                'Kredit',
            ]);

            // 4. Inisialisasi Variabel Total
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($entries as $entry) {
                // Hitung Total Berjalan
                $totalDebit += $entry->debit;
                $totalCredit += $entry->credit;

                fputcsv($handle, [
                    $entry->date->format('Y-m-d'), 
                    $entry->transaction_code, 
                    $entry->account_name, 
                    $entry->account_code, 
                    $entry->debit, 
                    $entry->credit
                ]);
            }

            // 5. Tulis Baris TOTAL di paling bawah
            fputcsv($handle, [
                '',             
                '',             
                'TOTAL',        
                '',             
                $totalDebit,    
                $totalCredit    
            ]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function destroy($transaction_code)
    {
        LedgerEntry::where('transaction_code', $transaction_code)->delete();

        return redirect()->back()->with('success', 'Bukti transaksi berhasil dihapus.');  
    }

    public function update(Request $request, $transaction_code)
    {
        DB::transaction(function () use ($request, $transaction_code) {
            // 1. AMBIL DATA LAMA SEBELUM DIHAPUS
            $oldEntry = LedgerEntry::where('transaction_code', $transaction_code)->first();
            
            // Fallback: Jika entah kenapa data tidak ada, buat UUID baru (opsional)
            $groupId = $oldEntry ? $oldEntry->transaction_group_id : \Illuminate\Support\Str::uuid();
            $imagePath = $oldEntry ? $oldEntry->receipt_image_path : null;

            // 2. HAPUS JURNAL LAMA
            LedgerEntry::where('transaction_code', $transaction_code)->delete();

            // 3. INPUT JURNAL BARU
            foreach ($request->items as $item) {
                // Pastikan debit/kredit ada nilainya, ubah null jadi 0
                $debit = filter_var($item['debit'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $credit = filter_var($item['credit'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                // Cek jika baris ini memiliki nilai (tidak kosong)
                if ($debit > 0 || $credit > 0) {
                    LedgerEntry::create([
                        'transaction_group_id' => $groupId, 
                        
                        'date' => $request->date,
                        'transaction_code' => $transaction_code,
                        
                        // Pecah string "Kode|Nama" dari dropdown
                        'account_code' => explode('|', $item['account_info'])[0],
                        'account_name' => explode('|', $item['account_info'])[1],
                        
                        'debit' => $debit,
                        'credit' => $credit,
                        
                        'receipt_image_path' => $imagePath,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Transaksi berhasil diperbarui.');
    }
}