<div class="sidebar d-flex flex-column justify-content-between vh-100 bg-light border-end">
  <div>
    <div class="px-3 py-3 border-bottom">
        <div class="d-flex align-items-center gap-4">
            <img src="{{ asset('assets/favicon/web-app-manifest-512x512.png') }}" 
                alt="Logo Koperasi" 
                style="height: 56px; width: auto; object-fit: contain; margin-top: -3px;">
            
            <div class="d-flex flex-column justify-content-center" style="line-height: 1.3;">
                <span class="fw-bold text-dark text-nowrap" style="font-size: 1.05rem;">
                    Sistem Koperasi
                </span>
                <small class="text-muted text-nowrap" style="font-size: 0.72rem;">
                    Kementerian Koperasi RI
                </small>
            </div>
        </div>
    </div>
    <nav class="nav flex-column">
      {{-- Rekomendasi Nomor Akun --}}
      <a href="{{ route('account-code-recommender.show') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('account-code-recommender*') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-lightning-charge"></i> Rekomendasi Kode Akun
      </a>

      {{-- Jurnal Umum --}}
      <a href="{{ route('ledger') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('ledger') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-journal-text"></i> Jurnal Umum
      </a>

      {{-- Buku Besar Umum --}}
      <a href="{{ route('posting') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('posting') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-upload"></i> Buku Besar Umum
      </a>

      {{-- Neraca Saldo --}}
      <a href="{{ route('trial-balance') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('trial-balance') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-list-check"></i> Neraca Saldo
      </a>

      {{-- Laporan SHU --}}
      <a href="{{ route('shu-report') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('shu-report') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-file-earmark-text"></i> Laporan SHU
      </a>

      {{-- Laporan Posisi Keuangan --}}
      <a href="{{ route('financial-position-report') }}" 
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('financial-position-report') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-bank"></i> Laporan Posisi Keuangan
      </a>

    </nav>
  </div>

  <div class="mt-auto px-4 py-4 border-top">
        @php
            $userName = Auth::user()->name ?? 'Admin';
            $words = explode(' ', $userName);
            $initials = '';

            $initials .= strtoupper(substr($words[0], 0, 1));

            if (count($words) > 1) {
                $initials .= strtoupper(substr($words[1], 0, 1));
            }
        @endphp

        {{-- Info User Login --}}
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                 style="width: 40px; height: 40px; min-width: 40px; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.5px; background-color: #e9ecef; color: #495057;">
                {{ $initials }}
            </div>
            <div class="d-flex flex-column overflow-hidden" style="line-height: 1.2;">
                <small class="fw-bold text-dark text-truncate">{{ $userName }}</small>
                <small class="text-muted" style="font-size: 0.75rem;">Administrator</small>
            </div>
        </div>

        {{-- Tombol Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>
