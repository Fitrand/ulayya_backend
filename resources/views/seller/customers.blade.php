@extends('seller.layout')

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────────────── --}}
<div class="cust-stats-grid">
    <article class="cust-stat-card" style="--sc:#2563eb;--sb:rgba(37,99,235,.10);">
        <div class="cust-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <span class="cust-stat-label">Total Pelanggan</span>
        <span class="cust-stat-value">{{ $totalCustomers }}</span>
    </article>

    <article class="cust-stat-card" style="--sc:#16a34a;--sb:rgba(22,163,74,.10);">
        <div class="cust-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                <polyline points="17 6 23 6 23 12"/>
            </svg>
        </div>
        <span class="cust-stat-label">Rata-rata Transaksi</span>
        <span class="cust-stat-value">{{ $avgTransaction }}</span>
    </article>

    <article class="cust-stat-card" style="--sc:#7c3aed;--sb:rgba(124,58,237,.10);">
        <div class="cust-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <span class="cust-stat-label">Total Pesanan</span>
        <span class="cust-stat-value">{{ $totalOrders }}</span>
    </article>
</div>

{{-- ── Search bar ───────────────────────────────────────────────── --}}
<form method="get" id="cust-filter" class="cust-search-bar">
    <div class="cust-search-wrap">
        <svg class="cust-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="q" value="{{ $queryText }}" class="cust-search-input"
               placeholder="Cari pelanggan..." autocomplete="off">
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-shrink:0;">
        <button type="submit" class="cust-search-btn">Cari</button>
        <a href="{{ route('seller.customers.create') }}" class="cust-add-btn">+ Tambah</a>
    </div>
</form>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="seller-card cust-table-card">
    <div class="cust-thead">
        <span>Pelanggan</span>
        <span>Kontak</span>
        <span>Total Pesanan</span>
        <span>Total Belanja</span>
        <span>Bergabung</span>
        <span>Aksi</span>
    </div>

    @forelse ($customers as $customer)
    @php
        $initial   = strtoupper(substr($customer->name, 0, 1));
        $colors    = ['#f59e0b','#2563eb','#16a34a','#7c3aed','#0891b2','#dc2626','#d97706'];
        $color     = $colors[crc32($customer->name) % count($colors)];
        $totalSpend= $customer->orders->where('status', '!=', 'cancelled')->sum('total_amount');
        $joinDate  = optional($customer->created_at)->translatedFormat('d M Y');
    @endphp
    <div class="cust-row">
        {{-- Pelanggan --}}
        <div class="cust-cell cust-name-cell">
            <div class="cust-avatar" style="background:{{ $color }}20;color:{{ $color }};">
                {{ $initial }}
            </div>
            <div>
                <div class="cust-name">{{ $customer->name }}</div>
            </div>
        </div>

        {{-- Kontak --}}
        <div class="cust-cell">
            <div class="cust-contact-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" width="13" height="13"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
                <a href="mailto:{{ $customer->email }}" class="cust-link">{{ $customer->email }}</a>
            </div>
            @if($customer->phone)
            <div class="cust-contact-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" width="13" height="13"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.12 1.18 2 2 0 012.1 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.46-.46a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                <span>{{ $customer->phone }}</span>
            </div>
            @endif
        </div>

        {{-- Total Pesanan --}}
        <div class="cust-cell">
            <span class="cust-orders-count">{{ $customer->orders_count }} pesanan</span>
        </div>

        {{-- Total Belanja --}}
        <div class="cust-cell">
            <span class="cust-spend">Rp {{ number_format($totalSpend, 0, ',', '.') }}</span>
        </div>

        {{-- Bergabung --}}
        <div class="cust-cell">
            <span class="cust-join">{{ $joinDate }}</span>
        </div>

        {{-- Aksi --}}
        <div class="cust-cell cust-actions">
            <a href="{{ route('seller.customers.edit', $customer->id) }}" class="cust-btn-edit">Edit</a>
            <form action="{{ route('seller.customers.destroy', $customer->id) }}" method="post"
                  style="display:contents" onsubmit="return confirm('Hapus pelanggan {{ addslashes($customer->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="cust-btn-del">Hapus</button>
            </form>
        </div>
    </div>
    @empty
    <div class="cust-empty">
        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <circle cx="24" cy="16" r="8"/><path d="M8 40c0-8.837 7.163-16 16-16s16 7.163 16 16"/>
        </svg>
        <p>Tidak ada pelanggan ditemukan.</p>
    </div>
    @endforelse
</div>

<div style="margin-top:1rem;">{{ $customers->links() }}</div>

