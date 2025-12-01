<div class="sidebar d-flex flex-column justify-content-between vh-100 bg-light border-end">
  <div>
    <div class="px-4 mb-4 mt-3">
      <h5 class="fw-bold d-flex align-items-center gap-2">
          <img src="{{ asset('assets/favicon.png') }}"
              alt="Logo Koperasi"
              style="height: 24px; width: auto;">

          <span>Sistem Koperasi</span>
      </h5>
    </div>
    <nav class="nav flex-column">

      {{-- Rekomendasi Nomor Akun --}}
      <a href="{{ route('account-code-recommender.show') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('account-code-recommender*') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-lightning-charge"></i> Rekomendasi Kode Akun
      </a>

      {{-- Buku Besar Akun --}}
      <a href="{{ route('ledger') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('ledger') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-journal-text"></i> Buku Besar Akun
      </a>

      {{-- Posting Jurnal --}}
      <a href="{{ route('posting') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('posting') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-upload"></i> Posting Jurnal
      </a>

      {{-- Neraca Saldo --}}
      <a href="{{ route('trial-balance') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('trial-balance') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-list-check"></i> Neraca Saldo
      </a>

      {{-- Laporan Keuangan --}}
      <a href="{{ route('finance-report') }}"
         class="nav-link d-flex align-items-center gap-2 {{ request()->is('finance-report') ? 'active fw-semibold text-primary' : 'text-dark' }}">
        <i class="bi bi-file-earmark-text"></i> Laporan Keuangan
      </a>

    </nav>
  </div>

  <div class="mt-auto px-4 py-4 border-top">
        
        {{-- LOGIKA INISIAL NAMA --}}
        @php
            $userName = Auth::user()->name ?? 'Admin';
            $words = explode(' ', $userName);
            $initials = '';

            // Ambil huruf pertama dari kata pertama
            $initials .= strtoupper(substr($words[0], 0, 1));

            // Jika ada kata kedua, ambil huruf pertamanya juga
            if (count($words) > 1) {
                $initials .= strtoupper(substr($words[1], 0, 1));
            }
        @endphp

        {{-- Info User Login --}}
        <div class="d-flex align-items-center gap-3 mb-3">
            {{-- AVATAR INISIAL --}}
            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                 style="width: 40px; height: 40px; min-width: 40px; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.5px; background-color: #e9ecef; color: #495057;">
                {{ $initials }}
            </div>
            
            {{-- NAMA LENGKAP --}}
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
