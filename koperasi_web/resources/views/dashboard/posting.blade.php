@extends('layouts.app')

@section('page_title', 'Buku Besar Umum')

@section('content')
<div class="container py-4">

    {{-- BAGIAN FILTER --}}
    <form action="{{ route('posting') }}" method="GET" class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            
            {{-- BAGIAN KIRI: Dropdown, Tanggal, Tombol Filter --}}
            <div class="d-flex align-items-center gap-2">
                
                {{-- Dropdown Akun --}}
                <div style="width: 350px;">
                    <select class="form-select select2" name="account_filter" required>
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->code }}" 
                                {{ (request('account_filter') ?? $selectedAccount->code ?? '') == $account->code ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Picker Tanggal --}}
                <input type="text" class="form-select" id="dateRangeFilter" name="date_range" 
                       value="{{ $dateRange ?? '' }}" placeholder="Pilih Rentang Periode" 
                       style="width: 250px;">

                {{-- Tombol Filter --}}
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
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-book me-2 text-primary"></i>
                    {{ $selectedAccount->name }} <span class="text-muted fw-normal">({{ $selectedAccount->code }})</span>
                </h6>
                <span class="badge bg-light text-secondary border">
                    Kategori: {{ $selectedAccount->category ?? 'Umum' }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.95rem;">
                    {{-- HEADER TABEL --}}
                    <thead class="bg-light text-secondary fw-bold text-uppercase" style="font-size: 0.85rem;">
                        <tr class="text-center align-middle">
                            <th style="width: 12%;">Tanggal</th>
                            <th style="width: 15%;">No. Bukti</th>
                            <th style="width: 30%;">Keterangan (Akun Lawan)</th>
                            <th style="width: 13%;">Debit (Rp)</th>
                            <th style="width: 13%;">Kredit (Rp)</th>
                            <th style="width: 5%;">D/K</th>
                            <th style="width: 15%;">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @php 
                            // Inisialisasi Saldo Berjalan
                            $runningBalance = 0; 
                        @endphp

                        @forelse ($entries as $entry)
                            @php
                                // 1. Hitung Saldo Global untuk Transaksi ini
                                $runningBalance += ($entry->debit - $entry->credit);
                                $posisi = ($runningBalance >= 0) ? 'D' : 'K';

                                // 2. Cek apakah ada Splits (dari Controller)
                                // Jika kosong (misal saldo awal), kita buat array dummy agar loop tetap jalan sekali
                                $splits = $entry->splits->isEmpty() ? [null] : $entry->splits;
                                $rowCount = count($splits);
                            @endphp

                            {{-- LOOPING RINCIAN (SPLIT ROWS) --}}
                            @foreach($splits as $index => $split)
                                <tr>
                                    {{-- KOLOM TANGGAL & NO BUKTI (ROWSPAN) --}}
                                    {{-- Hanya muncul di baris pertama dari grup transaksi --}}
                                    @if($index === 0)
                                        <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3">
                                            {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}
                                        </td>
                                        <td rowspan="{{ $rowCount }}" class="text-center bg-white align-top pt-3 small">
                                            {{ $entry->transaction_code }}
                                        </td>
                                    @endif

                                    {{-- KOLOM KETERANGAN (SPLIT) --}}
                                    <td class="bg-white">
                                        @if($split)
                                            {{ $split->account_code }} - {{ $split->account_name }}
                                        @else
                                            {{-- Fallback jika tidak ada akun lawan (misal Saldo Awal) --}}
                                            <span class="text-muted fst-italic">Penyesuaian / Saldo Awal</span>
                                        @endif
                                    </td>

                                    {{-- KOLOM DEBIT / KREDIT (LOGIKA NOMINAL) --}}
                                    @php
                                        $displayDebit = 0;
                                        $displayCredit = 0;

                                        if ($split) {
                                            if ($entry->debit > 0) {
                                                // Jika Transaksi Utama adalah DEBIT (Uang Masuk), 
                                                // maka rinciannya kita ambil dari nilai Kredit si Lawan (atau debitnya)
                                                // dan kita taruh di kolom DEBIT agar user tahu "Ini lho komponen debitnya"
                                                $displayDebit = ($split->credit > 0) ? $split->credit : $split->debit;
                                            } else {
                                                // Sebaliknya untuk KREDIT
                                                $displayCredit = ($split->debit > 0) ? $split->debit : $split->credit;
                                            }
                                        } else {
                                            // Fallback single row
                                            $displayDebit = $entry->debit;
                                            $displayCredit = $entry->credit;
                                        }
                                    @endphp

                                    <td class="text-end bg-white">
                                        {{ $displayDebit > 0 ? number_format($displayDebit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end bg-white">
                                        {{ $displayCredit > 0 ? number_format($displayCredit, 0, ',', '.') : '-' }}
                                    </td>

                                    {{-- KOLOM D/K & SALDO (ROWSPAN) --}}
                                    {{-- Hanya muncul di baris pertama --}}
                                    @if($index === 0)
                                        <td rowspan="{{ $rowCount }}" class="text-center fw-bold text-secondary bg-white align-top pt-3">
                                            {{ $posisi }}
                                        </td>
                                        <td rowspan="{{ $rowCount }}" class="text-end fw-bold text-dark bg-white align-top pt-3">
                                            {{ number_format(abs($runningBalance), 0, ',', '.') }}
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

                    {{-- FOOTER TOTAL --}}
                    <tfoot class="bg-light fw-bold border-top" style="border-top: 2px solid #dee2e6 !important;">
                        <tr>
                            <td colspan="3" class="text-end text-uppercase py-3 pe-3">Total Mutasi</td>
                            <td class="text-end py-3 fw-bold">
                                {{ number_format($entries->sum('debit'), 0, ',', '.') }}
                            </td>
                            <td class="text-end py-3 fw-bold">
                                {{ number_format($entries->sum('credit'), 0, ',', '.') }}
                            </td>
                            <td></td>
                            <td class="text-end py-3 bg-white border text-dark">
                                {{ number_format(abs($runningBalance), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            {{-- FOOTER / PAGINATION --}}
            <div class="card-footer bg-light py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Menampilkan {{ $entries->firstItem() ?? 0 }} - {{ $entries->lastItem() ?? 0 }} dari {{ $entries->total() }} transaksi
                    </small>

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
        defaultDate: ["{{ $startDate }}", "{{ $endDate }}"],
        locale: { rangeSeparator: " to " }
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