{{-- ========================================================================= --}}
{{-- DOKUMEN CHECKLIST STANDAR KEBETERIMAAN BAHAN BAKU: SINGKONG               --}}
{{-- NO. DOKUMEN: MFI/HACCP-04/FRM-03/048/VIII/2021 (1 LEMBAR TUNGGAL SELESAI) --}}
{{-- PERSIS FORMAT FORMULIR EXCEL RESMI PT MIRASA FOOD INDUSTRY                --}}
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
    
    {{-- KOP SURAT RESMI PT MIRASA --}}
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
                        {{ $docTitle ?? 'Cheklist Standar Kebeterimaan Bahan Baku' }}
                    </div>
                </td>
                <td style="width: 255px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 3px 6px; font-weight: 700; width: 110px; border-right: 1px solid #000000;">No. Dokumen :</td>
                            <td style="padding: 3px 6px; font-weight: 800;">{{ $docNo ?? 'MFI/HACCP-04/FRM-03/048/VIII/2021' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi :</td>
                            <td style="padding: 3px 6px;">{{ $revisi ?? '1' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit :</td>
                            <td style="padding: 3px 6px;">{{ $tglTerbit ?? '11-09-2023' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Halaman :</td>
                            <td style="padding: 3px 6px;">1 dari 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- TABEL IDENTITAS KEDATANGAN SINGKONG --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.35rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
            <tr>
                <td style="width: 35%; vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000; border-bottom: 2px solid #000000;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #334155; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                    <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                    <div style="font-size: 1.45rem; font-weight: 900; margin-top: 3px; letter-spacing: 0.08em;">
                        S I N G K O N G
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 900; margin-top: 4px; color: #000000; letter-spacing: 0.08em;">
                        {{ ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'PENGUJIAN II' : 'PENGUJIAN I' }}
                    </div>
                </td>
                <td style="width: 65%; padding: 0; vertical-align: top; border-bottom: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; width: 100px; font-weight: 700; border-right: 1px solid #000000;">Nama RM</td>
                            <td colspan="4" style="padding: 4px 6px; font-weight: 800;">
                                @php
                                    $namaRmDisplay = $qc->details->pluck('barang.barang_nm')->filter()->unique()->implode(', ') ?: ($firstDetail?->barang?->barang_nm ?: ($qc->nama_jenis ?: 'Singkong Basah Curah'));
                                @endphp
                                @if($isEdit)
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="text" name="nama_jenis" value="{{ old('nama_jenis', $namaRmDisplay) }}" class="excel-cell-input font-bold" style="flex: 2;">
                                        @if(!empty($qc->batch_no))
                                            <span style="font-size: 0.72rem; color: #475569; white-space: nowrap;">Batch: <strong>{{ $qc->batch_no }}</strong></span>
                                        @endif
                                    </div>
                                @else
                                    : {{ $namaRmDisplay }}
                                    @if(!empty($qc->batch_no))
                                        <span style="margin-left: 0.75rem; font-size: 0.72rem; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px; font-weight: 700;">
                                            Batch: {{ $qc->batch_no }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000; background: #f8fafc; text-align: center; font-weight: 700;">
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">Nama Produsen</td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">Negara Produsen</td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah Surat Jalan (kg)</td>
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">Jumlah di Pabrik (kg)</td>
                            <td style="padding: 4px 6px; width: 130px;">LOKASI PANEN :</td>
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
                            <td style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="number" step="any" name="jumlah_di_pabrik" value="{{ old('jumlah_di_pabrik', $qc->jumlah_di_pabrik ?? $totalGross) }}" class="excel-cell-input text-center font-bold">
                                @else
                                    <strong style="color: #0284c7;">{{ $qc->jumlah_di_pabrik ? number_format($qc->jumlah_di_pabrik, 0, ',', '.') : number_format($totalGross, 0, ',', '.') }}</strong>
                                @endif
                            </td>
                            <td rowspan="4" style="padding: 4px 6px; vertical-align: top; font-weight: 700;">
                                @if($isEdit)
                                    <input type="text" name="lokasi_panen" value="{{ old('lokasi_panen', $qc->lokasi_panen ?? 'Wonosobo / Mitra') }}" class="excel-cell-input text-center" placeholder="Lokasi Panen">
                                @else
                                    <div>{{ $qc->lokasi_panen ?: 'Wonosobo / Mitra' }}</div>
                                @endif
                                <div style="margin-top: 15px; border-top: 1px dashed #000; padding-top: 4px; font-size: 0.7rem; font-weight: 700;">
                                    <u>Tanda Tangan ACC</u>
                                </div>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Umur Singkong</td>
                            <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="number" step="0.1" name="umur_singkong_bln" value="{{ old('umur_singkong_bln', $qc->umur_singkong_bln ?? 9) }}" style="width: 70px; border: 1px solid #cbd5e1; padding: 2px 4px; font-size: 0.78rem;"> Bulan
                                @else
                                    : {{ $qc->umur_singkong_bln ? $qc->umur_singkong_bln . ' Bulan' : '9 Bulan' }}
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Panen</td>
                            <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="date" name="tgl_panen" value="{{ old('tgl_panen', $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : date('Y-m-d')) }}" class="excel-cell-input">
                                @else
                                    : {{ $qc->tgl_panen ? $qc->tgl_panen->format('d/m/Y') : '-' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Datang</td>
                            <td colspan="3" style="padding: 4px 6px; border-right: 1px solid #000000;">
                                @if($isEdit)
                                    <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="excel-cell-input">
                                @else
                                    : {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 10px; border-right: 2px solid #000000;">
                    <strong>Jumlah Sample (kg):</strong>
                    @if($isEdit)
                        <input type="number" step="0.1" name="jumlah_sample_kg" value="{{ old('jumlah_sample_kg', $qc->jumlah_sample_kg ?? 10) }}" style="width: 80px; border: 1px solid #94a3b8; padding: 2px 6px; font-size: 0.8rem; font-weight: 700;"> kg
                    @else
                        : {{ $qc->jumlah_sample_kg ? number_format($qc->jumlah_sample_kg, 1) . ' kg' : '10.0 kg' }}
                    @endif
                </td>
                <td style="padding: 5px 10px;">
                    <strong>Nomor DO / SJ :</strong>
                    @if($isEdit)
                        <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" style="width: 140px; border: 1px solid #94a3b8; padding: 2px 6px; font-size: 0.8rem; font-weight: 700;">
                        &bull; Plat: <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" style="width: 100px; border: 1px solid #94a3b8; padding: 2px 6px; font-size: 0.8rem; text-transform: uppercase;">
                        &bull; Sopir: <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" style="width: 110px; border: 1px solid #94a3b8; padding: 2px 6px; font-size: 0.8rem;">
                    @else
                        : {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }} &bull; Plat: {{ $qc->plat_nomor_truk ?: '-' }} ({{ $qc->sopir_nama ?: '-' }})
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- KONDISI TRANSPORTASI & AUDIT HALAL --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.35rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
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

            <tr>
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
        </table>
    </div>

    {{-- 2. ISI RAW MATERIAL, TABEL PARAMETER DIAMETER & CHECKLIST KONDISI --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.35rem;">
        <div style="padding: 5px 12px; border-bottom: 2px solid #000000; display: flex; align-items: center; gap: 1.5rem; background: #ffffff;">
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

        <div style="display: grid; grid-template-columns: 1.15fr 1fr;">
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
                        <td style="padding: 4px; text-align: center; font-weight: 800; border-right: 1px solid #000000;">
                            @if($isEdit)
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][diameter_kurang_4cm_persen]" value="{{ old('items.'.$qcdtlId.'.diameter_kurang_4cm_persen', $firstDetail?->diameter_kurang_4cm_persen ?? 0) }}" class="excel-cell-input text-center"> %
                            @else
                                <span style="color: {{ (float)($firstDetail?->diameter_kurang_4cm_persen ?? 0) > 5 ? '#dc2626' : '#059669' }};">
                                    {{ $firstDetail?->diameter_kurang_4cm_persen !== null ? number_format($firstDetail->diameter_kurang_4cm_persen, 1) . '%' : '-' }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 4px; text-align: center;">Max 5.0%</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 4px 8px; border-right: 1px solid #000000;">- &ge; 4 cm</td>
                        <td style="padding: 4px; text-align: center; font-weight: 800; border-right: 1px solid #000000;">
                            @if($isEdit)
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][diameter_lebih_4cm_persen]" value="{{ old('items.'.$qcdtlId.'.diameter_lebih_4cm_persen', $firstDetail?->diameter_lebih_4cm_persen ?? 100) }}" class="excel-cell-input text-center"> %
                            @else
                                <span style="color: {{ (float)($firstDetail?->diameter_lebih_4cm_persen ?? 0) < 95 ? '#dc2626' : '#059669' }};">
                                    {{ $firstDetail?->diameter_lebih_4cm_persen !== null ? number_format($firstDetail->diameter_lebih_4cm_persen, 1) . '%' : '-' }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 4px; text-align: center;">Min 95%</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td colspan="3" style="padding: 3px 6px; font-weight: 700; background: #f8fafc;">3. Hasil Fryer (Uji Rasa &amp; Tekstur)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 4px 8px; border-right: 1px solid #000000;">- RASA</td>
                        <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <select name="items[{{ $qcdtlId }}][fryer_rasa]" class="excel-cell-input">
                                    <option value="TIDAK_PAHIT" {{ ($firstDetail?->fryer_rasa ?? 'TIDAK_PAHIT') === 'TIDAK_PAHIT' ? 'selected' : '' }}>Tidak Pahit</option>
                                    <option value="PAHIT" {{ ($firstDetail?->fryer_rasa ?? '') === 'PAHIT' ? 'selected' : '' }}>Pahit</option>
                                </select>
                            @else
                                <span style="color: {{ $firstDetail?->fryer_rasa === 'PAHIT' ? '#dc2626' : '#059669' }};">
                                    {{ $firstDetail?->fryer_rasa === 'PAHIT' ? 'Pahit' : 'Tidak Pahit' }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 4px; text-align: center;">Tidak Pahit</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 4px 8px; border-right: 1px solid #000000;">- Tekstur</td>
                        <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <select name="items[{{ $qcdtlId }}][fryer_tekstur]" class="excel-cell-input">
                                    <option value="RENYAH" {{ ($firstDetail?->fryer_tekstur ?? 'RENYAH') === 'RENYAH' ? 'selected' : '' }}>Renyah</option>
                                    <option value="ALOT" {{ ($firstDetail?->fryer_tekstur ?? '') === 'ALOT' ? 'selected' : '' }}>Alot</option>
                                    <option value="LEMBEK" {{ ($firstDetail?->fryer_tekstur ?? '') === 'LEMBEK' ? 'selected' : '' }}>Lembek</option>
                                </select>
                            @else
                                <span style="color: {{ $firstDetail?->fryer_tekstur === 'ALOT' ? '#dc2626' : '#059669' }};">
                                    {{ $firstDetail?->fryer_tekstur ? ucfirst(strtolower($firstDetail->fryer_tekstur)) : 'Renyah' }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 4px; text-align: center;">Renyah</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; border-right: 1px solid #000000;">- Penampakan</td>
                        <td style="padding: 4px; text-align: center; border-right: 1px solid #000000; font-weight: 700;">
                            @if($isEdit)
                                <select name="items[{{ $qcdtlId }}][fryer_penampakan]" class="excel-cell-input">
                                    <option value="TIDAK_OILSOAKED" {{ ($firstDetail?->fryer_penampakan ?? 'TIDAK_OILSOAKED') === 'TIDAK_OILSOAKED' ? 'selected' : '' }}>Tidak Oilsoaked</option>
                                    <option value="OILSOAKED" {{ ($firstDetail?->fryer_penampakan ?? '') === 'OILSOAKED' ? 'selected' : '' }}>Oilsoaked</option>
                                </select>
                            @else
                                <span style="color: {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? '#dc2626' : '#059669' }};">
                                    {{ $firstDetail?->fryer_penampakan === 'OILSOAKED' ? 'Oilsoaked' : 'Tidak Oilsoaked' }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 4px; text-align: center;">Tidak Oilsoaked</td>
                    </tr>
                </table>
            </div>

            {{-- CHECKLIST KONDISI FISIK SINGKONG --}}
            <div style="padding: 8px 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; align-items: center; font-size: 0.775rem;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_segar]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_segar', $firstDetail?->kondisi_segar) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_segar ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">SEGAR</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_busuk]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_busuk', $firstDetail?->kondisi_busuk) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_busuk ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">BUSUK</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_layu]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_layu', $firstDetail?->kondisi_layu) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_layu ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">LAYU</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_berjamur]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_berjamur', $firstDetail?->kondisi_berjamur) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_berjamur ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">BERJAMUR</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_basah]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_basah', $firstDetail?->kondisi_basah) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_basah ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">BASAH</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_lembek]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_lembek', $firstDetail?->kondisi_lembek) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_lembek ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">TEKSTUR LEMBEK</span>
                </label>

                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_terkelupas]" value="1" {{ old('items.'.$qcdtlId.'.kondisi_terkelupas', $firstDetail?->kondisi_terkelupas) ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $firstDetail?->kondisi_terkelupas ? '✔' : '' }}</span>
                    @endif
                    <span style="font-weight: 800;">TERKELUPAS</span>
                </label>

                <div style="display: flex; align-items: center; gap: 6px; color: #64748b;">
                    <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000;"></span> ....................
                </div>
            </div>
        </div>
    </div>

    {{-- DEFFECT FRYING & TONASE KEDATANGAN & KESIMPULAN --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.35rem; padding: 5px 8px; font-size: 0.76rem;">
        {{-- DEFECT FRYING PERCENTAGES --}}
        <div style="margin-bottom: 0.5rem;">
            <span style="font-weight: 800; text-decoration: underline;">DEFFECT FRYING :</span>
            <span style="margin-left: 0.5rem;">
                @if($isEdit)
                    <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_breakage_persen]" value="{{ old('items.'.$qcdtlId.'.defect_breakage_persen', $firstDetail?->defect_breakage_persen ?? 0) }}" style="width: 55px; border: 1px solid #cbd5e1; padding: 1px 4px;"> Breakage / 
                    <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_cluster_persen]" value="{{ old('items.'.$qcdtlId.'.defect_cluster_persen', $firstDetail?->defect_cluster_persen ?? 0) }}" style="width: 55px; border: 1px solid #cbd5e1; padding: 1px 4px;"> Cluster / 
                    <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_foldover_persen]" value="{{ old('items.'.$qcdtlId.'.defect_foldover_persen', $firstDetail?->defect_foldover_persen ?? 0) }}" style="width: 55px; border: 1px solid #cbd5e1; padding: 1px 4px;"> Foldover / 
                    <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_oilsoaked_persen]" value="{{ old('items.'.$qcdtlId.'.defect_oilsoaked_persen', $firstDetail?->defect_oilsoaked_persen ?? 0) }}" style="width: 55px; border: 1px solid #cbd5e1; padding: 1px 4px;"> Oilsoaked - Polos / 
                    <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_gambos_persen]" value="{{ old('items.'.$qcdtlId.'.defect_gambos_persen', $firstDetail?->defect_gambos_persen ?? 0) }}" style="width: 55px; border: 1px solid #cbd5e1; padding: 1px 4px;"> Gambos (%)
                @else
                    <u>{{ $firstDetail?->defect_breakage_persen !== null ? number_format($firstDetail->defect_breakage_persen, 1) . '%' : '___' }}</u> Breakage / 
                    <u>{{ $firstDetail?->defect_cluster_persen !== null ? number_format($firstDetail->defect_cluster_persen, 1) . '%' : '___' }}</u> Cluster / 
                    <u>{{ $firstDetail?->defect_foldover_persen !== null ? number_format($firstDetail->defect_foldover_persen, 1) . '%' : '___' }}</u> Foldover / 
                    <u>{{ $firstDetail?->defect_oilsoaked_persen !== null ? number_format($firstDetail->defect_oilsoaked_persen, 1) . '%' : '___' }}</u> Oilsoaked - Polos / 
                    <u>{{ $firstDetail?->defect_gambos_persen !== null ? number_format($firstDetail->defect_gambos_persen, 1) . '%' : '___' }}</u> Gambos (%)
                @endif
            </span>
        </div>

        {{-- RINGKASAN TONASE & KESIMPULAN --}}
        <div style="border-top: 1px solid #000000; padding-top: 6px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                <span style="font-weight: 800;">KESIMPULAN</span>
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="kesimpulan_qc" value="TERIMA" {{ old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : '') === 'TERIMA' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}</span>
                    @endif
                    <strong>TERIMA :</strong> 
                    @if($isEdit)
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_netto_lolos]" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $firstDetail?->qty_netto_lolos ?? $totalNetto) }}" style="width: 100px; border: 1px solid #94a3b8; padding: 2px 4px; font-weight: 800; color: #0284c7;"> kg
                    @else
                        <u>{{ number_format($totalNetto, 2, ',', '.') }}</u> kg
                    @endif
                </label>
                <label style="display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                    @if($isEdit)
                        <input type="radio" name="kesimpulan_qc" value="TOLAK" {{ old('kesimpulan_qc', $qc->status_qc === 'DITOLAK_TOTAL' ? 'TOLAK' : '') === 'TOLAK' ? 'checked' : '' }} class="excel-checkbox">
                    @else
                        <span class="excel-box-check">{{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}</span>
                    @endif
                    <strong style="color: #dc2626;">TOLAK :</strong> 
                    @if($isEdit)
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_reject]" value="{{ old('items.'.$qcdtlId.'.qty_reject', $firstDetail?->qty_reject ?? $totalReject) }}" style="width: 80px; border: 1px solid #94a3b8; padding: 2px 4px; color: #dc2626; font-weight: 800;"> kg
                    @else
                        <u>{{ number_format($totalReject, 2, ',', '.') }}</u> kg
                    @endif
                </label>
            </div>

            <div style="font-size: 0.75rem; color: #475569;">
                Bruto: <strong>{{ number_format($totalGross, 0, ',', '.') }} kg</strong> &bull; 
                Refraksi: <strong>{{ number_format($firstDetail?->refraksi_persen ?? 0, 1) }}%</strong>
            </div>
        </div>

        <div style="border-top: 1px solid #000000; margin-top: 6px; padding-top: 4px;">
            <strong>KOMENTAR :</strong> 
            @if($isEdit)
                <input type="text" name="catatan_umum" value="{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}" class="excel-cell-input" style="margin-top: 3px;" placeholder="Catatan hasil sampling kedatangan dan uji goreng singkong...">
            @else
                {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: 'Sampling kedatangan dan uji fryer sesuai standar mutu PT Mirasa.') }}
            @endif
        </div>
    </div>

    {{-- TANDA TANGAN RESMI --}}
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
                    <table style="width: 100%;"><tr><td style="width: 50%; border-right: 1px solid #000; font-weight: 700;">{{ $qc->qc_supervisor_nama ?: 'Kepala Direktur / Supervisor' }}</td><td style="width: 50%;"></td></tr></table>
                </td>
            </tr>
        </table>
    </div>
    <div style="font-size: 0.7rem; margin-top: 4px;">Keterangan : N : Normal, R : Renyah</div>
</div>
