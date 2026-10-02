@extends('layouts.qc-mobile')

@php
    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
    $firstDetail = $qc->details->first();
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = $qc->details->sum('qty_refraksi');
    $totalReject = $qc->details->sum('qty_reject');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    $revisi = '1';
    $tglTerbit = '11-09-2023';

    if ($kat === 'MINYAK') {
        $docNo = 'MFI/HACCP-04/FRM-03/029/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Minyak Goreng';
    } elseif ($kat === 'PLASTIK') {
        $docNo = 'MFI/HACCP-04/FRM-03/030/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Plastik';
    } elseif ($kat === 'KARTON') {
        $docNo = 'MFI/HACCP-04/FRM-03/031/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Karton';
    } elseif ($kat === 'MSG') {
        $docNo = 'MFI/HACCP-04/FRM-03/032/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan MSG';
    } elseif ($kat === 'GARAM') {
        $docNo = 'MFI/HACCP-04/FRM-03/033/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Garam';
    } elseif ($kat === 'PERENYAH') {
        $docNo = 'MFI/HACCP-04/FRM-03/063/IX/2023';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Perenyah';
        $revisi = '0';
        $tglTerbit = '14-09-2023';
    } else {
        $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
        $docTitle = 'Cheklist Standar Kebeterimaan Bahan Baku';
    }
@endphp

@section('title', 'Dokumen QC: ' . $qc->qc_no . ' - PT Mirasa')

