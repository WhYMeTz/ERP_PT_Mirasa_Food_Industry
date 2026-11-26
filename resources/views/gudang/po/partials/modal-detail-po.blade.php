{{-- MODAL POPUP DETAIL DOKUMEN PURCHASE ORDER (macOS Window Style ERP Mirasa) --}}
<div id="modalDetailPo" class="modal-backdrop" onclick="if(event.target === this) closePoDetailModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
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
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #475569;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Dokumen PO: <strong id="poDetailNo" style="font-family: monospace; color: #0f172a;">-</strong></span>
                <span id="poDetailStatusBadge" class="mac-po-title-chip">-</span>
                <span id="poDetailQcBadge" class="mac-po-title-chip" style="display: none;">-</span>
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
                <div style="display: inline-block; width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #0f172a; border-radius: 50%; animation: spinPo 0.8s linear infinite;"></div>
                <p style="color: #64748b; font-size: 0.8125rem; margin-top: 0.85rem; font-weight: 600;">Memuat data Purchase Order...</p>
            </div>

            {{-- CONTENT CONTAINER --}}
            <div id="poDetailContentBody" style="display: none;">
                
                {{-- BANNER PERINGATAN REVISI / PENOLAKAN DARI ATASAN (JIKA ADA) --}}
                <div id="poDetailRejectBanner" style="display: none; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0; margin-top: 2px;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <strong style="color: #991b1b; font-size: 0.875rem;">Catatan Penolakan / Permintaan Revisi dari Atasan</strong>
                                <span id="poDetailRejectDate" style="font-size: 0.75rem; color: #dc2626; font-weight: 600;">-</span>
                            </div>
                            <div style="margin-top: 0.35rem; font-size: 0.8125rem; color: #7f1d1d;">
                                <strong>Direksi / Reviewer:</strong> <span id="poDetailRejectAuthor">-</span>
                            </div>
                            <div style="margin-top: 0.35rem; padding: 0.5rem 0.75rem; background: #ffffff; border: 1px solid #fca5a5; border-radius: 6px; font-size: 0.8125rem; color: #991b1b; line-height: 1.45;" id="poDetailRejectReason">
                                -
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 1. GRID INFORMASI UTAMA (macOS INSPECTOR 2x2 BALANCED GRID) --}}
                <div class="mac-po-meta-grid">
                    
                    {{-- Box 1: Mitra Supplier (Pemasok) --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Mitra Supplier</span>
                            </span>
                            <span class="mac-badge-pill">Pemasok</span>
                        </div>
                        <div class="mac-po-card-body">
                            <div class="mac-po-entity-title-row">
                                <strong id="poDetailSupplierNm" class="mac-po-entity-name">-</strong>
                                <span id="poDetailSupplierCd" class="mac-po-code-chip">-</span>
                            </div>
                            <div class="mac-po-entity-address">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:2px; color:#64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span id="poDetailSupplierAlamat">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Box 2: Lokasi Gudang Penerima --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Gudang Penerima</span>
                            </span>
                            <span class="mac-badge-pill">Inbound</span>
                        </div>
                        <div class="mac-po-card-body">
                            <div class="mac-po-entity-title-row">
                                <strong id="poDetailGudangNm" class="mac-po-entity-name">-</strong>
                                <span id="poDetailGudangCd" class="mac-po-code-chip">-</span>
                            </div>
                            <div class="mac-po-entity-address">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:2px; color:#64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span id="poDetailGudangAlamat">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Box 3: Parameter Jadwal & Ketentuan Transaksi --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Jadwal &amp; Ketentuan</span>
                            </span>
                            <span class="mac-badge-pill">Parameter</span>
                        </div>
                        <div class="mac-po-kv-list">
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Tgl Terbit PO:</span>
                                <span class="mac-po-kv-val" id="poDetailTgl">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Estimasi Tiba (ETA):</span>
                                <span class="mac-po-kv-val" id="poDetailEta">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Termin Pembayaran:</span>
                                <span class="mac-po-kv-val" id="poDetailTopHeader">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Dibuat Oleh:</span>
                                <span class="mac-po-kv-val" id="poDetailCreatedBy">-</span>
                            </div>
                            <div id="poDetailRevisiRow" class="mac-po-kv-row" style="display: none; border-top: 1px dashed #e2e8f0; background: #f8fafc; padding: 4px 8px; border-radius: 6px; margin-top: 4px;">
                                <span class="mac-po-kv-key">Revisi:</span>
                                <span class="mac-po-kv-val">
                                    <span id="poDetailRevisiText" class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:0.7rem; font-weight:700;">-</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Box 4: Alur Persetujuan & Kebijakan Pajak --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Otorisasi &amp; Pajak</span>
                            </span>
                            <span class="mac-badge-pill">Otoritas</span>
                        </div>
                        <div class="mac-po-kv-list">
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Approver:</span>
                                <span class="mac-po-kv-val" id="poDetailApproverNm">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Tgl Disetujui:</span>
                                <span class="mac-po-kv-val" id="poDetailApprovedAt">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Skema Pajak:</span>
                                <span class="mac-po-kv-val" id="poDetailTaxSchemeHeader">-</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 2. PROGRESS PEMENUHAN FISIK REALISASI --}}
                <div class="mac-po-card" style="margin-bottom: 1.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="mac-po-card-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Pemenuhan Fisik Barang</span>
                        </span>
                        <span id="poDetailPctText" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">0%</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                        <div id="poDetailProgressBar" style="width: 0%; height: 100%; background: #0f172a; border-radius: 9999px; transition: width 0.35s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.775rem; color: #64748b; margin-top: 0.55rem; flex-wrap: wrap; gap: 0.5rem;">
                        <span>Total Pesan: <strong id="poDetailQtyPesan" style="color: #0f172a;">0</strong></span>
                        <span>Total Diterima: <strong id="poDetailQtyTerima" style="color: #059669;">0</strong></span>
                        <span>Sisa Kuota: <strong id="poDetailQtySisa" style="color: #0f172a;">0</strong></span>
                    </div>
                </div>

                {{-- 3. TABEL RINCIAN BAHAN BAKU & FINANSIAL LENGKAP --}}
                <div class="mac-po-table-wrap">
                    <div style="padding: 0.75rem 1rem; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span class="mac-po-card-label">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Rincian Item Komoditas &amp; Finansial</span>
                        </span>
                        <span id="poDetailTableBadgeCount" class="mac-badge-pill">
                            0 Item Terdaftar
                        </span>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="mac-po-table">
                            <thead>
                                <tr>
                                    <th style="width: 36px; text-align: center;">#</th>
                                    <th style="text-align: left;">Komoditas &amp; Kode</th>
                                    <th style="width: 140px; text-align: right;">Kuantitas &amp; Realisasi</th>
                                    <th style="width: 125px; text-align: right;">Harga Satuan</th>
                                    <th style="width: 110px; text-align: center;">Pajak</th>
                                    <th style="width: 130px; text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="poDetailItemsTbody">
                                {{-- Diisi dinamis via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. FINANCIAL SUMMARY & CATATAN (macOS INSPECTOR STYLE) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.15rem; margin-bottom: 1.15rem;">
                    
                    {{-- Kolom Kiri: Catatan & Skema Pajak Dokumen --}}
                    <div style="display: flex; flex-direction: column; gap: 1.15rem;">
                        {{-- Kartu Skema Pajak & Ketentuan Pembayaran --}}
                        <div class="mac-po-card">
                            <span class="mac-po-card-label" style="margin-bottom: 0.65rem;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Skema Perpajakan &amp; TOP</span>
                            </span>
                            <div class="mac-po-kv-list">
                                <div class="mac-po-kv-row">
                                    <span class="mac-po-kv-key">Perlakuan Pajak:</span>
                                    <span class="mac-po-kv-val" id="poDetailTaxTreatment">-</span>
                                </div>
                                <div class="mac-po-kv-row">
                                    <span class="mac-po-kv-key">Jenis Pajak:</span>
                                    <span class="mac-po-kv-val" id="poDetailTaxName">-</span>
                                </div>
                                <div class="mac-po-kv-row">
                                    <span class="mac-po-kv-key">Tarif Pajak (%):</span>
                                    <span class="mac-po-kv-val" id="poDetailTaxRate">-</span>
                                </div>
                                <div class="mac-po-kv-row">
                                    <span class="mac-po-kv-key">Termin Pembayaran:</span>
                                    <span class="mac-po-kv-val" id="poDetailTopVal">-</span>
                                </div>
                            </div>
                        </div>

                        {{-- Kartu Catatan / Syarat Dokumen --}}
                        <div class="mac-po-card">
                            <span class="mac-po-card-label" style="margin-bottom: 0.5rem;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span>Catatan &amp; Remark</span>
                            </span>
                            <p id="poDetailCatatan" style="font-size: 0.8125rem; color: #475569; margin: 0; line-height: 1.5; white-space: pre-line;">
                                Tidak ada catatan tambahan untuk dokumen PO ini.
                            </p>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Rincian Lengkap Finansial --}}
                    <div class="mac-fin-summary-card">
                        <span class="mac-po-card-label" style="margin-bottom: 0.65rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Kalkulasi Tagihan &amp; Finansial</span>
                        </span>
                        
                        <div style="display: flex; flex-direction: column;">
                            <div class="mac-fin-row">
                                <span style="color: #64748b;">Subtotal Bruto:</span>
                                <strong id="poDetailSubtotalBruto" style="color: #0f172a; font-family: monospace;">Rp 0</strong>
                            </div>
                            <div class="mac-fin-row">
                                <div>
                                    <span style="color: #64748b;">Diskon Item:</span>
                                    <small id="poDetailDiskonPercentNote" style="display: block; font-size: 0.7rem; color: #94a3b8;"></small>
                                </div>
                                <span id="poDetailDiskon" style="color: #dc2626; font-weight: 600; font-family: monospace;">- Rp 0</span>
                            </div>
                            <div class="mac-fin-row">
                                <span style="color: #64748b;">Potongan Tambahan:</span>
                                <span id="poDetailPotongan" style="color: #dc2626; font-weight: 600; font-family: monospace;">- Rp 0</span>
                            </div>
                            <div class="mac-fin-row" style="background: #f8fafc; margin: 0.2rem -0.5rem; padding: 0.45rem 0.5rem; border-radius: 6px;">
                                <span style="color: #334155; font-weight: 700;">DPP (Dasar Pengenaan Pajak):</span>
                                <strong id="poDetailDpp" style="color: #0f172a; font-family: monospace;">Rp 0</strong>
                            </div>
                            <div class="mac-fin-row">
                                <div>
                                    <span style="color: #64748b;">Pajak (PPN / PPh):</span>
                                    <small id="poDetailTaxSubNote" style="display: block; font-size: 0.7rem; color: #64748b;"></small>
                                </div>
                                <span id="poDetailPpn" style="color: #0f172a; font-weight: 700; font-family: monospace;">Rp 0</span>
                            </div>
                            
                            {{-- Grand Total Box --}}
                            <div class="mac-fin-total-box">
                                <div>
                                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; display: block;">
                                        Total Tagihan Resmi PO
                                    </span>
                                    <small style="color: #94a3b8; font-size: 0.7rem;">Sudah termasuk seluruh pajak &amp; diskon</small>
                                </div>
                                <strong id="poDetailTotalTagihan" style="color: #0f172a; font-size: 1.25rem; font-family: monospace; font-weight: 800;">
                                    Rp 0
                                </strong>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 5. RIWAYAT INSPEKSI MUTU & SAMPLING QC INBOUND --}}
                <div id="poDetailQcSection" style="margin-bottom: 1.15rem;">
                    <div class="mac-po-card-label" style="margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span>Riwayat Uji Mutu QC Inbound</span>
                        </span>
                        <span id="poDetailQcSummaryBadge" class="mac-badge-pill">-</span>
                    </div>
                    <div id="poDetailQcList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Diisi dinamis via JS --}}
                    </div>
                </div>

                {{-- 6. RIWAYAT PENERIMAAN FISIK DI GERBANG (GRN) --}}
                <div id="poDetailGrnSection" style="margin-bottom: 1.15rem;">
                    <div class="mac-po-card-label" style="margin-bottom: 0.5rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Riwayat Penerimaan Barang Fisik (GRN)</span>
                    </div>
                    <div id="poDetailGrnList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Diisi dinamis via JS --}}
                    </div>
                </div>

                {{-- 7. RIWAYAT PERUBAHAN & AUDIT TRAIL PO --}}
                <div id="poDetailRevisiSection">
                    <div class="mac-po-card-label" style="margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Riwayat Perubahan &amp; Audit Trail</span>
                        </span>
                        <span id="poDetailRevisiSummaryBadge" class="mac-badge-pill">-</span>
                    </div>
                    <div id="poDetailRevisiList" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        {{-- Diisi dinamis via JS --}}
                    </div>
                </div>

            </div>
        </div>

        {{-- 3. macOS Window Statusbar / Footer --}}
        <div class="mac-po-statusbar">
            <div>
                <span id="poDetailItemCountInfo" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                    Menampilkan 0 komoditas
                </span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-pill-outline" onclick="closePoDetailModal()">
                    Tutup
                </button>
                <button type="button" id="btnPoDetailOpenHaccp" onclick="closePoDetailModal(); openPoHaccpModal(window.currentActivePoId)" class="btn-pill-outline">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Form HACCP</span>
                </button>
                <a id="btnPoDetailPdf" href="#" target="_blank" class="btn-pill-outline">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Cetak PDF</span>
                </a>
                <a id="btnPoDetailFullView" href="#" class="btn-pill-dark">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Detail Full</span>
                </a>
            </div>
        </div>

    </div>
</div>
