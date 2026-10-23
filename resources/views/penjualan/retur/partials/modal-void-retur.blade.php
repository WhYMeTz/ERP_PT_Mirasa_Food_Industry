{{-- MODAL VOID RETUR PENJUALAN (INDEX) --}}
<div id="modalVoidReturIndex" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 999999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-container" style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; justify-content: space-between; background: #fef2f2;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #dc2626; color: #ffffff; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #991b1b; margin: 0;">Batalkan Dokumen Retur (Void)</h3>
                    <p style="font-size: 0.75rem; color: #b91c1c; margin: 0.15rem 0 0 0;" id="modalVoidReturNoLbl">-</p>
                </div>
            </div>
            <button type="button" onclick="closeVoidModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formVoidReturIndex" method="POST" style="margin: 0;">
            @csrf
            <div style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.75rem; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; font-size: 0.8rem; color: #c53030; line-height: 1.4;">
                    <strong>Dampak Operasional:</strong> Membatalkan retur ini akan otomatis memotong dan mengeluarkan kembali stok fisik barang retur dari gudang, serta mengembalikan nilai piutang tagihan customer ke kondisi semula.
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Pembatalan Dokumen <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Kesalahan input kuantitas retur / klaim customer dibatalkan..." style="width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; resize: vertical;"></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeVoidModal()" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; color: #ffffff; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight: 700;">
                    Konfirmasi Void Retur
                </button>
            </div>
        </form>
    </div>
</div>
