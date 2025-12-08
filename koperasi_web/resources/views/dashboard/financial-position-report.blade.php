@extends('layouts.app')

@section('page_title', 'Laporan Posisi Keuangan')

{{-- HELPER FORMAT ANGKA --}}
@php
    if (!function_exists('nF')) {
        function nF($number) {
            if ($number == 0) return '-';
            if ($number < 0) {
                return '(' . number_format(abs($number), 0, ',', '.') . ')';
            }
            return number_format($number, 0, ',', '.');
        }
    }
@endphp

@section('content')
<div class="container py-4">
    
    {{-- BAGIAN FILTER & DOWNLOAD --}}
    <form action="{{ route('financial-position-report') }}" method="GET" class="mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center">
            
            {{-- Filter Kiri (Pilih Periode: Bulan & Tahun) --}}
            <div class="d-flex align-items-center gap-2">
                <div class="input-group" style="width: auto;">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-calendar-week"></i></span>
                    
                    {{-- Select Bulan --}}
                    <select name="month" class="form-select border-start-0 text-center" style="width: 160px;">
                        <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>Setahun Penuh</option>
                        @foreach(range(1, 12) as $m)
                            @php 
                                // FIX: Tambahkan ->locale('id') sebelum translatedFormat
                                $mName = \Carbon\Carbon::createFromDate(null, $m, 1)
                                    ->locale('id') 
                                    ->translatedFormat('F'); 
                            @endphp
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>

                    <span class="input-group-text bg-light text-secondary small px-3"></span>
                    
                    {{-- Select Tahun --}}
                    <select name="year" class="form-select text-center cursor-pointer" style="width: 160px;">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Tampilkan
                </button>
            </div>

            {{-- Tombol Kanan --}}
            <div class="d-flex gap-2">
                <a href="{{ route('financial-position-report.export', ['year' => $selectedYear, 'month' => $selectedMonth]) }}" class="btn btn-primary text-white">
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>
    
    {{-- TABEL UTAMA --}}
    <div class="card shadow-sm border-0">

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="font-size: 0.95rem;">
                
                {{-- HEADER TABEL --}}
                <thead class="fw-bold text-uppercase" style="font-size: 1rem;">
                    <tr class="align-middle text-center">
                        <th class="text-start ps-4 py-3" style="width: 60%">Uraian</th>
                        {{-- Kolom Periode Dinamis --}}
                        <th class="py-3" style="width: 40%;">{{ $periodLabel }}</th>
                    </tr>
                </thead>

                <tbody class="text-dark">
                    
                    {{-- 1. ASET --}}
                    <tr>
                        <td colspan="2" class="fw-bold bg-light ps-3 py-2 text-uppercase" style="font-size: 1rem; letter-spacing: 0.5px;">ASET</td>
                    </tr>
                    
                    {{-- Loop Item Aset --}}
                    @php
                        $asetItems = [
                            'kas_setara_kas' => 'Kas dan setara kas',
                            'piutang_bunga' => 'Piutang bunga',
                            'pinjaman_anggota' => 'Pinjaman anggota',
                            'penyisihan_pinjaman' => 'Penyisihan pinjaman',
                            'pinjaman_koperasi_lain' => 'Pinjaman koperasi lain',
                            'aset_tetap' => 'Aset tetap',
                            'akumulasi_penyusutan' => 'Akumulasi penyusutan',
                            'aset_tak_berwujud' => 'Aset tak berwujud',
                            'akumulasi_amortisasi' => 'Akumulasi amortisasi',
                            'aset_lain' => 'Aset lain'
                        ];
                    @endphp

                    @foreach($asetItems as $key => $label)
                        <tr>
                            <td class="bg-white ps-4 border-bottom-0">{{ $label }}</td> 
                            <td class="bg-white text-end pe-4 border-bottom-0">
                                {{-- Logic khusus: Akun kontra (negatif) tampil dalam kurung --}}
                                @if(in_array($key, ['penyisihan_pinjaman', 'akumulasi_penyusutan', 'akumulasi_amortisasi']))
                                    {{-- Data dari DB biasanya positif (kredit), kita negatifkan dulu agar nF memformatnya jadi kurung --}}
                                    {{ nF(($reportData[$key] ?? 0) * -1) }}
                                @else
                                    {{ nF($reportData[$key] ?? 0) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top text-dark">Total Aset</td>
                        <td class="text-end pe-4 fw-bold border-top text-dark">{{ nF($reportData['total_aset'] ?? 0) }}</td>
                    </tr>


                    {{-- 2. LIABILITAS --}}
                    <tr>
                        <td colspan="2" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">LIABILITAS</td>
                    </tr>

                    @php
                        $liabilitasItems = [
                            'utang_bunga' => 'Utang bunga',
                            'simpanan_anggota' => 'Simpanan anggota',
                            'simpanan_koperasi_lain' => 'Simpanan koperasi lain',
                            'utang_pinjaman' => 'Utang pinjaman',
                            'liabilitas_imbalan_kerja' => 'Liabilitas imbalan kerja',
                            'liabilitas_lain' => 'Liabilitas lain'
                        ];
                    @endphp

                    @foreach($liabilitasItems as $key => $label)
                        <tr>
                            <td class="bg-white ps-4 border-bottom-0">{{ $label }}</td>
                            <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData[$key] ?? 0) }}</td>
                        </tr>
                    @endforeach
                    
                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top text-dark">Total Liabilitas</td>
                        <td class="text-end pe-4 fw-bold border-top text-dark">{{ nF($reportData['total_liabilitas'] ?? 0) }}</td>
                    </tr>


                    {{-- 3. EKUITAS --}}
                    <tr>
                        <td colspan="2" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">EKUITAS</td>
                    </tr>

                    @php
                        $ekuitasItems = [
                            'ekuitas_simpanan_pokok' => 'Simpanan Pokok',
                            'ekuitas_simpanan_wajib' => 'Simpanan Wajib',
                            'ekuitas_simpanan_lain' => 'Simpanan Lain/Khusus',
                            'ekuitas_sumbangan' => 'Modal Sumbangan',
                            'ekuitas_cadangan' => 'Cadangan',
                            'ekuitas_shu_berjalan' => 'SHU Tahun Berjalan'
                        ];
                    @endphp

                    @foreach($ekuitasItems as $key => $label)
                        <tr>
                            <td class="bg-white ps-4 border-bottom-0">{{ $label }}</td>
                            <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData[$key] ?? 0) }}</td>
                        </tr>
                    @endforeach

                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top text-dark">Total Ekuitas</td>
                        <td class="text-end pe-4 fw-bold border-top text-dark">{{ nF($reportData['total_ekuitas'] ?? 0) }}</td>
                    </tr>


                    {{-- 4. GRAND TOTAL (LIABILITAS + EKUITAS) --}}
                    <tr class="fw-bold bg-white text-dark" style="solid #333; border-bottom: 4px #333;">
                        <td class="fw-bold bg-light ps-3 py-3 text-uppercase mt-2" style="font-size: 1rem;;">TOTAL LIABILITAS DAN EKUITAS</td>
                        <td class="text-end border-start py-3 pe-4 fw-bold bg-light" style="font-size: 1rem;">
                            {{ nF($reportData['total_liabilitas_ekuitas'] ?? 0) }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    body {
        background-color: #f8fafc !important; 
    }
    
    .table thead th {
        background-color: #EDEDED !important;
        border-bottom-width: 2px;
    }
    
    .input-group-text { background-color: #f8f9fa; border-color: #dee2e6; }
    .form-select { border-color: #dee2e6; cursor: pointer; }
    .focus-ring-none:focus { box-shadow: none; border-color: #86b7fe; }

    .table tr td { vertical-align: middle; }
    .ps-3 { padding-left: 1rem !important; }
    .ps-4 { padding-left: 3rem !important; }
</style>
@endpush