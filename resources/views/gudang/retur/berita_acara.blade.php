@extends('layouts.app')

@section('title', 'Berita Acara Penolakan Bahan Baku Singkong - Retur #' . $retur->retur_no)

@section('content')
@php
    \Carbon\Carbon::setLocale('id');
    $tgl = $retur->retur_tgl ? \Carbon\Carbon::parse($retur->retur_tgl) : now();
    $hari = $tgl->translatedFormat('l');
    $tglLengkap = $tgl->translatedFormat('d F Y');
    
    // Nomor Surat: No. XX/MFI/MM/YYYY
    $noUrut = str_pad($retur->retur_id, 2, '0', STR_PAD_LEFT);
    $bulanStr = $tgl->format('m');
    $tahunStr = $tgl->format('Y');
    $nomorSuratDefault = "{$noUrut}/MFI/{$bulanStr}/{$tahunStr}";

    // Jumlah total berat yang diretur
    $totalQty = $retur->details->sum('retur_qty');
    $jumlahText = $totalQty > 0 ? number_format($totalQty, 0, ',', '.') . ' KG' : '-';

    // Cari asal singkong dan no plat jika terkait tiket terima atau PO
    $asalSingkong = 'WONOSOBO';
    $noPlat = '-';
    if ($retur->terima) {
        $noPlat = $retur->terima->suratjalan_no ?: '-';
        if ($retur->terima->qcInbound) {
            $asalSingkong = $retur->terima->qcInbound->lokasi_panen ?: $asalSingkong;
            $noPlat = $retur->terima->qcInbound->plat_nomor_truk ?: $noPlat;
        }
    }

    // Alasan ketidaksesuaian mutu
    $alasanReject = $retur->alasan_txt ?: $retur->details->pluck('alasan_reject')->filter()->implode(', ');
    if (empty($alasanReject)) {
        $alasanReject = 'HASIL GORENG MBELING, GAMBOS, LENGKAT DAN SINGKONG MENTAH PAHIT';
    }
@endphp

<div style="max-width: 850px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- TOP ACTION / TOOLBAR (Hidden when printing) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; background: #ffffff; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('gudang.retur.show', $retur->retur_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Kembali ke Surat Jalan Retur
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
                    {{ strtoupper($retur->supplier?->supplier_nm ?? 'PAK ANGGRI') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; border: none;">Asal singkong</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($asalSingkong) }}
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
                    {{ strtoupper($noPlat) }}
                </td>
            </tr>
        </table>

        {{-- PARAGRAF PENERANGAN & ALASAN CACAT MUTU --}}
        <p style="margin-bottom: 1.5rem; text-align: justify; text-indent: 2.5rem; line-height: 1.6;">
            Menerangkan bahwa pada {{ $tglLengkap }} bahan baku singkong dari supplier di atas, setelah dilakukan sampling ternyata memiliki kualitas yang tidak sesuai dengan standar yang telah ditetapkan &rarr; <strong style="font-style: italic;" contenteditable="true">{{ strtoupper($alasanReject) }}.</strong> Demikian berita acara kualitas bahan baku singkong <strong>PT. Mirasa Food Industry</strong>, keterangan tersebut dibuat apa adanya.
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
                    Siti Astiyanti
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
