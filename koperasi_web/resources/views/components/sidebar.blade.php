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
        <i class="bi bi-lightning-charge"></i> Rekomendasi Nomor Akun
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

  <div class="px-4 mb-3">
    <a href="#" class="nav-link text-danger d-flex align-items-center gap-2">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>
  </div>
</div>
