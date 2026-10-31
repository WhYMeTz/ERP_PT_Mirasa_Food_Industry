{{-- MODAL POPUP DETAIL DOKUMEN PURCHASE ORDER (macOS Window Style ERP Mirasa) --}}
<div id="modalDetailPo" class="modal-backdrop" onclick="if(event.target === this) closePoDetailModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10000; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(5px);">
    <div class="modal-dialog mac-po-dialog">
        
        {{-- 1. macOS Window Titlebar --}}
        <div class="mac-po-titlebar">
            {{-- Window Controls: Traffic Lights --}}
            <div class="mac-po-traffic" aria-label="Window Controls">
                <button type="button" class="mac-po-dot red" onclick="closePoDetailModal()" title="Tutup (Esc)">
                    <span class="mac-po-icon">&times;</span>
                </button>
                <button type="button" class="mac-po-dot yellow" onclick="closePoDetailModal()" title="Minimalkan">
                    <span class="mac-po-icon">&minus;</span>
                </button>
                <button type="button" class="mac-po-dot green" onclick="togglePoDetailMaximize()" title="Maksimalkan / Normal">
                    <span class="mac-po-icon">&plus;</span>
                </button>
            </div>

            {{-- Centered macOS Window Title --}}
            <div class="mac-po-title">
                <svg width="15" height="15" fill="none" stroke="#0284c7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Dokumen PO: <strong id="poDetailNo" style="font-family: monospace; color: #0284c7;">-</strong></span>
                <span id="poDetailStatusBadge" class="mac-po-title-chip">-</span>
            </div>

            {{-- Right Controls: Buka Detail Full & Esc Shortcut --}}
            <div class="mac-po-actions-right">
                <a id="btnPoDetailFullHeader" href="#" class="mac-po-btn-full" title="Buka Halaman Rincian Lengkap PO">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Detail Full</span>
                </a>
                <kbd class="mac-po-kbd">esc</kbd>
            </div>
        </div>

        {{-- 2. macOS Window Body --}}
        <div class="mac-po-body">
            
            {{-- SPINNER LOADING --}}
            <div id="poDetailLoadingSpinner" style="text-align: center; padding: 4rem 1rem;">
                <div style="display: inline-block; width: 42px; height: 42px; border: 3px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: spinPo 0.8s linear infinite;"></div>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 1rem; font-weight: 500;">Memuat rincian Purchase Order &amp; kalkulasi finansial...</p>
            </div>

            {{-- CONTENT CONTAINER --}}
            <div id="poDetailContentBody" style="display: none;">
                
                {{-- BANNER PERINGATAN REVISI / PENOLAKAN DARI ATASAN (JIKA ADA) --}}
                <div id="poDetailRejectBanner" style="display: none; background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 10px; padding: 0.95rem 1.25rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <div style="width: 32px; height: 32px; border-radius: 6px; background: #ffe4e6; display: flex; align-items: center; justify-content: center; color: #e11d48; flex-shrink: 0; margin-top: 2px;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <strong style="color: #9f1239; font-size: 0.875rem;">Catatan Penolakan / Permintaan Revisi dari Atasan</strong>
                                <span id="poDetailRejectDate" style="font-size: 0.75rem; color: #be123c;">-</span>
                            </div>
                            <div style="margin-top: 0.35rem; font-size: 0.825rem; color: #881337;">
                                <strong>Direksi / Reviewer:</strong> <span id="poDetailRejectAuthor">-</span>
                            </div>
                            <div style="margin-top: 0.35rem; padding: 0.5rem 0.75rem; background: #ffffff; border: 1px solid #fda4af; border-radius: 6px; font-size: 0.8125rem; color: #9f1239; font-style: italic;" id="poDetailRejectReason">
                                -
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 1. KARTU GRID INFORMASI UTAMA (4 KOLOM macOS CARDS) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    
                    {{-- Box 1: Informasi Dokumen & Waktu --}}
                    <div class="mac-po-card">
                        <span class="mac-po-card-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Parameter Dokumen</span>
                        </span>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.8rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Tgl Terbit:</span>
                                <strong id="poDetailTgl" style="color: #0f172a;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Estimasi Tiba (ETA):</span>
                                <strong id="poDetailEta" style="color: #0284c7;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Dibuat Oleh:</span>
                                <span id="poDetailCreatedBy" style="color: #334155; font-weight: 600;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Termin (TOP):</span>
                                <strong id="poDetailTopHeader" style="color: #059669;">-</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Box 2: Mitra Supplier --}}
                    <div class="mac-po-card">
                        <span class="mac-po-card-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Mitra Supplier</span>
                        </span>
                        <div style="font-size: 0.8rem;">
                            <strong id="poDetailSupplierNm" style="color: #0f172a; display: block; font-size: 0.85rem;">-</strong>
                            <div style="color: #0284c7; font-family: monospace; font-size: 0.75rem; margin-top: 0.15rem;" id="poDetailSupplierCd">-</div>
                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem;" id="poDetailSupplierAlamat">-</div>
                        </div>
                    </div>

                    {{-- Box 3: Lokasi Gudang Tujuan --}}
                    <div class="mac-po-card">
                        <span class="mac-po-card-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Gudang Penerima</span>
                        </span>
                        <div style="font-size: 0.8rem;">
                            <strong id="poDetailGudangNm" style="color: #0f172a; display: block; font-size: 0.85rem;">-</strong>
                            <div style="color: #166534; font-family: monospace; font-size: 0.75rem; margin-top: 0.15rem;" id="poDetailGudangCd">-</div>
                            <div style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem;" id="poDetailGudangAlamat">-</div>
                        </div>
                    </div>

                    {{-- Box 4: Alur Persetujuan --}}
                    <div class="mac-po-card">
                        <span class="mac-po-card-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Status Approval</span>
                        </span>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.8rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Pemberi Izin:</span>
                                <strong id="poDetailApproverNm" style="color: #0f172a;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Tgl Disetujui:</span>
                                <span id="poDetailApprovedAt" style="color: #059669; font-weight: 600;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Skema PPN:</span>
                                <strong id="poDetailTaxSchemeHeader" style="color: #0284c7;">-</strong>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 2. PROGRESS PEMENUHAN FISIK REALISASI --}}
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <span style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #166534; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Status Pemenuhan Fisik Kedatangan Barang
                        </span>
                        <span id="poDetailPctText" style="font-size: 0.85rem; font-weight: 800; color: #15803d;">0%</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: #f1f5f9; border-radius: 9999px; overflow: hidden;">
                        <div id="poDetailProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); border-radius: 9999px; transition: width 0.35s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #334155; margin-top: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                        <span>Total Pesan: <strong id="poDetailQtyPesan" style="color: #0f172a;">0</strong></span>
                        <span>Total Diterima: <strong id="poDetailQtyTerima" style="color: #059669;">0</strong></span>
                        <span>Sisa Belum Tiba: <strong id="poDetailQtySisa" style="color: #ea580c;">0</strong></span>
                    </div>
                </div>

                {{-- 3. TABEL RINCIAN BAHAN BAKU & FINANSIAL LENGKAP --}}
                <div class="mac-po-table-wrap">
                    <div style="padding: 0.75rem 1rem; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Rincian Item Komoditas &amp; Skema Finansial
                        </span>
                        <span id="poDetailTableBadgeCount" class="mac-badge-pill" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                            0 Item Terdaftar
                        </span>
                    </div>
                    <div style="overflow: hidden;">
                        <table class="mac-po-table">
                            <thead>
                                <tr>
                                    <th style="width: 28px; text-align: center;">#</th>
                                    <th style="text-align: left;">Komoditas &amp; Kode</th>
                                    <th style="width: 55px; text-align: center;">Satuan</th>
                                    <th style="width: 70px; text-align: right;">Qty</th>
                                    <th style="width: 80px; text-align: right;">Terima / Sisa</th>
                                    <th style="width: 75px; text-align: right;">Bruto</th>
                                    <th style="width: 85px; text-align: right;">Diskon / Pot</th>
                                    <th style="width: 75px; text-align: right;">Netto</th>
                                    <th style="width: 90px; text-align: right;">Kode PPN</th>
                                    <th style="width: 95px; text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="poDetailItemsTbody">
                                {{-- Diisi dinamis via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. FINANCIAL SUMMARY & CATATAN (macOS INSPECTOR STYLE) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    
                    {{-- Kolom Kiri: Catatan & Skema Pajak Dokumen --}}
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        {{-- Kartu Skema Pajak & Ketentuan Pembayaran --}}
                        <div class="mac-po-card">
                            <span class="mac-po-card-label">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Informasi Skema Perpajakan &amp; TOP</span>
                            </span>
                            <div style="display: flex; flex-direction: column; gap: 0.45rem; font-size: 0.8125rem;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #64748b;">Perlakuan Pajak:</span>
                                    <strong id="poDetailTaxTreatment" style="color: #0f172a;">-</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #64748b;">Jenis Pajak Dikenakan:</span>
                                    <span id="poDetailTaxName" style="color: #0284c7; font-weight: 700;">-</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #64748b;">Tarif Pajak (%):</span>
                                    <span id="poDetailTaxRate" style="color: #0f172a; font-weight: 600;">-</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #64748b;">Termin Pembayaran:</span>
                                    <span id="poDetailTopVal" style="color: #059669; font-weight: 700;">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- Kartu Catatan / Syarat Dokumen --}}
                        <div class="mac-po-card">
                            <span class="mac-po-card-label">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span>Catatan &amp; Remark Dokumen</span>
                            </span>
                            <p id="poDetailCatatan" style="font-size: 0.8rem; color: #475569; margin: 0; line-height: 1.5; white-space: pre-line;">
                                Tidak ada catatan tambahan untuk dokumen PO ini.
                            </p>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Rincian Lengkap Finansial (macOS Inspector) --}}
                    <div class="mac-fin-summary-card">
                        <span class="mac-po-card-label">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Kalkulasi Tagihan &amp; Finansial</span>
                        </span>
                        
                        <div style="display: flex; flex-direction: column;">
                            <div class="mac-fin-row">
                                <span style="color: #64748b;">Subtotal Bruto (Kotor):</span>
                                <strong id="poDetailSubtotalBruto" style="color: #0f172a;">Rp 0</strong>
                            </div>
                            <div class="mac-fin-row">
                                <div>
                                    <span style="color: #64748b;">Total Diskon Item:</span>
                                    <small id="poDetailDiskonPercentNote" style="display: block; font-size: 0.7rem; color: #94a3b8;"></small>
                                </div>
                                <span id="poDetailDiskon" style="color: #dc2626; font-weight: 600;">- Rp 0</span>
                            </div>
                            <div class="mac-fin-row">
                                <span style="color: #64748b;">Potongan Tambahan / Kas:</span>
                                <span id="poDetailPotongan" style="color: #dc2626; font-weight: 600;">- Rp 0</span>
                            </div>
                            <div class="mac-fin-row" style="background: #f8fafc; margin: 0.2rem -0.5rem; padding: 0.45rem 0.5rem; border-radius: 6px;">
                                <span style="color: #334155; font-weight: 700;">DPP (Dasar Pengenaan Pajak):</span>
                                <strong id="poDetailDpp" style="color: #0f172a;">Rp 0</strong>
                            </div>
                            <div class="mac-fin-row">
                                <div>
                                    <span style="color: #64748b;">Pajak (PPN / PPh):</span>
                                    <small id="poDetailTaxSubNote" style="display: block; font-size: 0.7rem; color: #0284c7;"></small>
                                </div>
                                <span id="poDetailPpn" style="color: #0f172a; font-weight: 700;">Rp 0</span>
                            </div>
                            
                            {{-- Grand Total Box --}}
                            <div class="mac-fin-total-box">
                                <div>
                                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #047857; display: block;">
                                        Total Tagihan Resmi PO
                                    </span>
                                    <small style="color: #059669; font-size: 0.7rem;">Sudah termasuk seluruh kalkulasi pajak &amp; diskon</small>
                                </div>
                                <strong id="poDetailTotalTagihan" style="color: #065f46; font-size: 1.25rem; font-family: monospace;">
                                    Rp 0
                                </strong>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 5. RIWAYAT PENERIMAAN FISIK DI GERBANG (GRN) --}}
                <div id="poDetailGrnSection">
                    <span class="mac-po-card-label" style="margin-bottom: 0.5rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Riwayat Penerimaan Barang Fisik (Surat Jalan / GRN)</span>
                    </span>
                    <div id="poDetailGrnList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Diisi dinamis via JS --}}
                    </div>
                </div>

            </div>
        </div>

        {{-- 3. macOS Window Statusbar / Footer --}}
        <div class="mac-po-statusbar">
            <div>
                <span id="poDetailItemCountInfo" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                    Menampilkan 0 komoditas
                </span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="mac-po-btn mac-po-btn-secondary" onclick="closePoDetailModal()">
                    Tutup
                </button>
                <button type="button" id="btnPoDetailOpenHaccp" onclick="closePoDetailModal(); openPoHaccpModal(window.currentActivePoId)" class="mac-po-btn mac-po-btn-secondary" style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Form HACCP</span>
                </button>
                <a id="btnPoDetailPdf" href="#" target="_blank" class="mac-po-btn mac-po-btn-secondary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Cetak PDF</span>
                </a>
                <a id="btnPoDetailFullView" href="#" class="mac-po-btn mac-po-btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Detail Full &rarr;</span>
                </a>
            </div>
        </div>

    </div>
</div>
