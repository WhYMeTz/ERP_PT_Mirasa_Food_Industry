<div class="modal-backdrop" id="modalDeleteHutang">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header" style="background: #fef2f2; border-bottom: 1.5px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                    ⚠️
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #991b1b;">Batalkan Tagihan Hutang</h3>
                    <div style="font-size: 0.75rem; color: #b91c1c;">Tindakan ini akan membatalkan kewajiban tagihan pemasok</div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteHutang')">&times;</button>
        </div>

        <form id="formDeleteHutang" method="POST" action="">
            @csrf
            @method('DELETE')
            
            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                <p style="font-size: 0.875rem; color: #334155; line-height: 1.5; margin: 0 0 1rem 0;">
                    Apakah Anda yakin ingin membatalkan tagihan <strong id="deleteHutangNoText" style="color: #0f172a;"></strong> senilai <strong id="deleteHutangValText" style="color: #0f172a;"></strong>?
                </p>

                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.78rem; color: #92400e;">
                    <strong>Perhatian:</strong>
                    Tagihan hanya dapat dibatalkan jika belum memiliki riwayat pembayaran (BKK). GRN yang terkait akan kembali ke status belum ditagihkan.
                </div>
            </div>

            <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.6rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteHutang')">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; color: #ffffff; border: none; font-weight: 700; border-radius: 6px; padding: 0.5rem 1.2rem;">
                    Ya, Batalkan Tagihan
                </button>
            </div>
        </form>
    </div>
</div>
