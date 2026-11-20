{{-- MODAL POPUP PRATINJAU DOKUMEN HACCP RESMI (PURCHASE ORDER RESMI PT MIRASA) --}}
<div id="modalHaccpPo" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 10050; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(4px);">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; max-width: 920px; width: 100%; height: 92vh; max-height: 94vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; border: 1px solid #cbd5e1; animation: modalZoomHaccp 0.18s ease-out;">
        
        {{-- HEADER TOOLBAR MODAL --}}
        <div class="modal-header no-print" style="padding: 0.85rem 1.25rem; background: #ffffff; color: #0f172a; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span>Form Permintaan Barang (Purchase Order)</span>
                        <span id="haccpModalPoNo" style="font-family: monospace; color: #dc2626; font-size: 0.95rem; font-weight: 800;">-</span>
                    </h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b;">
                        Dokumen Resmi PT Mirasa Food Industry &bull; Dok: MFI/HACCP-04/FRM-03/050/VIII/2021
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

        {{-- BODY MODAL: KERTAS DOKUMEN --}}
        <div class="modal-body" style="flex: 1; padding: 1.5rem; overflow-y: auto; background: #f8fafc; display: flex; justify-content: center;">
            
            {{-- SPINNER LOADING --}}
            <div id="haccpLoadingSpinner" style="text-align: center; padding: 5rem 1rem; color: #475569;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: spinHaccp 0.8s linear infinite;"></div>
                <p style="font-size: 0.85rem; font-weight: 600; margin-top: 1rem; color: #64748b;">Mempersiapkan lembar formulir Purchase Order...</p>
            </div>

            {{-- LEMBAR KERTAS PO MIRASA --}}
            <div id="haccpPaperSheet" class="haccp-paper-sheet" style="display: none; background: #ffffff; width: 100%; max-width: 800px; min-height: 980px; padding: 35px 42px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #cbd5e1; color: #000000; font-family: 'Helvetica', 'Arial', sans-serif; box-sizing: border-box; position: relative;">
                
                {{-- 1. KOP SURAT HACCP --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
                    <tr>
                        <td style="width: 85px; vertical-align: middle; text-align: center; padding-right: 4px;">
                            <img src="{{ asset('images/logo.png') }}" style="width: 50px; height: auto; display: inline-block; margin: 0 auto;" alt="Logo Cap Payung">
                            <div style="font-family: Arial, Helvetica, sans-serif; font-size: 5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.01em; text-align: center; white-space: nowrap;">
                                <nobr>ENAK &bull; GURIH &bull; LEZAT</nobr>
                            </div>
                        </td>
                        <td style="vertical-align: middle; padding-left: 5px;">
                            <div style="font-size: 13pt; font-weight: 800; color: #000000; letter-spacing: 0.03em;">PT MIRASA FOOD INDUSTRY</div>
                            <div style="font-size: 11pt; font-weight: 700; color: #000000; margin-top: 3px;">FORM PERMINTAAN BARANG</div>
                        </td>
                        <td style="width: 255px; vertical-align: top;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                                <tr>
                                    <td style="width: 95px; font-weight: 600; padding: 2px 3px;">Nomor dokumen</td>
                                    <td style="width: 8px; text-align: center; padding: 2px 3px;">:</td>
                                    <td style="font-weight: 600; padding: 2px 3px;">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; padding: 2px 3px;">Terbitan/Tgl</td>
                                    <td style="text-align: center; padding: 2px 3px;">:</td>
                                    <td style="font-weight: 600; padding: 2px 3px;">19-08-2021</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; padding: 2px 3px;">Revisi</td>
                                    <td style="text-align: center; padding: 2px 3px;">:</td>
                                    <td style="font-weight: 600; padding: 2px 3px;">0</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; padding: 2px 3px;">Halaman</td>
                                    <td style="text-align: center; padding: 2px 3px;">:</td>
                                    <td style="font-weight: 600; padding: 2px 3px;">1</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <hr style="border: none; border-top: 2px solid #000000; margin: 4px 0 12px 0;">

                {{-- 2. JUDUL DOKUMEN --}}
                <div style="text-align: center; font-size: 14pt; font-weight: 800; letter-spacing: 0.06em; margin-bottom: 12px; color: #000000;">
                    PURCHASE ORDER
                </div>

                {{-- 3. PARAMETER PO & KOTAK SUPPLIER --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td style="width: 48%; vertical-align: top;">
                            <table style="border-collapse: collapse; font-size: 9pt;">
                                <tr>
                                    <td style="width: 65px; font-weight: 600; padding: 2px 4px;">Quot No</td>
                                    <td style="width: 10px; text-align: center; padding: 2px 4px;">:</td>
                                    <td style="padding: 2px 4px;">-</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; padding: 2px 4px;">PO No.</td>
                                    <td style="text-align: center; font-weight: 700; padding: 2px 4px;">:</td>
                                    <td style="padding: 2px 4px;">
                                        <span id="haccpSheetPoNo" style="color: #dc2626; font-weight: 800; font-size: 10pt; font-family: 'Courier New', Courier, monospace;">-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; padding: 2px 4px;">Tanggal</td>
                                    <td style="text-align: center; padding: 2px 4px;">:</td>
                                    <td style="padding: 2px 4px; font-weight: 700;" id="haccpSheetTgl">-</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 4%;"></td>
                        <td style="width: 48%; vertical-align: top;">
                            <div style="border: 1px solid #000000; padding: 6px 10px; background: #ffffff; font-size: 8.5pt; line-height: 1.35; min-height: 65px;">
                                <strong id="haccpSheetSupplierNm" style="font-size: 9.5pt; text-transform: uppercase;">-</strong>
                                <div id="haccpSheetSupplierAlamat">-</div>
                                <table style="width: 100%; border-collapse: collapse; margin-top: 3px; font-size: 8.5pt;">
                                    <tr>
                                        <td style="width: 45px; padding: 0;">Up</td>
                                        <td style="width: 8px; padding: 0;">:</td>
                                        <td style="padding: 0;" id="haccpSheetSupplierUp">-</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0;">Telpon</td>
                                        <td style="padding: 0;">:</td>
                                        <td style="padding: 0;" id="haccpSheetSupplierTelp">-</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- 4. KOTAK SHIP TO & INVOICE TO --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                    <tr>
                        <td style="width: 48%; border: 1px solid #000000; padding: 6px 10px; font-size: 8.5pt; line-height: 1.35; vertical-align: top;">
                            <strong style="text-decoration: underline;">Ship To :</strong><br>
                            <strong>PT MIRASA FOOD INDUSTRY</strong><br>
                            JL. Munggur No. 2 Kalangan Wetan, Ambartawang<br>
                            Mungkid, Magelang, Jawa Tengah<br>
                            Telp : 0293 3280554, 3280556
                        </td>
                        <td style="width: 4%;"></td>
                        <td style="width: 48%; border: 1px solid #000000; padding: 6px 10px; font-size: 8.5pt; line-height: 1.35; vertical-align: top;">
                            <strong style="text-decoration: underline;">Invoice To :</strong><br>
                            <strong>PT MIRASA FOOD INDUSTRY</strong><br>
                            JL. Munggur No. 2 Kalangan Wetan, Ambartawang<br>
                            Mungkid, Magelang, Jawa Tengah<br>
                            Telp : 0293 3280554, 3280556<br>
                            NPWP : 07.635.700.4-152.000
                        </td>
                    </tr>
                </table>

                {{-- 5. TABEL RINCIAN ITEM BARANG --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 0;">
                    <thead>
                        <tr style="background-color: #d1d5db; color: #000000; font-size: 8.5pt;">
                            <th style="width: 32px; border: 1px solid #000000; padding: 5px 4px; text-align: center;">No</th>
                            <th style="border: 1px solid #000000; padding: 5px 8px; text-align: left;">Description</th>
                            <th style="width: 70px; border: 1px solid #000000; padding: 5px 6px; text-align: right;">Qty</th>
                            <th style="width: 48px; border: 1px solid #000000; padding: 5px 4px; text-align: center;">Sat</th>
                            <th style="width: 105px; border: 1px solid #000000; padding: 5px 6px; text-align: right;">Unit Price</th>
                            <th style="width: 120px; border: 1px solid #000000; padding: 5px 8px; text-align: right;">Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="haccpSheetItemsBody">
                        {{-- Diisi secara dinamis via JS --}}
                    </tbody>
                </table>

                {{-- 6. NOTE & TOTAL SECTION --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; border-top: 1px solid #000000; margin-bottom: 10px;">
                    <tr>
                        <td style="width: 62%; border: 1px solid #000000; padding: 5px 8px; vertical-align: top; font-size: 8.5pt; line-height: 1.4;">
                            <strong>NOTE :</strong>
                            <span id="haccpSheetNoteEta" style="font-weight: 700; color: #000000;">*Mohon dikirim sesuai tanggal estimasi</span>
                            <div id="haccpSheetCatatan" style="margin-top: 2px; font-size: 8pt; color: #374151;"></div>
                        </td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 4px 6px; vertical-align: top; font-size: 8.5pt;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                                <tr>
                                    <td style="font-weight: 700; width: 85px; padding: 2px 4px;">Total</td>
                                    <td style="width: 15px; padding: 2px 4px;">Rp</td>
                                    <td style="text-align: right; font-weight: 700; padding: 2px 4px;" id="haccpSheetSubtotal">0</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; padding: 2px 4px;">PPN</td>
                                    <td style="padding: 2px 4px;">Rp</td>
                                    <td style="text-align: right; font-weight: 700; padding: 2px 4px;" id="haccpSheetPpn">0</td>
                                </tr>
                                <tr style="border-top: 1px solid #000000;">
                                    <td style="font-weight: 800; font-size: 9pt; padding: 2px 4px;">Grand Total</td>
                                    <td style="font-weight: 800; font-size: 9pt; padding: 2px 4px;">Rp</td>
                                    <td style="text-align: right; font-weight: 800; font-size: 9.5pt; padding: 2px 4px;" id="haccpSheetGrandTotal">0</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- 7. TERM OF PAYMENT & FRANCO BOX --}}
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 12px; font-size: 8.5pt;">
                    <tr>
                        <td style="width: 140px; font-weight: 700; border: 1px solid #000000; padding: 3px 8px;">Term of Payment</td>
                        <td style="width: 10px; text-align: center; border: 1px solid #000000; padding: 3px 2px;">:</td>
                        <td style="border: 1px solid #000000; padding: 3px 8px;" id="haccpSheetTop">Transfer</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 3px 8px;">Franco</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 3px 2px;">:</td>
                        <td style="border: 1px solid #000000; padding: 3px 8px;">Franco Gudang Magelang</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 3px 8px;">Remark</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 3px 2px;">:</td>
                        <td style="border: 1px solid #000000; padding: 3px 8px;" id="haccpSheetRemark">-</td>
                    </tr>
                </table>

                {{-- 8. TANDA TANGAN & FOOTNOTE NB FAKTUR PAJAK --}}
                <table style="width: 100%; border-collapse: collapse; margin-top: 6px;">
                    <tr>
                        <td style="width: 60%; vertical-align: bottom;">
                            <div style="font-size: 8pt; line-height: 1.35; color: #000000; margin-top: 15px;">
                                <div style="font-weight: 800; font-size: 8.5pt; margin-bottom: 2px;">NB.</div>
                                <div>Barang di kirim ke alamat:</div>
                                <div style="font-weight: 800; font-size: 8.5pt;">PT. MIRASA FOOD INDUSTRY</div>
                                <div>Magelang</div>
                                <div style="margin-top: 4px; font-size: 7.5pt; color: #334155; line-height: 1.35;">
                                    Faktur Pajak a.n. <strong>PT. MIRASA FOOD INDUSTRY</strong> (NPWP 07.635.700.4-152.000)<br>
                                    Pembayaran via BCA <strong>104-9173332</strong> a.n. PT. MIRASA FOOD INDUSTRY
                                </div>
                            </div>
                        </td>
                        <td style="width: 40%; text-align: center; vertical-align: top;">
                            <div style="font-size: 9pt; font-weight: 600; margin-bottom: 4px;">Hormat Kami,</div>
                            <div style="height: 48px;" id="haccpSheetStampArea"></div>
                            <div style="font-size: 9.5pt; font-weight: 800; text-decoration: underline; margin-top: 4px;" id="haccpSheetApprover">
                                Habieb Soleh Yoenanto
                            </div>
                            <div style="font-size: 7.5pt; color: #4b5563;">PT Mirasa Food Industry</div>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

        {{-- FOOTER MODAL --}}
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
