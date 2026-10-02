@extends('layouts.app')

@section('title', 'Modul Produksi & Manufaktur - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
@endpush

@section('content')
{{-- ═════════════════════════════════════════════════════════════
     HEADER MODUL PRODUKSI & MANUFAKTUR
     ═════════════════════════════════════════════════════════════ --}}
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="font-size: 0.8rem; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.35rem;">
            <span>🏭 Produksi &amp; Manufaktur</span>
            <span>&bull;</span>
            <span>PT Mirasa Food Industry</span>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0 0 0; letter-spacing: -0.02em;">
            Manajemen Hasil Produksi &amp; Kalkulasi HPP
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
            Pencatatan rincian output barang jadi (WIP), mutasi kartu stok, dan buku besar rekapitulasi HPP harian.
        </p>
    </div>

    <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.create') }}" class="btn btn-primary" style="padding: 0.55rem 1rem; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25); display: inline-flex; align-items: center; gap: 0.4rem;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Catat Hasil Produksi</span>
        </a>
    </div>
</div>

{{-- NOTIFIKASI SUKSES & ERROR --}}
@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>⚠️ {{ session('error') }}</span>
    </div>
@endif

{{-- ═════════════════════════════════════════════════════════════
     DUAL-TAB NAVIGATION BAR (POINT 9 & POINT 13)
     ═════════════════════════════════════════════════════════════ --}}
<div class="produksi-tabs-nav">
    <button type="button" id="btn-tab-hasil" class="tab-nav-btn {{ $activeTab === 'hasil' ? 'active' : '' }}" onclick="switchProduksiTab('hasil')">
        <span>📦 Hasil Barang Produksi</span>
        <span class="tab-nav-badge">{{ $hasilItems->total() }} Data</span>
    </button>
    <button type="button" id="btn-tab-rekap" class="tab-nav-btn {{ $activeTab === 'rekap' ? 'active' : '' }}" onclick="switchProduksiTab('rekap')">
        <span>📊 Buku Rekap Pembaca HPP</span>
        <span class="tab-nav-badge">{{ strtoupper($monthName) }} {{ $year }}</span>
    </button>
</div>

{{-- ═════════════════════════════════════════════════════════════
     TAB 1: 📦 HASIL BARANG PRODUKSI & SISA STOK (POINT 9)
     ═════════════════════════════════════════════════════════════ --}}
<div id="pane-tab-hasil" class="tab-pane-content {{ $activeTab === 'hasil' ? 'active' : '' }}">
    
    {{-- BARIS AKSI TAB 1 (TAMBAH, IMPORT, EXPORT) --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
        <div>
            <span style="font-weight: 700; font-size: 1.05rem; color: #0f172a;">Rincian Barang Produksi (WIP)</span>
            <span style="font-size: 0.825rem; color: #64748b; margin-left: 0.5rem;">Total kuantitas, estimasi HPP satuan, dan posisi sisa stok fisik gudang.</span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="openModalImportHasil()" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>📥 Import Excel (.xlsx)</span>
            </button>

            <a href="{{ route('produksi.export-hasil', request()->all()) }}" class="btn btn-success" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #059669; border-color: #059669; color: #ffffff;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                <span>📤 Export Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    {{-- FORM FILTER TAB 1 --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('produksi.index') }}">
            <input type="hidden" name="tab" value="hasil">
            <div class="filter-grid">
                <div>
                    <label class="filter-label">Gudang Simpan</label>
                    <select name="gudang_id" class="filter-input">
                        <option value="">-- Semua Gudang --</option>
                        @foreach($gudangList as $g)
                            <option value="{{ $g->gudang_id }}" {{ request('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->gudang_nm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="filter-label">Cari Barang / Dokumen</label>
                    <input type="text" name="search" class="filter-input" placeholder="Kode / Nama Barang / No Produksi..." value="{{ request('search') }}">
                </div>

                <div>
                    <label class="filter-label">Kode Batch</label>
                    <input type="text" name="batch_no" class="filter-input" placeholder="Misal: A0001 / B2063..." value="{{ request('batch_no') }}">
                </div>

                <div>
                    <label class="filter-label">Kategori Output</label>
                    <select name="kategori" class="filter-input">
                        <option value="">-- Semua Kategori --</option>
                        <option value="ASIN_BARCO" {{ request('kategori') == 'ASIN_BARCO' ? 'selected' : '' }}>Asin Barco</option>
                        <option value="ASIN_SAWIT" {{ request('kategori') == 'ASIN_SAWIT' ? 'selected' : '' }}>Asin Sawit</option>
                        <option value="NO_SALT" {{ request('kategori') == 'NO_SALT' ? 'selected' : '' }}>No Salt</option>
                        <option value="BALO_GELOMBANG" {{ request('kategori') == 'BALO_GELOMBANG' ? 'selected' : '' }}>Balo Gelombang</option>
                        <option value="BERKO" {{ request('kategori') == 'BERKO' ? 'selected' : '' }}>Berko</option>
                        <option value="BERKO_ME" {{ request('kategori') == 'BERKO_ME' ? 'selected' : '' }}>Berko ME</option>
                    </select>
                </div>

                <div>
                    <label class="filter-label">Tanggal Dari</label>
                    <input type="date" name="tgl_dari" class="filter-input" value="{{ request('tgl_dari') }}">
                </div>

                <div>
                    <label class="filter-label">Tanggal Sampai</label>
                    <input type="date" name="tgl_sampai" class="filter-input" value="{{ request('tgl_sampai') }}">
                </div>

                <div style="display: flex; gap: 0.4rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; flex: 1;">
                        🔍 Filter
                    </button>
                    <a href="{{ route('produksi.index', ['tab' => 'hasil']) }}" class="btn btn-secondary" style="padding: 0.45rem 0.65rem; font-size: 0.825rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABEL DATA HASIL PRODUKSI (POINT 9) --}}
    <div class="table-responsive-hasil">
        <table class="table-hasil">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">NO</th>
                    <th style="text-align: center;">TANGGAL</th>
                    <th style="text-align: center;">NO. PRODUKSI</th>
                    <th style="text-align: center;">KODE BATCH</th>
                    <th>GUDANG SIMPAN</th>
                    <th>KODE BARANG</th>
                    <th>NAMA BARANG</th>
                    <th style="text-align: center;">KATEGORI</th>
                    <th style="text-align: right;">QTY HASIL (KG)</th>
                    <th style="text-align: center;">SATUAN</th>
                    <th style="text-align: right;">HPP / KG (RP)</th>
                    <th style="text-align: right;">TOTAL NILAI (RP)</th>
                    <th style="text-align: center;">SISA STOK (KG)</th>
                    <th style="text-align: center; width: 85px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pageTotalQty = 0;
                    $pageTotalNilai = 0;
                @endphp
                @forelse($hasilItems as $idx => $item)
                    @php
                        $prod = $item->produksi;
                        $barang = $item->barang;
                        $pageTotalQty += (float) $item->qty_kg;
                        $pageTotalNilai += (float) $item->total_nilai;
                        $sisaStok = (float) ($item->sisa_stok ?? $item->qty_kg);
                        $menuId = 'menu-hasil-' . $item->output_id;
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                            {{ $hasilItems->firstItem() + $idx }}
                        </td>
                        <td style="text-align: center; font-weight: 600; color: #0f172a;">
                            {{ $prod ? \Carbon\Carbon::parse($prod->produksi_tgl)->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align: center;">
                            @if($prod)
                                <a href="{{ route('produksi.show', $prod->produksi_id) }}" style="color: #0284c7; font-weight: 700; text-decoration: none;">
                                    {{ $prod->produksi_no }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-batch-wip">{{ $item->batch_no ?? ($prod->batch_wip_no ?? '-') }}</span>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #334155;">{{ $prod?->gudang?->gudang_nm ?? 'Gudang WIP' }}</span>
                        </td>
                        <td>
                            <code style="background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 4px; font-weight: 700; color: #0284c7;">
                                {{ $barang?->barang_cd ?? '-' }}
                            </code>
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $barang?->barang_nm ?? '-' }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-kategori-out">{{ $item->kategori_output ?? 'WIP' }}</span>
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #0f172a;">
                            {{ number_format($item->qty_kg, 2, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-size: 0.75rem; color: #64748b; font-weight: 600;">
                            {{ $barang?->satuan?->satuan_nm ?? 'KG' }}
                        </td>
                        <td style="text-align: right; color: #475569;">
                            Rp {{ number_format($item->hpp_satuan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #047857;">
                            Rp {{ number_format($item->total_nilai, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @if($sisaStok > 0)
                                <span class="badge-stok-tersedia">
                                    ● {{ number_format($sisaStok, 1, ',', '.') }} kg
                                </span>
                            @else
                                <span class="badge-stok-habis">
                                    0 kg (Habis)
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; position: relative;">
                            {{-- SMART ACTION DROPDOWN --}}
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                Aksi ▼
                            </button>

                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                @if($prod)
                                    <a href="{{ route('produksi.show', $prod->produksi_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Detail Dokumen</span>
                                    </a>
                                    <a href="{{ route('produksi.cetak-stiker', $prod->produksi_id) }}" class="action-dropdown-item" target="_blank">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                        <span>Cetak Stiker Karton</span>
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $prod->produksi_id }}, '{{ $prod->produksi_no }}', '{{ \Carbon\Carbon::parse($prod->produksi_tgl)->format('d/m/Y') }}', '{{ $prod->shift_cd }}', '{{ $prod->batch_wip_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus Produksi</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📦</div>
                            <div style="font-size: 0.95rem; font-weight: 700; color: #475569;">Belum Ada Data Hasil Barang Produksi</div>
                            <div style="font-size: 0.8rem; margin-top: 0.25rem;">Klik tombol <strong>+ Catat Hasil Produksi</strong> atau gunakan fitur <strong>Import Excel</strong> di atas.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($hasilItems->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="8" style="text-align: right; font-weight: 800; color: #0f172a; padding: 0.75rem 0.85rem;">
                            SUBTOTAL HALAMAN INI:
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #0284c7; padding: 0.75rem 0.85rem;">
                            {{ number_format($pageTotalQty, 2, ',', '.') }} kg
                        </td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right; font-weight: 800; color: #047857; padding: 0.75rem 0.85rem;">
                            Rp {{ number_format($pageTotalNilai, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- PAGINASI TAB 1 --}}
    <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div style="font-size: 0.8rem; color: #64748b;">
            Menampilkan {{ $hasilItems->firstItem() ?? 0 }} - {{ $hasilItems->lastItem() ?? 0 }} dari total {{ $hasilItems->total() }} rincian output barang
        </div>
        <div>
            {{ $hasilItems->links() }}
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════
     TAB 2: 📊 BUKU REKAP PEMBACA HPP HARIAN (POINT 13)
     ═════════════════════════════════════════════════════════════ --}}
<div id="pane-tab-rekap" class="tab-pane-content {{ $activeTab === 'rekap' ? 'active' : '' }}">

    {{-- BARIS KONTROL BULAN/TAHUN & EXPORT REKAP HPP --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
        <div>
            <span style="font-weight: 700; font-size: 1.05rem; color: #0f172a;">Buku Besar Rekapitulasi HPP Harian</span>
            <span style="font-size: 0.825rem; color: #64748b; margin-left: 0.5rem;">Perhitungan biaya bahan, energi CNG, tenaga kerja, FOH, rendemen singkong, dan HPP/kg.</span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Bulan & Tahun --}}
            <form action="{{ route('produksi.index') }}" method="GET" style="display: flex; gap: 0.4rem; align-items: center; background: #ffffff; padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                <input type="hidden" name="tab" value="rekap">
                <select name="bulan" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.65rem; height: 34px; border: none; font-weight: 600;" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.65rem; height: 34px; border: none; font-weight: 600;" onchange="this.form.submit()">
                    @foreach ($yearsList as $yr)
                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('produksi.download-rekap-template') }}" class="btn" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #f0fdf4; border: 1.5px solid #16a34a; color: #16a34a; font-weight: 700; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                <span>📄 Format Template (.xlsx)</span>
            </a>

            <button type="button" onclick="openModalImportRekap()" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #0284c7; border-color: #0284c7; color: #ffffff; cursor: pointer;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>📥 Import Excel (.xlsx)</span>
            </button>

            <a href="{{ route('produksi.export-rekap-excel', ['tahun' => $year, 'bulan' => $month]) }}" class="btn btn-success" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #059669; border-color: #059669; color: #ffffff;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                <span>📊 Export Excel (.xlsx)</span>
            </a>

            <a href="{{ route('produksi.export-rekap-pdf', ['tahun' => $year, 'bulan' => $month]) }}" class="btn btn-danger" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #dc2626; border-color: #dc2626; color: #ffffff;" target="_blank">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>📄 Export PDF (.pdf)</span>
            </a>

            <button type="button" onclick="window.print()" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>🖨️ Cetak Lembar HPP</span>
            </button>
        </div>
    </div>

    {{-- 4 KARTU KPI RINGKASAN PRODUKSI BULANAN --}}
    @php
        $tot = $report['totals'];
        $rendemenColor = $tot['rendemen_persen'] >= 33.0 ? '#059669' : ($tot['rendemen_persen'] >= 30.0 ? '#d97706' : '#dc2626');
        $rendemenBg = $tot['rendemen_persen'] >= 33.0 ? '#ecfdf5' : ($tot['rendemen_persen'] >= 30.0 ? '#fffbeb' : '#fef2f2');
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        {{-- KPI 1: TOTAL BIAYA PRODUKSI --}}
        <div class="card" style="padding: 1.1rem; border-left: 4px solid #eab308;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #854d0e;">
                        Total Biaya Produksi
                    </span>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                        Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                    </div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef9c3; display: flex; align-items: center; justify-content: center; color: #a16207; font-weight: 800;">
                    Rp
                </div>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
                Bahan, CNG, Tenaga Kerja &amp; FOH
            </div>
        </div>

        {{-- KPI 2: TOTAL WIP OUTPUT JADI --}}
        <div class="card" style="padding: 1.1rem; border-left: 4px solid #0284c7;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0369a1;">
                        Total Hasil WIP Jadi
                    </span>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                        {{ number_format($tot['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">kg</span>
                    </div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                    📦
                </div>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
                Dari {{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg singkong mentah
                @if(!empty($tot['total_karton']) && $tot['total_karton'] > 0)
                    &bull; <strong style="color: #0369a1;">{{ number_format($tot['total_karton'], 0, ',', '.') }} Box/Karton</strong>
                @endif
            </div>
        </div>

        {{-- KPI 3: RENDEMEN RATA-RATA --}}
        <div class="card" style="padding: 1.1rem; border-left: 4px solid {{ $rendemenColor }};">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $rendemenColor }};">
                        Rendemen Rata-Rata
                    </span>
                    <div style="font-size: 1.4rem; font-weight: 800; color: {{ $rendemenColor }}; margin-top: 0.25rem;">
                        {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                    </div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $rendemenBg }}; display: flex; align-items: center; justify-content: center; color: {{ $rendemenColor }}; font-weight: 800;">
                    %
                </div>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
                Target standar pabrik: &ge; 33.00%
            </div>
        </div>

        {{-- KPI 4: HPP RATA-RATA PER KG --}}
        <div class="card" style="padding: 1.1rem; border-left: 4px solid #10b981;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #047857;">
                        HPP Rata-Rata per Kg
                    </span>
                    <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                        Rp {{ number_format($tot['hpp_per_kg'], 2, ',', '.') }}
                    </div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                    ⚖️
                </div>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
                Biaya riil per kg produk jadi
            </div>
        </div>
    </div>

    {{-- SPREADSHEET GRID UTAMA TAB 2 (POINT 13) --}}
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">
                    Tabel Rekapitulasi HPP Harian (Format Asli Spreadsheet Mirasa)
                </span>
                <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600;">
                    {{ $report['count'] }} Hari Produksi
                </span>
            </div>
        </div>

        {{-- CONTAINER SCROLLABLE HORIZONTAL --}}
        <div style="overflow-x: auto; max-height: calc(100vh - 300px); position: relative; border-radius: 0 0 12px 12px;">
            <table class="mirasa-grid-table">
                {{-- HEADER LEVEL 1 --}}
                <thead style="position: sticky; top: 0; z-index: 20;">
                    <tr>
                        <th rowspan="2" class="th-mirasa-orange" style="position: sticky; left: 0; z-index: 25; min-width: 80px;">HARI</th>
                        <th rowspan="2" class="th-mirasa-orange" style="position: sticky; left: 80px; z-index: 25; min-width: 75px;">TANGGAL</th>
                        <th rowspan="2" class="th-mirasa-orange" style="min-width: 110px;">SHIFT &amp; BATCH</th>

                        <th colspan="28" class="th-mirasa-green-mega">TOTAL BIAYA PRODUKSI / KG</th>

                        <th rowspan="2" class="th-mirasa-yellow" style="min-width: 110px;">
                            TOTAL<br>BIAYA
                        </th>

                        <th colspan="2" class="th-mirasa-green-mega">TOTAL WIP</th>

                        <th rowspan="2" class="th-mirasa-gray" style="min-width: 105px;">
                            HARGA POKOK<br>PRODUKSI
                        </th>
                        <th rowspan="2" class="th-mirasa-action" style="position: sticky; right: 0; z-index: 25; min-width: 75px;">
                            AKSI
                        </th>
                    </tr>

                    {{-- HEADER LEVEL 2 (SUB-HEADER BIAYA & WIP) --}}
                    <tr>
                        {{-- Singkong --}}
                        <th class="th-mirasa-green-sub" style="min-width: 65px;">KG</th>
                        <th class="th-mirasa-green-sub" style="min-width: 85px;">Rp</th>

                        {{-- Minyak Goreng --}}
                        <th class="th-mirasa-green-sub" style="min-width: 60px;">SAWIT</th>
                        <th class="th-mirasa-green-sub" style="min-width: 60px;">KELAPA</th>
                        <th class="th-mirasa-green-sub" style="min-width: 85px;">Rp</th>
                        <th class="th-mirasa-green-sub text-blue-ratio" style="min-width: 50px;">%</th>

                        {{-- Gas CNG --}}
                        <th class="th-mirasa-green-sub" style="min-width: 65px;">MMBTU</th>
                        <th class="th-mirasa-green-sub" style="min-width: 85px;">Rp</th>

                        {{-- Tenaga Kerja --}}
                        <th class="th-mirasa-green-sub" style="min-width: 45px;">LANGSUNG</th>
                        <th class="th-mirasa-green-sub" style="min-width: 45px;">TDK LANGSUNG</th>
                        <th class="th-mirasa-green-sub" style="min-width: 45px;">TRAINING</th>
                        <th class="th-mirasa-green-sub" style="min-width: 85px;">TOTAL (Rp)</th>

                        {{-- Bahan Pembantu & Pengemas --}}
                        <th class="th-mirasa-green-sub" style="min-width: 80px;">BUMBU PERENYAH</th>
                        <th class="th-mirasa-green-sub" style="min-width: 75px;">KARTON BARU</th>
                        <th class="th-mirasa-green-sub" style="min-width: 75px;">KARTON BEKAS</th>
                        <th class="th-mirasa-green-sub" style="min-width: 80px;">PLASTIK HD 90x100</th>
                        <th class="th-mirasa-green-sub" style="min-width: 65px;">LAKBAN BESAR</th>
                        <th class="th-mirasa-green-sub" style="min-width: 65px;">LAKBAN KECIL</th>
                        <th class="th-mirasa-green-sub" style="min-width: 65px;">TALI RAFIA</th>
                        <th class="th-mirasa-green-sub" style="min-width: 65px;">FOTO COPY</th>
                        <th class="th-mirasa-green-sub" style="min-width: 70px;">SARUNG TGN PLSTK</th>
                        <th class="th-mirasa-green-sub" style="min-width: 70px;">SARUNG TGN KAIN</th>

                        {{-- Pos Biaya FOH (Teks Merah Sesuai Spreadsheet Asli) --}}
                        <th class="th-mirasa-green-sub text-red-foh" style="min-width: 85px;">PEMERIKSAAN MUTU</th>
                        <th class="th-mirasa-green-sub text-red-foh" style="min-width: 85px;">LISTRIK &amp; AIR + TELP</th>
                        <th class="th-mirasa-green-sub text-red-foh" style="min-width: 80px;">PEMLHR MESIN</th>
                        <th class="th-mirasa-green-sub text-red-foh" style="min-width: 80px;">PENYS MESIN</th>
                        <th class="th-mirasa-green-sub text-red-foh" style="min-width: 75px;">LIMBAH PADAT</th>
                        <th class="th-mirasa-green-sub text-red-foh" style="min-width: 75px;">BAHAN KIMIA</th>

                        {{-- Sub Header Total WIP --}}
                        <th class="th-mirasa-green-sub" style="min-width: 75px;">TOTAL KG</th>
                        <th class="th-mirasa-green-sub text-blue-ratio" style="min-width: 65px;">RENDEMEN %</th>
                    </tr>
                </thead>

                {{-- BODY: 31 BARIS HARIAN BULAN BERJALAN --}}
                <tbody>
                    @foreach ($report['days'] as $d)
                        @php
                            $isSunday = in_array(strtolower($d['hari_nm']), ['minggu', 'ahad']);
                            $rowClass = $isSunday ? 'tr-sunday-mirasa' : '';
                        @endphp
                        <tr class="{{ $rowClass }}" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background=''">
                            {{-- Sticky Kolom 1 & 2 --}}
                            <td style="position: sticky; left: 0; z-index: 10; font-weight: 700; background: inherit; text-align: left;">
                                {{ $d['hari_nm'] }}
                            </td>
                            <td style="position: sticky; left: 80px; z-index: 10; font-weight: 600; background: inherit; text-align: center;">
                                {{ \Carbon\Carbon::parse($d['date'])->format('d/m/y') }}
                            </td>

                            {{-- Shift & Batch --}}
                            <td style="text-align: center;">
                                @if($d['has_data'])
                                    <div style="display: flex; gap: 0.3rem; align-items: center; justify-content: center;">
                                        <span class="{{ $d['shift_cd'] == 'A' ? 'badge-shift-a' : 'badge-shift-b' }}">
                                            Shift {{ $d['shift_cd'] }}
                                        </span>
                                        <span class="badge-batch-wip">{{ $d['batch_wip_no'] }}</span>
                                    </div>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>

                            {{-- Singkong --}}
                            <td style="text-align: right;">
                                {{ $d['singkong_qty'] > 0 ? number_format($d['singkong_qty'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['singkong_nilai'] > 0 ? number_format($d['singkong_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Minyak --}}
                            <td style="text-align: right;">
                                {{ $d['minyak_sawit_qty'] > 0 ? number_format($d['minyak_sawit_qty'], 1, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['minyak_kelapa_qty'] > 0 ? number_format($d['minyak_kelapa_qty'], 1, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['minyak_nilai'] > 0 ? number_format($d['minyak_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;" class="text-blue-ratio">
                                {{ $d['minyak_rasio_persen'] > 0 ? number_format($d['minyak_rasio_persen'], 2, ',', '.') . '%' : '-' }}
                            </td>

                            {{-- CNG --}}
                            <td style="text-align: right;">
                                {{ $d['cng_mmbtu'] > 0 ? number_format($d['cng_mmbtu'], 2, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['cng_nilai'] > 0 ? number_format($d['cng_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Tenaga Kerja --}}
                            <td style="text-align: center;">
                                {{ $d['tk_langsung_org'] > 0 ? $d['tk_langsung_org'] : '-' }}
                            </td>
                            <td style="text-align: center;">
                                {{ $d['tk_tidak_langsung_org'] > 0 ? $d['tk_tidak_langsung_org'] : '-' }}
                            </td>
                            <td style="text-align: center;">
                                {{ $d['tk_training_org'] > 0 ? $d['tk_training_org'] : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['tk_total_nilai'] > 0 ? number_format($d['tk_total_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Bahan Pembantu & Pengemas --}}
                            <td style="text-align: right;">
                                {{ $d['bumbu_nilai'] > 0 ? number_format($d['bumbu_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['karton_baru_nilai'] > 0 ? number_format($d['karton_baru_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['karton_bekas_nilai'] > 0 ? number_format($d['karton_bekas_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['plastik_hd_nilai'] > 0 ? number_format($d['plastik_hd_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['lakban_besar_nilai'] > 0 ? number_format($d['lakban_besar_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['lakban_kecil_nilai'] > 0 ? number_format($d['lakban_kecil_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['tali_rafia_nilai'] > 0 ? number_format($d['tali_rafia_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['fotocopy_nilai'] > 0 ? number_format($d['fotocopy_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['sarung_tangan_plastik_nilai'] > 0 ? number_format($d['sarung_tangan_plastik_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['sarung_tangan_kain_nilai'] > 0 ? number_format($d['sarung_tangan_kain_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Pos FOH --}}
                            <td style="text-align: right;">
                                {{ $d['qc_pengawasan_nilai'] > 0 ? number_format($d['qc_pengawasan_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['listrik_air_telp_nilai'] > 0 ? number_format($d['listrik_air_telp_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['pemeliharaan_mesin_nilai'] > 0 ? number_format($d['pemeliharaan_mesin_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['penyusutan_mesin_nilai'] > 0 ? number_format($d['penyusutan_mesin_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['limbah_padat_nilai'] > 0 ? number_format($d['limbah_padat_nilai'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;">
                                {{ $d['limbah_kimia_nilai'] > 0 ? number_format($d['limbah_kimia_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- TOTAL BIAYA (KUNING PEKAT SESUAI EXCEL) --}}
                            <td style="text-align: right;" class="{{ $d['has_data'] && $d['total_biaya_produksi'] > 0 ? 'cell-yellow-highlight' : '' }}">
                                {{ $d['total_biaya_produksi'] > 0 ? number_format($d['total_biaya_produksi'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- TOTAL WIP: TOTAL KG (KUNING) & RENDEMEN % (BIRU) --}}
                            <td style="text-align: right;" class="{{ $d['has_data'] && $d['total_wip_qty'] > 0 ? 'cell-yellow-highlight' : '' }}">
                                {{ $d['total_wip_qty'] > 0 ? number_format($d['total_wip_qty'], 2, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: right;" class="text-blue-ratio">
                                {{ $d['rendemen_persen'] > 0 ? number_format($d['rendemen_persen'], 2, ',', '.') . '%' : '-' }}
                            </td>

                            {{-- HARGA POKOK PRODUKSI (HPP/KG) --}}
                            <td style="text-align: right; font-weight: 700;">
                                {{ $d['hpp_per_kg'] > 0 ? number_format($d['hpp_per_kg'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Sticky Kolom Kanan Aksi --}}
                            <td style="position: sticky; right: 0; z-index: 10; background: inherit; text-align: center;">
                                @if($d['has_data'] && !empty($d['produksi_id']))
                                    @php $rekapMenuId = 'menu-rekap-' . $d['produksi_id']; @endphp
                                    <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $rekapMenuId }}')">
                                        Aksi ▼
                                    </button>
                                    <div id="{{ $rekapMenuId }}" class="action-dropdown-menu">
                                        <a href="{{ route('produksi.show', $d['produksi_id']) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Buka Detail Lembar</span>
                                        </a>
                                        <a href="{{ route('produksi.cetak-stiker', $d['produksi_id']) }}" class="action-dropdown-item" target="_blank">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                            <span>Cetak Stiker Karton</span>
                                        </a>
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $d['produksi_id'] }}, '{{ $d['produksi_no'] }}', '{{ $d['hari_nm'] }}, {{ $d['day'] }}/{{ $month }}/{{ $year }}', '{{ $d['shift_cd'] }}', '{{ $d['batch_wip_no'] }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus Data Ini</span>
                                        </button>
                                    </div>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- TFOOT: 2 BARIS SUMMARY SESUAI DENGAN FOTO ASLI EXCEL SPREADSHEET PT MIRASA --}}
                @if($report['count'] > 0)
                    @php
                        $c = $report['count'];
                    @endphp
                    <tfoot style="position: sticky; bottom: 0; z-index: 20;">
                        {{-- 1. BARIS T O T A L --}}
                        <tr class="tr-summary-total">
                            <td colspan="3" style="text-align: center; letter-spacing: 0.15em;">
                                T O T A L
                            </td>

                            {{-- Singkong --}}
                            <td style="text-align: right;">{{ number_format($tot['singkong_qty'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['singkong_nilai'], 0, ',', '.') }}</td>

                            {{-- Minyak --}}
                            <td style="text-align: right;">{{ number_format($tot['minyak_sawit_qty'], 1, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['minyak_kelapa_qty'], 1, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['minyak_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;" class="text-blue-ratio">{{ number_format($tot['minyak_rasio_persen'], 2, ',', '.') }}%</td>

                            {{-- CNG --}}
                            <td style="text-align: right;">{{ number_format($tot['cng_mmbtu'], 2, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['cng_nilai'], 0, ',', '.') }}</td>

                            {{-- TK --}}
                            <td style="text-align: center;">{{ $tot['tk_langsung_org'] }}</td>
                            <td style="text-align: center;">{{ $tot['tk_tidak_langsung_org'] }}</td>
                            <td style="text-align: center;">{{ $tot['tk_training_org'] }}</td>
                            <td style="text-align: right;">{{ number_format($tot['tk_total_nilai'], 0, ',', '.') }}</td>

                            {{-- Pembantu & Pengemas --}}
                            <td style="text-align: right;">{{ number_format($tot['bumbu_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['karton_baru_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['karton_bekas_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['plastik_hd_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['lakban_besar_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['lakban_kecil_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['tali_rafia_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['fotocopy_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['sarung_tangan_plastik_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['sarung_tangan_kain_nilai'], 0, ',', '.') }}</td>

                            {{-- FOH --}}
                            <td style="text-align: right;">{{ number_format($tot['qc_pengawasan_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['listrik_air_telp_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['pemeliharaan_mesin_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['penyusutan_mesin_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['limbah_padat_nilai'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['limbah_kimia_nilai'], 0, ',', '.') }}</td>

                            {{-- Total Biaya (Kuning) --}}
                            <td style="text-align: right;" class="cell-yellow-highlight">
                                {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                            </td>

                            {{-- Total WIP (Kuning) & Rendemen (Biru) --}}
                            <td style="text-align: right;" class="cell-yellow-highlight">
                                {{ number_format($tot['total_wip_qty'], 2, ',', '.') }}
                            </td>
                            <td style="text-align: right;" class="text-blue-ratio">
                                {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                            </td>

                            {{-- HPP/KG --}}
                            <td style="text-align: right;">
                                {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                            </td>

                            <td style="position: sticky; right: 0; z-index: 25; background: #ffc000;"></td>
                        </tr>

                        {{-- 2. BARIS R A T A - R A T A --}}
                        <tr class="tr-summary-avg">
                            <td colspan="3" style="text-align: center; letter-spacing: 0.15em;">
                                R A T A - R A T A
                            </td>

                            {{-- Singkong --}}
                            <td style="text-align: right;">{{ number_format($tot['singkong_qty'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['singkong_nilai'] / $c, 0, ',', '.') }}</td>

                            {{-- Minyak --}}
                            <td style="text-align: right;">{{ number_format($tot['minyak_sawit_qty'] / $c, 1, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['minyak_kelapa_qty'] / $c, 1, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['minyak_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;" class="text-blue-ratio">{{ number_format($tot['minyak_rasio_persen'], 2, ',', '.') }}%</td>

                            {{-- CNG --}}
                            <td style="text-align: right;">{{ number_format($tot['cng_mmbtu'] / $c, 2, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['cng_nilai'] / $c, 0, ',', '.') }}</td>

                            {{-- TK --}}
                            <td style="text-align: center;">{{ round($tot['tk_langsung_org'] / $c) }}</td>
                            <td style="text-align: center;">{{ round($tot['tk_tidak_langsung_org'] / $c) }}</td>
                            <td style="text-align: center;">{{ round($tot['tk_training_org'] / $c) }}</td>
                            <td style="text-align: right;">{{ number_format($tot['tk_total_nilai'] / $c, 0, ',', '.') }}</td>

                            {{-- Pembantu & Pengemas --}}
                            <td style="text-align: right;">{{ number_format($tot['bumbu_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['karton_baru_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['karton_bekas_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['plastik_hd_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['lakban_besar_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['lakban_kecil_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['tali_rafia_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['fotocopy_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['sarung_tangan_plastik_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['sarung_tangan_kain_nilai'] / $c, 0, ',', '.') }}</td>

                            {{-- FOH --}}
                            <td style="text-align: right;">{{ number_format($tot['qc_pengawasan_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['listrik_air_telp_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['pemeliharaan_mesin_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['penyusutan_mesin_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['limbah_padat_nilai'] / $c, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($tot['limbah_kimia_nilai'] / $c, 0, ',', '.') }}</td>

                            {{-- Total Biaya Rata-rata --}}
                            <td style="text-align: right;" class="cell-yellow-highlight">
                                {{ number_format($tot['total_biaya_produksi'] / $c, 0, ',', '.') }}
                            </td>

                            {{-- Total WIP Rata-rata & Rendemen --}}
                            <td style="text-align: right;" class="cell-yellow-highlight">
                                {{ number_format($tot['total_wip_qty'] / $c, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right;" class="text-blue-ratio">
                                {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                            </td>

                            {{-- HPP/KG Rata-rata --}}
                            <td style="text-align: right;">
                                {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                            </td>

                            <td style="position: sticky; right: 0; z-index: 25; background: #ffc000;"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- MODALS PARTIALS --}}
@include('produksi.partials.modal-import-hasil')
@include('produksi.partials.modal-import-rekap')
@include('produksi.partials.modal-delete-confirm')

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
@endpush
@endsection
