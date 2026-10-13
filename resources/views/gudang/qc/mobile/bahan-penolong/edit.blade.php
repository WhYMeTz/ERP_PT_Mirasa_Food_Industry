@extends('layouts.qc-mobile')

@section('title', 'Koreksi QC ' . ($qc->kategori_barang ?? 'Bahan Penolong') . ': ' . $qc->qc_no . ' - PT Mirasa')
@section('hide_bottom_nav', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-mobile-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong-detail.css') }}">
@endpush

@php
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;

    $curKomoditas = strtoupper($qc->kategori_barang ?? 'MSG');
    if (!in_array($curKomoditas, ['MSG', 'GARAM', 'PERENYAH', 'BUMBU'])) {
        $curKomoditas = 'BAHAN_PENOLONG';
    }

    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (
            str_contains($nm, 'MINYAK') || str_contains($nm, 'SINGKONG') || str_contains($nm, 'PUYUR') ||
            str_contains($nm, 'OPAK') || str_contains($nm, 'UBI') || str_contains($nm, 'PLASTIK') ||
            str_contains($nm, 'KARTON') || str_contains($nm, 'BOX') || str_contains($nm, 'DUS')
        ) {
            return 'LAINNYA';
        }
        if (str_contains($nm, 'BUMBU') || str_starts_with($cd, 'BP-BM') || str_starts_with($cd, 'BBL') || str_starts_with($cd, 'BCS') || str_starts_with($cd, 'BCL') || str_starts_with($cd, 'BSS') || str_starts_with($cd, 'BBQ')) {
            return 'BUMBU';
        }
        if (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM') || str_contains($nm, 'PENYEDAP') || str_starts_with($cd, 'BP-MS') || str_starts_with($cd, 'MSG')) {
            return 'MSG';
        }
        if (str_contains($nm, 'GARAM') || str_contains($nm, 'SALT') || str_starts_with($cd, 'BP-GR') || str_starts_with($cd, 'GRM')) {
            return 'GARAM';
        }
        if (str_contains($nm, 'PERENYAH') || str_starts_with($cd, 'BP-PR') || str_starts_with($cd, 'PRH') || str_contains($nm, 'STPP')) {
            return 'PERENYAH';
        }
        return 'BAHAN_PENOLONG';
    };

    // Filter PO khusus komoditas terpilih
    $filteredPos = $pos->filter(function($p) use ($qc, $classifyBarang, $curKomoditas) {
        if ($p->po_id == $qc->po_id) return true;
        foreach ($p->details as $d) {
            $cat = $classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd);
            if ($cat === $curKomoditas || ($curKomoditas === 'BAHAN_PENOLONG' && in_array($cat, ['MSG', 'GARAM', 'PERENYAH', 'BUMBU', 'BAHAN_PENOLONG']))) {
                return true;
            }
        }
        return false;
    });
    if ($filteredPos->isEmpty() && $qc->po) {
        $filteredPos = collect([$qc->po]);
    }

    // Filter Supplier Non-Petani Singkong
    $filteredSuppliers = $suppliers->filter(function($s) use ($qc) {
        if ($s->supplier_id == $qc->supplier_id) return true;
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    });
    if ($filteredSuppliers->isEmpty()) {
        $filteredSuppliers = $suppliers;
    }

    // Filter Master Barang
    $filteredBarangs = $barangs->filter(function($b) use ($classifyBarang, $firstDetail, $curKomoditas) {
        if ($firstDetail && $b->barang_id == $firstDetail->barang_id) return true;
        $cat = $classifyBarang($b->barang_nm, $b->barang_cd);
        return $cat === $curKomoditas || ($curKomoditas === 'BAHAN_PENOLONG' && in_array($cat, ['MSG', 'GARAM', 'PERENYAH', 'BUMBU', 'BAHAN_PENOLONG']));
    });
    if ($filteredBarangs->isEmpty()) {
        $filteredBarangs = $barangs;
    }

    $barangId = $firstDetail?->barang_id ?? $filteredBarangs->first()?->barang_id;
    $ref = request('ref', 'index');
    if ($ref === 'detail') {
        $backUrl = route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']);
        $backLabel = 'Batal ke Detail';
    } else {
        $backUrl = route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => $curKomoditas]);
        $backLabel = 'Batal ke Riwayat';
    }

    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');

    $commodityLabels = [
        'MSG'            => '🧂 MSG (MONOSODIUM)',
        'GARAM'          => '🧂 GARAM INDUSTRI',
        'PERENYAH'       => '🥨 PERENYAH (STPP)',
        'BUMBU'          => '🌶️ BUMBU TABUR',
        'BAHAN_PENOLONG' => '🧂 BAHAN PENOLONG',
    ];
    $commodityLabel = $commodityLabels[$curKomoditas] ?? '🧂 BAHAN PENOLONG';
