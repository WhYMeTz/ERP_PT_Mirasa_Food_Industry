<!-- resources/views/master_data/pajak/partials/modal-delete.blade.php -->
<div id="modalDeletePajak" class="modal-overlay" style="display: none;">
    <div class="modal-dialog modal-danger" style="max-width: 480px;">
        <div class="modal-header modal-header-danger">
            <div class="modal-title-wrap">
                <div class="modal-icon-badge danger-badge">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="modal-title text-danger">Hapus Data Master Pajak</h3>
                    <p class="modal-subtitle">Tindakan ini memerlukan konfirmasi keamanan</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalDeletePajak')">&times;</button>
        </div>

        <form id="formDeletePajak" method="POST" action="">
            @csrf
            @method('DELETE')

            <div class="modal-body">
                <div class="delete-warning-banner">
                    <div class="warning-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="warning-text">
                        <strong>Perhatian:</strong> Data pajak yang dihapus akan ditandai non-aktif (soft delete) dan tidak dapat lagi dipilih pada transaksi pembelian (PO/Penerimaan) maupun penjualan/faktur baru.
                    </div>
                </div>

                <div class="tax-info-card danger-card">
                    <div class="tax-info-row">
                        <span class="label">Kode Pajak:</span>
                        <strong id="deletePajakCd" class="value">-</strong>
                    </div>
                    <div class="tax-info-row">
                        <span class="label">Nama Pajak:</span>
                        <strong id="deletePajakNm" class="value">-</strong>
                    </div>
                </div>

                <p class="confirmation-text">
                    Apakah Anda yakin ingin menghapus data master pajak ini?
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeletePajak')">Batal</button>
                <button type="submit" class="btn btn-danger" id="btnSubmitDeletePajak">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Ya, Hapus Pajak</span>
                </button>
            </div>
        </form>
    </div>
</div>
