@extends('layouts.app')

@section('page_title', 'Laporan Posisi Keuangan')

@php
    if (!function_exists('nF')) {
        function nF($number) {
            // 1. Jika angka 0, tampilkan strip "-"
            if ($number == 0) return '-';
            // 2. Jika angka NEGATIF, gunakan format kurung (X.XXX)
            if ($number < 0) {
                return '(' . number_format(abs($number), 0, ',', '.') . ')';
            }
            // 3. Jika angka POSITIF, format biasa X.XXX
            return number_format($number, 0, ',', '.');
        }
    }
@endphp

@section('content')
<div class="container py-4">
    
    {{-- BAGIAN FILTER & DOWNLOAD --}}
    <form action="{{ route('financial-position-report') }}" method="GET" class="mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center">
            
            {{-- Filter Kiri --}}
            <div class="d-flex align-items-center gap-2">
                <div class="input-group" style="width: 320px;">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-calendar-event"></i></span>
                    
                    {{-- Select Tahun Awal --}}
                    <select name="start_year" class="form-select border-start-0 ps-0 text-center focus-ring-none">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $startYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    
                    <span class="input-group-text bg-light text-secondary small px-3">s/d</span>
                    
                    {{-- Select Tahun Akhir --}}
                    <select name="end_year" class="form-select text-center cursor-pointer">
                        @for($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ request('end_year', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Tampilkan
                </button>
                
                <small class="text-muted ms-2 fst-italic d-flex align-items-center">
                    <i class="bi bi-info-circle me-1"></i> Maks. 3 Tahun
                </small>
            </div>

            {{-- Tombol Kanan --}}
            <div class="d-flex gap-2">
                <a href="{{ route('financial-position-report.export', ['start_year' => $startYear, 'end_year' => $endYear]) }}" class="btn btn-primary text-white">
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>

    {{-- TABEL UTAMA --}}
    <div class="card shadow-sm border-0">

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="font-size: 0.95rem;">
                
                {{-- HEADER TABEL (Gaya Konsisten #EDEDED) --}}
                <thead class="fw-bold text-uppercase" style="font-size: 1rem;">
                    <tr class="align-middle text-center">
                        <th class="text-start ps-4 py-3" style="width: 50%">Uraian</th>
                        @foreach($years as $year)
                            <th class="py-3" style="width: 150px;">{{ $year }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="text-dark">
                    
                    {{-- 1. ASET --}}
                    <tr>
                        <td colspan="{{ count($years) + 1 }}" class="fw-bold bg-light ps-3 py-2 text-uppercase" style="font-size: 1rem; letter-spacing: 0.5px;">ASET</td>
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
                            @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y][$key] ?? 0) }}</td> @endforeach
                        </tr>
                    @endforeach

                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top text-dark">Total Aset</td>
                        @foreach($years as $y) <td class="text-end pe-4 fw-bold border-top text-dark">{{ nF($report[$y]['total_aset'] ?? 0) }}</td> @endforeach
                    </tr>


                    {{-- 2. LIABILITAS --}}
                    <tr>
                        <td colspan="{{ count($years) + 1 }}" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">LIABILITAS</td>
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
                            @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y][$key] ?? 0) }}</td> @endforeach
                        </tr>
                    @endforeach
                    
                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top text-dark">Total Liabilitas</td>
                        @foreach($years as $y) <td class="text-end pe-4 fw-bold border-top text-dark">{{ nF($report[$y]['total_liabilitas'] ?? 0) }}</td> @endforeach
                    </tr>


                    {{-- 3. EKUITAS --}}
                    <tr>
                        <td colspan="{{ count($years) + 1 }}" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">EKUITAS</td>
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
                            @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y][$key] ?? 0) }}</td> @endforeach
                        </tr>
                    @endforeach

                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top text-dark">Total Ekuitas</td>
                        @foreach($years as $y) <td class="text-end pe-4 fw-bold border-top text-dark">{{ nF($report[$y]['total_ekuitas'] ?? 0) }}</td> @endforeach
                    </tr>


                    {{-- 4. GRAND TOTAL (LIABILITAS + EKUITAS) --}}
                    <tr class="fw-bold bg-white text-dark" style="solid #333; border-bottom: 4px #333;">
                        <td class="fw-bold bg-light ps-3 py-3 text-uppercase mt-2" style="font-size: 1rem;;">TOTAL LIABILITAS DAN EKUITAS</td>
                        @foreach($years as $y) <td class="text-end border-start py-3 pe-4 fw-bold bg-light" style="font-size: 1rem;">{{ nF($report[$y]['total_liabilitas_ekuitas'] ?? 0) }}</td> @endforeach
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
@endpush