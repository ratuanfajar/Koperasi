<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
use App\Models\ChartOfAccount; 
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PostingController extends Controller
{
    public function index(Request $request)
    {
        $accounts = ChartOfAccount::orderBy('code', 'asc')->get();

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate   = now()->endOfMonth()->format('Y-m-d');
        $dateRange = $request->input('date_range');

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            $startDate = $dates[0] ?? $startDate;
            $endDate   = $dates[1] ?? $endDate;
        }

        $selectedAccount = null;
        $entries         = new LengthAwarePaginator([], 0, 10);
        $saldoAwal       = 0;

        if ($request->filled('account_filter')) {
            $selectedAccountCode = $request->input('account_filter');
            $selectedAccount     = $accounts->where('code', $selectedAccountCode)->first();
        } else {
            $selectedAccount = $accounts->first(function ($account) {
                return $account->code === '101' || stripos($account->name, 'Kas') !== false;
            });
            if (!$selectedAccount) {
                $selectedAccount = $accounts->first();
            }
        }

        // Query Data (Hanya jika ada akun terpilih)
        if ($selectedAccount) {
            // A. Hitung Saldo Awal (Sama)
            $saldoAwal = LedgerEntry::where('account_code', $selectedAccount->code)
                ->where('date', '<', $startDate)
                ->sum(DB::raw('debit - credit'));

            // B. Ambil Data Transaksi Periode Ini (Sama)
            $entriesQuery = LedgerEntry::where('account_code', $selectedAccount->code)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc');

            $entries = $entriesQuery->paginate(10); 
    
            $entries->getCollection()->transform(function ($entry) use ($selectedAccount) {

                $contraEntries = LedgerEntry::where('transaction_code', $entry->transaction_code)
                    ->where('account_code', '!=', $selectedAccount->code)
                    ->get(); 

                $entry->splits = $contraEntries;
                
                return $entry;
            });

            $entries->appends($request->all());
        }

        return view('dashboard.posting', [ 
            'accounts'        => $accounts,
            'entries'         => $entries,
            'selectedAccount' => $selectedAccount,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'saldoAwal'       => $saldoAwal 
        ]);
    }

    public function exportCsv(Request $request)
    {
        if (!$request->filled('account_filter')) {
            return back()->with('error', 'Silakan pilih akun terlebih dahulu.');
        }

        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate   = now()->endOfMonth()->format('Y-m-d');
        $dateRange = $request->input('date_range');

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            $startDate = $dates[0] ?? $startDate;
            $endDate   = $dates[1] ?? $endDate;
        }

        $accountCode     = $request->input('account_filter');
        $selectedAccount = ChartOfAccount::where('code', $accountCode)->first();

        // 1. Hitung Saldo Awal
        $saldo = LedgerEntry::where('account_code', $accountCode)
                ->where('date', '<', $startDate)
                ->sum(DB::raw('debit - credit'));

        // 2. Ambil Data
        $entries = LedgerEntry::where('account_code', $accountCode)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

        $fileName = 'Buku_Besar_' . $selectedAccount->name . '_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function() use ($entries, $selectedAccount, $saldo, $accountCode) {
            $handle = fopen('php://output', 'w');
            
            // Header Info
            fputcsv($handle, ['LAPORAN BUKU BESAR (DETAIL)']);
            fputcsv($handle, ['Akun:', $selectedAccount->code . ' - ' . $selectedAccount->name]);
            fputcsv($handle, []); 

            // Header Tabel
            fputcsv($handle, [
                'Tanggal', 'No. Bukti', 'Keterangan (Akun Lawan)', 'Debit (Rp)', 'Kredit (Rp)', 'D/K', 'Saldo (Rp)'
            ]);

            // 4. INISIALISASI VARIABEL TOTAL
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($entries as $entry) {
                // Logic Saldo berjalan
                $saldo += ($entry->debit - $entry->credit);
                $posisi = ($saldo >= 0) ? 'D' : 'K';

                // Ambil Contra Entries (Split)
                $contraEntries = LedgerEntry::where('transaction_code', $entry->transaction_code)
                    ->where('account_code', '!=', $accountCode)
                    ->get();

                if ($contraEntries->isEmpty()) {
                    
                    // 5. TAMBAHKAN KE TOTAL (Kasus Single Row)
                    $totalDebit  += $entry->debit;
                    $totalCredit += $entry->credit;

                    fputcsv($handle, [
                        $entry->date->format('Y-m-d'),
                        $entry->transaction_code,
                        'Penyesuaian / Saldo Awal', 
                        $entry->debit,
                        $entry->credit,
                        $posisi,
                        abs($saldo) 
                    ]);
                } else {
                    // Loop Split Row
                    foreach ($contraEntries as $index => $contra) {
                        
                        $showDebit  = 0;
                        $showCredit = 0;

                        if ($entry->debit > 0) {
                            $showDebit = ($contra->credit > 0) ? $contra->credit : $contra->debit; 
                        } else {
                            $showCredit = ($contra->debit > 0) ? $contra->debit : $contra->credit;
                        }

                        // 6. TAMBAHKAN KE TOTAL (Kasus Split Row)
                        $totalDebit  += $showDebit;
                        $totalCredit += $showCredit;

                        $dateShow  = ($index === 0) ? $entry->date->format('Y-m-d') : '';
                        $buktiShow = ($index === 0) ? $entry->transaction_code : '';
                        $saldoShow = ($index === $contraEntries->count() - 1) ? abs($saldo) : ''; 
                        $posisiShow= ($index === $contraEntries->count() - 1) ? $posisi : '';

                        fputcsv($handle, [
                            $dateShow,
                            $buktiShow,
                            $contra->account_name, 
                            $showDebit,            
                            $showCredit,           
                            $posisiShow,
                            $saldoShow 
                        ]);
                    }
                }
            }

            // 7. CETAK BARIS TOTAL DI FOOTER CSV
            fputcsv($handle, []); 
            fputcsv($handle, [
                '',                 
                '',                
                'TOTAL MUTASI',     
                $totalDebit,       
                $totalCredit,     
                '',                 
                abs($saldo)         
            ]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}