<style>
/* Stat Cards */
.cust-stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.cust-stat-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius-xl);padding:22px;display:flex;flex-direction:column;gap:6px;box-shadow:0 2px 12px rgba(78,43,17,.06);position:relative;overflow:hidden}
.cust-stat-card::after{content:'';position:absolute;bottom:-22px;right:-22px;width:80px;height:80px;border-radius:50%;background:var(--sc);opacity:.08;pointer-events:none}
.cust-stat-icon{width:36px;height:36px;border-radius:9px;background:var(--sb);display:grid;place-items:center;margin-bottom:4px}
.cust-stat-icon svg{width:18px;height:18px;stroke:var(--sc)}
.cust-stat-label{font-size:.84rem;color:var(--muted);font-weight:600}
.cust-stat-value{font-size:1.75rem;font-weight:800;letter-spacing:-.04em;color:var(--text);line-height:1}
/* Search */
.cust-search-bar{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.cust-search-wrap{flex:1;min-width:200px;position:relative;display:flex;align-items:center}
.cust-search-icon{position:absolute;left:13px;width:16px;height:16px;color:#9ca3af;pointer-events:none}
.cust-search-input{width:100%;border:1px solid var(--border);border-radius:12px;padding:10px 14px 10px 38px;font:inherit;font-size:.92rem;background:#fff;color:var(--text)}
.cust-search-input:focus{outline:2px solid rgba(37,99,235,.2);border-color:rgba(37,99,235,.4)}
.cust-search-btn{padding:10px 20px;border-radius:10px;border:none;background:#f3f4f6;font:inherit;font-weight:700;font-size:.88rem;cursor:pointer;color:var(--text)}
.cust-search-btn:hover{background:#e5e7eb}
.cust-add-btn{display:inline-flex;align-items:center;padding:10px 18px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:700;font-size:.88rem;text-decoration:none}
/* Table */
.cust-table-card{padding:0;overflow:hidden}
.cust-thead{display:grid;grid-template-columns:2fr 2fr 1fr 1.2fr 1fr 1fr;gap:12px;padding:12px 22px;background:rgba(247,241,232,.7);border-bottom:1px solid var(--border);color:var(--muted);font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em}
.cust-row{display:grid;grid-template-columns:2fr 2fr 1fr 1.2fr 1fr 1fr;gap:12px;align-items:center;padding:14px 22px;border-bottom:1px solid rgba(109,74,31,.08);transition:background .15s}
.cust-row:last-child{border-bottom:none}
.cust-row:hover{background:rgba(37,99,235,.03)}
.cust-cell{display:flex;flex-direction:column;gap:4px;min-width:0}
/* Avatar */
.cust-name-cell{flex-direction:row;align-items:center;gap:12px}
.cust-avatar{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;font-weight:800;font-size:.92rem;flex-shrink:0}
.cust-name{font-weight:700;font-size:.92rem;color:var(--text)}
/* Contact */
.cust-contact-row{display:flex;align-items:center;gap:6px;font-size:.84rem;color:#374151}
.cust-contact-row svg{flex-shrink:0;stroke:#9ca3af}
.cust-link{color:#2563eb;text-decoration:none;font-size:.84rem}
.cust-link:hover{text-decoration:underline}
/* Data */
.cust-orders-count{font-weight:700;font-size:.88rem;color:#374151}
.cust-spend{font-weight:800;font-size:.9rem;color:#16a34a}
.cust-join{font-size:.85rem;color:#6b7280}
/* Actions */
.cust-actions{flex-direction:row;align-items:center;gap:8px}
.cust-btn-edit{padding:6px 14px;border-radius:8px;background:rgba(37,99,235,.1);color:#2563eb;font-weight:700;font-size:.82rem;text-decoration:none;border:none;cursor:pointer;font:inherit}
.cust-btn-edit:hover{background:rgba(37,99,235,.18)}
.cust-btn-del{padding:6px 14px;border-radius:8px;background:rgba(220,38,38,.1);color:#dc2626;font-weight:700;font-size:.82rem;border:none;cursor:pointer;font:inherit}
.cust-btn-del:hover{background:rgba(220,38,38,.18)}
/* Empty */
.cust-empty{display:flex;flex-direction:column;align-items:center;gap:12px;padding:48px 24px;color:var(--muted)}
.cust-empty p{margin:0;font-size:.95rem}
/* Responsive */
@media(max-width:1100px){.cust-thead,.cust-row{grid-template-columns:2fr 2fr 1fr 1.2fr}}
@media(max-width:768px){.cust-stats-grid{grid-template-columns:1fr}.cust-thead{display:none}.cust-row{grid-template-columns:1fr 1fr;row-gap:8px}}
</style>
@endsection