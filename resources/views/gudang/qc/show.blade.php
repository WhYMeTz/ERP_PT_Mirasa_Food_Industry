@extends('layouts.qc-mobile')

@php
    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
    $firstDetail = $qc->details->first();
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = $qc->details->sum('qty_refraksi');
    $totalReject = $qc->details->sum('qty_reject');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();
@endphp

@section('title', 'Hasil Uji QC: ' . $qc->qc_no . ' - PT Mirasa')

@section('content')
<div style="max-width: 760px; margin: 0 auto; display: flex; flex-direction: column; gap: 1rem; padding-bottom: 2rem;">
    {{-- TOP BAR NAVIGASI & STATUS --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
            &larr; Riwayat Tiket QC
        </a>

        <div style="display: flex; gap: 0.45rem; align-items: center;">
            @if (Auth::user()?->canEditQc() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="btn btn-sm" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 700; border-radius: 8px;">
                    ✏️ Edit Sampling
                </a>
            @endif

            {{-- TOMBOL BUKA DOKUMEN CETAK A4 PABRIK (UNTUK ARSIP GUDANG / PC) --}}
            <a href="{{ route('gudang.qc.haccp_cetak', $qc->qc_id) }}" target="_blank" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700;">
                🖨️ Format Cetak A4
            </a>
        </div>
    </div>

    {{-- KARTU 1: HEADER HASIL INSPEKSI QC MOBILE --}}
    <div class="card" style="margin: 0; padding: 1.35rem; border-top: 6px solid #0284c7; border-radius: 14px; background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.45rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                    <span style="background: #e0f2fe; color: #0284c7; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 6px;">
                        @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                        KOMODITAS: {{ $kat }}
                    </span>
                    @if ($qc->status_qc === 'SIAP_GUDANG')
                        <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 6px;">
                            ⏳ Lolos Sampling &bull; Siap Gudang
                        </span>
                    @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                        <span style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 6px;">
                            ✅ Sudah Diterima Gudang (GRN)
                        </span>
                    @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                        <span style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 6px;">
                            ❌ Ditolak QC (Reject Total)
                        </span>
                    @endif
                </div>

                <h1 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">
                    {{ $qc->qc_no }}
                </h1>
                <p style="font-size: 0.825rem; color: #64748b; margin: 0.35rem 0 0 0;">
                    Diperiksa pada {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d F Y, H:i') : '-' }} &bull; Petugas: <strong>{{ $qc->petugas_qc_nama }}</strong>
                </p>
            </div>

            @if ($qc->terima)
                <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 0.65rem 0.85rem; text-align: right;">
                    <span style="font-size: 0.7rem; color: #065f46; font-weight: 700; text-transform: uppercase;">Sudah Masuk Gudang</span>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #047857;">
                        GRN #{{ $qc->terima->terima_no }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU 2: IDENTITAS LOGISTIK & PENGIRIMAN --}}
    <div class="card" style="margin: 0; padding: 1.25rem; border-radius: 14px; background: #ffffff;">
        <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.45rem;">
            <span>🚚</span> <span>Informasi Pengiriman &amp; Armada</span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem; font-size: 0.875rem;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #64748b; font-weight: 600;">Mitra Supplier</span>
                <strong style="color: #0f172a; text-align: right;">{{ $qc->supplier?->supplier_nm ?? '-' }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #64748b; font-weight: 600;">Referensi Purchase Order (PO)</span>
                <span style="font-weight: 700; color: #0284c7;">{{ $qc->po?->po_no ?? '(Tanpa PO / Langsung)' }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #64748b; font-weight: 600;">Armada Truk &amp; Sopir</span>
                <strong style="color: #0f172a;">🚛 {{ $qc->plat_nomor_truk ?: '-' }} &bull; {{ $qc->sopir_nama ?: '-' }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #64748b; font-weight: 600;">No. Surat Jalan &amp; DO</span>
                <span style="color: #334155; font-weight: 700;">SJ: {{ $qc->surat_jalan_supplier ?: '-' }} | DO: {{ $qc->nomor_do ?: '-' }}</span>
            </div>

            <div style="display: flex; justify-content: space-between;">
                <span style="color: #64748b; font-weight: 600;">Gudang / Lokasi Bongkar</span>
                <span style="color: #334155; font-weight: 700;">{{ $qc->gudang?->gudang_nm ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- KARTU 3: HASIL TIMBANGAN & SAMPLING --}}
    <div class="card" style="margin: 0; padding: 1.25rem; border-radius: 14px; background: #ffffff;">
        <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.45rem;">
            <span>⚖️</span> <span>Hasil Timbangan &amp; Potongan Mutu</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.65rem; margin-bottom: 0.85rem;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem; text-align: center;">
                <span style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Berat Gross (Kotor)</span>
                <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                    {{ number_format($totalGross, 2, ',', '.') }} <small style="font-size: 0.75rem;">kg</small>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem; text-align: center;">
                <span style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Refraksi / Potongan</span>
                <div style="font-size: 1.15rem; font-weight: 800; color: #d97706; margin-top: 0.2rem;">
                    {{ number_format($totalRefraksi, 2, ',', '.') }} <small style="font-size: 0.75rem;">kg</small>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem; text-align: center;">
                <span style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Barang Reject / Afkir</span>
                <div style="font-size: 1.15rem; font-weight: 800; color: #dc2626; margin-top: 0.2rem;">
                    {{ number_format($totalReject, 2, ',', '.') }} <small style="font-size: 0.75rem;">kg</small>
                </div>
            </div>

            <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 0.75rem; text-align: center;">
                <span style="font-size: 0.7rem; color: #065f46; font-weight: 800; text-transform: uppercase;">Netto Diterima Gudang</span>
                <div style="font-size: 1.35rem; font-weight: 900; color: #059669; margin-top: 0.2rem;">
                    {{ number_format($totalNetto, 2, ',', '.') }} <small style="font-size: 0.75rem;">kg</small>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 4: STANDAR KEBERSIHAN & JAMINAN HALAL --}}
    <div class="card" style="margin: 0; padding: 1.25rem; border-radius: 14px; background: #ffffff;">
        <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.45rem;">
            <span>🛡️</span> <span>Verifikasi Kebersihan Armada &amp; Jaminan Halal</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.875rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #334155; font-weight: 600;">Kondisi Transportasi Bebas Najis / Kotoran</span>
                <span style="font-weight: 800; color: {{ $qc->bebas_cemaran_st ? '#059669' : '#dc2626' }};">
                    {{ $qc->bebas_cemaran_st ? '✅ Memenuhi Syarat' : '❌ Tercemar' }}
                </span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #334155; font-weight: 600;">Tidak Diangkut Bersama Barang Haram</span>
                <span style="font-weight: 800; color: {{ $qc->tidak_bercampur_haram_st ? '#059669' : '#dc2626' }};">
                    {{ $qc->tidak_bercampur_haram_st ? '✅ Terpisah & Aman' : '❌ Tercampur' }}
                </span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #f1f5f9; padding-bottom: 0.4rem;">
                <span style="color: #334155; font-weight: 600;">Terdaftar di LPPOM MUI / BPJPH</span>
                <span style="font-weight: 800; color: {{ $qc->ada_sertifikat_halal_st ? '#059669' : '#dc2626' }};">
                    {{ $qc->ada_sertifikat_halal_st ? '✅ Terdaftar' : '❌ Tidak Ada' }}
                </span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #334155; font-weight: 600;">Sertifikat Halal Masih Berlaku</span>
                <span style="font-weight: 800; color: {{ $qc->sertifikat_halal_berlaku_st ? '#059669' : '#dc2626' }};">
                    {{ $qc->sertifikat_halal_berlaku_st ? '✅ Masih Berlaku' : '❌ Kedaluwarsa' }}
                </span>
            </div>
        </div>
    </div>

    {{-- KARTU 5: DETAIL ITEM & CATATAN MUTU FISIK --}}
    <div class="card" style="margin: 0; padding: 1.25rem; border-radius: 14px; background: #ffffff;">
        <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.45rem;">
            <span>🔬</span> <span>Item Bahan &amp; Parameter Uji Mutu</span>
        </div>

        @foreach ($qc->details as $d)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem; margin-bottom: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <strong style="color: #0f172a; font-size: 0.925rem;">
                        {{ $d->barang?->barang_nm ?? $qc->nama_jenis }}
                    </strong>
                    <span style="background: #e0f2fe; color: #0284c7; font-size: 0.75rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 6px;">
                        Grade: {{ $d->grade_cd ?: 'A' }}
                    </span>
                </div>

                <div style="font-size: 0.8rem; color: #475569; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem;">
                    @if ($d->kadar_air_persen !== null)
                        <div>Kadar Air: <strong>{{ number_format($d->kadar_air_persen, 1) }}%</strong></div>
                    @endif
                    @if ($d->refraksi_persen !== null)
                        <div>Refraksi: <strong>{{ number_format($d->refraksi_persen, 1) }}%</strong></div>
                    @endif
                    @if ($d->rendemen_persen !== null)
                        <div>Rendemen: <strong>{{ number_format($d->rendemen_persen, 1) }}%</strong></div>
                    @endif
                    <div>Status Uji: <strong style="color: #059669;">{{ $d->status_uji }}</strong></div>
                </div>

                @if ($d->catatan_cacat)
                    <div style="margin-top: 0.5rem; font-size: 0.775rem; color: #b45309; background: #fffbeb; padding: 0.35rem 0.55rem; border-radius: 6px;">
                        Catatan Cacat: {{ $d->catatan_cacat }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- TOMBOL KEMBALI DI BAWAH --}}
    <div style="text-align: center; margin-top: 0.5rem;">
        <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary" style="width: 100%; border-radius: 10px; font-weight: 700; min-height: 48px;">
            &larr; Kembali ke Daftar Riwayat Tiket
        </a>
    </div>
</div>
@endsection
