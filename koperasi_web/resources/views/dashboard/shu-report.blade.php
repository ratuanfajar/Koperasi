@extends('layouts.app')

@section('page_title', 'Laporan Sisa Hasil Usaha')

{{-- HELPER FORMAT ANGKA (Tetap sama, Negatif pakai Kurung) --}}
@php
    if (!function_exists('nF')) {
        function nF($number) {
            if ($number == 0) return '-';
            if ($number < 0) return '(' . number_format(abs($number), 0, ',', '.') . ')';
            return number_format($number, 0, ',', '.');
        }
    }
@endphp

@section('content')
<div class="container py-4">
    
    {{-- BAGIAN FILTER --}}
    <form action="{{ route('shu-report') }}" method="GET" class="mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center">
            
            {{-- Filter Kiri (Pilih Periode) --}}
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
                <a href="{{ route('shu-report.export', ['year' => $selectedYear, 'month' => $selectedMonth]) }}" class="btn btn-primary text-white">
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
                    <tr class="text-center align-middle">
                        <th class="text-start ps-4 py-3" style="width: 60%">Uraian</th>
                        {{-- Kolom Periode Dinamis --}}
                        <th class="py-3" style="width: 40%;">{{ $periodLabel }}</th>
                    </tr>
                </thead>

                <tbody class="text-dark">
                    
                    {{-- 1. PARTISIPASI ANGGOTA --}}
                    <tr>
                        <td colspan="2" class="fw-bold bg-light ps-3 py-2 text-uppercase" style="font-size: 1rem; letter-spacing: 0.5px;">
                            Partisipasi Anggota
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Pendapatan Bunga</td> 
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['partisipasi_bunga']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Pendapatan Usaha Lain</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['partisipasi_lain']) }}</td>
                    </tr>
                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top">Jumlah Partisipasi Anggota</td>
                        <td class="text-end pe-4 fw-bold border-top">{{ nF($reportData['total_partisipasi']) }}</td>
                    </tr>

                    {{-- 2. BEBAN USAHA --}}
                    <tr>
                        <td colspan="2" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">
                            Beban Usaha
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Bunga</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_bunga']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Penyisihan</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_penyisihan']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Kepegawaian</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_pegawai']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Administrasi dan Umum</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_admin']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Penyusutan dan Amortisasi</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_penyusutan']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Usaha Lain</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_usaha_lain']) }}</td>
                    </tr>
                    
                    {{-- Total Beban --}}
                    <tr style="background-color: #fafafa;">
                        <td class="ps-3 fw-bold border-top">Jumlah Beban Usaha</td>
                        <td class="text-end pe-4 fw-bold border-top">
                            {{-- Convert to negative for display logic if needed, otherwise just display --}}
                            {{ nF($reportData['total_beban_usaha'] * -1) }}
                        </td> 
                    </tr>

                    {{-- 3. SHU BRUTO --}}
                    <tr>
                        <td class="ps-3 py-3 fw-bold text-uppercase text-dark bg-white border-top border-bottom border-2">
                            Sisa Hasil Usaha Bruto
                        </td>
                        <td class="text-end pe-4 py-3 fw-bold text-dark bg-white border-top border-bottom border-2 fs-6">
                            {{ nF($reportData['shu_bruto']) }}
                        </td> 
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Hasil Investasi</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['hasil_investasi']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Perkoperasian</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_perkoperasian']) }}</td>
                    </tr>

                    {{-- 4. PENDAPATAN & BEBAN LAIN --}}
                    <tr>
                        <td colspan="2" class="fw-bold bg-light ps-3 py-2 text-uppercase mt-2" style="font-size: 1rem; letter-spacing: 0.5px;">
                            Pendapatan & Beban Lain
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Pendapatan Lain</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['pendapatan_lain']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Lain</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['beban_lain']) }}</td>
                    </tr>

                    {{-- 6. SHU SEBELUM PAJAK --}}
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Sisa Hasil Usaha Sebelum Pajak</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['shu_sebelum_pajak']) }}</td>
                    </tr>
                    <tr>
                        <td class="bg-white ps-4 border-bottom-0">Beban Pajak Penghasilan</td>
                        <td class="bg-white text-end pe-4 border-bottom-0">{{ nF($reportData['pajak']) }}</td>
                    </tr>
                    
                    {{-- 7. SHU NETO (FINAL) --}}
                    <tr class="bg-light border-top border-2">
                        <td class="text-uppercase fw-bold ps-3 py-3 fs-6">Sisa Hasil Usaha Neto</td>
                        <td class="text-end fw-bold pe-4 py-3 fs-6">
                            {{ nF($reportData['shu_neto']) }}
                        </td> 
                    </tr>

                    {{-- 8. PENGHASILAN KOMPREHENSIF --}}
                     <tr>
                        <td class="bg-white ps-4 border-bottom-0 text-muted">Penghasilan Komprehensif Lain</td>
                        <td class="bg-white text-end pe-4 text-muted border-bottom-0">-</td>
                    </tr>
                     <tr class="border-top">
                        <td class="ps-3 text-uppercase fw-bold text-dark py-3 fs-6">Penghasilan Komprehensif</td>
                        <td class="text-end pe-4 fw-bold text-dark py-3 fs-6">{{ nF($reportData['shu_neto']) }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    body { background-color: #f8fafc !important; }

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