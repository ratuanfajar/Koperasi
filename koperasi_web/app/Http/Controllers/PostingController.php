<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
use App\Models\ChartOfAccount; 
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class PostingController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil List Akun dari Master Akun (ChartOfAccount)
        $accounts = ChartOfAccount::orderBy('code', 'asc')->get();

        // 2. Setup Default Date Range (Bulan Ini)
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $dateRange = $request->input('date_range');

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            $startDate = $dates[0] ?? $startDate;
            $endDate = $dates[1] ?? $endDate;
        }

        $selectedAccount = null;

        $entries = new LengthAwarePaginator([], 0, 10);

        if ($request->filled('account_filter')) {
            $selectedAccountCode = $request->input('account_filter');
            $selectedAccount = $accounts->where('code', $selectedAccountCode)->first();
        } else {
            $selectedAccount = $accounts->first();
        }

        // 3. Query Data (Hanya jika ada akun terpilih)
        if ($selectedAccount) {
            $entries = LedgerEntry::where('account_code', $selectedAccount->code)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->paginate(10); 
            
            $entries->appends($request->all());
        }

        return view('dashboard.posting', [ 
            'accounts' => $accounts,
            'entries' => $entries,
            'entries' => $entries,
            'selectedAccount' => $selectedAccount,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function exportCsv(Request $request)
    {
        if (!$request->filled('account_filter')) {
            return back()->with('error', 'Silakan pilih akun terlebih dahulu.');
        }

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $dateRange = $request->input('date_range');

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            $startDate = $dates[0] ?? $startDate;
            $endDate = $dates[1] ?? $endDate;
        }

        $accountCode = $request->input('account_filter');
        $selectedAccount = ChartOfAccount::where('code', $accountCode)->first();

        if (!$selectedAccount) {
            return back()->with('error', 'Akun tidak ditemukan.');
        }

        $entries = LedgerEntry::where('account_code', $accountCode)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->get();

        if ($entries->isEmpty()) {
            return back()->with('error', 'Tidak ada data transaksi untuk diekspor.');
        }

        $fileName = 'Ledger_' . $accountCode . '_' . date('Ymd_His') . '.csv';

        $response = new StreamedResponse(function() use ($entries, $selectedAccount) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, ['Buku Besar Umum']);
            fputcsv($handle, ['Akun:', $selectedAccount->code . ' - ' . $selectedAccount->name]);
            fputcsv($handle, ['Kategori:', $selectedAccount->category]);
            fputcsv($handle, ['']); 

            fputcsv($handle, [
                'Tanggal',
                'Keterangan',
                'Debit',
                'Kredit',
                'D/K', 
                'Saldo'
            ]);

            $saldo = 0;
            $kategori = $selectedAccount->category ?? 'Aset';
            $isNormalDebit = in_array($kategori, ['Aset', 'Beban']);

            foreach ($entries as $entry) {
                if ($isNormalDebit) {
                    $saldo += ($entry->debit - $entry->credit);
                    $posisi = ($saldo >= 0) ? 'D' : 'K';
                } else {
                    $saldo += ($entry->credit - $entry->debit);
                    $posisi = ($saldo >= 0) ? 'K' : 'D';
                }

                fputcsv($handle, [
                    $entry->date,
                    $entry->description ?? '-', 
                    $entry->debit,
                    $entry->credit,
                    $posisi,
                    abs($saldo) 
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