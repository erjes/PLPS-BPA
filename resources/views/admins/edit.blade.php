<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    @vite(['resources/css/super-admin.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="sa-container" style="max-width: 600px;">
        
        <header class="sa-header">
            <div class="sa-title">
                <h1>Super Admin</h1>
                <p>Edit akun admin</p>
            </div>
            <div class="sa-actions">
                <form action="/logout" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </header>

        <div class="sa-card">
            <div class="sa-card-header">
                <h2>Form Edit Admin</h2>
            </div>

            @if ($errors->any())
                <div class="sa-alert-error">
                    <i class="fas fa-exclamation-circle"></i> Terdapat kesalahan pada input.
                </div>
            @endif

            <form action="{{ route('admins.update', $admin->id_admin) }}" method="POST" class="sa-form">
                @csrf
                @method('PUT')
                
                <div class="sa-form-group">
                    <label class="sa-form-label">Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" value="{{ $admin->username }}" class="sa-form-input" required>
                    @error('username')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>
                
                <div class="sa-form-group">
                    <label class="sa-form-label">Password Baru <span style="font-weight:400; color:#94a3b8; font-size:12px; margin-left:4px;">(Kosongkan jika tidak diubah)</span></label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="Masukkan password baru" class="sa-form-input" oninput="checkPasswordStrength(this.value)">
                        <button type="button" class="toggle-pwd" onclick="togglePassword()">
                            <i class="fa fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Password requirements --}}
                <div class="password-req">
                    <p id="req-length"><i class="fa fa-circle"></i> Minimal 8 karakter</p>
                    <p id="req-upper"><i class="fa fa-circle"></i> Mengandung huruf besar (A-Z)</p>
                    <p id="req-special"><i class="fa fa-circle"></i> Mengandung karakter spesial (!?@#$%^&* dll)</p>
                </div>

                <div class="form-actions">
                    <a class="btn btn-secondary" href="{{ route('admins.index') }}">Batal</a>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save"></i> Update Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength(value) {
            const hasLength  = value.length >= 8;
            const hasUpper   = /[A-Z]/.test(value);
            const hasSpecial = /[!?@#$%^&*()\-_=+\[\]{};:'",.<>\/\\`~]/.test(value);

            setReq('req-length',  hasLength);
            setReq('req-upper',   hasUpper);
            setReq('req-special', hasSpecial);
        }

        function setReq(id, passed) {
            const el = document.getElementById(id);
            if (passed) {
                el.classList.remove('text-gray-400', 'text-red-500');
                el.classList.add('text-green-500');
                el.querySelector('i').classList.remove('fa-circle');
                el.querySelector('i').classList.add('fa-check-circle');
            } else {
                el.classList.remove('text-green-500', 'text-gray-400');
                el.classList.add('text-red-500');
                el.querySelector('i').classList.remove('fa-check-circle');
                el.querySelector('i').classList.add('fa-circle');
            }
        }
    </script>
</body>
</html>
