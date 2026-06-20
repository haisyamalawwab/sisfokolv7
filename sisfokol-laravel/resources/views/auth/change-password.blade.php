<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ganti Password — SISFOKOL Laravel</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #3b0764 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            top: -10%;
            left: -10%;
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.12) 0%, rgba(168, 85, 247, 0) 70%);
            bottom: -15%;
            right: -10%;
            border-radius: 50%;
            pointer-events: none;
        }
        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 15px;
            z-index: 10;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .logo-area {
            text-align: center;
            margin-bottom: 25px;
        }
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 12px;
            box-shadow: 0 8px 16px rgba(225, 29, 72, 0.3);
        }
        .logo-title {
            color: #f8fafc;
            font-weight: 700;
            font-size: 22px;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }
        .logo-subtitle {
            color: #94a3b8;
            font-size: 14px;
            font-weight: 400;
        }
        .form-label {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .input-group-text {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            padding: 11px 15px;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.5);
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            color: #f8fafc;
        }
        .btn-submit {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border: none;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
            margin-top: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .alert-custom {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #fde047;
            border-radius: 12px;
            font-size: 14px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .alert-custom i {
            font-size: 16px;
            margin-right: 10px;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 14px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="logo-title">Ganti Password Wajib</h1>
            <p class="logo-subtitle">Untuk keamanan akun Anda, silakan ubah password bawaan.</p>
        </div>

        <div class="alert-custom">
            <i class="fas fa-shield-alt"></i>
            <div>
                Anda wajib mengubah password saat ini sebelum dapat mengakses sistem.
            </div>
        </div>

        <form method="POST" action="{{ route('password.change.store') }}">
            @csrf

            @if ($errors->any())
                <div class="alert-error">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4">
                <label for="current_password" class="form-label">Password Saat Ini</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-unlock"></i></span>
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Masukkan password saat ini" required autocomplete="current-password">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password Baru (min. 8, huruf besar+kecil & angka)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password baru" required autocomplete="new-password">
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Ulangi Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password baru" required autocomplete="new-password">
                </div>
            </div>

            <button class="btn btn-submit w-100" type="submit">
                Simpan Password Baru <i class="fas fa-save ms-2"></i>
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
