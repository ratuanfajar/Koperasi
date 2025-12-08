@extends('layouts.app')

@section('page_title', 'Jurnal Umum')

@section('content')
<div class="container py-4">
    
    {{-- FILTER TANGGAL & BUTTON EXPORT --}}
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
                @php $isDataEmpty = $paginator->isEmpty(); @endphp
                <a href="{{ $isDataEmpty ? '#' : route('ledger.export', request()->query()) }}" 
                   class="btn btn-primary {{ $isDataEmpty ? 'disabled' : '' }}"
                   @if($isDataEmpty) onclick="event.preventDefault(); alert('Tidak ada data.');" @endif>
                    <i class="bi bi-download me-1"></i> Unduh CSV
                </a>
            </div>
        </div>
    </form>

    {{-- TABEL DATA --}}
    <div class="card shadow-sm border-0">
        {{-- style="overflow: visible;" agar dropdown menu tidak terpotong --}}
        <div class="table-responsive" style="overflow: visible;">
            <table class="table table-bordered align-middle mb-0" style="font-size: 0.95rem;">
                <thead class="bg-light text-secondary fw-bold text-uppercase" style="font-size: 0.85rem;">
                    <tr class="text-center align-middle">
                        <th style="width: 100px;">Tanggal</th>
                        <th style="width: 160px;">No. Bukti</th>
                        <th style="width: 35%;">Nama Akun</th>
                        <th style="width: 80px;">Pos Ref</th>
                        <th style="width: 130px;">Debit (Rp)</th>
                        <th style="width: 130px;">Kredit (Rp)</th>
                        <th style="width: 80px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalDebit = 0; $totalCredit = 0; @endphp

                    @forelse ($groups as $group_id => $entries)
                        @php $rowCount = $entries->count(); @endphp

                        @foreach ($entries as $index => $entry)
                            @php
                                $totalDebit += $entry->debit;
                                $totalCredit += $entry->credit;
                            @endphp

                            <tr>
                                {{-- TANGGAL & NO BUKTI (Rowspan) --}}
                                @if ($index === 0)
                                    <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                        {{ $entry->date->format('d/m/Y') }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                        {{ $entry->transaction_code }}
                                    </td>
                                @endif

                                {{-- AKUN & NILAI --}}
                                <td class="position-relative {{ $entry->credit > 0 ? 'text-end pe-4' : 'text-start' }}">
                                    {{ $entry->account_name }}
                                </td>
                                <td class="text-center text-muted small">{{ $entry->account_code }}</td>
                                <td class="text-end">{{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', '.') : '-' }}</td>

                                {{-- TOMBOL AKSI (Rowspan) --}}
                                @if ($index === 0)
                                    <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Menu
                                            </button>
                                            <ul class="dropdown-menu shadow-sm">
                                                {{-- 1. LIHAT BUKTI --}}
                                                @if($entry->receipt_image_path)
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#proofModal-{{ $loop->parent->index }}">
                                                            <i class="bi bi-image me-2 text-info"></i> Lihat Bukti
                                                        </button>
                                                    </li>
                                                @endif

                                                {{-- 2. EDIT (TRIGGER MODAL) --}}
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $loop->parent->index }}">
                                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Ubah
                                                    </button>
                                                </li>

                                                <li><hr class="dropdown-divider"></li>

                                                {{-- 3. HAPUS (TRIGGER MODAL MERAH) --}}
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal-{{ $loop->parent->index }}">
                                                        <i class="bi bi-trash me-2"></i> Hapus
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted bg-white">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-journal-x fs-1 mb-2 text-secondary"></i>
                                    <p class="mb-0">Tidak ada data transaksi pada periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                
                {{-- FOOTER TOTAL SELALU MUNCUL --}}
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end py-3 pe-3">TOTAL</td>
                        <td class="text-end py-3">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="text-end py-3">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
            <small class="text-muted">Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }}</small>
            <div>{{ $paginator->appends(request()->query())->links() }}</div>
        </div>
    </div>
</div>

