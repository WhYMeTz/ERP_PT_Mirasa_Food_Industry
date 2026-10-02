{{-- ========================================================================= --}}
{{-- DOKUMEN CHECKLIST PEMERIKSAAN KEDATANGAN PLASTIK                          --}}
{{-- NO. DOKUMEN: MFI/HACCP-04/FRM-03/030/VIII/2021                            --}}
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
                        Cheklist Pemeriksaan Kedatangan Plastik
                    </div>
                </td>
                <td style="width: 250px; vertical-align: middle; padding: 0; border-left: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.72rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 3px 6px; font-weight: 700; width: 85px; border-right: 1px solid #000000;">No. Dokumen</td>
                            <td style="padding: 3px 6px; font-weight: 800;">MFI/HACCP-04/FRM-03/030/VIII/2021</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Revisi</td>
                            <td style="padding: 3px 6px;">1</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 3px 6px; font-weight: 700; border-right: 1px solid #000000;">Tanggal Terbit</td>
                            <td style="padding: 3px 6px;">11-09-2023</td>
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

    {{-- TABEL IDENTITAS KEDATANGAN PLASTIK --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
            <tr>
                <td style="width: 40%; vertical-align: middle; text-align: center; padding: 0.85rem 0.5rem; border-right: 2px solid #000000; border-bottom: 2px solid #000000;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #475569; letter-spacing: 0.05em;">MIRASA FOOD INDUSTRY</div>
                    <div style="font-size: 0.8rem; font-weight: 700; margin-top: 2px;">LAPORAN KEDATANGAN</div>
                    <div style="font-size: 1.35rem; font-weight: 900; margin-top: 3px; color: #000000; letter-spacing: 0.04em;">
                        PLASTIK
                    </div>
                </td>
                <td style="width: 60%; padding: 0; vertical-align: top; border-bottom: 2px solid #000000;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.78rem;">
                        <tr style="border-bottom: 1px solid #000000;">
                            <td style="padding: 4px 8px; width: 130px; font-weight: 700; border-right: 1px solid #000000;">Nama Bahan / Barang</td>
                            <td colspan="3" style="padding: 4px 8px; font-weight: 800;">: {{ $qc->details->pluck('barang.barang_nm')->filter()->first() ?: ($qc->nama_jenis ?: 'Plastik Kemasan') }}</td>
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
                    <strong>Jumlah Sample:</strong> {{ $qc->jumlah_sample_pcs ? $qc->jumlah_sample_pcs . ' pcs' : '10 pcs' }}
                </td>
                <td style="padding: 5px 10px;">
                    <strong>Nomor DO :</strong> {{ $qc->nomor_do ?: ($qc->surat_jalan_supplier ?: '-') }} &bull; Plat: {{ $qc->plat_nomor_truk ?: '-' }} ({{ $qc->sopir_nama ?: '-' }})
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

    {{-- SPESIFIK PLASTIK: KETEBALAN & CACAT KEMASAN --}}
    <div style="border: 2px solid #000000; margin-bottom: 0.75rem;">
        <div style="display: flex; border-bottom: 2px solid #000000; padding: 6px 10px; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-weight: 800;">2. ISI RAW MATERIAL</span>
                <span style="display: inline-flex; align-items: center; gap: 4px;">
                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                        {{ ($firstDetail?->status_raw_material ?? 'OK') === 'OK' ? '✔' : '' }}
                    </span> OK
                </span>
                <span style="display: inline-flex; align-items: center; gap: 4px;">
                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                        {{ ($firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? '✔' : '' }}
                    </span> TDK STD
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-weight: 800;">KEMASAN</span>
                <span style="display: inline-flex; align-items: center; gap: 4px;">
                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                        {{ ($firstDetail?->kemasan_kondisi ?? 'OK') === 'OK' ? '✔' : '' }}
                    </span> OK
                </span>
                <span style="display: inline-flex; align-items: center; gap: 4px;">
                    <span style="display: inline-block; width: 15px; height: 15px; border: 1.5px solid #000; text-align: center; line-height: 13px; font-weight: 900;">
                        {{ ($firstDetail?->kemasan_kondisi ?? '') === 'TIDAK_STANDARD' ? '✔' : '' }}
                    </span> TIDAK STANDARD
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 1fr; border-bottom: 2px solid #000000;">
            <div style="border-right: 2px solid #000000;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 0.78rem;">
                    <tr style="background: #e2e8f0; font-weight: 800; border-bottom: 1px solid #000000;">
                        <td style="padding: 6px; border-right: 1px solid #000000; width: 35%;">Parameter</td>
                        <td style="padding: 6px; border-right: 1px solid #000000; width: 35%;">Hasil Analisa</td>
                        <td style="padding: 6px; width: 30%;">Standard</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 700;">KETEBALAN</td>
                        <td style="padding: 6px; border-right: 1px solid #000000; font-weight: 800;">
                            {{ $firstDetail?->ketebalan_analisa ?: '0.08 mm' }}
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

            <div style="padding: 10px 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; align-items: center; font-size: 0.8rem;">
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

        <div style="padding: 6px 10px; border-bottom: 2px solid #000000; font-size: 0.78rem;">
            <span style="font-weight: 800; text-decoration: underline;">KOMENTAR :</span>
            <div style="margin-top: 3px; min-height: 22px;">
                {{ $firstDetail?->catatan_dtl ?: ($qc->catatan_umum ?: 'Kualitas Plastik Sesuai Standar HACCP') }}
            </div>
        </div>

        <div style="padding: 6px 10px; display: flex; align-items: center; gap: 2rem; font-size: 0.8rem;">
            <span style="font-weight: 800;">KESIMPULAN</span>
            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                    {{ $qc->status_qc !== 'DITOLAK_TOTAL' ? '✔' : '' }}
                </span> TERIMA : {{ number_format($totalNetto, 0, ',', '.') }} pcs
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 800;">
                <span style="display: inline-block; width: 16px; height: 16px; border: 1.5px solid #000; text-align: center; line-height: 14px; font-weight: 900;">
                    {{ $qc->status_qc === 'DITOLAK_TOTAL' ? '✔' : '' }}
                </span> TOLAK : {{ number_format($totalReject, 0, ',', '.') }} pcs
            </span>
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
