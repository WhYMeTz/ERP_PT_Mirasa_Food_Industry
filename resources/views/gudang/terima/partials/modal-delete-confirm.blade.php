{{-- MODAL KONFIRMASI HAPUS / BATALKAN PENERIMAAN BARANG --}}
<div id="modal-delete-terima" class="hazard-modal-backdrop" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="delete-terima-modal-title">
    <div class="hazard-modal-container">
        <div class="hazard-modal-header">
            <div class="hazard-modal-header-left">
                <div class="hazard-modal-icon-badge">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 id="delete-terima-modal-title" class="hazard-modal-title">Hapus Dokumen Penerimaan Barang</h3>
            </div>
            <button type="button" onclick="closeDeleteTerimaModal()" class="hazard-modal-close-btn" aria-label="Tutup modal">&times;</button>
        </div>
        <div class="hazard-modal-body">
            <p class="hazard-modal-intro">
                Apakah Anda yakin ingin MENGHAPUS dokumen penerimaan ini:
            </p>
            <div class="hazard-modal-target-box">
                <div class="hazard-modal-target-label">No. Penerimaan (GRN):</div>
                <div id="delete-terima-no" class="hazard-modal-target-code">-</div>
                <div id="delete-terima-supplier" class="hazard-modal-target-sub">-</div>
            </div>
            <div class="hazard-modal-warning-box">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong>Perhatian:</strong> Penghapusan ini akan otomatis menarik kembali (mengurangi) saldo fisik stok di gudang dan mengembalikan kuantitas pesanan yang belum datang pada PO terkait.</span>
            </div>
            <form id="form-delete-terima" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="hazard-modal-actions">
                    <button type="button" onclick="closeDeleteTerimaModal()" class="btn-pill-outline">Batal</button>
                    <button type="submit" class="btn-hazard-submit">
                        Ya, Hapus Dokumen Ini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
