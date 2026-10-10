{{-- ========================================================================= --}}
{{-- DOKUMEN CHECKLIST PEMERIKSAAN KEDATANGAN MINYAK GORENG                    --}}
{{-- NO. DOKUMEN: MFI/HACCP-04/FRM-03/029/VIII/2021                            --}}
{{-- PERSIS FORMAT EXCEL PT MIRASA FOOD INDUSTRY                               --}}
{{-- ========================================================================= --}}
@php
    $isEdit = $isEdit ?? false;
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? ($firstDtl?->qcdtl_id ?? 0);
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $totalReject = $qc->details->sum('qty_reject');

    // Deteksi Satuan: Liter vs Kg
    $satuanRaw = $firstDetail?->barang?->satuanDasar?->satuan_nm
        ?: ($firstDetail?->barang?->satuanDasar?->satuan_cd
        ?: ($qc->po?->details?->firstWhere('barang_id', $firstDetail?->barang_id)?->barang?->satuanDasar?->satuan_nm
        ?: ($qc->po?->details?->first()?->barang?->satuanDasar?->satuan_nm ?? '')));

    $isLiter = false;
    if ($satuanRaw) {
        $isLiter = (stripos($satuanRaw, 'liter') !== false || stripos($satuanRaw, 'ltr') !== false || strtoupper(trim($satuanRaw)) === 'L');
    }
    if (!$isLiter) {
        $namaItem = strtoupper(($firstDetail?->barang?->barang_nm ?? '') . ' ' . ($qc->nama_jenis ?? ''));
        if (str_contains($namaItem, 'KELAPA') && !str_contains($namaItem, 'SAWIT')) {
            $isLiter = true;
        }
    }

    $satuan = $isLiter ? 'Liter' : 'Kg';
    $satuanUpper = $isLiter ? 'LITER' : 'KG';
    $satuanLower = $isLiter ? 'liter' : 'kg';
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
                        Cheklist Pemeriksaan Kedatangan Minyak Goreng
                    </div>
                </td>
                <td style="width: 255px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; font-weight: 700; width: 110px; border-right: 1px solid #000000;">No. Dokumen :</td>
                            <td style="padding: 4px 6px; font-weight: 800;">MFI/HACCP-04/FRM-03/029/VIII/2021</td>
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

    {{-- 2. TABEL IDENTITAS KEDATANGAN MINYAK (PERSIS FORMAT EXCEL HACCP PT MIRASA) --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
            <tr>
                {{-- SISI KIRI: JUDUL BESAR LAPORAN KEDATANGAN MINYAK GORENG --}}
                <td style="width: 32%; vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000; border-bottom: 1px solid #000000;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #334155; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                    <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                    <div style="font-size: 1.45rem; font-weight: 900; margin-top: 3px; color: #000000; letter-spacing: 0.04em;">
                        MINYAK GORENG
                    </div>
                </td>

                {{-- SISI TENGAH: DETAIL TABEL IDENTITAS --}}
                <td style="width: 53%; padding: 0; vertical-align: top; border-right: 2px solid #000000; border-bottom: 1px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 5px 8px; width: 100px; font-weight: 700; border-right: 1px solid #000000;">Nama Bahan</td>
                            <td colspan="3" style="padding: 5px 8px; font-weight: 700;">
                                @if($isEdit)
                                    <input type="text" name="items[{{ $qcdtlId }}][nama_bahan]" value="{{ old('items.'.$qcdtlId.'.nama_bahan', $firstDetail?->barang?->barang_nm ?? ($qc->nama_jenis ?: 'Minyak Goreng Kelapa Sawit')) }}" class="excel-cell-input" placeholder="Minyak Goreng Kelapa Sawit">
                                @else
                                    : {{ $firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Minyak Goreng Kelapa Sawit') }}
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000; background: #f8fafc; text-align: center; font-weight: 700;">
                            <td style="padding: 4px 6px; border-right: 1px solid #000000; width: 25%;">Nama Produsen</td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000; width: 25%;">Negara Produsen</td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000; width: 25%;">Jumlah Surat Jalan ({{ $satuan }})</td>
                            <td style="padding: 4px 6px; width: 25%;">Jumlah di Pabrik ({{ $satuan }})</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000; text-align: center;">
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="text" name="nama_produsen" value="{{ old('nama_produsen', $qc->nama_produsen ?? $qc->supplier?->supplier_nm) }}" class="excel-cell-input text-center">
                                @else
                                    {{ $qc->nama_produsen ?: ($qc->supplier?->supplier_nm ?? '-') }}
                                @endif
                            </td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="text" name="negara_produsen" value="{{ old('negara_produsen', $qc->negara_produsen ?? 'Indonesia') }}" class="excel-cell-input text-center">
                                @else
                                    {{ $qc->negara_produsen ?: 'Indonesia' }}
                                @endif
                            </td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="number" step="any" name="jumlah_surat_jalan" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan) }}" class="excel-cell-input text-center font-bold">
                                @else
                                    {{ $qc->jumlah_surat_jalan ? number_format($qc->jumlah_surat_jalan, 0, ',', '.') : '-' }}
                                @endif
                            </td>
                            <td style="padding: 4px 6px;">
                                @if($isEdit)
                                    <input type="number" step="any" name="jumlah_di_pabrik" value="{{ old('jumlah_di_pabrik', $qc->jumlah_di_pabrik ?? $totalGross) }}" class="excel-cell-input text-center font-bold">
                                @else
                                    <strong>{{ $qc->jumlah_di_pabrik ? number_format($qc->jumlah_di_pabrik, 0, ',', '.') : number_format($totalGross, 0, ',', '.') }}</strong>
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                            <td colspan="3" style="padding: 4px 8px;">
                                @if($isEdit)
                                    <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="excel-cell-input">
                                @else
                                    : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y H:i') : '-' }} WIB
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Periksa</td>
                            <td colspan="3" style="padding: 4px 8px;">
                                @if($isEdit)
                                    <input type="date" name="tgl_panen" value="{{ old('tgl_panen', $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : date('Y-m-d')) }}" class="excel-cell-input">
                                @else
                                    : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d-m-Y') : '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 8px; font-weight: 700; border-right: 1px solid #000000;">Nomor DO</td>
                            <td colspan="3" style="padding: 4px 8px;">
                                @if($isEdit)
                                    <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="excel-cell-input">
                                @else
                                    : {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- SISI KANAN: KOTAK VERTIKAL NAMA JENIS : (PERSIS FORM EXCEL HACCP) --}}
                <td style="width: 15%; vertical-align: top; padding: 0; border-bottom: 1px solid #000000; background: #ffffff;">
                    <div style="padding: 5px 8px; font-weight: 900; font-size: 0.8rem; border-bottom: 1px solid #000000; text-align: left; background: #f8fafc; letter-spacing: 0.03em;">
                        NAMA JENIS :
                    </div>
                    <div style="padding: 12px 6px; text-align: center; font-weight: 800; font-size: 0.95rem; color: #0f172a; min-height: 120px; display: flex; align-items: center; justify-content: center; line-height: 1.35;">
                        @if($isEdit)
                            <textarea name="nama_jenis" rows="5" class="excel-cell-input text-center" style="font-weight: 800; resize: vertical; width: 100%; font-size: 0.85rem;" placeholder="RBD Palm Olein / Curah Sawit / Filma">{{ old('nama_jenis', $qc->nama_jenis) }}</textarea>
                        @else
                            <span>{{ $qc->nama_jenis ?: '-' }}</span>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- BARIS BAWAH: JUMLAH SAMPLE (GR) & INFO LOGISTIK ARMADA --}}
            <tr>
                <td style="padding: 6px 10px; border-right: 2px solid #000000; vertical-align: middle;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.4rem;">
                        <span style="font-weight: 700;">Jumlah Sample (gr):</span>
                        @if($isEdit)
                            <input type="number" step="any" name="jumlah_sample_gr" value="{{ old('jumlah_sample_gr', $qc->jumlah_sample_gr ?? 250) }}" style="width: 75px; border: 1px solid #94a3b8; padding: 2px 6px; font-size: 0.8rem; font-weight: 700; text-align: center;">
                        @else
                            <span style="font-weight: 800; font-size: 0.85rem;">: {{ $qc->jumlah_sample_gr ? $qc->jumlah_sample_gr . ' gr' : ($qc->jumlah_sample_kg ? $qc->jumlah_sample_kg . ' kg' : '250 gr') }}</span>
                        @endif
                    </div>
                </td>
                <td colspan="2" style="padding: 6px 10px; font-size: 0.78rem; vertical-align: middle; background: #fafafa;">
                    <span style="font-weight: 700;">Armada:</span>
                    No Plat: <strong>{{ $qc->plat_nomor_truk ?: '-' }}</strong>
                    &bull; Sopir: <strong>{{ $qc->sopir_nama ?: '-' }}</strong>
                    @if($qc->po)
                        &bull; PO: <strong>{{ $qc->po->po_no }}</strong>
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

    {{-- 4. 2. ISI RAW MATERIAL & KONDISI TANGKI/JERIGEN --}}
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

            {{-- KONDISI TANGKI / JERIGEN --}}
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <span style="font-weight: 800; text-decoration: underline;">KONDISI TANGKI/JERIGEN</span>
                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="items[{{ $qcdtlId }}][kondisi_tangki_jerigen]" value="OK" {{ old('items.'.$qcdtlId.'.kondisi_tangki_jerigen', $firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ ($firstDetail?->kondisi_tangki_jerigen ?? 'OK') === 'OK' ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 700;">OK</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="items[{{ $qcdtlId }}][kondisi_tangki_jerigen]" value="TIDAK_STANDARD" {{ old('items.'.$qcdtlId.'.kondisi_tangki_jerigen', $firstDetail?->kondisi_tangki_jerigen ?? '') === 'TIDAK_STANDARD' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ ($firstDetail?->kondisi_tangki_jerigen ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 700;">TIDAK STANDARD</span>
                </label>
            </div>
        </div>

        {{-- TABEL FFA & CHECKLIST MINYAK JERNIH / TANGKI BERSIH --}}
        <div style="display: grid; grid-template-columns: 1.15fr 1fr;">
            {{-- TABEL FFA KIRI --}}
            <div style="border-right: 2px solid #000000;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 0.78rem;">
                    <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000;">
                        <td style="padding: 6px; border-right: 1px solid #000000; width: 33%;">Parameter</td>
                        <td style="padding: 6px; border-right: 1px solid #000000; width: 33%;">FFA<br>DI COA</td>
                        <td style="padding: 6px; width: 34%;">FFA<br>CEK QC MIRASA</td>
                    </tr>
                    <tr style="height: 52px;">
                        <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 800; font-size: 0.95rem;">FFA</td>
                        <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <input type="number" step="0.001" name="items[{{ $qcdtlId }}][ffa_coa]" value="{{ old('items.'.$qcdtlId.'.ffa_coa', $firstDetail?->ffa_coa) }}" class="excel-cell-input text-center" placeholder="0.050">
                            @else
                                {{ $firstDetail?->ffa_coa !== null ? number_format($firstDetail->ffa_coa, 3, ',', '.') : '-' }}
                            @endif
                        </td>
                        <td style="padding: 6px; font-weight: 900; color: #0284c7;">
                            @if($isEdit)
                                <input type="number" step="0.001" name="items[{{ $qcdtlId }}][ffa_qc]" value="{{ old('items.'.$qcdtlId.'.ffa_qc', $firstDetail?->ffa_qc) }}" class="excel-cell-input text-center font-bold" placeholder="0.045">
                            @else
                                {{ $firstDetail?->ffa_qc !== null ? number_format($firstDetail->ffa_qc, 3, ',', '.') : '-' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- CHECKLIST SISI KANAN --}}
            <div style="padding: 12px 18px; display: flex; flex-direction: column; justify-content: center; gap: 0.85rem; font-size: 0.825rem;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][minyak_jernih_st]" value="1" {{ old('items.'.$qcdtlId.'.minyak_jernih_st', $firstDetail?->minyak_jernih_st ? '1' : '1') == '1' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->minyak_jernih_st ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">MINYAK JERNIH</span>
                </label>

                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][tangki_bersih_st]" value="1" {{ old('items.'.$qcdtlId.'.tangki_bersih_st', $firstDetail?->tangki_bersih_st ? '1' : '1') == '1' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->tangki_bersih_st ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">TANGKI BAGIAN DALAM BERSIH</span>
                </label>
            </div>
        </div>
    </div>

    {{-- 5. KOMENTAR --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem; padding: 6px 10px; font-size: 0.78rem;">
        <span style="font-weight: 800; text-decoration: underline;">KOMENTAR</span>
        <div style="margin-top: 4px;">
            @if($isEdit)
                <textarea name="catatan_umum" rows="2" class="excel-cell-input" style="border: 1px solid #cbd5e1; padding: 4px 6px;" placeholder="Catatan kondisi kualitas kedatangan minyak goreng...">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
            @else
                <div style="min-height: 24px; color: #1e293b;">
                    {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: 'Kualitas Minyak Goreng Sesuai Standar HACCP') }}
                </div>
            @endif
        </div>
    </div>

    {{-- 6. KESIMPULAN --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.5rem; padding: 6px 14px; display: flex; align-items: center; flex-wrap: wrap; gap: 2rem; font-size: 0.85rem; font-family: 'Segoe UI', Arial, sans-serif;">
        <span style="font-weight: 900; letter-spacing: 0.04em; color: #000000;">KESIMPULAN</span>

        @if($isEdit)
            <div style="display: inline-flex; align-items: center; gap: 2.5rem; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="radio" name="kesimpulan_qc" value="TERIMA" {{ old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : '') === 'TERIMA' ? 'checked' : '' }} class="excel-checkbox">
                    <strong style="font-weight: 800; color: #000000;">TERIMA :</strong>
                    <u style="font-weight: 700; margin-left: 2px; text-underline-offset: 3px;">{{ number_format($totalNetto, 2, ',', '.') }}</u>
                    <span style="margin-left: 2px;">{{ $satuanLower }}</span>
                </label>

                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer; color: #dc2626;">
                    <input type="radio" name="kesimpulan_qc" value="TOLAK" {{ old('kesimpulan_qc', $qc->status_qc === 'DITOLAK_TOTAL' ? 'TOLAK' : '') === 'TOLAK' ? 'checked' : '' }} class="excel-checkbox">
                    <strong style="font-weight: 800; color: #dc2626;">TOLAK :</strong>
                    <u style="font-weight: 700; margin-left: 2px; text-underline-offset: 3px; color: #dc2626;">{{ number_format($totalReject, 2, ',', '.') }}</u>
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
                    <u style="font-weight: 700; margin-left: 6px; margin-right: 4px; text-underline-offset: 3px;">{{ number_format($totalNetto, 2, ',', '.') }}</u>
                    <span>{{ $satuanLower }}</span>
                </div>

                <div style="display: inline-flex; align-items: center; color: #dc2626;">
                    @if($qc->status_qc === 'DITOLAK_TOTAL')
                        <span style="font-weight: 900; font-size: 1rem; margin-right: 6px; color: #dc2626;">✓</span>
                    @endif
                    <strong style="font-weight: 800; color: #dc2626;">TOLAK :</strong>
                    <u style="font-weight: 700; margin-left: 6px; margin-right: 4px; text-underline-offset: 3px; color: #dc2626;">{{ number_format($totalReject, 2, ',', '.') }}</u>
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
