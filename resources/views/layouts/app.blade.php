<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard PLPS')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @yield('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body>

{{-- MAIN AREA --}}
<div class="main-area">
    {{-- TOP HEADER --}}
    <header class="top-header">
        <div class="top-header-left">
            <h1><i class="fas fa-chart-line"></i> <span>Dashboard PLPS</span></h1>
            
            <nav class="top-nav">
                <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                @auth('admin')
                <a href="/input-data" class="{{ request()->is('input-data*') ? 'active' : '' }}">
                    <i class="fas fa-file-upload"></i> Input Data
                </a>
                @endauth
            </nav>
        </div>
        <div class="top-header-right">
            @auth('admin')
            <div class="admin-info">{{ Auth::guard('admin')->user()->username ?? 'Admin' }}</div>
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-white" style="padding:6px 14px;font-size:12px;color:#7B1113"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
            @endauth
            @guest('admin')
            <a href="{{ route('login') }}" class="btn btn-white" style="padding:6px 14px;font-size:12px;color:#7B1113;text-decoration:none;"><i class="fas fa-sign-in-alt"></i> Login</a>
            @endguest
            <button class="burger-btn" onclick="toggleMobileMenu()"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    {{-- MOBILE MENU --}}
    <div class="mobile-menu" id="mobileMenu">
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        @auth('admin')
        <a href="/input-data" class="{{ request()->is('input-data*') ? 'active' : '' }}">
            <i class="fas fa-file-upload"></i> Data Input
        </a>
        @endauth
    </div>

    {{-- CONTENT --}}
    <div class="content-area">
        {{-- SUCCESS TOAST --}}
        @if(session('success') && !session('show_success_modal'))
        <div class="toast" id="successToast">✅ {{ session('success') }}</div>
        <script>setTimeout(()=>{const t=document.getElementById('successToast');if(t)t.style.display='none'},3000)</script>
        @endif

        {{-- SUCCESS MODAL (interactive popup) --}}
        @if(session('show_success_modal'))
        <div class="success-overlay" id="successModal">
            <div class="success-box">
                <div class="success-icon"><i class="fas fa-check"></i></div>
                <h2>Import Berhasil!</h2>
                <p>{{ session('success') }}</p>
                <button class="btn btn-primary" onclick="document.getElementById('successModal').remove()" style="padding:10px 32px;font-size:14px">
                    Tutup
                </button>
            </div>
        </div>
        @endif

        @yield('content')
    </div>

    {{-- FOOTER --}}
    <footer class="app-footer">
        &copy; 2026 PLPS Admin System. All Rights Reserved.
    </footer>
</div>


@yield('scripts')
</body>
</html>
