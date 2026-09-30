@extends('layouts.app')

@section('title', 'Berita Acara Penolakan Bahan Baku Singkong - PT Mirasa')

@section('content')
@php
    \Carbon\Carbon::setLocale('id');
    $tgl = $qc->tgl_periksa ? \Carbon\Carbon::parse($qc->tgl_periksa) : now();
    $hari = $tgl->translatedFormat('l');
    $tglLengkap = $tgl->translatedFormat('d F Y');
    
    // Nomor Surat: No. XX/MFI/MM/YYYY
    $noUrut = str_pad($qc->qc_id, 2, '0', STR_PAD_LEFT);
    $bulanRomawi = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => '09', 10 => '10', 11 => '11', 12 => '12'
    ];
    $bulanStr = $tgl->format('m');
    $tahunStr = $tgl->format('Y');
    $nomorSuratDefault = "{$noUrut}/MFI/{$bulanStr}/{$tahunStr}";

    // Jumlah total berat yang ditolak atau gross
    $totalReject = $qc->details->sum('qty_reject');
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $jumlahText = $totalReject > 0 ? number_format($totalReject, 0, ',', '.') . ' KG' : ($totalGross > 0 ? number_format($totalGross, 0, ',', '.') . ' KG' : '-');

    // Alasan ketidaksesuaian mutu
    $alasanItems = [];
    foreach ($qc->details as $d) {
        if ($d->fryer_rasa === 'PAHIT') $alasanItems[] = 'SINGKONG MENTAH / GORENG PAHIT';
        if ($d->fryer_tekstur === 'ALOT') $alasanItems[] = 'TEKSTUR ALOT / LIAT';
        if ($d->fryer_penampakan === 'OILSOAKED') $alasanItems[] = 'HASIL GORENG MBELING & OILSOAKED';
        if ((float)$d->defect_gambos_persen > 0) $alasanItems[] = 'GAMBOS / KOPONG';
        if ($d->kondisi_busuk) $alasanItems[] = 'BUSUK';
        if ($d->kondisi_lembek) $alasanItems[] = 'LENGKAT & LEMBEK';
    }
    $alasanDefault = !empty($alasanItems) 
        ? implode(', ', array_unique($alasanItems))
        : 'HASIL GORENG MBELING, GAMBOS, LENGKAT DAN SINGKONG MENTAH PAHIT';
@endphp

