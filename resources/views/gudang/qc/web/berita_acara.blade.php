@extends('layouts.app')

@php
    \Carbon\Carbon::setLocale('id');
    $tgl = $qc->tgl_periksa ? \Carbon\Carbon::parse($qc->tgl_periksa) : now();
    $hari = $tgl->translatedFormat('l');
    $tglLengkap = $tgl->translatedFormat('d F Y');
    
    // Nomor Surat: No. XX/MFI/MM/YYYY
    $noUrut = str_pad($qc->qc_id, 2, '0', STR_PAD_LEFT);
    $bulanStr = $tgl->format('m');
    $tahunStr = $tgl->format('Y');
    $nomorSuratDefault = "{$noUrut}/MFI/{$bulanStr}/{$tahunStr}";

    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');

    // Unit & Quantity
    $unit = in_array($kat, ['PLASTIK', 'KARTON']) ? 'PCS' : 'KG';
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    
    // Jika tiket ini adalah induk dan punya pengujian 2 yang ada reject
    $childReject = (float) $qc->pengujian2List->sum(fn($p) => $p->details->sum('qty_reject'));
    if ($totalReject == 0 && $childReject > 0) {
        $totalReject = $childReject;
    }

    $isPartial = ($totalReject > 0 && ($totalNetto > 0 || $qc->status_qc === 'DITERIMA_GUDANG'));
    
    if ($totalReject > 0) {
        $jumlahText = number_format($totalReject, 0, ',', '.') . ' ' . $unit . ($isPartial ? ' (Penolakan Sebagian / Parsial)' : ' (Ditolak Total)');
    } elseif ($totalGross > 0) {
        $jumlahText = number_format($totalGross, 0, ',', '.') . ' ' . $unit . ' (Ditolak Total)';
    } else {
        $jumlahText = '-';
    }

    if ($kat === 'MINYAK') {
        $commodityTitle = 'Bahan Minyak Goreng';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
    } elseif ($kat === 'PLASTIK') {
        $commodityTitle = 'Bahan Kemas Plastik';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
    } elseif ($kat === 'KARTON') {
        $commodityTitle = 'Bahan Kemas Karton Box';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
    } elseif ($kat === 'MSG') {
        $commodityTitle = 'Bahan Penolong MSG';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
    } elseif ($kat === 'GARAM') {
        $commodityTitle = 'Bahan Penolong Garam';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
    } elseif ($kat === 'PERENYAH') {
        $commodityTitle = 'Bahan Penolong Perenyah';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
    } else {
        $tahapSuffix = $qc->parent_qc_id ? ' (Tahap II - Lantai Produksi)' : '';
        $commodityTitle = 'Bahan Baku Singkong' . $tahapSuffix;
        $asalLabel = 'Asal singkong';
        $asalValue = $qc->lokasi_panen ?: 'WONOSOBO';
    }

    // Alasan ketidaksesuaian mutu otomatis sesuai komoditas
    $alasanItems = [];
    foreach ($qc->details as $d) {
        if ($kat === 'SINGKONG') {
            if ($d->grade_cd === 'B') $alasanItems[] = 'SINGKONG GRADE B TIDAK MEMENUHI STANDAR MUTU PRODUKSI';
            if ($d->fryer_rasa === 'PAHIT') $alasanItems[] = 'SINGKONG MENTAH / GORENG PAHIT';
            if ($d->fryer_tekstur === 'ALOT') $alasanItems[] = 'TEKSTUR ALOT / LIAT';
            if ($d->fryer_penampakan === 'OILSOAKED') $alasanItems[] = 'HASIL GORENG MBELING & OILSOAKED';
            if ((float)$d->defect_gambos_persen > 0) $alasanItems[] = 'GAMBOS / KOPONG';
            if ($d->kondisi_busuk) $alasanItems[] = 'BUSUK';
            if ($d->kondisi_lembek) $alasanItems[] = 'LENGKAT & LEMBEK';
            if (!empty($d->kondisi_fisik) && !in_array($d->kondisi_fisik, ['NORMAL', 'OK'])) {
                $alasanItems[] = str_replace('_', ' ', $d->kondisi_fisik);
            }
        } elseif ($kat === 'MINYAK') {
            if ($d->kondisi_tangki_jerigen === 'TIDAK_STANDARD') $alasanItems[] = 'KONDISI WADAH / TANGKI TIDAK STANDARD';
            if (!$d->minyak_jernih_st) $alasanItems[] = 'MINYAK KERUH / TIDAK JERNIH';
            if (!$d->tangki_bersih_st) $alasanItems[] = 'TANGKI BAGIAN DALAM KOTOR';
            if ($d->status_raw_material === 'TDK_STD') $alasanItems[] = 'KUALITAS MINYAK TIDAK STANDARD';
        } elseif ($kat === 'PLASTIK') {
            if ($d->kemasan_sobek) $alasanItems[] = 'KEMASAN SOBEK / RUSAK';
            if ($d->kemasan_kotor) $alasanItems[] = 'KEMASAN KOTOR';
            if ($d->kemasan_apek) $alasanItems[] = 'KEMASAN APEK';
            if ($d->kemasan_basah) $alasanItems[] = 'KEMASAN BASAH';
            if ($d->status_raw_material === 'TDK_STD') $alasanItems[] = 'KETEBALAN / KEUTUHAN TIDAK STANDAR';
        } elseif ($kat === 'KARTON') {
            if ($d->kemasan_sobek) $alasanItems[] = 'KARTON SOBEK / RUSAK';
            if ($d->kemasan_jamur) $alasanItems[] = 'KARTON BERJAMUR';
            if ($d->kemasan_basah) $alasanItems[] = 'KARTON BASAH';
            if ($d->kemasan_berminyak) $alasanItems[] = 'KARTON BERMINYAK';
            if ($d->status_raw_material === 'TDK_STD') $alasanItems[] = 'DIMENSI / SPESIFIKASI TIDAK SESUAI STANDAR';
        } else {
            // Bahan Penolong (MSG, Garam, Perenyah)
            if ($d->isi_basah) $alasanItems[] = 'KONDISI FISIK BAHAN BASAH';
            if ($d->isi_gumpal) $alasanItems[] = 'KONDISI BAHAN MENGGUMPAL';
            if ($d->isi_berminyak) $alasanItems[] = 'KONDISI BAHAN BERMINYAK';
            if ($d->kemasan_sobek) $alasanItems[] = 'KEMASAN ZAK SOBEK';
            if ($d->kemasan_kotor) $alasanItems[] = 'KEMASAN KOTOR';
            if ($d->kemasan_jamur) $alasanItems[] = 'KEMASAN BERJAMUR';
            if ($d->status_raw_material === 'TDK_STD') $alasanItems[] = 'KUALITAS BAHAN TIDAK STANDARD';
        }

        if (!empty($d->catatan_dtl)) {
            $alasanItems[] = $d->catatan_dtl;
        }
    }

    // Periksa juga jika ada anak pengujian 2 yang ada catatan penolakan
    if (isset($qc->pengujian2List)) {
        foreach ($qc->pengujian2List as $p2) {
            foreach ($p2->details as $d2) {
                if ($d2->grade_cd === 'B') $alasanItems[] = 'PENGUJIAN II: SINGKONG GRADE B TIDAK MEMENUHI STANDAR PABRIK';
                if ($d2->fryer_rasa === 'PAHIT') $alasanItems[] = 'PENGUJIAN II: SINGKONG PAHIT';
                if (!empty($d2->catatan_dtl)) $alasanItems[] = $d2->catatan_dtl;
            }
        }
    }

    if (!empty($qc->catatan_umum)) {
        $alasanItems[] = $qc->catatan_umum;
    }

    if (!empty($alasanItems)) {
        $alasanDefault = implode(', ', array_unique($alasanItems));
    } else {
        if ($kat === 'SINGKONG') {
            $alasanDefault = 'HASIL GORENG MBELING, GAMBOS, LENGKAT DAN SINGKONG MENTAH PAHIT';
        } else {
            $alasanDefault = 'KUALITAS FISIK / KEMASAN TIDAK MEMENUHI STANDAR KEBETERIMAAN PT MIRASA';
        }
    }
