@extends('layouts.app')

@section('title', 'Dokumen QC HACCP: ' . $qc->qc_no)

@section('content')
<div style="max-width: 950px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- TOP ACTION BAR (Hidden when printing) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Riwayat Tiket QC
            </a>
            <span style="font-size: 0.875rem; color: #64748b;">|</span>
            <span style="font-size: 0.9rem; font-weight: 700; color: #0f172a;">Tiket #{{ $qc->qc_no }}</span>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700; background: #fee2e2; color: #b91c1c; border-color: #fca5a5;">
                📄 Berita Acara Penolakan
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700;">
                🖨️ Cetak Lembar HACCP (A4)
            </button>
            @if ($qc->status_qc === 'SIAP_GUDANG' && Auth::user()?->canAccessTerima())
                <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 700; background: #059669; border-color: #059669;">
                    📦 Tarik ke Penerimaan Barang (GRN) &rarr;
                </a>
            @endif
        </div>
    </div>

    {{-- OFFICIAL HACCP DOCUMENT CARD --}}
    <div class="card print-sheet" style="background: #ffffff; border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 2.25rem;">
        
        {{-- KOP SURAT RESMI PT MIRASA & FORM METADATA HACCP --}}
        <div style="border-bottom: 2.5px solid #0f172a; padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tr>
                    <td style="width: 75px; vertical-align: middle; padding: 0;">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo PT Mirasa" style="width: 65px; height: 65px; object-fit: contain;">
                    </td>
                    <td style="vertical-align: middle; padding-left: 1rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 900; margin: 0; color: #0f172a; letter-spacing: -0.01em; text-transform: uppercase;">
                            PT. MIRASA FOOD INDUSTRY
                        </h2>
                        <div style="font-size: 0.75rem; color: #475569; font-weight: 600; margin-top: 0.15rem;">
                            QUALITY CONTROL &amp; FOOD SAFETY MANAGEMENT SYSTEM
                        </div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0284c7; margin-top: 0.35rem; letter-spacing: 0.01em;">
                            CHECKLIST STANDAR KEBETERIMAAN BAHAN BAKU SINGKONG
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; font-style: italic;">
                            Pengujian I (Sampling Fisik Lapangan) &amp; Pengujian II (Hasil Fryer / Uji Goreng Lab QC)
                        </div>
                    </td>
                    <td style="width: 250px; vertical-align: middle; text-align: right; padding: 0;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem; text-align: left; border: 1.5px solid #0f172a;">
                            <tr style="border-bottom: 1px solid #cbd5e1;">
                                <td style="padding: 3px 6px; font-weight: 700; background: #f8fafc; width: 95px; border-right: 1px solid #cbd5e1;">No. Dokumen</td>
                                <td style="padding: 3px 6px; font-weight: 800; color: #0f172a;">MFI/HACCP-04/FRM-03/048/VIII/2021</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #cbd5e1;">
                                <td style="padding: 3px 6px; font-weight: 700; background: #f8fafc; border-right: 1px solid #cbd5e1;">Revisi</td>
                                <td style="padding: 3px 6px; font-weight: 700;">1</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #cbd5e1;">
                                <td style="padding: 3px 6px; font-weight: 700; background: #f8fafc; border-right: 1px solid #cbd5e1;">Tgl. Terbit</td>
                                <td style="padding: 3px 6px; font-weight: 700;">11-09-2023</td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 6px; font-weight: 700; background: #f8fafc; border-right: 1px solid #cbd5e1;">No. Tiket QC</td>
                                <td style="padding: 3px 6px; font-weight: 800; color: #0284c7;">{{ $qc->qc_no }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- STATUS BANNER (PRINTABLE) --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; background: #f8fafc; padding: 0.6rem 1rem; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.825rem;">
            <div>
                <span style="color: #64748b;">Status Pemeriksaan:</span>
                @if ($qc->status_qc === 'SIAP_GUDANG')
                    <span style="display: inline-block; margin-left: 0.5rem; background: #fef3c7; color: #b45309; padding: 0.2rem 0.6rem; border-radius: 20px; font-weight: 800; font-size: 0.775rem;">
                        ⏳ SIAP DITERIMA GUDANG (ACC QC)
                    </span>
                @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                    <span style="display: inline-block; margin-left: 0.5rem; background: #dcfce7; color: #15803d; padding: 0.2rem 0.6rem; border-radius: 20px; font-weight: 800; font-size: 0.775rem;">
                        ✅ DITERIMA GUDANG (GRN: {{ $qc->terima?->terima_no ?? 'Terverifikasi' }})
                    </span>
                @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                    <span style="display: inline-block; margin-left: 0.5rem; background: #fee2e2; color: #b91c1c; padding: 0.2rem 0.6rem; border-radius: 20px; font-weight: 800; font-size: 0.775rem;">
                        ❌ DITOLAK TOTAL (TIDAK MEMENUHI SYARAT)
                    </span>
                @endif
            </div>
            <div style="color: #64748b; font-size: 0.775rem;">
                Tanggal Kedatangan: <strong>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</strong>
            </div>
        </div>

        {{-- METADATA RAW MATERIAL, KEBUN & PENGIRIMAN --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.25rem; font-size: 0.825rem;">
            {{-- KOLOM KIRI: ASAL USUL BAHAN BAKU --}}
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1;">
                <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                    <th colspan="2" style="padding: 0.45rem 0.6rem; text-align: left; font-weight: 800; color: #0f172a; font-size: 0.825rem;">
                        📍 1. IDENTITAS RAW MATERIAL &amp; KEBUN
                    </th>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b; width: 140px;">Nama Raw Material:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0f172a;">
                        {{ $qc->details->pluck('barang.barang_nm')->filter()->implode(', ') ?: 'Singkong Kulit' }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Nama Produsen / Mitra:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0f172a;">{{ $qc->supplier?->supplier_nm ?? '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Negara Produsen:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 600;">{{ $qc->negara_produsen ?: 'Indonesia' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Lokasi Panen:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 600;">{{ $qc->lokasi_panen ?: '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Umur Singkong:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0284c7;">
                        {{ $qc->umur_singkong_bln ? $qc->umur_singkong_bln . ' Bulan' : '-' }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Tanggal Panen:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 600;">
                        {{ $qc->tgl_panen ? \Carbon\Carbon::parse($qc->tgl_panen)->format('d/m/Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Jumlah Sample:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700;">
                        {{ $qc->jumlah_sample_kg ? number_format($qc->jumlah_sample_kg, 1, ',', '.') . ' KG' : '-' }}
                    </td>
                </tr>
            </table>

            {{-- KOLOM KANAN: DOKUMEN & TRANSPORTASI --}}
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1;">
                <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                    <th colspan="2" style="padding: 0.45rem 0.6rem; text-align: left; font-weight: 800; color: #0f172a; font-size: 0.825rem;">
                        🚚 2. ARMADA &amp; DOKUMEN PENGIRIMAN
                    </th>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b; width: 140px;">No. Purchase Order:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0284c7;">
                        {{ $qc->po?->po_no ?? '(Tanpa PO / Kiriman Langsung)' }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">No. Surat Jalan:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 600;">{{ $qc->surat_jalan_supplier ?: '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Plat Nomor Truk:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0f172a;">{{ $qc->plat_nomor_truk ?: '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Nama Sopir / Pengemudi:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 600;">{{ $qc->sopir_nama ?: '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Gudang Bongkar:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 600;">{{ $qc->gudang?->gudang_nm ?? '-' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Petugas QC:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0f172a;">{{ $qc->petugas_qc_nama }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.35rem 0.6rem; color: #64748b;">Supervisor QC:</td>
                    <td style="padding: 0.35rem 0.6rem; font-weight: 700; color: #0f172a;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td>
                </tr>
            </table>
        </div>

        {{-- STANDAR TRANSPORTASI & JAMINAN HALAL (BOX) --}}
        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.6rem 0.85rem; margin-bottom: 1.25rem; background: #fafafa; font-size: 0.8rem;">
            <div style="font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                <span>🛡️</span> <span>STANDAR KEBERSIHAN TRANSPORTASI &amp; JAMINAN HALAL</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div>
                    <span style="color: #64748b;">Kondisi Transportasi:</span>
                    @if ($qc->bebas_cemaran_st)
                        <strong style="color: #15803d; margin-left: 0.35rem;">[✔] Tidak Ada Cemaran, Najis / Kotoran</strong>
                    @else
                        <strong style="color: #dc2626; margin-left: 0.35rem;">[✘] Ada Cemaran</strong>
                    @endif
                </div>
                <div>
                    <span style="color: #64748b;">Diangkut Bersama Barang Haram:</span>
                    @if (!$qc->angkut_barang_haram_st)
                        <strong style="color: #15803d; margin-left: 0.35rem;">[✔] TIDAK (Terjamin Halal &amp; Thayyib)</strong>
                    @else
                        <strong style="color: #dc2626; margin-left: 0.35rem;">[✘] YA (Terkontaminasi Haram)</strong>
                    @endif
                </div>
            </div>
            @if ($qc->komentar_transportasi)
                <div style="margin-top: 0.35rem; font-size: 0.775rem; color: #475569; border-top: 1px dashed #cbd5e1; padding-top: 0.25rem;">
                    <em>Keterangan Transportasi:</em> {{ $qc->komentar_transportasi }}
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTION: PENGUJIAN I (SAMPLING FISIK & DIAMETER)                          --}}
        {{-- ========================================================================= --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem; border-left: 4px solid #10b981; padding-left: 0.5rem;">
                <span>PENGUJIAN I : SAMPLING KEDATANGAN (DIAMETER, KONDISI FISIK &amp; TIMBANGAN)</span>
            </div>

            @php
                $sumGross = 0;
                $sumRefraksi = 0;
                $sumReject = 0;
                $sumNetto = 0;
            @endphp

            @foreach ($qc->details as $idx => $d)
                @php
                    $sumGross += (float)$d->qty_timbang_gross;
                    $sumRefraksi += (float)$d->qty_refraksi;
                    $sumReject += (float)$d->qty_reject;
                    $sumNetto += (float)$d->qty_netto_lolos;
                @endphp

                <div style="border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 1rem; overflow: hidden;">
                    {{-- Sub Header Item --}}
                    <div style="background: #f8fafc; padding: 0.5rem 0.75rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">
                            #{{ $idx + 1 }} &bull; {{ $d->barang?->barang_nm ?? '-' }}
                            <span style="font-weight: normal; color: #64748b; font-size: 0.75rem;">(Kode: {{ $d->barang?->barang_cd ?? '-' }})</span>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 4px; background: {{ $d->grade_cd === 'A' ? '#dcfce7' : ($d->grade_cd === 'B' ? '#e0f2fe' : '#fee2e2') }}; color: {{ $d->grade_cd === 'A' ? '#15803d' : ($d->grade_cd === 'B' ? '#0369a1' : '#b91c1c') }};">
                                Grade {{ $d->grade_cd }}
                            </span>
                        </div>
                    </div>

                    {{-- Grid Parameter Diameter & Kondisi Visual --}}
                    <div style="padding: 0.75rem; font-size: 0.8rem; display: grid; grid-template-columns: 1fr 1.3fr; gap: 1rem; border-bottom: 1px solid #f1f5f9;">
                        {{-- Standar Diameter --}}
                        <div style="border-right: 1px dashed #cbd5e1; padding-right: 0.75rem;">
                            <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.35rem; font-size: 0.775rem;">
                                📏 STANDAR DIAMETER SINGKONG:
                            </div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 3px 0; color: #64748b;">Diameter &lt; 4 cm (Standar Maks 5.0%):</td>
                                    <td style="padding: 3px 0; text-align: right; font-weight: 700; color: {{ (float)$d->diameter_kurang_4cm_persen > 5 ? '#dc2626' : '#15803d' }};">
                                        {{ number_format($d->diameter_kurang_4cm_persen, 1, ',', '.') }}%
                                        {{ (float)$d->diameter_kurang_4cm_persen > 5 ? '(Melebihi)' : '(OK)' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 0; color: #64748b;">Diameter &ge; 4 cm (Standar Min 95.0%):</td>
                                    <td style="padding: 3px 0; text-align: right; font-weight: 700; color: {{ (float)$d->diameter_lebih_4cm_persen < 95 ? '#dc2626' : '#15803d' }};">
                                        {{ number_format($d->diameter_lebih_4cm_persen, 1, ',', '.') }}%
                                        {{ (float)$d->diameter_lebih_4cm_persen < 95 ? '(Kurang)' : '(OK)' }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        {{-- Pemeriksaan Visual Fisik --}}
                        <div>
                            <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.35rem; font-size: 0.775rem;">
                                👁️ PEMERIKSAAN KONDISI FISIK SINGKONG:
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; font-size: 0.75rem;">
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_segar ? 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_segar ? '✔' : ' ' }}] Segar
                                </span>
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_layu ? 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_layu ? '✔' : ' ' }}] Layu
                                </span>
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_basah ? 'background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_basah ? '✔' : ' ' }}] Basah
                                </span>
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_terkelupas ? 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_terkelupas ? '✔' : ' ' }}] Terkelupas
                                </span>
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_busuk ? 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_busuk ? '✔' : ' ' }}] Busuk
                                </span>
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_berjamur ? 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_berjamur ? '✔' : ' ' }}] Berjamur
                                </span>
                                <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_lembek ? 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                    [{{ $d->kondisi_lembek ? '✔' : ' ' }}] Lembek
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Timbangan & Potongan Refraksi --}}
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.775rem;">
                        <tr style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; font-weight: 700; color: #334155;">
                            <th style="padding: 0.4rem 0.6rem; text-align: right;">Gross Timbangan (KG)</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: center;">Kadar Air (%)</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right;">Refraksi Tanah (%)</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right;">Potongan Refraksi (KG)</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right;">Afkir / Tolak (KG)</th>
                            <th style="padding: 0.4rem 0.6rem; text-align: right; background: #f0fdf4; color: #166534;">Netto Lolos / Diterima (KG)</th>
                        </tr>
                        <tr>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 700; font-size: 0.85rem; color: #0f172a;">
                                {{ number_format($d->qty_timbang_gross, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.5rem 0.6rem; text-align: center; font-weight: 700; color: {{ (float)$d->kadar_air_persen > 15 ? '#b45309' : '#059669' }};">
                                {{ number_format($d->kadar_air_persen, 1, ',', '.') }}%
                            </td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600; color: #b45309;">
                                {{ number_format($d->refraksi_persen, 1, ',', '.') }}%
                            </td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600; color: #b45309;">
                                -{{ number_format($d->qty_refraksi, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 700; color: #dc2626;">
                                -{{ number_format($d->qty_reject, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 800; font-size: 0.95rem; background: #f0fdf4; color: #15803d;">
                                {{ number_format($d->qty_netto_lolos, 2, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTION: PENGUJIAN II (HASIL FRYER / UJI GORENG LAB & DEFECT FRYING)      --}}
        {{-- ========================================================================= --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem; border-left: 4px solid #f59e0b; padding-left: 0.5rem;">
                <span>PENGUJIAN II : HASIL UJI GORENG LAB QC (FRYER SENSORI &amp; DEFECT FRYING)</span>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.775rem; border: 1px solid #cbd5e1;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-weight: 700; color: #334155;">
                        <th rowspan="2" style="padding: 0.5rem 0.6rem; text-align: left; vertical-align: middle; border-right: 1px solid #cbd5e1;">Komoditas</th>
                        <th colspan="3" style="padding: 0.35rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; background: #fef3c7; color: #92400e;">
                            Sensori Uji Fryer (Standar: Tidak Pahit, Renyah, Tidak Oilsoaked)
                        </th>
                        <th colspan="5" style="padding: 0.35rem 0.6rem; text-align: center; background: #fee2e2; color: #991b1b;">
                            Cacat Goreng / Defect Frying (%)
                        </th>
                    </tr>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; font-weight: 700; font-size: 0.72rem;">
                        <th style="padding: 0.3rem 0.5rem; text-align: center; border-right: 1px solid #cbd5e1;">Rasa</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: center; border-right: 1px solid #cbd5e1;">Tekstur</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: center; border-right: 1px solid #cbd5e1;">Penampakan</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: right;">Breakage</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: right;">Cluster</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: right;">Foldover</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: right;">Oilsoaked</th>
                        <th style="padding: 0.3rem 0.5rem; text-align: right;">Gambos/Kopong</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($qc->details as $d)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 0.5rem 0.6rem; font-weight: 700; color: #0f172a; border-right: 1px solid #cbd5e1;">
                                {{ $d->barang?->barang_nm ?? '-' }}
                            </td>
                            {{-- Rasa --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; font-weight: 700; color: {{ $d->fryer_rasa === 'TIDAK_PAHIT' ? '#15803d' : '#dc2626' }};">
                                {{ $d->fryer_rasa === 'TIDAK_PAHIT' ? 'Tidak Pahit' : 'PAHIT' }}
                            </td>
                            {{-- Tekstur --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; font-weight: 700; color: {{ $d->fryer_tekstur === 'RENYAH' ? '#15803d' : '#dc2626' }};">
                                {{ $d->fryer_tekstur === 'RENYAH' ? 'Renyah' : 'ALOT' }}
                            </td>
                            {{-- Penampakan --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; font-weight: 700; color: {{ $d->fryer_penampakan === 'TIDAK_OILSOAKED' ? '#15803d' : '#dc2626' }};">
                                {{ $d->fryer_penampakan === 'TIDAK_OILSOAKED' ? 'Tdk Oilsoaked' : 'OILSOAKED' }}
                            </td>
                            {{-- Breakage --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                {{ number_format($d->defect_breakage_persen, 1, ',', '.') }}%
                            </td>
                            {{-- Cluster --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                {{ number_format($d->defect_cluster_persen, 1, ',', '.') }}%
                            </td>
                            {{-- Foldover --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                {{ number_format($d->defect_foldover_persen, 1, ',', '.') }}%
                            </td>
                            {{-- Oilsoaked --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                {{ number_format($d->defect_oilsoaked_persen, 1, ',', '.') }}%
                            </td>
                            {{-- Gambos --}}
                            <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600; color: {{ (float)$d->defect_gambos_persen > 0 ? '#b45309' : '#0f172a' }};">
                                {{ number_format($d->defect_gambos_persen, 1, ',', '.') }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ========================================================================= --}}
        {{-- KESIMPULAN REKAPITULASI HASIL (ACC / TOLAK KG)                            --}}
        {{-- ========================================================================= --}}
        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.75rem;">
            <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.65rem;">
                ⚖️ REKAPITULASI KESIMPULAN KEBETERIMAAN BAHAN BAKU:
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; text-align: center;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem;">
                    <div style="font-size: 0.7rem; color: #64748b; font-weight: 600;">TIMBANGAN KOTOR (GROSS)</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        {{ number_format($sumGross, 2, ',', '.') }} KG
                    </div>
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem;">
                    <div style="font-size: 0.7rem; color: #b45309; font-weight: 600;">REFRAKSI TANAH</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #b45309; margin-top: 0.2rem;">
                        -{{ number_format($sumRefraksi, 2, ',', '.') }} KG
                    </div>
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem;">
                    <div style="font-size: 0.7rem; color: #dc2626; font-weight: 600;">AFKIR / TOLAK CACAT</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #dc2626; margin-top: 0.2rem;">
                        -{{ number_format($sumReject, 2, ',', '.') }} KG
                    </div>
                </div>
                <div style="background: #dcfce7; border: 1.5px solid #86efac; border-radius: 6px; padding: 0.5rem;">
                    <div style="font-size: 0.7rem; color: #166534; font-weight: 800;">BERSIH LOLOS (TERIMA)</div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #15803d; margin-top: 0.15rem;">
                        {{ number_format($sumNetto, 2, ',', '.') }} KG
                    </div>
                </div>
            </div>

            @if ($qc->catatan_umum)
                <div style="margin-top: 0.75rem; font-size: 0.775rem; color: #334155; border-top: 1px dashed #cbd5e1; padding-top: 0.45rem;">
                    <strong>Catatan Tim Penguji:</strong> {{ $qc->catatan_umum }}
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- TANDA TANGAN (SIGNATURE BLOCKS RESMI HACCP)                               --}}
        {{-- ========================================================================= --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; text-align: center; margin-top: 2rem; font-size: 0.775rem;">
            {{-- SOPIR / SUPPLIER --}}
            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">Pengemudi / Sopir Supplier,</div>
                <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                    {{ $qc->sopir_nama ?: '( ................................... )' }}
                </div>
            </div>

            {{-- PETUGAS QC RAW MATERIAL --}}
            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">Petugas QC Raw Material,</div>
                <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                    {{ $qc->petugas_qc_nama }}
                </div>
            </div>

            {{-- SUPERVISOR QC --}}
            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">QC Supervisor,</div>
                <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                    {{ $qc->qc_supervisor_nama ?: '( ................................... )' }}
                </div>
            </div>

            {{-- PENERIMA GUDANG --}}
            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">Admin / Penerima Gudang,</div>
                <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                    @if ($qc->terima)
                        {{ $qc->terima->creator?->name ?? 'Gudang Terverifikasi' }}
                    @else
                        ( ................................... )
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }
        .no-print, header, footer, .pill-nav, nav, .sidebar {
            display: none !important;
        }
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 11pt !important;
            color: #000000 !important;
        }
        .print-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            background: transparent !important;
        }
        table {
            page-break-inside: avoid;
        }
    }
</style>
@endsection
