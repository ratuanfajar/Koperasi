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

    .steps {
      position: relative;
      width: 100%;
      max-width: 600px;
      margin: 2rem auto 3rem; 
    }
    
    .steps::before {
      content: "";
      position: absolute;
      top: 20px; 
      left: 0;
      width: 100%;
      height: 4px;
      background: #e0e0e0;
      z-index: 0;
    }
    
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

    .step-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 1;
      width: 80px; 
      text-align: center;
    }

    .step-number {
      width: 40px; 
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      transition: all 0.3s ease;
      border: 4px solid #e0e0e0; 
      background-color: #e0e0e0; 
      color: #9e9e9e; 
    }

    /* Teks di bawah lingkaran */
    .step-title {
      margin-top: 0.5rem;
      font-size: 0.9rem;
      font-weight: 500;
      color: #9e9e9e;
      transition: all 0.3s ease;
    }


    .step-item.active .step-number {
        background-color: #198754;
        color: #fff;
        border-color: #198754;
    }
    .step-item.active .step-title {
        color: #198754;
        font-weight: 600;
    }

    .step-item.current .step-number {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .step-item.current .step-title {
        color: #0d6efd;
        font-weight: 600;
    }

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

    <header class="d-flex justify-content-between align-items-center py-4 bg-white shadow-sm rounded mb-4 px-4">
      <h4 class="mb-0 fw-bold">@yield('page_title', 'Sistem Koperasi')</h4>
      <!-- <div class="d-flex align-items-center gap-2">
        <span class="fw-medium text-dark">Jane Doe</span>
        <div class="avatar"></div>
      </div> -->
    </header>
    
    @yield('content')
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060;"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        const isSuccess = type === 'success';
        const color = isSuccess ? '#198754' : '#dc3545';
        const iconClass = isSuccess ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
        
        const toastHtml = `
            <div class="toast align-items-center border-0 shadow mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="background: white; border-left: 5px solid ${color} !important;">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi ${iconClass} fs-5"></i>
                        <span class="text-dark fw-medium">${message}</span>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const newToastEl = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(newToastEl, { delay: 5000 });
        toast.show();
        newToastEl.addEventListener('hidden.bs.toast', () => newToastEl.remove());
    }

    // Tangkap Session Flash dari Controller (Redirect)
    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
    });
  </script>

  @stack('scripts')
</body>
</html>