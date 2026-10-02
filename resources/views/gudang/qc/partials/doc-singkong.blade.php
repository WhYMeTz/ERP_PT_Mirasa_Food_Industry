{{-- ========================================================================= --}}
{{-- DOKUMEN CHECKLIST STANDAR KEBETERIMAAN BAHAN BAKU: SINGKONG               --}}
{{-- NO. DOKUMEN: MFI/HACCP-04/FRM-03/048/VIII/2021 (PENGUJIAN I & II)        --}}
{{-- ========================================================================= --}}

{{-- LEMBAR 1: PENGUJIAN I (SAMPLING KEDATANGAN) --}}
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

{{-- LEMBAR 2: PENGUJIAN II (FRYER & DEFECT) --}}
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
