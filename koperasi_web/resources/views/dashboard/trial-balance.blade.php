@extends('layouts.app')

@section('page_title', 'Neraca Saldo')

@section('content')
<div class="container py-4">

    <form action="{{ route('trial-balance') }}" method="GET">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                
                <input type="text" class="form-select" id="dateRangeFilter" name="date_range" 
                       value="{{ $dateRange ?? '' }}" placeholder="Pilih Rentang Periode" style="width: 240px;">

                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
            <div class="d-flex gap-2">
                @php
                    $isDataEmpty = $balances->isEmpty();
                @endphp
                <a href="{{ $isDataEmpty ? '#' : route('trial-balance.export', request()->query()) }}" 
                   class="btn btn-primary {{ $isDataEmpty ? 'disabled' : '' }}"
                   @if($isDataEmpty) 
                       onclick="event.preventDefault(); alert('Tidak ada data untuk diekspor pada periode ini.');" 
                   @endif>
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>

    @if ($dateRange)
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Akun</th>
                        <th style="width: 40%;">Nama Akun</th>
                        <th>Debit (Rp)</th>
                        <th>Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($balances as $balance)
                        <tr>
                            <td><strong>{{ $balance->account_code }}</strong></td>
                            <td>{{ $balance->account_name }}</td>
                            <td>{{ number_format($balance->total_debit, 2) }}</td>
                            <td>{{ number_format($balance->total_credit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Tidak ada data untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                
                <tfoot class="table-group-divider fw-bold">
                    <tr>
                        <td>Total</td>
                        <td></td>
                        <td>{{ number_format($totalDebit, 2) }}</td>
                        <td>{{ number_format($totalCredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-range fs-1 text-secondary mb-3"></i>
                <h5 class="card-title mb-2">Pilih Rentang Periode</h5>
                <p class="text-muted">Silakan pilih rentang periode terlebih dahulu.</p>
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .table thead th {
        font-weight: 600;
    }
    .table tfoot td {
        font-size: 1.1rem;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d",
    });
</script>
@endpush