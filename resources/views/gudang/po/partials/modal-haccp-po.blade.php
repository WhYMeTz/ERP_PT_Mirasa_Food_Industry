{{-- MODAL POPUP PRATINJAU DOKUMEN HACCP RESMI (SURAT PERMINTAAN BARANG / PURCHASE ORDER) --}}
<div id="modalHaccpPo" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 10050; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(4px);">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; max-width: 900px; width: 100%; height: 92vh; max-height: 94vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; border: 1px solid #cbd5e1; animation: modalZoomHaccp 0.18s ease-out;">
        
        {{-- HEADER TOOLBAR MODAL (LIGHT THEME MATCHING QC HACCP) --}}
        <div class="modal-header no-print" style="padding: 0.85rem 1.25rem; background: #ffffff; color: #0f172a; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span>Formulir HACCP: Surat Permintaan Barang</span>
                        <span id="haccpModalPoNo" style="font-family: monospace; color: #0284c7; font-size: 0.9rem; font-weight: 800;">-</span>
                    </h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b;">
                        Format Standar Sistem Manajemen Mutu Keamanan Pangan PT Mirasa Food Industry (MFI/HACCP-04/FRM-03/050/VIII/2021)
                    </p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" onclick="printHaccpDocument()" class="btn btn-secondary btn-sm" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak / Print</span>
                </button>
                <a id="btnHaccpDownloadPdf" href="#" target="_blank" class="btn btn-primary btn-sm" style="background: #0284c7; border: 1px solid #0284c7; color: #ffffff; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.75rem; border-radius: 6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download PDF</span>
                </a>
                <button type="button" onclick="closePoHaccpModal()" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; font-size: 1.25rem; font-weight: 700; line-height: 1;" title="Tutup Popup (Esc)">
                    &times;
                </button>
            </div>
        </div>

        {{-- BODY MODAL: CANVAS BERSIH DENGAN LEMBAR KERTAS A4 --}}
        <div class="modal-body" style="flex: 1; padding: 1.5rem; overflow-y: auto; background: #f8fafc; display: flex; justify-content: center;">
            
            {{-- SPINNER LOADING (LIGHT THEME) --}}
            <div id="haccpLoadingSpinner" style="text-align: center; padding: 5rem 1rem; color: #475569;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: spinHaccp 0.8s linear infinite;"></div>
                <p style="font-size: 0.85rem; font-weight: 600; margin-top: 1rem; color: #64748b;">Mempersiapkan lembar formulir HACCP...</p>
            </div>

            {{-- LEMBAR KERTAS A4 PUTIH DENGAN BAYANGAN BERSIH --}}
            <div id="haccpPaperSheet" class="haccp-paper-sheet" style="display: none; background: #ffffff; width: 100%; max-width: 780px; min-height: 980px; padding: 35px 42px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #cbd5e1; color: #000000; font-family: 'Arial', sans-serif; box-sizing: border-box; position: relative;">
                
                {{-- 1. KOP TABEL STANDAR HACCP MIRASA --}}
                <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-bottom: 22px;">
                    <tr>
                        {{-- Kolom Logo --}}
                        <td style="width: 135px; border: 1px solid #000000; text-align: center; vertical-align: middle; padding: 8px 6px;" rowspan="2">
                            <img src="{{ asset('images/logo.png') }}" style="width: 62px; height: auto; display: block; margin: 0 auto;" alt="Logo Cap Payung">
                            <div style="font-size: 7pt; font-weight: 800; color: #dc2626; margin-top: 4px; letter-spacing: 0.04em;">
                                ENAK &bull; GURIH &bull; LEZAT
                            </div>
                        </td>

                        {{-- Kolom Judul Perusahaan (Atas) --}}
                        <td style="border: 1px solid #000000; border-bottom: 1px solid #000000; text-align: center; vertical-align: middle; padding: 8px 12px;">
                            <div style="font-size: 13pt; font-weight: 800; color: #003366; letter-spacing: 0.04em;">
                                PT. MIRASA FOOD INDUSTRY
                            </div>
                        </td>

                        {{-- Kolom Tabel Metadata HACCP (Kanan) --}}
                        <td style="width: 290px; border: 1px solid #000000; padding: 0; vertical-align: top;" rowspan="2">
                            <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                <tr>
                                    <td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px; font-weight: 500; width: 105px;">Nomor dokumen</td>
                                    <td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 4px; text-align: center; width: 10px;">:</td>
                                    <td style="border-bottom: 1px solid #000000; padding: 4px 6px; font-size: 7.75pt;">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px; font-weight: 500;">Terbitan/Tgl</td>
                                    <td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 4px; text-align: center;">:</td>
                                    <td style="border-bottom: 1px solid #000000; padding: 4px 6px; font-weight: 500;">19-08-2021</td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 6px; font-weight: 500;">Revisi</td>
                                    <td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000; padding: 4px 4px; text-align: center;">:</td>
                                    <td style="border-bottom: 1px solid #000000; padding: 4px 6px; font-weight: 500;">00</td>
                                </tr>
                                <tr>
                                    <td style="border-right: 1px solid #000000; padding: 4px 6px; font-weight: 500;">Halaman</td>
                                    <td style="border-right: 1px solid #000000; padding: 4px 4px; text-align: center;">:</td>
                                    <td style="padding: 4px 6px; font-weight: 500;">1</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        {{-- Kolom Form Permintaan Barang (Bawah) --}}
                        <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; padding: 6px 10px;">
                            <div style="font-size: 11.5pt; font-weight: 800; line-height: 1.25;">
                                Form<br>Permintaan Barang
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- 2. NO PO (KIRI) & KEPADA YTH (KANAN) --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; font-size: 10pt;">
                            <div style="font-weight: 700;">No.</div>
                            <div style="display: flex; gap: 8px; margin-top: 2px;">
                                <span style="font-weight: 700;">PO</span>
                                <span style="font-weight: 700;">:</span>
                                <span id="haccpSheetPoNo" style="font-weight: 800; font-family: monospace; font-size: 10.5pt;">-</span>
                            </div>
                        </td>
                        <td style="width: 50%; vertical-align: top; font-size: 10pt; padding-left: 20px;">
                            <div style="font-weight: 700;">Kepada Yth:</div>
                            <div id="haccpSheetSupplierNm" style="font-weight: 800; font-size: 10.5pt; margin-top: 2px;">-</div>
                            <div id="haccpSheetSupplierAlamat" style="font-size: 9pt; color: #1e293b; margin-top: 1px;">-</div>
                        </td>
                    </tr>
                </table>

                {{-- 3. JUDUL SURAT --}}
                <div style="text-align: center; margin-bottom: 22px;">
                    <div style="font-size: 12.5pt; font-weight: 800; text-decoration: underline; letter-spacing: 0.05em;">
                        SURAT PERMINTAAN BARANG
                    </div>
                    <div style="font-size: 11pt; font-weight: 800; letter-spacing: 0.05em; margin-top: 2px;">
                        PURCHASE ORDER
                    </div>
                </div>

                {{-- 4. TABEL BARANG PERSIS SEPERTI GAMBAR ASLI --}}
                <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-bottom: 25px;">
                    <thead>
                        <tr>
                            <th style="width: 48px; border: 1px solid #000000; border-bottom: 2.5px solid #000000; padding: 6px; font-size: 10pt; font-weight: 800; text-align: center;">NO</th>
                            <th style="border: 1px solid #000000; border-bottom: 2.5px solid #000000; padding: 6px 12px; font-size: 10pt; font-weight: 800; text-align: center;">NAMA BARANG</th>
                            <th style="width: 150px; border: 1px solid #000000; border-bottom: 2.5px solid #000000; padding: 6px 10px; font-size: 10pt; font-weight: 800; text-align: center;">JUMLAH</th>
                            <th style="width: 200px; border: 1px solid #000000; border-bottom: 2.5px solid #000000; padding: 6px 10px; font-size: 10pt; font-weight: 800; text-align: center;">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody id="haccpSheetItemsBody">
                        {{-- Diisi secara dinamis via JS --}}
                    </tbody>
                </table>

                {{-- 5. TANDA TANGAN (KANAN) & NB (KIRI) --}}
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        {{-- Kiri: NB Pengiriman --}}
                        <td style="width: 52%; vertical-align: top; font-size: 9.5pt; line-height: 1.4;">
                            <div style="font-weight: 800;">NB.</div>
                            <div>Barang di kirim ke alamat:</div>
                            <div style="font-weight: 800; font-size: 10pt;">PT. MIRASA FOOD INDUSTRY</div>
                            <div style="font-weight: 800;" id="haccpSheetGudangCity">Magelang</div>
                            <div style="font-size: 8.5pt; color: #334155;" id="haccpSheetGudangAlamat">-</div>
                        </td>

                        {{-- Kanan: Tanda Tangan Mengetahui --}}
                        <td style="width: 48%; text-align: center; vertical-align: top; font-size: 10pt;">
                            <div style="font-weight: 700;">
                                Magelang, <span id="haccpSheetTgl">-</span>
                            </div>
                            <div style="font-weight: 700; margin-top: 2px;">
                                Mengetahui
                            </div>
                            <div style="height: 70px;"></div>
                            <div style="font-size: 10pt; font-weight: 700; letter-spacing: 0.1em;">
                                ..............................
                            </div>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

        {{-- FOOTER MODAL (LIGHT THEME) --}}
        <div class="modal-footer no-print" style="padding: 0.75rem 1.25rem; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closePoHaccpModal()" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 600; padding: 0.4rem 0.9rem; border-radius: 6px;">
                Tutup Dokumen
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spinHaccp {
    to { transform: rotate(360deg); }
}
@keyframes modalZoomHaccp {
    from {
        opacity: 0;
        transform: scale(0.97);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
@media print {
    body * {
        visibility: hidden;
    }
    #haccpPaperSheet, #haccpPaperSheet * {
        visibility: visible;
    }
    #haccpPaperSheet {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>
