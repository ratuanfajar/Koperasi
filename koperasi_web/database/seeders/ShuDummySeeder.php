<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShuDummySeeder extends Seeder
{
    public function run()
    {
        // Bersihkan data lama agar tidak duplikat saat seeding ulang
        // DB::table('ledger_entries')->truncate(); 

        $entries = [];

        // ==========================================
        // 1. PENDAPATAN DARI ANGGOTA (PARTISIPASI)
        // ==========================================
        
        // Transaksi A: Pendapatan Bunga Pinjaman Anggota
        $this->addTransaction($entries, '2024-01-15', 'BKM-2401-001', 'Penerimaan Jasa Pinjaman Anggota Bulan Januari', 
            '101', 'Kas', 15000000, 0, 
            '401', 'Partisipasi Jasa Pinjaman', 0, 15000000 
        );

        $this->addTransaction($entries, '2024-06-15', 'BKM-2406-005', 'Penerimaan Jasa Pinjaman Anggota Bulan Juni', 
            '101', 'Kas', 20000000, 0,
            '401', 'Partisipasi Jasa Pinjaman', 0, 20000000
        );

        // Transaksi B: Pendapatan Administrasi
        $this->addTransaction($entries, '2024-02-10', 'BKM-2402-002', 'Pendapatan Administrasi Pencairan Pinjaman', 
            '101', 'Kas', 5000000, 0,
            '403', 'Partisipasi Jasa Administrasi', 0, 5000000
        );

        // ==========================================
        // 2. PENDAPATAN NON-ANGGOTA
        // ==========================================

        // Transaksi C: Bunga Deposito Bank
        $this->addTransaction($entries, '2024-12-31', 'BKM-2412-099', 'Pendapatan Bunga Deposito Bank Jabar', 
            '102', 'Giro pada Bank', 2500000, 0,
            '411', 'Pendapatan Bunga Pinjaman', 0, 2500000 
        );

        // ==========================================
        // 3. BEBAN POKOK (JASA MODAL ANGGOTA)
        // ==========================================

        // Transaksi D: Pembayaran Bunga Simpanan
        $this->addTransaction($entries, '2024-12-20', 'BKK-2412-050', 'Pembayaran Jasa Simpanan Anggota Tahun 2024', 
            '501', 'Beban Bunga Simpanan Anggota', 12000000, 0, 
            '101', 'Kas', 0, 12000000 
        );

        // ==========================================
        // 4. BEBAN USAHA (OPERASIONAL)
        // ==========================================

        // Transaksi E: Gaji Karyawan
        $this->addTransaction($entries, '2024-11-25', 'BKK-2411-010', 'Pembayaran Gaji Karyawan & Staff', 
            '524', 'Beban Gaji Karyawan', 45000000, 0,
            '101', 'Kas', 0, 45000000
        );

        // Transaksi F: Listrik & Air
        $this->addTransaction($entries, '2024-10-05', 'BKK-2410-005', 'Bayar Tagihan Listrik & WiFi Kantor', 
            '527', 'Biaya Air, Listrik dan Telepon', 8500000, 0,
            '101', 'Kas', 0, 8500000
        );

        // Transaksi G: Konsumsi Rapat
        $this->addTransaction($entries, '2024-03-15', 'BKK-2403-015', 'Biaya Konsumsi RAT Tahun Buku 2023', 
            '538', 'Beban Rapat Anggota', 7000000, 0,
            '101', 'Kas', 0, 7000000
        );

        // Transaksi H: ATK
        $this->addTransaction($entries, '2024-05-20', 'BKK-2405-020', 'Pembelian ATK Kantor', 
            '526', 'Biaya Pemakaian Perlengkapan Kantor', 1500000, 0,
            '101', 'Kas', 0, 1500000
        );

        // Transaksi I: Penyusutan (Jurnal Memorial)
        $this->addTransaction($entries, '2024-12-31', 'BM-2412-001', 'Penyusutan Inventaris Kantor Tahun 2024', 
            '535', 'Beban Penyusutan Peralatan Kantor', 2000000, 0,
            '127', 'Akumulasi penyusutan Inventaris Kantor', 0, 2000000
        );

        // ==========================================
        // 5. PENDAPATAN & BEBAN LAIN-LAIN
        // ==========================================

        // Transaksi J: Sewa
        $this->addTransaction($entries, '2024-08-01', 'BKM-2408-001', 'Pendapatan Sewa Lahan Parkir', 
            '101', 'Kas', 1000000, 0,
            '422', 'Pendapatan Sewa', 0, 1000000
        );

        // Transaksi K: Admin Bank
        $this->addTransaction($entries, '2024-12-31', 'BM-2412-002', 'Potongan Administrasi Bank', 
            '541', 'Beban atas Penyertaan (untuk KSP)', 500000, 0, 
            '102', 'Giro pada Bank', 0, 500000
        );

        // Insert Batch
        DB::table('ledger_entries')->insert($entries);
    }

    // Helper untuk membuat pasangan jurnal debit-kredit
    private function addTransaction(&$entries, $date, $code, $desc, $accDeb, $nameDeb, $debVal, $credValDeb, $accCred, $nameCred, $debValCred, $credVal)
    {
        $groupId = (string) Str::uuid();
        $timestamp = now();
        
        $dummyImage = 'uploads/dummy_receipt.jpg'; 

        // Baris Debit
        $entries[] = [
            'transaction_group_id' => $groupId,
            'transaction_code' => $code,
            'date' => $date,
            'description' => $desc,
            'account_code' => $accDeb,
            'account_name' => $nameDeb,
            'debit' => $debVal,
            'credit' => $credValDeb,
            'receipt_image_path' => $dummyImage, 
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        // Baris Kredit
        $entries[] = [
            'transaction_group_id' => $groupId,
            'transaction_code' => $code,
            'date' => $date,
            'description' => $desc,
            'account_code' => $accCred,
            'account_name' => $nameCred,
            'debit' => $debValCred,
            'credit' => $credVal,
            'receipt_image_path' => $dummyImage,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}