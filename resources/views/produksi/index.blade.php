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
        <a href="{{ route('produksi.rekap') }}" class="btn-corp" title="Buka buku besar rekapitulasi HPP harian & bulanan">
            <span>Buku Rekapitulasi HPP</span>
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
            <button type="button" onclick="openModalImportHasil()" class="btn btn-secondary" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; background: #ffffff; border: 1px solid #cbd5e1; color: #334155;">
                <span>Import Excel</span>
            </button>

            <a href="{{ route('produksi.export-hasil', request()->all()) }}" class="btn btn-success" style="padding: 0.45rem 0.85rem; font-size: 0.825rem; background: #059669; border-color: #059669; color: #ffffff;">
                <span>Export Excel</span>
            </a>
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

    {{-- TABEL DATA HASIL PRODUKSI (POINT 9.B) --}}
    <div class="table-responsive-hasil">
        <table class="table-hasil">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">NO</th>
                    <th style="text-align: center;">TANGGAL</th>
                    <th style="text-align: center;">NO. PRODUKSI</th>
                    <th style="text-align: center;">KODE BATCH</th>
                    <th>GUDANG SIMPAN</th>
                    <th style="text-align: center;">JENIS</th>
                    <th>KODE BARANG</th>
                    <th>NAMA BARANG</th>
                    <th style="text-align: right;">QTY HASIL</th>
                    <th style="text-align: center;">SATUAN</th>
                    <th style="text-align: right;">BERAT (KG)</th>
                    <th style="text-align: right;">HPP SATUAN (RP)</th>
                    <th style="text-align: right;">TOTAL NILAI (RP)</th>
                    <th style="text-align: center;">SISA STOK (KG)</th>
                    <th style="text-align: center; width: 85px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pageTotalQtyKg = 0;
                    $pageTotalNilai = 0;
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
                            <span style="font-weight: 600; color: #334155;">{{ $prod?->gudang?->gudang_nm ?? 'Gudang Utama' }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($isFg)
                                <span class="badge-jenis-fg">FG</span>
                            @else
                                <span class="badge-jenis-wip">WIP</span>
                            @endif
                        </td>
                        <td>
                            <code style="background: #f1f5f9; padding: 0.15rem 0.35rem; border-radius: 4px; font-weight: 700; color: #0284c7;">
                                {{ $barang?->barang_cd ?? '-' }}
                            </code>
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $barang?->barang_nm ?? '-' }}
                        </td>
                        <td style="text-align: right;">
                            <div style="font-weight: 800; color: {{ $isFg ? '#047857' : '#0f172a' }};">
                                {{ number_format($qtyHasilTampil, $isFg ? 0 : 2, ',', '.') }}
                            </div>
                        </td>
                        <td style="text-align: center; font-size: 0.75rem; color: #64748b; font-weight: 700;">
                            {{ $satuanTampil }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #334155;">
                            {{ number_format($item->qty_kg, 2, ',', '.') }} kg
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
                                    {{ number_format($sisaStok, 1, ',', '.') }} kg
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
                        <td colspan="15" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            <div style="font-size: 0.95rem; font-weight: 700; color: #334155;">Belum Ada Data Hasil Barang Produksi</div>
                            <div style="font-size: 0.8rem; margin-top: 0.25rem;">Gunakan tombol <strong>+ Catat Hasil Produksi</strong> atau <strong>Import Excel</strong> di atas untuk menambahkan data.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($hasilItems->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="10" style="text-align: right; font-weight: 800; color: #0f172a; padding: 0.75rem 0.85rem;">
                            SUBTOTAL HALAMAN INI:
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #0284c7; padding: 0.75rem 0.85rem;">
                            {{ number_format($pageTotalQtyKg, 2, ',', '.') }} kg
                        </td>
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

{{-- MODALS PARTIALS --}}
@include('produksi.partials.modal-import-hasil')
@include('produksi.partials.modal-delete-confirm')

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
@endpush
@endsection
