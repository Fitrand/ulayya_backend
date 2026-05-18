<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($pageTitle ?? 'Dashboard') . ' · ' . config('app.name', 'Ulayya') }}</title>
    <link rel="stylesheet" href="{{ asset('css/seller.css') }}">
</head>
<body class="seller-body">
@php
    $navItems = [
        ['route' => 'seller.dashboard', 'label' => 'Dashboard'],
        ['route' => 'seller.products', 'label' => 'Produk'],
        ['route' => 'seller.categories', 'label' => 'Kategori'],
        ['route' => 'seller.orders', 'label' => 'Pesanan'],
        ['route' => 'seller.customers', 'label' => 'Pelanggan'],
        ['route' => 'seller.reviews', 'label' => 'Ulasan'],
        ['route' => 'seller.reports', 'label' => 'Laporan'],
        ['route' => 'seller.analytics', 'label' => 'Analytics'],
        ['route' => 'seller.settings', 'label' => 'Pengaturan'],
    ];
@endphp

<div class="seller-shell" data-seller-shell>
    <div class="seller-overlay" data-seller-overlay></div>

    <aside class="seller-sidebar">
        <div class="seller-brand">
            <div class="seller-brand-mark">🥮</div>
            <div>
                <div class="seller-brand-title">Ulayya</div>
                <div class="seller-brand-subtitle">Seller Portal</div>
            </div>
        </div>

        <div class="seller-user">
            <div class="seller-user-avatar" data-seller-avatar>U</div>
            <div>
                <div class="seller-user-name" data-seller-user-name>Penjual</div>
                <div class="seller-user-email" data-seller-user-email>Kelola toko dari sini</div>
            </div>
        </div>

        <nav class="seller-nav">
            @foreach ($navItems as $item)
                <a class="seller-nav-link {{ request()->routeIs($item['route']) ? 'is-active' : '' }}" href="{{ route($item['route']) }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="seller-sidebar-footer">
            @guest
                <a class="seller-ghost-link" href="{{ route('seller.login') }}">Login</a>
            @endguest
            <form method="post" action="{{ route('seller.logout') }}">
                @csrf
                <button class="seller-ghost-button" type="submit">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="seller-main">
        <header class="seller-topbar">
            <button class="seller-menu-button" type="button" data-seller-toggle aria-label="Buka menu">☰</button>
            <div>
                <p class="seller-kicker">Frontend penjual</p>
                <h1 class="seller-page-title">{{ $pageTitle ?? 'Dashboard' }}</h1>
            </div>
            <div class="seller-topbar-actions">
                <div class="seller-chip" data-seller-online-state>Terhubung ke DB</div>
                @guest
                    <a class="seller-button seller-button-soft" href="{{ route('seller.login') }}">Login</a>
                @endguest
            </div>
        </header>

        <section class="seller-intro">
            <p>{{ $pageDescription ?? 'Frontend seller yang dirancang dari export Figma Make AI dan dihubungkan ke database Laravel.' }}</p>
        </section>

        <section class="seller-content">
            @if (session('status'))
                <div class="seller-card" style="margin-bottom:1rem; border-left:4px solid #22c55e;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="seller-card" style="margin-bottom:1rem; border-left:4px solid #ef4444;">
                    <strong>Terjadi kesalahan validasi:</strong>
                    <ul style="margin:0.5rem 0 0 1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </section>
    </main>
</div>

<script src="{{ asset('js/seller.js') }}" defer></script>
@stack('scripts')
</body>
</html>