@section('content')
<div style="max-width: 950px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- TOP ACTION TOOLBAR (NO PRINT) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Riwayat Tiket QC
            </a>
            <span style="font-size: 0.85rem; color: #64748b;">|</span>
            <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">Tiket #{{ $qc->qc_no }}</span>
            <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0284c7; padding: 0.2rem 0.55rem; border-radius: 12px;">
                {{ $kat }}
            </span>
            @if ($qc->terima)
                <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" style="font-size: 0.75rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 0.2rem 0.55rem; border-radius: 12px; text-decoration: none; border: 1px solid #86efac;">
                    📦 GRN #{{ $qc->terima->terima_no }}
                </a>
            @endif
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            @if (Auth::user()?->canEditQc() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="btn btn-sm" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 700; border-radius: 8px;">
                    ✏️ Edit / Koreksi
                </a>
            @endif

            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 700;">
                🖨️ Cetak Dokumen HACCP (A4)
            </button>
        </div>
    </div>

    {{-- KOTAK UPDATE SUSULAN UJI GORENG LAB (KHUSUS SINGKONG JIKA MENUNGGU LAB) --}}
    @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
        <div class="no-print" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fde68a; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.4rem; font-weight: 800; font-size: 0.95rem; color: #b45309;">
                        <span>⏳</span> <span>Pengujian I Selesai &bull; Hasil Uji Goreng (Lab) Masih Tertunda</span>
                    </div>
                    <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: #78350f;">
                        Truk sudah lolos tahap sampling. Masukkan hasil uji penggorengan lab di bawah ini jika sampel goreng sudah selesai diuji di fryer.
                    </p>
                </div>
                <button type="button" onclick="toggleUjiGorengForm()" class="btn btn-sm" style="background: #b45309; color: #ffffff; font-weight: 700; border-radius: 8px; border: none; padding: 0.45rem 0.9rem;">
                    🍟 Input Hasil Uji Goreng Sekarang
                </button>
            </div>

            <div id="ujiGorengFormBox" style="display: none; margin-top: 1.25rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
                <form action="{{ route('qc.inbound.update_uji_goreng', $qc->qc_id) }}" method="POST">
                    @csrf
                    <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.75rem;">
                        Laboratorium QC &bull; Formulir Pengujian II (Uji Goreng Fryer)
                    </div>

                    @foreach ($qc->details as $d)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="font-weight: 800; font-size: 0.85rem; color: #0284c7; margin-bottom: 0.75rem;">
                                🍟 {{ $d->barang?->barang_nm ?? 'Singkong' }} (Grade: {{ $d->grade_cd }})
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">RASA (Standar: Tdk Pahit)</label>
                                    <select name="items[{{ $d->qcdtl_id }}][fryer_rasa]" class="form-control" style="font-size: 0.825rem; font-weight: 600;">
                                        <option value="TIDAK_PAHIT" {{ ($d->fryer_rasa ?? 'TIDAK_PAHIT') === 'TIDAK_PAHIT' ? 'selected' : '' }}>✅ Tidak Pahit</option>
                                        <option value="PAHIT" {{ ($d->fryer_rasa ?? '') === 'PAHIT' ? 'selected' : '' }}>❌ Pahit / Sianida</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">TEKSTUR (Standar: Renyah)</label>
                                    <select name="items[{{ $d->qcdtl_id }}][fryer_tekstur]" class="form-control" style="font-size: 0.825rem; font-weight: 600;">
                                        <option value="RENYAH" {{ ($d->fryer_tekstur ?? 'RENYAH') === 'RENYAH' ? 'selected' : '' }}>✅ Renyah</option>
                                        <option value="ALOT" {{ ($d->fryer_tekstur ?? '') === 'ALOT' ? 'selected' : '' }}>❌ Alot / Keras</option>
                                        <option value="LEMBEK" {{ ($d->fryer_tekstur ?? '') === 'LEMBEK' ? 'selected' : '' }}>⚠️ Lembek</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">PENAMPAKAN</label>
                                    <select name="items[{{ $d->qcdtl_id }}][fryer_penampakan]" class="form-control" style="font-size: 0.825rem; font-weight: 600;">
                                        <option value="TIDAK_OILSOAKED" {{ ($d->fryer_penampakan ?? 'TIDAK_OILSOAKED') === 'TIDAK_OILSOAKED' ? 'selected' : '' }}>✅ Tidak Oilsoaked</option>
                                        <option value="OILSOAKED" {{ ($d->fryer_penampakan ?? '') === 'OILSOAKED' ? 'selected' : '' }}>❌ Oilsoaked</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                        <button type="button" onclick="toggleUjiGorengForm()" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 800; background: #059669; border-color: #059669;">
                            💾 Simpan Hasil Uji Goreng Lab
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- WRAPPER RESPONSIVE AGAR TABEL DOKUMEN ASLI DAPAT DI-PAN DI HP TANPA HANCUR --}}
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 1rem;">
        
        @if ($kat === 'SINGKONG')
            {{-- ========================================================================= --}}
            {{-- DOKUMEN 1: LAPORAN KEDATANGAN SINGKONG - PENGUJIAN I                      --}}
            {{-- ========================================================================= --}}
            <div class="print-sheet" style="min-width: 820px; background: #ffffff; border: 1.5px solid #000000; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: Arial, sans-serif; font-size: 0.8rem; color: #000000;">
                
                {{-- KOP SURAT RESMI PT MIRASA --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 85px; text-align: center; vertical-align: middle; padding: 6px; border-right: 2px solid #000000;">
                                <img src="{{ asset('images/logo.png') }}" alt="Cap Payung" style="width: 65px; height: 65px; object-fit: contain;">
                            </td>
                            <td style="vertical-align: middle; text-align: center; padding: 6px;">
                                <div style="font-size: 1.2rem; font-weight: 900; color: #000000; letter-spacing: 0.05em;">
                                    PT. MIRASA FOOD INDUSTRY
                                </div>
                                <div style="font-size: 1rem; font-weight: 800; color: #000000; margin-top: 3px;">
                                    {{ $docTitle }}
                                </div>
                            </td>
                            <td style="width: 250px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; width: 85px; border-right: 1px solid #000000;">No. Dokumen</td>
                                        <td style="padding: 3px 6px; font-weight: 800;">{{ $docNo }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi</td>
                                        <td style="padding: 3px 6px;">{{ $revisi }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit</td>
                                        <td style="padding: 3px 6px;">{{ $tglTerbit }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Halaman</td>
                                        <td style="padding: 3px 6px;">1 dari 2</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- TABEL IDENTITAS PENGUJIAN I --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr>
                            {{-- SISI KIRI: JUDUL PENGUJIAN I --}}
                            <td style="width: 35%; vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000; border-bottom: 2px solid #000000;">
                                <div style="font-size: 0.75rem; font-weight: 700; color: #334155; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                                <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                                <div style="font-size: 1.35rem; font-weight: 900; margin-top: 3px; letter-spacing: 0.08em;">
                                    S I N G K O N G
                                </div>
                                <div style="font-size: 1rem; font-weight: 900; margin-top: 3px; color: #0284c7; letter-spacing: 0.05em;">
                                    PENGUJIAN I
                                </div>
                            </td>

                            {{-- SISI KANAN: METADATA & LOKASI PANEN --}}
                            <td style="width: 65%; padding: 0; vertical-align: top; border-bottom: 2px solid #000000;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 6px; width: 100px; font-weight: 700; border-right: 1px solid #000000;">Nama RM</td>
                                        <td colspan="4" style="padding: 4px 6px; font-weight: 800;">: {{ $qc->nama_jenis ?: 'Singkong Basah Curah' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000; background: #f8fafc; text-align: center; font-weight: 700;">
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Nama Produsen</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Negara Produsen</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah Surat Jalan (kg)</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah di Pabrik (kg)</td>
                                        <td style="padding: 4px 6px; width: 120px;">LOKASI PANEN :</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000; text-align: center;">
                                        <td style="padding: 5px 6px; font-weight: 700; border-right: 1px solid #000000;">{{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }}</td>
                                        <td style="padding: 5px 6px; border-right: 1px solid #000000;">{{ $qc->negara_produsen ?: 'Indonesia' }}</td>
                                        <td style="padding: 5px 6px; font-weight: 700; border-right: 1px solid #000000;">{{ $qc->jumlah_surat_jalan ? number_format($qc->jumlah_surat_jalan, 0, ',', '.') : '-' }}</td>
                                        <td style="padding: 5px 6px; font-weight: 800; color: #0284c7; border-right: 1px solid #000000;">{{ $qc->jumlah_di_pabrik ? number_format($qc->jumlah_di_pabrik, 0, ',', '.') : number_format($totalGross, 0, ',', '.') }}</td>
                                        <td rowspan="4" style="padding: 5px 6px; vertical-align: top; font-weight: 700;">
                                            <div>{{ $qc->lokasi_panen ?: 'Wonosobo / Mitra' }}</div>
                                            <div style="margin-top: 15px; border-top: 1px dashed #000; padding-top: 4px; font-size: 0.7rem; font-weight: 700;">
                                                <u>Tanda Tangan ACC</u>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Umur Singkong</td>
                                        <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">: {{ $qc->umur_singkong_bln ? $qc->umur_singkong_bln . ' Bulan' : '9 Bulan' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Panen</td>
                                        <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">: {{ $qc->tgl_panen ? $qc->tgl_panen->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                                        <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">: {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- SAMPLE & DO --}}
                        <tr>
                            <td style="padding: 5px 10px; border-right: 2px solid #000000;">
                                <strong>Jumlah Sample (kg):</strong> : {{ $qc->jumlah_sample_kg ? number_format($qc->jumlah_sample_kg, 1) . ' kg' : '10.0 kg' }}
                            </td>
                            <td style="padding: 5px 10px;">
                                <strong>Nomor DO / SJ :</strong> {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }} &bull; Plat: {{ $qc->plat_nomor_truk ?: '-' }} ({{ $qc->sopir_nama ?: '-' }})
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- KONDISI TRANSPORTASI & AUDIT HALAL --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 5px 8px; width: 170px; font-weight: 700;">KONDISI TRANSPORTASI</td>
                            <td style="padding: 5px 8px; width: 25px; text-align: center;">
                                <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                    {{ $qc->bebas_cemaran_st ? '✔' : '' }}
                                </span>
                            </td>
                            <td style="padding: 5px 8px; width: 240px;">Tidak ada cemaran, Najis / Kotoran</td>
                            <td style="padding: 5px 8px; width: 25px; text-align: center;">
                                <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                    {{ !$qc->bebas_cemaran_st ? '✔' : '' }}
                                </span>
                            </td>
                            <td style="padding: 5px 8px;">Ada cemaran</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                                APAKAH BARANG TERSEBUT DIANGKUT BERSAMA DENGAN BARANG HARAM ?
                            </td>
                            <td colspan="2" style="padding: 5px 8px;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                            {{ !$qc->angkut_barang_haram_st ? '✔' : '' }}
                                        </span> Tidak
                                    </span>
                                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                            {{ $qc->angkut_barang_haram_st ? '✔' : '' }}
                                        </span> Ya
                                    </span>
                                    <span style="margin-left: 0.75rem; color: #475569;">Komentar : {{ $qc->komentar_transportasi ?: '-' }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- 2. ISI RAW MATERIAL, TABEL PARAMETER DIAMETER & CHECKLIST KONDISI --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <div style="padding: 5px 8px; border-bottom: 1px solid #000000; display: flex; align-items: center; gap: 1.5rem; background: #f8fafc;">
                        <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                            </span> TDK STD
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.15fr 1fr;">
                        {{-- TABEL PARAMETER DIAMETER --}}
                        <div style="border-right: 2px solid #000000;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000; text-align: center;">
                                    <td style="padding: 4px; border-right: 1px solid #000000; width: 45%;">Parameter</td>
                                    <td style="padding: 4px; border-right: 1px solid #000000; width: 30%;">Hasil Analisa</td>
                                    <td style="padding: 4px; width: 25%;">Standard</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td colspan="3" style="padding: 3px 6px; font-weight: 700; background: #f8fafc;">1. Diameter</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- &lt; 4 cm</td>
                                    <td style="padding: 4px; text-align: center; font-weight: 800; border-right: 1px solid #000000; color: {{ (float)($firstDetail?->diameter_kurang_4cm_persen ?? 0) > 5 ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->diameter_kurang_4cm_persen !== null ? number_format($firstDetail->diameter_kurang_4cm_persen, 1) . '%' : '-' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Max 5.0%</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- &ge; 4 cm</td>
                                    <td style="padding: 4px; text-align: center; font-weight: 800; border-right: 1px solid #000000; color: {{ (float)($firstDetail?->diameter_lebih_4cm_persen ?? 0) < 95 ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->diameter_lebih_4cm_persen !== null ? number_format($firstDetail->diameter_lebih_4cm_persen, 1) . '%' : '-' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Min 95%</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td colspan="3" style="padding: 3px 6px; font-weight: 700; background: #f8fafc;">3. Hasil Fryer (Pengujian I: Sampel Awal)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- RASA</td>
                                    <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 700;">
                                        {{ $firstDetail?->fryer_rasa === 'PAHIT' ? 'Pahit' : 'Tidak Pahit' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Tidak Pahit</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- Tekstur</td>
                                    <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 700;">
                                        {{ $firstDetail?->fryer_tekstur ? ucfirst(strtolower($firstDetail->fryer_tekstur)) : 'Renyah' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Renyah</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- Penampakan</td>
                                    <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 700;">
                                        {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? 'Oilsoaked' : 'Tidak Oilsoaked' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Tidak Oilsoaked</td>
                                </tr>
                            </table>
                        </div>

                        {{-- CHECKLIST KONDISI FISIK SINGKONG --}}
                        <div style="padding: 8px 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; align-items: center; font-size: 0.775rem;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_segar ? '✔' : '' }}
                                </span> SEGAR
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_busuk ? '✔' : '' }}
                                </span> BUSUK
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_layu ? '✔' : '' }}
                                </span> LAYU
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_berjamur ? '✔' : '' }}
                                </span> BERJAMUR
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_basah ? '✔' : '' }}
                                </span> BASAH
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_lembek ? '✔' : '' }}
                                </span> TEKSTUR LEMBEK
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_terkelupas ? '✔' : '' }}
                                </span> TERKELUPAS
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; color: #64748b;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000;"></span> ....................
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DEFECT FRYING & KESIMPULAN PENGUJIAN I --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem; padding: 6px 10px; font-size: 0.78rem;">
                    <div style="margin-bottom: 0.5rem;">
                        <span style="font-weight: 800; text-decoration: underline;">DEFECT FRYING :</span>
                        <span style="margin-left: 0.5rem;">
                            <u>{{ $firstDetail?->defect_breakage_persen !== null ? number_format($firstDetail->defect_breakage_persen, 1) . '%' : '___' }}</u> Breakage / 
                            <u>{{ $firstDetail?->defect_cluster_persen !== null ? number_format($firstDetail->defect_cluster_persen, 1) . '%' : '___' }}</u> Cluster / 
                            <u>{{ $firstDetail?->defect_foldover_persen !== null ? number_format($firstDetail->defect_foldover_persen, 1) . '%' : '___' }}</u> Foldover / 
                            <u>{{ $firstDetail?->defect_oilsoaked_persen !== null ? number_format($firstDetail->defect_oilsoaked_persen, 1) . '%' : '___' }}</u> Oilsoaked - Polos / 
                            <u>{{ $firstDetail?->defect_gambos_persen !== null ? number_format($firstDetail->defect_gambos_persen, 1) . '%' : '___' }}</u> Gambos (%)
                        </span>
                    </div>

                    <div style="border-top: 1px solid #000000; padding-top: 5px; display: flex; align-items: center; gap: 2rem;">
                        <span style="font-weight: 800;">KESIMPULAN</span>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                            </span>
                            <strong>TERIMA :</strong> <u>{{ number_format($totalNetto, 2, ',', '.') }}</u> kg
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                            </span>
                            <strong>TOLAK :</strong> <u>{{ number_format($totalReject, 2, ',', '.') }}</u> kg
                        </div>
                    </div>

                    <div style="border-top: 1px solid #000000; margin-top: 5px; padding-top: 4px;">
                        <strong>KOMENTAR :</strong> {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: 'Sampling Pengujian I Sesuai Standar HACCP') }}
                    </div>
                </div>

                {{-- TANDA TANGAN RESMI PENGUJIAN I --}}
                <div style="border: 2px solid #000000; font-size: 0.78rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: center;">
                        <tr style="border-bottom: 1px solid #000000; background: #f8fafc; font-weight: 700;">
                            <td style="padding: 4px; width: 50%; border-right: 2px solid #000000;">QC RAW MATERIAL : {{ $qc->petugas_qc_nama }}</td>
                            <td style="padding: 4px; width: 50%;">QC Supervisor : {{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000; background: #f1f5f9; font-weight: 700; font-size: 0.72rem;">
                            <td style="padding: 2px; border-right: 2px solid #000000;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                            </td>
                            <td style="padding: 2px;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                            </td>
                        </tr>
                        <tr style="height: 48px;">
                            <td style="padding: 2px; border-right: 2px solid #000000; vertical-align: middle;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->petugas_qc_nama }}</td><td style="width: 50%; color: #15803d; font-weight: 800;">[VERIFIED]</td></tr></table>
                            </td>
                            <td style="padding: 2px; vertical-align: middle;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td><td style="width: 50%; color: #15803d; font-weight: 800;">[APPROVED]</td></tr></table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="font-size: 0.7rem; margin-top: 4px;">Keterangan : N : Normal, R : Renyah</div>
            </div>

            {{-- ========================================================================= --}}
            {{-- DOKUMEN 2: LAPORAN KEDATANGAN SINGKONG - PENGUJIAN II (FRYER & DEFECT)    --}}
            {{-- ========================================================================= --}}
            <div class="print-sheet" style="min-width: 820px; background: #ffffff; border: 1.5px solid #000000; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: Arial, sans-serif; font-size: 0.8rem; color: #000000;">
                
                {{-- KOP SURAT RESMI PT MIRASA --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 85px; text-align: center; vertical-align: middle; padding: 6px; border-right: 2px solid #000000;">
                                <img src="{{ asset('images/logo.png') }}" alt="Cap Payung" style="width: 65px; height: 65px; object-fit: contain;">
                            </td>
                            <td style="vertical-align: middle; text-align: center; padding: 6px;">
                                <div style="font-size: 1.2rem; font-weight: 900; color: #000000; letter-spacing: 0.05em;">
                                    PT. MIRASA FOOD INDUSTRY
                                </div>
                                <div style="font-size: 1rem; font-weight: 800; color: #000000; margin-top: 3px;">
                                    {{ $docTitle }}
                                </div>
                            </td>
                            <td style="width: 250px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; width: 85px; border-right: 1px solid #000000;">No. Dokumen</td>
                                        <td style="padding: 3px 6px; font-weight: 800;">{{ $docNo }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi</td>
                                        <td style="padding: 3px 6px;">{{ $revisi }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit</td>
                                        <td style="padding: 3px 6px;">{{ $tglTerbit }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Halaman</td>
                                        <td style="padding: 3px 6px;">2 dari 2</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- TABEL IDENTITAS PENGUJIAN II --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr>
                            {{-- SISI KIRI: JUDUL PENGUJIAN II --}}
                            <td style="width: 35%; vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000; border-bottom: 2px solid #000000;">
                                <div style="font-size: 0.75rem; font-weight: 700; color: #334155; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                                <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                                <div style="font-size: 1.35rem; font-weight: 900; margin-top: 3px; letter-spacing: 0.08em;">
                                    S I N G K O N G
                                </div>
                                <div style="font-size: 1rem; font-weight: 900; margin-top: 3px; color: #d97706; letter-spacing: 0.05em;">
                                    PENGUJIAN II
                                </div>
                            </td>

                            {{-- SISI KANAN: METADATA & LOKASI PANEN --}}
                            <td style="width: 65%; padding: 0; vertical-align: top; border-bottom: 2px solid #000000;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 6px; width: 100px; font-weight: 700; border-right: 1px solid #000000;">Nama RM</td>
                                        <td colspan="4" style="padding: 4px 6px; font-weight: 800;">: {{ $qc->nama_jenis ?: 'Singkong Basah Curah' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000; background: #f8fafc; text-align: center; font-weight: 700;">
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Nama Produsen</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Negara Produsen</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah Surat Jalan (kg)</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah di Pabrik (kg)</td>
                                        <td style="padding: 4px 6px; width: 120px;">LOKASI PANEN :</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000; text-align: center;">
                                        <td style="padding: 5px 6px; font-weight: 700; border-right: 1px solid #000000;">{{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }}</td>
                                        <td style="padding: 5px 6px; border-right: 1px solid #000000;">{{ $qc->negara_produsen ?: 'Indonesia' }}</td>
                                        <td style="padding: 5px 6px; font-weight: 700; border-right: 1px solid #000000;">{{ $qc->jumlah_surat_jalan ? number_format($qc->jumlah_surat_jalan, 0, ',', '.') : '-' }}</td>
                                        <td style="padding: 5px 6px; font-weight: 800; color: #0284c7; border-right: 1px solid #000000;">{{ $qc->jumlah_di_pabrik ? number_format($qc->jumlah_di_pabrik, 0, ',', '.') : number_format($totalGross, 0, ',', '.') }}</td>
                                        <td rowspan="4" style="padding: 5px 6px; vertical-align: top; font-weight: 700;">
                                            <div>{{ $qc->lokasi_panen ?: 'Wonosobo / Mitra' }}</div>
                                            <div style="margin-top: 15px; border-top: 1px dashed #000; padding-top: 4px; font-size: 0.7rem; font-weight: 700;">
                                                <u>Tanda Tangan ACC</u>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Umur Singkong</td>
                                        <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">: {{ $qc->umur_singkong_bln ? $qc->umur_singkong_bln . ' Bulan' : '9 Bulan' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Panen</td>
                                        <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">: {{ $qc->tgl_panen ? $qc->tgl_panen->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                                        <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">: {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- SAMPLE & DO --}}
                        <tr>
                            <td style="padding: 5px 10px; border-right: 2px solid #000000;">
                                <strong>Jumlah Sample (kg):</strong> : {{ $qc->jumlah_sample_kg ? number_format($qc->jumlah_sample_kg, 1) . ' kg' : '10.0 kg' }}
                            </td>
                            <td style="padding: 5px 10px;">
                                <strong>Nomor DO / SJ :</strong> {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }} &bull; Plat: {{ $qc->plat_nomor_truk ?: '-' }} ({{ $qc->sopir_nama ?: '-' }})
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- KONDISI TRANSPORTASI & AUDIT HALAL --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 5px 8px; width: 170px; font-weight: 700;">KONDISI TRANSPORTASI</td>
                            <td style="padding: 5px 8px; width: 25px; text-align: center;">
                                <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                    {{ $qc->bebas_cemaran_st ? '✔' : '' }}
                                </span>
                            </td>
                            <td style="padding: 5px 8px; width: 240px;">Tidak ada cemaran, Najis / Kotoran</td>
                            <td style="padding: 5px 8px; width: 25px; text-align: center;">
                                <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                    {{ !$qc->bebas_cemaran_st ? '✔' : '' }}
                                </span>
                            </td>
                            <td style="padding: 5px 8px;">Ada cemaran</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                                APAKAH BARANG TERSEBUT DIANGKUT BERSAMA DENGAN BARANG HARAM ?
                            </td>
                            <td colspan="2" style="padding: 5px 8px;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                            {{ !$qc->angkut_barang_haram_st ? '✔' : '' }}
                                        </span> Tidak
                                    </span>
                                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                            {{ $qc->angkut_barang_haram_st ? '✔' : '' }}
                                        </span> Ya
                                    </span>
                                    <span style="margin-left: 0.75rem; color: #475569;">Komentar : {{ $qc->komentar_transportasi ?: '-' }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- 2. ISI RAW MATERIAL, TABEL HASIL FRYER & CHECKLIST KONDISI --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <div style="padding: 5px 8px; border-bottom: 1px solid #000000; display: flex; align-items: center; gap: 1.5rem; background: #f8fafc;">
                        <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                            </span> TDK STD
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.15fr 1fr;">
                        {{-- TABEL PARAMETER DIAMETER & HASIL FRYER LAB LENGKAP --}}
                        <div style="border-right: 2px solid #000000;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000; text-align: center;">
                                    <td style="padding: 4px; border-right: 1px solid #000000; width: 45%;">Parameter</td>
                                    <td style="padding: 4px; border-right: 1px solid #000000; width: 30%;">Hasil Analisa</td>
                                    <td style="padding: 4px; width: 25%;">Standard</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td colspan="3" style="padding: 3px 6px; font-weight: 700; background: #f8fafc;">1. Diameter</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- &lt; 4 cm</td>
                                    <td style="padding: 4px; text-align: center; font-weight: 800; border-right: 1px solid #000000; color: {{ (float)($firstDetail?->diameter_kurang_4cm_persen ?? 0) > 5 ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->diameter_kurang_4cm_persen !== null ? number_format($firstDetail->diameter_kurang_4cm_persen, 1) . '%' : '-' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Max 5.0%</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- &ge; 4 cm</td>
                                    <td style="padding: 4px; text-align: center; font-weight: 800; border-right: 1px solid #000000; color: {{ (float)($firstDetail?->diameter_lebih_4cm_persen ?? 0) < 95 ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->diameter_lebih_4cm_persen !== null ? number_format($firstDetail->diameter_lebih_4cm_persen, 1) . '%' : '-' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Min 95%</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td colspan="3" style="padding: 3px 6px; font-weight: 700; background: #fef3c7; color: #b45309;">
                                        3. Hasil Fryer (Uji Laboratorium)
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- RASA</td>
                                    <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 800; color: {{ $firstDetail?->fryer_rasa === 'PAHIT' ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->fryer_rasa === 'PAHIT' ? '❌ Pahit' : 'Tidak Pahit' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Tidak Pahit</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #000000;">
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- Tekstur</td>
                                    <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 800; color: {{ $firstDetail?->fryer_tekstur === 'ALOT' ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->fryer_tekstur ? ucfirst(strtolower($firstDetail->fryer_tekstur)) : 'Renyah' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Renyah</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 8px; border-right: 1px solid #000000;">- Penampakan</td>
                                    <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 800; color: {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? '#dc2626' : '#059669' }};">
                                        {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? '❌ Oilsoaked' : 'Tidak Oilsoaked' }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">Tidak Oilsoaked</td>
                                </tr>
                            </table>
                        </div>

                        {{-- CHECKLIST KONDISI FISIK SINGKONG --}}
                        <div style="padding: 8px 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; align-items: center; font-size: 0.775rem;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_segar ? '✔' : '' }}
                                </span> SEGAR
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_busuk ? '✔' : '' }}
                                </span> BUSUK
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_layu ? '✔' : '' }}
                                </span> LAYU
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_berjamur ? '✔' : '' }}
                                </span> BERJAMUR
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_basah ? '✔' : '' }}
                                </span> BASAH
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_lembek ? '✔' : '' }}
                                </span> TEKSTUR LEMBEK
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                    {{ $firstDetail?->kondisi_terkelupas ? '✔' : '' }}
                                </span> TERKELUPAS
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; color: #64748b;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000;"></span> ....................
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DEFECT FRYING & KESIMPULAN PENGUJIAN II --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem; padding: 6px 10px; font-size: 0.78rem;">
                    <div style="margin-bottom: 0.5rem;">
                        <span style="font-weight: 800; text-decoration: underline;">DEFECT FRYING :</span>
                        <span style="margin-left: 0.5rem;">
                            <u>{{ $firstDetail?->defect_breakage_persen !== null ? number_format($firstDetail->defect_breakage_persen, 1) . '%' : '___' }}</u> Breakage / 
                            <u>{{ $firstDetail?->defect_cluster_persen !== null ? number_format($firstDetail->defect_cluster_persen, 1) . '%' : '___' }}</u> Cluster / 
                            <u>{{ $firstDetail?->defect_foldover_persen !== null ? number_format($firstDetail->defect_foldover_persen, 1) . '%' : '___' }}</u> Foldover / 
                            <u>{{ $firstDetail?->defect_oilsoaked_persen !== null ? number_format($firstDetail->defect_oilsoaked_persen, 1) . '%' : '___' }}</u> Oilsoaked - Polos / 
                            <u>{{ $firstDetail?->defect_gambos_persen !== null ? number_format($firstDetail->defect_gambos_persen, 1) . '%' : '___' }}</u> Gambos (%)
                        </span>
                    </div>

                    <div style="border-top: 1px solid #000000; padding-top: 5px; display: flex; align-items: center; gap: 2rem;">
                        <span style="font-weight: 800;">KESIMPULAN FINAL</span>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                            </span>
                            <strong>TERIMA :</strong> <u>{{ number_format($totalNetto, 2, ',', '.') }}</u> kg
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                            </span>
                            <strong>TOLAK :</strong> <u>{{ number_format($totalReject, 2, ',', '.') }}</u> kg
                        </div>
                    </div>

                    <div style="border-top: 1px solid #000000; margin-top: 5px; padding-top: 4px;">
                        <strong>KOMENTAR :</strong> {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: 'Pengujian II Fryer Selesai & Lolos Uji Lab') }}
                    </div>
                </div>

                {{-- TANDA TANGAN RESMI PENGUJIAN II --}}
                <div style="border: 2px solid #000000; font-size: 0.78rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: center;">
                        <tr style="border-bottom: 1px solid #000000; background: #f8fafc; font-weight: 700;">
                            <td style="padding: 4px; width: 50%; border-right: 2px solid #000000;">QC RAW MATERIAL : {{ $qc->petugas_qc_nama }}</td>
                            <td style="padding: 4px; width: 50%;">QC Supervisor : {{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000; background: #f1f5f9; font-weight: 700; font-size: 0.72rem;">
                            <td style="padding: 2px; border-right: 2px solid #000000;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                            </td>
                            <td style="padding: 2px;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                            </td>
                        </tr>
                        <tr style="height: 48px;">
                            <td style="padding: 2px; border-right: 2px solid #000000; vertical-align: middle;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->petugas_qc_nama }}</td><td style="width: 50%; color: #15803d; font-weight: 800;">[VERIFIED]</td></tr></table>
                            </td>
                            <td style="padding: 2px; vertical-align: middle;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td><td style="width: 50%; color: #15803d; font-weight: 800;">[APPROVED]</td></tr></table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="font-size: 0.7rem; margin-top: 4px;">Keterangan : N : Normal, R : Renyah</div>
            </div>

        @else
            {{-- ========================================================================= --}}
            {{-- DOKUMEN 6 KOMODITAS LAINNYA (MINYAK, PLASTIK, KARTON, MSG, GARAM, PERENYAH)--}}
            {{-- MENGGUNAKAN FORMAT ASLI EXCEL HACCP PT MIRASA                              --}}
            {{-- ========================================================================= --}}
            <div class="print-sheet" style="min-width: 820px; background: #ffffff; border: 1.5px solid #000000; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); font-family: Arial, sans-serif; font-size: 0.8rem; color: #000000;">
                
                {{-- KOP SURAT RESMI PT MIRASA --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 85px; text-align: center; vertical-align: middle; padding: 6px; border-right: 2px solid #000000;">
                                <img src="{{ asset('images/logo.png') }}" alt="Cap Payung" style="width: 65px; height: 65px; object-fit: contain;">
                            </td>
                            <td style="vertical-align: middle; text-align: center; padding: 6px;">
                                <div style="font-size: 1.2rem; font-weight: 900; color: #000000; letter-spacing: 0.05em;">
                                    PT. MIRASA FOOD INDUSTRY
                                </div>
                                <div style="font-size: 1rem; font-weight: 800; color: #000000; margin-top: 3px;">
                                    {{ $docTitle }}
                                </div>
                            </td>
                            <td style="width: 250px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; width: 85px; border-right: 1px solid #000000;">No. Dokumen</td>
                                        <td style="padding: 3px 6px; font-weight: 800;">{{ $docNo }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi</td>
                                        <td style="padding: 3px 6px;">{{ $revisi }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit</td>
                                        <td style="padding: 3px 6px;">{{ $tglTerbit }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Halaman</td>
                                        <td style="padding: 3px 6px;">1 dari 1</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- TABEL IDENTITAS KEDATANGAN LAINNYA --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr>
                            <td style="width: 40%; vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000; border-bottom: 2px solid #000000;">
                                <div style="font-size: 0.75rem; font-weight: 700; color: #475569; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                                <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                                <div style="font-size: 1.35rem; font-weight: 900; margin-top: 3px; color: #000000; letter-spacing: 0.04em;">
                                    {{ in_array($kat, ['MSG', 'GARAM', 'PERENYAH']) ? $kat : ($kat === 'MINYAK' ? 'MINYAK GORENG' : ($kat === 'PLASTIK' ? 'PLASTIK' : 'KARTON')) }}
                                </div>
                            </td>
                            <td style="width: 60%; padding: 0; vertical-align: top; border-bottom: 2px solid #000000;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 8px; width: 130px; font-weight: 700; border-right: 1px solid #000000;">Nama Bahan / Barang</td>
                                        <td colspan="3" style="padding: 4px 8px; font-weight: 800;">: {{ $qc->details->pluck('barang.barang_nm')->filter()->first() ?: ($qc->nama_jenis ?: '-') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000; background: #f8fafc; text-align: center; font-weight: 700;">
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Nama Produsen</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Negara Produsen</td>
                                        <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah Surat Jalan</td>
                                        <td style="padding: 4px 6px;">Jumlah di Pabrik</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000; text-align: center;">
                                        <td style="padding: 5px 6px; font-weight: 700; border-right: 1px solid #000000;">{{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }}</td>
                                        <td style="padding: 5px 6px; border-right: 1px solid #000000;">{{ $qc->negara_produsen ?: 'Indonesia' }}</td>
                                        <td style="padding: 5px 6px; font-weight: 700; border-right: 1px solid #000000;">{{ $qc->jumlah_surat_jalan ? number_format($qc->jumlah_surat_jalan, 0, ',', '.') : '-' }}</td>
                                        <td style="padding: 5px 6px; font-weight: 800; color: #0284c7;">{{ $qc->jumlah_di_pabrik ? number_format($qc->jumlah_di_pabrik, 0, ',', '.') : number_format($totalGross, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                                        <td colspan="3" style="padding: 4px 8px;">: {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y H:i') : '-' }} WIB</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Periksa</td>
                                        <td colspan="3" style="padding: 4px 8px;">: {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 10px; border-right: 2px solid #000000;">
                                <strong>Jumlah Sample:</strong> 
                                @if (in_array($kat, ['MINYAK', 'MSG', 'GARAM', 'PERENYAH']))
                                    {{ $qc->jumlah_sample_gr ? $qc->jumlah_sample_gr . ' gr' : '-' }}
                                @elseif ($kat === 'PLASTIK' || $kat === 'KARTON')
                                    {{ $qc->jumlah_sample_pcs ? $qc->jumlah_sample_pcs . ' pcs' : '-' }}
                                @else
                                    {{ $qc->jumlah_sample_kg ? $qc->jumlah_sample_kg . ' kg' : '-' }}
                                @endif
                            </td>
                            <td style="padding: 5px 10px;">
                                <strong>Nomor DO :</strong> {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- KONDISI TRANSPORTASI & AUDIT HALAL --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 5px 8px; width: 170px; font-weight: 700;">KONDISI TRANSPORTASI</td>
                            <td style="padding: 5px 8px; width: 25px; text-align: center;">
                                <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                    {{ $qc->bebas_cemaran_st ? '✔' : '' }}
                                </span>
                            </td>
                            <td style="padding: 5px 8px; width: 240px;">Tidak ada cemaran, Najis / Kotoran</td>
                            <td style="padding: 5px 8px; width: 25px; text-align: center;">
                                <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                    {{ !$qc->bebas_cemaran_st ? '✔' : '' }}
                                </span>
                            </td>
                            <td style="padding: 5px 8px;">Ada cemaran</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">APAKAH BARANG TERSEBUT DIANGKUT BERSAMA DENGAN BARANG HARAM ?</td>
                            <td colspan="2" style="padding: 5px 8px;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                        {{ !$qc->angkut_barang_haram_st ? '✔' : '' }}
                                    </span> Tidak
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; margin-left: 1rem;">
                                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                        {{ $qc->angkut_barang_haram_st ? '✔' : '' }}
                                    </span> Ya
                                </span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">APAKAH BAHAN TERSEBUT TERDAFTAR &amp; DISETUJUI OLEH LPPOM MUI/BPJPH ?</td>
                            <td colspan="2" style="padding: 5px 8px;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                        {{ $qc->terdaftar_lppom_st ? '✔' : '' }}
                                    </span> Ya
                                </span>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">APAKAH BARANG TERSEBUT MEMPUNYAI SERTIFIKAT HALAL ?</td>
                            <td colspan="2" style="padding: 5px 8px;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                        {{ $qc->ada_sertifikat_halal_st ? '✔' : '' }}
                                    </span> Ya
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">APAKAH SERTIFIKAT HALAL BARANG TERSEBUT MASIH BERLAKU ?</td>
                            <td colspan="2" style="padding: 5px 8px;">
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                        {{ $qc->sertifikat_halal_berlaku_st ? '✔' : '' }}
                                    </span> Ya
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- ISI RAW MATERIAL & HASIL UJI KOMODITAS LAINNYA --}}
                <div style="border: 2px solid #000000; margin-bottom: 0.75rem; padding: 6px 10px;">
                    <div style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 1rem;">
                        <strong>2. ISI RAW MATERIAL :</strong>
                        <span>[{{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : ' ' }}] OK</span>
                        <span>[{{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : ' ' }}] TDK STD</span>
                    </div>

                    @if ($kat === 'MINYAK')
                        <div style="display: flex; gap: 2rem; border-top: 1px solid #000; padding-top: 5px;">
                            <div>FFA COA: <strong>{{ $firstDetail?->ffa_coa !== null ? number_format($firstDetail->ffa_coa, 3) : '-' }}</strong></div>
                            <div>FFA QC MIRASA: <strong>{{ $firstDetail?->ffa_qc !== null ? number_format($firstDetail->ffa_qc, 3) : '-' }}</strong></div>
                            <div>[{{ $firstDetail?->minyak_jernih_st ? '✔' : ' ' }}] MINYAK JERNIH</div>
                            <div>[{{ $firstDetail?->tangki_bersih_st ? '✔' : ' ' }}] TANGKI BERSIH</div>
                        </div>
                    @else
                        <div style="display: flex; gap: 1.5rem; border-top: 1px solid #000; padding-top: 5px; flex-wrap: wrap;">
                            <div>[{{ $firstDetail?->isi_kering ? '✔' : ' ' }}] KERING</div>
                            <div>[{{ $firstDetail?->isi_basah ? '✔' : ' ' }}] BASAH</div>
                            <div>[{{ $firstDetail?->isi_gumpal ? '✔' : ' ' }}] GUMPAL</div>
                            <div>[{{ $firstDetail?->isi_berminyak ? '✔' : ' ' }}] BERMINYAK</div>
                            <div>[{{ $firstDetail?->kemasan_kotor ? '✔' : ' ' }}] KEMASAN KOTOR</div>
                            <div>[{{ $firstDetail?->kemasan_sobek ? '✔' : ' ' }}] KEMASAN SOBEK</div>
                        </div>
                    @endif

                    <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 4px; display: flex; align-items: center; gap: 2rem;">
                        <span style="font-weight: 800;">KESIMPULAN :</span>
                        <span>[{{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : ' ' }}] TERIMA</span>
                        <span>[{{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : ' ' }}] TOLAK</span>
                    </div>
                </div>

                {{-- TANDA TANGAN DUA KOLOM RESMI --}}
                <div style="border: 2px solid #000000; font-size: 0.78rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: center;">
                        <tr style="border-bottom: 1px solid #000000; background: #f8fafc; font-weight: 700;">
                            <td style="padding: 4px; width: 50%; border-right: 2px solid #000000;">QC RAW MATERIAL : {{ $qc->petugas_qc_nama }}</td>
                            <td style="padding: 4px; width: 50%;">QC Supervisor : {{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000; background: #f1f5f9; font-weight: 700; font-size: 0.72rem;">
                            <td style="padding: 2px; border-right: 2px solid #000000;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                            </td>
                            <td style="padding: 2px;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                            </td>
                        </tr>
                        <tr style="height: 48px;">
                            <td style="padding: 2px; border-right: 2px solid #000000; vertical-align: middle;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->petugas_qc_nama }}</td><td style="width: 50%; color: #15803d; font-weight: 800;">[VERIFIED]</td></tr></table>
                            </td>
                            <td style="padding: 2px; vertical-align: middle;">
                                <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td><td style="width: 50%; color: #15803d; font-weight: 800;">[APPROVED]</td></tr></table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    function toggleUjiGorengForm() {
        const box = document.getElementById('ujiGorengFormBox');
        if (box) {
            if (box.style.display === 'none' || !box.style.display) {
                box.style.display = 'block';
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                box.style.display = 'none';
            }
        }
    }
</script>

<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm 10mm;
        }
        .no-print, header, footer, .qc-top-appbar, .qc-bottom-nav {
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
            border: 2px solid #000000 !important;
            box-shadow: none !important;
            padding: 1rem !important;
            max-width: 100% !important;
            background: transparent !important;
            page-break-after: always;
        }
        table {
            page-break-inside: avoid;
        }
    }
</style>
@endsection
