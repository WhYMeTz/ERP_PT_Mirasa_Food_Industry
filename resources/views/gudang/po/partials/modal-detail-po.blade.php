{{-- MODAL POPUP DETAIL DOKUMEN PURCHASE ORDER (QUICK VIEW) --}}
<div id="modalDetailPo" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 10000; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; max-width: 920px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; border: 1px solid #e2e8f0; animation: modalZoomIn 0.18s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div class="modal-header" style="padding: 1.15rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; flex-shrink: 0;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">
                            Detail Dokumen: <span id="poDetailNo" style="color: #0284c7; font-family: monospace;">-</span>
                        </h2>
                        <span id="poDetailStatusBadge" class="badge" style="font-size: 0.75rem; font-weight: 700;">-</span>
                    </div>
                    <small style="color: #64748b; font-size: 0.775rem;">Pratinjau cepat rincian pemesanan, jadwal kirim, rincian bahan, dan penerimaan fisik.</small>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closePoDetailModal()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 6px; line-height: 1;">&times;</button>
        </div>

        {{-- BODY MODAL --}}
        <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1;">
            
            {{-- SPINNER LOADING --}}
            <div id="poDetailLoadingSpinner" style="text-align: center; padding: 3rem 1rem;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: spinPo 0.8s linear infinite;"></div>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 1rem;">Memuat rincian dokumen Purchase Order...</p>
            </div>

            {{-- CONTENT CONTAINER --}}
            <div id="poDetailContentBody" style="display: none;">
                
                {{-- 1. KARTU GRID INFORMASI UTAMA (3 KOLOM) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    
                    {{-- Box 1: Informasi Dokumen --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem;">
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; display: block; margin-bottom: 0.5rem;">
                            📄 Parameter Dokumen
                        </span>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.8125rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Tanggal Terbit:</span>
                                <strong id="poDetailTgl" style="color: #0f172a;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Estimasi Kedatangan (ETA):</span>
                                <strong id="poDetailEta" style="color: #0284c7;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Dibuat Oleh:</span>
                                <span id="poDetailCreatedBy" style="color: #334155; font-weight: 600;">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Box 2: Supplier Mitra --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem;">
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; display: block; margin-bottom: 0.5rem;">
                            🏢 Mitra Supplier
                        </span>
                        <div style="font-size: 0.8125rem;">
                            <strong id="poDetailSupplierNm" style="color: #0f172a; display: block; font-size: 0.875rem;">-</strong>
                            <div style="color: #0284c7; font-family: monospace; font-size: 0.75rem; margin-top: 0.15rem;" id="poDetailSupplierCd">-</div>
                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem;" id="poDetailSupplierAlamat">-</div>
                        </div>
                    </div>

                    {{-- Box 3: Lokasi Gudang Tujuan --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem;">
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; display: block; margin-bottom: 0.5rem;">
                            📍 Gudang Tujuan Bongkar
                        </span>
                        <div style="font-size: 0.8125rem;">
                            <strong id="poDetailGudangNm" style="color: #0f172a; display: block; font-size: 0.875rem;">-</strong>
                            <div style="color: #166534; font-family: monospace; font-size: 0.75rem; margin-top: 0.15rem;" id="poDetailGudangCd">-</div>
                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem;" id="poDetailGudangAlamat">-</div>
                        </div>
                    </div>
                </div>

                {{-- 2. PROGRESS PEMENUHAN FISIK REALISASI --}}
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #166534;">
                            📊 Status Pemenuhan Fisik Kedatangan Barang
                        </span>
                        <span id="poDetailPctText" style="font-size: 0.85rem; font-weight: 800; color: #15803d;">0%</span>
                    </div>
                    <div style="width: 100%; height: 7px; background: #dcfce7; border-radius: 9999px; overflow: hidden;">
                        <div id="poDetailProgressBar" style="width: 0%; height: 100%; background: #22c55e; border-radius: 9999px; transition: width 0.3s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #166534; margin-top: 0.4rem;">
                        <span>Total Pesan: <strong id="poDetailQtyPesan">0</strong></span>
                        <span>Total Diterima: <strong id="poDetailQtyTerima">0</strong></span>
                        <span>Sisa Belum Tiba: <strong id="poDetailQtySisa" style="color: #c2410c;">0</strong></span>
                    </div>
                </div>

                {{-- 3. TABEL RINCIAN BAHAN BAKU & BARANG --}}
                <div style="margin-bottom: 1.25rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Daftar Bahan Baku &amp; Harga Satuan PO</span>
                    </h3>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 700; text-align: left;">
                                    <th style="padding: 0.6rem 0.75rem; width: 40px; text-align: center;">No</th>
                                    <th style="padding: 0.6rem 0.75rem;">Nama Bahan Baku / Barang</th>
                                    <th style="padding: 0.6rem 0.75rem; text-align: center; width: 70px;">Satuan</th>
                                    <th style="padding: 0.6rem 0.75rem; text-align: right; width: 85px;">Qty Pesan</th>
                                    <th style="padding: 0.6rem 0.75rem; text-align: right; width: 85px;">Diterima</th>
                                    <th style="padding: 0.6rem 0.75rem; text-align: right; width: 85px;">Sisa</th>
                                    <th style="padding: 0.6rem 0.75rem; text-align: right; width: 110px;">Harga Satuan</th>
                                    <th style="padding: 0.6rem 0.75rem; text-align: right; width: 125px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="poDetailItemsTbody">
                                {{-- Diisi dinamis via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. FINANCIAL SUMMARY & CATATAN --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                    
                    {{-- Catatan / Syarat Dokumen --}}
                    <div style="background: #fafafa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.85rem 1rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.35rem;">
                            📝 Catatan / Ketentuan PO:
                        </span>
                        <p id="poDetailCatatan" style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.5; font-style: italic;">
                            -
                        </p>
                    </div>

                    {{-- Rekapitulasi Finansial --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem;">
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.8125rem;">
                            <div style="display: flex; justify-content: space-between; color: #64748b;">
                                <span>Subtotal Bruto:</span>
                                <strong id="poDetailSubtotalBruto" style="color: #0f172a;">Rp 0</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: #64748b;">
                                <span>Diskon / Potongan:</span>
                                <span id="poDetailDiskon" style="color: #dc2626;">Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: #64748b;">
                                <span>DPP (Dasar Pengenaan Pajak):</span>
                                <span id="poDetailDpp" style="color: #0f172a;">Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: #64748b;">
                                <span>PPN:</span>
                                <span id="poDetailPpn" style="color: #0f172a;">Rp 0</span>
                            </div>
                            <div style="height: 1px; background: #cbd5e1; margin: 0.25rem 0;"></div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem;">
                                <strong style="color: #0f172a;">Total Tagihan PO:</strong>
                                <strong id="poDetailTotalTagihan" style="color: #0284c7; font-size: 1.05rem;">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. RIWAYAT PENERIMAAN FISIK DI GERBANG (GRN) --}}
                <div id="poDetailGrnSection">
                    <h3 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Riwayat Penerimaan Barang Fisik (Surat Jalan / GRN)</span>
                    </h3>
                    <div id="poDetailGrnList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Diisi dinamis via JS --}}
                    </div>
                </div>

            </div>
        </div>

        {{-- FOOTER MODAL --}}
        <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-size: 0.75rem; color: #94a3b8;">
                ERP PT Mirasa &bull; Sistem Pengadaan Terpadu
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closePoDetailModal()">
                    Tutup
                </button>
                <button type="button" id="btnPoDetailOpenHaccp" onclick="closePoDetailModal(); openPoHaccpModal(window.currentActivePoId)" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #0284c7; font-weight: 700; border-color: #bae6fd; background: #f0f9ff;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Pratinjau Form HACCP</span>
                </button>
                <a id="btnPoDetailPdf" href="#" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #475569;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Cetak PDF</span>
                </a>
                <a id="btnPoDetailFullView" href="#" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Halaman Lengkap</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spinPo {
    to { transform: rotate(360deg); }
}
@keyframes modalZoomIn {
    from {
        opacity: 0;
        transform: scale(0.96);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
