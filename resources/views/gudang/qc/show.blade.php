@extends('layouts.app')

@php
    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
    $firstDetail = $qc->details->first();
    $revisi = '1';
    $tglTerbit = '11-09-2023';

    if ($kat === 'MINYAK') {
        $docNo = 'MFI/HACCP-04/FRM-03/029/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Minyak Goreng';
        $docSubtitle = 'LAPORAN KEDATANGAN MINYAK GORENG';
    } elseif ($kat === 'PLASTIK') {
        $docNo = 'MFI/HACCP-04/FRM-03/030/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Plastik';
        $docSubtitle = 'LAPORAN KEDATANGAN PLASTIK';
    } elseif ($kat === 'KARTON') {
        $docNo = 'MFI/HACCP-04/FRM-03/031/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Karton';
        $docSubtitle = 'LAPORAN KEDATANGAN KARTON';
    } elseif ($kat === 'MSG') {
        $docNo = 'MFI/HACCP-04/FRM-03/032/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan MSG';
        $docSubtitle = 'LAPORAN KEDATANGAN MSG';
        $revisi = '1';
        $tglTerbit = '11-09-2023';
    } elseif ($kat === 'GARAM') {
        $docNo = 'MFI/HACCP-04/FRM-03/033/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Garam';
        $docSubtitle = 'LAPORAN KEDATANGAN GARAM';
        $revisi = '1';
        $tglTerbit = '11-09-2023';
    } elseif ($kat === 'PERENYAH') {
        $docNo = 'MFI/HACCP-04/FRM-03/063/IX/2023';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Perenyah';
        $docSubtitle = 'LAPORAN KEDATANGAN PERENYAH';
        $revisi = '0';
        $tglTerbit = '14-09-2023';
    } else {
        $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
        $docTitle = 'Checklist Standar Kebeterimaan Singkong';
        $docSubtitle = 'LAPORAN KEDATANGAN SINGKONG';
        $revisi = '1';
        $tglTerbit = '11-09-2023';
    }
@endphp

@section('title', 'Dokumen QC HACCP: ' . $qc->qc_no)

