@extends('layouts.app')

@section('title', 'Barang Keluar & Pemakaian Bahan - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Barang Keluar &amp; Pemakaian Bahan (Outbound)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Buku catatan fisik pengeluaran bahan baku &amp; bahan penolong ke proses produksi, packing, seasoning, atau afkir ulang (FIFO per Batch).
        </p>
    </div>
    <div>
        @if (Auth::user()?->canCreatePemakaian())
            <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Barang Keluar
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (SERAGAM DENGAN PO, BARANG MASUK & LACAK STOK) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
    {{-- 1. SEMUA DOKUMEN PENGELUARAN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                    Semua Dokumen Keluar
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['total'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total bukti pengeluaran bahan tercatat
        </div>
    </div>

    {{-- 2. PENGELUARAN HARI INI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Pengeluaran Hari Ini
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($kpiCounts['today'] ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Bongkar ke lantai produksi hari ini
        </div>
    </div>

    {{-- 3. BAHAN BAKU SINGKONG --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Bahan Baku Singkong
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($ringkasan['singkong_qty'] ?? 0, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #b45309; font-weight: 600; margin-top: 0.4rem;">
            Rp {{ number_format($ringkasan['singkong_nilai'] ?? 0, 0, ',', '.') }} nilai terpakai
        </div>
    </div>

    {{-- 4. TOTAL BIAYA BAHAN (HPP) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Total Biaya Bahan (HPP)
                </span>
                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; font-family: monospace;">
                    Rp {{ number_format($ringkasan['grand_total_nilai'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Dari {{ number_format($ringkasan['total_item_count'] ?? 0) }} rincian bahan keluar
        </div>
    </div>
</div>

{{-- RINCIAN REKAPITULASI BAHAN KELUAR PABRIK --}}
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.65rem; font-size: 0.8rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <span style="font-weight: 700; color: #475569; text-transform: uppercase; font-size: 0.725rem; letter-spacing: 0.04em;">
            Rincian Kategori:
        </span>
        <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 4px; border: 1px solid #fde68a;">
            🌾 Singkong: {{ number_format($ringkasan['singkong_qty'] ?? 0, 0, ',', '.') }} kg (Rp {{ number_format($ringkasan['singkong_nilai'] ?? 0, 0, ',', '.') }})
        </span>
        <span class="badge" style="background: #fef9c3; color: #854d0e; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 4px; border: 1px solid #fef08a;">
            🛢️ Minyak: {{ number_format($ringkasan['minyak_qty'] ?? 0, 0, ',', '.') }} kg (Rp {{ number_format($ringkasan['minyak_nilai'] ?? 0, 0, ',', '.') }})
        </span>
        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 4px; border: 1px solid #bae6fd;">
            🧂 Bumbu &amp; Penolong: {{ number_format($ringkasan['bumbu_qty'] ?? 0, 0, ',', '.') }} kg (Rp {{ number_format($ringkasan['bumbu_nilai'] ?? 0, 0, ',', '.') }})
        </span>
        <span class="badge" style="background: #d1fae5; color: #065f46; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 4px; border: 1px solid #a7f3d0;">
            📦 Kemasan: {{ number_format($ringkasan['kemasan_qty'] ?? 0, 0, ',', '.') }} unit (Rp {{ number_format($ringkasan['kemasan_nilai'] ?? 0, 0, ',', '.') }})
        </span>
    </div>
    <div style="font-size: 0.75rem; color: #64748b;">
        Biaya Bahan Terakumulasi Otomatis
    </div>
</div>

{{-- WADAH TABEL UTAMA (PERSIS FORMAT CARD PO & BARANG MASUK) --}}
<div class="card">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        {{-- Form Filter & Pencarian Fleksibel --}}
        <form action="{{ route('gudang.pemakaian.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari No Outbound, batch, barang, SPK..." class="form-control" style="padding: 0.45rem 0.75rem; width: 220px; font-size: 0.85rem;">

            <select name="tujuan" class="form-control" style="padding: 0.45rem 0.75rem; width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Lini Tujuan --</option>
                @foreach ($tujuanOptions as $opt)
                    <option value="{{ $opt }}" {{ ($tujuan === $opt) ? 'selected' : '' }}>
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
            
            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; width: 165px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Gudang --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                        {{ $gdg->display_name }}
                    </option>
                @endforeach
            </select>

            <div style="display: flex; align-items: center; gap: 0.25rem;">
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" title="Dari Tanggal" class="form-control" style="padding: 0.45rem 0.5rem; font-size: 0.825rem; width: 130px;">
                <span style="font-size: 0.75rem; color: #64748b;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" title="Sampai Tanggal" class="form-control" style="padding: 0.45rem 0.5rem; font-size: 0.825rem; width: 130px;">
            </div>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($gudangId) || !empty($tujuan) || !empty($startDate) || !empty($endDate))
                <a href="{{ route('gudang.pemakaian.index', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            {{-- Switcher Bersih & Halus (Tanpa Emoji) --}}
            <div style="display: inline-flex; background: #f1f5f9; padding: 0.2rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                <a href="{{ route('gudang.pemakaian.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
                   style="text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background: {{ $viewType === 'item' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'item' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'item' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                    Buku Rekap Bahan (Excel Grid)
                </a>
                <a href="{{ route('gudang.pemakaian.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
                   style="text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; background: {{ $viewType === 'header' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'header' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'header' ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">
                    Daftar Dokumen Pengeluaran
                </a>
            </div>

            <span style="color: #64748b; font-size: 0.85rem;">
                Total: <strong style="color: #0f172a;">{{ $dataList->total() }}</strong> {{ $viewType === 'item' ? 'baris data' : 'dokumen' }}
            </span>
        </div>
    </div>

    @if ($viewType === 'item')
        {{-- VIEW 1: BUKU REKAP BAHAN KELUAR (EXCEL GRID COMPREHENSIVE YANG DISUKAI OPERATOR) --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 140px;">Tgl &amp; No. Dokumen</th>
                        <th style="min-width: 180px;">Gudang &amp; Tujuan / SPK</th>
                        <th style="min-width: 190px;">Bahan Keluar</th>
                        <th style="min-width: 130px;">No. Batch (FIFO)</th>
                        <th style="min-width: 110px; text-align: right;">Qty Keluar</th>
                        <th style="min-width: 130px; text-align: right;">Total Biaya (HPP)</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $row)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                                    {{ \Carbon\Carbon::parse($row->header?->pakai_tgl)->format('d/m/Y') }}
                                </div>
                                <a href="{{ route('gudang.pemakaian.show', $row->header?->pakai_id) }}" style="font-size: 0.85rem; font-weight: 700; color: #dc2626; text-decoration: none;">
                                    {{ $row->header?->pakai_no ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                                    {{ $row->header?->gudang?->gudang_nm ?? '-' }}
                                </div>
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.725rem; margin-top: 0.2rem; display: inline-block;">
                                    {{ $row->keterangan_txt ?? ($row->header?->tujuan_pemakaian ?? '-') }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.875rem;">
                                    {{ $row->barang?->barang_nm ?? '-' }}
                                </strong>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem;">
                                    <span style="font-family: monospace; font-size: 0.75rem; color: #64748b;">
                                        {{ $row->barang?->barang_cd ?? '-' }}
                                    </span>
                                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; padding: 0.1rem 0.35rem;">
                                        {{ $row->barang?->jenisBarang?->jenis_barang_nm ?? ($row->barang?->jenisBarang?->jenis_barang_cd ?? '-') }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.825rem; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; display: inline-block;">
                                    {{ $row->batch_no }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span style="font-weight: 700; color: #dc2626; font-size: 0.95rem;">
                                    {{ number_format((float) $row->qty_keluar, 2, ',', '.') }}
                                </span>
                                <span style="font-size: 0.8rem; color: #64748b; margin-left: 0.2rem;">
                                    {{ $row->barang?->satuanDasar?->satuan_nm ?? '-' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                    Rp {{ number_format((float) $row->total_harga, 0, ',', '.') }}
                                </div>
                                <small style="color: #64748b; font-size: 0.725rem;">
                                    @ Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}
                                </small>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.pemakaian.show', $row->header?->pakai_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumen Pengeluaran &amp; Cetak BPPB">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat transaksi pengeluaran barang. Klik tombol <strong>"+ Catat Barang Keluar"</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- VIEW 2: DAFTAR DOKUMEN PENGELUARAN (HEADER VIEW) --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 160px;">Nomor Pengeluaran</th>
                        <th style="min-width: 120px;">Tanggal Keluar</th>
                        <th style="min-width: 150px;">Gudang Asal</th>
                        <th style="min-width: 180px;">Tujuan Pemakaian / SPK</th>
                        <th style="min-width: 110px; text-align: center;">Total Item</th>
                        <th style="min-width: 160px;">Catatan</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $hdr)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                                {{ $dataList->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('gudang.pemakaian.show', $hdr->pakai_id) }}" style="font-weight: 700; color: #dc2626; text-decoration: none; font-size: 0.9rem;">
                                    {{ $hdr->pakai_no }}
                                </a>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($hdr->pakai_tgl)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #334155;">{{ $hdr->gudang?->gudang_nm ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700;">
                                    {{ $hdr->tujuan_pemakaian }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">
                                    {{ $hdr->details->count() }} Bahan
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 0.85rem;">{{ $hdr->catatan_txt ?? '-' }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.pemakaian.show', $hdr->pakai_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumen Lengkap">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat dokumen pengeluaran barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($dataList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $dataList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
