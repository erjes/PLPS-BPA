<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi CAPTCHA - Admin</title>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            
            {{-- Logos --}}
            <div class="auth-logos">
                <img src="{{ asset('images/telkom.png') }}" alt="Telkom University">
                <img src="{{ asset('images/bpa.png') }}" alt="BPA">
            </div>

            <h1 class="auth-title" style="margin-bottom: 8px;">Verifikasi CAPTCHA</h1>
            <p class="auth-subtitle">Langkah 2 dari 2 — Khusus Super Admin</p>

            @if ($errors->any())
                <div class="auth-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.captcha.verify') }}" class="auth-form">
                @csrf

                {{-- CAPTCHA image + refresh --}}
                <div class="captcha-box" id="captcha-container">
                    {!! captcha_img('flat') !!}
                </div>

                <div class="captcha-input-wrapper">
                    <div class="input-group">
                        <input type="text" name="captcha" id="captcha" placeholder="Masukkan kode captcha" required autofocus autocomplete="off">
                    </div>
                    
                    <button type="button" onclick="refreshCaptcha()" class="refresh-captcha" title="Refresh CAPTCHA">
                        <i class="fa fa-rotate-right"></i>
                    </button>
                </div>

                <button type="submit" class="auth-btn">
                    Verifikasi & Masuk
                </button>
                
                <a href="{{ route('login.superadmin') }}" class="back-link">
                    &larr; Kembali ke halaman login
                </a>
            </form>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            $.ajax({
                type: 'GET',
                url: '/refresh-captcha',
                success: function (data) {
                    $('#captcha-container').html(data.captcha);
                    $('#captcha').val('').focus();
                }
            });
        }
    </script>
</body>
</html>
