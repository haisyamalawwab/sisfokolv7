<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — SISFOKOL Laravel</title>
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
            max-width: 440px;
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
            margin-bottom: 30px;
        }
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 15px;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
        }
        .logo-title {
            color: #f8fafc;
            font-weight: 700;
            font-size: 24px;
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
            padding: 12px 16px;
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
        .form-control::placeholder {
            color: #64748b;
        }
        .btn-login {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border: none;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .form-check-input {
            background-color: rgba(15, 23, 42, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        .form-check-label {
            color: #94a3b8;
            font-size: 14px;
            cursor: pointer;
        }
        .alert-custom {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
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

        /* ── Demo Quick Login Panel ─────────────────────────── */
        .demo-panel {
            margin-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.07);
            padding-top: 20px;
        }
        .demo-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .demo-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.07);
        }
        .demo-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .demo-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(15, 23, 42, 0.4);
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
        }
        .demo-chip:hover {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(99,102,241,0.2);
        }
        .demo-chip.active {
            border-color: #a855f7;
            background: rgba(168, 85, 247, 0.2);
            color: #d8b4fe;
        }
        .demo-chip .chip-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .chip-superadmin  { background: #f43f5e; }
        .chip-admin       { background: #f97316; }
        .chip-admin-tenant{ background: #eab308; }
        .chip-piket       { background: #22c55e; }
        .chip-bk          { background: #06b6d4; }
        .chip-guru        { background: #6366f1; }
        .chip-walikelas   { background: #a855f7; }
        .chip-siswa       { background: #94a3b8; }
        .demo-hint {
            margin-top: 10px;
            font-size: 11px;
            color: #475569;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="logo-title">SISFOKOL v7</h1>
            <p class="logo-subtitle">Sistem Informasi Sekolah Multi-Tenant</p>
        </div>

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            @if ($errors->any())
                <div class="alert-custom">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat Saya</label>
                </div>
            </div>

            <button class="btn btn-login w-100" type="submit">
                Masuk <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>

        {{-- ── Demo Quick Login ─────────────────────────────── --}}
        @if(config('app.env') === 'local')
        <div class="demo-panel">
            <div class="demo-label"><i class="fas fa-bolt" style="color:#6366f1;font-size:11px;"></i> Demo Akun</div>
            <div class="demo-chips">
                <button type="button" class="demo-chip" id="chip-superadmin"
                    onclick="quickLogin('superadmin','SuperAdmin#2026','chip-superadmin')">
                    <span class="chip-dot chip-superadmin"></span>SuperAdmin
                </button>
                <button type="button" class="demo-chip" id="chip-admin"
                    onclick="quickLogin('admin','password','chip-admin')">
                    <span class="chip-dot chip-admin"></span>Admin Global
                </button>
                <button type="button" class="demo-chip" id="chip-admin-tenant"
                    onclick="quickLogin('admin.sekolah','demo1234','chip-admin-tenant')">
                    <span class="chip-dot chip-admin-tenant"></span>Admin Sekolah
                </button>
                <button type="button" class="demo-chip" id="chip-piket"
                    onclick="quickLogin('piket.demo','demo1234','chip-piket')">
                    <span class="chip-dot chip-piket"></span>Guru Piket
                </button>
                <button type="button" class="demo-chip" id="chip-bk"
                    onclick="quickLogin('bk.demo','demo1234','chip-bk')">
                    <span class="chip-dot chip-bk"></span>Guru BK
                </button>
                <button type="button" class="demo-chip" id="chip-guru"
                    onclick="quickLogin('guru.demo','demo1234','chip-guru')">
                    <span class="chip-dot chip-guru"></span>Guru Mapel
                </button>
                <button type="button" class="demo-chip" id="chip-walikelas"
                    onclick="quickLogin('walikelas.demo','demo1234','chip-walikelas')">
                    <span class="chip-dot chip-walikelas"></span>Wali Kelas
                </button>
                <button type="button" class="demo-chip" id="chip-siswa"
                    onclick="quickLogin('siswa.2024001','demo1234','chip-siswa')">
                    <span class="chip-dot chip-siswa"></span>Siswa
                </button>
            </div>
            <p class="demo-hint">Klik akun lalu tekan <kbd style="background:rgba(255,255,255,0.08);padding:1px 5px;border-radius:4px;font-size:10px;">Masuk</kbd></p>
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function quickLogin(username, password, chipId) {
        // Fill fields
        document.getElementById('username').value = username;
        document.getElementById('password').value = password;

        // Visual feedback: mark active chip
        document.querySelectorAll('.demo-chip').forEach(c => c.classList.remove('active'));
        const chip = document.getElementById(chipId);
        if (chip) chip.classList.add('active');

        // Auto-submit after short delay so user can see the fill
        setTimeout(() => {
            document.getElementById('loginForm').submit();
        }, 280);
    }
</script>
</body>
</html>
