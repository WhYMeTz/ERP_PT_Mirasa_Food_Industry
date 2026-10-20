{{-- ========================================================================= --}}
{{-- DOKUMEN CHECKLIST PEMERIKSAAN KEDATANGAN KARTON                           --}}
{{-- NO. DOKUMEN: MFI/HACCP-04/FRM-03/031/VIII/2021                            --}}
{{-- PERSIS FORMAT EXCEL PT MIRASA FOOD INDUSTRY                               --}}
{{-- ========================================================================= --}}
@php
    $isEdit = $isEdit ?? false;
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? ($firstDtl?->qcdtl_id ?? 0);
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $totalReject = $qc->details->sum('qty_reject');
@endphp

<div class="excel-doc-sheet" style="width: 100%; max-width: 860px; margin: 0 auto 1.5rem; background: #ffffff; border: 2px solid #000000; padding: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); font-family: Arial, sans-serif; font-size: 0.76rem; color: #000000;">
    
    {{-- 1. KOP SURAT RESMI PT MIRASA --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.35rem;">
        {{-- BARIS ATAS: NAMA PERUSAHAAN DI ATAS --}}
        <div style="text-align: center; font-size: 1.25rem; font-weight: 900; color: #000000; letter-spacing: 0.05em; padding: 4px; border-bottom: 2px solid #000000;">
            PT. MIRASA FOOD INDUSTRY
        </div>

        {{-- BARIS UTAMA: LOGO DENGAN TULISAN ENAK GURIH LEZAT | JUDUL DOKUMEN | NO DOKUMEN --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 100px; text-align: center; vertical-align: middle; padding: 4px; border-right: 2px solid #000000;">
                    <img src="{{ asset('images/logo.png') }}" alt="Cap Payung" class="doc-header-logo" style="width: 50px; height: 50px; object-fit: contain;">
                    <div style="margin-top: 3px; font-weight: 900; font-size: 0.58rem; color: #cc0000; font-style: italic; letter-spacing: 0.02em; white-space: nowrap; line-height: 1.2; font-family: 'Arial Black', Impact, Arial, sans-serif; text-align: center;">
                        ENAK &bull; GURIH &bull; LEZAT
                    </div>
                </td>
                <td style="vertical-align: middle; text-align: center; padding: 6px 10px;">
                    <div style="font-size: 1.15rem; font-weight: 900; color: #000000; line-height: 1.3;">
                        Cheklist Pemeriksaan Kedatangan Karton
                    </div>
                </td>
                <td style="width: 255px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; font-weight: 700; width: 110px; border-right: 1px solid #000000;">No. Dokumen :</td>
                            <td style="padding: 4px 6px; font-weight: 800;">MFI/HACCP-04/FRM-03/031/VIII/2021</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi :</td>
                            <td style="padding: 4px 6px;">1</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit :</td>
                            <td style="padding: 4px 6px;">11-09-2023</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Halaman :</td>
                            <td style="padding: 4px 6px;">1 dari 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- 2. TABEL IDENTITAS KEDATANGAN KARTON (PERSIS FORMAT RESMI EXCEL PT MIRASA) --}}
    @php
        $jumlahPabrikVal = $qc->jumlah_di_pabrik !== null ? (float) $qc->jumlah_di_pabrik : $totalGross;
        $jumlahSjVal = $qc->jumlah_surat_jalan !== null ? (float) $qc->jumlah_surat_jalan : null;
    @endphp
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem; table-layout: fixed;">
            <colgroup>
                <col style="width: 27%;">
                <col style="width: 14%;">
                <col style="width: 13%;">
                <col style="width: 13%;">
                <col style="width: 13%;">
                <col style="width: 20%;">
            </colgroup>
            {{-- BARIS 1: NAMA BARANG DI ATAS --}}
            <tr style="border-bottom: 1px solid #000000;">
                {{-- SISI KIRI: JUDUL BESAR LAPORAN KEDATANGAN KARTON (SPAN BARIS 1 s/d 5) --}}
                <td rowspan="5" style="vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #334155; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                    <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                    <div style="font-size: 1.55rem; font-weight: 900; margin-top: 3px; color: #000000; letter-spacing: 0.04em;">
                        KARTON
                    </div>
                </td>
                {{-- NAMA BARANG --}}
                <td style="padding: 5px 8px; font-weight: 700; border-right: 1px solid #000000;">Nama Barang</td>
                <td colspan="4" style="padding: 5px 8px;">
                    @if($isEdit)
                        <input type="text" name="items[{{ $qcdtlId }}][nama_bahan]" value="{{ old('items.'.$qcdtlId.'.nama_bahan', $firstDetail?->barang?->barang_nm ?? $qc->nama_jenis) }}" class="excel-cell-input" placeholder="Karton Box Mirasa">
                    @else
                        : {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Karton Box Mirasa') }}
                    @endif
                </td>
            </tr>

            {{-- BARIS 2: HEADER 4 KOLOM IDENTITAS + NAMA JENIS DI SISI KANAN --}}
            <tr style="border-bottom: 1px solid #000000; background: #ffffff; text-align: center; font-weight: 700;">
                <td style="padding: 4px 6px; border-right: 1px solid #000000;">Nama Produsen</td>
                <td style="padding: 4px 6px; border-right: 1px solid #000000;">Negara Produsen</td>
                <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah Surat Jalan</td>
                <td style="padding: 4px 6px; border-right: 2px solid #000000;">Jumlah di Pabrik</td>
                <td style="padding: 4px 6px; font-weight: 900; background: #ffffff;">NAMA JENIS</td>
            </tr>

            {{-- BARIS 3: VALUE 4 KOLOM IDENTITAS + KOTAK NAMA JENIS VERTIKAL (ROWSPAN 4) --}}
            <tr style="border-bottom: 1px solid #000000; text-align: center;">
                <td style="padding: 5px 6px; border-right: 1px solid #000000; height: 32px; word-break: break-word;">
                    @if($isEdit)
                        <input type="text" name="nama_produsen" value="{{ old('nama_produsen', $qc->nama_produsen ?? $qc->supplier?->supplier_nm) }}" class="excel-cell-input text-center">
                    @else
                        {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }}
                    @endif
                </td>
                <td style="padding: 5px 6px; border-right: 1px solid #000000; word-break: break-word;">
                    @if($isEdit)
                        <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?? 'Indonesia') }}" class="excel-cell-input text-center">
                    @else
                        {{ $qc->negara_produsen ?: 'Indonesia' }}
                    @endif
                </td>
                <td style="padding: 5px 6px; border-right: 1px solid #000000; font-weight: 700;">
                    @if($isEdit)
                        <input type="number" step="any" name="jumlah_surat_jalan" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan) }}" class="excel-cell-input text-center font-bold">
                    @else
                        {{ $jumlahSjVal !== null ? number_format($jumlahSjVal, 0, ',', '.') : '-' }}
                    @endif
                </td>
                <td style="padding: 5px 6px; border-right: 2px solid #000000; font-weight: 800;">
                    @if($isEdit)
                        <input type="number" step="any" name="jumlah_di_pabrik" value="{{ old('jumlah_di_pabrik', $jumlahPabrikVal) }}" class="excel-cell-input text-center font-bold">
                    @else
                        <strong>{{ $jumlahPabrikVal > 0 ? number_format($jumlahPabrikVal, 0, ',', '.') : '-' }}</strong>
                    @endif
                </td>
                {{-- KOTAK NAMA JENIS SPAN 4 BARIS SESUAI CONTOH EXCEL RESMI --}}
                <td rowspan="4" style="padding: 6px 4px; vertical-align: middle; text-align: center; border-left: 1px solid #000000; background: #ffffff; word-break: break-word; overflow-wrap: anywhere; hyphens: auto;">
                    @php
                        $namaJenisText = trim($qc->nama_jenis ?? '');
                        $lenNamaJenis = strlen($namaJenisText);
                        $fontSizeJenis = '0.85rem';
                        if ($lenNamaJenis > 45) {
                            $fontSizeJenis = '0.68rem';
                        } elseif ($lenNamaJenis > 28) {
                            $fontSizeJenis = '0.75rem';
                        }
                    @endphp
                    @if($isEdit)
                        <textarea name="nama_jenis" class="excel-cell-input" style="width: 100%; height: 95px; border: 1px solid #cbd5e1; padding: 4px; font-weight: 800; font-size: 0.78rem; text-align: center; resize: vertical;" placeholder="Karton Box">{{ old('nama_jenis', $qc->nama_jenis) }}</textarea>
                    @else
                        <div style="font-size: {{ $fontSizeJenis }}; font-weight: 800; color: #000000; line-height: 1.35; padding: 2px 4px; word-break: break-word; overflow-wrap: anywhere; hyphens: auto; text-align: center;">
                            {{ $namaJenisText ?: '-' }}
                        </div>
                    @endif
                </td>
            </tr>

            {{-- BARIS 4: TANGGAL DATANG --}}
            <tr style="border-bottom: 1px solid #000000;">
                <td style="padding: 5px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                <td colspan="3" style="padding: 5px 8px; border-right: 2px solid #000000;">
                    @if($isEdit)
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="excel-cell-input">
                    @else
                        : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y H:i') : '-' }} WIB
                    @endif
                </td>
            </tr>

            {{-- BARIS 5: TANGGAL PERIKSA --}}
            <tr style="border-bottom: 1px solid #000000;">
                <td style="padding: 5px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Periksa</td>
                <td colspan="3" style="padding: 5px 8px; border-right: 2px solid #000000;">
                    @if($isEdit)
                        <input type="date" name="tgl_panen" value="{{ old('tgl_panen', $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : date('Y-m-d')) }}" class="excel-cell-input">
                    @else
                        : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y') : '-' }}
                    @endif
                </td>
            </tr>

            {{-- BARIS 6: JUMLAH SAMPLE (SISI KIRI) & NOMOR DO (SISI KANAN) --}}
            <tr>
                {{-- SISI KIRI: JUMLAH SAMPLE (PCS) --}}
                <td style="padding: 5px 8px; border-right: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: 700; white-space: nowrap; width: 62%;">Jumlah Sample (pcs):</td>
                            <td style="font-weight: 800; padding-left: 4px;">
                                @if($isEdit)
                                    <input type="number" name="jumlah_sample_pcs" value="{{ old('jumlah_sample_pcs', $qc->jumlah_sample_pcs ?? 10) }}" style="width: 70px; border: 1px solid #94a3b8; padding: 2px 4px; font-size: 0.78rem; font-weight: 700;">
                                @else
                                    : {{ $qc->jumlah_sample_pcs ? $qc->jumlah_sample_pcs : 10 }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- SISI TENGAH & KANAN: NOMOR DO --}}
                <td style="padding: 5px 8px; font-weight: 700; border-right: 1px solid #000000;">Nomor DO</td>
                <td colspan="3" style="padding: 5px 8px; border-right: 2px solid #000000;">
                    @if($isEdit)
                        <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" style="width: 140px; border: 1px solid #94a3b8; padding: 2px 6px; font-size: 0.78rem; font-weight: 700;">
                        &bull; Plat: <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" style="width: 90px; border: 1px solid #94a3b8; padding: 2px 4px; font-size: 0.78rem; text-transform: uppercase;">
                        &bull; Sopir: <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" style="width: 100px; border: 1px solid #94a3b8; padding: 2px 4px; font-size: 0.78rem;">
                    @else
                        : {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}
                        @if ($qc->plat_nomor_truk)
                            &bull; Plat: {{ $qc->plat_nomor_truk }}
                        @endif
                        @if ($qc->sopir_nama)
                            ({{ $qc->sopir_nama }})
                        @endif
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- 3. KONDISI TRANSPORTASI & AUDIT HALAL --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
            {{-- KONDISI TRANSPORTASI --}}
            <tr style="border-bottom: 1px solid #000000;">
                <td style="padding: 6px 8px; width: 170px; font-weight: 700;">KONDISI TRANSPORTASI</td>
                <td style="padding: 6px 8px; width: 30px; text-align: center;">
                    @if($isEdit)
                        <input type="radio" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? '1' : '1') == '1' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $qc->bebas_cemaran_st ? '✔' : '' }}</span>
                    @endif
                </td>
                <td style="padding: 6px 8px; width: 230px;">Tidak ada cemaran, Najis / Kotoran</td>
                <td style="padding: 6px 8px; width: 30px; text-align: center;">
                    @if($isEdit)
                        <input type="radio" name="bebas_cemaran_st" value="0" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st ? '1' : '1') == '0' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ !$qc->bebas_cemaran_st ? '✔' : '' }}</span>
                    @endif
                </td>
                <td style="padding: 6px 8px;">Ada cemaran</td>
            </tr>

            {{-- HALAL 1: ANGKUT BERSAMA BARANG HARAM --}}
            <tr style="border-bottom: 1px solid #000000;">
                <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                    APAKAH BARANG TERSEBUT DIANGKUT BERSAMA DENGAN BARANG HARAM ?
                </td>
                <td colspan="2" style="padding: 5px 8px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <label class="excel-btn-toggle {{ !$qc->angkut_barang_haram_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="angkut_barang_haram_st" value="0" {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ? '1' : '0') == '0' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ !$qc->angkut_barang_haram_st ? '✔' : '' }}</span>
                            @endif
                            <span>Tidak</span>
                        </label>
                        <label class="excel-btn-toggle {{ $qc->angkut_barang_haram_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="angkut_barang_haram_st" value="1" {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st ? '1' : '0') == '1' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ $qc->angkut_barang_haram_st ? '✔' : '' }}</span>
                            @endif
                            <span>Ya</span>
                        </label>
                        <span style="margin-left: 0.75rem; font-weight: 700;">Komentar :</span>
                        @if($isEdit)
                            <input type="text" name="komentar_transportasi" value="{{ old('komentar_transportasi', $qc->komentar_transportasi) }}" style="flex: 1; border: 1px solid #cbd5e1; padding: 2px 6px; font-size: 0.78rem;" placeholder="-">
                        @else
                            <span style="color: #475569;">{{ $qc->komentar_transportasi ?: '-' }}</span>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- HALAL 2: TERDAFTAR LPPOM MUI / BPJPH --}}
            <tr style="border-bottom: 1px solid #000000;">
                <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                    APAKAH BAHAN TERSEBUT TERDAFTAR &amp; DISETUJUI OLEH LPPOM MUI/BPJPH ?
                </td>
                <td colspan="2" style="padding: 5px 8px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <label class="excel-btn-toggle {{ !$qc->terdaftar_lppom_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="terdaftar_lppom_st" value="0" {{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ? '1' : '1') == '0' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ !$qc->terdaftar_lppom_st ? '✔' : '' }}</span>
                            @endif
                            <span>Tidak</span>
                        </label>
                        <label class="excel-btn-toggle {{ $qc->terdaftar_lppom_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="terdaftar_lppom_st" value="1" {{ old('terdaftar_lppom_st', $qc->terdaftar_lppom_st ? '1' : '1') == '1' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ $qc->terdaftar_lppom_st ? '✔' : '' }}</span>
                            @endif
                            <span>Ya</span>
                        </label>
                        <span style="margin-left: 0.75rem; font-weight: 700;">Komentar :</span>
                        @if($isEdit)
                            <input type="text" name="komentar_lppom" value="{{ old('komentar_lppom', $qc->komentar_lppom) }}" style="flex: 1; border: 1px solid #cbd5e1; padding: 2px 6px; font-size: 0.78rem;" placeholder="-">
                        @else
                            <span style="color: #475569;">{{ $qc->komentar_lppom ?: '-' }}</span>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- HALAL 3: MEMPUNYAI SERTIFIKAT HALAL --}}
            <tr style="border-bottom: 1px solid #000000;">
                <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                    APAKAH BARANG TERSEBUT MEMPUNYAI SERTIFIKAT HALAL ?
                </td>
                <td colspan="2" style="padding: 5px 8px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <label class="excel-btn-toggle {{ !$qc->ada_sertifikat_halal_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="ada_sertifikat_halal_st" value="0" {{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ? '1' : '1') == '0' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ !$qc->ada_sertifikat_halal_st ? '✔' : '' }}</span>
                            @endif
                            <span>Tidak</span>
                        </label>
                        <label class="excel-btn-toggle {{ $qc->ada_sertifikat_halal_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="ada_sertifikat_halal_st" value="1" {{ old('ada_sertifikat_halal_st', $qc->ada_sertifikat_halal_st ? '1' : '1') == '1' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ $qc->ada_sertifikat_halal_st ? '✔' : '' }}</span>
                            @endif
                            <span>Ya</span>
                        </label>
                        <span style="margin-left: 0.75rem; font-weight: 700;">Komentar :</span>
                        @if($isEdit)
                            <input type="text" name="komentar_sertifikat" value="{{ old('komentar_sertifikat', $qc->komentar_sertifikat) }}" style="flex: 1; border: 1px solid #cbd5e1; padding: 2px 6px; font-size: 0.78rem;" placeholder="-">
                        @else
                            <span style="color: #475569;">{{ $qc->komentar_sertifikat ?: '-' }}</span>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- HALAL 4: SERTIFIKAT HALAL MASIH BERLAKU --}}
            <tr>
                <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                    APAKAH SERTIFIKAT HALAL BARANG TERSEBUT MASIH BERLAKU ?
                </td>
                <td colspan="2" style="padding: 5px 8px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <label class="excel-btn-toggle {{ !$qc->sertifikat_halal_berlaku_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="sertifikat_halal_berlaku_st" value="0" {{ old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ? '1' : '1') == '0' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ !$qc->sertifikat_halal_berlaku_st ? '✔' : '' }}</span>
                            @endif
                            <span>Tidak</span>
                        </label>
                        <label class="excel-btn-toggle {{ $qc->sertifikat_halal_berlaku_st ? 'active' : '' }}">
                            @if($isEdit)
                                <input type="radio" name="sertifikat_halal_berlaku_st" value="1" {{ old('sertifikat_halal_berlaku_st', $qc->sertifikat_halal_berlaku_st ? '1' : '1') == '1' ? 'checked' : '' }}>
                            @else
                                <span class="excel-box-check mini">{{ $qc->sertifikat_halal_berlaku_st ? '✔' : '' }}</span>
                            @endif
                            <span>Ya</span>
                        </label>
                        <span style="margin-left: 0.75rem; font-weight: 700;">Komentar :</span>
                        @if($isEdit)
                            <input type="text" name="komentar_berlaku" value="{{ old('komentar_berlaku', $qc->komentar_berlaku) }}" style="flex: 1; border: 1px solid #cbd5e1; padding: 2px 6px; font-size: 0.78rem;" placeholder="-">
                        @else
                            <span style="color: #475569;">{{ $qc->komentar_berlaku ?: '-' }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 4. 2. ISI RAW MATERIAL & KONDISI KEMASAN --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem;">
        <div style="display: flex; border-bottom: 2px solid #000000; padding: 6px 12px; justify-content: space-between; align-items: center; background: #ffffff;">
            {{-- ISI RAW MATERIAL --}}
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 700;">OK</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ old('items.'.$qcdtlId.'.status_raw_material', $firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 700;">TDK STD</span>
                </label>
            </div>

            {{-- KEMASAN --}}
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <span style="font-weight: 800; text-decoration: underline;">KEMASAN</span>
                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="items[{{ $qcdtlId }}][kemasan_kondisi]" value="OK" {{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 700;">OK</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="items[{{ $qcdtlId }}][kemasan_kondisi]" value="TIDAK_STANDARD" {{ old('items.'.$qcdtlId.'.kemasan_kondisi', $firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ ($firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 700;">TIDAK STANDARD</span>
                </label>
            </div>
        </div>

        {{-- TABEL DIMENSI & SPESIFIKASI VS CHECKLIST CACAT KARTON (GRID 3 KOLOM PERSIS GAMBAR 3) --}}
        <div style="display: grid; grid-template-columns: 1.2fr 1.1fr;">
            {{-- TABEL PARAMETER KIRI --}}
            <div style="border-right: 2px solid #000000;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 0.78rem;">
                    <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000;">
                        <td style="padding: 5px; border-right: 1px solid #000000; width: 35%;">Parameter</td>
                        <td style="padding: 5px; border-right: 1px solid #000000; width: 35%;">Hasil Analisa</td>
                        <td style="padding: 5px; width: 30%;">Standard</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">PANJANG</td>
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][dimensi_panjang_analisa]" value="{{ old('items.'.$qcdtlId.'.dimensi_panjang_analisa', $firstDetail?->dimensi_panjang_analisa) }}" class="excel-cell-input text-center" placeholder="38 cm">
                            @else
                                {{ $firstDetail?->dimensi_panjang_analisa ?: '-' }}
                            @endif
                        </td>
                        <td style="padding: 5px;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][dimensi_panjang_standar]" value="{{ old('items.'.$qcdtlId.'.dimensi_panjang_standar', $firstDetail?->dimensi_panjang_standar) }}" class="excel-cell-input text-center">
                            @else
                                {{ $firstDetail?->dimensi_panjang_standar ?: '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">LEBAR</td>
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][dimensi_lebar_analisa]" value="{{ old('items.'.$qcdtlId.'.dimensi_lebar_analisa', $firstDetail?->dimensi_lebar_analisa) }}" class="excel-cell-input text-center" placeholder="28 cm">
                            @else
                                {{ $firstDetail?->dimensi_lebar_analisa ?: '-' }}
                            @endif
                        </td>
                        <td style="padding: 5px;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][dimensi_lebar_standar]" value="{{ old('items.'.$qcdtlId.'.dimensi_lebar_standar', $firstDetail?->dimensi_lebar_standar) }}" class="excel-cell-input text-center">
                            @else
                                {{ $firstDetail?->dimensi_lebar_standar ?: '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">TINGGI</td>
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][dimensi_tinggi_analisa]" value="{{ old('items.'.$qcdtlId.'.dimensi_tinggi_analisa', $firstDetail?->dimensi_tinggi_analisa) }}" class="excel-cell-input text-center" placeholder="32 cm">
                            @else
                                {{ $firstDetail?->dimensi_tinggi_analisa ?: '-' }}
                            @endif
                        </td>
                        <td style="padding: 5px;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][dimensi_tinggi_standar]" value="{{ old('items.'.$qcdtlId.'.dimensi_tinggi_standar', $firstDetail?->dimensi_tinggi_standar) }}" class="excel-cell-input text-center">
                            @else
                                {{ $firstDetail?->dimensi_tinggi_standar ?: '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 800;">SPESIFIKASI</td>
                        <td style="padding: 5px; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][spesifikasi_analisa]" value="{{ old('items.'.$qcdtlId.'.spesifikasi_analisa', $firstDetail?->spesifikasi_analisa ?? 'K150/M125/K150') }}" class="excel-cell-input text-center">
                            @else
                                {{ $firstDetail?->spesifikasi_analisa ?: 'K150/M125/K150' }}
                            @endif
                        </td>
                        <td style="padding: 5px;">
                            @if($isEdit)
                                <input type="text" name="items[{{ $qcdtlId }}][spesifikasi_standar]" value="{{ old('items.'.$qcdtlId.'.spesifikasi_standar', $firstDetail?->spesifikasi_standar ?? 'Standard') }}" class="excel-cell-input text-center">
                            @else
                                {{ $firstDetail?->spesifikasi_standar ?: 'Standard' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- CHECKLIST SISI KANAN (GRID 3 KOLOM PERSIS GAMBAR 3) --}}
            <div style="padding: 10px 14px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.65rem; align-items: center; font-size: 0.78rem;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_kotor]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_kotor', $firstDetail?->kemasan_kotor) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_kotor ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">KOTOR</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_apek]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_apek', $firstDetail?->kemasan_apek) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_apek ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">APEK</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_basah]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_basah', $firstDetail?->kemasan_basah) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_basah ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">BASAH</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_jamur]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_jamur', $firstDetail?->kemasan_jamur) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_jamur ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">JAMUR</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_sobek]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_sobek', $firstDetail?->kemasan_sobek) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_sobek ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">SOBEK</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_berminyak]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_berminyak', $firstDetail?->kemasan_berminyak) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_berminyak ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">BERMINYAK</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; grid-column: span 3;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kemasan_berdebu]" value="1" {{ old('items.'.$qcdtlId.'.kemasan_berdebu', $firstDetail?->kemasan_berdebu) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kemasan_berdebu ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">BERDEBU</span>
                </label>
            </div>
        </div>
    </div>

    {{-- 5. KOMENTAR --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem; padding: 6px 10px; font-size: 0.78rem;">
        <span style="font-weight: 800; text-decoration: underline;">KOMENTAR</span>
        <div style="margin-top: 4px;">
            @if($isEdit)
                <textarea name="catatan_umum" rows="2" class="excel-cell-input" style="border: 1px solid #cbd5e1; padding: 4px 6px;" placeholder="Catatan kondisi kualitas kedatangan karton box...">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
            @else
                <div style="min-height: 24px; color: #1e293b;">
                    {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: 'Kualitas Karton Box Sesuai Standar HACCP') }}
                </div>
            @endif
        </div>
    </div>

    {{-- 6. KESIMPULAN --}}
    @php
        $rawSatuan = $firstDetail?->satuan ?? ($qc->satuan ?? 'pcs');
        $satuanLower = strtolower($rawSatuan);
        
        $nettoVal = $firstDetail?->qty_netto_lolos ?? $totalNetto;
        $rejectVal = $firstDetail?->qty_reject ?? $totalReject;
        $pabrikVal = $qc->jumlah_di_pabrik ?? $totalGross;

        // Jika Ditolak Total tapi rejectVal masih 0, ambil kuantitas pabrik/gross
        if ($qc->status_qc === 'DITOLAK_TOTAL' && $rejectVal <= 0) {
            $rejectVal = $pabrikVal > 0 ? $pabrikVal : ($totalGross > 0 ? $totalGross : ($qc->jumlah_surat_jalan ?? 0));
        }

        // Jika Diterima tapi nettoVal masih 0, ambil dari pabrik/gross dikurang reject
        if ($qc->status_qc !== 'DITOLAK_TOTAL' && $nettoVal <= 0 && $pabrikVal > 0) {
            $nettoVal = max(0, $pabrikVal - $rejectVal);
        }

        $nettoFormatted = number_format($nettoVal, 0, ',', '.');
        $rejectFormatted = number_format($rejectVal, 0, ',', '.');
    @endphp
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem; padding: 7px 12px; display: flex; align-items: center; gap: 2rem; font-size: 0.85rem;">
        <span style="font-weight: 900; letter-spacing: 0.04em;">KESIMPULAN :</span>

        @if($isEdit)
            <div style="display: inline-flex; align-items: center; gap: 2rem;">
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="radio" name="kesimpulan_qc" value="TERIMA" {{ old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : '') === 'TERIMA' ? 'checked' : '' }} class="excel-checkbox">
                    <strong style="font-weight: 800; color: #000000;">TERIMA :</strong>
                    <u style="font-weight: 700; margin-left: 2px; text-underline-offset: 3px;">{{ $nettoFormatted }}</u>
                    <span style="margin-left: 2px;">{{ $satuanLower }}</span>
                </label>

                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; color: #dc2626;">
                    <input type="radio" name="kesimpulan_qc" value="TOLAK" {{ old('kesimpulan_qc', $qc->status_qc === 'DITOLAK_TOTAL' ? 'TOLAK' : '') === 'TOLAK' ? 'checked' : '' }} class="excel-checkbox">
                    <strong style="font-weight: 800; color: #dc2626;">TOLAK :</strong>
                    <u style="font-weight: 700; margin-left: 2px; text-underline-offset: 3px; color: #dc2626;">{{ $rejectFormatted }}</u>
                    <span style="margin-left: 2px; color: #dc2626;">{{ $satuanLower }}</span>
                </label>
            </div>
        @else
            <div style="display: inline-flex; align-items: center; gap: 2.5rem; flex-wrap: wrap;">
                <div style="display: inline-flex; align-items: center;">
                    @if($qc->status_qc !== 'DITOLAK_TOTAL')
                        <span style="font-weight: 900; font-size: 1rem; margin-right: 6px; color: #000000;">✓</span>
                    @endif
                    <strong style="font-weight: 800; color: #000000;">TERIMA :</strong>
                    <u style="font-weight: 800; margin-left: 6px; margin-right: 4px; text-underline-offset: 3px;">{{ $nettoFormatted }}</u>
                    <span>{{ $satuanLower }}</span>
                </div>

                <div style="display: inline-flex; align-items: center; color: #dc2626;">
                    @if($qc->status_qc === 'DITOLAK_TOTAL')
                        <span style="font-weight: 900; font-size: 1rem; margin-right: 6px; color: #dc2626;">✓</span>
                    @endif
                    <strong style="font-weight: 800; color: #dc2626;">TOLAK :</strong>
                    <u style="font-weight: 800; margin-left: 6px; margin-right: 4px; text-underline-offset: 3px; color: #dc2626;">{{ $rejectFormatted }}</u>
                    <span style="color: #dc2626;">{{ $satuanLower }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- 7. TANDA TANGAN DUA KOLOM RESMI --}}
    <div style="border: 2px solid #000000; font-size: 0.78rem;">
        <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <tr style="border-bottom: 1px solid #000000; background: #f8fafc; font-weight: 700;">
                <td style="padding: 4px; width: 50%; border-right: 2px solid #000000;">
                    QC RAW MATERIAL
                </td>
                <td style="padding: 4px; width: 50%;">
                    QC Supervisor
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #000000; background: #f1f5f9; font-weight: 700; font-size: 0.72rem;">
                <td style="padding: 2px; border-right: 2px solid #000000;">
                    <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                </td>
                <td style="padding: 2px;">
                    <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000;">NAMA</td><td style="width: 50%;">TTD</td></tr></table>
                </td>
            </tr>
            <tr style="height: 38px;">
                <td style="padding: 2px; border-right: 2px solid #000000; vertical-align: bottom;">
                    <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->petugas_qc_nama }}</td><td style="width: 50%;"></td></tr></table>
                </td>
                <td style="padding: 2px; vertical-align: bottom;">
                    <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->qc_supervisor_nama ?: 'Supervisor QC' }}</td><td style="width: 50%;"></td></tr></table>
                </td>
            </tr>
        </table>
    </div>
</div>
