@extends('seller.layout')

@push('scripts')
<script>
    // Print handler
    document.getElementById('btn-print')?.addEventListener('click', () => window.print());

    // Simple CSV/Excel export
    document.getElementById('btn-excel')?.addEventListener('click', () => {
        const rows = [['Tanggal', 'Pesanan', 'Pendapatan']];
        @foreach ($salesByDate as $row)
            rows.push(['{{ $row['date'] }}', '{{ $row['orders'] }}', '{{ $row['raw_revenue'] }}']);
        @endforeach
        const csv = rows.map(r => r.join(',')).join('\n');
        const a   = document.createElement('a');
        a.href     = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        a.download = 'laporan-penjualan.csv';
        a.click();
    });
</script>
@endpush

@section('content')

{{-- ── Period Filter ─────────────────────────────────────────────── --}}
<div class="rpt-filter-card">
    <form method="get" id="period-form" class="rpt-filter-row">
        <div class="rpt-filter-group">
            <label class="rpt-label" for="period-select">Periode Laporan</label>
            <div class="rpt-select-wrap">
                <select id="period-select" name="period" class="rpt-select"
                        onchange="document.getElementById('period-form').submit()">
                    <option value="today"  @selected($period === 'today') >Hari Ini</option>
                    <option value="week"   @selected($period === 'week')  >7 Hari Terakhir</option>
                    <option value="month"  @selected($period === 'month') >30 Hari Terakhir</option>
                    <option value="all"    @selected($period === 'all')   >Semua Waktu</option>
                </select>
                <svg class="rpt-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 8l5 5 5-5"/>
                </svg>
            </div>
        </div>

        <div class="rpt-action-row">
            <button id="btn-print" type="button" class="rpt-action-btn rpt-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
                Print
            </button>
            <a href="{{ route('seller.reports', ['period' => $period, 'format' => 'pdf']) }}"
               class="rpt-action-btn rpt-btn-red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                PDF
            </a>
            <button id="btn-excel" type="button" class="rpt-action-btn rpt-btn-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Excel
            </button>
        </div>
    </form>
</div>

{{-- ── Stat Cards ────────────────────────────────────────────────── --}}
<div class="rpt-stats-grid">
    @foreach ([
        [
            'label' => 'Total Pendapatan',
            'value' => $reportStats[0]['value'] ?? 'Rp 0',
            'color' => '#16a34a',
            'bg'    => 'rgba(22,163,74,.10)',
            'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M12 6v2m0 8v2m-4-6h1a2 2 0 000-4H9m6 4h-1a2 2 0 010-4H15"/>',
        ],
        [
            'label' => 'Total Pesanan',
            'value' => $reportStats[1]['value'] ?? '0',
            'color' => '#2563eb',
            'bg'    => 'rgba(37,99,235,.10)',
            'icon'  => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        ],
        [
            'label' => 'Total Item Terjual',
            'value' => $reportStats[2]['value'] ?? '0',
            'color' => '#7c3aed',
            'bg'    => 'rgba(124,58,237,.10)',
            'icon'  => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.97-1.67L23 6H6"/>',
        ],
        [
            'label' => 'Rata-rata Pesanan',
            'value' => $reportStats[3]['value'] ?? 'Rp 0',
            'color' => '#d97706',
            'bg'    => 'rgba(217,119,6,.10)',
            'icon'  => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        ],
    ] as $stat)
    <article class="rpt-stat-card" style="--stat-color:{{ $stat['color'] }}; --stat-bg:{{ $stat['bg'] }};">
        <div class="rpt-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                {!! $stat['icon'] !!}
            </svg>
        </div>
        <div class="rpt-stat-label">{{ $stat['label'] }}</div>
        <div class="rpt-stat-value">{{ $stat['value'] }}</div>
    </article>
    @endforeach
</div>

