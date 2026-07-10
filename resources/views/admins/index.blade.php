<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    @vite(['resources/css/super-admin.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* === SUCCESS MODAL (animated popup) === */
        .success-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:10000;animation:fadeIn .3s}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        .success-box{background:#fff;border-radius:16px;padding:40px;text-align:center;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:popIn .4s cubic-bezier(.34,1.56,.64,1)}
        @keyframes popIn{from{transform:scale(.7);opacity:0}to{transform:scale(1);opacity:1}}
        .success-icon{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 16px;animation:checkBounce .6s .3s both}
        @keyframes checkBounce{0%{transform:scale(0)}50%{transform:scale(1.2)}100%{transform:scale(1)}}
        .success-box h2{font-size:22px;font-weight:700;color:#1e293b;margin-bottom:8px; margin-top:0;}
        .success-box p{font-size:14px;color:#64748b;margin-bottom:24px;line-height:1.6}
    </style>
</head>
<body>
    <div class="sa-container">
        
        <header class="sa-header">
            <div class="sa-title">
                <h1>Super Admin</h1>
                <p>Kelola akun admin</p>
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
                <h2>Daftar Admin</h2>
                <a href="{{ route('admins.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Admin
                </a>
            </div>

            <div class="sa-table-wrapper">
                <table class="sa-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $index => $admin)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $admin->username }}</strong></td>
                            <td>
                                <div class="td-actions">
                                    <a href="{{ route('admins.edit', $admin->id_admin) }}" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admins.destroy', $admin->id_admin) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-red btn-sm btn-delete">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    {{-- SUCCESS MODAL (interactive popup) --}}
    @if(session('success'))
    <div class="success-overlay" id="successModal">
        <div class="success-box">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h2>Berhasil!</h2>
            <p>{{ session('success') }}</p>
            <button class="btn-primary" onclick="document.getElementById('successModal').remove()" style="padding:10px 32px;font-size:14px;font-weight:600;">
                Tutup
            </button>
        </div>
    </div>
    @endif

    <script>
        // SweetAlert2 is only used for Delete Confirmation now.

        // Konfirmasi Hapus dengan SweetAlert2
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Admin yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>
