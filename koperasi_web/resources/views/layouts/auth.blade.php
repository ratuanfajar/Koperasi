<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Koperasi')</title>
    
    {{-- Favicon Standar (Browser Desktop Lama & Baru) --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon.ico') }}" />

    {{-- Untuk iPhone/iPad (Apple Touch Icon) --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}" />

    {{-- Untuk Android & Chrome Mobile (PWA Manifest) --}}
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #cce9f8 0%, #f0f9ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
        }
        body::after {
            content: '';
            position: absolute;
            width: 800px;
            height: 800px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            border-radius: 24px; 
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 10;
        }

        .logo-icon-wrapper {
            width: 60px;
            height: 60px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-control-custom {
            background-color: #f3f4f6;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 14px 16px 14px 45px;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: #2563eb; 
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }

        .btn-login {
            background-color: #2b4c9b; 
            color: white;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            border: none;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background-color: #1e3a8a;
        }
    </style>
</head>
<body>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }
    </script>
</body>
</html>