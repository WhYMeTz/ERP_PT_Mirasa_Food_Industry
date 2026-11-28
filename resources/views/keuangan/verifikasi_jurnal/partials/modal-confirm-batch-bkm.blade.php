{{-- Modal Konfirmasi Verifikasi Masal Jurnal Kas Masuk (BKM) --}}
<div id="modalConfirmBatchBkm" class="vj-modal-backdrop" style="display: none;" aria-hidden="true">
    <div class="vj-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalBatchBkmTitle">
        <div class="vj-modal-header">
            <div class="vj-modal-header-info">
                <span class="vj-kicker">VERIFIKASI MASAL AKUNTANSI</span>
                <h3 id="modalBatchBkmTitle" class="vj-modal-title">Konfirmasi Terbitkan Jurnal Masal Kas Masuk</h3>
            </div>
            <button type="button" class="vj-modal-close" onclick="closeBatchConfirmModalBkm()" aria-label="Tutup modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="vj-modal-body">
            <div class="vj-confirm-summary-box">
                <div class="vj-confirm-metric">
                    <span class="vj-confirm-metric-label">Jumlah Transaksi Terpilih</span>
                    <span class="vj-confirm-metric-val text-emerald-700" id="batchConfirmBkmCount">0 Dokumen</span>
                </div>
                <div class="vj-confirm-metric">
                    <span class="vj-confirm-metric-label">Total Nilai Penerimaan</span>
                    <span class="vj-confirm-metric-val text-emerald-700" id="batchConfirmBkmTotal">Rp 0</span>
                </div>
            </div>

            <p class="vj-confirm-desc">
                Sistem akan memverifikasi seluruh bukti kas masuk terpilih dan menerbitkan transaksi Jurnal Umum berpasangan secara otomatis (Debet: Kas/Bank, Kredit: COA Lawan terpilih). Pastikan akun COA kredit dan keterangan pada setiap baris telah sesuai.
            </p>

            {{-- Ringkasan Item Terpilih --}}
            <div class="vj-batch-preview-wrapper">
                <div class="vj-batch-preview-header">
                    <span>Ringkasan BKM &amp; Pos Akun Kredit Lawan</span>
                </div>
                <div class="vj-batch-preview-list" id="batchPreviewListBkm">
                    {{-- Diisi secara dinamis oleh JavaScript --}}
                </div>
            </div>

            <div class="vj-confirm-notice">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Seluruh baris terpilih akan diproses dalam 1 transaksi database atomik (ACID). Total Debet dan Kredit dipastikan seimbang (selisih Rp 0).</span>
            </div>
        </div>

        <div class="vj-modal-footer">
            <button type="button" class="vj-btn vj-btn-secondary" onclick="closeBatchConfirmModalBkm()" id="btnCancelBatchBkm">
                <span>Batal</span>
            </button>
            <button type="button" class="vj-btn-batch-primary" id="btnExecuteBatchBkm" onclick="submitBatchVerificationBkm()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Ya, Terbitkan Jurnal Sekarang</span>
            </button>
        </div>
    </div>
</div>
