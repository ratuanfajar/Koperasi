@extends('layouts.app')

@section('page_title', 'Posting Jurnal')

@section('content')
<div class="container py-4">
    <form action="{{ route('posting') }}" method="GET">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                
                <select class="form-select" name="account_filter" style="width: 250px;">
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->account_code }}"
                                {{ request('account_filter') == $account->account_code ? 'selected' : '' }}>
                            {{ $account->account_code }} - {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>

                <input type="text" class="form-select" id="dateRangeFilter" name="date_range" 
                       value="{{ $dateRange ?? '' }}" placeholder="Pilih Rentang Periode" style="width: 240px;">

                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
            
            <div class="d-flex gap-2">
                @php
                    $isDataEmpty = empty($entries);
                @endphp
                <a href="{{ $isDataEmpty ? '#' : route('posting.export', request()->query()) }}" 
                   class="btn btn-primary {{ $isDataEmpty ? 'disabled' : '' }}"
                   @if($isDataEmpty) 
                       onclick="event.preventDefault(); alert('Tidak ada data untuk diekspor. Silakan filter akun terlebih dahulu.');" 
                   @endif>
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>
    
    @if (!empty($entries))
        
        <h4 class="mb-3">
            {{ $selectedAccount->account_name }} Ledger - {{ $selectedAccount->account_code }}
        </h4>

        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th style="white-space: nowrap;">Kode Transaksi</th>
                        <th>Keterangan</th>
                        <th>Debit (Rp)</th>
                        <th>Kredit (Rp)</th>
                        <th>Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($entry->date)->format('d-M-Y') }}</td>
                            <td>{{ $entry->transaction_code }}</td>
                            <td class="text-muted">{{ $entry->description }}</td>
                            <td>{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                            <td>{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                            <td class="fw-bold">
                                {{ number_format($entry->balance, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-filter-circle fs-1 text-secondary mb-3"></i>
                <h5 class="card-title mb-2">Pilih Akun</h5>
                <p class="text-muted">Silakan pilih akun dari daftar di atas untuk menampilkan informasi akun.</p>
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