{{-- Modal Katalog & Picker Stok Gudang Asal --}}
<div id="modalStokPicker" class="stok-modal-backdrop" style="display: none;">
    <div class="stok-modal-dialog">
        <div class="stok-modal-header">
            <div>
                <h3 class="stok-modal-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Katalog Stok Gudang Asal</span>
                </h3>
                <p class="stok-modal-subtitle">Pilih barang dan nomor batch yang tersedia untuk langsung dimasukkan ke daftar transfer.</p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeStokPickerModal()" title="Tutup Modal">&times;</button>
        </div>

        <div class="stok-modal-body">
            {{-- FILTER TABS & SEARCH --}}
            <div class="picker-filter-bar">
                <div class="picker-category-tabs" id="modalCategoryTabs">
                    <button type="button" class="btn-picker-tab active" data-category="ALL" onclick="filterModalCategory('ALL')">
                        Semua (<span id="modalCountAll">0</span>)
                    </button>
                    <button type="button" class="btn-picker-tab" data-category="BAHAN_BAKU" onclick="filterModalCategory('BAHAN_BAKU')">
                        🌾 Bahan Baku (<span id="modalCountBB">0</span>)
                    </button>
                    <button type="button" class="btn-picker-tab" data-category="BAHAN_PENOLONG" onclick="filterModalCategory('BAHAN_PENOLONG')">
                        🧪 Bahan Penolong (<span id="modalCountBP">0</span>)
                    </button>
                    <button type="button" class="btn-picker-tab" data-category="WIP" onclick="filterModalCategory('WIP')">
                        🏭 Stok WIP (<span id="modalCountWIP">0</span>)
                    </button>
                    <button type="button" class="btn-picker-tab" data-category="FG" onclick="filterModalCategory('FG')">
                        📦 Barang Jadi (FG) (<span id="modalCountFG">0</span>)
                    </button>
                </div>

                <div class="picker-search-box">
                    <svg width="16" height="16" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="modalSearchInput" placeholder="Cari kode/nama barang, batch..." oninput="filterModalSearch(this.value)">
                </div>
            </div>

            {{-- DAFTAR BATCH TERSEDIA --}}
            <div class="picker-table-wrapper">
                <table class="picker-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="modalSelectAll" onchange="toggleSelectAllModal(this)">
                            </th>
                            <th style="width: 140px;">Kategori</th>
                            <th>Nama Barang &amp; Kode</th>
                            <th style="width: 180px;">Nomor Batch &amp; Grade</th>
                            <th style="width: 130px; text-align: right;">Sisa Stok</th>
                            <th style="width: 160px; text-align: right;">Qty Transfer</th>
                        </tr>
                    </thead>
                    <tbody id="modalBatchesBody">
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 2.5rem;">
                                Silakan pilih Gudang Asal terlebih dahulu.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stok-modal-footer">
            <div style="font-size: 0.8125rem; color: #475569;">
                Terpilih: <strong id="modalSelectedCount" style="color: #0d9488;">0</strong> item batch
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeStokPickerModal()" style="font-size: 0.8125rem; font-weight: 600;">
                    Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmModalSelection()" style="background: #0d9488; border-color: #0f766e; color: #fff; font-size: 0.8125rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambahkan ke Tabel Transfer</span>
                </button>
            </div>
        </div>
    </div>
</div>
