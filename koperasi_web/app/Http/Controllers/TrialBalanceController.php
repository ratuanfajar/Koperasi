<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tentukan periode default (Bulan Ini)
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;

        // 2. Ambil dateRange (dari filter atau default)
        $dateRange = $request->input('date_range', $defaultDateRange);

        // 3. Buat query dasar
        $query = LedgerEntry::query();
        
        // 4. [PERBAIKAN] Selalu terapkan filter tanggal (karena $dateRange selalu ada)
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $query->whereBetween('date', [$dates[0], $dates[1]]);
        }

        // 5. Jalankan logika Trial Balance
        $balances = $query->select(
                            'account_code', 
                            DB::raw('MIN(account_name) as account_name'),
                            DB::raw('SUM(debit) as total_debit'),
                            DB::raw('SUM(credit) as total_credit')
                        )
                        ->groupBy('account_code')
                        ->orderBy('account_code', 'asc')
                        ->get();

        // 6. Hitung Total
        $totalDebit = $balances->sum('total_debit');
        $totalCredit = $balances->sum('total_credit');

        // 7. Kirim ke view
        return view('dashboard.trial-balance', [
            'balances' => $balances,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'dateRange' => $dateRange 
        ]);
    }

    public function exportCsv(Request $request)
    {
        // 1. Tentukan periode default (sama seperti index)
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        $dateRange = $request->input('date_range', $defaultDateRange);

        // 2. Buat query dasar
        $query = LedgerEntry::query();

        // 3. [PERBAIKAN] Selalu terapkan filter tanggal
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $query->whereBetween('date', [$dates[0], $dates[1]]);
        }

        // 4. Jalankan query
        $balances = $query->select(
                            'account_code', 
                            DB::raw('MIN(account_name) as account_name'),
                            DB::raw('SUM(debit) as total_debit'),
                            DB::raw('SUM(credit) as total_credit')
                        )
                        ->groupBy('account_code')
                        ->orderBy('account_code', 'asc')
                        ->get();
        
        // 5. [PERBAIKAN] Validasi data kosong (feedback untuk user)
        if ($balances->isEmpty()) {
            return redirect()->route('trial-balance', $request->query())
                             ->with('error', 'Tidak ada data untuk diekspor pada periode ini.');
        }
        
        // 6. Hitung total
        $totalDebit = $balances->sum('total_debit');
        $totalCredit = $balances->sum('total_credit');

        // 7. Buat file CSV
        $fileName = 'trial_balance_export_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function() use ($balances, $totalDebit, $totalCredit) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Account', 'Account Name', 'Debit (Rp)', 'Credit (Rp)']);

            // Data
            foreach ($balances as $balance) {
                fputcsv($handle, [
                    $balance->account_code,
                    $balance->account_name,
                    $balance->total_debit,
                    $balance->total_credit
                ]);
            }

            // Total
            fputcsv($handle, ['']); 
            fputcsv($handle, ['Total', '', $totalDebit, $totalCredit]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);

        return $response;
    }
}