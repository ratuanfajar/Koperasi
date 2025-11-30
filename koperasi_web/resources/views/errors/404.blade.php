<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Koperasi')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon.png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
        }

        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-content {
            text-align: center;
            max-width: 480px;
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 16px;
            /* Bayangan halus yang sama dengan Login */
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }

        .error-icon {
            font-size: 6rem;
            color: #ff6b6b; /* Merah soft untuk ikon error */
            margin-bottom: 20px;
            line-height: 1;
        }

        .error-heading {
            font-size: 2.5rem;
            font-weight: 800;
            color: #212529;
            margin-bottom: 10px;
        }

        .error-subheading {
            font-size: 1.25rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 15px;
        }

        .error-text {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-primary-custom {
            background-color: #2b4c9b !important;
            border-color: #2b4c9b !important;
            color: white !important;
            padding: 12px 35px;
            border-radius: 50px; 
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }

        .btn-primary-custom:hover {
            background-color: #1e3a8a !important; /* Biru sedikit lebih gelap saat hover */
            border-color: #1e3a8a !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>
<body>

    <div class="error-container">
        <div class="error-content">
            
            <div class="error-icon">
                <i class="bi bi-emoji-frown-fill"></i>
            </div>

            <h1 class="error-heading">404</h1>
            <h2 class="error-subheading">{{ __('Halaman Tidak Ditemukan') }}</h2>
            
            <p class="error-text">
                {{ __('Maaf, kami tidak dapat menemukan halaman yang Anda cari. Mungkin tautannya salah atau halaman telah dihapus.') }}
            </p>

            {{-- Tombol Kembali dengan Warna Biru --}}
            <a href="{{ route('account-code-recommender.show') }}" class="btn btn-primary-custom">
                <i class="bi bi-arrow-left me-2"></i> {{ __('Kembali ke Dashboard') }}
            </a>
        </div>
    </div>

</body>
</html>