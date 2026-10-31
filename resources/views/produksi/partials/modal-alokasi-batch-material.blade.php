{{-- 
    Partial Modal: Alokasi Batch Fisik Tambahan dari Gudang (Universal Multi-Barang)
    Standar: Enterprise Window & Separation of Concerns (AGENTS.md)
--}}
<div id="modalAmbilBatchMaterial" class="modal-overlay" onclick="if(event.target === this) closeModalAmbilBatchMaterial()">
    <div class="modal-dialog-custom">
        {{-- 1. Header Modal Enterprise --}}
        <div class="batch-modal-header">
            <div class="batch-modal-header-left">
                <div class="batch-modal-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="batch-modal-title">Alokasi Batch Fisik Tambahan</h3>
                    <p class="batch-modal-subtitle">
                        Komponen: <strong id="modalBatchKomponenNama">Bahan Baku</strong> 
                        <span id="modalBatchKomponenKode" style="color: #64748b; font-size: 0.75rem;">(BBL-01)</span>
                    </p>
                </div>
            </div>
            <button type="button" class="batch-modal-close-btn" onclick="closeModalAmbilBatchMaterial()" title="Tutup Modal (Esc)">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- 2. Body Modal --}}
        <div class="batch-modal-body">
            {{-- Snapshot Metrik Awal --}}
            <div class="batch-snapshot-grid">
                <div class="batch-snapshot-card">
                    <div class="batch-snapshot-card-label">Pemakaian Awal Dokumen</div>
                    <div class="batch-snapshot-card-val">
                        <span id="modalBatchAwalQty">0,00</span>
                        <span id="modalBatchAwalSatuan" style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Kg</span>
                    </div>
                </div>
                <div class="batch-snapshot-card">
                    <div class="batch-snapshot-card-label">Batch Utama Dokumen</div>
                    <div class="batch-snapshot-card-val">
                        <span id="modalBatchAwalList" class="adj-batch-pill">-</span>
                    </div>
                </div>
            </div>

            {{-- 1. Dropdown Pilih Batch Gudang --}}
            <div class="batch-form-group">
                <label for="modal_select_batch" class="batch-form-label">
                    <span>Pilih Batch Gudang Sumber Pengambilan</span>
                    <span class="batch-label-badge">Wajib</span>
                </label>
                <div class="batch-select-wrapper">
                    <select id="modal_select_batch" class="batch-custom-select" onchange="onModalBatchChange(this)">
                        <option value="">-- Pilih Nomor Batch Gudang --</option>
                    </select>
                    <div class="batch-select-chevron">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="modalBatchHint" class="batch-hint-pill">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="modalBatchHintText">Sisa tersedia di gudang: -</span>
                </div>
            </div>

            {{-- 2. Input Kuantitas Fisik Tambahan --}}
            <div class="batch-form-group">
                <label for="modal_input_qty" class="batch-form-label">
                    <span>Kuantitas Fisik Tambahan yang Diambil</span>
                    <span class="batch-label-badge">Wajib</span>
                </label>
                <div class="batch-input-group">
                    <input type="number" step="0.01" min="0.01" id="modal_input_qty" class="batch-input-field" placeholder="0.00" oninput="onModalQtyChange(this.value)">
                    <span id="modalBatchInputAddon" class="batch-input-addon">Kg</span>
                </div>
            </div>

            {{-- 3. Live Simulation & Ringkasan Dampak Finansial --}}
            <div id="modalSummaryBox" class="batch-summary-card">
                <div class="batch-summary-row">
                    <span class="batch-summary-label">Total Pemakaian Baru:</span>
                    <span id="modalSummaryTotalQty" class="batch-summary-val" style="color: #0284c7;">0,00 Kg</span>
                </div>
                <div class="batch-summary-row">
                    <span class="batch-summary-label">Tambahan Biaya Masuk HPP:</span>
                    <span id="modalSummaryAddCost" class="batch-summary-val" style="color: #059669;">+Rp 0</span>
                </div>
                <div class="batch-summary-notice">
                    <svg width="16" height="16" fill="none" stroke="#0284c7" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Kartu stok fisik gudang untuk batch terpilih akan <strong>otomatis dipotong</strong> oleh sistem saat lembar kerja ini disimpan.</span>
                </div>
            </div>
        </div>

        {{-- 3. Footer Modal --}}
        <div class="batch-modal-footer">
            <button type="button" class="btn-batch-modal-cancel" onclick="closeModalAmbilBatchMaterial()">
                Batal
            </button>
            <button type="button" id="btnApplyBatchModal" class="btn-batch-modal-submit" onclick="applyModalBatchToWorksheet()" disabled>
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Terapkan Tambahan Batch</span>
            </button>
        </div>
    </div>
</div>

