@extends('seller.layout')

@section('content')

{{-- Filter bar --}}
<form method="get" id="filter-form" class="ord-filter-bar">
    <div class="ord-search-wrap">
        <svg class="ord-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" value="{{ $queryText }}" class="ord-search-input" placeholder="Cari nomor pesanan atau nama pelanggan..." autocomplete="off">
    </div>
    <div class="ord-filter-right">
        <select name="status" class="ord-status-filter" onchange="document.getElementById('filter-form').submit()">
            @foreach ($statusOptions as $opt)
                <option value="{{ $opt['value'] }}" @selected($status===$opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="ord-apply-btn">Cari</button>
    </div>
</form>

{{-- Table --}}
<div class="seller-card ord-table-card">
    <div class="ord-thead">
        <span>Nomor Pesanan</span><span>Pelanggan</span><span>Tanggal</span>
        <span>Total</span><span>Status</span><span>Aksi</span>
    </div>

    @forelse ($orders as $order)
    {{-- hidden status form --}}
    <form id="sf-{{ $order->id }}" method="post" action="{{ route('seller.orders.update-status', $order->id) }}" style="display:none">
        @csrf @method('PATCH')
        <input type="hidden" name="status" id="sv-{{ $order->id }}">
    </form>

    @php
        $addr = $order->address;
        $addrStr = $addr
            ? trim(($addr->address_line ?? '') . ', ' . ($addr->city ?? '') . ', ' . ($addr->province ?? ''))
            : ($order->shipping_address ?? '-');
        $phone = optional($order->user)->phone ?? '-';
        $waPhone = '62' . ltrim($phone, '0');
        $items = $order->items->map(fn($i) => [
            'name' => $i->product_name,
            'qty'  => $i->quantity,
            'price'=> 'Rp ' . number_format($i->price, 0, ',', '.'),
            'sub'  => 'Rp ' . number_format($i->subtotal, 0, ',', '.'),
        ])->toArray();
    @endphp

    <div class="ord-row"
         data-id="{{ $order->id }}"
         data-num="{{ $order->order_number }}"
         data-customer="{{ $order->display_customer }}"
         data-phone="{{ $phone }}"
         data-wa="{{ $waPhone }}"
         data-addr="{{ $addrStr }}"
         data-status="{{ $order->status }}"
         data-status-label="{{ $order->status_label }}"
         data-payment="{{ $order->display_payment }}"
         data-total="{{ $order->display_total }}"
         data-items="{{ json_encode($items) }}">

        <div class="ord-cell">
            <span class="ord-order-id">{{ $order->order_number }}</span>
            <small class="ord-sub">{{ $order->display_payment ?: '—' }}</small>
        </div>
        <div class="ord-cell">
            <span class="ord-customer-name">{{ $order->display_customer }}</span>
            <small class="ord-sub">{{ $phone }}</small>
        </div>
        <div class="ord-cell">
            <span class="ord-date">{{ $order->display_date_only }}</span>
            <small class="ord-sub">{{ $order->display_time }}</small>
        </div>
        <div class="ord-cell"><span class="ord-total">{{ $order->display_total }}</span></div>
        <div class="ord-cell">
            <div class="ord-select-wrap ord-sel-{{ $order->status }}">
                <select class="ord-status-select"
                        onchange="document.getElementById('sv-{{ $order->id }}').value=this.value; document.getElementById('sf-{{ $order->id }}').submit();"
                        aria-label="Status {{ $order->order_number }}">
                    @foreach ($statusOptions as $opt)
                        @if($opt['value']!=='all')
                        <option value="{{ $opt['value'] }}" @selected($order->status===$opt['value'])>{{ $opt['label'] }}</option>
                        @endif
                    @endforeach
                </select>
                <svg class="ord-sel-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
            </div>
        </div>
        <div class="ord-cell ord-actions">
            <button type="button" class="ord-action-detail" onclick="openDetail(this.closest('.ord-row'))">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Detail
            </button>
            @if($order->status==='pending')
            <form method="post" action="{{ route('seller.orders.update-status',$order->id) }}" style="display:contents">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="processing">
                <button type="submit" class="ord-icon-btn ord-icon-green" title="Proses">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </form>
            <form method="post" action="{{ route('seller.orders.update-status',$order->id) }}" onsubmit="return confirm('Batalkan pesanan ini?')" style="display:contents">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="ord-icon-btn ord-icon-red" title="Batalkan">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="ord-empty">
        <p>Tidak ada pesanan yang sesuai filter.</p>
    </div>
    @endforelse
</div>

{{-- ── MODAL DETAIL ─────────────────────────────────────────────── --}}
<div id="detail-overlay" class="mod-overlay" onclick="if(event.target===this)closeDetail()">
    <div class="mod-box" role="dialog" aria-modal="true">
        <div class="mod-header">
            <div>
                <h3 class="mod-title">Detail Pesanan</h3>
                <p class="mod-num" id="mod-num"></p>
            </div>
            <button type="button" class="mod-close" onclick="closeDetail()">✕</button>
        </div>

        <div class="mod-body">
            {{-- Info Pelanggan --}}
            <h4 class="mod-section-title">Informasi Pelanggan</h4>
            <div class="mod-info-grid">
                <div class="mod-info-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span id="mod-name"></span>
                </div>
                <div class="mod-info-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.12 1.18 2 2 0 012.1 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.09a16 16 0 006 6l.46-.46a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    <span id="mod-phone"></span>
                </div>
                <div class="mod-info-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span id="mod-addr"></span>
                </div>
            </div>

            {{-- Item Pesanan --}}
            <h4 class="mod-section-title" style="margin-top:18px">Item Pesanan</h4>
            <div id="mod-items" class="mod-items"></div>

            {{-- Total --}}
            <div class="mod-total-row">
                <div>
                    <span class="mod-total-label">Total</span>
                    <small id="mod-payment" class="mod-payment-method"></small>
                </div>
                <span id="mod-total" class="mod-total-value"></span>
            </div>

            {{-- Dynamic action area (changes per status) --}}
            <div id="mod-action-area"></div>

            {{-- Status manual --}}
            <form id="mod-status-form" method="post">
                @csrf @method('PATCH')
                <label class="mod-status-label">Update Status Manual</label>
                <select name="status" id="mod-status-sel" class="mod-status-sel"
                        onchange="this.form.submit()">
                    @foreach ($statusOptions as $opt)
                        @if($opt['value']!=='all')
                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                        @endif
                    @endforeach
                </select>
            </form>

            {{-- WhatsApp --}}
            <a id="mod-wa-btn" href="#" target="_blank" rel="noopener" class="mod-btn mod-btn-wa">
                <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>

            {{-- Cetak Invoice --}}
            <button type="button" onclick="printInvoice()" class="mod-btn mod-btn-black">
                Cetak Invoice
            </button>
        </div>
    </div>
</div>

<style>
/* Filter */
.ord-filter-bar{display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.ord-search-wrap{flex:1;min-width:220px;position:relative;display:flex;align-items:center}
.ord-search-icon{position:absolute;left:14px;width:17px;height:17px;color:#9ca3af;pointer-events:none}
.ord-search-input{width:100%;border:1px solid var(--border);border-radius:12px;padding:11px 14px 11px 40px;font:inherit;font-size:.92rem;background:#fff;color:var(--text)}
.ord-search-input:focus{outline:2px solid rgba(217,119,6,.22);border-color:rgba(217,119,6,.45)}
.ord-filter-right{display:flex;align-items:center;gap:10px}
.ord-status-filter{appearance:none;border:1px solid var(--border);border-radius:10px;padding:10px 36px 10px 14px;font:inherit;font-size:.88rem;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 10px center;background-size:16px;cursor:pointer}
.ord-apply-btn{padding:10px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font:inherit;font-weight:700;font-size:.88rem;cursor:pointer}
/* Table */
.ord-table-card{padding:0;overflow:hidden}
.ord-thead{display:grid;grid-template-columns:1.6fr 1.4fr 1.2fr 1fr 1.2fr 1.1fr;gap:12px;padding:13px 22px;background:rgba(247,241,232,.7);border-bottom:1px solid var(--border);color:var(--muted);font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em}
.ord-row{display:grid;grid-template-columns:1.6fr 1.4fr 1.2fr 1fr 1.2fr 1.1fr;gap:12px;align-items:center;padding:15px 22px;border-bottom:1px solid rgba(109,74,31,.08);transition:background .15s}
.ord-row:last-child{border-bottom:none}
.ord-row:hover{background:rgba(245,158,11,.04)}
.ord-cell{display:grid;gap:3px}
.ord-order-id{font-weight:700;font-size:.92rem;color:#2563eb}
.ord-customer-name{font-weight:600;font-size:.92rem}
.ord-date{font-size:.88rem;font-weight:600}
.ord-total{font-weight:800;font-size:.92rem}
.ord-sub{font-size:.8rem;color:var(--muted)}
/* Status select */
.ord-select-wrap{position:relative;display:inline-flex;align-items:center;border-radius:999px}
.ord-status-select{appearance:none;border-radius:999px;padding:5px 28px 5px 12px;font:inherit;font-size:.8rem;font-weight:700;border:none;cursor:pointer;outline:none;background:transparent}
.ord-sel-chevron{position:absolute;right:8px;pointer-events:none;width:12px;height:12px;opacity:.7}
.ord-sel-pending{background:rgba(251,191,36,.2);color:#92400e}
.ord-sel-pending .ord-status-select{color:#92400e}
.ord-sel-processing{background:rgba(37,99,235,.12);color:#1d4ed8}
.ord-sel-processing .ord-status-select{color:#1d4ed8}
.ord-sel-ready{background:rgba(124,58,237,.12);color:#6d28d9}
.ord-sel-ready .ord-status-select{color:#6d28d9}
.ord-sel-shipping{background:rgba(8,145,178,.12);color:#0e7490}
.ord-sel-shipping .ord-status-select{color:#0e7490}
.ord-sel-delivered{background:rgba(22,163,74,.12);color:#15803d}
.ord-sel-delivered .ord-status-select{color:#15803d}
.ord-sel-cancelled{background:rgba(220,38,38,.12);color:#b91c1c}
.ord-sel-cancelled .ord-status-select{color:#b91c1c}
/* Actions */
.ord-actions{display:flex;align-items:center;gap:8px}
.ord-action-detail{display:inline-flex;align-items:center;gap:5px;font-size:.8rem;font-weight:700;color:#d97706;text-decoration:none;padding:5px 11px;border-radius:8px;background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.2);cursor:pointer;font:inherit;white-space:nowrap;transition:background .15s}
.ord-action-detail svg{width:13px;height:13px}
.ord-action-detail:hover{background:rgba(217,119,6,.18)}
.ord-icon-btn{width:30px;height:30px;border-radius:50%;border:none;display:grid;place-items:center;cursor:pointer;flex-shrink:0}
.ord-icon-btn svg{width:14px;height:14px}
.ord-icon-green{background:rgba(22,163,74,.15);color:#15803d}
.ord-icon-red{background:rgba(220,38,38,.12);color:#b91c1c}
.ord-empty{padding:48px 24px;text-align:center;color:var(--muted)}
/* Modal */
.mod-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:100;align-items:center;justify-content:center;padding:16px}
.mod-overlay.is-open{display:flex}
.mod-box{background:#fff;border-radius:20px;width:min(520px,100%);max-height:90vh;overflow-y:auto;box-shadow:0 24px 80px rgba(0,0,0,.18)}
.mod-header{display:flex;justify-content:space-between;align-items:flex-start;padding:22px 24px 16px;border-bottom:1px solid #f0ebe3}
.mod-title{margin:0;font-size:1.15rem;font-weight:800}
.mod-num{margin:4px 0 0;color:#6b7280;font-size:.88rem}
.mod-close{background:none;border:none;font-size:1.2rem;cursor:pointer;color:#9ca3af;width:32px;height:32px;border-radius:8px;display:grid;place-items:center}
.mod-close:hover{background:#f3f4f6}
.mod-body{padding:20px 24px;display:grid;gap:12px}
.mod-section-title{margin:0 0 10px;font-size:.92rem;font-weight:800;color:var(--text)}
.mod-info-grid{display:grid;gap:8px}
.mod-info-row{display:flex;align-items:flex-start;gap:10px;font-size:.9rem}
.mod-info-row svg{width:16px;height:16px;flex-shrink:0;stroke:#9ca3af;margin-top:2px}
.mod-items{display:grid;gap:8px}
.mod-item-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;font-size:.88rem}
.mod-item-name{font-weight:600}
.mod-item-sub{color:#6b7280;font-size:.8rem;margin-top:2px}
.mod-item-price{font-weight:700;white-space:nowrap}
.mod-total-row{display:flex;justify-content:space-between;align-items:flex-end;padding:14px 0;border-top:2px solid #f0ebe3;border-bottom:1px solid #f0ebe3}
.mod-total-label{font-weight:800;font-size:1rem}
.mod-payment-method{display:block;color:#6b7280;font-size:.8rem;margin-top:2px}
.mod-total-value{font-size:1.2rem;font-weight:900;color:#d97706}
.mod-quick-row{display:flex;gap:10px}
.mod-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:12px;font:inherit;font-weight:700;font-size:.92rem;cursor:pointer;border:none;text-decoration:none;transition:opacity .15s}
.mod-btn:hover{opacity:.85}
.mod-btn-green{background:#16a34a;color:#fff}
.mod-btn-red{background:#dc2626;color:#fff}
.mod-btn-violet{background:#7c3aed;color:#fff}
.mod-btn-blue{background:#0891b2;color:#fff}
.mod-btn-wa{background:#25d366;color:#fff}
.mod-btn-black{background:#111827;color:#fff}
.mod-status-label{display:block;font-size:.82rem;font-weight:700;color:#6b7280;margin-bottom:6px}
.mod-status-sel{width:100%;border:1px solid #e5e7eb;border-radius:10px;padding:10px 14px;font:inherit;font-size:.92rem;background:#f9fafb}
@media(max-width:1120px){.ord-thead,.ord-row{grid-template-columns:1.2fr 1.2fr 1fr 1fr}}
@media(max-width:768px){.ord-thead{display:none}.ord-row{grid-template-columns:1fr 1fr;row-gap:8px}.ord-filter-bar{flex-direction:column;align-items:stretch}}
</style>

<script>
const STATUS_ACTIONS = {
    pending: [
        { nextStatus:'processing', label:'Terima Pesanan',   cls:'mod-btn-green', icon:'check',  confirm: false },
        { nextStatus:'cancelled',  label:'Tolak Pesanan',    cls:'mod-btn-red',   icon:'x',      confirm: true  },
    ],
    processing: [
        { nextStatus:'ready',      label:'Tandai Siap Dikirim', cls:'mod-btn-violet', icon:'box',    confirm: false },
    ],
    ready: [
        { nextStatus:'shipping',   label:'Tandai Dikirim',  cls:'mod-btn-blue',  icon:'truck',  confirm: false },
    ],
    shipping: [
        { nextStatus:'delivered',  label:'Tandai Selesai',  cls:'mod-btn-green', icon:'check',  confirm: false },
    ],
};

const SVG_ICONS = {
    check: '<polyline points="20 6 9 17 4 12"/>',
    x:     '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
    box:   '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>',
    truck: '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
};

function buildActionArea(status, baseUrl, orderId) {
    const actions = STATUS_ACTIONS[status] || [];
    if (!actions.length) return '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}';

    const formsHtml = actions.map(a => {
        const onsubmit = a.confirm ? `onsubmit="return confirm('${a.label}?')"` : '';
        return `
        <form method="post" action="${baseUrl}${orderId}/status"
              style="flex:1;min-width:0" ${onsubmit}>
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="status" value="${a.nextStatus}">
            <button type="submit" class="mod-btn ${a.cls}" style="width:100%">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     width="16" height="16">${SVG_ICONS[a.icon]}</svg>
                ${a.label}
            </button>
        </form>`;
    }).join('');

    return `<div style="display:flex;gap:10px;">${formsHtml}</div>`;
}

function openDetail(row) {
    const d = row.dataset;
    document.getElementById('mod-num').textContent     = d.num;
    document.getElementById('mod-name').textContent    = d.customer;
    document.getElementById('mod-phone').textContent   = d.phone;
    document.getElementById('mod-addr').textContent    = d.addr;
    document.getElementById('mod-total').textContent   = d.total;
    document.getElementById('mod-payment').textContent = 'Pembayaran: ' + d.payment;

    // Items
    const items = JSON.parse(d.items || '[]');
    document.getElementById('mod-items').innerHTML = items.map(i =>
        `<div class="mod-item-row">
            <div><div class="mod-item-name">${i.name}</div>
            <div class="mod-item-sub">${i.qty} × ${i.price}</div></div>
            <span class="mod-item-price">${i.sub}</span>
        </div>`).join('');

    // Dynamic action buttons
    const baseUrl = '{{ url("/penjual/pesanan") }}/';
    document.getElementById('mod-action-area').innerHTML = buildActionArea(d.status, baseUrl, d.id);

    // Status form
    document.getElementById('mod-status-form').action = baseUrl + d.id + '/status';
    document.getElementById('mod-status-sel').value   = d.status;

    // WhatsApp
    document.getElementById('mod-wa-btn').href = 'https://wa.me/' + d.wa.replace(/\D/g,'');

    document.getElementById('detail-overlay').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closeDetail() {
    document.getElementById('detail-overlay').classList.remove('is-open');
    document.body.style.overflow = '';
}

function printInvoice() { window.print(); }

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>

@endsection