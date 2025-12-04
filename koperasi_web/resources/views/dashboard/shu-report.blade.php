@extends('layouts.app')

@section('page_title', 'Laporan Sisa Hasil Usaha')

@php
    if (!function_exists('nF')) {
        function nF($number) {
            if ($number == 0) return '-';
            return number_format($number, 0, ',', '.');
        }
    }
@endphp

@section('content')
<div class="container py-4">
    
    {{-- BAGIAN FILTER --}}
    <form action="{{ route('shu-report') }}" method="GET" class="mb-4 no-print">
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
                <a href="{{ route('shu-report.export', ['start_year' => $startYear, 'end_year' => $endYear]) }}" class="btn btn-primary text-white">
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
                <thead class="fw-bold text-uppercase" style="font-size: 0.85rem;">
                    <tr class="text-center align-middle">
                        <th class="text-start ps-4 py-3" style="width: 50%">Uraian</th>
                        @foreach($years as $year)
                            <th class="py-3" style="width: 150px;">{{ $year }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="text-dark">
                    
                    {{-- 1. PARTISIPASI ANGGOTA --}}
                    <tr>
                        <td colspan="{{ count($years) + 1 }}" class="fw-bold bg-light ps-3 py-2 text-uppercase" style="font-size: 1rem; letter-spacing: 0.5px;">
                            Partisipasi Anggota
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Pendapatan Bunga</td> 
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['partisipasi_bunga']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Pendapatan Usaha Lain</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['partisipasi_lain']) }}</td> @endforeach
                    </tr>
                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top">Jumlah Partisipasi Anggota</td>
                        @foreach($years as $y) <td class="text-end pe-4 fw-bold border-top">{{ nF($report[$y]['total_partisipasi']) }}</td> @endforeach
                    </tr>

                    {{-- 2. BEBAN USAHA --}}
                    <tr>
                        <td colspan="{{ count($years) + 1 }}" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">
                            Beban Usaha
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Bunga</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_bunga']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Penyisihan</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_penyisihan']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Kepegawaian</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_pegawai']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Administrasi dan Umum</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_admin']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Penyusutan dan Amortisasi</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_penyusutan']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Usaha Lain</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_usaha_lain']) }}</td> @endforeach
                    </tr>
                    
                    {{-- Total Beban --}}
                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top">Jumlah Beban Usaha</td>
                        {{-- Logika: Jika total beban positif, tampilkan dalam kurung --}}
                        @foreach($years as $y) 
                            <td class="text-end pe-4 fw-bold border-top">
                                {{ nF($report[$y]['total_beban_usaha'] * -1) }}
                            </td> 
                        @endforeach
                    </tr>

                    {{-- 3. SHU BRUTO --}}
                    <tr>
                        <td class="ps-3 py-3 fw-bold text-uppercase text-dark bg-white border-top border-bottom border-2">
                            Sisa Hasil Usaha Bruto
                        </td>
                        @foreach($years as $y) 
                            <td class="text-end pe-4 py-3 fw-bold text-dark bg-white border-top border-bottom border-2 fs-6">
                                {{ nF($report[$y]['shu_bruto']) }}
                            </td> 
                        @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Hasil Investasi</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['hasil_investasi']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Perkoperasian</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_perkoperasian']) }}</td> @endforeach
                    </tr>

                    {{-- 4. HASIL INVESTASI & PERKOPERASIAN --}}
                    <tr>
                        <td colspan="{{ count($years) + 1 }}" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">
                            Pendapatan & Beban Lain
                        </td>
                    </tr>

                    {{-- 5. LAIN-LAIN --}}
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Pendapatan Lain</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['pendapatan_lain']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Lain</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['beban_lain']) }}</td> @endforeach
                    </tr>

                    {{-- 6. SHU SEBELUM PAJAK --}}
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Sisa Hasil Usaha Sebelum Pajak</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['shu_sebelum_pajak']) }}</td> @endforeach
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Pajak Penghasilan</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($report[$y]['pajak']) }}</td> @endforeach
                    </tr>
                    
                    {{-- 7. SHU NETO (FINAL) --}}
                    <tr class="bg-light border-top border-2">
                        <td class="text-uppercase fw-bold ps-3 py-3 fs-6">Sisa Hasil Usaha Neto</td>
                        @foreach($years as $y) 
                            <td class="text-end fw-bold pe-4 py-3 fs-6">
                                {{ nF($report[$y]['shu_neto']) }}
                            </td> 
                        @endforeach
                    </tr>

                    {{-- 8. PENGHASILAN KOMPREHENSIF --}}
                     <tr>
                        <td class="bg-white ps-4 border-bottom-0 text-muted">Penghasilan Komprehensif Lain</td>
                        @foreach($years as $y) <td class="bg-white text-end pe-4 text-muted border-bottom-0">-</td> @endforeach
                    </tr>
                     <tr class="border-top">
                        <td class="ps-3 text-uppercase fw-bold text-dark py-3 fs-6">Penghasilan Komprehensif</td>
                        @foreach($years as $y) <td class="text-end pe-4 fw-bold text-dark py-3 fs-6">{{ nF($report[$y]['shu_neto']) }}</td> @endforeach
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table thead th {
        background-color: #EDEDED !important;
        border-bottom-width: 2px;
    }
    
    .input-group-text { background-color: #fff; border-color: #dee2e6; }
    .form-select { border-color: #dee2e6; }
    .focus-ring-none:focus { box-shadow: none; border-color: #86b7fe; }

    .table tr td { vertical-align: middle; }
</style>
@endpush