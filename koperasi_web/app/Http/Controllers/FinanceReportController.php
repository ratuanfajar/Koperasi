<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon; // Pastikan Carbon di-import

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('report_type', 'profit_loss');

        // Tentukan periode default
        $defaultStartDate = now()->startOfMonth()->format('Y-m-d');
        $defaultEndDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $defaultStartDate . ' to ' . $defaultEndDate;
        $defaultAsOfDate = now()->format('Y-m-d');

        $data = [];
        $dateFilter = null;

        if ($reportType == 'profit_loss') {
            // --- LOGIKA UNTUK PROFIT & LOSS ---
            
            // [PERBAIKAN] Cek jika input diisi, jika tidak, gunakan default
            $dateFilter = $request->filled('date_filter') 
                            ? $request->input('date_filter') 
                            : $defaultDateRange;
            
            $dates = explode(' to ', $dateFilter);
            if (count($dates) == 2) {
                $data = $this->getProfitLossData($dates[0], $dates[1]);
            }
        } 
        elseif ($reportType == 'balance_sheet') {
            // --- LOGIKA UNTUK NERACA ---
            
            // [PERBAIKAN] Cek jika input diisi, jika tidak, gunakan default "Hari Ini"
            $dateFilter = $request->filled('date_filter') 
                            ? $request->input('date_filter') 
                            : $defaultAsOfDate;
            
            $data = $this->getBalanceSheetData($dateFilter);
        }

        // Kirim data ke view
        return view('dashboard.finance-report', [
            'reportType' => $reportType,
            'dateFilter' => $dateFilter,
            'data' => $data
        ]);
    }

    /**
     * Helper function untuk menghitung Laba Rugi
     */
    private function getProfitLossData($startDate, $endDate)
    {
        // 4xx (Pendapatan) + 7xx (Pendapatan Non-Operasional)
        $totalIncome = LedgerEntry::whereBetween('date', [$startDate, $endDate])
            ->where(function($query) {
                $query->where('account_code', 'LIKE', '4%')
                      // Pastikan kode Aset (111-Kas) tidak ikut terhitung
                      ->where('account_code', 'NOT LIKE', '1%') 
                      ->orWhere('account_code', 'LIKE', '7%');
            })
            ->sum('credit'); // Pendapatan adalah Kredit

        // 5xx (Beban) + 6xx (Beban Non-Operasional)
        $totalExpenses = LedgerEntry::whereBetween('date', [$startDate, $endDate])
            ->where(function($query) {
                $query->where('account_code', 'LIKE', '5%')
                      ->orWhere('account_code', 'LIKE', '6%');
            })
            ->sum('debit'); // Beban adalah Debit

        return [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'profitOrLoss' => $totalIncome - $totalExpenses,
        ];
    }

    /**
     * Helper function untuk menghitung Neraca
     */
    private function getBalanceSheetData($asOfDate)
    {
        // --- 1. Hitung Laba/Rugi Tahun Berjalan ---
        $year = Carbon::parse($asOfDate)->format('Y');
        $startOfYear = $year . '-01-01';
        
        $profitData = $this->getProfitLossData($startOfYear, $asOfDate);
        $currentYearEarnings = $profitData['profitOrLoss'];

        // --- 2. Hitung Aset (1xx) ---
        $assets = LedgerEntry::where('account_code', 'LIKE', '1%')
            ->where('date', '<=', $asOfDate) 
            ->select('account_code', DB::raw('MIN(account_name) as account_name'), 
                     DB::raw('SUM(debit) - SUM(credit) as balance'))
            ->groupBy('account_code')
            ->having('balance', '!=', 0) 
            ->get();
        $totalAssets = $assets->sum('balance');

        // --- 3. Hitung Liabilitas (2xx) ---
        $liabilities = LedgerEntry::where('account_code', 'LIKE', '2%')
            ->where('date', '<=', $asOfDate)
            ->select('account_code', DB::raw('MIN(account_name) as account_name'), 
                     DB::raw('SUM(credit) - SUM(debit) as balance')) // Dibalik
            ->groupBy('account_code')
            ->having('balance', '!=', 0)
            ->get();
        $totalLiabilities = $liabilities->sum('balance');

        // --- 4. Hitung Ekuitas (3xx) ---
        $equity = LedgerEntry::where('account_code', 'LIKE', '3%')
            ->where('date', '<=', $asOfDate)
            ->select('account_code', DB::raw('MIN(account_name) as account_name'), 
                     DB::raw('SUM(credit) - SUM(debit) as balance')) // Dibalik
            ->groupBy('account_code')
            ->having('balance', '!=', 0)
            ->get();
        $totalEquity = $equity->sum('balance');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'currentYearEarnings' => $currentYearEarnings,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity + $currentYearEarnings,
        ];
    }

    /**
     * Fungsi untuk menangani Export CSV
     */
    public function exportCsv(Request $request)
    {
        $reportType = $request->input('report_type', 'profit_loss');

        if ($reportType == 'profit_loss') {
            // --- Logika export Laba Rugi ---
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
            $defaultDateRange = $startDate . ' to ' . $endDate;
            
            $dateFilter = $request->filled('date_filter') 
                            ? $request->input('date_filter') 
                            : $defaultDateRange;
            
            $dates = explode(' to ', $dateFilter);
            if (count($dates) != 2) {
                return redirect()->route('finance-report', $request->query())->with('error', 'Invalid date range for export.');
            }
            
            $data = $this->getProfitLossData($dates[0], $dates[1]);

            // [PERBAIKAN] Validasi jika data kosong
            if ($data['totalIncome'] == 0 && $data['totalExpenses'] == 0) {
                return redirect()->route('finance-report', $request->query())
                                 ->with('error', 'Tidak ada data untuk diekspor pada periode ini.');
            }

            $fileName = 'profit_loss_export_' . $dates[0] . '_to_' . $dates[1] . '.csv';

            $response = new StreamedResponse(function() use ($data) {
                // ... (sisa kode CSV Laba Rugi Anda sudah benar) ...
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Description', 'Total Amount (Rp)']);
                fputcsv($handle, ['Income (4xx, 7xx)', $data['totalIncome']]);
                fputcsv($handle, ['Operational Costs (5xx, 6xx)', $data['totalExpenses']]);
                fputcsv($handle, ['Profit (Loss)', $data['profitOrLoss']]);
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
            return $response;

        } else {
            // --- Logika export Neraca ---
            $defaultAsOfDate = now()->format('Y-m-d');
            $dateFilter = $request->filled('date_filter') 
                            ? $request->input('date_filter') 
                            : $defaultAsOfDate;

            $data = $this->getBalanceSheetData($dateFilter);

            // [PERBAIKAN] Validasi jika data kosong
            if ($data['totalAssets'] == 0 && $data['totalLiabilities'] == 0 && $data['totalEquity'] == 0) {
                return redirect()->route('finance-report', $request->query())
                                 ->with('error', 'Tidak ada data untuk diekspor pada tanggal ini.');
            }
            
            $fileName = 'balance_sheet_export_' . $dateFilter . '.csv';

            $response = new StreamedResponse(function() use ($data) {
                // ... (sisa kode CSV Neraca Anda sudah benar) ...
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Assets']);
                fputcsv($handle, ['Account', 'Balance']);
                foreach($data['assets'] as $asset) {
                    fputcsv($handle, [$asset->account_code . ' - ' . $asset->account_name, $asset->balance]);
                }
                fputcsv($handle, ['Total Assets', $data['totalAssets']]);
                fputcsv($handle, ['']);
                fputcsv($handle, ['Liabilities']);
                fputcsv($handle, ['Account', 'Balance']);
                foreach($data['liabilities'] as $liability) {
                    fputcsv($handle, [$liability->account_code . ' - ' . $liability->account_name, $liability->balance]);
                }
                fputcsv($handle, ['Total Liabilities', $data['totalLiabilities']]);
                fputcsv($handle, ['']);
                fputcsv($handle, ['Equity']);
                fputcsv($handle, ['Account', 'Balance']);
                foreach($data['equity'] as $eq) {
                    fputcsv($handle, [$eq->account_code . ' - ' . $eq->account_name, $eq->balance]);
                }
                fputcsv($handle, ['Current Year Earnings', $data['currentYearEarnings']]);
                fputcsv($handle, ['Total Equity', $data['totalEquity']]);
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
            return $response;
        }
    }
}