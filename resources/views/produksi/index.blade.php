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
        <div style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.06em; display: flex; align-items: center; gap: 0.35rem;">
            <span>Produksi &amp; Manufaktur</span>
            <span>&bull;</span>
            <span>PT Mirasa Food Industry</span>
        </div>
        <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0 0 0; letter-spacing: -0.02em;">
            Hasil Barang Produksi Pabrik
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
            Pencatatan output barang jadi (Finish Good) &amp; setengah jadi (WIP), histori pengerjaan, dan mutasi kartu stok gudang simpan.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('produksi.export-hasil', request()->all()) }}" 
           class="btn btn-secondary" 
           style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
           onmouseover="this.style.background='#ecfdf5'" 
           onmouseout="this.style.background='#ffffff'" 
           title="Download data hasil produksi format Excel (.xlsx)">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel</span>
        </a>

        <button type="button" 
                onclick="openModalImportHasil()" 
                class="btn btn-secondary" 
                style="background: #ffffff; border: 1.5px solid #7c3aed; color: #7c3aed; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
                onmouseover="this.style.background='#faf5ff'" 
                onmouseout="this.style.background='#ffffff'" 
                title="Import data hasil produksi dari berkas Excel">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Import Excel</span>
        </button>

        <a href="{{ route('produksi.rekap') }}" 
           class="btn btn-secondary" 
           style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
           onmouseover="this.style.background='#f0f9ff'" 
           onmouseout="this.style.background='#ffffff'" 
           title="Buka buku besar rekapitulasi HPP harian &amp; bulanan">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Buku Rekapitulasi HPP</span>
        </a>

        @if(Auth::user()->canCreateProduksi())
            <a href="{{ route('produksi.create') }}" 
               class="btn btn-primary" 
               style="background: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
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
    
    {{-- BARIS FILTER PILLS & AKSI IMPORT / EXPORT (BLUEPRINT 9.B) --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
        <div class="filter-pills-bar">
            <a href="{{ route('produksi.index', array_merge(request()->except('jenis_cd', 'page'))) }}" 
               class="filter-pill-link {{ empty(request('jenis_cd')) ? 'active' : '' }}">
                <span>Semua Output</span>
            </a>
            <a href="{{ route('produksi.index', array_merge(request()->except('page'), ['jenis_cd' => 'FG'])) }}" 
               class="filter-pill-link {{ request('jenis_cd') == 'FG' ? 'active' : '' }}">
                <span>Finish Good (FG)</span>
            </a>
            <a href="{{ route('produksi.index', array_merge(request()->except('page'), ['jenis_cd' => 'WIP'])) }}" 
               class="filter-pill-link {{ request('jenis_cd') == 'WIP' ? 'active' : '' }}">
                <span>Olahan Curah (WIP)</span>
            </a>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('produksi.export-hasil', request()->all()) }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #059669; color: #059669; font-size: 0.8rem; font-weight: 700; padding: 0.45rem 0.85rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
               onmouseover="this.style.background='#ecfdf5'" 
               onmouseout="this.style.background='#ffffff'" 
               title="Download daftar hasil produksi format Excel (.xlsx)">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>

            <button type="button" 
                    onclick="openModalImportHasil()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #7c3aed; color: #7c3aed; font-size: 0.8rem; font-weight: 700; padding: 0.45rem 0.85rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" 
                    onmouseover="this.style.background='#faf5ff'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Import data hasil produksi dari berkas Excel">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import Excel</span>
            </button>
        </div>
    </div>

    {{-- FORM FILTER PENCARIAN (BLUEPRINT POINT 2: GUDANG, NAMA BARANG, KODE BARANG, KODE BATCH) --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('produksi.index') }}">
            @if(request('jenis_cd'))
                <input type="hidden" name="jenis_cd" value="{{ request('jenis_cd') }}">
            @endif
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
                    <label class="filter-label">Kode Barang</label>
                    <input type="text" name="barang_cd" class="filter-input" placeholder="Misal: BRG-001 / WIP..." value="{{ request('barang_cd') }}">
                </div>

                <div>
                    <label class="filter-label">Nama Barang</label>
                    <input type="text" name="barang_nm" class="filter-input" placeholder="Misal: Ping-Ping / Asin..." value="{{ request('barang_nm') }}">
                </div>

                <div>
                    <label class="filter-label">Kode Batch</label>
                    <input type="text" name="batch_no" class="filter-input" placeholder="Misal: A0001 / B2063..." value="{{ request('batch_no') }}">
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
                        Terapkan Filter
                    </button>
                    <a href="{{ route('produksi.index', request('jenis_cd') ? ['jenis_cd' => request('jenis_cd')] : []) }}" class="btn btn-secondary" style="padding: 0.45rem 0.65rem; font-size: 0.825rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABEL DATA HASIL PRODUKSI (OPTIMAL & ERGONOMIS - ZERO HORIZONTAL SCROLL) --}}
    <div class="table-responsive-hasil">
        <table class="table-hasil">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">NO</th>
                    <th>DOKUMEN &amp; TANGGAL</th>
                    <th style="text-align: center;">KODE BATCH</th>
                    <th style="text-align: center; width: 75px;">JENIS</th>
                    <th>PRODUK / BARANG</th>
                    <th style="text-align: right;">QTY HASIL</th>
                    <th style="text-align: center; width: 75px;">SATUAN</th>
                    <th style="text-align: right;">BERAT (KG)</th>
                    <th style="text-align: right;">FINANSIAL (RP)</th>
                    <th style="text-align: center;">STOK GUDANG</th>
                    <th style="text-align: center; width: 80px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pageTotalQtyKg = 0;
                    $pageTotalNilai = 0;
                    $pageTotalQtyHasil = 0;
                @endphp
                @forelse($hasilItems as $idx => $item)
                    @php
                        $prod = $item->produksi;
                        $barang = $item->barang;
                        $isFg = ($item->jenis_cd === 'FG' || ($barang && $barang->isFinishGood()));
                        $pageTotalQtyKg += (float) $item->qty_kg;
                        $pageTotalNilai += (float) $item->total_nilai;
                        $sisaStok = (float) ($item->sisa_stok ?? $item->qty_kg);
                        $menuId = 'menu-hasil-' . $item->output_id;
                        $satuanTampil = $item->satuan_cd ?: ($barang?->satuan?->satuan_nm ?? ($isFg ? 'DUS' : 'KG'));
                        $qtyHasilTampil = $item->qty_hasil > 0 ? (float) $item->qty_hasil : (float) $item->qty_kg;
                        $pageTotalQtyHasil += $qtyHasilTampil;
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                            {{ $hasilItems->firstItem() + $idx }}
                        </td>

                        {{-- 1. DOKUMEN & TANGGAL --}}
                        <td>
                            @if($prod)
                                <a href="{{ route('produksi.show', $prod->produksi_id) }}" class="cell-doc-link">
                                    {{ $prod->produksi_no }}
                                </a>
                            @else
                                <span style="color: #64748b;">-</span>
                            @endif
                            <div class="cell-subtext">
                                <span>{{ $prod ? \Carbon\Carbon::parse($prod->produksi_tgl)->format('d/m/Y') : '-' }}</span>
                                <span>&bull;</span>
                                <span style="font-weight: 600;">{{ $prod?->gudang?->gudang_nm ?? 'Gudang Utama' }}</span>
                            </div>
                        </td>

                        {{-- 2. KODE BATCH (TERPISAH) --}}
                        <td style="text-align: center;">
                            <span class="badge-batch-wip">{{ $item->batch_no ?? ($prod->batch_wip_no ?? '-') }}</span>
                        </td>

                        {{-- 3. JENIS (TERPISAH) --}}
                        <td style="text-align: center;">
                            @if($isFg)
                                <span class="badge-jenis-fg">FG</span>
                            @else
                                <span class="badge-jenis-wip">WIP</span>
                            @endif
                        </td>

                        {{-- 4. PRODUK / BARANG --}}
                        <td>
                            <div class="cell-primary-title">
                                {{ $barang?->barang_nm ?? '-' }}
                            </div>
                            <div class="cell-subtext">
                                <code style="background: #f1f5f9; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 700; color: #0284c7; font-size: 0.725rem;">
                                    {{ $barang?->barang_cd ?? '-' }}
                                </code>
                            </div>
                        </td>

                        {{-- 5. QTY HASIL (TERPISAH) --}}
                        <td style="text-align: right;">
                            <span class="cell-qty-val" style="color: {{ $isFg ? '#047857' : '#0f172a' }};">
                                {{ number_format($qtyHasilTampil, $isFg ? 0 : 2, ',', '.') }}
                            </span>
                        </td>

                        {{-- 6. SATUAN (TERPISAH) --}}
                        <td style="text-align: center;">
                            <span class="badge-satuan-pill">{{ $satuanTampil }}</span>
                        </td>

                        {{-- 7. BERAT KG (TERPISAH) --}}
                        <td style="text-align: right; font-weight: 700; color: #1e293b; font-variant-numeric: tabular-nums;">
                            {{ number_format($item->qty_kg, 2, ',', '.') }} kg
                        </td>

                        {{-- 8. FINANSIAL & HPP --}}
                        <td style="text-align: right;">
                            <div class="cell-nilai-val">
                                Rp {{ number_format($item->total_nilai, 0, ',', '.') }}
                            </div>
                            <div class="cell-subtext" style="justify-content: flex-end;">
                                @ Rp {{ number_format($item->hpp_satuan, 0, ',', '.') }} / kg
                            </div>
                        </td>

                        {{-- 9. SISA STOK GUDANG --}}
                        <td style="text-align: center;">
                            @if($sisaStok > 0)
                                <span class="badge-stok-tersedia">
                                    {{ number_format($sisaStok, 1, ',', '.') }} kg
                                </span>
                            @else
                                <span class="badge-stok-habis">
                                    0 kg (Habis)
                                </span>
                            @endif
                        </td>

                        {{-- 10. AKSI --}}
                        <td style="text-align: center; position: relative;">
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                Aksi ▼
                            </button>

                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                @if($prod)
                                    <a href="{{ route('produksi.show', $prod->produksi_id) }}" class="action-dropdown-item">
                                        <span>Detail Dokumen</span>
                                    </a>
                                    <a href="{{ route('produksi.cetak-stiker', $prod->produksi_id) }}" class="action-dropdown-item" target="_blank">
                                        <span>Cetak Stiker Karton</span>
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $prod->produksi_id }}, '{{ $prod->produksi_no }}', '{{ \Carbon\Carbon::parse($prod->produksi_tgl)->format('d/m/Y') }}', '{{ $prod->shift_cd }}', '{{ $prod->batch_wip_no }}')">
                                        <span>Hapus Produksi</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            <div style="font-size: 0.95rem; font-weight: 700; color: #334155;">Belum Ada Data Hasil Barang Produksi</div>
                            <div style="font-size: 0.8rem; margin-top: 0.25rem;">Gunakan tombol <strong>+ Catat Hasil Produksi</strong> atau <strong>Import Excel</strong> di atas untuk menambahkan data.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($hasilItems->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 800; color: #0f172a; padding: 0.75rem 0.85rem;">
                            SUBTOTAL HALAMAN INI:
                        </td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right; font-weight: 800; color: #0284c7; padding: 0.75rem 0.85rem;">
                            {{ number_format($pageTotalQtyKg, 2, ',', '.') }} kg
                        </td>
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

{{-- MODALS PARTIALS --}}
@include('produksi.partials.modal-import-hasil')
@include('produksi.partials.modal-delete-confirm')

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
@endpush
@endsection
