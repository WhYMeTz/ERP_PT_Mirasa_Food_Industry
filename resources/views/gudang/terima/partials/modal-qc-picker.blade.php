{{-- ========================================================================= --}}
{{-- MODAL PILIH TIKET QC INBOUND (STATUS SIAP_GUDANG)                          --}}
{{-- ========================================================================= --}}
<div id="modalPilihQc" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 980px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                    🔬
                </div>
                <div>
                    <h2 class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">Tiket QC Inbound (Siap Terima di Gudang)</h2>
                    <p style="color: #64748b; font-size: 0.8rem; margin: 0.2rem 0 0 0;">Pilih tiket hasil inspeksi mutu lapangan untuk ditarik otomatis ke formulir penerimaan barang.</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalPilihQc')">&times;</button>
        </div>

        {{-- TOOLBAR FILTER & CARI TIKET QC --}}
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; flex-direction: column; gap: 0.6rem;">
            {{-- Baris 1: Pencarian Cepat, Filter PO, dan Status Counter --}}
            <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 250px;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.85rem; color: #94a3b8; pointer-events: none;">🔍</span>
                    <input type="text" id="qcSearchInput" class="form-control" placeholder="Cari No. Tiket QC, Supplier, Barang, No PO, Truk, Sopir..." style="padding-left: 2.2rem; padding-right: 2rem; font-size: 0.825rem; height: 34px; border-radius: 6px;" oninput="filterQcTickets()">
                    <button type="button" id="btnQcSearchClear" onclick="clearQcSearch()" style="display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 0.9rem; cursor: pointer; padding: 2px 4px;" title="Hapus pencarian">&times;</button>
                </div>

                <div style="width: 175px;">
                    <select id="qcFilterPo" class="form-control" style="font-size: 0.8rem; height: 34px; border-radius: 6px;" onchange="filterQcTickets()">
                        <option value="">Semua Status PO</option>
                        <option value="PO">Dengan No. PO</option>
                        <option value="NON_PO">Non-PO (Langsung)</option>
                    </select>
                </div>

                <div style="font-size: 0.775rem; color: #475569; font-weight: 600; white-space: nowrap;" id="qcResultCount">
                    Memuat...
                </div>
            </div>

            {{-- Baris 2: Filter Pills Kategori Komoditas --}}
            <div style="display: flex; gap: 0.35rem; align-items: center; overflow-x: auto; padding-bottom: 2px;">
                <span style="font-size: 0.725rem; font-weight: 700; color: #64748b; margin-right: 0.25rem; text-transform: uppercase;">Komoditas:</span>
                <button type="button" class="qc-filter-chip active" data-cat="" onclick="setQcCategoryFilter('', this)">
                    Semua
                </button>
                <button type="button" class="qc-filter-chip" data-cat="SINGKONG" onclick="setQcCategoryFilter('SINGKONG', this)">
                    🥔 Singkong
                </button>
                <button type="button" class="qc-filter-chip" data-cat="MINYAK" onclick="setQcCategoryFilter('MINYAK', this)">
                    🛢️ Minyak Goreng
                </button>
                <button type="button" class="qc-filter-chip" data-cat="PLASTIK" onclick="setQcCategoryFilter('PLASTIK', this)">
                    🛍️ Plastik Kemasan
                </button>
                <button type="button" class="qc-filter-chip" data-cat="KARTON" onclick="setQcCategoryFilter('KARTON', this)">
                    📦 Karton Box
                </button>
                <button type="button" class="qc-filter-chip" data-cat="MSG" onclick="setQcCategoryFilter('MSG', this)">
                    🧂 MSG
                </button>
                <button type="button" class="qc-filter-chip" data-cat="GARAM" onclick="setQcCategoryFilter('GARAM', this)">
                    🧂 Garam
                </button>
                <button type="button" class="qc-filter-chip" data-cat="PERENYAH" onclick="setQcCategoryFilter('PERENYAH', this)">
                    ✨ Perenyah
                </button>
            </div>
        </div>

        <div style="flex: 1; overflow-y: auto; padding: 0.85rem 1.25rem; min-height: 250px; max-height: 55vh;" class="excel-table-scroll">
            <div id="qcModalLoading" style="text-align: center; padding: 2.5rem; color: #64748b;">
                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">⏳</div>
                <div>Memuat daftar tiket QC yang siap diterima...</div>
            </div>
            <div id="qcModalEmpty" style="display: none; text-align: center; padding: 3rem 1rem; color: #64748b;">
                <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📭</div>
                <div style="font-weight: 700; color: #0f172a; font-size: 1rem;">Tidak Ada Tiket QC Pending</div>
                <div style="font-size: 0.825rem; margin-top: 0.25rem;">Semua tiket QC sudah diproses atau belum ada input uji baru dari tim QC.</div>
            </div>
            <table class="excel-grid-table" id="qcModalTable" style="display: none; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">No</th>
                        <th style="width: 135px; text-align: left;">No. Tiket QC</th>
                        <th style="width: 115px; text-align: left;">Waktu Uji</th>
                        <th style="text-align: left;">Supplier &amp; Komoditas / PO</th>
                        <th style="width: 130px; text-align: left;">Truk / Sopir</th>
                        <th style="width: 90px; text-align: right;">Gross (KG)</th>
                        <th style="width: 95px; text-align: right; background: #064e3b; color: #ffffff;">Netto (KG)</th>
                        <th style="width: 75px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="qcModalTbody">
                </tbody>
            </table>
        </div>

        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.65rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.75rem; color: #64748b;">
                💡 <em>Klik pada baris atau tombol <strong>Pilih</strong> untuk memuat data ke formulir penerimaan.</em>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalPilihQc')">Tutup</button>
        </div>
    </div>
</div>
