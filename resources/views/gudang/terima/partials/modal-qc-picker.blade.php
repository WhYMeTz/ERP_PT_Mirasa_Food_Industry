{{-- ========================================================================= --}}
{{-- MODAL PILIH TIKET QC INBOUND (MACOS WINDOW DESIGN - SIAP_GUDANG)          --}}
{{-- ========================================================================= --}}
<div id="modalPilihQc" class="modal-backdrop">
    <div class="modal-dialog modal-qc-dialog macos-window">
        {{-- MACOS WINDOW TITLEBAR / HEADER --}}
        <div class="modal-header macos-modal-header">
            {{-- macOS Traffic Lights (Window Controls) --}}
            <div class="macos-window-controls" title="macOS Controls">
                <button type="button" class="macos-dot macos-dot-close" onclick="closeModal('modalPilihQc')" title="Tutup (Esc)"></button>
                <button type="button" class="macos-dot macos-dot-minimize" onclick="closeModal('modalPilihQc')" title="Kecilkan"></button>
                <button type="button" class="macos-dot macos-dot-zoom" onclick="toggleModalFullscreen('modalPilihQc')" title="Perbesar / Normal"></button>
            </div>

            {{-- Center / Title Area --}}
            <div class="macos-modal-title-wrap">
                <div class="macos-modal-icon">🔬</div>
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h2 class="macos-modal-title">Pilih Tiket Hasil Pemeriksaan Mutu (QC)</h2>
                        <span class="macos-pill-status">Status: Siap Terima</span>
                    </div>
                    <p class="macos-modal-subtitle">
                        Tarik data kedatangan bahan baku yang telah lolos uji mutu &amp; verifikasi tim QC ke formulir penerimaan barang (GRN).
                    </p>
                </div>
            </div>

            {{-- Clean close icon on right --}}
            <button type="button" class="macos-btn-close-subtle" onclick="closeModal('modalPilihQc')" title="Tutup">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- MACOS FINDER-STYLE TOOLBAR --}}
        <div class="macos-modal-toolbar">
            {{-- Baris 1: Pencarian Cepat Spotlight Style & Filter Dropdowns --}}
            <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                {{-- Input Cari Spotlight Style --}}
                <div class="macos-search-wrap">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.825rem; color: #94a3b8; pointer-events: none;">🔍</span>
                    <input type="text" id="qcSearchInput" class="form-control macos-search-input" placeholder="Cari No. Tiket QC, Supplier, PO, Komoditas, Plat Truk, Sopir..." oninput="filterQcTickets()">
                    <button type="button" id="btnQcSearchClear" onclick="clearQcSearch()" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 0.95rem; cursor: pointer; padding: 2px 4px;" title="Hapus pencarian">&times;</button>
                </div>

                {{-- Filter Supplier --}}
                <div style="width: 175px;">
                    <select id="qcFilterSupplier" class="form-control macos-select" onchange="filterQcTickets()">
                        <option value="">Semua Supplier</option>
                    </select>
                </div>

                {{-- Filter PO --}}
                <div style="width: 140px;">
                    <select id="qcFilterPo" class="form-control macos-select" onchange="filterQcTickets()">
                        <option value="">Semua PO</option>
                        <option value="PO">📄 Dengan PO</option>
                        <option value="NON_PO">⚡ Non-PO (Direct)</option>
                    </select>
                </div>

                {{-- Filter Tahap Singkong --}}
                <div style="width: 170px;">
                    <select id="qcFilterTahap" class="form-control macos-select" onchange="filterQcTickets()">
                        <option value="">Semua Tahap Uji</option>
                        <option value="LENGKAP">🟢 Uji 1 + 2 Lengkap</option>
                        <option value="UJI_1">🟡 Uji 1 Saja (1/2 Bak)</option>
                    </select>
                </div>

                {{-- Tombol Reset Filter --}}
                <button type="button" onclick="resetAllQcFilters()" class="macos-btn-reset" title="Reset seluruh filter ke default">
                    <span>🔄 Reset</span>
                </button>
            </div>

            {{-- Baris 2: macOS Segmented Control untuk Komoditas & Counter --}}
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding-top: 0.2rem;">
                <div class="macos-segmented-bar">
                    <button type="button" class="macos-segment-item qc-filter-chip active" data-cat="" onclick="setQcCategoryFilter('', this)" id="qcChipAll">
                        Semua
                    </button>
                    <button type="button" class="macos-segment-item qc-filter-chip" data-cat="SINGKONG" onclick="setQcCategoryFilter('SINGKONG', this)" id="qcChipSingkong">
                        🥔 Singkong
                    </button>
                    <button type="button" class="macos-segment-item qc-filter-chip" data-cat="MINYAK" onclick="setQcCategoryFilter('MINYAK', this)" id="qcChipMinyak">
                        🛢️ Minyak
                    </button>
                    <button type="button" class="macos-segment-item qc-filter-chip" data-cat="PLASTIK" onclick="setQcCategoryFilter('PLASTIK', this)" id="qcChipPlastik">
                        🛍️ Plastik
                    </button>
                    <button type="button" class="macos-segment-item qc-filter-chip" data-cat="KARTON" onclick="setQcCategoryFilter('KARTON', this)" id="qcChipKarton">
                        📦 Karton
                    </button>
                    <button type="button" class="macos-segment-item qc-filter-chip" data-cat="BAHAN_PENOLONG" onclick="setQcCategoryFilter('BAHAN_PENOLONG', this)" id="qcChipPenolong">
                        🧂 Bumbu &amp; Penolong
                    </button>
                </div>

                <div style="font-size: 0.785rem; color: #64748b; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 0.35rem;" id="qcResultCountContainer">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);"></span>
                    <span id="qcResultCount">Memuat tiket...</span>
                </div>
            </div>

            {{-- Banner Info Tambahan jika Supplier terfilter dari Form --}}
            <div id="qcSupplierFilterAlert" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.775rem; color: #15803d; justify-content: space-between; align-items: center;">
                <div>
                    <span>📌 Menampilkan tiket untuk supplier: </span>
                    <strong id="qcFilteredSupplierName">-</strong>
                </div>
                <button type="button" onclick="clearSupplierFilter()" style="background: none; border: none; color: #0369a1; text-decoration: underline; cursor: pointer; font-size: 0.75rem; font-weight: 700;">
                    Tampilkan Semua Supplier
                </button>
            </div>
        </div>

        {{-- AREA TABEL DAFTAR TIKET QC --}}
        <div style="flex: 1; overflow-y: auto; padding: 0.75rem 1.25rem; min-height: 280px; max-height: 58vh;" class="excel-table-scroll">
            <div id="qcModalLoading" style="text-align: center; padding: 3rem; color: #64748b;">
                <div style="font-size: 1.8rem; margin-bottom: 0.5rem; animation: spin 1s linear infinite;">⏳</div>
                <div style="font-weight: 600;">Memuat daftar tiket QC yang siap diterima...</div>
            </div>

            <div id="qcModalEmpty" style="display: none; text-align: center; padding: 3.5rem 1rem; color: #64748b;">
                <div style="font-size: 2.4rem; margin-bottom: 0.5rem;">📭</div>
                <div style="font-weight: 700; color: #0f172a; font-size: 1rem;">Tidak Ada Tiket QC yang Sesuai</div>
                <div style="font-size: 0.825rem; margin-top: 0.25rem; color: #64748b;" id="qcEmptyMessage">
                    Tidak ditemukan tiket dengan status SIAP_GUDANG yang cocok dengan filter atau kata kunci Anda.
                </div>
                <button type="button" onclick="resetAllQcFilters()" class="btn btn-sm btn-primary" style="margin-top: 1rem; background: #007aff; border: none; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 122, 255, 0.25);">
                    🔄 Reset Filter
                </button>
            </div>

            <table class="qc-picker-table" id="qcModalTable" style="display: none; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th style="width: 175px; text-align: left;">Tiket QC &amp; Waktu</th>
                        <th style="text-align: left; min-width: 220px;">Supplier &amp; Dokumen PO</th>
                        <th style="text-align: left; min-width: 190px;">Komoditas &amp; Mutu</th>
                        <th style="width: 145px; text-align: left;">Armada &amp; Sopir</th>
                        <th style="width: 135px; text-align: right;">Tonase Netto</th>
                        <th style="width: 95px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="qcModalTbody">
                </tbody>
            </table>
        </div>

        {{-- MACOS WINDOW FOOTER --}}
        <div class="modal-footer macos-modal-footer">
            <div style="font-size: 0.775rem; color: #64748b; display: flex; align-items: center; gap: 0.35rem;">
                <span>💡</span>
                <em>Klik pada baris atau tombol <strong>⚡ Tarik</strong> untuk menyuntikkan data inspeksi ke formulir penerimaan barang.</em>
            </div>
            <button type="button" class="macos-btn-cancel" onclick="closeModal('modalPilihQc')">
                Tutup
            </button>
        </div>
    </div>
</div>
