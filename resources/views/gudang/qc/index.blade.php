@extends('layouts.app')

@section('title', 'Pemeriksaan Mutu (QC) Inbound & HACCP - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-admin-index.css') }}">
@endpush

@section('content')
{{-- TOP HEADER COMMAND (STANDAR PO) --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Pemeriksaan Mutu (QC) Inbound &amp; HACCP</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Monitoring hasil sampling kedatangan bahan baku, arsip lembar mutu HACCP, serta serah terima penarikan ke Gudang (GRN).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        @if (Auth::user()?->canCreateQc())
            <a href="{{ route('qc.inbound.create_pengujian_2') }}" style="background: #7e22ce; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 0.5rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(126, 34, 206, 0.25);" title="Input Pengujian II di Lantai Produksi">
                <span>🍟 + QC 2 Produksi</span>
            </a>
            <a href="{{ route('qc.inbound.create') }}" class="btn btn-primary" style="font-weight: 700; border-radius: 8px; padding: 0.5rem 0.85rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ QC 1 Kedatangan</span>
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- 1. SIAP DITARIK KE GRN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Siap Ditarik ke GRN
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countSiap ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Lolos sampling &bull; Siap bongkar gudang
        </div>
    </div>

    {{-- 2. MENUNGGU PENGUJIAN 2 --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #7e22ce;">
                    Menunggu Pengujian 2
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countMenungguUji2 ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f3e8ff; display: flex; align-items: center; justify-content: center; color: #7e22ce;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Truk singkong siap uji sisa bak
        </div>
    </div>

    {{-- 3. SUDAH MASUK GUDANG (GRN) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Sudah Masuk Gudang
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countSelesai ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Telah terbit bukti penerimaan (GRN)
        </div>
    </div>

    {{-- 4. DITOLAK TOTAL (REJECT) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Ditolak QC / Reject
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($countReject ?? 0) }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Tidak memenuhi parameter mutu
        </div>
    </div>
</div>

{{-- KARTU TABEL UTAMA & FILTER TERPADU (STANDAR PO) --}}
<div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form action="{{ route('qc.inbound.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 850px; width: 100%;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nomor QC, plat truk, supplier, sopir..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 250px; font-size: 0.85rem;">
            
            <select name="kategori_barang" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 165px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Komoditas --</option>
                <option value="SINGKONG" {{ ($filters['kategori_barang'] ?? '') === 'SINGKONG' ? 'selected' : '' }}>Singkong</option>
                <option value="MINYAK" {{ ($filters['kategori_barang'] ?? '') === 'MINYAK' ? 'selected' : '' }}>Minyak Goreng</option>
                <option value="PLASTIK" {{ ($filters['kategori_barang'] ?? '') === 'PLASTIK' ? 'selected' : '' }}>Plastik</option>
                <option value="KARTON" {{ ($filters['kategori_barang'] ?? '') === 'KARTON' ? 'selected' : '' }}>Karton</option>
                <option value="MSG" {{ ($filters['kategori_barang'] ?? '') === 'MSG' ? 'selected' : '' }}>MSG</option>
                <option value="GARAM" {{ ($filters['kategori_barang'] ?? '') === 'GARAM' ? 'selected' : '' }}>Garam</option>
                <option value="PERENYAH" {{ ($filters['kategori_barang'] ?? '') === 'PERENYAH' ? 'selected' : '' }}>Perenyah</option>
            </select>

            <select name="status_qc" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Status QC --</option>
                <option value="SIAP_GUDANG" {{ ($filters['status_qc'] ?? '') === 'SIAP_GUDANG' ? 'selected' : '' }}>Siap Gudang</option>
                <option value="MENUNGGU_UJI_2" {{ ($filters['status_qc'] ?? '') === 'MENUNGGU_UJI_2' ? 'selected' : '' }}>⏳ Menunggu Uji 2</option>
                <option value="DITERIMA_GUDANG" {{ ($filters['status_qc'] ?? '') === 'DITERIMA_GUDANG' ? 'selected' : '' }}>Selesai Masuk (GRN)</option>
                <option value="DITOLAK_TOTAL" {{ ($filters['status_qc'] ?? '') === 'DITOLAK_TOTAL' ? 'selected' : '' }}>Ditolak QC (Reject)</option>
            </select>

            <select name="supplier_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Supplier --</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $s->supplier_id ? 'selected' : '' }}>
                        {{ $s->supplier_nm }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($filters['search']) || !empty($filters['kategori_barang']) || !empty($filters['status_qc']) || !empty($filters['supplier_id']))
                <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem;">
            Total QC: <strong style="color: #0f172a;">{{ $inspeksiList->total() }}</strong> dokumen
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0; background: #f8fafc;">
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; min-width: 165px; text-align: left;">No. Tiket QC &amp; Dokumen</th>
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; min-width: 165px; text-align: left;">Armada &amp; Rekanan</th>
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0284c7; min-width: 185px; text-align: left; background: #f0f9ff; border-left: 2px solid #bae6fd;">
                        🚛 Pengujian 1 (Setengah Bak)
                    </th>
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #7e22ce; min-width: 185px; text-align: left; background: #faf5ff; border-left: 2px solid #e9d5ff;">
                        🍟 Pengujian 2 (Sisa Bak)
                    </th>
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #047857; min-width: 145px; text-align: right; background: #f0fdf4; border-left: 2px solid #bbf7d0;">
                        Total Netto Lolos
                    </th>
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; width: 130px; text-align: center;">Status Gudang</th>
                    <th style="padding: 0.75rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569; width: 90px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inspeksiList as $qc)
                    @php
                        $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                        $isSingkong = ($kat === 'SINGKONG');

                        // Pengujian 1 Data
                        $p1Gross = (float) $qc->details->sum('qty_timbang_gross');
                        $p1Refraksi = (float) $qc->details->sum('qty_refraksi');
                        $p1Reject = (float) $qc->details->sum('qty_reject');
                        $p1Netto = (float) $qc->details->sum('qty_netto_lolos');
                        $p1FirstDtl = $qc->details->first();
                        $p1Grade = $p1FirstDtl?->grade_cd ?? 'A';
                        $p1RefPersen = (float) ($p1FirstDtl?->refraksi_persen ?? 0);

                        // Pengujian 2 Data (child)
                        $p2 = $qc->pengujian2List->first();
                        $p2Gross = $p2 ? (float) $p2->details->sum('qty_timbang_gross') : 0;
                        $p2Refraksi = $p2 ? (float) $p2->details->sum('qty_refraksi') : 0;
                        $p2Reject = $p2 ? (float) $p2->details->sum('qty_reject') : 0;
                        $p2Netto = $p2 ? (float) $p2->details->sum('qty_netto_lolos') : 0;
                        $p2FirstDtl = $p2 ? $p2->details->first() : null;
                        $p2Grade = $p2FirstDtl?->grade_cd ?? 'A';
                        $p2RefPersen = $p2FirstDtl ? (float) ($p2FirstDtl->refraksi_persen ?? 0) : 0;

                        // Combined Truck Totals
                        $totalGross = $p1Gross + $p2Gross;
                        $totalRefraksi = $p1Refraksi + $p2Refraksi;
                        $totalReject = $p1Reject + $p2Reject;
                        $totalNetto = $p1Netto + $p2Netto;

                        // Terima GRN
                        $terimaObj = $qc->terima ?: ($p2?->terima);
                        $isLocked = !empty($terimaObj) && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isGudang();
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.12s ease;">
                        {{-- 1. NO QC & DOKUMEN --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: top;">
                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                <a href="javascript:void(0)" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" style="font-weight: 800; color: #0284c7; text-decoration: none; font-family: monospace; font-size: 0.875rem;" title="Buka Dokumen HACCP Pengujian 1">
                                    {{ $qc->qc_no }}
                                </a>
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 700; font-size: 0.68rem; padding: 2px 5px; border-radius: 4px;">
                                    {{ $kat }}
                                </span>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                                📅 {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }}
                            </div>
                            <div style="font-size: 0.75rem; color: #334155; margin-top: 0.35rem; line-height: 1.35;">
                                @if($qc->surat_jalan_supplier)
                                    <div><span style="color: #64748b;">SJ:</span> <strong>{{ $qc->surat_jalan_supplier }}</strong></div>
                                @endif
                                @if((float)$qc->jumlah_surat_jalan > 0)
                                    <div style="color: #64748b; font-size: 0.72rem;">Muatan SJ: <strong style="color: #0f172a;">{{ number_format((float)$qc->jumlah_surat_jalan, 0, ',', '.') }} kg</strong></div>
                                @endif
                            </div>
                        </td>

                        {{-- 2. ARMADA & REKANAN --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: top;">
                            <div style="font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                <span>🚛</span> <span>{{ $qc->plat_nomor_truk ?: '-' }}</span>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                Sopir: <strong>{{ $qc->sopir_nama ?: '-' }}</strong>
                            </div>
                            <div style="font-size: 0.8rem; font-weight: 700; color: #1e293b; margin-top: 0.35rem;">
                                {{ $qc->supplier?->supplier_nm ?: '-' }}
                            </div>
                            @if ($qc->po)
                                <div style="font-size: 0.72rem; color: #0284c7; margin-top: 0.1rem;">
                                    PO: <a href="{{ route('gudang.po.show', $qc->po->po_id) }}" style="color: #0284c7; text-decoration: none; font-weight: 600;">{{ $qc->po->po_no }}</a>
                                </div>
                            @endif
                            @if ($qc->batch_no)
                                <div style="font-size: 0.7rem; color: #7e22ce; font-weight: 700; margin-top: 0.15rem;">
                                    Batch: {{ $qc->batch_no }}
                                </div>
                            @endif
                        </td>

                        {{-- 3. PENGUJIAN 1 (SETENGAH BAK PERTAMA) --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.825rem; vertical-align: top; background: #fafcff; border-left: 2px solid #e0f2fe;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-size: 0.7rem; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px;">
                                    Uji 1
                                </span>
                                @if ($p1Grade === 'B')
                                    <span style="font-size: 0.68rem; font-weight: 800; background: #fef3c7; color: #b45309; padding: 1px 6px; border-radius: 4px; border: 1px solid #fde68a;">
                                        🟡 Grade B
                                    </span>
                                @elseif ($p1Grade === 'REJECT')
                                    <span style="font-size: 0.68rem; font-weight: 800; background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 4px; border: 1px solid #fca5a5;">
                                        ❌ Afkir
                                    </span>
                                @else
                                    <span style="font-size: 0.68rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 1px 6px; border-radius: 4px; border: 1px solid #86efac;">
                                        🟢 Grade A
                                    </span>
                                @endif
                            </div>
                            <div style="font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                                Gross: {{ number_format($p1Gross, 0, ',', '.') }} kg
                            </div>
                            @if ($p1Refraksi > 0)
                                <div style="font-size: 0.72rem; color: #d97706; margin-top: 0.1rem;">
                                    Refraksi: -{{ number_format($p1Refraksi, 0, ',', '.') }} kg ({{ number_format($p1RefPersen, 1) }}%)
                                </div>
                            @endif
                            <div style="font-size: 0.8rem; font-weight: 800; color: #047857; margin-top: 0.2rem;">
                                Netto: {{ number_format($p1Netto, 0, ',', '.') }} kg
                            </div>
                            <div style="margin-top: 0.35rem;">
                                @if ($qc->status_qc === 'DITOLAK_TOTAL')
                                    <span style="font-size: 0.7rem; font-weight: 800; color: #dc2626; background: #fee2e2; padding: 1px 6px; border-radius: 4px;">
                                        ❌ Ditolak (Uji 1)
                                    </span>
                                @else
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #15803d;">
                                        ✅ Uji 1 Lolos
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- 4. PENGUJIAN 2 (SISA BAK KEDUA) --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.825rem; vertical-align: top; background: #fdfbff; border-left: 2px solid #f3e8ff;">
                            @if ($p2)
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <a href="javascript:void(0)" onclick="openHaccpModal('{{ $p2->qc_id }}', '{{ $p2->qc_no }}')" style="font-size: 0.7rem; font-weight: 800; background: #f3e8ff; color: #7e22ce; padding: 1px 6px; border-radius: 4px; text-decoration: none;" title="Buka Dokumen HACCP Pengujian 2">
                                        #{{ $p2->qc_no }}
                                    </a>
                                    @if ($p2Grade === 'B')
                                        <span style="font-size: 0.68rem; font-weight: 800; background: #fef3c7; color: #b45309; padding: 1px 6px; border-radius: 4px; border: 1px solid #fde68a;">
                                            🟡 Grade B
                                        </span>
                                    @elseif ($p2Grade === 'REJECT')
                                        <span style="font-size: 0.68rem; font-weight: 800; background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 4px; border: 1px solid #fca5a5;">
                                            ❌ Afkir
                                        </span>
                                    @else
                                        <span style="font-size: 0.68rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 1px 6px; border-radius: 4px; border: 1px solid #86efac;">
                                            🟢 Grade A
                                        </span>
                                    @endif
                                </div>
                                <div style="font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                                    Gross: {{ number_format($p2Gross, 0, ',', '.') }} kg
                                </div>
                                @if ($p2Refraksi > 0)
                                    <div style="font-size: 0.72rem; color: #d97706; margin-top: 0.1rem;">
                                        Refraksi: -{{ number_format($p2Refraksi, 0, ',', '.') }} kg ({{ number_format($p2RefPersen, 1) }}%)
                                    </div>
                                @endif
                                <div style="font-size: 0.8rem; font-weight: 800; color: #047857; margin-top: 0.2rem;">
                                    Netto: {{ number_format($p2Netto, 0, ',', '.') }} kg
                                </div>
                                <div style="margin-top: 0.35rem;">
                                    @if ($p2->status_qc === 'DITOLAK_TOTAL')
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #dc2626; background: #fee2e2; padding: 1px 6px; border-radius: 4px;">
                                            🚫 Ditolak Sisa
                                        </span>
                                    @else
                                        <span style="font-size: 0.7rem; font-weight: 700; color: #7e22ce;">
                                            ✅ Uji 2 Lolos
                                        </span>
                                    @endif
                                </div>
                            @elseif ($isSingkong)
                                @if ($qc->status_qc === 'DITOLAK_TOTAL')
                                    <div style="font-size: 0.75rem; color: #94a3b8; font-style: italic; margin-top: 0.5rem;">
                                        — Truk dipulangkan (Uji 1 gagal)
                                    </div>
                                @else
                                    <div style="margin-top: 0.25rem;">
                                        <span style="font-size: 0.72rem; font-weight: 800; background: #fef3c7; color: #b45309; padding: 2px 7px; border-radius: 4px; display: inline-block;">
                                            ⏳ Menunggu Uji 2
                                        </span>
                                        <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem;">
                                            Setengah bak sedang turun
                                        </div>
                                        @if (Auth::user()?->canCreateQc())
                                            <div style="margin-top: 0.5rem;">
                                                <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" 
                                                   style="background: #7e22ce; color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 4px 9px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(126,34,206,0.25);">
                                                    <span>🍟 + Catat Uji 2</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div style="font-size: 0.75rem; color: #94a3b8; font-style: italic; margin-top: 0.5rem;">
                                    — Non-Tahap (Uji Tunggal)
                                </div>
                            @endif
                        </td>

                        {{-- 5. TOTAL NETTO DITERIMA --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: top; text-align: right; background: #f8fdfa; border-left: 2px solid #bbf7d0;">
                            <div style="font-size: 0.72rem; color: #64748b;">
                                Total Gross:
                            </div>
                            <div style="font-weight: 700; color: #334155; font-size: 0.85rem;">
                                {{ number_format($totalGross, 0, ',', '.') }} kg
                            </div>
                            @if ($totalRefraksi > 0)
                                <div style="font-size: 0.72rem; color: #d97706; margin-top: 0.15rem;">
                                    Tot. Refraksi: -{{ number_format($totalRefraksi, 0, ',', '.') }} kg
                                </div>
                            @endif
                            <div style="font-size: 0.72rem; color: #166534; font-weight: 700; margin-top: 0.35rem;">
                                TOTAL BERSIH LOLOS:
                            </div>
                            <div style="font-size: 1.15rem; font-weight: 900; color: #047857; margin-top: 0.05rem;">
                                {{ number_format($totalNetto, 0, ',', '.') }} kg
                            </div>
                        </td>

                        {{-- 6. STATUS GUDANG --}}
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.85rem; vertical-align: top; text-align: center;">
                            @if ($terimaObj)
                                <a href="{{ route('gudang.terima.show', $terimaObj->terima_id) }}" class="badge-grn-pill" title="Buka Dokumen Penerimaan Gudang (GRN)">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span>GRN #{{ $terimaObj->terima_no }}</span>
                                </a>
                            @elseif ($qc->status_qc === 'DITOLAK_TOTAL' && (!$p2 || $p2->status_qc === 'DITOLAK_TOTAL'))
                                <span style="font-size: 0.75rem; color: #dc2626; font-weight: 800; background: #fee2e2; padding: 2px 7px; border-radius: 4px;">
                                    Truk Ditolak
                                </span>
                            @elseif ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL')
                                <span style="font-size: 0.75rem; color: #b45309; font-weight: 700; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                                    Bongkar 1/2 Bak
                                </span>
                            @elseif ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG'))
                                <span style="font-size: 0.75rem; color: #047857; font-weight: 800; background: #dcfce7; padding: 2px 7px; border-radius: 4px;">
                                    Siap Tarik GRN
                                </span>
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8;">-</span>
                            @endif
                        </td>

                        {{-- 7. SMART ACTION DROPDOWN --}}
                        <td style="text-align: right; position: relative; padding: 0.75rem 0.85rem; vertical-align: top;">
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'qcDropdown-{{ $qc->qc_id }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="qcDropdown-{{ $qc->qc_id }}" class="action-dropdown-menu">
                                {{-- 1. LIHAT DOKUMEN HACCP --}}
                                <button type="button" class="action-dropdown-item" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>HACCP Uji 1 (#{{ $qc->qc_no }})</span>
                                </button>
                                @if ($p2)
                                    <button type="button" class="action-dropdown-item" onclick="openHaccpModal('{{ $p2->qc_id }}', '{{ $p2->qc_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span style="color: #7e22ce; font-weight: 700;">HACCP Uji 2 (#{{ $p2->qc_no }})</span>
                                    </button>
                                @endif

                                {{-- 2. CETAK LEMBAR HACCP A4 --}}
                                <button type="button" class="action-dropdown-item" onclick="directPrintHaccp('{{ $qc->qc_id }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak HACCP Uji 1 (A4)</span>
                                </button>
                                @if ($p2)
                                    <button type="button" class="action-dropdown-item" onclick="directPrintHaccp('{{ $p2->qc_id }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        <span style="color: #7e22ce;">Cetak HACCP Uji 2 (A4)</span>
                                    </button>
                                @endif

                                {{-- 3. CETAK BERITA ACARA PENOLAKAN --}}
                                @if ($qc->status_qc === 'DITOLAK_TOTAL')
                                    <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="action-dropdown-item" style="color: #dc2626; font-weight: 700;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Berita Acara Penolakan (Uji 1)</span>
                                    </a>
                                @endif
                                @if ($p2 && $p2->status_qc === 'DITOLAK_TOTAL')
                                    <a href="{{ route('qc.inbound.berita_acara', $p2->qc_id) }}" class="action-dropdown-item" style="color: #dc2626; font-weight: 700;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Berita Acara Penolakan (Uji 2)</span>
                                    </a>
                                @endif

                                {{-- 4. TARIK KE GRN GUDANG --}}
                                @if (!$terimaObj && ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG')) && Auth::user()?->canAccessTerima())
                                    <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <span>Tarik ke GRN Gudang</span>
                                    </a>
                                @endif

                                {{-- 5. CATAT PENGUJIAN II --}}
                                @if ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL' && Auth::user()?->canCreateQc())
                                    <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" class="action-dropdown-item" style="color: #7e22ce; font-weight: 700;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>🍟 Catat Pengujian II</span>
                                    </a>
                                @endif

                                <div class="action-dropdown-divider"></div>

                                {{-- 6. EDIT DOKUMEN QC --}}
                                @if (Auth::user()?->canEditQc())
                                    @if (!$isLocked)
                                        <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit QC Uji 1</span>
                                        </a>
                                        @if ($p2)
                                            <a href="{{ route('qc.inbound.edit', $p2->qc_id) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>Edit QC Uji 2</span>
                                            </a>
                                        @endif
                                    @else
                                        <span class="action-dropdown-item" style="color: #94a3b8; cursor: not-allowed;" title="Terkunci karena sudah terbit GRN Gudang">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Terkunci (GRN Ada)</span>
                                        </span>
                                    @endif
                                @endif

                                {{-- 7. BATALKAN / HAPUS TIKET QC --}}
                                @if (Auth::user()?->canDeleteQc() && !$isLocked)
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteQcModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan Tiket QC</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 3.5rem 1.5rem; text-align: center; color: #64748b;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔬</div>
                            <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Belum Ada Tiket QC Inbound</div>
                            <div style="font-size: 0.85rem; margin-top: 0.25rem;">
                                Belum ada data kedatangan bahan baku yang dicatat atau sesuai kriteria filter di atas.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($inspeksiList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
            {{ $inspeksiList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.modal-delete')

{{-- MODAL POPUP PREVIEW DOKUMEN HACCP --}}
@include('gudang.qc.partials.modal-preview-haccp')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/qc-admin-index.js') }}"></script>
    @if (request('direct_print'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    if (typeof directPrintHaccp === 'function') {
                        directPrintHaccp('{{ request('direct_print') }}');
                    }
                }, 400);
            });
        </script>
    @endif
@endpush
