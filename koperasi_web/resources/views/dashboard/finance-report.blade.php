@extends('layouts.app')

@section('page_title', 'Laporan Keuangan')

@section('content')
<div class="container py-4">

    <form action="{{ route('finance-report') }}" method="GET">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                
                <select class="form-select" name="report_type" id="reportTypeSelect" style="width: 200px;">
                    <option value="profit_loss" {{ $reportType == 'profit_loss' ? 'selected' : '' }}>
                        Laba dan Rugi
                    </option>
                    <option value="balance_sheet" {{ $reportType == 'balance_sheet' ? 'selected' : '' }}>
                        Neraca
                    </option>
                </select>

                <input type="text" class="form-select" id="dateFilter" name="date_filter" 
                       value="{{ $dateFilter ?? '' }}" placeholder="Pilih Rentang Periode" style="width: 240px;">

                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
            <div class="d-flex gap-2">
                
                @php
                    $isDataEmpty = true; // Asumsikan kosong
                    if (!empty($data)) {
                        if ($reportType == 'profit_loss' && ($data['totalIncome'] != 0 || $data['totalExpenses'] != 0)) {
                            $isDataEmpty = false;
                        }
                        if ($reportType == 'balance_sheet' && ($data['totalAssets'] != 0 || $data['totalLiabilities'] != 0 || $data['totalEquity'] != 0)) {
                            $isDataEmpty = false;
                        }
                    }
                @endphp

                <a href="{{ $isDataEmpty ? '#' : route('finance-report.export', request()->query()) }}" 
                   class="btn btn-primary {{ $isDataEmpty ? 'disabled' : '' }}"
                   @if($isDataEmpty) 
                       onclick="event.preventDefault(); alert('Tidak ada data untuk diekspor pada periode ini.');" 
                   @endif>
                    <i class="bi bi-download me-1"></i> Unduh File CSV
                </a>
            </div>
        </div>
    </form>

    {{-- ============================================= --}}
    {{-- BAGIAN UNTUK MENAMPILKAN LAPORAN --}}
    {{-- ============================================= --}}
    
    {{-- ======================== --}}
    {{-- 1. TAMPILAN PROFIT & LOSS --}}
    {{-- ======================== --}}
    @if ($reportType == 'profit_loss')
        <h4 class="mb-3">Laporan Laba dan Rugi</h4>
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Keterangan</th>
                        <th class="text-end">Jumlah Total (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!$isDataEmpty)
                        <tr>
                            <td>Total Pendapatan</td>
                            <td class="text-end">{{ number_format($data['totalIncome'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Pengeluaran</td>
                            <td class="text-end">{{ number_format($data['totalExpenses'] ?? 0, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">
                                Tidak ada data untuk periode yang dipilih.
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="table-group-divider fw-bold">
                    <tr>
                        <td>Laba(Rugi)</td>
                        <td class="text-end {{ ($data['profitOrLoss'] ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($data['profitOrLoss'] ?? 0, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- ======================== --}}
    {{-- 2. TAMPILAN NERACA --}}
    {{-- ======================== --}}
    @if ($reportType == 'balance_sheet')
        <h4 class="mb-3">Neraca</h4>
        
        @if ($isDataEmpty)
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-info-circle fs-1 text-secondary mb-3"></i>
                    <h5 class="card-title mb-2">Tidak Ada Data</h5>
                    <p class="text-muted">Tidak ada data yang ditemukan pada periode yang dipilih.</p>
                </div>
            </div>
        @else
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">Aset</div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            @forelse($data['assets'] as $asset)
                            <tr>
                                <td>{{ $asset->account_code }} - {{ $asset->account_name }}</td>
                                <td class="text-end">{{ number_format($asset->balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted">Tidak ada aset</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td>Total Aset</td>
                                <td class="text-end">{{ number_format($data['totalAssets'] ?? 0, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">Kewajiban</div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            @forelse($data['liabilities'] as $liability)
                            <tr>
                                <td>{{ $liability->account_code }} - {{ $liability->account_name }}</td>
                                <td class="text-end">{{ number_format($liability->balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted">Tidak ada kewajiban</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td>Total kewajiban</td>
                                <td class="text-end">{{ number_format($data['totalLiabilities'] ?? 0, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Ekuitas</div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            @forelse($data['equity'] as $eq)
                            <tr>
                                <td>{{ $eq->account_code }} - {{ $eq->account_name }}</td>
                                <td class="text-end">{{ number_format($eq->balance, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted">Tidak ada ekuitas</td></tr>
                            @endforelse
                            <tr>
                                <td>Laba Tahun Berjalan</td>
                                <td class="text-end">{{ number_format($data['currentYearEarnings'] ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td>Total Ekuitas</td>
                                <td class="text-end">{{ number_format($data['totalEquity'] ?? 0, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table th, .table td { vertical-align: middle; }
    .table thead th { font-weight: 600; }
    .table tfoot td { font-size: 1.1rem; padding-top: 1rem; padding-bottom: 1rem; }
    .card-body .table { margin-bottom: 0; }
    .card-body .table tbody tr:last-child td { border-bottom: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const reportSelect = document.getElementById('reportTypeSelect');
    const dateFilterInput = document.getElementById('dateFilter');
    let pickerInstance = null;

    function updatePickerMode() {
        if (pickerInstance) {
            pickerInstance.destroy();
        }

        if (reportSelect.value === 'profit_loss') {
            pickerInstance = flatpickr(dateFilterInput, {
                mode: "range",
                dateFormat: "Y-m-d"
            });
            dateFilterInput.placeholder = "Pilih Rentang Periode";
        } else {
            pickerInstance = flatpickr(dateFilterInput, {
                mode: "single",
                dateFormat: "Y-m-d"
            });
            dateFilterInput.placeholder = "Pilih Tanggal";
        }
    }

    reportSelect.addEventListener('change', function() {
        dateFilterInput.value = "";
        updatePickerMode();
    });

    document.addEventListener('DOMContentLoaded', updatePickerMode);
</script>
@endpush