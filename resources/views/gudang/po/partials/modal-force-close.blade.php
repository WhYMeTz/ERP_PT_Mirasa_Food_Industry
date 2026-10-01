{{-- MODAL KONFIRMASI TUTUP PO --}}
<div id="modalForceClose" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 480px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <strong style="color: #0f172a; font-size: 1rem;">Tutup PO (Selesai Parsial)</strong>
            <button type="button" onclick="closeForceCloseModal()" style="background: transparent; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('gudang.po.force_close', $po->po_id) }}" method="POST" style="padding: 1.25rem;">
            @csrf
            <p style="color: #475569; font-size: 0.875rem; margin-top: 0; margin-bottom: 0.75rem; line-height: 1.4;">
                Gunakan fungsi ini jika sisa barang tidak akan dikirim lagi oleh supplier. Status PO akan diubah menjadi <strong>Ditutup</strong> dan sisa kuota ({{ number_format($po->total_sisa_qty, 2) }}) dibatalkan.
            </p>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="closed_reason" class="form-label" style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                    Alasan Penutupan <span style="color:#ef4444;">*</span>
                </label>
                <textarea id="closed_reason" name="closed_reason" rows="3" class="form-control" placeholder="Tuliskan alasan penutupan PO..." required minlength="5"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeForceCloseModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">
                    Simpan &amp; Tutup PO
                </button>
            </div>
        </form>
    </div>
</div>
