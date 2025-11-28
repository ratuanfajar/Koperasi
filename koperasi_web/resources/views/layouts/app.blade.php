<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Sistem Koperasi')</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/favicon.png') }}">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  @stack('styles')

    @php
    // Variabel $step sekarang dikontrol oleh view yang meng-extend
    $step = $step ?? 1; 
    @endphp

  <style>
    body {
      background-color: #f8fafc;
    }
    .sidebar {
      width: 240px;
      min-height: 100vh;
      background-color: #f1f5f9;
      padding: 1rem 0;
      position: fixed;
      top: 0;
      left: 0;
    }
    .sidebar .nav-link {
      color: #334155;
      font-weight: 500;
      padding: .75rem 1.25rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .sidebar .nav-link.active {
      background-color: #e2e8f0;
      border-left: 4px solid #0d6efd;
    }
    .main-content {
      margin-left: 240px;
      padding: 1.5rem;
    }

    /* =================================================================== */
    /* [PERBAIKAN] STYLE BARU UNTUK STEP PROGRESS (SESUAI GAMBAR) */
    /* =================================================================== */
    .steps {
      position: relative;
      width: 100%;
      max-width: 600px;
      /* [PERBAIKAN] Menambah jarak atas dan bawah */
      margin: 2rem auto 3rem; 
    }
    
    /* Garis abu-abu di belakang */
    .steps::before {
      content: "";
      position: absolute;
      top: 20px; /* Posisi di tengah lingkaran (40px / 2) */
      left: 0;
      width: 100%;
      height: 4px;
      background: #e0e0e0; /* Warna abu-abu */
      z-index: 0;
    }
    
    /* Garis progress (hijau) di depan */
    .steps::after {
      content: "";
      position: absolute;
      top: 20px;
      left: 0;
      height: 4px;
      background: #198754; /* Warna hijau */
      z-index: 0;
      transition: width 0.3s ease;
      width:
      @if ($step == 1) 0%;
      @elseif ($step == 2) 50%;
      @elseif ($step == 3) 100%;
      @else 0%;
      @endif
      ;
    }

    /* Wrapper untuk lingkaran + teks */
    .step-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 1;
      width: 80px; /* Beri sedikit ruang */
      text-align: center;
    }

    /* Lingkaran Angka */
    .step-number {
      width: 40px; /* Ukuran lingkaran lebih besar */
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      transition: all 0.3s ease;
      border: 4px solid #e0e0e0; /* Default border abu-abu */
      background-color: #e0e0e0; /* Default bg abu-abu */
      color: #9e9e9e; /* Default teks abu-abu */
    }

    /* Teks di bawah lingkaran */
    .step-title {
      margin-top: 0.5rem;
      font-size: 0.9rem;
      font-weight: 500;
      color: #9e9e9e; /* Default teks abu-abu */
      transition: all 0.3s ease;
    }

    /* --- LOGIKA STATUS --- */

    /* Status: Selesai (active) - Hijau */
    .step-item.active .step-number {
        background-color: #198754;
        color: #fff;
        border-color: #198754;
    }
    .step-item.active .step-title {
        color: #198754;
        font-weight: 600;
    }

    /* Status: Sekarang (current) - Biru */
    .step-item.current .step-number {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .step-item.current .step-title {
        color: #0d6efd;
        font-weight: 600;
    }
    /* ====================== AKHIR STYLE BARU ====================== */

     /* AVATAR */
   .avatar {
     width: 36px;
     height: 36px;
     border-radius: 50%;
     background: #ccc;
   }
  </style>


</head>
<body>

  {{-- Sidebar --}}
  <x-sidebar />

  {{-- Konten Halaman --}}
  <div class="main-content">

    {{-- [PERBAIKAN] Header dirapikan dan avatar ditambahkan kembali --}}
    <header class="d-flex justify-content-between align-items-center py-4 bg-white shadow-sm rounded mb-4 px-4">
      <h4 class="mb-0 fw-bold">@yield('page_title', 'Sistem Koperasi')</h4>
      <!-- <div class="d-flex align-items-center gap-2">
        <span>Jane Doe</span>
        <div class="avatar"></div>
      </div> -->
    </header>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @yield('content')
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>