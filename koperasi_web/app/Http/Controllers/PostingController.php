<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
// [PERBAIKAN] Tambahkan 'use' statement yang hilang untuk CSV
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostingController extends Controller
{
    public function index(Request $request)
    {
        $accounts = LedgerEntry::select('account_code', 'account_name')
                               ->distinct()
                               ->orderBy('account_code', 'asc')
                               ->get();

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        
        $dateRange = $request->input('date_range', $defaultDateRange);

        $entries = [];
        $selectedAccount = null;
        $runningBalance = 0;

        if ($request->filled('account_filter')) {
            
            $selectedAccountCode = $request->input('account_filter');
            $selectedAccount = $accounts->firstWhere('account_code', $selectedAccountCode);

            $accountQuery = LedgerEntry::where('account_code', $selectedAccountCode);
                                        
            $dates = explode(' to ', $dateRange);
            if (count($dates) == 2) {
                $accountQuery->whereBetween('date', [$dates[0], $dates[1]]);
            }
            
            $accountEntries = $accountQuery->orderBy('date', 'asc')->get();

            foreach ($accountEntries as $entry) {
                $runningBalance += $entry->debit - $entry->credit;
                $entry->balance = $runningBalance;
                $entries[] = $entry;
            }
        }

        return view('dashboard.posting', [
            'accounts' => $accounts,
            'entries' => $entries,
            'selectedAccount' => $selectedAccount,
            'dateRange' => $dateRange 
        ]);
    }

    public function exportCsv(Request $request)
    {
        if (!$request->filled('account_filter')) {
            return redirect()->route('posting', $request->query())
                             ->with('error', 'Silakan pilih akun terlebih dahulu untuk mengekspor.');
        }

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        $dateRange = $request->input('date_range', $defaultDateRange);

        $selectedAccountCode = $request->input('account_filter');
        $selectedAccount = LedgerEntry::select('account_name', 'account_code')
                                      ->where('account_code', $selectedAccountCode)
                                      ->first();

        $accountQuery = LedgerEntry::where('account_code', $selectedAccountCode);
        
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $accountQuery->whereBetween('date', [$dates[0], $dates[1]]);
        }

        $entries = $accountQuery->orderBy('date', 'asc')->get();
        
        if ($entries->isEmpty()) {
            return redirect()->route('posting', $request->query())
                             ->with('error', 'Tidak ada data untuk diekspor pada filter ini.');
        }

        $fileName = 'posting_export_' . $selectedAccountCode . '_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function() use ($entries, $selectedAccount) {
            
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, ['Account:', $selectedAccount->account_code . ' - ' . $selectedAccount->account_name]);
            fputcsv($handle, ['']); 
            fputcsv($handle, [
                'Date',
                'Trx Code',
                'Description',
                'Debit',
                'Credit',
                'Balance'
            ]);

            $runningBalance = 0;
            foreach ($entries as $entry) {
                $runningBalance += $entry->debit - $entry->credit;
                fputcsv($handle, [
                    $entry->date,
                    $entry->transaction_code,
                    $entry->description,
                    $entry->debit,
                    $entry->credit,
                    $runningBalance 
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);

        return $response;
    }
}