<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Login' }} - PLPS</title>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            
            {{-- Logos --}}
            <div class="auth-logos">
                <img src="{{ asset('images/telkom.png') }}" alt="Telkom University">
                <img src="{{ asset('images/bpa.png') }}" alt="BPA">
            </div>

            <h1 class="auth-title">{{ $title ?? 'Login' }}</h1>

            @if ($errors->any())
                <div class="auth-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ $postUrl ?? url()->current() }}" class="auth-form">
                @csrf

                <div class="input-group">
                    <input type="text" name="login_id" placeholder="Email / Username" required autofocus value="{{ old('login_id') }}">
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fa fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>

                <button type="submit" class="auth-btn">
                    Login
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('togglePasswordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
