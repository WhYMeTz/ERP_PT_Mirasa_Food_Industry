{{-- MODAL REKOMENDASI STOK KRITIS (macOS Window Style ERP Mirasa) --}}
<div id="modalSafetyStock" class="modal-backdrop">
    <div class="modal-dialog mac-window-dialog">
        
        {{-- 1. macOS Titlebar --}}
        <div class="mac-modal-titlebar">
            {{-- Window Controls: Traffic Lights --}}
            <div class="mac-modal-traffic" aria-label="Window Controls">
                <button type="button" class="mac-dot red" onclick="closeSafetyStockModal()" title="Tutup (Esc)">
                    <span class="mac-icon">&times;</span>
                </button>
                <button type="button" class="mac-dot yellow" onclick="closeSafetyStockModal()" title="Minimalkan">
                    <span class="mac-icon">&minus;</span>
                </button>
                <button type="button" class="mac-dot green" onclick="toggleSafetyModalMaximize()" title="Maksimalkan Ukuran">
                    <span class="mac-icon">&plus;</span>
                </button>
            </div>

            {{-- Centered macOS Window Title --}}
            <div class="mac-modal-title">
                <svg width="15" height="15" fill="none" stroke="#d97706" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Rekomendasi Stok Kritis &amp; Defisit Gudang</span>
                <span id="modal_safety_count_badge" class="mac-title-chip">
                    {{ !empty($belowMinimumList) ? $belowMinimumList->count() : 0 }} Item
                </span>
            </div>

            {{-- Right: Shortcut hint --}}
            <div style="display: flex; align-items: center; gap: 6px;">
                <button type="button" onclick="closeSafetyStockModal()" style="background: transparent; border: none; font-size: 0.725rem; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 4px;">
                    <kbd style="background: rgba(0,0,0,0.06); padding: 1px 5px; border-radius: 3px; font-family: monospace; font-size: 0.7rem; font-weight: 700;">esc</kbd>
                </button>
            </div>
        </div>

        {{-- 2. macOS Toolbar: Search & Segmented Control --}}
        <div class="mac-modal-toolbar">
            <div class="mac-toolbar-top">
                {{-- Search Box --}}
                <div class="mac-search-wrapper">
                    <span class="mac-search-icon">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" 
                           id="modal_safety_search" 
                           class="mac-search-input" 
                           placeholder="Cari berdasarkan nama komoditas atau kode barang..." 
                           oninput="renderSafetyStockModalTable();"
                           autocomplete="off">
                </div>

                {{-- Quick Bulk Action --}}
                <button type="button" 
                        id="btnInsertAllSafetyItems"
                        onclick="insertAllFilteredSafetyItems()" 
                        class="mac-btn-insert-all">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Masukkan Semua Item Defisit</span>
                </button>
            </div>

            {{-- macOS Native Capsule Segmented Control --}}
            <div class="mac-segmented-control" role="tablist" aria-label="Filter Kategori Komoditas">
                <button type="button" class="mac-segment-btn active" data-tab="ALL" onclick="filterSafetyStockModalTab('ALL', this)">
                    <span>Semua Komoditas</span>
                    <span class="mac-seg-count" id="countTabAll">0</span>
                </button>
                <button type="button" class="mac-segment-btn" data-tab="BB" onclick="filterSafetyStockModalTab('BB', this)">
                    <span>Bahan Baku</span>
                    <span class="mac-seg-count" id="countTabBb">0</span>
                </button>
                <button type="button" class="mac-segment-btn" data-tab="BUMBU" onclick="filterSafetyStockModalTab('BUMBU', this)">
                    <span>Bumbu &amp; Penolong</span>
                    <span class="mac-seg-count" id="countTabBumbu">0</span>
                </button>
                <button type="button" class="mac-segment-btn" data-tab="PACK" onclick="filterSafetyStockModalTab('PACK', this)">
                    <span>Kemasan &amp; Karton</span>
                    <span class="mac-seg-count" id="countTabPack">0</span>
                </button>
            </div>
        </div>

        {{-- 3. macOS Data Table --}}
        <div class="mac-modal-table-wrap">
            <table class="mac-modal-table">
                <thead>
                    <tr>
                        <th style="width: 38px; text-align: center;">#</th>
                        <th style="min-width: 220px; text-align: left;">Komoditas &amp; Kode</th>
                        <th style="width: 120px; text-align: left;">Kategori</th>
                        <th style="width: 120px; text-align: right;">Stok Riil</th>
                        <th style="width: 120px; text-align: right;">Min. Pabrik</th>
                        <th style="width: 130px; text-align: right; color: #b45309;">Kekurangan Defisit</th>
                        <th style="width: 120px; text-align: right;">Harga Std.</th>
                        <th style="width: 110px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="safety_modal_tbody">
                    {{-- Dinamis via po-create.js --}}
                </tbody>
            </table>
        </div>

        {{-- 4. macOS Window Status Bar --}}
        <div class="mac-modal-statusbar">
            <div style="font-size: 0.775rem; color: #64748b;">
                Menampilkan: <strong id="modal_safety_count_text" style="color: #0f172a; font-family: monospace;">0 komoditas</strong>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeSafetyStockModal()" style="padding: 0.35rem 0.85rem; font-size: 0.775rem; font-weight: 600; border-radius: 6px;">
                    Tutup
                </button>
                <button type="button" onclick="insertAllFilteredSafetyItems()" class="btn btn-primary btn-sm" style="background: #059669; border-color: #059669; padding: 0.35rem 0.95rem; font-size: 0.775rem; font-weight: 700; border-radius: 6px;">
                    Terapkan Semua ke PO
                </button>
            </div>
        </div>
    </div>
</div>
