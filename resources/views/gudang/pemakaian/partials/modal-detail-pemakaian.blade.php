{{-- MODAL POPUP DETAIL DOKUMEN PEMAKAIAN BAHAN / BPPB (macOS Window Style ERP Mirasa) --}}
<div id="modalDetailPemakaian" class="modal-backdrop" onclick="if(event.target === this) closePakaiDetailModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
    <div class="modal-dialog mac-po-dialog" style="width: 100%; max-width: 1050px; max-height: 90vh;">
        
        {{-- 1. macOS Window Titlebar --}}
        <div class="mac-po-titlebar">
            {{-- Window Controls: Traffic Lights --}}
            <div class="mac-po-traffic" aria-label="Window Controls">
                <button type="button" class="mac-po-dot red" onclick="closePakaiDetailModal()" title="Tutup (Esc)">
                    <span class="mac-po-icon">&times;</span>
                </button>
                <button type="button" class="mac-po-dot yellow" onclick="closePakaiDetailModal()" title="Minimalkan">
                    <span class="mac-po-icon">&minus;</span>
                </button>
                <button type="button" class="mac-po-dot green" onclick="togglePakaiDetailMaximize()" title="Maksimalkan / Normal">
                    <span class="mac-po-icon">&plus;</span>
                </button>
            </div>

            {{-- Centered macOS Window Title --}}
            <div class="mac-po-title">
                <svg width="15" height="15" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="flex-shrink: 0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Dokumen BPPB: <strong id="pakaiDetailNo" style="font-family: monospace; color: #dc2626;">-</strong></span>
                <span id="pakaiDetailStatusBadge" class="mac-po-title-chip">-</span>
            </div>

            {{-- Right Controls: Buka Detail Full & Esc Shortcut --}}
            <div class="mac-po-actions-right">
                <a id="btnPakaiDetailFullHeader" href="#" class="mac-po-btn-full" title="Buka Lembar Kerja Penuh Dokumen BPPB">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Detail Full</span>
                </a>
                <kbd class="mac-po-kbd">esc</kbd>
            </div>
        </div>

        {{-- 2. macOS Window Body --}}
        <div class="mac-po-body" style="padding: 1.25rem; overflow-y: auto;">
            
            {{-- SPINNER LOADING --}}
            <div id="pakaiDetailLoadingSpinner" style="text-align: center; padding: 4rem 1rem;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #dc2626; border-radius: 50%; animation: spinPakai 0.8s linear infinite;"></div>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 1rem; font-weight: 600;">Memuat rincian dokumen pengeluaran &amp; kalkulasi bahan...</p>
            </div>

            {{-- CONTENT CONTAINER --}}
            <div id="pakaiDetailContentBody" style="display: none;">
                
                {{-- KOTAK INFORMASI AUDIT KOREKSI DARI VERIFIKASI KEUANGAN --}}
                <div id="pakaiDetailKoreksiBox" style="display: none; background: #fffdf5; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                        <div style="display: flex; align-items: flex-start; gap: 0.65rem;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                🔄
                            </div>
                            <div>
                                <strong style="color: #92400e; font-size: 0.875rem; display: block;">
                                    Dokumen Ini Telah Disesuaikan oleh Verifikator Keuangan
                                </strong>
                                <div style="font-size: 0.775rem; color: #b45309; margin-top: 2px;" id="pakaiDetailKoreksiSub">
                                    Pembuat awal: <strong id="pakaiDetailKoreksiCreator">-</strong> &bull; Dikoreksi oleh: <strong id="pakaiDetailKoreksiVerifier">-</strong> (<span id="pakaiDetailKoreksiDate">-</span>)
                                </div>
                                <div style="margin-top: 0.4rem; font-size: 0.75rem; color: #78350f; background: #fef3c7; padding: 0.35rem 0.65rem; border-radius: 4px;" id="pakaiDetailKoreksiMemo">
                                    Catatan Verifikator: -
                                </div>
                            </div>
                        </div>
                        <span id="pakaiDetailKoreksiBadge" class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.725rem; border: 1px solid #fecaca;">
                            KOREKSI_FISIK
                        </span>
                    </div>
                    <div id="pakaiDetailKoreksiDiffWrap" style="margin-top: 0.65rem; font-size: 0.75rem; border-top: 1px dashed #fde68a; padding-top: 0.5rem;">
                        <span style="font-weight: 700; color: #475569; display: block; margin-bottom: 0.25rem;">Rincian Penyesuaian Data:</span>
                        <ul id="pakaiDetailKoreksiList" style="margin: 0; padding-left: 1.25rem; color: #334155; line-height: 1.5;">
                        </ul>
                    </div>
                </div>

                {{-- 1. GRID INFORMASI UTAMA (macOS INSPECTOR 2x2 BALANCED GRID) --}}
                <div class="mac-po-meta-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    
                    {{-- Box 1: Gudang Asal Pengeluaran --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Gudang Asal Barang</span>
                            </span>
                            <span class="mac-badge-pill" style="background: #fef2f2; color: #991b1b; border-color: #fecaca; font-size: 0.675rem;">Fasilitas Sumber</span>
                        </div>
                        <div class="mac-po-card-body">
                            <div class="mac-po-entity-title-row">
                                <strong id="pakaiDetailGudangNm" class="mac-po-entity-name">-</strong>
                                <span id="pakaiDetailGudangCd" class="mac-po-code-chip red">-</span>
                            </div>
                            <div class="mac-po-entity-address" id="pakaiDetailGudangLokasi">
                                Pusat Penyimpanan Bahan Baku &amp; Penolong
                            </div>
                        </div>
                    </div>

                    {{-- Box 2: Tujuan Pemakaian / Lini Kerja --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Tujuan / Lini Kerja (WIP)</span>
                            </span>
                            <span class="mac-badge-pill" style="background: #fffbeb; color: #92400e; border-color: #fde68a; font-size: 0.675rem;">Lantai Pabrik</span>
                        </div>
                        <div class="mac-po-card-body">
                            <div class="mac-po-entity-title-row">
                                <strong id="pakaiDetailTujuan" class="mac-po-entity-name" style="color: #991b1b;">-</strong>
                            </div>
                            <div class="mac-po-entity-address" id="pakaiDetailCatatan">
                                Catatan: -
                            </div>
                        </div>
                    </div>

                    {{-- Box 3: Parameter Tanggal & Operator --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Jadwal Pengeluaran</span>
                            </span>
                            <span class="mac-badge-pill" style="background: #f0f9ff; color: #0284c7; border-color: #bae6fd; font-size: 0.675rem;">Kronologi</span>
                        </div>
                        <div class="mac-po-kv-list">
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Tgl Pemakaian:</span>
                                <span class="mac-po-kv-val" id="pakaiDetailTgl" style="font-weight: 700; color: #0f172a;">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Waktu Input:</span>
                                <span class="mac-po-kv-val" id="pakaiDetailWaktu" style="color: #475569;">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Dibuat Oleh:</span>
                                <span class="mac-po-kv-val" id="pakaiDetailCreator" style="color: #0369a1; font-weight: 600;">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Box 4: Status Audit & Pemotongan Stok --}}
                    <div class="mac-po-card">
                        <div class="mac-po-card-header">
                            <span class="mac-po-card-label">
                                <svg width="14" height="14" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Status Pemotongan Stok</span>
                            </span>
                            <span class="mac-badge-pill" style="background: #ecfdf5; color: #065f46; border-color: #a7f3d0; font-size: 0.675rem;">Otorisasi</span>
                        </div>
                        <div class="mac-po-kv-list">
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Status Dokumen:</span>
                                <span class="mac-po-kv-val" id="pakaiDetailStatusText" style="font-weight: 800;">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Efek Persediaan:</span>
                                <span class="mac-po-kv-val" id="pakaiDetailStokEffect" style="color: #475569; font-size: 0.75rem;">-</span>
                            </div>
                            <div class="mac-po-kv-row">
                                <span class="mac-po-kv-key">Jurnal Akuntansi:</span>
                                <span class="mac-po-kv-val" id="pakaiDetailJurnalStatus" style="color: #065f46; font-weight: 700;">-</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- 2. METRIK AKUMULASI PENGELUARAN --}}
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                📦
                            </div>
                            <div>
                                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                                    Total Kuantitas Fisik Keluar:
                                </span>
                                <div style="font-size: 1.35rem; font-weight: 800; color: #dc2626; font-family: monospace;" id="pakaiDetailTotalQty">
                                    0,00 Kg
                                </div>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                                Total Jenis Bahan:
                            </span>
                            <div style="font-size: 1.15rem; font-weight: 700; color: #0284c7;" id="pakaiDetailCountItems">
                                0 Bahan Fisik
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. TABEL RINCIAN BAHAN BAKU & ALOKASI BATCH (FIFO) --}}
                <div class="mac-po-table-wrap" style="border: 1.5px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 1rem;">
                    <div style="padding: 0.75rem 1rem; background: #ffffff; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Rincian Bahan Produksi &amp; Alokasi Batch Fisik
                        </span>
                        <span id="pakaiDetailTableBadgeCount" class="mac-badge-pill" style="background: #fef2f2; color: #991b1b; border-color: #fecaca; font-weight: 700;">
                            0 Item Terdaftar
                        </span>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="mac-po-table" style="width: 100%; border-collapse: collapse; table-layout: auto;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1.5px solid #cbd5e1;">
                                    <th style="width: 35px; text-align: center; padding: 0.55rem 0.65rem;">#</th>
                                    <th style="text-align: left; min-width: 220px; padding: 0.55rem 0.65rem;">Bahan Produksi</th>
                                    <th style="width: 140px; text-align: left; padding: 0.55rem 0.65rem;">Kategori</th>
                                    <th style="min-width: 190px; text-align: left; padding: 0.55rem 0.65rem;">Alokasi Batch &amp; Grade (FIFO)</th>
                                    <th style="min-width: 170px; text-align: left; padding: 0.55rem 0.65rem;">Keterangan / Lini Produksi</th>
                                    <th style="width: 130px; text-align: right; padding: 0.55rem 0.65rem;">Kuantitas Keluar</th>
                                    <th style="width: 80px; text-align: center; padding: 0.55rem 0.65rem;">Satuan</th>
                                </tr>
                            </thead>
                            <tbody id="pakaiDetailItemsTbody">
                                {{-- Diisi dinamis via JavaScript --}}
                            </tbody>
                            <tfoot id="pakaiDetailItemsTfoot" style="display: none;">
                                <tr style="background: #f8fafc; border-top: 1.5px solid #cbd5e1; font-weight: 700;">
                                    <td colspan="5" style="padding: 0.55rem 0.65rem; text-align: right; color: #475569; font-size: 0.75rem; text-transform: uppercase;">Total Kuantitas Fisik:</td>
                                    <td id="pakaiDetailFootQty" style="padding: 0.55rem 0.65rem; text-align: right; color: #dc2626; font-family: monospace; font-size: 0.95rem;">0,00</td>
                                    <td id="pakaiDetailFootSatuan" style="padding: 0.55rem 0.65rem; text-align: center; color: #64748b; font-size: 0.75rem;">Kg</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- 3. macOS Window Statusbar / Footer --}}
        <div class="mac-po-statusbar" style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span id="pakaiDetailFooterCount" style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                    Menampilkan 0 bahan fisik
                </span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closePakaiDetailModal()" style="font-weight: 600; padding: 0.4rem 0.85rem;">
                    Tutup
                </button>
                <a id="btnPakaiDetailPdf" href="#" target="_blank" class="btn btn-secondary btn-sm" style="font-weight: 700; color: #dc2626; border-color: #dc2626; background: #ffffff; padding: 0.4rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Cetak PDF</span>
                </a>
                <a id="btnPakaiDetailFullView" href="#" class="btn btn-primary btn-sm" style="background: #dc2626; border: none; font-weight: 700; padding: 0.4rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Detail Full &rarr;</span>
                </a>
            </div>
        </div>

    </div>
</div>

