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
        // 1. Tentukan periode default (Bulan Ini)
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        
        $dateRange = $request->input('date_range', $defaultDateRange);

        // 2. Buat query dasar
        $groupQuery = LedgerEntry::select('transaction_group_id', 
                                          DB::raw('MAX(date) as max_date'))
                                 ->groupBy('transaction_group_id');
        
        // 3. [HANYA FILTER TANGGAL] Terapkan filter tanggal
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $groupQuery->whereBetween('date', [$dates[0], $dates[1]]);
        }

        // 4. Paginasi dan ambil data
        $paginatedGroups = $groupQuery->orderBy('max_date', 'desc')->paginate(10);
        $groups = LedgerEntry::whereIn('transaction_group_id', $paginatedGroups->pluck('transaction_group_id'))
                             ->with('items')
                             ->orderBy('date', 'desc')
                             ->get()
                             ->groupBy('transaction_group_id');

        // 5. Kirim data ke view (tanpa $categories)
        return view('dashboard.ledger', [
            'paginator' => $paginatedGroups,
            'groups' => $groups,
            'dateRange' => $dateRange
        ]);
    }

    public function exportCsv(Request $request)
    {
        // Tentukan periode default
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $defaultDateRange = $startDate . ' to ' . $endDate;
        $dateRange = $request->input('date_range', $defaultDateRange);

        // Buat query dasar
        $query = LedgerEntry::orderBy('date', 'desc');

        // [HANYA FILTER TANGGAL] Terapkan filter
        $dates = explode(' to ', $dateRange);
        if (count($dates) == 2) {
            $query->whereBetween('date', [$dates[0], $dates[1]]);
        }

        $entries = $query->get();
        
        if ($entries->isEmpty()) {
            return redirect()->route('ledger', $request->query())
                             ->with('error', 'Tidak ada data untuk diekspor pada filter ini.');
        }

        $fileName = 'ledger_export_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function() use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date', 'Transaction Code', 'Description', 'Account Code', 
                'Account Name', 'Debit', 'Credit', 'Receipt Image Path'
            ]);
            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->date, $entry->transaction_code, $entry->description, 
                    $entry->account_code, $entry->account_name, $entry->debit, 
                    $entry->credit, $entry->receipt_image_path
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