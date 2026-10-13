@extends('layouts.app')

@section('title', 'Detail Lembar Produksi - ' . $produksi->produksi_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-show.css') }}">
@endpush

@section('content')
<div class="show-container">

    {{-- KOP SURAT RESMI CETAK (PRINT-ONLY) --}}
    <div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
                <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                    Pabrik Pengolahan F&amp;B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
                </p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 1.05rem; color: #0284c7; display: block;">LEMBAR BUKTI HASIL PRODUKSI &amp; HARGA POKOK (HPP)</strong>
                <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $produksi->produksi_no }}</span>
            </div>
        </div>
    </div>

    {{-- BARIS HEADER NAVIGASI & AKSI (NO-PRINT) --}}
    <div class="show-header no-print">
        <div>
            <a href="{{ route('produksi.rekap', ['tahun' => Carbon\Carbon::parse($produksi->produksi_tgl)->year, 'bulan' => Carbon\Carbon::parse($produksi->produksi_tgl)->month]) }}" class="btn-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Buku Rekap Bulanan
            </a>
            <div class="show-title-group">
                <h1 class="show-title">
                    Bukti Hasil Produksi: {{ $produksi->produksi_no }}
                </h1>
                @if ($produksi->status_cd === 'POSTED')
                    <span class="badge-status badge-status-posted">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        POSTED (Stok WIP Tersimpan)
                    </span>
                @else
                    <span class="badge-status badge-status-draft">
                        📝 DRAFT
                    </span>
                @endif
                <span class="badge-version">
                    v{{ $produksi->version_no ?? 1 }}
                </span>
                @if ($produksi->shift_cd)
                    <span class="badge-shift-pill {{ $produksi->shift_cd === 'A' ? 'badge-shift-a' : 'badge-shift-b' }}">
                        Shift {{ $produksi->shift_cd }}
                    </span>
                @endif
            </div>
        </div>

        <div class="show-actions">
            @if(Auth::user()->canCreateProduksi())
                <button type="button" id="btnOpenModalAdjustHpp" onclick="openModalAdjustHpp()" class="btn-adjust-hpp" title="Koreksi / Adjustment HPP dengan Standar Audit Trail Akuntansi">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Adjustment HPP</span>
                </button>
            @endif
            <button type="button" onclick="window.print()" class="btn-print-doc">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar HPP</span>
            </button>
        </div>
    </div>

    {{-- ALERT NOTIFIKASI SUKSES / ERROR --}}
    @if(session('success'))
        <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="no-print" style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $rendemenColor = $produksi->rendemen_persen >= 33.0 ? '#059669' : ($produksi->rendemen_persen >= 30.0 ? '#d97706' : '#dc2626');
        $rendemenClass = $produksi->rendemen_persen >= 33.0 ? 'kpi-rendemen-emerald' : ($produksi->rendemen_persen >= 30.0 ? 'kpi-rendemen-amber' : 'kpi-rendemen-red');

        $isIfm = str_contains(strtoupper($produksi->lini_produksi), 'IFM') || str_contains(strtoupper($produksi->lini_produksi), 'IFL');
        $expDate = !empty($produksi->exp_date)
            ? Carbon\Carbon::parse($produksi->exp_date)->format('d/m/Y')
            : ($isIfm
                ? Carbon\Carbon::parse($produksi->produksi_tgl)->addMonths(6)->format('d/m/Y')
                : Carbon\Carbon::parse($produksi->produksi_tgl)->addYear()->subDay()->format('d/m/Y'));

        $subtotalBahan = (float) ($produksi->total_bahan_nilai ?: ($produksi->singkong_nilai + $produksi->minyak_nilai + $produksi->bumbu_nilai));
        $subtotalKemasan = (float) ($produksi->karton_baru_nilai + $produksi->karton_bekas_nilai + $produksi->plastik_hd_nilai + $produksi->lakban_besar_nilai + $produksi->lakban_kecil_nilai + $produksi->tali_rafia_nilai);
        $subtotalEnergiBtkl = (float) ($produksi->cng_nilai + $produksi->tk_total_nilai);
        $subtotalFoh = (float) ($produksi->fotocopy_nilai + $produksi->sarung_tangan_plastik_nilai + $produksi->sarung_tangan_kain_nilai + $produksi->qc_pengawasan_nilai + $produksi->listrik_air_telp_nilai + $produksi->pemeliharaan_mesin_nilai + $produksi->penyusutan_mesin_nilai + $produksi->limbah_padat_nilai + $produksi->limbah_kimia_nilai);
    @endphp

    {{-- 4 KARTU METRIK UTAMA (EXECUTIVE KPI GRID) --}}
    <div class="kpi-grid">
        {{-- KPI 1: RENDEMEN SINGKONG --}}
        <div class="kpi-card {{ $rendemenClass }}">
            <div class="kpi-header">
                <span class="kpi-label">Rendemen Singkong</span>
                <span class="kpi-icon-badge" style="background: {{ $produksi->rendemen_persen >= 33.0 ? '#ecfdf5' : '#fef2f2' }}; color: {{ $rendemenColor }};">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="kpi-value" style="color: {{ $rendemenColor }};">
                {{ number_format($produksi->rendemen_persen, 2, ',', '.') }}%
            </div>
            <div class="kpi-subtext">
                Target Standar Pabrik: &ge; 33.00%
            </div>
        </div>

        {{-- KPI 2: HPP RIIL PER KG --}}
        <div class="kpi-card kpi-hpp">
            <div class="kpi-header">
                <span class="kpi-label">HPP Riil per Kg</span>
                <span class="kpi-icon-badge" style="background: #f0f9ff; color: #0284c7;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </span>
            </div>
            <div class="kpi-value">
                Rp {{ number_format($produksi->hpp_per_kg, 2, ',', '.') }}
            </div>
            <div class="kpi-subtext">
                Biaya pokok per kg produk jadi WIP
            </div>
        </div>

        {{-- KPI 3: TOTAL OUTPUT WIP --}}
        <div class="kpi-card kpi-wip">
            <div class="kpi-header">
                <span class="kpi-label">Total Output Barang Jadi (WIP)</span>
                <span class="kpi-icon-badge" style="background: #ecfdf5; color: #059669;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="kpi-value">
                {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} <span style="font-size: 0.95rem; font-weight: 600; color: #64748b;">kg</span>
            </div>
            <div class="kpi-subtext">
                @if ($produksi->qty_karton)
                    📦 {{ number_format($produksi->qty_karton, 0, ',', '.') }} Karton / Box Jadi
                @else
                    Hasil penimbangan fisik tersimpan
                @endif
            </div>
        </div>

        {{-- KPI 4: TOTAL BIAYA PRODUKSI --}}
        <div class="kpi-card kpi-cost">
            <div class="kpi-header">
                <span class="kpi-label">Total Biaya Operasional</span>
                <span class="kpi-icon-badge" style="background: #fffbeb; color: #d97706;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="kpi-value">
                Rp {{ number_format($produksi->total_biaya_produksi, 0, ',', '.') }}
            </div>
            <div class="kpi-subtext">
                Akumulasi Bahan, Upah, Energi &amp; FOH
            </div>
        </div>
    </div>

    {{-- GRID SECTION: INFORMASI OPERASIONAL & MANAJEMEN BATCH (2 KOLOM SIMETRIS) --}}
    <div class="spec-grid">
        {{-- CARD KIRI: PARAMETER MANUFAKTUR & SHIFT --}}
        <div class="spec-card">
            <div class="spec-card-header">
                <div class="spec-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Parameter Operasional &amp; Manufaktur</span>
                </div>
                @if ($produksi->shift_cd)
                    <span class="badge-shift-pill {{ $produksi->shift_cd === 'A' ? 'badge-shift-a' : 'badge-shift-b' }}">
                        Shift {{ $produksi->shift_cd }} ({{ $produksi->shift_cd === 'A' ? 'Pagi - Siang' : 'Siang - Malam' }})
                    </span>
                @else
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Shift Reguler</span>
                @endif
            </div>
            <div class="spec-card-body">
                <table class="spec-table">
                    <tr>
                        <td class="label-col">Tanggal Produksi</td>
                        <td class="value-col">{{ Carbon\Carbon::parse($produksi->produksi_tgl)->translatedFormat('l, d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Lini Produksi Pabrik</td>
                        <td class="value-col">{{ $produksi->lini_produksi }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Jam Mulai / Pengerjaan</td>
                        <td class="value-col">{{ $produksi->jam_produksi ? $produksi->jam_produksi . ' WIB' : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Varietas Singkong (RM)</td>
                        <td class="value-col" style="color: #0284c7;">{{ $produksi->varietas_singkong ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Jumlah Tenaga Kerja</td>
                        <td class="value-col">
                            {{ $produksi->tk_jumlah_org ?? ($produksi->tk_langsung_org + $produksi->tk_tidak_langsung_org + $produksi->tk_training_org) }} Orang Hadir
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- CARD KANAN: MANAJEMEN BATCH & GUDANG PERSEDIAAN --}}
        <div class="spec-card">
            <div class="spec-card-header">
                <div class="spec-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Identitas Batch &amp; Gudang Simpan</span>
                </div>
                <span class="badge-batch-pill">
                    {{ $produksi->batch_wip_no ?: $produksi->produksi_no }}
                </span>
            </div>
            <div class="spec-card-body">
                <table class="spec-table">
                    <tr>
                        <td class="label-col">No. Batch Persediaan</td>
                        <td class="value-col" style="font-family: monospace; color: #0284c7;">
                            {{ $produksi->batch_wip_no ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Rentang Seri Karton Box</td>
                        <td class="value-col" style="font-family: monospace;">
                            @if ($produksi->no_karton_awal && $produksi->no_karton_akhir)
                                No. {{ str_pad($produksi->no_karton_awal, 4, '0', STR_PAD_LEFT) }} s/d {{ str_pad($produksi->no_karton_akhir, 4, '0', STR_PAD_LEFT) }} ({{ $produksi->qty_karton }} Box)
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Estimasi Masa Kedaluwarsa</td>
                        <td class="value-col" style="color: #059669;">
                            {{ $expDate }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Gudang Penyimpanan</td>
                        <td class="value-col">{{ $produksi->gudang?->gudang_nm ?? 'Gudang Utama Pabrik' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Alur Pengeluaran Stok</td>
                        <td class="value-col" style="color: #64748b; font-weight: 600;">
                            FIFO Persediaan &bull; Penjualan Toko / SO
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL 1: RINCIAN HASIL TIMBANGAN BARANG JADI (WIP OUTPUT) --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                <span>Rincian Hasil Timbangan Barang Jadi (WIP Output)</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                {{ $produksi->outputs->count() }} Varian Output Terdaftar
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 140px;">Kategori Varian</th>
                        <th>Kode &amp; Nama Barang WIP</th>
                        <th style="width: 140px;">Batch Varian</th>
                        <th style="width: 140px; text-align: right;">Kuantitas (Kg)</th>
                        <th style="width: 140px; text-align: right;">HPP Satuan</th>
                        <th style="width: 170px; text-align: right;">Total Valuasi (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produksi->outputs as $idx => $out)
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $idx + 1 }}</td>
                            <td style="font-weight: 700; color: #0f172a;">
                                <span style="background: #f1f5f9; color: #334155; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                                    {{ $out->kategori_output }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: #0284c7; font-family: monospace;">{{ $out->barang?->barang_cd ?? '-' }}</strong> &ndash; 
                                <span style="font-weight: 600;">{{ $out->barang?->barang_nm ?? '-' }}</span>
                            </td>
                            <td style="font-family: monospace; font-weight: 700; color: #475569;">
                                {{ $out->batch_no }}
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #059669; font-family: monospace;">
                                {{ number_format($out->qty_kg, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align: right; color: #475569; font-family: monospace;">
                                Rp {{ number_format($out->hpp_satuan, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;">
                                Rp {{ number_format($out->total_nilai, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                Belum ada rincian output timbangan tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td colspan="4" style="padding: 0.75rem 1rem; text-align: right; font-size: 0.8rem; color: #475569;">
                            TOTAL WIP TERTIMBANG:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #059669; font-size: 0.95rem; font-family: monospace;">
                            {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #64748b; font-size: 0.8rem;">
                            HPP/KG:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #0284c7; font-size: 0.95rem; font-family: monospace;">
                            Rp {{ number_format($produksi->hpp_per_kg, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TABEL 2: RINCIAN KOMPONEN BIAYA PRODUKSI (STANDAR AKUNTANSI BIAYA TERSTRUKTUR) --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rincian Komponen Biaya Produksi (Bahan Baku, Energi, Upah &amp; FOH)</span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                Audit Kalkulasi Akuntansi Biaya Manufaktur
            </span>
        </div>

        {{-- SUMMARY TOP BAR (4 KELOMPOK BIAYA) --}}
        <div class="cost-summary-bar">
            <div class="cost-summary-pill">
                <span class="cost-pill-label" style="color: #059669;">🌿 Bahan Baku (Direct Material)</span>
                <span class="cost-pill-val">Rp {{ number_format($subtotalBahan, 0, ',', '.') }}</span>
            </div>
            <div class="cost-summary-pill">
                <span class="cost-pill-label" style="color: #0284c7;">📦 Kemasan &amp; Pembantu</span>
                <span class="cost-pill-val">Rp {{ number_format($subtotalKemasan, 0, ',', '.') }}</span>
            </div>
            <div class="cost-summary-pill">
                <span class="cost-pill-label" style="color: #d97706;">⚡ Energi &amp; Tenaga Kerja</span>
                <span class="cost-pill-val">Rp {{ number_format($subtotalEnergiBtkl, 0, ',', '.') }}</span>
            </div>
            <div class="cost-summary-pill">
                <span class="cost-pill-label" style="color: #7c3aed;">🏭 Overhead Pabrik (FOH)</span>
                <span class="cost-pill-val">Rp {{ number_format($subtotalFoh, 0, ',', '.') }}</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th>Komponen Biaya Manufaktur</th>
                        <th>Rincian Fisik / Parameter Kuantitas</th>
                        <th style="width: 200px; text-align: right;">Nominal Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. KELOMPOK A: BAHAN BAKU LANGSUNG --}}
                    <tr class="cost-group-header" style="background: #f0fdf4;">
                        <td colspan="3" style="color: #166534;">
                            A. Bahan Baku Langsung (Direct Materials)
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">Singkong Mentah</td>
                        <td>{{ number_format($produksi->singkong_qty, 0, ',', '.') }} kg (Input Bahan Utama)</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">Rp {{ number_format($produksi->singkong_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">Minyak Goreng Sawit</td>
                        <td>{{ number_format($produksi->minyak_sawit_qty, 1, ',', '.') }} kg</td>
                        <td style="text-align: right; color: #64748b; font-family: monospace;">-</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">Minyak Goreng Kelapa (Barco)</td>
                        <td>{{ number_format($produksi->minyak_kelapa_qty, 1, ',', '.') }} kg</td>
                        <td style="text-align: right; color: #64748b; font-family: monospace;">-</td>
                    </tr>
                    <tr style="background: #f8fafc;">
                        <td style="padding-left: 2rem; font-weight: 600; color: #0284c7;">&bull; Subtotal Minyak Goreng</td>
                        <td style="color: #0284c7; font-weight: 600;">Rasio terhadap Singkong: {{ number_format($produksi->minyak_rasio_persen, 2, ',', '.') }}%</td>
                        <td style="text-align: right; font-weight: 700; color: #0284c7; font-family: monospace;">Rp {{ number_format($produksi->minyak_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">Bumbu &amp; Perenyah</td>
                        <td>Garam, bumbu racik &amp; bahan perenyah</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">Rp {{ number_format($produksi->bumbu_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="cost-group-subtotal" style="color: #166534;">
                        <td colspan="2" style="padding-left: 1.5rem;">SUBTOTAL BAHAN BAKU LANGSUNG:</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($subtotalBahan, 0, ',', '.') }}</td>
                    </tr>

                    {{-- 2. KELOMPOK B: KEMASAN & MATERIAL PACKING --}}
                    <tr class="cost-group-header" style="background: #f0f9ff;">
                        <td colspan="3" style="color: #0369a1;">
                            B. Biaya Kemasan &amp; Material Packing (Packaging Materials)
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Karton Baru (FL)</td>
                        <td>Kemasan karton baru box luar</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->karton_baru_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Karton Bekas</td>
                        <td>Kemasan karton daur ulang / karton packing alternatif</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->karton_bekas_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Plastik HD 90x100</td>
                        <td>Plastik inner pelindung karton</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->plastik_hd_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Lakban Besar &amp; Kecil</td>
                        <td>Isolasi perekat penutup karton</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->lakban_besar_nilai + $produksi->lakban_kecil_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Tali Rafia</td>
                        <td>Pengikat packing karton</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->tali_rafia_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="cost-group-subtotal" style="color: #0369a1;">
                        <td colspan="2" style="padding-left: 1.5rem;">SUBTOTAL BIAYA KEMASAN:</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($subtotalKemasan, 0, ',', '.') }}</td>
                    </tr>

                    {{-- 3. KELOMPOK C: ENERGI & TENAGA KERJA (BTKL) --}}
                    <tr class="cost-group-header" style="background: #fffbeb;">
                        <td colspan="3" style="color: #92400e;">
                            C. Energi &amp; Biaya Tenaga Kerja Langsung (BTKL)
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">Gas Alam (CNG)</td>
                        <td>{{ number_format($produksi->cng_mmbtu, 3, ',', '.') }} MMBTU (Tarif: Rp {{ number_format($produksi->cng_tarif, 0, ',', '.') }})</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">Rp {{ number_format($produksi->cng_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #0f172a; padding-left: 1.5rem;">Upah Tenaga Kerja Langsung (BTKL)</td>
                        <td>Tenaga Kerja Hadir: {{ $produksi->tk_jumlah_org ?? ($produksi->tk_langsung_org + $produksi->tk_tidak_langsung_org + $produksi->tk_training_org) }} Orang</td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace;">Rp {{ number_format($produksi->tk_total_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="cost-group-subtotal" style="color: #92400e;">
                        <td colspan="2" style="padding-left: 1.5rem;">SUBTOTAL ENERGI &amp; UPAH BTKL:</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($subtotalEnergiBtkl, 0, ',', '.') }}</td>
                    </tr>

                    {{-- 4. KELOMPOK D: BIAYA OVERHEAD PABRIK (FOH) & UTILITAS --}}
                    <tr class="cost-group-header" style="background: #faf5ff;">
                        <td colspan="3" style="color: #6b21a8;">
                            D. Biaya Overhead Pabrik (FOH) &amp; Utilitas
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Listrik, Air &amp; Telepon (Utilitas)</td>
                        <td>Utilitas operasional harian pabrik</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->listrik_air_telp_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Pemeliharaan &amp; Service Mesin</td>
                        <td>Perawatan preventif &amp; suku cadang fryer/boiler</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->pemeliharaan_mesin_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Pengawasan Mutu (QC Lab)</td>
                        <td>Inspeksi standar mutu, organoleptik &amp; sampling</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->qc_pengawasan_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Penyusutan Mesin &amp; Gedung</td>
                        <td>Amortisasi aset fasilitas manufaktur</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->penyusutan_mesin_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">Pengolahan Limbah &amp; IPAL</td>
                        <td>Treatment limbah padat singkong &amp; kimia IPAL</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->limbah_padat_nilai + $produksi->limbah_kimia_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 1.5rem;">ATK &amp; Sanitasi Operator</td>
                        <td>Fotocopy, sarung tangan plastik &amp; kain sanitasi</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($produksi->fotocopy_nilai + $produksi->sarung_tangan_plastik_nilai + $produksi->sarung_tangan_kain_nilai, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="cost-group-subtotal" style="color: #6b21a8;">
                        <td colspan="2" style="padding-left: 1.5rem;">SUBTOTAL BIAYA OVERHEAD PABRIK (FOH):</td>
                        <td style="text-align: right; font-family: monospace;">Rp {{ number_format($subtotalFoh, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot class="cost-total-footer">
                    <tr>
                        <td colspan="2" style="font-size: 0.95rem;">TOTAL BIAYA PRODUKSI HARIAN:</td>
                        <td style="text-align: right; font-size: 1.15rem; font-family: monospace;">
                            Rp {{ number_format($produksi->total_biaya_produksi, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- TABEL 3: RIWAYAT ADJUSTMENT & AUDIT TRAIL HPP --}}
    <div class="card-enterprise">
        <div class="card-enterprise-header">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Adjustment &amp; Audit Trail HPP</span>
                <span class="badge-version">
                    Versi: v{{ $produksi->version_no ?? 1 }}
                </span>
            </div>
            <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                Catatan resmi setiap revisi atau penyesuaian nominal HPP produksi harian
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-enterprise">
                <thead>
                    <tr>
                        <th style="width: 140px;">No. Berita Acara</th>
                        <th style="width: 110px;">Tanggal</th>
                        <th style="width: 110px;">Komponen</th>
                        <th style="width: 170px; text-align: right;">Perubahan Biaya</th>
                        <th style="width: 170px; text-align: right;">Dampak HPP / Kg</th>
                        <th>Alasan &amp; Dasar Dokumen</th>
                        <th style="width: 130px;">Operator</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produksi->adjustmentLogs as $log)
                        @php
                            $isNaik = (float) $log->selisih_hpp > 0;
                            $isTurun = (float) $log->selisih_hpp < 0;
                        @endphp
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                                {{ $log->adj_no }}
                            </td>
                            <td>
                                {{ Carbon\Carbon::parse($log->adj_tgl)->format('d/m/Y') }}
                                <div style="font-size: 0.7rem; color: #94a3b8;">{{ $log->created_at?->format('H:i') }} WIB</div>
                            </td>
                            <td>
                                <span style="background: #f1f5f9; color: #334155; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                    {{ $log->kategori_komponen }}
                                </span>
                            </td>
                            <td style="text-align: right; font-family: monospace;">
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    Rp {{ number_format($log->total_biaya_sebelum, 0, ',', '.') }} &rarr;
                                </div>
                                <div style="font-weight: 700; color: #0f172a;">
                                    Rp {{ number_format($log->total_biaya_sesudah, 0, ',', '.') }}
                                </div>
                                <div style="font-size: 0.7rem; font-weight: 700; color: {{ (float)$log->selisih_biaya >= 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ (float)$log->selisih_biaya >= 0 ? '+' : '' }}Rp {{ number_format($log->selisih_biaya, 0, ',', '.') }}
                                </div>
                            </td>
                            <td style="text-align: right; font-family: monospace;">
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    Rp {{ number_format($log->hpp_sebelum, 2, ',', '.') }} &rarr;
                                </div>
                                <div style="font-weight: 800; color: #0284c7;">
                                    Rp {{ number_format($log->hpp_sesudah, 2, ',', '.') }}
                                </div>
                                <div style="font-size: 0.7rem; font-weight: 700; color: {{ $isNaik ? '#dc2626' : ($isTurun ? '#16a34a' : '#64748b') }};">
                                    {{ $isNaik ? '+' : '' }}Rp {{ number_format($log->selisih_hpp, 2, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #334155;">{{ $log->alasan_txt }}</div>
                                @if(!empty($log->perubahan_field_json))
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem;">
                                        Rincian: 
                                        @foreach($log->perubahan_field_json as $fKey => $fVal)
                                            <span style="background: #f8fafc; padding: 1px 4px; border: 1px solid #e2e8f0; border-radius: 3px; margin-right: 3px;">
                                                {{ $fVal['label'] ?? $fKey }}: Rp {{ number_format($fVal['old'], 0, ',', '.') }} &rarr; Rp {{ number_format($fVal['new'], 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td style="color: #475569; font-weight: 600;">
                                {{ $log->created_by ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2rem 1rem; text-align: center; color: #94a3b8;">
                                <div style="font-weight: 700; color: #64748b; font-size: 0.85rem;">Belum Pernah Ada Adjustment HPP</div>
                                <div style="font-size: 0.75rem;">Dokumen ini masih mencatat nilai kalkulasi produksi orisinal (Versi v1).</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL ADJUSTMENT HPP --}}
@include('produksi.partials.modal-adjust-hpp')
@endsection

@push('scripts')
    <script>
        window.adjProdConfig = {
            totalWipQty: {{ (float) $produksi->total_wip_qty }},
            totalBiayaAwal: {{ (float) $produksi->total_biaya_produksi }},
            hppAwal: {{ (float) $produksi->hpp_per_kg }},
            singkongAwal: {{ (float) $produksi->singkong_nilai }},
            minyakAwal: {{ (float) $produksi->minyak_nilai }},
            bumbuAwal: {{ (float) $produksi->bumbu_nilai }},
            tkAwal: {{ (float) $produksi->tk_total_nilai }},
            listrikAwal: {{ (float) $produksi->listrik_air_telp_nilai }},
            cngAwal: {{ (float) $produksi->cng_nilai }},
        };
    </script>
    <script src="{{ asset('js/produksi/produksi-show.js') }}"></script>
@endpush
