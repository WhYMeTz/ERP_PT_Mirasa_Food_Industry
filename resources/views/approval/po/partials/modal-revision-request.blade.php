{{-- Modal Minta Revisi / Tinjauan PO --}}
<div id="modalRevisionPo" class="modal-review-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalRevisionTitle">
    <div class="modal-review-content">
        <div class="modal-review-header header-revision">
            <h3 class="modal-review-title" id="modalRevisionTitle">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Instruksi Tinjauan & Revisi PO
            </h3>
            <button type="button" class="btn-close-modal" onclick="closeReviewModal('modalRevisionPo')">&times;</button>
        </div>
        
        <form id="formRevisionPo" action="{{ route('gudang.po.request_revision', $po->po_id) }}" method="POST">
            @csrf
            <div class="modal-review-body">
                <p class="modal-instruction-text">
                    Status PO <strong>{{ $po->po_no }}</strong> akan dialihkan kembali menjadi <strong>PERLU REVISI</strong>. Tim gudang atau purchasing akan menerima instruksi Anda untuk melakukan penyesuaian draft PO sebelum diajukan kembali.
                </p>

                <div style="margin-bottom: 0.5rem;">
                    <label for="revision_notes" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Poin Koreksi / Instruksi Revisi: <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea 
                        name="revision_notes" 
                        id="revision_notes" 
                        class="modal-textarea" 
                        required 
                        rows="4"
                        placeholder="Contoh: Kuantitas Tepung Terigu dikurangi 1.000 KG dulu karena kapasitas gudang terbatas, atau negosiasi ulang harga satuan karena ada kenaikan di atas harga acuan pabrik."></textarea>
                </div>
            </div>

            <div class="modal-review-footer">
                <button type="button" class="btn-executive btn-executive-secondary" onclick="closeReviewModal('modalRevisionPo')">
                    Batal
                </button>
                <button type="submit" class="btn-executive btn-executive-revision" id="btnSubmitRevision">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Instruksi Revisi
                </button>
            </div>
        </form>
    </div>
</div>