{{-- MODALS SECTION --}}
@foreach ($groups as $group_id => $entries)
    @php $main_entry = $entries->first(); @endphp

    {{-- 1. MODAL BUKTI --}}
    @if($main_entry->receipt_image_path)
    <div class="modal fade" id="proofModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti: {{ $main_entry->transaction_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="{{ route('document.show', ['filename' => basename($main_entry->receipt_image_path)]) }}" class="img-fluid rounded border" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. MODAL EDIT --}}
    <div class="modal fade" id="editModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="updateForm-{{ $loop->index }}" action="{{ route('ledger.update', $main_entry->transaction_code) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold">Edit Transaksi: {{ $main_entry->transaction_code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ $main_entry->date->format('Y-m-d') }}" required>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="text-center bg-light">
                                    <tr><th>Akun</th><th>Debit</th><th>Kredit</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($entries as $k => $item)
                                    <tr>
                                        <td>
                                            <select name="items[{{ $k }}][account_info]" class="form-select form-select-sm" required>
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->code }}|{{ $acc->name }}" {{ $item->account_code == $acc->code ? 'selected' : '' }}>
                                                        {{ $acc->code }} - {{ $acc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[{{ $k }}][debit]" class="form-control form-control-sm text-end" value="{{ $item->debit }}"></td>
                                        <td><input type="number" name="items[{{ $k }}][credit]" class="form-control form-control-sm text-end" value="{{ $item->credit }}"></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" 
                                onclick="swapModal('editModal-{{ $loop->index }}', 'saveConfirmModal-{{ $loop->index }}')">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. MODAL KONFIRMASI DELETE (ICON MERAH) --}}
    <div class="modal fade" id="deleteModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content modal-content-clean p-3">
                <div class="modal-body text-center">
                    
                    {{-- ICON DELETE DISINI --}}
                    <div class="icon-circle-danger mx-auto mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
                    
                    <h5 class="fw-bold mb-2">Hapus Transaksi?</h5>
                    <p class="text-muted small mb-4">
                        Data transaksi <strong>{{ $main_entry->transaction_code }}</strong> akan dihapus secara permanen.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <form action="{{ route('ledger.destroy', ['transaction_code' => $main_entry->transaction_code]) }}" method="POST" class="w-50">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100 fw-bold" style="border-radius: 8px;">Hapus</button>
                        </form>
                        <button type="button" class="btn btn-clean-light w-50" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MODAL KONFIRMASI SAVE (ICON BIRU) --}}
    <div class="modal fade" id="saveConfirmModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content modal-content-clean p-3">
                <div class="modal-body text-center">
                    
                    {{-- ICON SAVE DISINI --}}
                    <div class="icon-circle-primary mx-auto mb-3">
                        <i class="bi bi-question-lg fs-2 fw-bold"></i>
                    </div>
                    
                    <h5 class="fw-bold mb-2">Simpan Perubahan?</h5>
                    <p class="text-muted small mb-4">
                        Apakah Anda yakin data yang dimasukkan sudah benar?
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-clean-light w-50" 
                                onclick="swapModal('saveConfirmModal-{{ $loop->index }}', 'editModal-{{ $loop->index }}')" 
                                style="border-radius: 8px;">
                            Batal
                        </button>
                        <button type="button" class="btn btn-primary w-50 fw-bold" onclick="document.getElementById('updateForm-{{ $loop->index }}').submit()" style="border-radius: 8px;">
                            Ya, Konfirmasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endforeach
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .modal-content-clean { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }

    .icon-circle-danger { 
        background-color: #fef2f2; 
        color: #ef4444; 
        width: 70px; 
        height: 70px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
    }

    .icon-circle-primary { 
        background-color: #eff6ff; 
        color: #3b82f6; 
        width: 70px; 
        height: 70px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
    }

    .btn-clean-light { background-color: #fff; border: 1px solid #e5e7eb; color: #374151; font-weight: 600; }
    .btn-clean-light:hover { background-color: #f9fafb; color: #111827; }
    .table thead th { background-color: #f8f9fa !important; border-bottom-width: 2px; }
    .dropdown-menu { z-index: 1050; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#dateRangeFilter", {
        mode: "range", dateFormat: "Y-m-d",
        defaultDate: ["{{ explode(' to ', $dateRange)[0] ?? '' }}", "{{ explode(' to ', $dateRange)[1] ?? '' }}"]
    });
    
    function swapModal(hideId, showId) {
        const hideEl = document.getElementById(hideId);
        const showEl = document.getElementById(showId);
        const hideModal = bootstrap.Modal.getOrCreateInstance(hideEl);
        const showModal = bootstrap.Modal.getOrCreateInstance(showEl);

        hideModal.hide();
        hideEl.addEventListener('hidden.bs.modal', function () {
            showModal.show();
        }, { once: true });
    }
</script>
@endpush