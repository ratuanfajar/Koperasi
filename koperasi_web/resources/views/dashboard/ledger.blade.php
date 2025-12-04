@extends('layouts.app')

@section('page_title', 'Jurnal Umum')

@section('content')
<div class="container py-4">
    
    {{-- FILTER TANGGAL --}}
    <form action="{{ route('ledger') }}" method="GET" class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <input type="text" class="form-select" id="dateRangeFilter" name="date_range" 
                       value="{{ $dateRange ?? '' }}" placeholder="Pilih Rentang Periode" style="width: 250px;">
                <button type="submit" class="btn btn-primary">
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

    {{-- TABEL JURNAL UMUM --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="font-size: 0.95rem;">
                <thead class="text-secondary fw-bold text-uppercase" style="font-size: 0.85rem;">
                    <tr class="text-center align-middle">
                        <th style="width: 100px;">Tanggal</th>
                        <th style="width: 160px;">No. Bukti Transaksi</th>
                        <th style="width: 35%;">Nama Akun</th>
                        <th style="width: 80px;">Pos Ref</th>
                        <th style="width: 130px;">Debit (Rp)</th>
                        <th style="width: 130px;">Kredit (Rp)</th>
                        <th style="width: 100px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group_id => $entries)
                        @php
                            $rowCount = $entries->count(); 
                        @endphp

                        {{-- Loop setiap baris dalam satu transaksi --}}
                        @foreach ($entries as $index => $entry)
                            <tr>
                                {{-- Code Loop Anda (tidak berubah karena sudah bg-white) ... --}}
                                @if ($index === 0)
                                    <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                        {{ $entry->date->format('d/m/Y') }}
                                    </td>
                                    
                                    <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                        {{ $entry->transaction_code }}
                                    </td>
                                @endif

                                <td class="position-relative">
                                    <div class="bg-white align-top">
                                        {{ $entry->account_name }}
                                    </div>
                                </td>

                                <td class="text-center text-muted font-monospace small">
                                    {{ $entry->account_code }}
                                </td>

                                <td class="text-end">
                                    {{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', '.') : '-' }}
                                </td>

                                <td class="text-end">
                                    {{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', '.') : '-' }}
                                </td>

                                @if ($index === 0)
                                    <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                        <div class="d-flex flex-column gap-2 align-items-center">
                                            @if($entry->receipt_image_path)
                                                <button type="button" class="btn btn-sm btn-outline-info w-100" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#proofModal-{{ $loop->parent->index }}"
                                                        title="Lihat Bukti Foto">
                                                    <i class="bi bi-image me-1"></i> Bukti
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            {{-- PERUBAHAN 2: bg-light diganti bg-white pada state kosong --}}
                            <td colspan="7" class="text-center py-5 text-muted bg-white">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-journal-x fs-1 mb-2 text-secondary"></i>
                                    <span>Belum ada data jurnal pada periode ini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
            <small class="text-muted">
                Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }} transaksi
            </small>
            <div>{{ $paginator->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

{{-- MODALS BUKTI TRANSAKSI --}}
@foreach ($groups as $group_id => $entries)
    @php $main_entry = $entries->first(); @endphp

    @if($main_entry->receipt_image_path)
    <div class="modal fade" id="proofModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Transaksi: {{ $main_entry->transaction_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center bg-light p-4">
                    <img src="{{ route('document.show', ['filename' => basename($main_entry->receipt_image_path)]) }}" 
                         class="img-fluid rounded shadow-sm border" 
                         style="max-height: 70vh;"
                         alt="Bukti Transaksi">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table thead th {
        background-color: #f8f9fa !important;
        border-bottom-width: 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [
            "{{ explode(' to ', $dateRange)[0] ?? '' }}", 
            "{{ explode(' to ', $dateRange)[1] ?? '' }}"
        ]
    });
</script>
@endpush