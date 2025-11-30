@extends('layouts.auth')

@section('content')
<div class="login-card">
    
    <div class="logo-icon-wrapper">
        <i class="bi bi-box-arrow-in-right fs-4 text-dark"></i>
    </div>

    <div class="text-center mb-4">
        <h4 class="fw-bold mb-2">{{ __('Masuk ke akun Anda') }}</h4>
        <p class="text-muted small mb-0">{{ __('Masukkan alamat email dan kata sandi Anda untuk mengakses akun Anda') }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 text-sm mb-3 border-0 bg-danger-subtle text-danger rounded-3">
            <i class="bi bi-exclamation-circle me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form-group">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" 
                   class="form-control-custom" 
                   id="email" 
                   name="email" 
                   placeholder="Email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus>
        </div>

        <div class="form-group">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" 
                   class="form-control-custom" 
                   id="password" 
                   name="password" 
                   placeholder="Password" 
                   required>
            
            <button type="button" class="password-toggle" onclick="togglePassword()">
                <i class="bi bi-eye-slash" id="toggleIcon"></i>
            </button>
        </div>

        <button type="submit" class="btn-login mt-2">
            {{ __('Masuk') }}
        </button>

    </form>
</div>
@endsection