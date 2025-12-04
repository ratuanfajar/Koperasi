@extends('layouts.app')

@section('page_title', 'Buku Besar Umum')

@section('content')
<div class="container py-4">

    {{-- BAGIAN FILTER --}}
    <form action="{{ route('posting') }}" method="GET" class="mb-4">
        {{-- Container Flex: Kiri (Input & Filter) vs Kanan (Export) --}}
        <div class="d-flex justify-content-between align-items-center">
            
            {{-- BAGIAN KIRI: Dropdown, Tanggal, Tombol Filter --}}
            <div class="d-flex align-items-center gap-2">
                
                {{-- Dropdown Akun (Dibungkus div width tetap agar tidak kepotong) --}}
                <div style="width: 350px;">
                    <select class="form-select select2" name="account_filter" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->code }}" 
                                {{ request('account_filter') == $account->code ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Picker Tanggal (Width disamakan dengan Jurnal Umum) --}}
                <input type="text" class="form-select" id="dateRangeFilter" name="date_range" 
                       value="{{ $dateRange ?? '' }}" placeholder="Pilih Rentang Periode" 
                       style="width: 250px;">

                {{-- Tombol Filter (Ukuran compact, hapus w-100) --}}
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>

            {{-- BAGIAN KANAN: Tombol Export CSV --}}
            <div class="d-flex gap-2">
                @php 
                    $hasData = $selectedAccount && $entries->isNotEmpty();
                @endphp
                <a href="{{ $hasData ? route('posting.export', request()->query()) : '#' }}" 
                   class="btn btn-primary {{ !$hasData ? 'disabled' : '' }}"
                   @if(!$hasData) onclick="event.preventDefault();" @endif>
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>

        </div>
    </form>

    {{-- KONTEN UTAMA --}}
    @if ($selectedAccount)
        <div class="card shadow-sm border-0">
            {{-- Header Card Nama Akun --}}
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-book me-2 text-primary"></i>
                    {{ $selectedAccount->name }} <span class="text-muted fw-normal">({{ $selectedAccount->code }})</span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size: 0.95rem;">
                    {{-- HEADER TABEL --}}
                    <thead class="text-secondary fw-bold text-uppercase" style="font-size: 0.85rem;">
                        <tr class="text-center align-middle">
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 35%;">Keterangan</th>
                            <th style="width: 15%;">Debit (Rp)</th>
                            <th style="width: 15%;">Kredit (Rp)</th>
                            <th style="width: 5%;">D/K</th>
                            <th style="width: 15%;">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            @php
                                // LOGIKA SALDO BERJALAN
                                $kategori = $selectedAccount->category ?? 'Aset'; 
                                $isNormalDebit = in_array($kategori, ['Aset', 'Beban', 'Harta', 'Biaya']);
                                
                                if (!isset($runningBalance)) $runningBalance = 0;

                                if ($isNormalDebit) {
                                    $runningBalance += ($entry->debit - $entry->credit);
                                    $posisi = ($runningBalance >= 0) ? 'D' : 'K';
                                } else {
                                    $runningBalance += ($entry->credit - $entry->debit);
                                    $posisi = ($runningBalance >= 0) ? 'K' : 'D';
                                }
                            @endphp

                            <tr>
                                <td class="text-center bg-white">
                                    {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                </td>
                                
                                <td class="bg-white">
                                    {{ $entry->description ?? '-' }}
                                </td>
                                
                                <td class="text-end bg-white">
                                    {{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end bg-white">
                                    {{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', '.') : '-' }}
                                </td>
                                
                                <td class="text-center fw-bold text-secondary bg-white">
                                    {{ $posisi }}
                                </td>

                                <td class="text-end fw-bold text-dark bg-white">
                                    {{ number_format(abs($runningBalance), 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            {{-- EMPTY STATE --}}
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted bg-white">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-journal-x fs-1 mb-2 text-secondary"></i>
                                        <p class="mb-0">Belum ada transaksi untuk akun ini pada periode yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    {{-- Informasi "Menampilkan n - m dari N" --}}
                    <small class="text-muted">
                        Menampilkan {{ $entries->firstItem() ?? 0 }} - {{ $entries->lastItem() ?? 0 }} dari {{ $entries->total() }} transaksi
                    </small>

                    {{-- Tombol Navigasi Halaman (Prev, 1, 2, Next) --}}
                    <div>
                        {{ $entries->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- EMPTY STATE (BELUM PILIH AKUN) --}}
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body text-center py-5">
                <div class="py-4">
                    <i class="bi bi-search fs-1 text-primary mb-3"></i>
                    <h5 class="fw-bold text-dark mb-2">Silakan Pilih Akun</h5>
                    <p class="text-muted mb-0">
                        Pilih nama akun dan periode tanggal di atas untuk melihat mutasi buku besar.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d", 
        defaultDate: ["{{ explode(' to ', $dateRange ?? '')[0] ?? '' }}", "{{ explode(' to ', $dateRange ?? '')[1] ?? '' }}"],
        locale: {
            rangeSeparator: " to "
        }
    });

    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Akun --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush