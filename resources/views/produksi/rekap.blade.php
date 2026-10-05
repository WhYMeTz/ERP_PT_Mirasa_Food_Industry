@extends('layouts.app')

@section('title', 'Buku Rekapitulasi Harga Pokok Produksi (HPP) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
@endpush

@section('content')
{{-- ═════════════════════════════════════════════════════════════
     HEADER BUKU REKAPITULASI HPP
     ═════════════════════════════════════════════════════════════ --}}
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.06em; display: flex; align-items: center; gap: 0.35rem;">
            <span>Produksi &amp; Manufaktur</span>
            <span>&bull;</span>
            <span>PT Mirasa Food Industry</span>
        </div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0 0 0; letter-spacing: -0.02em;">
            Buku Rekapitulasi Harga Pokok Produksi (HPP)
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
            Audit kalkulasi biaya bahan baku, energi CNG, upah operator, FOH, rendemen dan beban pokok per kilogram.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.index') }}" class="btn-corp" title="Beralih ke daftar rincian barang hasil produksi">
            <span>Hasil Barang Produksi</span>
        </a>

        @if(Auth::user()->canCreateProduksi())
            <a href="{{ route('produksi.create') }}" class="btn btn-primary" style="padding: 0.5rem 0.9rem; font-size: 0.825rem; display: inline-flex; align-items: center;">
                <span>Catat Hasil Produksi</span>
            </a>
        @endif
    </div>
</div>

{{-- NOTIFIKASI SUKSES & ERROR --}}
@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- ═════════════════════════════════════════════════════════════
     PILIHAN JANGKA WAKTU REKAP: HARIAN VS BULANAN
     ═════════════════════════════════════════════════════════════ --}}
<div class="produksi-tabs-nav" style="margin-bottom: 1.25rem;">
    <a href="{{ route('produksi.rekap', ['mode' => 'harian', 'tahun' => $year, 'bulan' => $month]) }}" 
       class="tab-nav-btn {{ $mode === 'harian' ? 'active' : '' }}" 
       style="text-decoration: none;">
        <span>Rekapitulasi Harian</span>
        <span class="tab-nav-badge">{{ strtoupper($monthName) }} {{ $year }}</span>
    </a>
    <a href="{{ route('produksi.rekap', ['mode' => 'bulanan', 'tahun' => $year]) }}" 
       class="tab-nav-btn {{ $mode === 'bulanan' ? 'active' : '' }}" 
       style="text-decoration: none;">
        <span>Rekapitulasi Bulanan (Tren 1 Tahun)</span>
        <span class="tab-nav-badge">TAHUN {{ $year }}</span>
    </a>
</div>

{{-- ═════════════════════════════════════════════════════════════
     TAMPILAN 1: REKAPITULASI HARIAN (DALAM 1 BULAN KALENDER)
     ═════════════════════════════════════════════════════════════ --}}
