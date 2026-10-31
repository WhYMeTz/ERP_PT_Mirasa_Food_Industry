{{-- Modal Penolakan PO (Reject) --}}
<div id="modalRejectPo" class="modal-review-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalRejectTitle">
    <div class="modal-review-content">
        <div class="modal-review-header header-reject">
            <h3 class="modal-review-title" id="modalRejectTitle">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tolak Pengadaan (Reject PO)
            </h3>
            <button type="button" class="btn-close-modal" onclick="closeReviewModal('modalRejectPo')">&times;</button>
        </div>
        
        <form id="formRejectPo" action="{{ route('gudang.po.reject_internal', $po->po_id) }}" method="POST">
            @csrf
            <div class="modal-review-body">
                <p class="modal-instruction-text" style="color: #991b1b; background: #fef2f2; padding: 0.75rem; border-radius: 6px; border: 1px solid #fee2e2;">
                    <strong>Perhatian:</strong> Menolak pengadaan akan membatalkan pemesanan ini secara permanen. Dokumen <strong>{{ $po->po_no }}</strong> tidak dapat diajukan kembali tanpa membuat PO baru.
                </p>

                <div style="margin-bottom: 0.5rem;">
                    <label for="reject_reason" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Alasan Resmi Penolakan: <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea 
                        name="reject_reason" 
                        id="reject_reason" 
                        class="modal-textarea" 
                        required 
                        rows="4"
                        placeholder="Sebutkan pertimbangan manajemen mengapa pengadaan ini dibatalkan/ditolak secara resmi."></textarea>
                </div>
            </div>

            <div class="modal-review-footer">
                <button type="button" class="btn-executive btn-executive-secondary" onclick="closeReviewModal('modalRejectPo')">
                    Batal
                </button>
                <button type="submit" class="btn-executive btn-executive-reject" id="btnSubmitReject">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Konfirmasi Tolak Pengadaan
                </button>
            </div>
        </form>
    </div>
</div>
