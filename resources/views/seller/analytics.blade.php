@extends('seller.layout')

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Colour palette ──────────────────────────────────────── */
    const amber     = '#f59e0b';
    const amberLight= 'rgba(245,158,11,.15)';
    const pieColors = ['#7c3aed','#16a34a','#0891b2','#f59e0b','#e11d48','#64748b','#d97706'];

    /* ── 1. Line chart: Pendapatan 7 hari ───────────────────── */
    const lineCtx = document.getElementById('chartLine').getContext('2d');
    const lineGrad = lineCtx.createLinearGradient(0, 0, 0, 260);
    lineGrad.addColorStop(0, 'rgba(245,158,11,.22)');
    lineGrad.addColorStop(1, 'rgba(245,158,11,.00)');

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: @json($dailyRevenue['labels']),
            datasets: [{
                data: @json($dailyRevenue['data']),
                borderColor: amber,
                backgroundColor: lineGrad,
                borderWidth: 2.5,
                pointBackgroundColor: amber,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#7a6b5d', font: { size: 12 } },
                },
                y: {
                    grid: { color: 'rgba(109,74,31,.08)' },
                    border: { display: false },
                    ticks: {
                        color: '#7a6b5d', font: { size: 11 },
                        callback: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v,
                    },
                },
            },
        },
    });

    /* ── 2. Pie chart: Kategori ─────────────────────────────── */
    const pieLabels = @json($categoryPerformance->pluck('name'));
    const pieData   = @json($categoryPerformance->pluck('raw_revenue'));
    const pieCtx    = document.getElementById('chartPie').getContext('2d');

    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: pieColors,
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 8,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#2f2318',
                        font: { size: 12, weight: '600' },
                        padding: 14,
                        boxWidth: 14,
                        boxHeight: 14,
                        borderRadius: 4,
                        generateLabels(chart) {
                            const ds   = chart.data.datasets[0];
                            const total = ds.data.reduce((a, b) => a + b, 0);
                            return chart.data.labels.map((label, i) => ({
                                text: `${label} ${total > 0 ? Math.round(ds.data[i] / total * 100) : 0}%`,
                                fillStyle: ds.backgroundColor[i],
                                strokeStyle: '#fff',
                                lineWidth: 2,
                                index: i,
                            }));
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct   = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                            return ` Rp ${Number(ctx.parsed).toLocaleString('id-ID')} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });

    /* ── 3. Bar chart: Top Produk ───────────────────────────── */
    const barCtx = document.getElementById('chartBar').getContext('2d');
    const barGrad = barCtx.createLinearGradient(0, 0, 0, 280);
    barGrad.addColorStop(0, '#f59e0b');
    barGrad.addColorStop(1, '#d97706');

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: @json($topProducts->pluck('name')),
            datasets: [{
                data: @json($topProducts->pluck('quantity')),
                backgroundColor: barGrad,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 72,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} terjual`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#7a6b5d', font: { size: 12 }, maxRotation: 20 },
                },
                y: {
                    grid: { color: 'rgba(109,74,31,.08)' },
                    border: { display: false },
                    beginAtZero: true,
                    ticks: { color: '#7a6b5d', font: { size: 11 }, precision: 0 },
                },
            },
        },
    });

});
</script>
@endpush

@section('content')

{{-- ── Stat Cards ──────────────────────────────────────────────── --}}
<div class="an-stats-grid">
    @php
    $icons = [
        'dollar' => '<circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v2m0 8v2M9.5 9.5A2.5 2.5 0 0112 7.5h.5a2.5 2.5 0 010 5h-1a2.5 2.5 0 000 5h.5a2.5 2.5 0 002.5-2.5"/>',
        'cart'   => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.97-1.67L23 6H6"/>',
        'bag'    => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>',
        'trend'  => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
    ];
    @endphp

    @foreach ($analyticsStats as $stat)
    <article class="an-stat-card" style="--sc:{{ $stat['color'] }};--sb:{{ $stat['bg'] }};">
        <div class="an-stat-top">
            <div class="an-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    {!! $icons[$stat['icon']] !!}
                </svg>
            </div>
            <span class="an-stat-label">{{ $stat['label'] }}</span>
        </div>
        <div class="an-stat-value">{{ $stat['value'] }}</div>
        @if($stat['change'])
        <div class="an-stat-change {{ $stat['up'] ? 'an-up' : 'an-down' }}">
            @if($stat['up'])
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 4 14 10 2 10"/></svg>
            @else
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 12 14 6 2 6"/></svg>
            @endif
            {{ $stat['change'] }} {{ $stat['detail'] }}
        </div>
        @endif
    </article>
    @endforeach
