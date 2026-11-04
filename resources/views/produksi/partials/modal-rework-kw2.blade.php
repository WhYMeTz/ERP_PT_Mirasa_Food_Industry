{{-- MODAL SORTIR ULANG / REWORK KW 2 (POIN 23 & POIN 24) --}}
<div id="modalReworkKw2" class="modal-rework-overlay" role="dialog" aria-modal="true" aria-labelledby="modalReworkTitle">
    <div class="modal-rework-container">
        <div class="modal-rework-header">
            <div>
                <h3 id="modalReworkTitle" class="modal-rework-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Sortir Ulang / Rework KW 2</span>
                </h3>
                <div class="modal-rework-subtitle">
                    Penyortiran batch IFM tidak rilis / bongkar barang ekspor tidak disetujui menjadi keripik curah KW 2 dan limbah lengket.
                </div>
            </div>
            <button type="button" class="modal-rework-close" onclick="closeModalReworkKw2()" aria-label="Tutup">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="formReworkKw2" method="POST" action="{{ route('produksi.rework.store') }}">
            @csrf
            <input type="hidden" name="barang_id" id="reworkBarangId" value="">
            <input type="hidden" name="batch_no" id="reworkBatchNo" value="">

            <div class="modal-rework-body">
                {{-- 1. SUMBER BATCH ASAL --}}
                <div class="rework-section-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>1. Batch Sumber yang Disortir Ulang</span>
                </div>

                <div class="rework-form-grid">
                    <div class="rework-form-group">
                        <label class="rework-label">Tanggal Rework <span class="req">*</span></label>
                        <input type="date" name="produksi_tgl" class="rework-input" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="rework-form-group">
                        <label class="rework-label">Lokasi Gudang <span class="req">*</span></label>
                        <select name="gudang_id" id="reworkGudangId" class="rework-select" required onchange="loadBatchesForRework()">
                            @foreach($gudangList as $g)
                                <option value="{{ $g->gudang_id }}">{{ $g->gudang_nm }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rework-form-group col-span-2">
                        <label class="rework-label">Pilih Batch / Barang Asal <span class="req">*</span></label>
                        <select id="reworkBatchSelect" class="rework-select" required onchange="onBatchSelected(this)">
                            <option value="">-- Memuat Data Batch Tersedia... --</option>
                        </select>
                    </div>
                </div>

                {{-- INFO BATCH TERPILIH --}}
                <div id="reworkBatchInfo" class="rework-info-box" style="display: none;">
                    <div class="rework-info-row">
                        <span><strong>Barang Asal:</strong> <span id="infoBarangNm">-</span> (<span id="infoBarangCd">-</span>)</span>
                        <span id="infoBadgeJenis" style="background: #e2e8f0; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; font-size: 0.725rem;">WIP</span>
                    </div>
                    <div class="rework-info-row">
                        <span>Sisa Stok Tersedia: <strong id="infoSisaStok" style="color: #059669;">0</strong> <span id="infoSatuan">-</span></span>
                        <span>Estimasi HPP Satuan: <strong id="infoHppSatuan" style="color: #0f172a;">Rp 0</strong></span>
                    </div>
                </div>

                {{-- 2. KUANTITAS & ALOKASI HASIL SORTIR --}}
                <div class="rework-section-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>2. Alokasi Kuantitas Hasil Sortir Ulang</span>
                </div>

                <div class="rework-form-grid">
                    <div class="rework-form-group col-span-2">
                        <label class="rework-label">Total Kuantitas yang Disortir Ulang <span class="req">*</span></label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" step="0.01" min="0.01" name="qty_rework" id="reworkQtyInput" class="rework-input" placeholder="0.00" required oninput="calculateBalance()">
                            <span id="reworkSatuanLabel" style="font-size: 0.825rem; font-weight: 700; color: #475569; width: 60px;">KG</span>
                        </div>
                    </div>
                </div>

                <div class="rework-allocation-card">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #166534; margin-bottom: 0.75rem;">
                        Rincian Hasil Pemilahan / Sortir Keripik:
                    </div>
                    <div class="rework-form-grid" style="margin-bottom: 0;">
                        <div class="rework-form-group">
                            <label class="rework-label" style="color: #15803d;">
                                🔄 Hasil Baik &rarr; KS CURAH SORTIR (KW 2) (kg) <span class="req">*</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" name="qty_kw2" id="reworkQtyKw2" class="rework-input" placeholder="0.00" required oninput="calculateBalance()">
                        </div>

                        <div class="rework-form-group">
                            <label class="rework-label" style="color: #b45309;">
                                ⚠️ Afkir Lengket &rarr; KS BERKO LENGKET (kg)
                            </label>
                            <input type="number" step="0.01" min="0" name="qty_lengket" id="reworkQtyLengket" class="rework-input" placeholder="0.00" value="0" oninput="calculateBalance()">
                        </div>

                        <div class="rework-form-group col-span-2">
                            <label class="rework-label" style="color: #64748b;">
                                🗑️ Afkir Remahan &rarr; KS REMAHAN (kg)
                            </label>
                            <input type="number" step="0.01" min="0" name="qty_remahan" id="reworkQtyRemahan" class="rework-input" placeholder="0.00" value="0" oninput="calculateBalance()">
                        </div>
                    </div>
                    <div id="balanceCheckMsg" style="margin-top: 0.65rem; font-size: 0.775rem; font-weight: 600; color: #475569;">
                        Total Alokasi: <span id="totalAllocatedVal">0.00</span> kg
                    </div>
                </div>

                {{-- 3. KEMASAN SIRKULAR (HANYA MUNCUL JIKA BARANG ASAL DUS/KARTON) --}}
                <div id="reworkCartonSection" class="rework-carton-card">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #1e293b; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.35rem;">
                        <span>📦 Pemulihan Kemasan Karton Dus (Bongkar Dus)</span>
                    </div>
                    <div class="rework-form-grid" style="margin-bottom: 0;">
                        <div class="rework-form-group">
                            <label class="rework-label">Total Dus Dibongkar (pcs)</label>
                            <input type="number" min="0" name="dus_dibongkar" id="reworkDusDibongkar" class="rework-input" placeholder="0" value="0">
                        </div>
                        <div class="rework-form-group">
                            <label class="rework-label">Dus Layak Pakai &rarr; KARTON PUTERAN (pcs)</label>
                            <input type="number" min="0" name="dus_puteran" id="reworkDusPuteran" class="rework-input" placeholder="0" value="0">
                        </div>
                        <div class="rework-form-group col-span-2">
                            <label class="rework-label">Dus Rusak &rarr; AFKIR DUS (pcs)</label>
                            <input type="number" min="0" name="dus_rusak" id="reworkDusRusak" class="rework-input" placeholder="0" value="0">
                        </div>
                    </div>
                </div>

                {{-- 4. ALASAN QC & CATATAN --}}
                <div class="rework-section-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>3. Alasan QC &amp; Keterangan Sortir</span>
                </div>

                <div class="rework-form-grid" style="margin-bottom: 0;">
                    <div class="rework-form-group col-span-2">
                        <label class="rework-label">Kategori / Alasan QC <span class="req">*</span></label>
                        <select name="alasan_qc" id="reworkAlasanQc" class="rework-select" required onchange="onAlasanQcChange(this)">
                            <option value="Persentase lengket tinggi / tidak jadi rilis IFM (Poin 23)">
                                [IFM] Persentase lengket tinggi / tidak jadi rilis IFM (Poin 23)
                            </option>
                            <option value="Barang reject tidak disetujui QC buyer ekspor (Poin 24)">
                                [EKSPOR] Barang reject tidak disetujui QC buyer ekspor (Poin 24)
                            </option>
                            <option value="Kemasan bocor / rusak saat penyimpanan atau handling">
                                [GUDANG] Kemasan bocor / rusak saat penyimpanan atau handling
                            </option>
                            <option value="Lainnya">
                                [LAINNYA] Keterangan Lain (Tulis di catatan)
                            </option>
                        </select>
                    </div>

                    <div class="rework-form-group col-span-2">
                        <label class="rework-label">Catatan Tambahan</label>
                        <textarea name="catatan_txt" id="reworkCatatanTxt" class="rework-textarea" rows="2" placeholder="Catatan hasil inspeksi QC / keterangan pengerjaan..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-rework-footer">
                <button type="button" class="btn-rework-cancel" onclick="closeModalReworkKw2()">Batal</button>
                <button type="submit" class="btn-rework-submit" id="btnSubmitRework">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Update Stok</span>
                </button>
            </div>
        </form>
    </div>
</div>

