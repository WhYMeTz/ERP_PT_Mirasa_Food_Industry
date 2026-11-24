@extends('layouts.app')
@section('title', 'Dashboard — ERP PT Mirasa Food Industry')

@push('styles')
<style>
    .dash { max-width: 1400px; margin: 0 auto; }
    .dash-head { display:flex; flex-wrap:wrap; gap:.75rem; align-items:flex-end; justify-content:space-between; margin-bottom:1.1rem; }
    .dash-head h1 { font-size:1.35rem; font-weight:800; color:#0f172a; margin:0; }
    .dash-head p  { margin:.15rem 0 0; font-size:.82rem; color:#64748b; }
    .dash-filter { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
    .dash-lbl { font-size:.75rem; font-weight:700; color:#64748b; display:flex; align-items:center; gap:.35rem; }
    .dash-filter input[type=date] { padding:.38rem .55rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.82rem; background:#fff; color:#0f172a; }
    .dash-btn { padding:.45rem .95rem; border:0; border-radius:8px; background:#0369a1; color:#fff; font-weight:700; font-size:.82rem; cursor:pointer; }
    .dash-btn:hover { background:#075985; }
    .dash-presets { width:100%; display:flex; gap:.4rem; flex-wrap:wrap; justify-content:flex-end; }
    .dash-presets a { font-size:.74rem; font-weight:700; padding:.22rem .65rem; border-radius:999px; background:#f1f5f9; color:#475569; text-decoration:none; }
    .dash-presets a:hover { background:#e2e8f0; }
    .dash-presets a.on { background:#e0f2fe; color:#0369a1; }
    .dash-filter select { padding:.42rem .7rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.82rem; background:#fff; color:#0f172a; }
    .dash-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:.8rem; margin-bottom:1rem; }
    .dash-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:.85rem 1rem; border-left:4px solid var(--c); }
    .dash-card .lbl { font-size:.72rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#64748b; }
    .dash-card .val { font-size:1.35rem; font-weight:800; color:#0f172a; margin-top:.2rem; font-variant-numeric:tabular-nums; }
    .dash-card .sub { font-size:.75rem; color:#64748b; margin-top:.1rem; }
    .dash-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.9rem; }
    @media (max-width:1100px){ .dash-grid{ grid-template-columns:minmax(0,1fr);} }
    .dash-panel { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1rem 1.1rem 1.1rem; }
    .dash-panel h2 { font-size:.95rem; font-weight:800; color:#0f172a; margin:0; }
    .dash-panel .note { font-size:.74rem; color:#64748b; margin:.15rem 0 .7rem; }
    .dash-chart { position:relative; height:300px; }
    .dash-empty { color:#94a3b8; font-size:.85rem; text-align:center; padding:3rem 0; }
</style>
@endpush

@section('content')
@php
    $rp = function ($n) {
        $n = (float) $n;
        if (abs($n) >= 1e9) return 'Rp ' . number_format($n / 1e9, 2, ',', '.') . ' M';
        if (abs($n) >= 1e6) return 'Rp ' . number_format($n / 1e6, 1, ',', '.') . ' jt';
        return 'Rp ' . number_format($n, 0, ',', '.');
    };
    $kg = fn($n) => number_format((float) $n, 0, ',', '.') . ' kg';
@endphp
<div class="dash">
    <div class="dash-head">
        <div>
            <h1>Dashboard</h1>
            <p>PT Mirasa Food Industry · Periode {{ $periode }}</p>
        </div>
        <form method="GET" class="dash-filter" id="dashFilter">
            <label class="dash-lbl">Dari <input type="date" name="dari" value="{{ $dari }}" max="{{ now()->toDateString() }}"></label>
            <label class="dash-lbl">Sampai <input type="date" name="sampai" value="{{ $sampai }}"></label>
            @if($bolehProd)
            <select name="pabrik" title="Berlaku untuk Produksi & HPP">
                <option value="">Semua pabrik</option>
                <option value="BARAT" @selected($pabrik === 'BARAT')>Pabrik Barat</option>
                <option value="TIMUR" @selected($pabrik === 'TIMUR')>Pabrik Timur</option>
            </select>
            @endif
            <button type="submit" class="dash-btn">Terapkan</button>
        </form>
        <div class="dash-presets">
            @php
                $t = now();
                $preset = [
                    'Bulan ini'  => [$t->copy()->startOfMonth(), $t],
                    '3 bulan'    => [$t->copy()->subMonthsNoOverflow(2)->startOfMonth(), $t],
                    'Tahun ini'  => [$t->copy()->startOfYear(), $t],
                    'Tahun lalu' => [$t->copy()->subYear()->startOfYear(), $t->copy()->subYear()->endOfYear()],
                ];
            @endphp
            @foreach($preset as $nama => [$a, $b])
                <a href="{{ request()->fullUrlWithQuery(['dari' => $a->toDateString(), 'sampai' => $b->toDateString()]) }}"
                   class="{{ $dari === $a->toDateString() && $sampai === $b->toDateString() ? 'on' : '' }}">{{ $nama }}</a>
            @endforeach
        </div>
    </div>

    <div class="dash-cards">
        @if($bolehBeli)
        <div class="dash-card" style="--c:#0ea5e9">
            <div class="lbl">Pembelian</div>
            <div class="val">{{ $rp($kartu['pembelian']['nilai']) }}</div>
            <div class="sub">{{ number_format($kartu['pembelian']['dok']) }} penerimaan barang</div>
        </div>
        @endif
        @if($bolehJual)
        <div class="dash-card" style="--c:#16a34a">
            <div class="lbl">Penjualan</div>
            <div class="val">{{ $rp($kartu['penjualan']['nilai']) }}</div>
            <div class="sub">{{ number_format($kartu['penjualan']['dok']) }} transaksi</div>
        </div>
        @endif
        @if($bolehProd)
        <div class="dash-card" style="--c:#f59e0b">
            <div class="lbl">Hasil Produksi (WIP)</div>
            <div class="val">{{ $kg($kartu['produksi']['kg']) }}</div>
            <div class="sub">{{ number_format($kartu['produksi']['dok']) }} dokumen produksi</div>
        </div>
        <div class="dash-card" style="--c:#8b5cf6">
            <div class="lbl">HPP / kg Terakhir</div>
            <div class="val">{{ $kartu['hpp']['terakhir'] !== null ? 'Rp ' . number_format($kartu['hpp']['terakhir'], 0, ',', '.') : '—' }}</div>
            <div class="sub">Rata-rata periode: {{ $kartu['hpp']['rata'] !== null ? 'Rp ' . number_format($kartu['hpp']['rata'], 0, ',', '.') : '—' }}</div>
        </div>
        @endif
    </div>

    <div class="dash-grid">
        @if($bolehBeli)
        <div class="dash-panel">
            <h2>Pembelian {{ $harian ? 'per Hari' : 'per Bulan' }}</h2>
            <div class="note">Total tagihan penerimaan barang (GRN), tidak termasuk dokumen void/dibatalkan.</div>
            <div class="dash-chart"><canvas id="chBeli"></canvas></div>
        </div>
        @endif
        @if($bolehJual)
        <div class="dash-panel">
            <h2>Penjualan {{ $harian ? 'per Hari' : 'per Bulan' }}</h2>
            <div class="note">Omzet pesanan non-batal, dibedakan PO dan Penjualan Langsung.</div>
            <div class="dash-chart"><canvas id="chJual"></canvas></div>
        </div>
        @endif
        @if($bolehProd)
        <div class="dash-panel">
            <h2>Hasil Produksi {{ $harian ? 'per Hari' : 'per Bulan' }}</h2>
            <div class="note">Total WIP hasil timbangan (kg).</div>
            <div class="dash-chart"><canvas id="chProd"></canvas></div>
        </div>
        <div class="dash-panel">
            <h2>Kurva HPP per Kg</h2>
            <div class="note">Rata-rata tertimbang volume, hanya dokumen yang HPP-nya sudah terhitung. Area = rentang terendah–tertinggi.</div>
            <div class="dash-chart"><canvas id="chHpp"></canvas></div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
{{-- Ganti ke asset('js/chart.umd.min.js') bila server tanpa internet --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    const D = @json($data);
    const L = D.labels;
    const fmtRp  = v => 'Rp ' + Number(v).toLocaleString('id-ID', {maximumFractionDigits: 0});
    const fmtAx  = v => v >= 1e9 ? (v/1e9).toLocaleString('id-ID') + ' M' : v >= 1e6 ? (v/1e6).toLocaleString('id-ID') + ' jt' : Number(v).toLocaleString('id-ID');
    Chart.defaults.font.family = 'inherit';
    Chart.defaults.color = '#64748b';
    const base = (extra = {}) => Object.assign({
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#eef2f7' } } },
    }, extra);
    const el = id => document.getElementById(id);

    if (el('chBeli')) new Chart(el('chBeli'), {
        type: 'bar',
        data: { labels: L, datasets: [{ label: 'Pembelian', data: D.pembelian.total, backgroundColor: '#0ea5e9', borderRadius: 4 }] },
        options: base({
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => fmtRp(c.raw) + ' · ' + D.pembelian.dok[c.dataIndex] + ' dok' } } },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#eef2f7' }, ticks: { callback: fmtAx } } },
        }),
    });

    if (el('chJual')) new Chart(el('chJual'), {
        type: 'bar',
        data: { labels: L, datasets: [
            { label: 'PO', data: D.penjualan.po, backgroundColor: '#16a34a', borderRadius: 4, stack: 's' },
            { label: 'Langsung', data: D.penjualan.langsung, backgroundColor: '#86efac', borderRadius: 4, stack: 's' },
        ] },
        options: base({
            plugins: { legend: { display: true, position: 'bottom' }, tooltip: { callbacks: { label: c => c.dataset.label + ': ' + fmtRp(c.raw) } } },
            scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true, grid: { color: '#eef2f7' }, ticks: { callback: fmtAx } } },
        }),
    });

    if (el('chProd')) new Chart(el('chProd'), {
        type: 'bar',
        data: { labels: L, datasets: [{ label: 'WIP (kg)', data: D.produksi.kg, backgroundColor: '#f59e0b', borderRadius: 4 }] },
        options: base({
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => Number(c.raw).toLocaleString('id-ID') + ' kg' } } },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#eef2f7' }, ticks: { callback: v => Number(v).toLocaleString('id-ID') } } },
        }),
    });

    if (el('chHpp')) new Chart(el('chHpp'), {
        type: 'line',
        data: { labels: L, datasets: [
            { label: 'Tertinggi', data: D.hpp.max, borderColor: 'transparent', backgroundColor: 'rgba(139,92,246,.12)', fill: '+1', pointRadius: 0, spanGaps: true },
            { label: 'Terendah',  data: D.hpp.min, borderColor: 'transparent', backgroundColor: 'transparent', fill: false, pointRadius: 0, spanGaps: true },
            { label: 'HPP / kg (rata-rata)', data: D.hpp.avg, borderColor: '#8b5cf6', backgroundColor: '#8b5cf6', borderWidth: 2.5, tension: .3, pointRadius: 3, spanGaps: true },
        ] },
        options: base({
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.dataset.label + ': ' + (c.raw == null ? '—' : fmtRp(c.raw)) } } },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: false, grid: { color: '#eef2f7' }, ticks: { callback: v => Number(v).toLocaleString('id-ID') } } },
        }),
    });
})();
</script>
@endpush
