<div class="sidebar d-flex flex-column flex-shrink-0 vh-100 bg-white border-end" style="width: 280px; min-width: 280px;">
    
    {{-- 1. HEADER / LOGO --}}
    <div class="px-4 py-4 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('assets/favicon/web-app-manifest-512x512.png') }}" 
                 alt="Logo Koperasi" 
                 style="height: 48px; width: 48px; object-fit: contain;">
            
            <div class="d-flex flex-column justify-content-center" style="line-height: 1.2;">
                <span class="fw-bold text-dark" style="font-size: 1.1rem; letter-spacing: -0.5px;">
                    Sistem Koperasi
                </span>
                <small class="text-secondary" style="font-size: 0.75rem;">
                    Kementerian Koperasi RI
                </small>
            </div>
        </div>
    </div>

    {{-- 2. NAVIGATION MENU --}}
    <div class="flex-grow-1 overflow-y-auto custom-scrollbar">
        <nav class="nav flex-column p-3 gap-1">
            
            @php
                function getNavLinkClass($route) {
                    $isActive = request()->is($route . '*');
                    return $isActive 
                        ? 'nav-link d-flex align-items-center gap-3 py-3 px-3 rounded bg-primary bg-opacity-10 text-primary fw-bold' 
                        : 'nav-link d-flex align-items-center gap-3 py-3 px-3 rounded text-dark hover-bg-light';
                }
            @endphp

            {{-- Rekomendasi Nomor Akun --}}
            <a href="{{ route('account-code-recommender.show') }}" class="{{ getNavLinkClass('account-code-recommender') }}">
                <i class="bi bi-lightning-charge fs-5"></i> 
                <span>Rekomendasi Kode Akun</span>
            </a>

            {{-- Jurnal Umum --}}
            <a href="{{ route('ledger') }}" class="{{ getNavLinkClass('ledger') }}">
                <i class="bi bi-journal-text fs-5"></i> 
                <span>Jurnal Umum</span>
            </a>

            {{-- Buku Besar Umum --}}
            <a href="{{ route('posting') }}" class="{{ getNavLinkClass('posting') }}">
                <i class="bi bi-upload fs-5"></i> 
                <span>Buku Besar Umum</span>
            </a>

            {{-- Neraca Saldo --}}
            <a href="{{ route('trial-balance') }}" class="{{ getNavLinkClass('trial-balance') }}">
                <i class="bi bi-list-check fs-5"></i> 
                <span>Neraca Saldo</span>
            </a>

            {{-- Laporan SHU --}}
            <a href="{{ route('shu-report') }}" class="{{ getNavLinkClass('shu-report') }}">
                <i class="bi bi-file-earmark-text fs-5"></i> 
                <span>Laporan SHU</span>
            </a>

            {{-- Laporan Posisi Keuangan --}}
            <a href="{{ route('financial-position-report') }}" class="{{ getNavLinkClass('financial-position-report') }}">
                <i class="bi bi-bank fs-5"></i> 
                <span>Laporan Posisi Keuangan</span>
            </a>

        </nav>
    </div>

    {{-- 3. FOOTER / USER PROFILE --}}
    <div class="mt-auto px-4 py-4 border-top bg-light">
        @php
            $userName = Auth::user()->name ?? 'Admin';
            $words = explode(' ', $userName);
            $initials = strtoupper(substr($words[0], 0, 1));
            if (count($words) > 1) {
                $initials .= strtoupper(substr($words[1], 0, 1));
            }
        @endphp

        {{-- Info User --}}
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                 style="width: 42px; height: 42px; min-width: 42px; background-color: #fff; color: #0d6efd; font-weight: 700; border: 1px solid #dee2e6;">
                {{ $initials }}
            </div>
            <div class="d-flex flex-column overflow-hidden">
                <span class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">{{ $userName }}</span>
                <span class="text-muted" style="font-size: 0.75rem;">Administrator</span>
            </div>
        </div>

        {{-- Tombol Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 py-2" style="border-radius: 8px;">
                <i class="bi bi-box-arrow-right"></i>
                <span class="fw-semibold" style="font-size: 0.9rem;">Keluar Aplikasi</span>
            </button>
        </form>
    </div>
</div>

<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa; 
        color: #0d6efd !important; 
        transition: all 0.2s ease;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 10px;
    }
</style>