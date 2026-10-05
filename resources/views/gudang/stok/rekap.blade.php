@extends('layouts.app')

@section('title', 'Rekapitulasi Stok & Valuasi Persediaan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/stok/rekap-stok.css') }}">
@endpush

@section('content')
<div class="rekap-container">
    {{-- Header Halaman & Tombol Aksi --}}
    <div class="rekap-header-wrapper">
        <div class="rekap-title-group">
            <h1>
                <span>Rekapitulasi Stok &amp; Valuasi Persediaan</span>
            </h1>
            <p>
                Monitoring saldo fisik komoditas, ambang batas minimum (*safety stock*), pergerakan masuk-keluar, dan estimasi nilai aset.
            </p>
        </div>

        <div class="rekap-actions-group">
            <a href="{{ route('gudang.stok.rekap.export-excel', request()->all()) }}" class="btn btn-sm btn-outline-success" style="background: #ffffff; border: 1px solid #059669; color: #059669; font-weight: 600; padding: 0.4rem 0.85rem;">
                Export Excel
            </a>
            <a href="{{ route('gudang.stok.rekap.export-pdf', request()->all()) }}" target="_blank" class="btn btn-sm btn-outline-danger" style="background: #ffffff; border: 1px solid #dc2626; color: #dc2626; font-weight: 600; padding: 0.4rem 0.85rem;">
                Cetak PDF
            </a>
            <a href="{{ route('gudang.stok.index') }}" class="btn btn-sm btn-secondary" style="padding: 0.4rem 0.85rem; font-weight: 600;">
                Monitoring Batch
            </a>
        </div>
    </div>

    {{-- 4 KARTU METRIK KPI RINGKASAN --}}
    <div class="kpi-rekap-grid">
        {{-- 1. VALUASI PERSEDIAAN GUDANG --}}
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-label">Valuasi Persediaan Fisik</span>
            </div>
            <div class="kpi-value" style="color: #0f172a; font-family: monospace;">
                Rp {{ number_format($totals['grand_nilai_persediaan'], 0, ',', '.') }}
            </div>
            <div class="kpi-desc">
                Estimasi total nilai aset persediaan on-hand
            </div>
        </div>

        {{-- 2. SALDO FISIK ON-HAND --}}
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-label" style="color: #047857;">Total Fisik On-Hand</span>
            </div>
            <div class="kpi-value" style="color: #047857;">
                {{ number_format($totals['grand_stok_akhir'], 2, ',', '.') }}
            </div>
            <div class="kpi-desc">
                Akumulasi seluruh kuantitas fisik di gudang
            </div>
        </div>

        {{-- 3. TOTAL MASUK & KELUAR --}}
        <div class="kpi-card">
            <div class="kpi-card-header">
                <span class="kpi-label" style="color: #0284c7;">Pergerakan Mutasi (IN / OUT)</span>
            </div>
            <div class="kpi-value" style="font-size: 1.15rem; color: #334155;">
                <span style="color: #059669;">+{{ number_format($totals['grand_masuk'], 1, ',', '.') }}</span>
                <span style="color: #94a3b8; font-weight: 400; margin: 0 0.25rem;">/</span>
                <span style="color: #dc2626;">-{{ number_format($totals['grand_keluar'], 1, ',', '.') }}</span>
            </div>
            <div class="kpi-desc">
                Total barang masuk vs pengeluaran terpakai
            </div>
        </div>

        {{-- 4. KESEHATAN SKU --}}
        <div class="kpi-card" style="background: {{ $totals['sku_rendah'] > 0 ? '#fffbeb' : '#ffffff' }}; border-color: {{ $totals['sku_rendah'] > 0 ? '#fde68a' : '#e2e8f0' }};">
            <div class="kpi-card-header">
                <span class="kpi-label" style="color: {{ $totals['sku_rendah'] > 0 ? '#b45309' : '#64748b' }};">Status Ambang Batas SKU</span>
            </div>
            <div class="kpi-value" style="font-size: 1.15rem; color: #0f172a;">
                <span style="color: #059669; font-weight: 800;">{{ $totals['sku_aman'] }} Aman</span>
                <span style="color: #94a3b8; font-weight: 400; margin: 0 0.25rem;">&bull;</span>
                <span style="color: {{ $totals['sku_rendah'] > 0 ? '#b45309' : '#64748b' }}; font-weight: 800;">{{ $totals['sku_rendah'] }} Rendah</span>
            </div>
            <div class="kpi-desc">
                Dari total {{ $totals['total_sku'] }} komoditas aktif tercatat
            </div>
        </div>
    </div>

    {{-- TOOLBAR FILTER PENCARIAN --}}
    <div class="filter-card">
        <form action="{{ route('gudang.stok.rekap') }}" method="GET" class="filter-grid">
            {{-- Filter Gudang --}}
            <div>
                <label class="filter-label">Pilih Gudang</label>
                <select name="gudang_id" class="filter-input">
                    <option value="">-- Semua Gudang --</option>
                    @foreach($gudangList as $g)
                        <option value="{{ $g->gudang_id }}" {{ request('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                            {{ $g->display_name }} ({{ $g->gudang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Jenis Barang --}}
            <div>
                <label class="filter-label">Jenis Barang</label>
                <select name="jenis_barang_id" class="filter-input">
                    <option value="">-- Semua Jenis --</option>
                    @foreach($jenisBarangList as $jb)
                        <option value="{{ $jb->jenis_barang_id }}" {{ request('jenis_barang_id') == $jb->jenis_barang_id ? 'selected' : '' }}>
                            {{ $jb->jenis_barang_nm }} ({{ $jb->jenis_barang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="filter-label">Status Ketersediaan</label>
                <select name="status" class="filter-input">
                    <option value="">-- Semua Status --</option>
                    <option value="aman" {{ request('status') === 'aman' ? 'selected' : '' }}>Aman (Di Atas Batas Min)</option>
                    <option value="rendah" {{ request('status') === 'rendah' ? 'selected' : '' }}>Rendah (&le; Batas Min)</option>
                    <option value="habis" {{ request('status') === 'habis' ? 'selected' : '' }}>Habis (Stok 0)</option>
                </select>
            </div>

            {{-- Cari Nama / Kode --}}
            <div>
                <label class="filter-label">Nama / Kode Barang</label>
                <input type="text" name="search" class="filter-input" placeholder="Cari nama atau kode..." value="{{ request('search') }}">
            </div>

            {{-- Periode Tanggal Dari --}}
            <div>
                <label class="filter-label">Tanggal Dari</label>
                <input type="date" name="tgl_dari" class="filter-input" value="{{ request('tgl_dari') }}">
            </div>

            {{-- Periode Tanggal Sampai --}}
            <div>
                <label class="filter-label">Tanggal Sampai</label>
                <input type="date" name="tgl_sampai" class="filter-input" value="{{ request('tgl_sampai') }}">
            </div>

            {{-- Tombol Aksi Filter --}}
            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.45rem 0.85rem; font-size: 0.8125rem;">
                    Terapkan
                </button>
                <a href="{{ route('gudang.stok.rekap') }}" class="btn btn-secondary" style="padding: 0.45rem 0.65rem; font-size: 0.8125rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;" title="Reset Filter">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABEL HASIL REKAP STOK (BLUEPRINT 10) --}}
    <div class="table-rekap-container">
        <div class="table-responsive">
            <table class="table-rekap">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">NO</th>
                        <th style="min-width: 130px;">GUDANG</th>
                        <th style="text-align: center; width: 75px;">JENIS</th>
                        <th style="min-width: 110px;">KODE BARANG</th>
                        <th style="min-width: 220px;">NAMA BARANG</th>
                        <th style="text-align: center; width: 75px;">SATUAN</th>
                        <th style="text-align: right; min-width: 95px;">BATAS MIN</th>
                        <th style="text-align: right; min-width: 105px;">TOTAL MASUK</th>
                        <th style="text-align: right; min-width: 105px;">TOTAL KELUAR</th>
                        <th style="text-align: right; min-width: 110px;">STOK AKHIR</th>
                        <th style="text-align: right; min-width: 140px;">NILAI PERSEDIAAN</th>
                        <th style="text-align: center; width: 85px;">STATUS</th>
                        <th style="text-align: center; width: 65px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapList as $idx => $item)
                        @php
                            $minQty = (float) $item->batas_minimum_qty;
                            $masuk = (float) $item->total_masuk;
                            $keluar = (float) $item->total_keluar;
                            $akhir = (float) $item->stok_akhir;
                            $nilai = (float) $item->nilai_persediaan;
                            $jenisCd = $item->jenisBarang?->jenis_barang_cd ?? '-';
                            $satuan = $item->satuanDasar?->satuan_cd ?? 'KG';

                            $menuId = 'menu-rekap-' . $item->barang_id;
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                                {{ $rekapList->firstItem() + $idx }}
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #334155; font-size: 0.775rem;">
                                    {{ $selectedGudang ? $selectedGudang->gudang_nm : 'Semua Gudang' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-jenis">{{ $jenisCd }}</span>
                            </td>
                            <td>
                                <code style="background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 4px; font-weight: 700; color: #0284c7; font-size: 0.75rem;">
                                    {{ $item->barang_cd }}
                                </code>
                            </td>
                            <td style="font-weight: 700; color: #0f172a;">
                                {{ $item->barang_nm }}
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #475569; font-size: 0.75rem;">
                                {{ $satuan }}
                            </td>
                            <td style="text-align: right; color: #64748b;">
                                {{ number_format($minQty, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #059669;">
                                {{ number_format($masuk, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #dc2626;">
                                {{ number_format($keluar, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 800; font-size: 0.85rem; color: {{ $akhir <= 0 ? '#dc2626' : ($akhir <= $minQty ? '#b45309' : '#047857') }};">
                                    {{ number_format($akhir, 2, ',', '.') }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace;">
                                Rp {{ number_format($nilai, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($akhir <= 0)
                                    <span class="badge-status-habis">HABIS</span>
                                @elseif($akhir <= $minQty)
                                    <span class="badge-status-rendah">RENDAH</span>
                                @else
                                    <span class="badge-status-aman">AMAN</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <button type="button" 
                                        class="btn btn-sm btn-action-trigger" 
                                        style="padding: 0.2rem 0.5rem; font-size: 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px; color: #334155; font-weight: 600;"
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    Aksi &#9660;
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $gudangId]) }}" class="action-dropdown-item">
                                        <span>Buku Kartu Stok</span>
                                    </a>
                                    <a href="{{ route('gudang.stok.index', ['search' => $item->barang_cd, 'view' => 'batch']) }}" class="action-dropdown-item">
                                        <span>Rincian Batch FIFO</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <div style="font-size: 0.95rem; font-weight: 700; color: #64748b;">Belum Ada Data Rekap Stok</div>
                                <div style="font-size: 0.8rem; margin-top: 0.25rem;">Tidak ditemukan komoditas dengan kriteria filter yang dipilih.</div>
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