@if ($mode === 'harian')
    {{-- BARIS KONTROL BULAN/TAHUN & EXPORT REKAP HPP --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
        {{-- TITLE & PERIOD PICKER BAR --}}
        <div>
            <span style="font-weight: 700; font-size: 1.05rem; color: #0f172a; letter-spacing: -0.01em;">Rekapitulasi Produksi Harian</span>
            <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.5rem;">Audit kalkulasi biaya bahan, energi CNG, upah kerja, FOH, rendemen &amp; HPP/kg.</span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Periode Kalender Terpadu --}}
            <form action="{{ route('produksi.rekap') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <input type="hidden" name="mode" value="harian">
                <select name="bulan" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            {{-- Action Group Corporate --}}
            <button type="button" onclick="openModalAdjustUtilitas()" class="btn-corp" title="Alokasi tagihan bulanan listrik, air dan penyesuaian tarif gas CNG">
                <span>Penyesuaian Utilitas</span>
            </button>

            <button type="button" onclick="openModalImportRekap()" class="btn-corp" title="Import data spreadsheet harian format Excel">
                <span>Import Excel</span>
            </button>

            <a href="{{ route('produksi.export-rekap-excel', ['tahun' => $year, 'bulan' => $month]) }}" class="btn-corp" style="color: #15803d; border-color: #86efac; background: #f0fdf4;" title="Unduh berkas spreadsheet lengkap (.xlsx)">
                <span>Export Excel</span>
            </a>

            <a href="{{ route('produksi.export-rekap-pdf', ['tahun' => $year, 'bulan' => $month]) }}" class="btn-corp" style="color: #b91c1c; border-color: #fecaca; background: #fef2f2;" target="_blank" title="Cetak dokumen resmi rekapitulasi (.pdf)">
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    {{-- EXECUTIVE FINANCIAL METRIC STRIP (4 INDIKATOR KUNCI BULANAN) --}}
    @php
        $tot = $report['totals'];
        $rendemenLolos = $tot['rendemen_persen'] >= 33.0;
        $cardRendemenClass = $rendemenLolos ? 'card-success' : ($tot['rendemen_persen'] >= 30.0 ? 'card-warning' : 'card-neutral');
    @endphp

    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA OPERASIONAL --}}
        <div class="executive-stat-card card-primary">
            <div>
                <div class="executive-stat-label">Total Biaya Operasional</div>
                <div class="executive-stat-value">
                    Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Akumulasi Bahan, Gas CNG, Upah &amp; FOH ({{ $report['count'] }} Hari)</span>
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP JADI --}}
        <div class="executive-stat-card card-neutral">
            <div>
                <div class="executive-stat-label">Total Output Barang Jadi (WIP)</div>
                <div class="executive-stat-value">
                    {{ number_format($tot['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Dari input {{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg singkong mentah</span>
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN RATA-RATA --}}
        <div class="executive-stat-card {{ $cardRendemenClass }}">
            <div>
                <div class="executive-stat-label">Rasio Rendemen Efektif</div>
                <div class="executive-stat-value" style="color: {{ $rendemenLolos ? '#059669' : ($tot['rendemen_persen'] >= 30.0 ? '#d97706' : '#dc2626') }};">
                    {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $rendemenLolos ? '#059669' : ($tot['rendemen_persen'] >= 30.0 ? '#d97706' : '#dc2626') }};"></span>
                <span>Standar Toleransi Pabrik: Min. 33.00%</span>
            </div>
        </div>

        {{-- METRIK 4: HPP RATA-RATA PER KG --}}
        <div class="executive-stat-card card-success">
            <div>
                <div class="executive-stat-label">Harga Pokok Produksi (HPP)</div>
                <div class="executive-stat-value" style="color: #0f172a; font-family: monospace;">
                    Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="color: #059669; font-weight: 700;">Rata-rata Riil per Kg Produk Jadi</span>
            </div>
        </div>
    </div>

    {{-- DUAL-VIEW CONTAINER: RINGKASAN EKSEKUTIF (DEFAULT) ATAU SPREADSHEET LENGKAP --}}
    <div id="compact-view-container">
        @include('produksi.partials.table-rekap-compact')
    </div>

    <div id="spreadsheet-view-container" style="display: none;">
        @include('produksi.partials.table-rekap-spreadsheet')
    </div>

{{-- ═════════════════════════════════════════════════════════════
     TAMPILAN 2: REKAPITULASI BULANAN (TREN 12 BULAN DALAM 1 TAHUN)
     ═════════════════════════════════════════════════════════════ --}}
@else
    {{-- BARIS KONTROL TAHUN --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
        <div>
            <span style="font-weight: 700; font-size: 1.05rem; color: #0f172a; letter-spacing: -0.01em;">Konsolidasi Tren Produksi &amp; HPP Tahunan</span>
            <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.5rem;">Perbandingan akumulasi biaya, output WIP, dan rendemen antar bulan sepanjang tahun.</span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Tahun --}}
            <form action="{{ route('produksi.rekap') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <input type="hidden" name="mode" value="bulanan">
                <select name="tahun" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <button type="button" onclick="window.print()" class="btn-corp" style="color: #1e293b; border-color: #cbd5e1; background: #ffffff;" title="Cetak atau simpan PDF laporan konsolidasi tahunan">
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    {{-- EXECUTIVE FINANCIAL METRIC STRIP (4 INDIKATOR KUNCI TAHUNAN) --}}
    @php
        $an = $yearlyReport['annual_totals'];
        $anRendemenLolos = $an['rendemen'] >= 33.0;
        $anCardRendemenClass = $anRendemenLolos ? 'card-success' : ($an['rendemen'] >= 30.0 ? 'card-warning' : 'card-neutral');
    @endphp

    <div class="executive-stat-grid">
        {{-- METRIK 1: TOTAL BIAYA TAHUNAN --}}
        <div class="executive-stat-card card-primary">
            <div>
                <div class="executive-stat-label">Total Biaya Operasional (Tahun {{ $year }})</div>
                <div class="executive-stat-value">
                    Rp {{ number_format($an['total_biaya'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Total akumulasi seluruh biaya pabrik ({{ $an['total_work_days'] }} Hari Kerja)</span>
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP TAHUNAN --}}
        <div class="executive-stat-card card-neutral">
            <div>
                <div class="executive-stat-label">Total Output Barang Jadi (WIP)</div>
                <div class="executive-stat-value">
                    {{ number_format($an['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span>Dari input {{ number_format($an['singkong_qty'], 0, ',', '.') }} kg singkong mentah</span>
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN RATA-RATA TAHUNAN --}}
        <div class="executive-stat-card {{ $anCardRendemenClass }}">
            <div>
                <div class="executive-stat-label">Rasio Rendemen Rata-Rata Tahunan</div>
                <div class="executive-stat-value" style="color: {{ $anRendemenLolos ? '#059669' : ($an['rendemen'] >= 30.0 ? '#d97706' : '#dc2626') }};">
                    {{ number_format($an['rendemen'], 2, ',', '.') }}%
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $anRendemenLolos ? '#059669' : ($an['rendemen'] >= 30.0 ? '#d97706' : '#dc2626') }};"></span>
                <span>Standar Target Pabrik: Min. 33.00%</span>
            </div>
        </div>

        {{-- METRIK 4: HPP RATA-RATA TAHUNAN PER KG --}}
        <div class="executive-stat-card card-success">
            <div>
                <div class="executive-stat-label">HPP Rata-Rata per Kg (Tahun {{ $year }})</div>
                <div class="executive-stat-value" style="color: #0f172a; font-family: monospace;">
                    Rp {{ number_format($an['hpp_per_kg'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-subtext">
                <span style="color: #059669; font-weight: 700;">Biaya Riil Tertimbang per Kg Setahun</span>
            </div>
        </div>
    </div>

    {{-- TABEL KONSOLIDASI 12 BULAN --}}
    @include('produksi.partials.table-rekap-bulanan')

@endif

{{-- MODALS PARTIALS --}}
@if($mode === 'harian')
    @include('produksi.partials.modal-import-rekap')
    @include('produksi.partials.modal-adjust-utilitas')
@endif
@include('produksi.partials.modal-delete-confirm')

@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
@endpush