@section('content')
<div style="max-width: 950px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- TOP ACTION BAR (Hidden when printing) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Riwayat Tiket QC
            </a>
            <span style="font-size: 0.875rem; color: #64748b;">|</span>
            <span style="font-size: 0.9rem; font-weight: 700; color: #0f172a;">Tiket #{{ $qc->qc_no }}</span>
            <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0284c7; padding: 0.15rem 0.5rem; border-radius: 12px;">
                {{ $kat }}
            </span>
            @if ($qc->terima)
                <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" style="font-size: 0.75rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 0.15rem 0.5rem; border-radius: 12px; text-decoration: none; border: 1px solid #86efac;">
                    📦 GRN #{{ $qc->terima->terima_no }}
                </a>
            @endif
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            {{-- TOMBOL EDIT / KOREKSI --}}
            @if (Auth::user()?->canEditQc())
                @if (!$qc->terima || Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border-color: #93c5fd;">
                        ✏️ Edit / Koreksi
                    </a>
                @else
                    <span class="btn btn-secondary btn-sm" title="Tiket terkunci karena sudah diproses ke Penerimaan Gudang. Hanya Super Administrator yang berhak mengedit." style="border-radius: 8px; font-weight: 700; opacity: 0.65; cursor: not-allowed; background: #f1f5f9; color: #64748b;">
                        🔒 Terkunci (GRN)
                    </span>
                @endif
            @endif

            {{-- TOMBOL HAPUS / BATALKAN TIKET --}}
            @if (Auth::user()?->canDeleteQc() && (!$qc->terima || Auth::user()?->isSuperAdmin()))
                <form action="{{ route('qc.inbound.destroy', $qc->qc_id) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan / menghapus tiket QC #{{ $qc->qc_no }}? {{ $qc->terima ? 'PERHATIAN: Tiket ini terhubung dengan GRN #' . $qc->terima->terima_no . ' (Super Admin Override).' : '' }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700; background: #fef2f2; color: #dc2626; border-color: #fca5a5;">
                        🗑️ Hapus
                    </button>
                </form>
            @endif

            <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700; background: #fee2e2; color: #b91c1c; border-color: #fca5a5;">
                📄 Berita Acara Penolakan
            </a>
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 700;">
                🖨️ Cetak Formulir HACCP (A4)
            </button>
            @if ($qc->status_qc === 'SIAP_GUDANG' && Auth::user()?->canAccessTerima())
                <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 700; background: #059669; border-color: #059669;">
                    📦 Tarik ke Penerimaan Barang (GRN) &rarr;
                </a>
            @endif
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
                        Truk sudah di-ACC untuk bongkar muat gudang. Masukkan hasil uji penggorengan lab di bawah ini jika sampel goreng sudah selesai diuji.
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

    {{-- OFFICIAL HACCP DOCUMENT CARD (PRINTABLE A4) --}}
    <div class="card print-sheet" style="background: #ffffff; border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 2rem;">
        
        {{-- KOP SURAT RESMI PT MIRASA & FORM METADATA HACCP --}}
        <div style="border: 2px solid #000000; margin-bottom: 1rem;">
            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">
                <tr>
                    <td style="width: 90px; text-align: center; vertical-align: middle; padding: 8px; border-right: 2px solid #000000;">
                        <img src="{{ asset('images/logo.png') }}" alt="Cap Payung" style="width: 70px; height: 70px; object-fit: contain;">
                    </td>
                    <td style="vertical-align: middle; text-align: center; padding: 8px;">
                        <div style="font-size: 1.25rem; font-weight: 900; color: #000000; letter-spacing: 0.05em;">
                            PT. MIRASA FOOD INDUSTRY
                        </div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #000000; margin-top: 4px;">
                            {{ $docTitle }}
                        </div>
                    </td>
                    <td style="width: 260px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem; text-align: left;">
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px 6px; font-weight: 700; width: 85px; border-right: 1px solid #000000;">No. Dokumen</td>
                                <td style="padding: 4px 6px; font-weight: 800;">{{ $docNo }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi</td>
                                <td style="padding: 4px 6px;">{{ $revisi }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit</td>
                                <td style="padding: 4px 6px;">{{ $tglTerbit }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Halaman</td>
                                <td style="padding: 4px 6px;">1 dari 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- TABEL IDENTITAS KEDATANGAN (PERSIS FORMAT EXCEL MFI) --}}
        <div style="border: 2px solid #000000; margin-bottom: 1rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; font-family: Arial, sans-serif;">
                <tr>
                    {{-- SISI KIRI: JUDUL BESAR LAPORAN KEDATANGAN --}}
                    <td style="width: 42%; vertical-align: middle; text-align: center; padding: 1rem 0.5rem; border-right: 2px solid #000000; border-bottom: 2px solid #000000;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: #475569; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                        <div style="font-size: 0.825rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                        <div style="font-size: 1.35rem; font-weight: 900; margin-top: 4px; color: #000000; letter-spacing: 0.04em;">
                            {{ in_array($kat, ['MSG', 'GARAM', 'PERENYAH']) ? $kat : ($kat === 'SINGKONG' ? 'SINGKONG' : ($kat === 'MINYAK' ? 'MINYAK GORENG' : ($kat === 'PLASTIK' ? 'PLASTIK' : 'KARTON'))) }}
                        </div>
                    </td>

                    {{-- SISI KANAN: TABEL PRODUSEN, JUMLAH, JENIS --}}
                    <td style="width: 58%; padding: 0; vertical-align: top; border-bottom: 2px solid #000000;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px 8px; width: 130px; font-weight: 700; border-right: 1px solid #000000;">
                                    {{ in_array($kat, ['MSG', 'GARAM', 'PERENYAH']) ? 'Nama Bahan Penolong' : 'Nama Bahan / Barang' }}
                                </td>
                                <td colspan="3" style="padding: 4px 8px; font-weight: 800;">
                                    : {{ $qc->details->pluck('barang.barang_nm')->filter()->first() ?: ($qc->nama_jenis ?: '-') }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000; background: #f8fafc; text-align: center; font-weight: 700;">
                                <td style="padding: 4px 6px; border-right: 1px solid #000000;">Nama Produsen</td>
                                <td style="padding: 4px 6px; border-right: 1px solid #000000;">Negara Produsen</td>
                                <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah Surat Jalan</td>
                                <td style="padding: 4px 6px;">Jumlah di Pabrik</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000; text-align: center;">
                                <td style="padding: 6px; font-weight: 700; border-right: 1px solid #000000;">
                                    {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }}
                                </td>
                                <td style="padding: 6px; border-right: 1px solid #000000;">
                                    {{ $qc->negara_produsen ?: 'Indonesia' }}
                                </td>
                                <td style="padding: 6px; font-weight: 700; border-right: 1px solid #000000;">
                                    {{ $qc->jumlah_surat_jalan ? number_format($qc->jumlah_surat_jalan, 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding: 6px; font-weight: 800; color: #0284c7;">
                                    {{ $qc->jumlah_di_pabrik ? number_format($qc->jumlah_di_pabrik, 0, ',', '.') : ($qc->details->sum('qty_timbang_gross') > 0 ? number_format($qc->details->sum('qty_timbang_gross'), 0, ',', '.') : '-') }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                                <td colspan="3" style="padding: 4px 8px;">
                                    : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y H:i') : '-' }} WIB
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Periksa</td>
                                <td colspan="3" style="padding: 4px 8px;">
                                    : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">NAMA JENIS</td>
                                <td colspan="3" style="padding: 4px 8px; font-weight: 800;">
                                    : {{ $qc->nama_jenis ?: '-' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- BARIS KEDUA: JUMLAH SAMPLE & NOMOR DO --}}
                <tr>
                    <td style="padding: 6px 10px; border-right: 2px solid #000000;">
                        <strong>Jumlah Sample:</strong> 
                        @if (in_array($kat, ['MINYAK', 'MSG', 'GARAM', 'PERENYAH']))
                            {{ $qc->jumlah_sample_gr ? $qc->jumlah_sample_gr . ' gr' : '-' }}
                        @elseif ($kat === 'PLASTIK' || $kat === 'KARTON')
                            {{ $qc->jumlah_sample_pcs ? $qc->jumlah_sample_pcs . ' pcs' : '-' }}
                        @else
                            {{ $qc->jumlah_sample_kg ? $qc->jumlah_sample_kg . ' kg' : '-' }}
                        @endif
                    </td>
                    <td style="padding: 6px 10px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 120px;"><strong>Nomor DO :</strong></td>
                                <td>{{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- STANDAR TRANSPORTASI & JAMINAN HALAL (PERSIS TABEL EXCEL MFI) --}}
        <div style="border: 2px solid #000000; margin-bottom: 1rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; font-family: Arial, sans-serif;">
                {{-- 1. KONDISI TRANSPORTASI --}}
                <tr style="border-bottom: 1px solid #000000;">
                    <td style="padding: 6px 10px; width: 220px; font-weight: 700;">KONDISI TRANSPORTASI</td>
                    <td style="padding: 6px 10px; width: 30px; text-align: center;">
                        <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                            {{ $qc->bebas_cemaran_st ? '✔' : '' }}
                        </span>
                    </td>
                    <td style="padding: 6px 10px; width: 260px;">Tidak ada cemaran, Najis / Kotoran</td>
                    <td style="padding: 6px 10px; width: 30px; text-align: center;">
                        <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                            {{ !$qc->bebas_cemaran_st ? '✔' : '' }}
                        </span>
                    </td>
                    <td style="padding: 6px 10px;">Ada cemaran</td>
                </tr>

                {{-- 2. ANGKUT BERSAMA BARANG HARAM --}}
                <tr style="border-bottom: 1px solid #000000;">
                    <td colspan="3" style="padding: 6px 10px; font-weight: 700;">
                        APAKAH BARANG TERSEBUT DIANGKUT BERSAMA DENGAN BARANG HARAM ?
                    </td>
                    <td colspan="2" style="padding: 6px 10px;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ !$qc->angkut_barang_haram_st ? '✔' : '' }}
                                </span> Tidak
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ $qc->angkut_barang_haram_st ? '✔' : '' }}
                                </span> Ya
                            </span>
                            <span style="margin-left: 1rem; color: #475569;">Komentar : {{ $qc->komentar_transportasi ?: '-' }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 3. TERDAFTAR & DISETUJUI LPPOM MUI / BPJPH --}}
                <tr style="border-bottom: 1px solid #000000;">
                    <td colspan="3" style="padding: 6px 10px; font-weight: 700;">
                        APAKAH BAHAN TERSEBUT TERDAFTAR &amp; DISETUJUI OLEH LPPOM MUI/BPJPH ?
                    </td>
                    <td colspan="2" style="padding: 6px 10px;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ !$qc->terdaftar_lppom_st ? '✔' : '' }}
                                </span> Tidak
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ $qc->terdaftar_lppom_st ? '✔' : '' }}
                                </span> Ya
                            </span>
                            <span style="margin-left: 1rem; color: #475569;">Komentar : {{ $qc->komentar_lppom ?: '-' }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 4. MEMPUNYAI SERTIFIKAT HALAL --}}
                <tr style="border-bottom: 1px solid #000000;">
                    <td colspan="3" style="padding: 6px 10px; font-weight: 700;">
                        APAKAH BARANG TERSEBUT MEMPUNYAI SERTIFIKAT HALAL ?
                    </td>
                    <td colspan="2" style="padding: 6px 10px;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ !$qc->ada_sertifikat_halal_st ? '✔' : '' }}
                                </span> Tidak
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ $qc->ada_sertifikat_halal_st ? '✔' : '' }}
                                </span> Ya
                            </span>
                            <span style="margin-left: 1rem; color: #475569;">Komentar : {{ $qc->komentar_sertifikat ?: '-' }}</span>
                        </div>
                    </td>
                </tr>

                {{-- 5. SERTIFIKAT HALAL MASIH BERLAKU --}}
                <tr>
                    <td colspan="3" style="padding: 6px 10px; font-weight: 700;">
                        APAKAH SERTIFIKAT HALAL BARANG TERSEBUT MASIH BERLAKU ?
                    </td>
                    <td colspan="2" style="padding: 6px 10px;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ !$qc->sertifikat_halal_berlaku_st ? '✔' : '' }}
                                </span> Tidak
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                    {{ $qc->sertifikat_halal_berlaku_st ? '✔' : '' }}
                                </span> Ya
                            </span>
                            <span style="margin-left: 1rem; color: #475569;">Komentar : {{ $qc->komentar_berlaku ?: '-' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ========================================================================= --}}
        {{-- SECTION PEMERIKSAAN SPESIFIK SESUAI KOMODITAS                             --}}
        {{-- ========================================================================= --}}

        @if ($kat === 'MINYAK')
            {{-- MINYAK GORENG (MFI/HACCP-04/FRM-03/029/VIII/2021) --}}
            <div style="border: 2px solid #000000; margin-bottom: 1rem; font-family: Arial, sans-serif; font-size: 0.825rem;">
                {{-- ISI RAW MATERIAL & KONDISI TANGKI/JERIGEN --}}
                <div style="display: flex; border-bottom: 2px solid #000000; padding: 6px 10px; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                            </span> TDK STD
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">KONDISI TANGKI / JERIGEN</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kondisi_tangki_jerigen ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}
                            </span> TIDAK STANDARD
                        </span>
                    </div>
                </div>

                {{-- TABEL FFA & CHECKBOX MINYAK JERNIH / TANGKI BERSIH --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 2px solid #000000;">
                    <div style="border-right: 2px solid #000000;">
                        <table style="width: 100%; border-collapse: collapse; text-align: center;">
                            <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000;">
                                <td style="padding: 6px; border-right: 1px solid #000000; width: 33%;">Parameter</td>
                                <td style="padding: 6px; border-right: 1px solid #000000; width: 33%;">FFA DI COA</td>
                                <td style="padding: 6px; width: 34%;">FFA CEK QC MIRASA</td>
                            </tr>
                            <tr style="height: 50px;">
                                <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 800;">FFA</td>
                                <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 700;">
                                    {{ $firstDetail?->ffa_coa !== null ? number_format($firstDetail->ffa_coa, 3, ',', '.') : '-' }}
                                </td>
                                <td style="padding: 6px; font-weight: 900; color: #0284c7;">
                                    {{ $firstDetail?->ffa_qc !== null ? number_format($firstDetail->ffa_qc, 3, ',', '.') : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="padding: 10px 14px; display: flex; flex-direction: column; justify-content: center; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900; font-size: 0.95rem;">
                                {{ $firstDetail?->minyak_jernih_st ? '✔' : '' }}
                            </span>
                            <span style="font-weight: 800;">MINYAK JERNIH</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900; font-size: 0.95rem;">
                                {{ $firstDetail?->tangki_bersih_st ? '✔' : '' }}
                            </span>
                            <span style="font-weight: 800;">TANGKI BAGIAN DALAM BERSIH</span>
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="padding: 8px 10px; border-bottom: 2px solid #000000;">
                    <span style="font-weight: 800; text-decoration: underline;">KOMENTAR :</span>
                    <div style="margin-top: 4px; min-height: 25px; color: #1e293b;">
                        {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: '-') }}
                    </div>
                </div>

                <div style="padding: 8px 10px; display: flex; align-items: center; gap: 1.5rem;">
                    <span style="font-weight: 800;">KESIMPULAN</span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TERIMA
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TOLAK
                    </span>
                </div>
            </div>

        @elseif ($kat === 'PLASTIK')
            {{-- PLASTIK (MFI/HACCP-04/FRM-03/030/VIII/2021) --}}
            <div style="border: 2px solid #000000; margin-bottom: 1rem; font-family: Arial, sans-serif; font-size: 0.825rem;">
                {{-- ISI RAW MATERIAL & KEMASAN --}}
                <div style="display: flex; border-bottom: 2px solid #000000; padding: 6px 10px; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                            </span> TDK STD
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">KEMASAN</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}
                            </span> TIDAK STANDARD
                        </span>
                    </div>
                </div>

                {{-- PARAMETER KETEBALAN / KEUTUHAN & CACAT KEMASAN --}}
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; border-bottom: 2px solid #000000;">
                    <div style="border-right: 2px solid #000000;">
                        <table style="width: 100%; border-collapse: collapse; text-align: center;">
                            <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000;">
                                <td style="padding: 6px; border-right: 1px solid #000000; width: 35%;">Parameter</td>
                                <td style="padding: 6px; border-right: 1px solid #000000; width: 35%;">Hasil Analisa</td>
                                <td style="padding: 6px; width: 30%;">Standard</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 700;">KETEBALAN</td>
                                <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 800;">
                                    {{ $firstDetail?->ketebalan_analisa ?: '-' }}
                                </td>
                                <td style="padding: 6px;">{{ $firstDetail?->ketebalan_standar ?: '0.08 mm' }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 700;">KEUTUHAN</td>
                                <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 800;">
                                    {{ $firstDetail?->keutuhan_analisa ?: 'Tidak Sobek' }}
                                </td>
                                <td style="padding: 6px;">Tidak Sobek</td>
                            </tr>
                        </table>
                    </div>

                    <div style="padding: 10px 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_kotor ? '✔' : '' }}
                            </span> KOTOR
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_apek ? '✔' : '' }}
                            </span> APEK
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_basah ? '✔' : '' }}
                            </span> BASAH
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_sobek ? '✔' : '' }}
                            </span> SOBEK
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="padding: 8px 10px; border-bottom: 2px solid #000000;">
                    <span style="font-weight: 800; text-decoration: underline;">KOMENTAR :</span>
                    <div style="margin-top: 4px; min-height: 25px; color: #1e293b;">
                        {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: '-') }}
                    </div>
                </div>

                <div style="padding: 8px 10px; display: flex; align-items: center; gap: 1.5rem;">
                    <span style="font-weight: 800;">KESIMPULAN</span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TERIMA
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TOLAK
                    </span>
                </div>
            </div>

        @elseif ($kat === 'KARTON')
            {{-- KARTON (MFI/HACCP-04/FRM-03/031/VIII/2021) --}}
            <div style="border: 2px solid #000000; margin-bottom: 1rem; font-family: Arial, sans-serif; font-size: 0.825rem;">
                {{-- ISI RAW MATERIAL & KEMASAN --}}
                <div style="display: flex; border-bottom: 2px solid #000000; padding: 6px 10px; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                            </span> TDK STD
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">KEMASAN</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}
                            </span> TIDAK STANDARD
                        </span>
                    </div>
                </div>

                {{-- PARAMETER DIMENSI & CACAT KEMASAN KARTON --}}
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; border-bottom: 2px solid #000000;">
                    <div style="border-right: 2px solid #000000;">
                        <table style="width: 100%; border-collapse: collapse; text-align: center;">
                            <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000;">
                                <td style="padding: 5px; border-right: 1px solid #000000; width: 35%;">Parameter</td>
                                <td style="padding: 5px; border-right: 1px solid #000000; width: 35%;">Hasil Analisa</td>
                                <td style="padding: 5px; width: 30%;">Standard</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">PANJANG</td>
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">
                                    {{ $firstDetail?->dimensi_panjang_analisa ?: '-' }}
                                </td>
                                <td style="padding: 5px;">{{ $firstDetail?->dimensi_panjang_standar ?: '-' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">LEBAR</td>
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">
                                    {{ $firstDetail?->dimensi_lebar_analisa ?: '-' }}
                                </td>
                                <td style="padding: 5px;">{{ $firstDetail?->dimensi_lebar_standar ?: '-' }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">TINGGI</td>
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">
                                    {{ $firstDetail?->dimensi_tinggi_analisa ?: '-' }}
                                </td>
                                <td style="padding: 5px;">{{ $firstDetail?->dimensi_tinggi_standar ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">SPESIFIKASI</td>
                                <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">
                                    {{ $firstDetail?->spesifikasi_analisa ?: '-' }}
                                </td>
                                <td style="padding: 5px;">{{ $firstDetail?->spesifikasi_standar ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div style="padding: 8px 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; align-items: center; font-size: 0.8rem;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_kotor ? '✔' : '' }}
                            </span> KOTOR
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_apek ? '✔' : '' }}
                            </span> APEK
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_basah ? '✔' : '' }}
                            </span> BASAH
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_jamur ? '✔' : '' }}
                            </span> JAMUR
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_sobek ? '✔' : '' }}
                            </span> SOBEK
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_berminyak ? '✔' : '' }}
                            </span> BERMINYAK
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_berdebu ? '✔' : '' }}
                            </span> BERDEBU
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="padding: 8px 10px; border-bottom: 2px solid #000000;">
                    <span style="font-weight: 800; text-decoration: underline;">KOMENTAR :</span>
                    <div style="margin-top: 4px; min-height: 25px; color: #1e293b;">
                        {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: '-') }}
                    </div>
                </div>

                <div style="padding: 8px 10px; display: flex; align-items: center; gap: 1.5rem;">
                    <span style="font-weight: 800;">KESIMPULAN</span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TERIMA
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TOLAK
                    </span>
                </div>
            </div>

        @elseif (in_array($kat, ['MSG', 'GARAM', 'PERENYAH']))
            {{-- BAHAN PENOLONG (MSG: HACCP-032, GARAM: HACCP-033, PERENYAH: HACCP-063) --}}
            <div style="border: 2px solid #000000; margin-bottom: 1rem; font-family: Arial, sans-serif; font-size: 0.825rem;">
                {{-- ISI RAW MATERIAL & KEMASAN --}}
                <div style="display: flex; border-bottom: 2px solid #000000; padding: 6px 10px; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                            </span> TDK STD
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 800;">KEMASAN</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? '✔' : '' }}
                            </span> OK
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900; font-size: 0.85rem;">
                                {{ ($firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}
                            </span> TIDAK STANDARD
                        </span>
                    </div>
                </div>

                {{-- CHECKBOX GRID 2x4 (PERSIS EXCEL MFI) --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 2px solid #000000; min-height: 100px;">
                    {{-- SISI KIRI: KONDISI ISI --}}
                    <div style="border-right: 2px solid #000000; padding: 12px 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->isi_kering ? '✔' : '' }}
                            </span> KERING
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->isi_basah ? '✔' : '' }}
                            </span> BASAH
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->isi_gumpal ? '✔' : '' }}
                            </span> GUMPAL
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->isi_berminyak ? '✔' : '' }}
                            </span> BERMINYAK
                        </div>
                    </div>

                    {{-- SISI KANAN: KONDISI KEMASAN --}}
                    <div style="padding: 12px 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_kotor ? '✔' : '' }}
                            </span> KOTOR
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_apek ? '✔' : '' }}
                            </span> APEK
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_jamur ? '✔' : '' }}
                            </span> JAMUR
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                                {{ $firstDetail?->kemasan_sobek ? '✔' : '' }}
                            </span> SOBEK
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="padding: 8px 10px; border-bottom: 2px solid #000000;">
                    <span style="font-weight: 800; text-decoration: underline;">KOMENTAR :</span>
                    <div style="margin-top: 4px; min-height: 25px; color: #1e293b;">
                        {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: '-') }}
                    </div>
                </div>

                <div style="padding: 8px 10px; display: flex; align-items: center; gap: 1.5rem;">
                    <span style="font-weight: 800;">KESIMPULAN</span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TERIMA
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                        <span style="display: inline-block; width: 18px; height: 18px; border: 1.5px solid #000; text-align: center; line-height: 16px; font-weight: 900;">
                            {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                        </span> TOLAK
                    </span>
                </div>
            </div>

        @else
            {{-- SINGKONG (MFI/HACCP-04/FRM-03/048/VIII/2021) --}}
            {{-- SECTION: PENGUJIAN I (SAMPLING FISIK & DIAMETER) --}}
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

                        <div style="padding: 0.75rem; font-size: 0.8rem; display: grid; grid-template-columns: 1fr 1.3fr; gap: 1rem; border-bottom: 1px solid #f1f5f9;">
                            <div style="border-right: 1px dashed #cbd5e1; padding-right: 0.75rem;">
                                <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.35rem; font-size: 0.775rem;">
                                    📏 STANDAR DIAMETER SINGKONG:
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 3px 0; color: #64748b;">Diameter &lt; 4 cm (Standar Maks 5.0%):</td>
                                        <td style="padding: 3px 0; text-align: right; font-weight: 700; color: {{ (float)$d->diameter_kurang_4cm_persen > 5 ? '#dc2626' : '#15803d' }};">
                                            {{ number_format($d->diameter_kurang_4cm_persen, 1, ',', '.') }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px 0; color: #64748b;">Diameter &ge; 4 cm (Standar Min 95.0%):</td>
                                        <td style="padding: 3px 0; text-align: right; font-weight: 700; color: {{ (float)$d->diameter_lebih_4cm_persen < 95 ? '#dc2626' : '#15803d' }};">
                                            {{ number_format($d->diameter_lebih_4cm_persen, 1, ',', '.') }}%
                                        </td>
                                    </tr>
                                </table>
                            </div>

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
                                    <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_terkelupas ? 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                        [{{ $d->kondisi_terkelupas ? '✔' : ' ' }}] Terkelupas
                                    </span>
                                    <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_busuk ? 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                        [{{ $d->kondisi_busuk ? '✔' : ' ' }}] Busuk
                                    </span>
                                    <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_berjamur ? 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                        [{{ $d->kondisi_berjamur ? '✔' : ' ' }}] Berjamur
                                    </span>
                                    <span style="padding: 2px 6px; border-radius: 4px; font-weight: 600; {{ $d->kondisi_lembek ? 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;' : 'background: #f1f5f9; color: #94a3b8;' }}">
                                        [{{ $d->kondisi_lembek ? '✔' : ' ' }}] Lembek
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Timbangan & Refraksi --}}
                        <div style="background: #fafafa; padding: 0.5rem 0.75rem; font-size: 0.8rem; display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; text-align: center;">
                            <div>
                                <span style="font-size: 0.7rem; color: #64748b;">GROSS (KOTOR)</span>
                                <div style="font-weight: 800; color: #0f172a; margin-top: 2px;">
                                    {{ number_format($d->qty_timbang_gross, 2, ',', '.') }} KG
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #64748b;">KADAR AIR</span>
                                <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">
                                    {{ number_format($d->kadar_air_persen, 1, ',', '.') }}%
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #ca8a04;">REFRAKSI TANAH</span>
                                <div style="font-weight: 800; color: #b45309; margin-top: 2px;">
                                    -{{ number_format($d->qty_refraksi, 2, ',', '.') }} KG ({{ number_format($d->refraksi_persen, 1, ',', '.') }}%)
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; color: #dc2626;">REJECT CACAT</span>
                                <div style="font-weight: 800; color: #dc2626; margin-top: 2px;">
                                    -{{ number_format($d->qty_reject, 2, ',', '.') }} KG
                                </div>
                            </div>
                            <div style="background: #dcfce7; border-radius: 4px; padding: 2px 4px;">
                                <span style="font-size: 0.7rem; color: #166534; font-weight: 800;">NETTO LOLOS</span>
                                <div style="font-weight: 900; color: #15803d; font-size: 0.95rem; margin-top: 1px;">
                                    {{ number_format($d->qty_netto_lolos, 2, ',', '.') }} KG
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- SECTION: PENGUJIAN II (HASIL FRYER / UJI GORENG LAB) --}}
            <div style="margin-bottom: 1.5rem;">
                <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #f59e0b; padding-left: 0.5rem;">
                    <span>PENGUJIAN II : HASIL FRYER &amp; DEFECT CACAT GORENG (LABORATORIUM QC)</span>
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                        Standar: Rasa Tidak Pahit, Tekstur Renyah &amp; Cacat &lt; 5%
                    </span>
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem; border: 1px solid #cbd5e1;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; text-align: center;">
                            <th style="padding: 0.5rem 0.6rem; text-align: left; border-right: 1px solid #cbd5e1; width: 140px;">Komoditas</th>
                            <th style="padding: 0.5rem 0.6rem; border-right: 1px solid #cbd5e1;">Rasa</th>
                            <th style="padding: 0.5rem 0.6rem; border-right: 1px solid #cbd5e1;">Tekstur</th>
                            <th style="padding: 0.5rem 0.6rem; border-right: 1px solid #cbd5e1;">Penampakan</th>
                            <th style="padding: 0.5rem 0.6rem;">Breakage</th>
                            <th style="padding: 0.5rem 0.6rem;">Cluster</th>
                            <th style="padding: 0.5rem 0.6rem;">Foldover</th>
                            <th style="padding: 0.5rem 0.6rem;">Oilsoaked</th>
                            <th style="padding: 0.5rem 0.6rem;">Gambos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($qc->details as $d)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.5rem 0.6rem; font-weight: 700; border-right: 1px solid #cbd5e1;">
                                    {{ $d->barang?->barang_nm ?? '-' }}
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; font-weight: 700; color: {{ $d->fryer_rasa === 'TIDAK_PAHIT' ? '#15803d' : '#dc2626' }};">
                                    {{ $d->fryer_rasa === 'TIDAK_PAHIT' ? 'Tdk Pahit' : 'PAHIT' }}
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; font-weight: 700; color: {{ $d->fryer_tekstur === 'RENYAH' ? '#15803d' : '#dc2626' }};">
                                    {{ $d->fryer_tekstur === 'RENYAH' ? 'Renyah' : 'ALOT' }}
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: center; border-right: 1px solid #cbd5e1; font-weight: 700; color: {{ $d->fryer_penampakan === 'TIDAK_OILSOAKED' ? '#15803d' : '#dc2626' }};">
                                    {{ $d->fryer_penampakan === 'TIDAK_OILSOAKED' ? 'Tdk Oilsoaked' : 'OILSOAKED' }}
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                    {{ number_format($d->defect_breakage_persen, 1, ',', '.') }}%
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                    {{ number_format($d->defect_cluster_persen, 1, ',', '.') }}%
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                    {{ number_format($d->defect_foldover_persen, 1, ',', '.') }}%
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600;">
                                    {{ number_format($d->defect_oilsoaked_persen, 1, ',', '.') }}%
                                </td>
                                <td style="padding: 0.5rem 0.6rem; text-align: right; font-weight: 600; color: {{ (float)$d->defect_gambos_persen > 0 ? '#b45309' : '#0f172a' }};">
                                    {{ number_format($d->defect_gambos_persen, 1, ',', '.') }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- REKAPITULASI HASIL (ACC / TOLAK KG) --}}
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
        @endif

        {{-- ========================================================================= --}}
        {{-- TANDA TANGAN (SIGNATURE BLOCKS RESMI SESUAI EXCEL MFI)                    --}}
        {{-- ========================================================================= --}}
        @if ($kat === 'SINGKONG')
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; text-align: center; margin-top: 2rem; font-size: 0.775rem;">
                <div>
                    <div style="color: #64748b; margin-bottom: 3.5rem;">Pengemudi / Sopir Supplier,</div>
                    <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                        {{ $qc->sopir_nama ?: '( ................................... )' }}
                    </div>
                </div>
                <div>
                    <div style="color: #64748b; margin-bottom: 3.5rem;">Petugas QC Raw Material,</div>
                    <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                        {{ $qc->petugas_qc_nama }}
                    </div>
                </div>
                <div>
                    <div style="color: #64748b; margin-bottom: 3.5rem;">QC Supervisor,</div>
                    <div style="font-weight: 700; color: #0f172a; border-top: 1px dashed #94a3b8; padding-top: 0.35rem;">
                        {{ $qc->qc_supervisor_nama ?: '( ................................... )' }}
                    </div>
                </div>
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
        @else
            {{-- FORMAT SIGNATURE DUA KOLOM RESMI EXCEL (QC RAW MATERIAL & QC SUPERVISOR) --}}
            <div style="border: 2px solid #000000; font-family: Arial, sans-serif; font-size: 0.8rem; margin-top: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse; text-align: center;">
                    <tr style="border-bottom: 1px solid #000000; background: #f8fafc; font-weight: 700;">
                        <td style="padding: 6px; width: 50%; border-right: 2px solid #000000;">
                            QC RAW MATERIAL : {{ $qc->petugas_qc_nama }}
                        </td>
                        <td style="padding: 6px; width: 50%;">
                            QC Supervisor : {{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000; background: #f1f5f9; font-weight: 700; font-size: 0.75rem;">
                        <td style="padding: 4px; border-right: 2px solid #000000;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 50%; border-right: 1px solid #000000; padding: 2px;">NAMA</td>
                                    <td style="width: 50%; padding: 2px;">TTD</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 4px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 50%; border-right: 1px solid #000000; padding: 2px;">NAMA</td>
                                    <td style="width: 50%; padding: 2px;">TTD</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="height: 55px;">
                        <td style="padding: 4px; border-right: 2px solid #000000; vertical-align: middle;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 50%; border-right: 1px solid #000000; font-weight: 700;">{{ $qc->petugas_qc_nama }}</td>
                                    <td style="width: 50%; font-size: 0.7rem; color: #15803d; font-weight: 800;">[VERIFIED]</td>
                                </tr>
                            </table>
                        </td>
                        <td style="padding: 4px; vertical-align: middle;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 50%; border-right: 1px solid #000000; font-weight: 700;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td>
                                    <td style="width: 50%; font-size: 0.7rem; color: #15803d; font-weight: 800;">[APPROVED]</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
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