@endphp

@section('content')
<div class="qc-detail-wrap" style="max-width: 680px; margin: 0 auto; padding-bottom: 5.5rem;">

    {{-- TOP APP NAV --}}
    <div class="qc-detail-top-nav">
        <a href="{{ $backUrl }}" class="qc-detail-back-btn" title="{{ $backLabel }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Batal &amp; Kembali</span>
        </a>

        <div style="display: flex; align-items: center; gap: 0.45rem;">
            @if (Auth::user()?->isSuperAdmin())
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-qc-switch-desktop" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1.5px solid #bae6fd; padding: 0.35rem 0.65rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;" title="Kembali ke Tabel QC Web Desktop">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Tabel Web QC</span>
                </a>
            @endif
            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                QC {{ $curKomoditas }} &bull; Edit
            </span>
        </div>
    </div>

    {{-- HEADER CARD MOBILE --}}
    <div class="qc-detail-hero" style="background: linear-gradient(135deg, #0f172a 0%, #4c1d95 60%, #7c3aed 100%);">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity" style="background: rgba(124, 58, 237, 0.45); color: #ede9fe;">
                    {{ $commodityLabel }}
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem; color: #ffffff;">Edit Tiket #{{ $qc->qc_no }}</h1>
            </div>
            <span style="background: #ede9fe; color: #6d28d9; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 20px;">
                Mode Mobile
            </span>
        </div>
        <p style="font-size: 0.8rem; margin: 0.4rem 0 0; opacity: 0.85; line-height: 1.4;">
            Koreksi parameter sampling mutu {{ strtolower($commodityLabel) }}, kondisi isi, kemasan zak, kondisi armada, atau kuantitas kedatangan.
        </p>
    </div>

    {{-- ALERT JIKA SUDAH DIPROSES KE GUDANG (GRN) --}}
    @if ($qc->terima)
        <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; display: flex; gap: 0.65rem; align-items: flex-start;">
            <span style="font-size: 1.25rem; line-height: 1;">📦</span>
            <div style="font-size: 0.78rem; color: #92400e; line-height: 1.4;">
                <strong>Perhatian:</strong> Tiket ini sudah ditarik ke Penerimaan Gudang (GRN <strong>#{{ $qc->terima->terima_no }}</strong>).
                Setiap revisi netto atau kuantitas reject akan menyinkronkan stok gudang secara otomatis.
            </div>
        </div>
    @endif

    {{-- FORM EDIT UTAMA --}}
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcBpEditForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="ref" value="{{ $ref }}">
        <input type="hidden" name="kategori_barang" value="{{ $curKomoditas }}">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">

        {{-- Hidden Items Data --}}
        <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][barang_id]" value="{{ $barangId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][grade_cd]" value="{{ $firstDetail?->grade_cd ?? 'A' }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputQtyNettoLolos" value="{{ $totalNetto }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][status_raw_material]" id="inputRawMaterial" value="{{ $firstDetail?->status_raw_material ?? 'OK' }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][kemasan_kondisi]" id="inputKemasanKondisi" value="{{ $firstDetail?->kemasan_kondisi ?? 'OK' }}">

        {{-- Hidden Keputusan / Disposisi --}}
        <input type="hidden" name="kesimpulan_qc" id="inputBpKesimpulan" value="{{ $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : 'TOLAK' }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][keputusan_qc]" id="inputKesimpulanQc" value="{{ $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : 'TOLAK' }}">
        <input type="hidden" name="qty_terima" id="inputQtyTerima" value="{{ $totalNetto }}">
        <input type="hidden" name="qty_tolak" id="inputQtyTolak" value="{{ $totalReject }}">

        {{-- ========================================================================= --}}
        {{-- CARD 1: INFORMASI BAHAN & KUANTITAS PENERIMAAN                           --}}
        {{-- ========================================================================= --}}
        <div class="card" style="border-radius: 14px; border: 1.5px solid #e2e8f0; margin-bottom: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.15rem;">⚖️</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">1. Kuantitas Kedatangan &amp; Rekonsiliasi</h2>
                        <span style="font-size: 0.72rem; color: #64748b;">Gross, Reject dan Netto lolos QC (Satuan: KG)</span>
                    </div>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; background: #ede9fe; color: #6d28d9; padding: 2px 8px; border-radius: 6px;">
                    Satuan: KG
                </span>
            </div>

            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 0.9rem;">
                {{-- Master Barang --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="qc-field-label">Master Barang {{ $curKomoditas }} <span style="color:#ef4444;">*</span></label>
                    <select name="items[{{ $qcdtlId }}][barang_id]" class="form-control" style="font-weight: 800; color: #0f172a;" required>
                        @foreach ($filteredBarangs as $b)
                            <option value="{{ $b->barang_id }}" {{ $b->barang_id == $barangId ? 'selected' : '' }}>
                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Deskripsi Jenis / Spesifikasi --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="qc-field-label">Nama Komoditas / Spesifikasi <span style="font-size: 0.72rem; color: #64748b;">(Tercetak di Surat Jalan)</span></label>
                    <input type="text" name="nama_jenis" value="{{ old('nama_jenis', $qc->nama_jenis ?: $curKomoditas) }}" class="form-control" style="font-weight: 700;">
                </div>

                {{-- Grid Kuantitas: Gross & Reject --}}
                <div class="qc-grid-2col">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Gross Timbang Pabrik (KG) <span style="color:#ef4444;">*</span></label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="any" min="0" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="editQtyGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $totalGross) }}" class="form-control" style="font-size: 1.15rem; font-weight: 900; color: #0f172a;" required oninput="calcBpEditNetto()">
                            <span class="input-suffix-text">KG</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Reject / Rusak (KG)</label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="any" min="0" name="items[{{ $qcdtlId }}][qty_reject]" id="editQtyReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $totalReject) }}" class="form-control" style="font-size: 1.15rem; font-weight: 900; color: #dc2626;" oninput="calcBpEditNetto()">
                            <span class="input-suffix-text">KG</span>
                        </div>
                    </div>
                </div>

                {{-- Netto Hasil Lolos QC (Live Preview) --}}
                <div style="background: #faf5ff; border: 1.5px solid #ddd6fe; border-radius: 12px; padding: 0.85rem 1rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 0.72rem; font-weight: 800; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.04em;">
                            Netto Lolos Inspeksi QC
                        </div>
                        <div style="font-size: 0.72rem; color: #64748b;">
                            (Gross - Reject = Netto lolos)
                        </div>
                    </div>
                    <div id="labelBpEditNetto" style="font-size: 1.4rem; font-weight: 900; color: #7c3aed;">
                        {{ number_format($totalNetto, 2, ',', '.') }} KG
                    </div>
                </div>

                {{-- Perbandingan Surat Jalan vs Pabrik & Sampel --}}
                <div class="qc-grid-2col">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Jumlah di Surat Jalan (DO)</label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="any" min="0" name="jumlah_surat_jalan" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan ?? $totalGross) }}" class="form-control" style="font-weight: 800;">
                            <span class="input-suffix-text">KG</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Sampel Diperiksa (Gram) <span style="font-size: 0.72rem; color: #7c3aed;">(HACCP: 200 gr)</span></label>
                        <div class="input-suffix-wrap">
                            <input type="number" step="any" min="1" name="jumlah_sample_gr" value="{{ old('jumlah_sample_gr', $qc->jumlah_sample_gr ?: ($firstDetail?->jumlah_sample_gr ?: 200)) }}" class="form-control" style="font-weight: 800; color: #7c3aed;">
                            <span class="input-suffix-text">gr</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- CARD 2: PENGUJIAN MUTU FISIK & KONDISI BAHAN PENOLONG                    --}}
        {{-- ========================================================================= --}}
        <div class="card" style="border-radius: 14px; border: 1.5px solid #ddd6fe; margin-bottom: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.15rem;">🔬</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #581c87; margin: 0;">2. Pengujian Mutu Fisik Bahan Penolong</h2>
                        <span style="font-size: 0.72rem; color: #6d28d9; font-weight: 700;">Analisa kondisi isi, kekeringan &amp; cacat kemasan zak</span>
                    </div>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; background: #ede9fe; color: #6d28d9; padding: 2px 8px; border-radius: 6px;">
                    FORM HACCP
                </span>
            </div>

            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                {{-- SUBCARD: ISI RAW MATERIAL & KEMASAN STATUS --}}
                <div class="bp-subcard">
                    <span class="bp-subcard-title">
                        <span>🔍</span> <span>ISI RAW MATERIAL &amp; KONDISI KEMASAN:</span>
                    </span>
                    <div class="qc-grid-2col">
                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.45rem;">2. ISI RAW MATERIAL :</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'active ok' : '' }}" id="btnRawOk" onclick="setRawMaterialStatus('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn {{ ($firstDetail?->status_raw_material ?? 'OK') !== 'OK' ? 'active danger' : '' }}" id="btnRawTdkStd" onclick="setRawMaterialStatus('TDK_STD')">
                                    <span>✖</span> <span>TDK STD</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.45rem;">KONDISI KEMASAN :</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn {{ ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? 'active ok' : '' }}" id="btnKemasanOk" onclick="setKemasanKondisi('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn {{ ($firstDetail?->kemasan_kondisi ?? 'OK') !== 'OK' ? 'active danger' : '' }}" id="btnKemasanTdkStd" onclick="setKemasanKondisi('TIDAK_STANDARD')">
                                    <span>✖</span> <span>TIDAK STANDARD</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBCARD: 4 KONDISI FISIK ISI (RAW MATERIAL) --}}
                <div class="bp-subcard">
                    <span class="bp-subcard-title" style="color: #6d28d9;">
                        <span>🧪</span> <span>KONDISI FISIK ISI (RAW MATERIAL):</span>
                    </span>
                    <div class="modern-checklist-grid">
                        {{-- KERING (Default Standar OK) --}}
                        <div class="modern-check-card is-ok {{ $firstDetail?->isi_kering ? 'is-checked' : '' }}" id="card_bp_isi_kering" onclick="toggleModernCheck('checkIsiKering')">
                            <div class="modern-check-label">
                                <span>🌾</span> <span>KERING</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiKering">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][isi_kering]" id="checkIsiKering" value="1" {{ $firstDetail?->isi_kering ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        {{-- BASAH --}}
                        <div class="modern-check-card is-danger {{ $firstDetail?->isi_basah ? 'is-checked' : '' }}" id="card_bp_isi_basah" onclick="toggleModernCheck('checkIsiBasah')">
                            <div class="modern-check-label">
                                <span>💧</span> <span>BASAH</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiBasah">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][isi_basah]" id="checkIsiBasah" value="1" {{ $firstDetail?->isi_basah ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        {{-- GUMPAL --}}
                        <div class="modern-check-card is-warning {{ $firstDetail?->isi_gumpal ? 'is-checked' : '' }}" id="card_bp_isi_gumpal" onclick="toggleModernCheck('checkIsiGumpal')">
                            <div class="modern-check-label">
                                <span>🧱</span> <span>GUMPAL</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiGumpal">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][isi_gumpal]" id="checkIsiGumpal" value="1" {{ $firstDetail?->isi_gumpal ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        {{-- BERMINYAK --}}
                        <div class="modern-check-card is-warning {{ $firstDetail?->isi_berminyak ? 'is-checked' : '' }}" id="card_bp_isi_berminyak" onclick="toggleModernCheck('checkIsiBerminyak')">
                            <div class="modern-check-label">
                                <span>🛢️</span> <span>BERMINYAK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiBerminyak">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][isi_berminyak]" id="checkIsiBerminyak" value="1" {{ $firstDetail?->isi_berminyak ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD: 4 CACAT KEMASAN / WADAH ZAK --}}
                <div class="bp-subcard">
                    <span class="bp-subcard-title" style="color: #475569;">
                        <span>📦</span> <span>KONDISI KEMASAN / WADAH ZAK (Ketuk jika ditemukan cacat):</span>
                    </span>
                    <div class="modern-checklist-grid">
                        {{-- KOTOR --}}
                        <div class="modern-check-card is-danger {{ $firstDetail?->kemasan_kotor ? 'is-checked' : '' }}" id="card_bp_kemasan_kotor" onclick="toggleModernCheck('checkKemasanKotor')">
                            <div class="modern-check-label">
                                <span>🧼</span> <span>KOTOR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanKotor">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_kotor]" id="checkKemasanKotor" value="1" {{ $firstDetail?->kemasan_kotor ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        {{-- APEK --}}
                        <div class="modern-check-card is-danger {{ $firstDetail?->kemasan_apek ? 'is-checked' : '' }}" id="card_bp_kemasan_apek" onclick="toggleModernCheck('checkKemasanApek')">
                            <div class="modern-check-label">
                                <span>👃</span> <span>APEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanApek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_apek]" id="checkKemasanApek" value="1" {{ $firstDetail?->kemasan_apek ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        {{-- JAMUR --}}
                        <div class="modern-check-card is-danger {{ $firstDetail?->kemasan_jamur ? 'is-checked' : '' }}" id="card_bp_kemasan_jamur" onclick="toggleModernCheck('checkKemasanJamur')">
                            <div class="modern-check-label">
                                <span>🍄</span> <span>JAMUR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanJamur">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_jamur]" id="checkKemasanJamur" value="1" {{ $firstDetail?->kemasan_jamur ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>

                        {{-- SOBEK --}}
                        <div class="modern-check-card is-danger {{ $firstDetail?->kemasan_sobek ? 'is-checked' : '' }}" id="card_bp_kemasan_sobek" onclick="toggleModernCheck('checkKemasanSobek')">
                            <div class="modern-check-label">
                                <span>✂️</span> <span>SOBEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanSobek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_sobek]" id="checkKemasanSobek" value="1" {{ $firstDetail?->kemasan_sobek ? 'checked' : '' }} style="display:none;" onchange="updateModernCheckCard(this)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- CARD 3: VENDOR, PO & ARMADA PENGIRIMAN                                   --}}
        {{-- ========================================================================= --}}
        <div class="card" style="border-radius: 14px; border: 1.5px solid #e2e8f0; margin-bottom: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.15rem;">🚚</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">3. Rekanan Vendor &amp; Pengiriman</h2>
                        <span style="font-size: 0.72rem; color: #64748b;">Referensi Purchase Order, Surat Jalan &amp; Truk Armada</span>
                    </div>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; background: #ede9fe; color: #6d28d9; padding: 2px 8px; border-radius: 6px;">
                    Logistik
                </span>
            </div>

            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 0.9rem;">
                {{-- Vendor Supplier --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="qc-field-label">Mitra Vendor Supplier <span style="color:#ef4444;">*</span></label>
                    <select name="supplier_id" id="supplierSelect" class="form-control" style="font-weight: 800; color: #0f172a;" required onchange="onSupplierChanged(this)">
                        @foreach ($filteredSuppliers as $s)
                            <option value="{{ $s->supplier_id }}" {{ $s->supplier_id == $qc->supplier_id ? 'selected' : '' }}>
                                {{ $s->supplier_nm }} ({{ $s->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Referensi PO & Gudang Bongkar --}}
                <div class="qc-grid-2col">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Referensi PO {{ $curKomoditas }} <span style="font-size: 0.72rem; color: #64748b;">(Opsional)</span></label>
                        <select name="po_id" id="poSelect" class="form-control" style="font-weight: 700;">
                            <option value="">-- Non-PO / Kiriman Langsung --</option>
                            @foreach ($filteredPos as $p)
                                <option value="{{ $p->po_id }}" data-supplier-id="{{ $p->supplier_id }}" {{ $p->po_id == $qc->po_id ? 'selected' : '' }}>
                                    {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Gudang Bongkar Muat <span style="color:#ef4444;">*</span></label>
                        <select name="gudang_id" class="form-control" style="font-weight: 800;" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ $g->gudang_id == $qc->gudang_id ? 'selected' : '' }}>
                                    {{ $g->display_name ?? $g->gudang_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Pabrik Produsen & Negara --}}
                <div class="qc-grid-2col">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Pabrik Produsen Asal</label>
                        <input type="text" name="nama_produsen" value="{{ old('nama_produsen', $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '')) }}" class="form-control" style="font-weight: 700;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Negara Produsen</label>
                        <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?: 'Indonesia') }}" class="form-control" style="font-weight: 700;">
                    </div>
                </div>

                {{-- No DO / Surat Jalan & Plat Truk --}}
                <div class="qc-grid-2col">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">No. Surat Jalan / DO</label>
                        <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do ?: $qc->surat_jalan_supplier) }}" class="form-control" style="font-weight: 800; color: #0f172a;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Plat Nomor Truk</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" style="font-weight: 800; text-transform: uppercase;">
                    </div>
                </div>

                {{-- Nama Sopir & Tanggal Periksa --}}
                <div class="qc-grid-2col">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Nama Pengemudi / Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" style="font-weight: 700;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="qc-field-label">Waktu Periksa Lapangan</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" class="form-control" style="font-weight: 700;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- CARD 4: AUDIT SISTEM JAMINAN HALAL (SJH) & TRANSPORTASI                  --}}
        {{-- ========================================================================= --}}
        <div class="card" style="border-radius: 14px; border: 1.5px solid #ddd6fe; margin-bottom: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.15rem;">🛡️</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #581c87; margin: 0;">4. Standar Transportasi &amp; Jaminan Halal</h2>
                        <span style="font-size: 0.72rem; color: #6d28d9; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; Higiene Truk</span>
                    </div>
                </div>
                <span style="font-size: 0.72rem; font-weight: 800; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 6px;">
                    AUDIT SJH
                </span>
            </div>

            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                {{-- 1. Kebersihan Armada --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">1. Kebersihan Armada &amp; Bebas Najis / Cemaran</div>
                            <div class="halal-qa-sub">Truk bersih dari oli, air kotor, bau menyengat atau zat kimia berbahaya</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $qc->bebas_cemaran_st ? 'active ok' : '' }}" id="btnCemarBebas" onclick="setHalalToggle('bebas_cemaran', 1)">
                                <span>✔</span> <span>Bebas</span>
                            </button>
                            <button type="button" class="segmented-toggle-btn {{ !$qc->bebas_cemaran_st ? 'active danger' : '' }}" id="btnCemarAda" onclick="setHalalToggle('bebas_cemaran', 0)">
                                <span>✖</span> <span>Tercemar</span>
                            </button>
                        </div>
                        <input type="hidden" name="bebas_cemaran_st" id="input_bebas_cemaran" value="{{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? 1 : 0) }}">
                    </div>
                    <input type="text" name="komentar_transportasi" value="{{ old('komentar_transportasi', $qc->komentar_transportasi) }}" class="form-control" placeholder="Komentar armada : -" style="font-size: 0.825rem;">
                </div>

                {{-- 2. Mengangkut Barang Haram --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">2. Pemisahan Logistik Barang Haram</div>
                            <div class="halal-qa-sub">Armada tidak pernah digunakan bersamaan untuk mengangkut barang non-halal</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ !$qc->angkut_barang_haram_st ? 'active ok' : '' }}" id="btnHaramTidak" onclick="setHalalToggle('angkut_barang_haram', 0)">
                                <span>✔</span> <span>Tidak</span>
                            </button>
                            <button type="button" class="segmented-toggle-btn {{ $qc->angkut_barang_haram_st ? 'active danger' : '' }}" id="btnHaramYa" onclick="setHalalToggle('angkut_barang_haram', 1)">
                                <span>✖</span> <span>Ya (Tercampur)</span>
                            </button>
                        </div>
                        <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ? 1 : 0) }}">
                    </div>
                </div>

                {{-- 3. Terdaftar LPPOM MUI / BPJPH --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">3. Pendaftaran di LPPOM MUI / BPJPH</div>
                            <div class="halal-qa-sub">Bahan penolong resmi terdaftar dalam approved list bahan halal</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $qc->terdaftar_lppom_st ? 'active ok' : '' }}" id="btnLppomYa" onclick="setHalalToggle('terdaftar_lppom', 1)">
                                <span>✔</span> <span>Ya</span>
                            </button>
                            <button type="button" class="segmented-toggle-btn {{ !$qc->terdaftar_lppom_st ? 'active danger' : '' }}" id="btnLppomTidak" onclick="setHalalToggle('terdaftar_lppom', 0)">
                                <span>✖</span> <span>Tidak</span>
                            </button>
                        </div>
                        <input type="hidden" name="terdaftar_lppom_st" id="input_terdaftar_lppom" value="{{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ? 1 : 0) }}">
                    </div>
                    <input type="text" name="komentar_lppom" value="{{ old('komentar_lppom', $qc->komentar_lppom) }}" class="form-control" placeholder="Komentar LPPOM : -" style="font-size: 0.825rem;">
                </div>

                {{-- 4. Mempunyai Sertifikat Halal --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">4. Kepemilikan Sertifikat Halal Produsen</div>
                            <div class="halal-qa-sub">Salinan sertifikat halal resmi terlampir pada dokumen vendor</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $qc->ada_sertifikat_halal_st ? 'active ok' : '' }}" id="btnSertifikatYa" onclick="setHalalToggle('ada_sertifikat', 1)">
                                <span>✔</span> <span>Ada</span>
                            </button>
                            <button type="button" class="segmented-toggle-btn {{ !$qc->ada_sertifikat_halal_st ? 'active danger' : '' }}" id="btnSertifikatTidak" onclick="setHalalToggle('ada_sertifikat', 0)">
                                <span>✖</span> <span>Tidak Ada</span>
                            </button>
                        </div>
                        <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat" value="{{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ? 1 : 0) }}">
                    </div>
                    <input type="text" name="komentar_sertifikat" value="{{ old('komentar_sertifikat', $qc->komentar_sertifikat) }}" class="form-control" placeholder="Komentar Sertifikat : -" style="font-size: 0.825rem;">
                </div>

                {{-- 5. Masa Berlaku Sertifikat Halal --}}
                <div class="halal-qa-row">
                    <div class="halal-qa-header">
                        <div class="halal-qa-info">
                            <div class="halal-qa-title">5. Masa Berlaku Sertifikat Halal</div>
                            <div class="halal-qa-sub">Masa berlaku dokumen sertifikat halal masih aktif dan sah</div>
                        </div>
                        <div class="segmented-toggle">
                            <button type="button" class="segmented-toggle-btn {{ $qc->sertifikat_halal_berlaku_st ? 'active ok' : '' }}" id="btnBerlakuYa" onclick="setHalalToggle('sertifikat_berlaku', 1)">
                                <span>✔</span> <span>Berlaku</span>
                            </button>
                            <button type="button" class="segmented-toggle-btn {{ !$qc->sertifikat_halal_berlaku_st ? 'active danger' : '' }}" id="btnBerlakuTidak" onclick="setHalalToggle('sertifikat_berlaku', 0)">
                                <span>✖</span> <span>Kadaluarsa</span>
                            </button>
                        </div>
                        <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_berlaku" value="{{ old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ? 1 : 0) }}">
                    </div>
                    <input type="text" name="komentar_berlaku" value="{{ old('komentar_berlaku', $qc->komentar_berlaku) }}" class="form-control" placeholder="Komentar Validitas : -" style="font-size: 0.825rem;">
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- CARD 5: KEPUTUSAN, OTORISASI & CATATAN QC                                --}}
        {{-- ========================================================================= --}}
        <div class="card" style="border-radius: 14px; border: 1.5px solid #e2e8f0; margin-bottom: 1.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.15rem;">💬</span>
                    <div>
                        <h2 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">5. Disposisi Keputusan &amp; Tanda Tangan</h2>
                        <span style="font-size: 0.72rem; color: #64748b;">Persetujuan bongkar &amp; otorisasi laboratorium</span>
                    </div>
                </div>
            </div>

            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                {{-- PILIHAN KEPUTUSAN TERIMA VS TOLAK (STANDAR ENTERPRISE QC DISPOSISI GRID) --}}
                @php
                    $isTolakAwal = old('items.'.$qcdtlId.'.keputusan_qc', $firstDetail?->keputusan_qc ?? '') === 'REJECT_TOTAL' || $qc->status_qc === 'DITOLAK_TOTAL';
                @endphp
                <div>
                    <label class="form-label" style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; display: block;">
                        Disposisi Status Tiket Inspeksi <span style="color:#ef4444;">*</span>
                    </label>
                    <div class="qc-disposisi-grid">
                        <!-- KARTU TERIMA -->
                        <div class="qc-disposisi-card {{ !$isTolakAwal ? 'active terima' : 'terima' }}" id="cardDisposisiTerima" onclick="setKesimpulanBp('TERIMA')">
                            <div class="qc-disposisi-head">
                                <div class="qc-disposisi-title-wrap">
                                    <span class="qc-disposisi-radio-icon"></span>
                                    <span class="qc-disposisi-title">DITERIMA</span>
                                </div>
                                <span class="qc-disposisi-status-badge">✓</span>
                            </div>
                            <div class="qc-disposisi-qty-wrap">
                                <span class="qc-disposisi-qty-label">Kuantitas Diterima:</span>
                                <div class="qc-disposisi-input-box">
                                    <input type="number" step="any" min="0" name="qty_terima" id="inputQtyTerima" class="qc-disposisi-input" value="{{ $totalNetto }}" readonly>
                                    <span class="qc-disposisi-unit">kg</span>
                                </div>
                            </div>
                        </div>

                        <!-- KARTU TOLAK -->
                        <div class="qc-disposisi-card {{ $isTolakAwal ? 'active tolak' : 'tolak' }}" id="cardDisposisiTolak" onclick="setKesimpulanBp('TOLAK')">
                            <div class="qc-disposisi-head">
                                <div class="qc-disposisi-title-wrap">
                                    <span class="qc-disposisi-radio-icon"></span>
                                    <span class="qc-disposisi-title">DITOLAK</span>
                                </div>
                                <span class="qc-disposisi-status-badge">✕</span>
                            </div>
                            <div class="qc-disposisi-qty-wrap">
                                <span class="qc-disposisi-qty-label">Kuantitas Ditolak:</span>
                                <div class="qc-disposisi-input-box">
                                    <input type="number" step="any" min="0" name="qty_tolak" id="inputQtyTolak" class="qc-disposisi-input" value="{{ $totalReject }}" readonly>
                                    <span class="qc-disposisi-unit">kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CATATAN UMUM & HASIL ANALISA --}}
                <div>
                    <label class="qc-field-label">Catatan Pengujian Bahan Penolong</label>
                    <textarea name="catatan_umum" rows="3" class="form-control" placeholder="Contoh: Tekstur kering sesuai standar HACCP, kemasan utuh bebas bocor..." style="font-size: 0.85rem;">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
                </div>

                {{-- PETUGAS & SUPERVISOR PENINJAU --}}
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.65rem; display: flex; flex-direction: column; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">Petugas QC Raw Material <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama ?: auth()->user()?->name) }}" class="form-control" style="font-weight: 800; color: #0f172a;" required>
                    </div>

                    <div>
                        <label class="qc-info-label" style="font-weight: 700; margin-bottom: 0.2rem; display: block;">QC Supervisor / Lab Head Peninjau</label>
                        <input type="text" name="qc_supervisor_nama" list="listSupervisorQc" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama ?: 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" class="form-control" style="font-weight: 800; color: #0f172a;">
                        <datalist id="listSupervisorQc">
                            <option value="Hendrawan, S.TP (SPV QC Shift Pagi)">
                            <option value="Rina Wijaya, S.Si (Supervisor QC Lab)">
                            <option value="Dedi (QC Bahan Baku)">
                            <option value="Bambang (Produksi Magelang)">
                            <option value="Super Administrator">
                            <option value="Ahmad Farhan, S.T (Ka. Gudang)">
                        </datalist>
                    </div>
                </div>
            </div>
        </div>

        {{-- FLOATING ACTION DOCK BOTTOM --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <a href="{{ $backUrl }}" class="btn-dock-back" style="display: inline-flex; text-decoration: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Batal</span>
            </a>

            <button type="submit" class="btn-dock-next btn-primary-state" style="background: #7c3aed; border-color: #7c3aed;">
                <span>Simpan Perubahan QC</span>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </button>
        </div>
    </form>
</div>

{{-- SUBMIT OVERLAY LOADING --}}
<div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
    <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🧂</div>
        <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem;">Menyimpan Koreksi {{ $curKomoditas }}...</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memperbarui data inspeksi bahan penolong.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/bahan-penolong/bahan-penolong-edit.js') }}"></script>
@endpush
