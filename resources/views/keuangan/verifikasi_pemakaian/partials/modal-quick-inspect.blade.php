{{-- MODAL QUICK INSPECT & EDIT PEMAKAIAN BAHAN --}}
<div id="modalQuickInspect" class="qi-modal-backdrop" style="display: none;">
    <div class="qi-modal-dialog">
        {{-- MODAL HEADER --}}
        <div class="qi-modal-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="qi-header-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h2 class="qi-modal-title">Pemeriksaan Cepat Pemakaian Bahan (HPP)</h2>
                        <span id="qiHeaderStatusBadge" class="badge-status badge-draft">⏳ DRAFT</span>
                    </div>
                    <div class="qi-modal-subtitle">
                        No. BPPB: <strong id="qiHeaderPakaiNo" style="font-family: monospace; color: #ea580c;">-</strong>
                        &bull; Oleh: <span id="qiHeaderCreatedBy">-</span>
                    </div>
                </div>
            </div>
            <button type="button" class="qi-btn-close" onclick="closeQuickInspectModal()">&times;</button>
        </div>

        {{-- LOADING SPINNER --}}
        <div id="qiLoadingSpinner" class="qi-loading-container">
            <div class="qi-spinner"></div>
            <div style="margin-top: 0.75rem; font-size: 0.85rem; color: #64748b; font-weight: 600;">
                Memuat data rincian pemakaian &amp; batch FIFO...
            </div>
        </div>

        {{-- MODAL BODY --}}
        <div id="qiModalBody" class="qi-modal-body" style="display: none;">
            {{-- TOOLBAR INFO FORM --}}
            <div class="qi-info-grid">
                <div>
                    <label class="qi-label">Tanggal Pengeluaran <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="qi_pakai_tgl" class="qi-input">
                </div>
                <div>
                    <label class="qi-label">Gudang Asal <span style="color: #ef4444;">*</span></label>
                    <select id="qi_gudang_id" class="qi-select" onchange="onQiGudangChange()">
                        {{-- Populated by JS --}}
                    </select>
                </div>
                <div>
                    <label class="qi-label">Tujuan Pemakaian / SPK <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="qi_tujuan_pemakaian" class="qi-input" placeholder="Contoh: PRODUKSI BERKO / KERIPIK">
                </div>
                <div>
                    <label class="qi-label">Catatan / Memo Verifikator</label>
                    <input type="text" id="qi_catatan_txt" class="qi-input" placeholder="Catatan opsional pemeriksaan">
                </div>
            </div>

            {{-- ALERT NOTIFIKASI DI MODAL --}}
            <div id="qiModalAlert" style="display: none; margin-bottom: 1rem; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.825rem; font-weight: 600;"></div>

            {{-- TABEL ITEM DILENGKAPI GRID VERIFIKASI CEPAT --}}
            <div class="qi-table-wrapper">
                <table class="qi-table" id="qiTableItems">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">#</th>
                            <th style="min-width: 220px;">Nama Bahan / Komponen</th>
                            <th style="width: 85px; text-align: center;">Grade</th>
                            <th style="min-width: 250px;">Batch Terpilih &amp; Sisa Stok</th>
                            <th style="width: 130px; text-align: right;">Qty Keluar</th>
                            <th style="width: 80px; text-align: center;">Satuan</th>
                            <th style="width: 140px; text-align: right;">Harga Satuan (Rp)</th>
                            <th style="width: 150px; text-align: right;">Subtotal (Rp)</th>
                            <th style="width: 45px; text-align: center;" id="qiThAction">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="qiTableBody">
                        {{-- Dynamic rows by JS --}}
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <span style="font-size: 0.775rem; font-weight: 700; color: #475569;">+ Tambah:</span>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addQiRow('BAHAN_BAKU')" style="font-size: 0.775rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd;">
                        🌾 Bahan Baku
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addQiRow('BAHAN_PENOLONG')" style="font-size: 0.775rem; font-weight: 700; color: #d97706; background: #fffbeb; border: 1px solid #fde68a;">
                        🧂 Bumbu / Penolong
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addQiRow('KEMASAN')" style="font-size: 0.775rem; font-weight: 700; color: #059669; background: #f0fdf4; border: 1px solid #bbf7d0;">
                        📦 Kemasan
                    </button>
                    <button type="button" id="qiBtnAddRow" class="btn btn-secondary btn-sm" onclick="addQiRow('')" style="font-size: 0.775rem; font-weight: 600; color: #64748b; background: #ffffff; border: 1px solid #cbd5e1;">
                        + Lainnya
                    </button>
                </div>
                <div style="font-size: 0.775rem; color: #64748b;">
                    💡 Tips: Urutan batch ditata menurut <strong>FIFO (First In First Out)</strong>. Filter Grade A/B menyaring batch otomatis.
                </div>
            </div>

            {{-- LIVE SUMMARY & SIMULASI JURNAL AKUNTANSI --}}
            <div class="qi-summary-container">
                <div class="qi-summary-left">
                    <div style="font-size: 0.775rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.35rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Simulasi Jurnal Akuntansi Otomatis (HPP)
                    </div>
                    <div class="qi-journal-box">
                        <div style="display: flex; justify-content: space-between; color: #059669; font-weight: 700;">
                            <span>(D) 1151 - Biaya Bahan Baku Dalam Proses (WIP)</span>
                            <span id="qiJournalDebet" style="font-family: monospace;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: #dc2626; font-weight: 700; margin-top: 0.25rem;">
                            <span style="padding-left: 1rem;">(K) 1150 - Persediaan Bahan Baku &amp; Penolong</span>
                            <span id="qiJournalKredit" style="font-family: monospace;">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div class="qi-summary-right">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem;">
                        <span>Total Kuantitas Fisik:</span>
                        <strong id="qiTotalQty" style="color: #0f172a; font-family: monospace;">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px dashed #cbd5e1; padding-top: 0.35rem;">
                        <span style="font-size: 0.9rem; font-weight: 700; color: #334155;">Total Estimasi HPP:</span>
                        <strong id="qiTotalHpp" style="font-size: 1.35rem; color: #ea580c; font-family: monospace;">Rp 0</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL FOOTER --}}
        <div class="qi-modal-footer">
            <div class="qi-footer-left">
                <a id="qiBtnFullPage" href="#" class="qi-btn-link" target="_self">
                    <span>Buka Halaman Lengkap</span>
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <button type="button" id="qiBtnReject" class="btn btn-secondary btn-sm" onclick="triggerQiReject()" style="color: #dc2626; border-color: #fecaca; background: #fef2f2; font-weight: 700;">
                    Tolak / Batalkan
                </button>
            </div>

            <div class="qi-footer-right">
                <button type="button" class="btn btn-secondary" onclick="closeQuickInspectModal()" style="font-size: 0.85rem; font-weight: 600;">
                    Tutup (Esc)
                </button>
                <button type="button" id="qiBtnSaveDraft" class="btn btn-secondary" onclick="submitQiForm('save_draft')" style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-weight: 700; font-size: 0.85rem;">
                    💾 Simpan Koreksi (Draft)
                </button>
                <button type="button" id="qiBtnApprove" class="btn btn-primary" onclick="submitQiForm('save_and_approve')" style="background: #059669; border-color: #059669; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(5,150,105,0.25);">
                    ⚡ Simpan &amp; Setujui (Posting Jurnal)
                </button>
            </div>
        </div>
    </div>
</div>
