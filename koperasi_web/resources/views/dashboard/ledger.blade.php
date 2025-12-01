@extends('layouts.app')

@section('page_title', 'Buku Besar Akun')

@section('content')
<div class="container py-4">
    <form action="{{ route('ledger') }}" method="GET">
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
                    $isDataEmpty = $paginator->isEmpty();
                @endphp
                <a href="{{ $isDataEmpty ? '#' : route('ledger.export', request()->query()) }}" 
                class="btn btn-primary {{ $isDataEmpty ? 'disabled' : '' }}"
                @if($isDataEmpty) 
                    onclick="event.preventDefault(); alert('Tidak ada data untuk diekspor.');" 
                @endif>
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>

    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th style="white-space: nowrap;">Kode Transaksi</th>
                    <th style="width: 30%;">Keterangan</th> 
                    <th>Akun</th>
                    <th>Debit (Rp)</th>
                    <th>Kredit (Rp)</th>
                    <th>Detail</th> 
                </tr>
            </thead>
            
            <tbody>
                @forelse ($groups as $group_id => $entries_in_group)
                    
                    @php
                        $debit_entry = $entries_in_group->where('debit', '>', 0)->first();
                        $credit_entry = $entries_in_group->where('credit', '>', 0)->first();
                        $main_entry = $debit_entry ?? $entries_in_group->first();
                    @endphp

                    <tr class="transaction-row-top">
                        <td rowspan="2" class="transaction-group-cell">
                            {{ \Carbon\Carbon::parse($main_entry->date)->format('d-M-Y') }}
                        </td>
                        <td rowspan="2" class="transaction-group-cell">
                            {{ $main_entry->transaction_code }}
                        </td>
                        <td rowspan="2" class="transaction-group-cell">
                            {{ $main_entry->description }}
                        </td>
                        <td>
                            @if($debit_entry)
                                {{ $debit_entry->account_code }} - {{ $debit_entry->account_name }}
                            @endif
                        </td>
                        <td>{{ $debit_entry ? number_format($debit_entry->debit, 2) : '-' }}</td>
                        <td>-</td>
                        <td rowspan="2" class="transaction-group-cell text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#proofModal-{{ $main_entry->id }}">
                                    Lihat Bukti
                                </button>
                                <button type="button" class="btn btn-outline-secondary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#itemsModal-{{ $main_entry->id }}">
                                    Lihat Item
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="transaction-row-bottom">
                        <td>
                            @if($credit_entry)
                                {{ $credit_entry->account_code }} - {{ $credit_entry->account_name }}
                            @endif
                        </td>
                        <td>-</td>
                        <td>{{ $credit_entry ? number_format($credit_entry->credit, 2) : '-' }}</td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Tidak ada transaksi ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} transactions
        </div>
        {{ $paginator->appends(request()->query())->links() }}
    </div>
</div>

@foreach ($groups as $group_id => $entries_in_group)
    @php
        $debit_entry = $entries_in_group->where('debit', '>', 0)->first();
        $main_entry = $debit_entry ?? $entries_in_group->first();
    @endphp

    <div class="modal fade" id="proofModal-{{ $main_entry->id }}" tabindex="-1" aria-labelledby="proofModalLabel-{{ $main_entry->id }}" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="proofModalLabel-{{ $main_entry->id }}">Bukti Transaksi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <img src="{{ route('document.show', ['filename' => basename($main_entry->receipt_image_path)]) }}" alt="Receipt" class="img-fluid w-100">
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="itemsModal-{{ $main_entry->id }}" tabindex="-1" aria-labelledby="itemsModalLabel-{{ $main_entry->id }}" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="itemsModalLabel-{{ $main_entry->id }}">Detail Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body"> 
            
            @if ($debit_entry && $debit_entry->items->count() > 0)
              <table class="table">
                <thead>
                    <tr><th>Nama Item</th><th>Harga</th><th>Kuantitas</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    @foreach ($debit_entry->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
              </table>
            @else
              <div class="alert alert-info text-center" role="alert">
                Tidak ada item untuk transaksi ini.
              </div>
            @endif

          </div>
        </div>
      </div>
    </div>
@endforeach

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table > :not(caption) > * > * {
        border-bottom-width: 0 !important;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table thead th {
        font-weight: 600;
        border-bottom-width: 2px !important; 
    }
    .pagination { margin-bottom: 0; }
    .table > tbody > tr,
    .table > tbody > tr > td {
        border: none !important;
    }
    .table tbody tr td {
        border-top: none !important;
        border-bottom: none !important;
    }
    .transaction-group-cell {
        vertical-align: top !important;
        padding-top: 1rem !important;
    }
    .transaction-row-top > td {
        padding-bottom: 0.25rem;
    }
    .transaction-row-bottom > td {
        padding-top: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Inisialisasi flatpickr dalam mode "range"
    flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d", // Format yang akan dikirim ke Laravel
    });
</script>
@endpush