<div style="max-width: 850px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- TOP ACTION / TOOLBAR (Hidden when printing) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; background: #ffffff; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Kembali ke Lembar Uji QC
            </a>
            <span style="font-size: 0.875rem; color: #64748b;">|</span>
            <span style="font-size: 0.9rem; font-weight: 700; color: #0f172a;">Berita Acara Penolakan Singkong</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 700; background: #dc2626; border-color: #dc2626;">
                🖨️ Cetak Berita Acara (A4)
            </button>
        </div>
    </div>

    {{-- OFFICIAL DOCUMENT SHEET --}}
    <div class="card ba-sheet" style="background: #ffffff; border: 1.5px solid #000000; padding: 2.5rem 3rem; font-family: 'Times New Roman', Times, serif; color: #000000; line-height: 1.5; font-size: 11pt;">
        
        {{-- KOP SURAT BERITA ACARA --}}
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-bottom: 2rem;">
            <tr>
                {{-- LOGO CAP PAYUNG --}}
                <td style="width: 130px; text-align: center; vertical-align: middle; padding: 0.6rem 0.5rem; border: 1.5px solid #000000;">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Cap Payung" style="width: 72px; height: 72px; object-fit: contain; display: block; margin: 0 auto;">
                    <div style="font-family: Arial, sans-serif; font-size: 8pt; font-weight: 800; color: #dc2626; margin-top: 4px; letter-spacing: 0.5px;">
                        ENAK-GURIH-LEZAT
                    </div>
                </td>
                
                {{-- NAMA PERUSAHAAN & NAMA FORM --}}
                <td style="text-align: center; vertical-align: middle; padding: 0.5rem 1rem; border: 1.5px solid #000000;">
                    <div style="font-family: Arial, sans-serif; font-size: 14pt; font-weight: 900; letter-spacing: 0.5px; color: #000000;">
                        PT. MIRASA FOOD INDUSTRY
                    </div>
                    <div style="font-family: Arial, sans-serif; font-size: 13pt; font-weight: 800; margin-top: 0.5rem; color: #000000;">
                        Form Berita Acara Penolakan<br>Bahan Baku Singkong
                    </div>
                </td>

                {{-- KOTAK DOKUMEN HACCP --}}
                <td style="width: 250px; vertical-align: top; padding: 0; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt;">
                    <table style="width: 100%; border-collapse: collapse; border: none;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; border: none; vertical-align: top;">
                                <strong>No. Dokumen</strong> : MFI/HACCP-04/FRM-03/041/VIII/2021
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; border: none;">
                                <strong>Revisi</strong> : 0
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; border: none;">
                                <strong>Tanggal Terbit</strong> : 19-08-2021
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 6px; border: none;">
                                <strong>Halaman</strong> :
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- JUDUL BERITA ACARA --}}
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <div style="font-size: 13pt; font-weight: 800; text-decoration: underline; letter-spacing: 0.5px;">
                BERITA ACARA KUALITAS BAHAN BAKU SINGKONG
            </div>
            <div style="font-size: 11pt; margin-top: 0.2rem;" contenteditable="true" title="Klik untuk mengubah nomor surat">
                No. {{ $nomorSuratDefault }}
            </div>
        </div>

        {{-- PARAGRAF PEMBUKA --}}
        <p style="margin-bottom: 1.25rem; text-align: justify; text-indent: 2.5rem;">
            Pada hari <strong>{{ $hari }}</strong>, <strong>{{ $tglLengkap }}</strong> telah dilakukan pengecekan bahan baku singkong dengan rincian sebagai berikut:
        </p>

        {{-- TABEL RINCIAN --}}
        <table style="margin-left: 2.5rem; margin-bottom: 1.5rem; border-collapse: collapse; border: none; font-size: 11pt; width: 80%;">
            <tr>
                <td style="width: 140px; padding: 3px 0; border: none;">Nama supplier</td>
                <td style="width: 15px; text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($qc->supplier?->supplier_nm ?? 'PAK ANGGRI') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; border: none;">Asal singkong</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($qc->lokasi_panen ?: 'WONOSOBO') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; border: none;">Jumlah</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ $jumlahText }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; border: none;">No Plat</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($qc->plat_nomor_truk ?: 'R 9659 BT') }}
                </td>
            </tr>
        </table>

        {{-- PARAGRAF PENERANGAN & ALASAN CACAT MUTU --}}
        <p style="margin-bottom: 1.5rem; text-align: justify; text-indent: 2.5rem; line-height: 1.6;">
            Menerangkan bahwa pada {{ $tglLengkap }} bahan baku singkong dari supplier di atas, setelah dilakukan sampling ternyata memiliki kualitas yang tidak sesuai dengan standar yang telah ditetapkan &rarr; <strong style="font-style: italic;" contenteditable="true">{{ strtoupper($alasanDefault) }}.</strong> Demikian berita acara kualitas bahan baku singkong <strong>PT. Mirasa Food Industry</strong>, keterangan tersebut dibuat apa adanya.
        </p>

        <p style="margin-bottom: 2rem;">
            Terimakasih.
        </p>

        {{-- TANGGAL SURAT --}}
        <div style="text-align: center; margin-bottom: 2.5rem; margin-left: 100px;">
            Magelang, {{ $tglLengkap }}
        </div>

        {{-- TANDA TANGAN (4 PIHAK RESMI) --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3.5rem 2rem; text-align: center; font-size: 11pt; padding: 0 1rem;">
            {{-- BARIS 1 --}}
            <div>
                <div style="font-weight: 700; margin-bottom: 5.5rem;">Tim Pengecekan Bahan Baku</div>
                <div style="font-weight: 700; text-decoration: underline;" contenteditable="true">
                    {{ $qc->petugas_qc_nama ?: 'Siti Astiyanti' }}
                </div>
            </div>

            <div>
                <div style="font-weight: 700; margin-bottom: 5.5rem;">Direktur</div>
                <div style="font-weight: 700; text-decoration: underline;" contenteditable="true">
                    Habieb Soleh Y.
                </div>
            </div>

            {{-- BARIS 2 --}}
            <div>
                <div style="font-weight: 700; margin-bottom: 5.5rem;">Kepala Produksi</div>
                <div style="font-weight: 700; text-decoration: underline;" contenteditable="true">
                    Raden Rahmat
                </div>
            </div>

            <div>
                <div style="font-weight: 700; margin-bottom: 5.5rem;">Kabag Pembelian</div>
                <div style="font-weight: 700; text-decoration: underline;" contenteditable="true">
                    Mei Nur Rahmawati
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 15mm 15mm;
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
        .ba-sheet {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        table {
            page-break-inside: avoid;
        }
    }
</style>
@endsection
