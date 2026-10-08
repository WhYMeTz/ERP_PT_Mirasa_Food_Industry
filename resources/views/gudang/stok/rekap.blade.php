@extends('layouts.app')

@section('title', 'Buku Rekapitulasi Stok Harian & Valuasi Persediaan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/stok/rekap-stok.css') }}">
@endpush

@section('content')
<div class="rekap-container">
    {{-- Header Halaman & Tombol Aksi Korporat --}}
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.06em; display: flex; align-items: center; gap: 0.35rem;">
                <span>Gudang &amp; Logistik</span>
                <span>&bull;</span>
                <span>PT Mirasa Food Industry</span>
            </div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0 0 0; letter-spacing: -0.02em;">
                Buku Rekapitulasi Stok Harian &amp; Valuasi Persediaan
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Monitoring saldo awal, mutasi masuk/keluar, saldo penutupan harian, dan estimasi nilai persediaan barang.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('gudang.stok.rekap.export-pdf', request()->all()) }}" 
               target="_blank" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
               onmouseover="this.style.background='#fef2f2'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Buka &amp; Cetak Laporan Rekapitulasi Dokumen PDF Resmi">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Rekap (PDF)</span>
            </a>

            <a href="{{ route('gudang.stok.rekap.export-excel', request()->all()) }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
               onmouseover="this.style.background='#ecfdf5'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Download Rekap Stok format Excel (.xlsx)">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>

            <a href="{{ route('gudang.stok.index') }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
               onmouseover="this.style.background='#f8fafc'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Beralih ke pemantauan batch FIFO">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Monitoring Batch FIFO</span>
            </a>
        </div>
    </div>

    {{-- 1. CORPORATE DUAL-TAB NAVIGATION BAR --}}
    <div class="rekap-tabs-nav">
        <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['tab', 'page', 'jenis_cd', 'status']), ['tab' => 'hasil_produksi', 'jenis_cd' => 'all'])) }}"
           class="rekap-tab-btn {{ $tab === 'hasil_produksi' ? 'active' : '' }}">
            <span>Rekap Stok Gudang Jadi (Hasil Produksi)</span>
            <span class="rekap-tab-badge">FG &bull; WIP</span>
        </a>
        <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['tab', 'page', 'jenis_cd', 'status']), ['tab' => 'bahan', 'jenis_cd' => 'all'])) }}"
           class="rekap-tab-btn {{ $tab === 'bahan' ? 'active' : '' }}">
            <span>Rekap Stok Bahan Baku &amp; Penolong</span>
            <span class="rekap-tab-badge">BB &bull; BP</span>
        </a>
    </div>

    {{-- 2. SNAPSHOT DATE BAR (Jauh lebih lega, bersih & proporsional) --}}
    <div class="snapshot-date-bar">
        {{-- Kiri: Tanggal Aktif --}}
        <div class="date-title-group">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="active-date-txt">{{ $formattedDateIndo }}</span>
        </div>

        {{-- Kanan: Kontrol Navigasi Tanggal --}}
        <div class="date-controls-group">
            <a href="{{ route('gudang.stok.rekap', array_merge(request()->except('page'), ['tanggal' => $prevDate])) }}" 
               class="date-nav-btn" 
               title="Mundur satu hari ({{ $prevDate }})">
                &laquo; Kemarin
            </a>
            
            <input type="date" 
                   id="rekapDatePicker" 
                   name="tanggal" 
                   value="{{ $tanggal }}" 
                   class="date-picker-input" 
                   title="Pilih tanggal snapshot persediaan">

            <a href="{{ route('gudang.stok.rekap', array_merge(request()->except('page'), ['tanggal' => $nextDate])) }}" 
               class="date-nav-btn" 
               title="Maju satu hari ({{ $nextDate }})">
                Besok &raquo;
            </a>

            @if(!$isToday)
                <a href="{{ route('gudang.stok.rekap', array_merge(request()->except('page'), ['tanggal' => date('Y-m-d')])) }}" 
                   class="date-nav-btn today-btn" 
                   title="Kembali ke posisi hari ini">
                    Hari Ini
                </a>
            @endif
        </div>
    </div>

    {{-- 3. 4 KARTU METRIK OPERASIONAL --}}
    <div class="kpi-rekap-grid">
        {{-- Card 1: Valuasi Persediaan --}}
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-label">Valuasi {{ $tab === 'hasil_produksi' ? 'Gudang Jadi' : 'Bahan Baku & Penolong' }}</span>
                <div class="kpi-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="kpi-value" style="font-family: monospace;">
                Rp {{ number_format($totals['grand_nilai_persediaan'], 0, ',', '.') }}
            </div>
            <div class="kpi-desc">
                Estimasi total nilai aset fisik persediaan per {{ $tanggal }}
            </div>
        </div>

        {{-- Card 2: Saldo Fisik & Komoditas Utama --}}
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-label" style="color: #047857;">
                    {{ $tab === 'hasil_produksi' ? 'Total Stok Gudang Jadi' : 'Total Bahan Utama' }}
                </span>
                <div class="kpi-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="kpi-value" style="color: #047857;">
                @if($tab === 'hasil_produksi')
                    {{ number_format($totals['total_karton_fg'], 0, ',', '.') }} <span style="font-size: 0.825rem; font-weight: 700; color: #334155;">Karton FG</span>
                @else
                    {{ number_format($totals['total_singkong_kg'], 0, ',', '.') }} <span style="font-size: 0.825rem; font-weight: 700; color: #334155;">Kg Singkong</span>
                @endif
            </div>
            <div class="kpi-desc">
                @if($tab === 'hasil_produksi')
                    + {{ number_format($totals['total_berko_kg'], 1, ',', '.') }} Kg Berko (WIP)
                @else
                    + {{ number_format($totals['total_minyak_kg'], 1, ',', '.') }} Kg Minyak / CNG
                @endif
            </div>
        </div>

        {{-- Card 3: Pergerakan Mutasi Hari Ini --}}
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-label" style="color: #0284c7;">Mutasi Hari Ini (IN / OUT)</span>
                <div class="kpi-icon-box" style="background: #d1fae5; color: #059669;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
            </div>
            <div class="kpi-value" style="font-size: 1.25rem;">
                <span style="color: #059669;">+{{ number_format($totals['grand_masuk'], 1, ',', '.') }}</span>
                <span style="color: #94a3b8; font-weight: 400; margin: 0 0.25rem;">/</span>
                <span style="color: #dc2626;">-{{ number_format($totals['grand_keluar'], 1, ',', '.') }}</span>
            </div>
            <div class="kpi-desc">
                <strong>{{ $totals['sku_bergerak_count'] }}</strong> komoditas bergerak pada {{ $tanggal }}
            </div>
        </div>

        {{-- Card 4: Status Ambang Batas SKU (Menampilkan Aman, Sedang, dan Habis Lengkap) --}}
        <div class="kpi-card" style="background: {{ ($totals['sku_habis'] > 0 || $totals['sku_rendah'] > 0) ? '#fffbeb' : '#ffffff' }}; border-color: {{ ($totals['sku_habis'] > 0 || $totals['sku_rendah'] > 0) ? '#fde68a' : '#e2e8f0' }};">
            <div class="kpi-card-header">
                <span class="kpi-label" style="color: {{ ($totals['sku_habis'] > 0 || $totals['sku_rendah'] > 0) ? '#b45309' : '#64748b' }};">
                    Status Ambang Batas SKU
                </span>
                <div class="kpi-icon-box" style="background: {{ ($totals['sku_habis'] > 0 || $totals['sku_rendah'] > 0) ? '#fef3c7' : '#f1f5f9' }}; color: {{ ($totals['sku_habis'] > 0 || $totals['sku_rendah'] > 0) ? '#d97706' : '#64748b' }};">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="kpi-value" style="font-size: 1.25rem; display: flex; align-items: baseline; gap: 0.4rem; flex-wrap: wrap;">
                <span style="color: #059669; font-weight: 800;">
                    {{ $totals['sku_aman'] }} <span style="font-size: 0.825rem; font-weight: 700; color: #334155;">Aman</span>
                </span>
                <span style="color: #cbd5e1; font-weight: 400; margin: 0 0.15rem;">&bull;</span>
                <span style="color: {{ $totals['sku_rendah'] > 0 ? '#b45309' : '#64748b' }}; font-weight: 800;">
                    {{ $totals['sku_rendah'] }} <span style="font-size: 0.825rem; font-weight: 700; color: #334155;">Sedang</span>
                </span>
                <span style="color: #cbd5e1; font-weight: 400; margin: 0 0.15rem;">&bull;</span>
                <span style="color: {{ $totals['sku_habis'] > 0 ? '#dc2626' : '#94a3b8' }}; font-weight: 800;">
                    {{ $totals['sku_habis'] }} <span style="font-size: 0.825rem; font-weight: 700; color: #334155;">Habis</span>
                </span>
            </div>
            <div class="kpi-desc">
                Monitoring ketersediaan dari total <strong>{{ $totals['total_sku'] }} SKU</strong> terdaftar
            </div>
        </div>
    </div>

    {{-- 4. WADAH TABEL UTAMA DENGAN TOOLBAR TERPADU (Card Format Mirasa) --}}
    <div class="table-rekap-card">
        {{-- Card Header Toolbar: Kiri Filter Pills, Kanan Search & Gudang --}}
        <div class="table-card-toolbar">
            {{-- Kiri: Filter Pills Navigation --}}
            <div class="filter-pills-bar">
                @if($tab === 'hasil_produksi')
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'hasil_produksi', 'jenis_cd' => 'all', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ (request('jenis_cd', 'all') === 'all' && request('status', 'all') === 'all') ? 'active' : '' }}">
                        <span>Semua Hasil Produksi</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'hasil_produksi', 'jenis_cd' => 'FG', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ request('jenis_cd') === 'FG' ? 'active' : '' }}">
                        <span class="badge-commodity badge-fg">FG</span>
                        <span>Barang Jadi</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'hasil_produksi', 'jenis_cd' => 'WIP', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ request('jenis_cd') === 'WIP' ? 'active' : '' }}">
                        <span class="badge-commodity badge-wip">WIP</span>
                        <span>Setengah Jadi (Berko)</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'status']), ['tab' => 'hasil_produksi', 'status' => 'habis'])) }}"
                       class="filter-pill-link {{ request('status') === 'habis' ? 'active-red' : '' }}">
                        <span>Habis ({{ $totals['sku_habis'] }})</span>
                    </a>
                    @if($totals['sku_rendah'] > 0)
                        <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'status']), ['tab' => 'hasil_produksi', 'status' => 'rendah'])) }}"
                           class="filter-pill-link {{ request('status') === 'rendah' ? 'active-amber' : '' }}">
                            <span>Sedang ({{ $totals['sku_rendah'] }})</span>
                        </a>
                    @endif
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'status']), ['tab' => 'hasil_produksi', 'status' => 'bergerak'])) }}"
                       class="filter-pill-link {{ request('status') === 'bergerak' ? 'active-green' : '' }}">
                        <span>Mutasi Hari Ini</span>
                    </a>
                @else
                    {{-- Tab: Rekap Bahan Baku & Penolong --}}
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'bahan', 'jenis_cd' => 'all', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ (request('jenis_cd', 'all') === 'all' && request('status', 'all') === 'all') ? 'active' : '' }}">
                        <span>Semua Bahan</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'bahan', 'jenis_cd' => 'BB', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ (request('jenis_cd') === 'BB' || request('jenis_cd') === 'RAW') ? 'active' : '' }}">
                        <span class="badge-commodity badge-raw">BB</span>
                        <span>Bahan Baku</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'bahan', 'jenis_cd' => 'BP', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ request('jenis_cd') === 'BP' ? 'active' : '' }}">
                        <span class="badge-commodity badge-bp">BP</span>
                        <span>Penolong</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'bahan', 'jenis_cd' => 'PACK', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ request('jenis_cd') === 'PACK' ? 'active' : '' }}">
                        <span class="badge-commodity badge-pack">PACK</span>
                        <span>Kemasan</span>
                    </a>
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'jenis_cd', 'status']), ['tab' => 'bahan', 'jenis_cd' => 'SUPP', 'status' => 'all'])) }}"
                       class="filter-pill-link {{ request('jenis_cd') === 'SUPP' ? 'active' : '' }}">
                        <span class="badge-commodity badge-supp">SUPP</span>
                        <span>Bumbu</span>
                    </a>
                    @if($totals['sku_habis'] > 0)
                        <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'status']), ['tab' => 'bahan', 'status' => 'habis'])) }}"
                           class="filter-pill-link {{ request('status') === 'habis' ? 'active-red' : '' }}">
                            <span>Habis ({{ $totals['sku_habis'] }})</span>
                        </a>
                    @endif
                    @if($totals['sku_rendah'] > 0)
                        <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'status']), ['tab' => 'bahan', 'status' => 'rendah'])) }}"
                           class="filter-pill-link {{ request('status') === 'rendah' ? 'active-amber' : '' }}">
                            <span>Sedang ({{ $totals['sku_rendah'] }})</span>
                        </a>
                    @endif
                    <a href="{{ route('gudang.stok.rekap', array_merge(request()->except(['page', 'status']), ['tab' => 'bahan', 'status' => 'bergerak'])) }}"
                       class="filter-pill-link {{ request('status') === 'bergerak' ? 'active-green' : '' }}">
                        <span>Mutasi Hari Ini</span>
                    </a>
                @endif
            </div>

            {{-- Kanan: Form Search, Filter Gudang & Info Total --}}
            <form action="{{ route('gudang.stok.rekap') }}" method="GET" class="toolbar-search-form" id="tableFilterForm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                <input type="hidden" name="jenis_cd" value="{{ request('jenis_cd', 'all') }}">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">

                <select name="gudang_id" class="form-select-corp" onchange="document.getElementById('tableFilterForm').submit()">
                    <option value="">Semua Lokasi Gudang</option>
                    @foreach($gudangList as $g)
                        <option value="{{ $g->gudang_id }}" {{ (string)request('gudang_id') === (string)$g->gudang_id ? 'selected' : '' }}>
                            {{ $g->display_name }}
                        </option>
                    @endforeach
                </select>

                <input type="text" 
                       name="search" 
                       class="form-input-corp" 
                       placeholder="Cari kode atau nama..." 
                       value="{{ request('search') }}">

                <button type="submit" class="btn-corp" style="background: #0f172a; color: #ffffff; border-color: #0f172a; height: 32px;">
                    <span>Cari</span>
                </button>

                @if(request('search') || request('gudang_id') || request('jenis_cd', 'all') !== 'all' || request('status', 'all') !== 'all')
                    <a href="{{ route('gudang.stok.rekap', ['tab' => $tab, 'tanggal' => $tanggal]) }}" 
                       class="btn-corp" 
                       style="background: #f8fafc; color: #64748b; height: 32px;" 
                       title="Reset filter pencarian">
                        <span>Reset</span>
                    </a>
                @endif

                <span style="font-size: 0.775rem; color: #64748b; margin-left: 0.35rem; white-space: nowrap;">
                    Total: <strong style="color: #0f172a;">{{ $rekapList->total() }}</strong> item
                </span>
            </form>
        </div>

        {{-- Tabel Rekapitulasi Grid --}}
        <div class="table-responsive">
            <table class="table-rekap">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">NO</th>
                        <th style="min-width: 90px;">KODE</th>
                        <th style="min-width: 220px;">NAMA BARANG</th>
                        <th style="text-align: right; min-width: 95px;">STOK AWAL</th>
                        <th class="th-masuk" style="text-align: right; min-width: 95px;">MASUK (IN)</th>
                        <th class="th-keluar" style="text-align: right; min-width: 95px;">KELUAR (OUT)</th>
                        <th style="min-width: 140px;">KETERANGAN</th>
                        <th style="text-align: right; min-width: 105px;">STOK AKHIR</th>
                        <th style="text-align: center; width: 70px;">SATUAN</th>
                        <th style="text-align: right; min-width: 105px;">HARGA POKOK</th>
                        <th style="text-align: right; min-width: 125px;">TOTAL NILAI</th>
                        <th style="text-align: center; width: 85px;">STATUS</th>
                        <th style="text-align: center; width: 65px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapList as $idx => $item)
                        @php
                            $awal = (float) $item->stok_awal;
                            $masuk = (float) $item->total_masuk;
                            $keluar = (float) $item->total_keluar;
                            $akhir = (float) $item->stok_akhir;
                            $minQty = (float) $item->batas_minimum_qty;
                            $harga = (float) $item->harga_satuan;
                            $nilai = (float) $item->nilai_persediaan;
                            $satuan = $item->satuanDasar?->satuan_cd ?? 'KG';
                            $jenisCd = $item->jenisBarang?->jenis_barang_cd ?? 'RAW';
                            $menuId = 'menu-rekap-' . $item->barang_id;
                            $isMoved = ($masuk > 0 || $keluar > 0);

                            // Format kuantitas: bulat jika integer, 2 desimal jika pecahan
                            $fmtAwal = (floor($awal) == $awal) ? number_format($awal, 0, ',', '.') : number_format($awal, 2, ',', '.');
                            $fmtMasuk = (floor($masuk) == $masuk) ? number_format($masuk, 0, ',', '.') : number_format($masuk, 2, ',', '.');
                            $fmtKeluar = (floor($keluar) == $keluar) ? number_format($keluar, 0, ',', '.') : number_format($keluar, 2, ',', '.');
                            $fmtAkhir = (floor($akhir) == $akhir) ? number_format($akhir, 0, ',', '.') : number_format($akhir, 2, ',', '.');
                        @endphp
                        <tr class="{{ $isMoved ? 'row-moved-today' : '' }}">
                            <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                                {{ $rekapList->firstItem() + $idx }}
                            </td>
                            <td>
                                <code style="background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 4px; font-weight: 700; color: #0284c7; font-size: 0.75rem;">
                                    {{ $item->barang_cd }}
                                </code>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    @php
                                        $badgeClass = match($jenisCd) {
                                            'FG' => 'badge-fg',
                                            'WIP' => 'badge-wip',
                                            'RAW', 'BB' => 'badge-raw',
                                            'BP' => 'badge-bp',
                                            'PACK' => 'badge-pack',
                                            'SUPP' => 'badge-supp',
                                            default => 'badge-raw',
                                        };
                                        $badgeLabel = ($jenisCd === 'RAW' || $jenisCd === 'BB') ? 'BB' : $jenisCd;
                                    @endphp
                                    <span class="badge-commodity {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                    <span style="font-weight: 700; color: #0f172a;">
                                        {{ $item->barang_nm }}
                                    </span>
                                </div>
                            </td>
                            <td style="text-align: right; color: #475569; font-weight: 600;">
                                {{ $fmtAwal }}
                            </td>
                            <td class="cell-masuk {{ $masuk > 0 ? 'has-in' : '' }}">
                                @if($masuk > 0)
                                    +{{ $fmtMasuk }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="cell-keluar {{ $keluar > 0 ? 'has-out' : '' }}">
                                @if($keluar > 0)
                                    -{{ $fmtKeluar }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($item->keterangan_txt))
                                    <span style="font-size: 0.75rem; color: #334155;" title="{{ $item->keterangan_txt }}">
                                        {{ \Illuminate\Support\Str::limit($item->keterangan_txt, 32) }}
                                    </span>
                                @else
                                    <span style="color: #cbd5e1; font-size: 0.75rem;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 800; font-size: 0.875rem; color: {{ $akhir <= 0 ? '#dc2626' : ($akhir <= $minQty ? '#b45309' : '#047857') }};">
                                    {{ $fmtAkhir }}
                                </span>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #475569; font-size: 0.75rem;">
                                {{ $satuan }}
                            </td>
                            <td style="text-align: right; color: #64748b; font-family: monospace; font-size: 0.775rem;">
                                Rp {{ number_format($harga, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace;">
                                Rp {{ number_format($nilai, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($akhir <= 0)
                                    <span class="badge-status badge-status-habis">HABIS</span>
                                @elseif($akhir <= $minQty)
                                    <span class="badge-status badge-status-rendah">RENDAH</span>
                                @else
                                    <span class="badge-status badge-status-aman">AMAN</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $gudangId]) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Kartu Stok Ledger</span>
                                    </a>
                                    <a href="{{ route('gudang.stok.index', ['search' => $item->barang_cd, 'view' => 'batch']) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span>Rincian Batch FIFO</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                <div style="font-size: 1rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Tidak Ada Data Rekap Stok</div>
                                <div style="font-size: 0.8rem; color: #94a3b8;">Tidak ditemukan komoditas pada tanggal {{ $tanggal }} untuk kriteria yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Paginasi --}}
        @if($rekapList->hasPages())
            <div style="padding: 0.75rem 1rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="font-size: 0.775rem; color: #64748b;">
                    Menampilkan <strong>{{ $rekapList->firstItem() }}</strong> - <strong>{{ $rekapList->lastItem() }}</strong> dari <strong>{{ $rekapList->total() }}</strong> komoditas
                </div>
                <div>
                    {{ $rekapList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/stok/rekap-stok.js') }}"></script>
@endpush
