@extends('seller.layout')

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────────────── --}}
<div class="db-stats-grid">
    @php
    $icons = [
        'trend' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'box'   => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'cart'  => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.97-1.67L23 6H6"/>',
        'users' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
    ];
    @endphp

    @foreach ($stats as $stat)
    <article class="db-stat-card" style="--sc:{{ $stat['color'] }};--sb:{{ $stat['bg'] }};">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                {!! $icons[$stat['icon']] !!}
            </svg>
        </div>
        <div class="db-stat-label">{{ $stat['label'] }}</div>
        <div class="db-stat-value">{{ $stat['value'] }}</div>
        @if($stat['sub'])
        <div class="db-stat-sub {{ $stat['sub_up'] ? 'db-sub-up' : 'db-sub-warn' }}">
            {{ $stat['sub'] }}
        </div>
        @endif
    </article>
    @endforeach
</div>

{{-- ── Body: Recent Orders + Low Stock ──────────────────────────── --}}
<div class="db-body-grid">

    {{-- Recent Orders --}}
    <section class="seller-card">
        <div class="db-section-head">
            <h2>Pesanan Terbaru</h2>
            <a href="{{ route('seller.orders') }}" class="db-see-all">Lihat semua →</a>
        </div>

        <div class="db-order-list">
            @forelse ($recentOrders as $order)
            <div class="db-order-row">
                <div class="db-order-left">
                    <div class="db-order-id-row">
                        <span class="db-order-id">{{ $order->order_number }}</span>
                        <span class="db-status-badge db-status-{{ $order->status }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                    <div class="db-order-customer">{{ $order->display_customer }}</div>
                    <div class="db-order-date">{{ $order->display_date }}</div>
                </div>
                <div class="db-order-right">
                    <div class="db-order-amount">{{ $order->display_total }}</div>
                    <div class="db-order-pay">{{ $order->display_payment }}</div>
                </div>
            </div>
            @empty
            <p class="seller-empty">Belum ada pesanan terbaru.</p>
            @endforelse
        </div>
    </section>

    {{-- Low Stock --}}
    <aside class="seller-card">
        <div class="db-section-head">
            <h2>
                <span class="db-dot"></span>
                Stok Menipis
            </h2>
        </div>

        <div class="db-stock-list">
            @forelse ($lowStockProducts as $product)
            <div class="db-stock-row">
                <div>
                    <div class="db-stock-name">{{ $product->name }}</div>
                    <div class="db-stock-meta">Stok: {{ $product->stock }} unit</div>
                </div>
                <a href="{{ route('seller.products.edit', $product->id) }}"
                   class="db-restok-btn">Restok!</a>
            </div>
            @empty
            <p class="seller-empty">Semua stok aman ✓</p>
            @endforelse
        </div>
    </aside>

</div>

{{-- ── Page styles ─────────────────────────────────────────────── --}}
<style>
/* Stats grid */
.db-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
.db-stat-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px 22px;
    display: grid;
    gap: 5px;
    box-shadow: 0 2px 12px rgba(78,43,17,.06);
    position: relative;
    overflow: hidden;
}
.db-stat-card::after {
    content: '';
    position: absolute; bottom: -22px; right: -22px;
    width: 80px; height: 80px; border-radius: 50%;
    background: var(--sc); opacity: .08; pointer-events: none;
}
.db-stat-icon {
    width: 36px; height: 36px; border-radius: 9px;
    background: var(--sb); display: grid; place-items: center;
    margin-bottom: 4px;
}
.db-stat-icon svg  { width: 18px; height: 18px; stroke: var(--sc); }
.db-stat-label     { font-size: .84rem; color: var(--muted); font-weight: 600; }
.db-stat-value     { font-size: 1.75rem; font-weight: 800; letter-spacing: -.04em; color: var(--text); line-height: 1.1; }
.db-stat-sub       { font-size: .8rem; font-weight: 700; }
.db-sub-up         { color: #16a34a; }
.db-sub-warn       { color: #d97706; }

/* Body grid */
.db-body-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(260px, 1fr);
    gap: 20px;
}

/* Section head */
.db-section-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 16px;
}
.db-section-head h2 {
    margin: 0; font-size: 1.1rem;
    display: flex; align-items: center; gap: 8px;
}
.db-see-all {
    font-size: .84rem; font-weight: 700;
    color: var(--amber); text-decoration: none;
    white-space: nowrap;
}
.db-see-all:hover { text-decoration: underline; }
.db-dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: #f59e0b;
    animation: pulse-dot 1.8s ease-in-out infinite;
    display: inline-block;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .5; transform: scale(.75); }
}

/* Order list */
.db-order-list { display: grid; gap: 0; }
.db-order-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 14px 0;
    border-bottom: 1px solid rgba(109,74,31,.09);
    gap: 12px;
}
.db-order-row:last-child { border-bottom: none; }
.db-order-left   { display: grid; gap: 3px; }
.db-order-right  { text-align: right; display: grid; gap: 3px; flex-shrink: 0; }
.db-order-id-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.db-order-id     { font-weight: 700; font-size: .96rem; }
.db-order-customer { font-size: .88rem; color: var(--text); }
.db-order-date   { font-size: .82rem; color: var(--muted); }
.db-order-amount { font-weight: 800; font-size: .96rem; color: var(--text); }
.db-order-pay    { font-size: .82rem; color: var(--muted); }

/* Status badges */
.db-status-badge {
    display: inline-flex; align-items: center;
    padding: 3px 9px; border-radius: 999px;
    font-size: .75rem; font-weight: 700;
}
.db-status-pending    { background: rgba(250,204,21,.2);  color: #92400e; }
.db-status-processing { background: rgba(37,99,235,.12);  color: #1d4ed8; }
.db-status-ready      { background: rgba(124,58,237,.12); color: #6d28d9; }
.db-status-shipping, .db-status-shipped { background: rgba(8,145,178,.12); color: #0e7490; }
.db-status-delivered  { background: rgba(22,163,74,.12);  color: #15803d; }
.db-status-cancelled  { background: rgba(220,38,38,.12);  color: #b91c1c; }

/* Stock list */
.db-stock-list { display: grid; gap: 0; }
.db-stock-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px;
    border-radius: 12px;
    background: rgba(253,246,228,.7);
    border: 1px solid rgba(217,119,6,.15);
    margin-bottom: 10px;
    gap: 10px;
}
.db-stock-row:last-child { margin-bottom: 0; }
.db-stock-name { font-weight: 700; font-size: .92rem; }
.db-stock-meta { font-size: .82rem; color: var(--muted); margin-top: 2px; }
.db-restok-btn {
    display: inline-flex; align-items: center;
    padding: 5px 12px; border-radius: 8px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff; font-weight: 700; font-size: .78rem;
    text-decoration: none; white-space: nowrap;
    box-shadow: 0 2px 8px rgba(217,119,6,.25);
    transition: opacity .15s;
}
.db-restok-btn:hover { opacity: .85; }

/* Responsive */
@media (max-width: 1120px) {
    .db-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .db-body-grid  { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .db-stats-grid { grid-template-columns: 1fr; }
}
</style>
@endsection