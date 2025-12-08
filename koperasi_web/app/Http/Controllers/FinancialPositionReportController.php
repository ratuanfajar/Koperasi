<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon; 

class FinancialPositionReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Input Filter
        $selectedYear  = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', 'all'); 

        // 2. Tentukan Label Periode
        if ($selectedMonth == 'all') {
            $periodLabel = "Tahun " . $selectedYear;
        } else {
            $dt = \Carbon\Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1);
            $periodLabel = $dt->locale('id')->translatedFormat('F Y');
        }

        // 3. Hitung Data (Sekarang fungsi ini menerima bulan)
        $reportData = $this->calculateBalanceData($selectedYear, $selectedMonth);

        return view('dashboard.financial-position-report', compact('reportData', 'selectedYear', 'selectedMonth', 'periodLabel'));
    }

    public function exportCsv(Request $request)
    {
        // 1. Ambil Filter (Harus sama dengan index agar hasil download sesuai tampilan)
        $selectedYear  = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', 'all');

        // Tentukan Nama File & Header Kolom
        if ($selectedMonth == 'all') {
            $periodLabel = "Tahun " . $selectedYear;
            $fileName = 'Laporan_Posisi_Keuangan_Tahun_' . $selectedYear . '.csv';
        } else {
            $dt = \Carbon\Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1);
            $periodLabel = $dt->locale('id')->translatedFormat('F Y');
            $monthName = $dt->locale('id')->translatedFormat('F');
            $fileName = 'Laporan_Posisi_Keuangan_' . $monthName . '_' . $selectedYear . '.csv';
        }

        // 2. Hitung Data
        $data = $this->calculateBalanceData($selectedYear, $selectedMonth);

        // 3. Setup CSV Streaming
        $response = new StreamedResponse(function () use ($data, $periodLabel) {
            $handle = fopen('php://output', 'w');

            // Header Laporan
            fputcsv($handle, ['LAPORAN POSISI KEUANGAN']);
            fputcsv($handle, [$periodLabel]);
            fputcsv($handle, []);
            fputcsv($handle, ['URAIAN', 'NILAI (Rp)']);

            // Definisi Baris
            $rows = [
                ['ASET', null, true],
                ['Kas dan setara kas', 'kas_setara_kas', false],
                ['Piutang bunga', 'piutang_bunga', false],
                ['Pinjaman anggota', 'pinjaman_anggota', false],
                ['Penyisihan pinjaman', 'penyisihan_pinjaman', false], 
                ['Pinjaman koperasi lain', 'pinjaman_koperasi_lain', false],
                ['Aset tetap', 'aset_tetap', false],
                ['Akumulasi penyusutan', 'akumulasi_penyusutan', false],
                ['Aset tak berwujud', 'aset_tak_berwujud', false],
                ['Akumulasi amortisasi', 'akumulasi_amortisasi', false],
                ['Aset lain', 'aset_lain', false],
                ['TOTAL ASET', 'total_aset', false],

                ['', null, true],

                ['LIABILITAS', null, true],
                ['Utang bunga', 'utang_bunga', false],
                ['Simpanan anggota', 'simpanan_anggota', false],
                ['Simpanan koperasi lain', 'simpanan_koperasi_lain', false],
                ['Utang pinjaman', 'utang_pinjaman', false],
                ['Liabilitas imbalan kerja', 'liabilitas_imbalan_kerja', false],
                ['Liabilitas lain', 'liabilitas_lain', false],
                ['TOTAL LIABILITAS', 'total_liabilitas', false],

                ['', null, true], 

                ['EKUITAS', null, true],
                ['Simpanan Pokok', 'ekuitas_simpanan_pokok', false],
                ['Simpanan Wajib', 'ekuitas_simpanan_wajib', false],
                ['Simpanan Lain/Khusus', 'ekuitas_simpanan_lain', false],
                ['Modal Sumbangan', 'ekuitas_sumbangan', false],
                ['Cadangan', 'ekuitas_cadangan', false],
                ['SHU Tahun Berjalan', 'ekuitas_shu_berjalan', false],
                ['TOTAL EKUITAS', 'total_ekuitas', false],
                
                ['TOTAL LIABILITAS DAN EKUITAS', 'total_liabilitas_ekuitas', false],
            ];

            foreach ($rows as $rowDef) {
                $label = $rowDef[0];
                $key   = $rowDef[1];
                $isHeader = $rowDef[2];

                if ($isHeader) {
                    fputcsv($handle, [strtoupper($label), '']);
                } else {
                    $val = $data[$key] ?? 0;
                    
                    // Logic khusus CSV: Akun kontra dibuat negatif agar user paham itu mengurangi
                    if (in_array($key, ['penyisihan_pinjaman', 'akumulasi_penyusutan', 'akumulasi_amortisasi'])) {
                        $val = -abs($val);
                    }
                    
                    fputcsv($handle, [$label, $val]);
                }
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    private function calculateBalanceData($year, $month = 'all')
    {
        $getBalance = function ($codes, $type = 'debit') use ($year, $month) {
            if (!is_array($codes)) $codes = [$codes];
            
            $total = 0;
            foreach ($codes as $code) {
                $query = LedgerEntry::whereYear('date', $year) 
                    ->where('account_code', 'like', $code . '%');

                // Berbeda dengan SHU yang "=" bulan ini, Neraca harus "<="
                if ($month != 'all') {
                    $query->whereMonth('date', '<=', (int)$month); 
                }
                
                if ($type === 'debit') {
                    $total += $query->sum(DB::raw('debit - credit'));
                } else {
                    $total += $query->sum(DB::raw('credit - debit'));
                }
            }
            return $total;
        };

        // ==========================================
        // 1. ASET (Aktiva)
        // ==========================================
        $kas_setara_kas      = $getBalance(['101', '102', '103', '104', '105', '108'], 'debit');
        $piutang_bunga       = $getBalance('111', 'debit');
        $pinjaman_anggota    = $getBalance('109', 'debit');
        $penyisihan_pinjaman = $getBalance('113', 'credit'); 
        $pinjaman_koperasi_lain = $getBalance(['110', '112'], 'debit');

        $aset_tetap           = $getBalance(['121', '122', '123', '124'], 'debit');
        $akumulasi_penyusutan = $getBalance(['125', '126', '127'], 'credit');

        $aset_tak_berwujud    = $getBalance('131', 'debit');
        $akumulasi_amortisasi = 0; 
        $aset_lain            = $getBalance(['114', '115', '116', '117', '118'], 'debit');

        $total_aset = ($kas_setara_kas + $piutang_bunga + $pinjaman_anggota + $pinjaman_koperasi_lain + 
                       $aset_tetap + $aset_tak_berwujud + $aset_lain) 
                       - ($penyisihan_pinjaman + $akumulasi_penyusutan + $akumulasi_amortisasi);

        // ==========================================
        // 2. LIABILITAS (Kewajiban)
        // ==========================================
        $utang_bunga             = $getBalance('203', 'credit');
        $simpanan_anggota        = $getBalance(['201', '205'], 'credit');
        $simpanan_koperasi_lain  = $getBalance(['202', '206'], 'credit');
        $utang_pinjaman          = $getBalance(['210', '211'], 'credit');
        $liabilitas_imbalan_kerja= 0; 
        $liabilitas_lain         = $getBalance('204', 'credit');

        $total_liabilitas = $utang_bunga + $simpanan_anggota + $simpanan_koperasi_lain + 
                            $utang_pinjaman + $liabilitas_imbalan_kerja + $liabilitas_lain;

        // ==========================================
        // 3. EKUITAS (Modal)
        // ==========================================
        $ekuitas_simpanan_pokok = $getBalance('301', 'credit');
        $ekuitas_simpanan_wajib = $getBalance('302', 'credit');
        $ekuitas_simpanan_lain  = $getBalance(['303', '304', '305'], 'credit');
        $ekuitas_sumbangan      = $getBalance('306', 'credit');
        $ekuitas_cadangan       = $getBalance(['307', '308'], 'credit');

        // HITUNG SHU TAHUN BERJALAN 
        // Logic: Pendapatan - Beban
        $total_pendapatan_th_ini = $getBalance('4', 'credit');
        $total_beban_th_ini      = $getBalance('5', 'debit');
        $ekuitas_shu_berjalan    = $total_pendapatan_th_ini - $total_beban_th_ini;

        $total_ekuitas = $ekuitas_simpanan_pokok + $ekuitas_simpanan_wajib + $ekuitas_simpanan_lain + 
                         $ekuitas_sumbangan + $ekuitas_cadangan + $ekuitas_shu_berjalan;

        // TOTAL PASIVA
        $total_liabilitas_ekuitas = $total_liabilitas + $total_ekuitas;

        return [
            'kas_setara_kas'        => $kas_setara_kas,
            'piutang_bunga'         => $piutang_bunga,
            'pinjaman_anggota'      => $pinjaman_anggota,
            'penyisihan_pinjaman'   => $penyisihan_pinjaman,
            'pinjaman_koperasi_lain'=> $pinjaman_koperasi_lain,
            'aset_tetap'            => $aset_tetap,
            'akumulasi_penyusutan'  => $akumulasi_penyusutan,
            'aset_tak_berwujud'     => $aset_tak_berwujud,
            'akumulasi_amortisasi'  => $akumulasi_amortisasi,
            'aset_lain'             => $aset_lain,
            'total_aset'            => $total_aset,

            'utang_bunga'             => $utang_bunga,
            'simpanan_anggota'        => $simpanan_anggota,
            'simpanan_koperasi_lain'  => $simpanan_koperasi_lain,
            'utang_pinjaman'          => $utang_pinjaman,
            'liabilitas_imbalan_kerja'=> $liabilitas_imbalan_kerja,
            'liabilitas_lain'         => $liabilitas_lain,
            'total_liabilitas'        => $total_liabilitas,

            'ekuitas_simpanan_pokok'  => $ekuitas_simpanan_pokok,
            'ekuitas_simpanan_wajib'  => $ekuitas_simpanan_wajib,
            'ekuitas_simpanan_lain'   => $ekuitas_simpanan_lain,
            'ekuitas_sumbangan'       => $ekuitas_sumbangan,
            'ekuitas_cadangan'        => $ekuitas_cadangan,
            'ekuitas_shu_berjalan'    => $ekuitas_shu_berjalan,
            'total_ekuitas'           => $total_ekuitas,
            
            'total_liabilitas_ekuitas'=> $total_liabilitas_ekuitas,
        ];
    }
}