</div>

{{-- ── Charts Row ──────────────────────────────────────────────── --}}
<div class="an-charts-grid">

    {{-- Line Chart --}}
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Pendapatan 7 Hari Terakhir</h2>
                <p>Tren harian pendapatan bersih toko.</p>
            </div>
        </div>
        <div class="an-chart-wrap an-chart-line">
            <canvas id="chartLine"></canvas>
        </div>
    </section>

    {{-- Pie Chart --}}
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Penjualan per Kategori</h2>
                <p>Distribusi revenue per kategori.</p>
            </div>
        </div>
        @if($categoryPerformance->sum('raw_revenue') > 0)
            <div class="an-chart-wrap an-chart-pie">
                <canvas id="chartPie"></canvas>
            </div>
        @else
            <div class="an-chart-empty">Belum ada data penjualan per kategori.</div>
            {{-- hidden canvas so JS doesn't error --}}
            <canvas id="chartPie" style="display:none;"></canvas>
        @endif
    </section>

</div>

{{-- ── Bar Chart: Top Produk ──────────────────────────────────── --}}
<section class="seller-card">
    <div class="seller-section-head">
        <div>
            <h2>Top {{ $topProducts->count() }} Produk Terlaris</h2>
            <p>Berdasarkan jumlah item terjual dari transaksi aktif.</p>
        </div>
    </div>
    @if($topProducts->isNotEmpty())
        <div class="an-chart-wrap an-chart-bar">
            <canvas id="chartBar"></canvas>
        </div>
    @else
        <div class="an-chart-empty">Belum ada data produk terlaris.</div>
        <canvas id="chartBar" style="display:none;"></canvas>
    @endif
</section>

{{-- ── Insights CRUD ────────────────────────────────────────── --}}
<section class="seller-card">
    <div class="seller-section-head">
        <div>
            <h2>Insight Analytics</h2>
            <p>Simpan metrik penting dan tren untuk monitoring.</p>
        </div>
    </div>

    <form method="post" action="{{ route('seller.analytics.store') }}" class="seller-form" style="margin-bottom:1.25rem;">
        @csrf
        <div class="an-insight-grid">
            <label class="seller-field">
                <span>Judul</span>
                <input type="text" name="title" placeholder="Contoh: Konversi Mei 2026"
                       @class(['is-invalid' => $errors->has('title')]) required>
                @error('title')<small class="seller-field-error">{{ $message }}</small>@enderror
            </label>
            <label class="seller-field">
                <span>Metrik</span>
                <input type="text" name="metric" placeholder="Contoh: conversion_rate"
                       @class(['is-invalid' => $errors->has('metric')]) required>
                @error('metric')<small class="seller-field-error">{{ $message }}</small>@enderror
            </label>
            <label class="seller-field">
                <span>Nilai</span>
                <input type="number" step="0.01" name="value" placeholder="0.00"
                       @class(['is-invalid' => $errors->has('value')]) required>
                @error('value')<small class="seller-field-error">{{ $message }}</small>@enderror
            </label>
            <label class="seller-field">
                <span>Trend</span>
                <select name="trend" @class(['is-invalid' => $errors->has('trend')]) required>
                    <option value="up">↑ Naik</option>
                    <option value="down">↓ Turun</option>
                    <option value="stable">→ Stabil</option>
                </select>
                @error('trend')<small class="seller-field-error">{{ $message }}</small>@enderror
            </label>
        </div>
        <label class="seller-field">
            <span>Catatan</span>
            <textarea name="note" rows="2" placeholder="Keterangan tambahan..."
                      @class(['is-invalid' => $errors->has('note')])></textarea>
            @error('note')<small class="seller-field-error">{{ $message }}</small>@enderror
        </label>
        <div>
            <button class="seller-button seller-button-primary" type="submit">Tambah Insight</button>
        </div>
    </form>

    <div class="seller-stack">
        @forelse ($insights as $insight)
            <article class="seller-table-card">
                <form method="post" action="{{ route('seller.analytics.update', $insight->id) }}" class="seller-form">
                    @csrf @method('PUT')
                    <div class="an-insight-grid">
                        <label class="seller-field">
                            <span>Judul</span>
                            <input type="text" name="title" value="{{ $insight->title }}" required>
                        </label>
                        <label class="seller-field">
                            <span>Metrik</span>
                            <input type="text" name="metric" value="{{ $insight->metric }}" required>
                        </label>
                        <label class="seller-field">
                            <span>Nilai</span>
                            <input type="number" step="0.01" name="value" value="{{ $insight->value }}" required>
                        </label>
                        <label class="seller-field">
                            <span>Trend</span>
                            <select name="trend" required>
                                <option value="up"     @selected($insight->trend==='up')    >↑ Naik</option>
                                <option value="down"   @selected($insight->trend==='down')  >↓ Turun</option>
                                <option value="stable" @selected($insight->trend==='stable')>→ Stabil</option>
                            </select>
                        </label>
                    </div>
                    <label class="seller-field">
                        <span>Catatan</span>
                        <textarea name="note" rows="2">{{ $insight->note }}</textarea>
                    </label>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <button class="seller-button seller-button-primary" type="submit"
                                style="font-size:.85rem;padding:9px 16px;">Simpan</button>
                        <small style="color:var(--muted);font-size:.83rem;">{{ optional($insight->created_at)->translatedFormat('d M Y') }}</small>
                    </div>
                </form>
                <form method="post" action="{{ route('seller.analytics.destroy', $insight->id) }}"
                      onsubmit="return confirm('Hapus insight ini?')" style="margin-top:.5rem;">
                    @csrf @method('DELETE')
                    <button style="background:#fee2e2;color:#b91c1c;border:none;border-radius:10px;
                                   padding:7px 14px;font:inherit;font-weight:700;font-size:.83rem;cursor:pointer;">
                        Hapus
                    </button>
                </form>
            </article>
        @empty
            <p class="seller-empty">Belum ada insight analytics tersimpan.</p>
        @endforelse
    </div>
    <div style="margin-top:1rem;">{{ $insights->links() }}</div>
</section>

{{-- ── Page styles ─────────────────────────────────────────────── --}}
<style>
/* Stat grid */
.an-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
.an-stat-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px 22px;
    display: grid;
    gap: 6px;
    box-shadow: 0 2px 12px rgba(78,43,17,.06);
    position: relative; overflow: hidden;
}
.an-stat-card::after {
    content: '';
    position: absolute; bottom: -22px; right: -22px;
    width: 80px; height: 80px; border-radius: 50%;
    background: var(--sc); opacity: .09; pointer-events: none;
}
.an-stat-top   { display: flex; align-items: center; gap: 10px; }
.an-stat-icon  {
    width: 36px; height: 36px; border-radius: 9px;
    background: var(--sb); display: grid; place-items: center; flex-shrink: 0;
}
.an-stat-icon svg { width: 18px; height: 18px; stroke: var(--sc); }
.an-stat-label { font-size: .84rem; color: var(--muted); font-weight: 600; }
.an-stat-value {
    font-size: 1.8rem; font-weight: 800;
    letter-spacing: -.04em; color: var(--text); line-height: 1;
}
.an-stat-change {
    display: flex; align-items: center; gap: 4px;
    font-size: .8rem; font-weight: 700;
}
.an-stat-change svg { width: 13px; height: 13px; flex-shrink: 0; }
.an-up   { color: #16a34a; }
.an-down { color: #dc2626; }

/* Charts */
.an-charts-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    gap: 20px;
}
.an-chart-wrap { position: relative; }
.an-chart-line { height: 260px; }
.an-chart-pie  { height: 260px; }
.an-chart-bar  { height: 280px; }
.an-chart-empty {
    color: var(--muted); font-size: .9rem;
    padding: 32px 0; text-align: center;
}

/* Insight form grid */
.an-insight-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 14px;
}
.seller-field { display: grid; gap: 6px; }
.seller-field span {
    font-weight: 700; color: var(--muted); font-size: .85rem;
}
.seller-field input,
.seller-field select,
.seller-field textarea {
    border: 1px solid var(--border); border-radius: 12px;
    padding: 10px 13px; font: inherit;
    background: rgba(255,255,255,.95); color: var(--text); width: 100%;
}
.seller-field input:focus,
.seller-field select:focus,
.seller-field textarea:focus {
    outline: 2px solid rgba(217,119,6,.22);
    border-color: rgba(217,119,6,.42);
}
.seller-field input.is-invalid,
.seller-field select.is-invalid,
.seller-field textarea.is-invalid { border-color: rgba(220,38,38,.5); }

/* Responsive */
@media (max-width: 1120px) {
    .an-stats-grid, .an-charts-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .an-stats-grid   { grid-template-columns: 1fr; }
    .an-charts-grid  { grid-template-columns: 1fr; }
    .an-insight-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endsection