<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerEntry; // Pastikan model ini benar
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class SHUReportController extends Controller
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
        $reportData = $this->calculateShuData($selectedYear, $selectedMonth);

        return view('dashboard.shu-report', compact('reportData', 'selectedYear', 'selectedMonth', 'periodLabel'));
    }

    public function exportCsv(Request $request)
    {
        // 1. Ambil Filter (Harus sama dengan index agar hasil download sesuai tampilan)
        $selectedYear  = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', 'all');

        // Tentukan Nama File & Header Kolom
        if ($selectedMonth == 'all') {
            $periodLabel = "Tahun " . $selectedYear;
            $fileName = 'Laporan_SHU_Tahun_' . $selectedYear . '.csv';
        } else {
            $dt = \Carbon\Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1);
            $periodLabel = $dt->locale('id')->translatedFormat('F Y');
            $monthName = $dt->locale('id')->translatedFormat('F');
            $fileName = 'Laporan_SHU_' . $monthName . '_' . $selectedYear . '.csv';
        }

        // 2. Hitung Data
        $data = $this->calculateShuData($selectedYear, $selectedMonth);

        // 3. Setup CSV Streaming
        $response = new StreamedResponse(function () use ($data, $periodLabel) {
            $handle = fopen('php://output', 'w');

            // Header CSV
            fputcsv($handle, ['LAPORAN SISA HASIL USAHA']);
            fputcsv($handle, ['Periode', $periodLabel]);
            fputcsv($handle, []); 
            fputcsv($handle, ['URAIAN', 'NILAI (Rp)']);

            // Definisi Baris
            $rows = [
                ['PARTISIPASI ANGGOTA', null, true], 
                ['Pendapatan Bunga', 'partisipasi_bunga', false],
                ['Pendapatan Usaha Lain (Provisi & Adm)', 'partisipasi_lain', false],
                ['Jumlah Partisipasi Anggota', 'total_partisipasi', false],
                
                ['BEBAN USAHA', null, true],
                ['Beban Bunga', 'beban_bunga', false],
                ['Beban Penyisihan', 'beban_penyisihan', false],
                ['Beban Kepegawaian', 'beban_pegawai', false],
                ['Beban Administrasi dan Umum', 'beban_admin', false],
                ['Beban Penyusutan dan Amortisasi', 'beban_penyusutan', false],
                ['Beban Usaha Lain', 'beban_usaha_lain', false],
                ['Jumlah Beban Usaha', 'total_beban_usaha', false],

                ['SISA HASIL USAHA BRUTO', 'shu_bruto', false],

                ['', null, true], // Spacer
                ['Hasil Investasi', 'hasil_investasi', false],
                ['Beban Perkoperasian (RAT & Pendidikan)', 'beban_perkoperasian', false],

                ['PENDAPATAN & BEBAN LAIN', null, true],
                ['Pendapatan Lain (Non-Anggota & Sewa)', 'pendapatan_lain', false],
                ['Beban Lain', 'beban_lain', false],

                ['Sisa Hasil Usaha Sebelum Pajak', 'shu_sebelum_pajak', false],
                ['Beban Pajak Penghasilan', 'pajak', false],
                ['SISA HASIL USAHA NETO', 'shu_neto', false],
                ['PENGHASILAN KOMPREHENSIF', 'shu_neto', false],
            ];

            foreach ($rows as $rowDef) {
                $label = $rowDef[0];
                $key   = $rowDef[1];
                $isHeader = $rowDef[2];

                if ($isHeader) {
                    fputcsv($handle, [strtoupper($label), '']);
                } else {
                    $val = $data[$key] ?? 0;
                    fputcsv($handle, [$label, $val]); 
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    private function calculateShuData($year, $month = 'all')
    {
        $getBalance = function ($codes, $type = 'credit') use ($year, $month) {
            if (!is_array($codes)) $codes = [$codes];

            $total = 0;
            foreach ($codes as $code) {
                $query = LedgerEntry::whereYear('date', $year) 
                    ->where('account_code', 'like', $code . '%');
                
                // FILTER BULAN DITAMBAHKAN
                if ($month != 'all') {
                    $query->whereMonth('date', $month);
                }
                
                if ($type === 'credit') {
                    $total += $query->sum(DB::raw('credit - debit'));
                } else {
                    $total += $query->sum(DB::raw('debit - credit'));
                }
            }
            return $total;
        };

        // =================================================================
        // 1. PARTISIPASI ANGGOTA (Pendapatan dari Anggota)
        // =================================================================
        // 401: Jasa Pinjaman
        $partisipasi_bunga = $getBalance('401', 'credit'); 
        // 402: Provisi, 403: Administrasi
        $partisipasi_lain  = $getBalance(['40', '402', '403'], 'credit'); 
        
        $total_partisipasi = $partisipasi_bunga + $partisipasi_lain;


        // =================================================================
        // 2. BEBAN USAHA
        // =================================================================
        // Beban Bunga (Simpanan Anggota, Non-Anggota, Pinjaman Bank)
        // COA: 501, 502, 511, 512, 521
        $beban_bunga = $getBalance(['501', '502', '511', '512', '521'], 'debit');

        // Beban Penyisihan / NPL
        // COA: 532 (Penghapusan), 522 (Provisi Pinjaman)
        $beban_penyisihan = $getBalance(['532', '522'], 'debit');

        // Beban Kepegawaian
        // COA: 524
        $beban_pegawai = $getBalance('524', 'debit');

        // Beban Administrasi & Umum
        // COA: 523, 525(Asuransi), 526(ATK), 527(Listrik), 530(Alat), 531(Lainnya)
        $beban_admin = $getBalance(['523', '525', '526', '527', '530', '531'], 'debit');

        // Beban Penyusutan & Amortisasi
        // COA: 533(Gedung), 534(Kendaraan), 535(Alat)
        $beban_penyusutan = $getBalance(['533', '534', '535'], 'debit');

        // Beban Usaha Lain
        // COA: 536(Macam-macam), 537(Anggaran)
        $beban_usaha_lain = $getBalance(['536', '537'], 'debit');

        // TOTAL BEBAN
        $total_beban_usaha = $beban_bunga + $beban_penyisihan + $beban_pegawai + 
                             $beban_admin + $beban_penyusutan + $beban_usaha_lain;


        // =================================================================
        // 3. SISA HASIL USAHA BRUTO (Calculated)
        // =================================================================
        $shu_bruto = $total_partisipasi - $total_beban_usaha;


        // =================================================================
        // 4. HASIL INVESTASI & PERKOPERASIAN
        // =================================================================
        // Hasil Investasi (Deviden) - COA: 421
        $hasil_investasi = $getBalance('421', 'credit');

        // Beban Perkoperasian (Jati diri koperasi)
        // COA: 538(RAT), 539(Pendidikan)
        $beban_perkoperasian = $getBalance(['538', '539'], 'debit');


        // =================================================================
        // 5. PENDAPATAN & BEBAN LAIN (Termasuk Non-Anggota)
        // =================================================================
        // Pendapatan Lain: Gabungan Non-Anggota (41) + Sewa (422)
        // COA: 411, 412, 413, 422
        $pendapatan_lain = $getBalance(['41', '422'], 'credit');

        // Beban Lain: Pemeliharaan (Non-rutin), Rugi Aset, Penyertaan
        // COA: 528, 529, 541, 542
        $beban_lain = $getBalance(['528', '529', '541', '542'], 'debit');


        // =================================================================
        // 6. TOTAL AKHIR
        // =================================================================
        
        // Rumus: SHU Bruto + Investasi - Beban Perkoperasian + Pendapatan Lain - Beban Lain
        $shu_sebelum_pajak = $shu_bruto + $hasil_investasi - $beban_perkoperasian + 
                             $pendapatan_lain - $beban_lain;

        // Pajak (Biasanya manual journal entry di akhir tahun, misal akun 59 atau similar)
        // Jika belum ada akun spesifik di COA, set 0 atau ambil dari akun perkiraan pajak
        $pajak = 0;

        $shu_neto = $shu_sebelum_pajak - $pajak;

        return [
            'partisipasi_bunga'   => $partisipasi_bunga,
            'partisipasi_lain'    => $partisipasi_lain,
            'total_partisipasi'   => $total_partisipasi,

            'beban_bunga'         => $beban_bunga,
            'beban_penyisihan'    => $beban_penyisihan,
            'beban_pegawai'       => $beban_pegawai,
            'beban_admin'         => $beban_admin,
            'beban_penyusutan'    => $beban_penyusutan,
            'beban_usaha_lain'    => $beban_usaha_lain,
            'total_beban_usaha'   => $total_beban_usaha,

            'shu_bruto'           => $shu_bruto,

            'hasil_investasi'     => $hasil_investasi,
            'beban_perkoperasian' => $beban_perkoperasian,

            'pendapatan_lain'     => $pendapatan_lain,
            'beban_lain'          => $beban_lain,

            'shu_sebelum_pajak'   => $shu_sebelum_pajak,
            'pajak'               => $pajak,
            'shu_neto'            => $shu_neto,
        ];
    }
}