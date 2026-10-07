{{-- ========================================================================= --}}
{{-- MODAL PILIH TIKET QC INBOUND (STATUS SIAP_GUDANG)                          --}}
{{-- ========================================================================= --}}
<div id="modalPilihQc" class="modal-backdrop">
    <div class="modal-dialog modal-qc-dialog">
        {{-- HEADER MODAL --}}
        <div class="modal-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%); border-bottom: 1px solid #cbd5e1; padding: 1rem 1.35rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                    🔬
                </div>
                <div>
                    <h2 class="modal-title" style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span>Pilih Tiket Hasil Pemeriksaan Mutu (QC)</span>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.7rem; font-weight: 700;">Status: Siap Terima</span>
                    </h2>
                    <p style="color: #475569; font-size: 0.8rem; margin: 0.2rem 0 0 0;">
                        Tarik data kedatangan bahan baku yang telah lolos uji mutu &amp; verifikasi tim QC ke formulir penerimaan barang (GRN).
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalPilihQc')" title="Tutup">&times;</button>
        </div>

        {{-- TOOLBAR FILTER & PENCARIAN TIKET QC --}}
        <div style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.35rem; display: flex; flex-direction: column; gap: 0.65rem;">
            {{-- Baris 1: Pencarian Cepat, Filter PO, Filter Tahap Singkong, Filter Supplier, dan Reset --}}
            <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                {{-- Input Cari --}}
                <div style="position: relative; flex: 1; min-width: 220px;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.85rem; color: #94a3b8; pointer-events: none;">🔍</span>
                    <input type="text" id="qcSearchInput" class="form-control" placeholder="Cari No. Tiket QC, Supplier, PO, Komoditas, No Plat Truk, Sopir..." style="padding-left: 2.2rem; padding-right: 2rem; font-size: 0.825rem; height: 36px; border-radius: 6px;" oninput="filterQcTickets()">
                    <button type="button" id="btnQcSearchClear" onclick="clearQcSearch()" style="display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 0.9rem; cursor: pointer; padding: 2px 4px;" title="Hapus pencarian">&times;</button>
                </div>

                {{-- Filter Supplier --}}
                <div style="width: 175px;">
                    <select id="qcFilterSupplier" class="form-control" style="font-size: 0.8rem; height: 36px; border-radius: 6px;" onchange="filterQcTickets()">
                        <option value="">Semua Supplier</option>
                    </select>
                </div>

                {{-- Filter PO --}}
                <div style="width: 140px;">
                    <select id="qcFilterPo" class="form-control" style="font-size: 0.8rem; height: 36px; border-radius: 6px;" onchange="filterQcTickets()">
                        <option value="">Semua PO</option>
                        <option value="PO">📄 Dengan PO</option>
                        <option value="NON_PO">⚡ Non-PO (Direct)</option>
                    </select>
                </div>

                {{-- Filter Tahap Singkong --}}
                <div style="width: 170px;">
                    <select id="qcFilterTahap" class="form-control" style="font-size: 0.8rem; height: 36px; border-radius: 6px;" onchange="filterQcTickets()">
                        <option value="">Semua Tahap Uji</option>
                        <option value="LENGKAP">🟢 Uji 1 + 2 Lengkap</option>
                        <option value="UJI_1">🟡 Uji 1 Saja (1/2 Bak)</option>
                    </select>
                </div>

                {{-- Tombol Reset Filter --}}
                <button type="button" onclick="resetAllQcFilters()" class="btn btn-secondary btn-sm" style="height: 36px; font-size: 0.8rem; padding: 0 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1;" title="Reset seluruh filter ke default">
                    <span>🔄 Reset</span>
                </button>
            </div>

            {{-- Baris 2: Filter Chips Komoditas & Counter --}}
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding-top: 0.2rem;">
                <div style="display: flex; gap: 0.4rem; align-items: center; overflow-x: auto; padding-bottom: 2px;">
                    <button type="button" class="qc-filter-chip active" data-cat="" onclick="setQcCategoryFilter('', this)" id="qcChipAll">
                        Semua
                    </button>
                    <button type="button" class="qc-filter-chip" data-cat="SINGKONG" onclick="setQcCategoryFilter('SINGKONG', this)" id="qcChipSingkong">
                        🥔 Singkong
                    </button>
                    <button type="button" class="qc-filter-chip" data-cat="MINYAK" onclick="setQcCategoryFilter('MINYAK', this)" id="qcChipMinyak">
                        🛢️ Minyak
                    </button>
                    <button type="button" class="qc-filter-chip" data-cat="PLASTIK" onclick="setQcCategoryFilter('PLASTIK', this)" id="qcChipPlastik">
                        🛍️ Plastik
                    </button>
                    <button type="button" class="qc-filter-chip" data-cat="KARTON" onclick="setQcCategoryFilter('KARTON', this)" id="qcChipKarton">
                        📦 Karton
                    </button>
                    <button type="button" class="qc-filter-chip" data-cat="BAHAN_PENOLONG" onclick="setQcCategoryFilter('BAHAN_PENOLONG', this)" id="qcChipPenolong">
                        🧂 Bumbu &amp; Penolong
                    </button>
                </div>

                <div style="font-size: 0.785rem; color: #64748b; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 0.35rem;" id="qcResultCountContainer">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <span id="qcResultCount">Memuat tiket...</span>
                </div>
            </div>

            {{-- Banner Info Tambahan jika Supplier terfilter dari Form --}}
            <div id="qcSupplierFilterAlert" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.35rem 0.75rem; font-size: 0.775rem; color: #15803d; justify-content: space-between; align-items: center;">
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
        <div style="flex: 1; overflow-y: auto; padding: 0.75rem 1.35rem; min-height: 280px; max-height: 58vh;" class="excel-table-scroll">
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
                <button type="button" onclick="resetAllQcFilters()" class="btn btn-sm btn-primary" style="margin-top: 1rem; background: #0284c7; border: none; font-weight: 700; font-size: 0.8rem; padding: 0.4rem 0.85rem; border-radius: 6px;">
                    🔄 Reset Filter
                </button>
            </div>

            <table class="qc-picker-table" id="qcModalTable" style="display: none; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th style="width: 165px; text-align: left;">Tiket QC &amp; Waktu</th>
                        <th style="text-align: left; min-width: 220px;">Supplier &amp; Dokumen PO</th>
                        <th style="text-align: left; min-width: 190px;">Komoditas &amp; Mutu</th>
                        <th style="width: 145px; text-align: left;">Armada &amp; Sopir</th>
                        <th style="width: 135px; text-align: right;">Tonase Netto</th>
                        <th style="width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="qcModalTbody">
                </tbody>
            </table>
        </div>

        {{-- FOOTER MODAL --}}
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.75rem 1.35rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.775rem; color: #64748b;">
                💡 <em>Klik pada baris atau tombol <strong>⚡ Tarik</strong> untuk menyuntikkan data inspeksi ke formulir penerimaan barang.</em>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalPilihQc')" style="padding: 0.35rem 0.85rem; font-weight: 600;">
                Tutup
            </button>
        </div>
    </div>
</div>
