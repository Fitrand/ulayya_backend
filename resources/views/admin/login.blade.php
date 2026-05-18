<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kue Bhoi Admin · {{ config('app.name', 'Ulayya') }}</title>
    <style>
        :root {
            --bg-start: #fdfcf9;
            --bg-end: #fdf3e5;
            --card: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --primary: #d97706;
            --primary-hover: #b45309;
            --border: #e5e7eb;
        }
        
        * { box-sizing: border-box; }
        
        html, body {
            height: 100%;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .brand-header {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .brand-icon {
            width: 56px;
            height: 56px;
            background: #ffffff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.08);
            border: 1px solid rgba(217, 119, 6, 0.1);
        }
        
        .brand-icon img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
        
        .brand-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }
        
        .brand-header p {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }
        
        .card {
            background: var(--card);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04), 0 2px 10px rgba(0, 0, 0, 0.02);
            padding: 32px;
            width: 100%;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-wrapper svg {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: #9ca3af;
            pointer-events: none;
        }
        
        .input-field {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s;
            background: #ffffff;
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1);
        }
        
        .input-field::placeholder {
            color: #9ca3af;
        }
        
        .btn-submit {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 24px;
        }
        
        .btn-submit:hover {
            background: var(--primary-hover);
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        
        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-header">
            <div class="brand-icon">
                <!-- Gunakan kue.png atau logo fallback -->
                <img src="{{ asset('assets/image/kue.png') }}" alt="Kue Bhoi Logo" onerror="this.src='{{ asset('images/logo.png') }}'">
            </div>
            <h1>Kue Bhoi Admin</h1>
            <p>Masuk ke panel admin untuk mengelola toko Anda</p>
        </div>

        <div class="card">
            @if ($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ url('/admin/login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <input id="email" type="email" name="email" class="input-field" value="{{ old('email') }}" placeholder="admin@kuebhoi.com" required autocomplete="email" autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <input id="password" type="password" name="password" class="input-field" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk</button>
            </form>
        </div>

        <div class="footer-text">
            © 2026 Kue Bhoi. Panel Admin Khusus Penjual.
        </div>
    </div>
</body>
</html>
