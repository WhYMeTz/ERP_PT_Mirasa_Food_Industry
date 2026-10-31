{{-- Modal Konfirmasi Setujui PO --}}
<div id="modalApprovePo" class="modal-review-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalApproveTitle">
    <div class="modal-review-content">
        <div class="modal-review-header header-approve">
            <h3 class="modal-review-title" id="modalApproveTitle">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Persetujuan Pengadaan (Approve PO)
            </h3>
            <button type="button" class="btn-close-modal" onclick="closeReviewModal('modalApprovePo')">&times;</button>
        </div>
        
        <form id="formApprovePo" action="{{ route('gudang.po.approve_internal', $po->po_id) }}" method="POST">
            @csrf
            <div class="modal-review-body">
                <p class="modal-instruction-text">
                    Apakah Anda yakin ingin menyetujui dokumen <strong>{{ $po->po_no }}</strong> sebesar 
                    <strong style="color: #059669;">Rp {{ number_format($po->total_tagihan ?? $po->grand_total, 0, ',', '.') }}</strong>?
                    <br><br>
                    Setelah disetujui, sistem akan secara otomatis menerbitkan <strong>QR-Code Legalitas Digital</strong> dan dokumen dinyatakan sah untuk diproses bagian pembelian / gudang.
                </p>

                <div style="margin-bottom: 0.5rem;">
                    <label for="approval_notes" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Catatan Persetujuan / Pesan untuk Gudang (Opsional):
                    </label>
                    <textarea 
                        name="notes" 
                        id="approval_notes" 
                        class="modal-textarea" 
                        placeholder="Contoh: Disetujui sesuai kuota anggaran bulan ini. Pastikan barang diterima tepat waktu."></textarea>
                </div>
            </div>

            <div class="modal-review-footer">
                <button type="button" class="btn-executive btn-executive-secondary" onclick="closeReviewModal('modalApprovePo')">
                    Batal
                </button>
                <button type="submit" class="btn-executive btn-executive-approve" id="btnSubmitApprove">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Ya, Setujui Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