{{-- ── Two-column Body ───────────────────────────────────────────── --}}
<div class="rpt-body-grid">

    {{-- Sales by Date --}}
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Penjualan per Tanggal</h2>
                <p>Periode: {{ collect(['today'=>'Hari Ini','week'=>'7 Hari Terakhir','month'=>'30 Hari Terakhir','all'=>'Semua Waktu'])[$period] ?? $period }}</p>
            </div>
        </div>

        <div class="rpt-date-list">
            @forelse ($salesByDate as $row)
                <div class="rpt-date-row">
                    <div>
                        <div class="rpt-date-title">{{ $row['date'] }}</div>
                        <div class="rpt-date-meta">{{ $row['orders'] }} pesanan</div>
                    </div>
                    <span class="rpt-revenue">{{ $row['revenue'] }}</span>
                </div>
            @empty
                <p class="seller-empty">Belum ada data pada periode ini.</p>
            @endforelse
        </div>
    </section>

    {{-- Top Products --}}
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Produk Terlaris</h2>
                <p>Berdasarkan transaksi aktif.</p>
            </div>
        </div>

        <div class="rpt-rank-list">
            @forelse ($topProducts as $i => $product)
                <div class="rpt-rank-row">
                    <span class="rpt-rank-badge
                        {{ $i === 0 ? 'rpt-rank-gold' : ($i === 1 ? 'rpt-rank-silver' : ($i === 2 ? 'rpt-rank-bronze' : '')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="rpt-rank-info">
                        <div class="rpt-rank-name">{{ $product['name'] }}</div>
                        <div class="rpt-rank-sub">{{ $product['quantity'] }} terjual</div>
                    </div>
                    <span class="rpt-revenue">{{ $product['revenue'] }}</span>
                </div>
            @empty
                <p class="seller-empty">Belum ada produk terlaris.</p>
            @endforelse
        </div>
    </section>

</div>

{{-- ── Report Notes CRUD ─────────────────────────────────────────── --}}
<section class="seller-card">
    <div class="seller-section-head">
        <div>
            <h2>Catatan Laporan</h2>
            <p>Simpan insight periodik agar bisa ditinjau kembali.</p>
        </div>
    </div>

    <form method="post" action="{{ route('seller.reports.store') }}" class="seller-form" style="margin-bottom:1.25rem;">
        @csrf
        <div class="rpt-note-grid">
            <label class="seller-field">
                <span>Judul</span>
                <input type="text" name="title" placeholder="Contoh: Penjualan Mei 2026"
                       @class(['is-invalid' => $errors->has('title')]) required>
                @error('title')<small class="seller-field-error">{{ $message }}</small>@enderror
            </label>
            <label class="seller-field">
                <span>Periode</span>
                <select name="period" @class(['is-invalid' => $errors->has('period')]) required>
                    <option value="today">Hari Ini</option>
                    <option value="week">7 Hari</option>
                    <option value="month" selected>30 Hari</option>
                    <option value="all">Semua</option>
                </select>
                @error('period')<small class="seller-field-error">{{ $message }}</small>@enderror
            </label>
        </div>
        <label class="seller-field">
            <span>Catatan</span>
            <textarea name="content" rows="3" placeholder="Tulis ringkasan atau insight..."
                      @class(['is-invalid' => $errors->has('content')]) required></textarea>
            @error('content')<small class="seller-field-error">{{ $message }}</small>@enderror
        </label>
        <div>
            <button class="seller-button seller-button-primary" type="submit">Tambah Catatan</button>
        </div>
    </form>

    <div class="seller-stack">
        @forelse ($reportNotes as $note)
            <article class="seller-table-card">
                <form method="post" action="{{ route('seller.reports.update', $note->id) }}" class="seller-form">
                    @csrf @method('PUT')
                    <div class="rpt-note-grid">
                        <label class="seller-field">
                            <span>Judul</span>
                            <input type="text" name="title" value="{{ $note->title }}" required>
                        </label>
                        <label class="seller-field">
                            <span>Periode</span>
                            <select name="period" required>
                                <option value="today" @selected($note->period==='today')>Hari Ini</option>
                                <option value="week"  @selected($note->period==='week') >7 Hari</option>
                                <option value="month" @selected($note->period==='month')>30 Hari</option>
                                <option value="all"   @selected($note->period==='all')  >Semua</option>
                            </select>
                        </label>
                    </div>
                    <label class="seller-field">
                        <span>Catatan</span>
                        <textarea name="content" rows="2" required>{{ $note->content }}</textarea>
                    </label>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <button class="seller-button seller-button-primary" type="submit" style="font-size:.88rem;padding:9px 16px;">Simpan</button>
                        <small class="rpt-note-date">{{ optional($note->created_at)->translatedFormat('d M Y') }}</small>
                    </div>
                </form>
                <form method="post" action="{{ route('seller.reports.destroy', $note->id) }}"
                      onsubmit="return confirm('Hapus catatan ini?')" style="margin-top:.5rem;">
                    @csrf @method('DELETE')
                    <button class="seller-button rpt-btn-delete" type="submit">Hapus</button>
                </form>
            </article>
        @empty
            <p class="seller-empty">Belum ada catatan laporan.</p>
        @endforelse
    </div>

    <div style="margin-top:1rem;">{{ $reportNotes->links() }}</div>
</section>

{{-- ── Page-specific styles ──────────────────────────────────────── --}}
<style>
/* Filter Card */
.rpt-filter-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px 24px;
    box-shadow: 0 2px 12px rgba(78,43,17,.07);
}
.rpt-filter-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.rpt-filter-group { display: grid; gap: 6px; }
.rpt-label { font-size: .82rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; }
.rpt-select-wrap { position: relative; display: inline-flex; align-items: center; }
.rpt-select {
    appearance: none;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 11px 42px 11px 16px;
    font: inherit;
    font-size: .98rem;
    background: rgba(255,255,255,.95);
    color: var(--text);
    min-width: 220px;
    cursor: pointer;
}
.rpt-select:focus { outline: 2px solid rgba(217,119,6,.25); border-color: rgba(217,119,6,.45); }
.rpt-chevron {
    position: absolute; right: 14px; pointer-events: none;
    width: 16px; height: 16px; color: var(--muted);
}
.rpt-action-row { display: flex; gap: 10px; align-items: center; }
.rpt-action-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 18px; border-radius: 10px; font: inherit;
    font-weight: 700; font-size: .88rem; border: 1px solid transparent;
    text-decoration: none; cursor: pointer; transition: opacity .15s;
}
.rpt-action-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
.rpt-action-btn:hover { opacity: .82; }
.rpt-btn-ghost { background: #fff; border-color: var(--border); color: var(--text); }
.rpt-btn-red   { background: #fee2e2; color: #b91c1c; }
.rpt-btn-green { background: #dcfce7; color: #166534; }

/* Stat Cards */
.rpt-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
.rpt-stat-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 22px;
    display: grid;
    gap: 8px;
    box-shadow: 0 2px 12px rgba(78,43,17,.06);
    position: relative;
    overflow: hidden;
}
.rpt-stat-card::after {
    content: '';
    position: absolute;
    bottom: -24px; right: -24px;
    width: 90px; height: 90px;
    border-radius: 50%;
    background: var(--stat-color);
    opacity: .08;
    pointer-events: none;
}
.rpt-stat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--stat-bg);
    display: grid; place-items: center;
}
.rpt-stat-icon svg { width: 20px; height: 20px; stroke: var(--stat-color); }
.rpt-stat-label { font-size: .88rem; color: var(--muted); font-weight: 600; }
.rpt-stat-value { font-size: 1.75rem; font-weight: 800; letter-spacing: -.04em; color: var(--text); }

/* Two-column body */
.rpt-body-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Date list */
.rpt-date-list { display: grid; gap: 0; }
.rpt-date-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid rgba(109,74,31,.09);
}
.rpt-date-row:last-child { border-bottom: none; }
.rpt-date-title { font-weight: 700; font-size: .96rem; }
.rpt-date-meta { color: var(--muted); font-size: .85rem; margin-top: 2px; }
.rpt-revenue { font-weight: 700; color: #16a34a; font-size: .95rem; white-space: nowrap; }

/* Rank list */
.rpt-rank-list { display: grid; gap: 0; }
.rpt-rank-row {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 0;
    border-bottom: 1px solid rgba(109,74,31,.09);
}
.rpt-rank-row:last-child { border-bottom: none; }
.rpt-rank-badge {
    width: 30px; height: 30px; border-radius: 50%;
    display: grid; place-items: center;
    font-weight: 800; font-size: .82rem; flex-shrink: 0;
    background: rgba(109,74,31,.1); color: #7a6b5d;
}
.rpt-rank-gold   { background: linear-gradient(135deg,#ffd700,#f59e0b); color: #7c4800; }
.rpt-rank-silver { background: linear-gradient(135deg,#e2e8f0,#94a3b8); color: #334155; }
.rpt-rank-bronze { background: linear-gradient(135deg,#fed7aa,#f97316); color: #7c2d12; }
.rpt-rank-info { flex: 1; min-width: 0; }
.rpt-rank-name { font-weight: 700; font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rpt-rank-sub  { color: var(--muted); font-size: .84rem; }

/* Note form layout */
.rpt-note-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.seller-field { display: grid; gap: 6px; }
.seller-field span { font-weight: 700; color: var(--muted); font-size: .88rem; }
.seller-field input,
.seller-field select,
.seller-field textarea {
    border: 1px solid var(--border); border-radius: 12px;
    padding: 11px 14px; font: inherit; background: rgba(255,255,255,.95);
    color: var(--text); width: 100%;
}
.seller-field input:focus,
.seller-field select:focus,
.seller-field textarea:focus { outline: 2px solid rgba(217,119,6,.22); border-color: rgba(217,119,6,.42); }
.rpt-note-date { color: var(--muted); font-size: .84rem; }
.rpt-btn-delete {
    background: #fee2e2; color: #b91c1c;
    border-radius: 10px; padding: 8px 16px; font: inherit; font-weight: 700; font-size: .84rem;
    border: none; cursor: pointer;
}
.rpt-btn-delete:hover { background: #fecaca; }

/* Print */
@media print {
    .seller-sidebar, .seller-topbar, .rpt-filter-card .rpt-action-row,
    .seller-form, .seller-sidebar-footer { display: none !important; }
    .seller-shell { grid-template-columns: 1fr; }
    .seller-main { padding: 0; }
}

/* Responsive */
@media (max-width: 1120px) {
    .rpt-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .rpt-body-grid  { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .rpt-stats-grid  { grid-template-columns: 1fr; }
    .rpt-note-grid   { grid-template-columns: 1fr; }
    .rpt-filter-row  { flex-direction: column; align-items: stretch; }
    .rpt-action-row  { flex-wrap: wrap; }
    .rpt-select      { min-width: unset; width: 100%; }
}
</style>
@endsection