@extends('seller.layout')

@section('content')

{{-- ── Search bar ───────────────────────────────────────────────── --}}
<form method="get" id="review-filter" class="cust-search-bar" style="margin-bottom: 20px;">
    <div class="cust-search-wrap">
        <svg class="cust-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="q" value="{{ $queryText }}" class="cust-search-input"
               placeholder="Cari berdasarkan nama produk, pelanggan, atau isi ulasan..." autocomplete="off">
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-shrink:0;">
        <button type="submit" class="cust-search-btn">Cari</button>
    </div>
</form>

{{-- ── Review List ─────────────────────────────────────────────────────── --}}
<div class="reviews-grid">
    @forelse ($reviews as $review)
    <article class="seller-card review-card">
        <div class="review-header">
            <div class="review-user">
                @php
                    $initial = strtoupper(substr(optional($review->user)->name ?? 'A', 0, 1));
                    $colors  = ['#f59e0b','#2563eb','#16a34a','#7c3aed','#0891b2','#dc2626','#d97706'];
                    $color   = $colors[crc32(optional($review->user)->name ?? 'A') % count($colors)];
                @endphp
                <div class="review-avatar" style="background:{{ $color }}20;color:{{ $color }};">
                    {{ $initial }}
                </div>
                <div>
                    <div class="review-name">{{ optional($review->user)->name ?? 'Anonim' }}</div>
                    <div class="review-date">{{ optional($review->created_at)->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>
            <div class="review-rating">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= $review->rating)
                        <svg viewBox="0 0 24 24" fill="#f59e0b" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="#e5e7eb" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    @endif
                @endfor
            </div>
        </div>

        <div class="review-product">
            <span style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Produk:</span>
            <div style="font-weight:700;font-size:14px;">{{ optional($review->product)->name ?? 'Produk Dihapus' }}</div>
            @if($review->order)
            <div style="font-size:13px;color:#2563eb;">No. Pesanan: {{ $review->order->order_number }}</div>
            @endif
        </div>

        <div class="review-body">
            @if($review->review)
                "{{ $review->review }}"
            @else
                <span style="color:var(--muted);font-style:italic;">Tidak ada teks ulasan, hanya memberikan rating.</span>
            @endif
        </div>
    </article>
    @empty
    <div class="seller-card" style="grid-column: 1 / -1; display:flex;flex-direction:column;align-items:center;padding:40px;color:var(--muted)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="margin-bottom:10px;">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <p>Belum ada ulasan yang sesuai pencarian.</p>
    </div>
    @endforelse
</div>

<div style="margin-top:20px;">{{ $reviews->links() }}</div>

<style>
/* Search */
.cust-search-bar{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.cust-search-wrap{flex:1;min-width:200px;position:relative;display:flex;align-items:center}
.cust-search-icon{position:absolute;left:13px;width:16px;height:16px;color:#9ca3af;pointer-events:none}
.cust-search-input{width:100%;border:1px solid var(--border);border-radius:12px;padding:10px 14px 10px 38px;font:inherit;font-size:.92rem;background:#fff;color:var(--text)}
.cust-search-input:focus{outline:2px solid rgba(37,99,235,.2);border-color:rgba(37,99,235,.4)}
.cust-search-btn{padding:10px 20px;border-radius:10px;border:none;background:#f3f4f6;font:inherit;font-weight:700;font-size:.88rem;cursor:pointer;color:var(--text)}
.cust-search-btn:hover{background:#e5e7eb}

/* Reviews Grid */
.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}
.review-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}
.review-user {
    display: flex;
    align-items: center;
    gap: 10px;
}
.review-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 800;
    font-size: .92rem;
    flex-shrink: 0;
}
.review-name {
    font-weight: 700;
    font-size: .92rem;
    color: var(--text);
}
.review-date {
    font-size: .8rem;
    color: var(--muted);
}
.review-rating {
    display: flex;
    gap: 2px;
}
.review-product {
    background: rgba(37,99,235,.05);
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(37,99,235,.1);
}
.review-body {
    font-size: 0.95rem;
    line-height: 1.5;
    color: #374151;
    background: #f9fafb;
    padding: 12px;
    border-radius: 8px;
    font-style: italic;
    border-left: 3px solid #e5e7eb;
}
</style>
@endsection
