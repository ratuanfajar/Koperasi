@extends('layouts.app')

@section('page_title', 'Neraca Saldo')

@section('content')
<div class="container py-4">

    {{-- BAGIAN FILTER --}}
    <form action="{{ route('trial-balance') }}" method="GET" class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            
            {{-- BAGIAN KIRI: Filter Tanggal & Tombol --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Picker Tanggal (Width konsisten) --}}
                <input type="text" class="form-select" id="dateRangeFilter" name="date_range" 
                       value="{{ $dateRange ?? '' }}" placeholder="Pilih Rentang Periode" 
                       style="width: 250px;">

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>

            {{-- BAGIAN KANAN: Export CSV --}}
            <div class="d-flex gap-2">
                @php
                    $isDataEmpty = $balances->isEmpty();
                @endphp
                <a href="{{ $isDataEmpty ? '#' : route('trial-balance.export', request()->query()) }}" 
                   class="btn btn-primary {{ $isDataEmpty ? 'disabled' : '' }}"
                   @if($isDataEmpty) 
                       onclick="event.preventDefault();" 
                   @endif>
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>

    {{-- KONTEN UTAMA --}}
    @if ($dateRange)
        <div class="card shadow-sm border-0">

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size: 0.95rem;">
                    {{-- HEADER TABEL (STYLE KONSISTEN) --}}
                    <thead class="text-secondary fw-bold text-uppercase" style="font-size: 0.85rem;">
                        <tr class="text-center align-middle">
                            <th style="width: 15%;">Kode Akun</th>
                            <th style="width: 45%;">Nama Akun</th>
                            <th style="width: 20%;">Debit (Rp)</th>
                            <th style="width: 20%;">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($balances as $balance)
                            <tr>
                                <td class="text-center bg-white text-dark fw-bold">
                                    {{ $balance->account_code }}
                                </td>
                                <td class="bg-white">
                                    {{ $balance->account_name }}
                                </td>
                                <td class="text-end bg-white">
                                    {{ $balance->total_debit > 0 ? number_format($balance->total_debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end bg-white">
                                    {{ $balance->total_credit > 0 ? number_format($balance->total_credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted bg-white">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-clipboard-x fs-1 mb-2 text-secondary"></i>
                                        <p class="mb-0">Tidak ada data transaksi untuk periode yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    {{-- FOOTER TOTAL --}}
                    <tfoot class="bg-light fw-bold border-top" style="font-size: 0.95rem;">
                        <tr>
                            <td colspan="2" class="text-end text-uppercase fw-bold pe-3 py-3">Total</td>
                            <td class="text-end text-dark py-3">
                                {{ number_format($totalDebit, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-dark py-3">
                                {{ number_format($totalCredit, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="card-footer bg-light py-3 border-top">
                <small class="text-muted">Menampilkan {{ $balances->count() }} akun</small>
            </div>
        </div>
    
    @else
        {{-- EMPTY STATE (BELUM PILIH TANGGAL) --}}
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body text-center py-5">
                <div class="py-4">
                    <i class="bi bi-calendar-range fs-1 text-primary mb-3"></i>
                    <h5 class="fw-bold text-dark mb-2">Silakan Pilih Periode</h5>
                    <p class="text-muted mb-0">
                        Pilih rentang tanggal di atas untuk menampilkan neraca saldo.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table thead th {
        background-color: #f8f9fa !important;
        border-bottom-width: 2px;
    }

    .select2-container--bootstrap-5 .select2-selection {
        border-color: #dee2e6;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: ["{{ explode(' to ', $dateRange ?? '')[0] ?? '' }}", "{{ explode(' to ', $dateRange ?? '')[1] ?? '' }}"],
        locale: {
            rangeSeparator: " to "
        }
    });
</script>
@endpush