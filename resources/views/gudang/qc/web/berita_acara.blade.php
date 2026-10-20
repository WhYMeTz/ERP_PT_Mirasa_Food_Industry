@extends(request('popup') || request('iframe') ? 'layouts.blank' : 'layouts.app')

@php
    $isPopup = request('popup') || request('iframe');

    \Carbon\Carbon::setLocale('id');
    $tgl = $qc->tgl_periksa ? \Carbon\Carbon::parse($qc->tgl_periksa) : now();
    $hari = $tgl->translatedFormat('l');
    $tglLengkap = $tgl->translatedFormat('d F Y');
    
    // Nomor Surat: No. XX/MFI/MM/YYYY
    $noUrut = str_pad($qc->qc_id, 2, '0', STR_PAD_LEFT);
    $bulanStr = $tgl->format('m');
    $tahunStr = $tgl->format('Y');
    $nomorSuratDefault = "{$noUrut}/MFI/{$bulanStr}/{$tahunStr}";

    // Deteksi Komoditas & Nama Barang
    $kat = strtoupper(trim($qc->kategori_barang ?? 'SINGKONG'));
    $detailFirst = $qc->details->first();
    $barangNm = strtoupper($detailFirst?->barang?->barang_nm ?? '');
    $jenisNm = strtoupper($detailFirst?->barang?->jenisBarang?->jenis_barang_nm ?? '');

    // Default Dokumen HACCP & Judul Komoditas
    if ($kat === 'MINYAK' || str_contains($barangNm, 'MINYAK')) {
        $commodityTitle = 'Bahan Minyak Goreng';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/029/VIII/2021';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
        $unit = 'KG';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } elseif ($kat === 'PLASTIK' || str_contains($barangNm, 'PLASTIK') || str_contains($jenisNm, 'PLASTIK')) {
        $commodityTitle = 'Bahan Kemas Plastik';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/030/VIII/2021';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
        $unit = 'PCS';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } elseif ($kat === 'KARTON' || str_contains($barangNm, 'KARTON') || str_contains($barangNm, 'BOX') || str_contains($jenisNm, 'KARTON')) {
        $commodityTitle = 'Bahan Kemas Karton Box';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/031/VIII/2021';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
        $satRaw = strtoupper($detailFirst?->barang?->satuanDasar?->satuan_nm ?? '');
        $unit = ($satRaw === 'BOX') ? 'BOX' : 'PCS';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } elseif ($kat === 'MSG' || str_contains($barangNm, 'MSG') || str_contains($barangNm, 'MONONATRIUM')) {
        $commodityTitle = 'Bahan Penolong MSG';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/032/VIII/2021';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
        $unit = 'KG';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } elseif ($kat === 'GARAM' || str_contains($barangNm, 'GARAM')) {
        $commodityTitle = 'Bahan Penolong Garam';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/033/VIII/2021';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
        $unit = 'KG';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } elseif ($kat === 'PERENYAH' || str_contains($barangNm, 'PERENYAH') || str_contains($barangNm, 'CITRUN')) {
        $commodityTitle = 'Bahan Penolong Perenyah';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/063/IX/2023';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->negara_produsen ?: 'INDONESIA');
        $unit = 'KG';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } elseif (in_array($kat, ['BUMBU', 'BAHAN_PENOLONG']) || str_contains($barangNm, 'BUMBU') || str_contains($jenisNm, 'PENOLONG')) {
        $commodityTitle = 'Bahan Penolong Bumbu / Perisa';
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/063/IX/2023';
        $asalLabel = 'Pabrik Produsen';
        $asalValue = $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?: 'INDONESIA');
        $unit = 'KG';
        $qcRoleDefault = 'Tim Pengecekan Bahan';
    } else {
        $tahapSuffix = $qc->parent_qc_id ? ' (Tahap II - Penggorengan)' : '';
        $commodityTitle = 'Bahan Baku Singkong' . $tahapSuffix;
        $docNoHaccp = 'MFI/HACCP-04/FRM-03/041/VIII/2021';
        $asalLabel = 'Asal singkong';
        $asalValue = $qc->lokasi_panen ?: 'WONOSOBO';
        $unit = 'KG';
        $qcRoleDefault = 'Tim Pengecekan Bahan Baku';
    }

    // Hitung Kuantitas Penolakan
    $totalReject = (float) $qc->details->sum('qty_reject');
    $totalGross = (float) $qc->details->sum('qty_timbang_gross');
    $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
    
    // Periksa juga anak pengujian 2 (jika tiket adalah induk)
    if (isset($qc->pengujian2List)) {
        $childReject = (float) $qc->pengujian2List->sum(fn($p) => $p->details->sum('qty_reject'));
        if ($totalReject == 0 && $childReject > 0) {
            $totalReject = $childReject;
        }
    }

    $isPartial = ($totalReject > 0 && ($totalNetto > 0 || $qc->status_qc === 'DITERIMA_GUDANG'));
    
    if ($totalReject > 0) {
        $jumlahText = number_format($totalReject, 0, ',', '.') . ' ' . $unit . ($isPartial ? ' (Penolakan Sebagian / Parsial)' : ' (Ditolak Total)');
    } elseif ($totalGross > 0) {
        $jumlahText = number_format($totalGross, 0, ',', '.') . ' ' . $unit . ' (Ditolak Total)';
    } else {
        $jumlahText = '';
    }

    // Nama Barang Tampilan
    $namaBarangDisplay = $qc->details->pluck('barang.barang_nm')->filter()->first() ?: ($qc->nama_jenis ?: $commodityTitle);

    // Kumpulan Alasan Cacat Mutu Otomatis Sesuai Komoditas
    $alasanItems = [];
    foreach ($qc->details as $d) {
        if ($kat === 'SINGKONG') {
            if ($d->grade_cd === 'B') $alasanItems[] = 'SINGKONG GRADE B TIDAK MEMENUHI STANDAR MUTU PABRIK';
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
            // Bahan Penolong (MSG, Garam, Perenyah, Bumbu)
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

    if (isset($qc->pengujian2List)) {
        foreach ($qc->pengujian2List as $p2) {
            foreach ($p2->details as $d2) {
                if ($d2->grade_cd === 'B') $alasanItems[] = 'PENGUJIAN II: SINGKONG GRADE B';
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

    // Nama Petugas QC default
    $petugasQcNama = $qc->petugas_qc_nama ?: 'Siti Astiyanti';
@endphp

@section('title', 'Berita Acara Penolakan: ' . $commodityTitle . ' - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/web/qc-berita-acara.css') }}?v={{ filemtime(public_path('css/gudang/qc/web/qc-berita-acara.css')) }}">
@endpush

@section('content')
<div class="ba-container">
    @if (!$isPopup)
        {{-- TOOLBAR ATAS (Hanya Muncul Jika Halaman Dibuka Mandiri Tanpa Modal) --}}
        <div class="ba-toolbar no-print">
            <div class="ba-toolbar-left">
                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="ba-btn ba-btn-secondary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali</span>
                </a>
                <span class="ba-toolbar-title">
                    📋 Berita Acara Penolakan: {{ $commodityTitle }}
                </span>
            </div>
            <div class="ba-toolbar-right">
                <button type="button" id="btnToggleBaEdit" class="ba-btn ba-btn-secondary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Mode Edit Dokumen</span>
                </button>
                <button type="button" onclick="resetBaDefaults()" class="ba-btn ba-btn-outline" title="Kembalikan data ke standar sistem">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>
                <button type="button" onclick="window.print()" class="ba-btn ba-btn-danger">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak (A4)</span>
                </button>
            </div>
        </div>

        {{-- BANNER PETUNJUK KETIKA MODE EDIT AKTIF --}}
        <div id="baEditBanner" class="ba-edit-banner no-print">
            <div>
                <strong>✏️ Mode Edit Aktif:</strong> Anda dapat mengklik teks bergaris bawah kuning di bawah untuk mengubah nomor surat, alasan penolakan, maupun nama penandatangan. Perubahan tersimpan otomatis.
            </div>
        </div>
    @endif

    {{-- LEMBAR RESMI BERITA ACARA (SAMA PERSIS DENGAN STANDAR DOKUMEN MIRASA) --}}
    <div class="ba-sheet">
        {{-- 1. KOP SURAT BERITA ACARA --}}
        <table class="ba-kop-table">
            <tr>
                {{-- LOGO CAP PAYUNG (ROWSPAN 2) --}}
                <td rowspan="2" class="ba-kop-logo-td">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Cap Payung" class="ba-kop-logo-img">
                    <div class="ba-kop-slogan">ENAK-GURIH-LEZAT</div>
                </td>
                
                {{-- PT. MIRASA FOOD INDUSTRY (COLSPAN 2 DI ATAS) --}}
                <td colspan="2" class="ba-kop-company-td">
                    <div class="ba-company-name">PT. MIRASA FOOD INDUSTRY</div>
                </td>
            </tr>
            <tr>
                {{-- FORM BERITA ACARA PENOLAKAN --}}
                <td class="ba-kop-form-td">
                    <div class="ba-form-name">
                        Form Berita Acara Penolakan<br>
                        <span>{{ $commodityTitle }}</span>
                    </div>
                </td>

                {{-- KOTAK DOKUMEN HACCP (4 BARIS) --}}
                <td class="ba-kop-docinfo-td">
                    <table class="ba-docinfo-table">
                        <tr>
                            <td>
                                No. Dokumen : <span class="ba-editable" data-field="doc_no">{{ $docNoHaccp }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Revisi : <span class="ba-editable" data-field="revisi">0</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Tanggal Terbit : <span class="ba-editable" data-field="tgl_terbit">19-08-2021</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Halaman: <span class="ba-editable" data-field="halaman"></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- 2. JUDUL BERITA ACARA & NOMOR SURAT --}}
        <div class="ba-title-box">
            <div class="ba-title-text">
                BERITA ACARA KUALITAS {{ strtoupper($commodityTitle) }}
            </div>
            <div class="ba-subtitle-no">
                No. <span class="ba-editable" data-field="no_surat">{{ $nomorSuratDefault }}</span>
            </div>
        </div>

        {{-- 3. PARAGRAF PEMBUKA --}}
        <p class="ba-p-opening">
            Pada hari <strong>{{ $hari }}</strong>, <strong>{{ $tglLengkap }}</strong> telah dilakukan pengecekan {{ strtolower($commodityTitle) }} dengan rincian sebagai berikut:
        </p>

        {{-- 4. TABEL RINCIAN LOGISTIK / BARANG --}}
        <table class="ba-details-table">
            <tr>
                <td class="ba-details-label">Nama supplier</td>
                <td class="ba-details-colon">:</td>
                <td class="ba-details-val">
                    <span class="ba-editable" data-field="supplier_nm">{{ strtoupper($qc->supplier?->supplier_nm ?? '-') }}</span>
                </td>
            </tr>
            <tr>
                <td class="ba-details-label">{{ $asalLabel }}</td>
                <td class="ba-details-colon">:</td>
                <td class="ba-details-val">
                    <span class="ba-editable" data-field="asal_value">{{ strtoupper($asalValue) }}</span>
                </td>
            </tr>
            @if ($kat !== 'SINGKONG')
                <tr>
                    <td class="ba-details-label">Nama Barang / Jenis</td>
                    <td class="ba-details-colon">:</td>
                    <td class="ba-details-val">
                        <span class="ba-editable" data-field="barang_nm">{{ strtoupper($namaBarangDisplay) }}</span>
                    </td>
                </tr>
            @endif
            <tr>
                <td class="ba-details-label">Jumlah</td>
                <td class="ba-details-colon">:</td>
                <td class="ba-details-val">
                    <span class="ba-editable" data-field="jumlah_reject">{{ $jumlahText }}</span>
                </td>
            </tr>
            <tr>
                <td class="ba-details-label">No Plat</td>
                <td class="ba-details-colon">:</td>
                <td class="ba-details-val">
                    <span class="ba-editable" data-field="plat_truk">{{ strtoupper($qc->plat_nomor_truk ?: '-') }}</span>
                </td>
            </tr>
        </table>

        {{-- 5. PARAGRAF KETERANGAN ALASAN CACAT MUTU --}}
        <p class="ba-p-reason">
            Menerangkan bahwa pada {{ $tglLengkap }} {{ strtolower($commodityTitle) }} dari supplier di atas, setelah dilakukan sampling ternyata memiliki kualitas yang tidak sesuai dengan standar yang telah ditetapkan &rarr; <strong style="font-style: italic;"><span class="ba-editable" data-field="alasan_cacat">{{ strtoupper($alasanDefault) }}</span>.</strong> Demikian berita acara kualitas {{ strtolower($commodityTitle) }} <strong>PT. Mirasa Food Industry</strong>, keterangan tersebut dibuat apa adanya.
        </p>

        <p class="ba-p-thanks">
            Terimakasih.
        </p>

        {{-- 6. TANGGAL SURAT (TENGAH) --}}
        <div class="ba-date-center">
            <span class="ba-editable" data-field="tgl_surat">Magelang, {{ $tglLengkap }}</span>
        </div>

        {{-- 7. TANDA TANGAN (4 PIHAK RESMI) --}}
        <div class="ba-signatures-grid">
            {{-- TIM PENGECEKAN QC --}}
            <div class="ba-sig-col">
                <div class="ba-sig-role">
                    <span class="ba-editable" data-field="role_qc">{{ $qcRoleDefault }}</span>
                </div>
                <div class="ba-sig-space"></div>
                <div class="ba-sig-name">
                    <span class="ba-editable" data-field="nama_qc">{{ $petugasQcNama }}</span>
                </div>
            </div>

            {{-- DIREKTUR --}}
            <div class="ba-sig-col">
                <div class="ba-sig-role">
                    <span class="ba-editable" data-field="role_direktur">Direktur</span>
                </div>
                <div class="ba-sig-space"></div>
                <div class="ba-sig-name">
                    <span class="ba-editable" data-field="nama_direktur">Habieb Soleh Y.</span>
                </div>
            </div>

            {{-- KEPALA PRODUKSI --}}
            <div class="ba-sig-col">
                <div class="ba-sig-role">
                    <span class="ba-editable" data-field="role_produksi">Kepala Produksi</span>
                </div>
                <div class="ba-sig-space"></div>
                <div class="ba-sig-name">
                    <span class="ba-editable" data-field="nama_produksi">Raden Rahmat</span>
                </div>
            </div>

            {{-- KABAG PEMBELIAN --}}
            <div class="ba-sig-col">
                <div class="ba-sig-role">
                    <span class="ba-editable" data-field="role_pembelian">Kabag Pembelian</span>
                </div>
                <div class="ba-sig-space"></div>
                <div class="ba-sig-name">
                    <span class="ba-editable" data-field="nama_pembelian">Mei Nur Rahmawati</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.baConfig = {
        qcId: '{{ $qc->qc_id }}',
        isPopup: {{ $isPopup ? 'true' : 'false' }}
    };
</script>
<script src="{{ asset('js/gudang/qc/web/qc-berita-acara.js') }}?v={{ filemtime(public_path('js/gudang/qc/web/qc-berita-acara.js')) }}"></script>
@endpush