@endphp

@section('title', 'Berita Acara Penolakan ' . $commodityTitle . ' - PT Mirasa')

@section('content')
<div style="max-width: 850px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- TOP ACTION / TOOLBAR (Hidden when printing) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; background: #ffffff; padding: 1rem 1.25rem; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Kembali ke Lembar Uji QC
            </a>
            @if ($qc->terima)
                <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                    Lihat Dokumen GRN &rarr;
                </a>
            @endif
            <span style="font-size: 0.875rem; color: #64748b;">|</span>
            <span style="font-size: 0.9rem; font-weight: 700; color: #0f172a;">Berita Acara Penolakan: {{ $commodityTitle }}</span>
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
                        Form Berita Acara Penolakan<br>{{ $commodityTitle }}
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
                                <strong>Halaman</strong> : 1 dari 1
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- JUDUL BERITA ACARA --}}
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <div style="font-size: 13pt; font-weight: 800; text-decoration: underline; letter-spacing: 0.5px;">
                BERITA ACARA KUALITAS {{ strtoupper($commodityTitle) }}
            </div>
            <div style="font-size: 11pt; margin-top: 0.2rem;" contenteditable="true" title="Klik untuk mengubah nomor surat">
                No. {{ $nomorSuratDefault }}
            </div>
        </div>

        {{-- PARAGRAF PEMBUKA --}}
        <p style="margin-bottom: 1.25rem; text-align: justify; text-indent: 2.5rem;">
            Pada hari <strong>{{ $hari }}</strong>, <strong>{{ $tglLengkap }}</strong> telah dilakukan pengecekan {{ strtolower($commodityTitle) }} dengan rincian sebagai berikut:
        </p>

        {{-- TABEL RINCIAN --}}
        <table style="margin-left: 2.5rem; margin-bottom: 1.5rem; border-collapse: collapse; border: none; font-size: 11pt; width: 85%;">
            <tr>
                <td style="width: 150px; padding: 3px 0; border: none;">Nama supplier</td>
                <td style="width: 15px; text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($qc->supplier?->supplier_nm ?? '-') }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; border: none;">{{ $asalLabel }}</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($asalValue) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 3px 0; border: none;">Nama Barang / Jenis</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($qc->details->pluck('barang.barang_nm')->filter()->first() ?: ($qc->nama_jenis ?: $kat)) }}
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
                <td style="padding: 3px 0; border: none;">No Plat / Truk</td>
                <td style="text-align: center; border: none;">:</td>
                <td style="padding: 3px 0; border: none; font-weight: 700;" contenteditable="true">
                    {{ strtoupper($qc->plat_nomor_truk ?: '-') }}
                </td>
            </tr>
        </table>

        {{-- PARAGRAF PENERANGAN & ALASAN CACAT MUTU --}}
        <p style="margin-bottom: 1.5rem; text-align: justify; text-indent: 2.5rem; line-height: 1.6;">
            Menerangkan bahwa pada {{ $tglLengkap }} {{ strtolower($commodityTitle) }} dari supplier di atas, setelah dilakukan sampling ternyata memiliki kualitas yang tidak sesuai dengan standar yang telah ditetapkan &rarr; <strong style="font-style: italic;" contenteditable="true">{{ strtoupper($alasanDefault) }}.</strong> Demikian berita acara penolakan {{ strtolower($commodityTitle) }} <strong>PT. Mirasa Food Industry</strong>, keterangan tersebut dibuat apa adanya.
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
                <div style="font-weight: 700; margin-bottom: 5.5rem;">Tim Pengecekan Bahan</div>
                <div style="font-weight: 700; text-decoration: underline;" contenteditable="true">
                    {{ $qc->petugas_qc_nama ?: 'Petugas QC' }}
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
