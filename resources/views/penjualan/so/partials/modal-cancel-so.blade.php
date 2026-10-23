{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL BATALKAN DOKUMEN PO PENJUALAN (VOID / CANCEL)                        --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalCancelSo" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e2e8f0;">
        
        <div style="background: #fef2f2; border-bottom: 1.5px solid #fecaca; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #991b1b; margin: 0;">
                        Batalkan PO Penjualan
                    </h3>
                    <span style="font-size: 0.75rem; color: #b91c1c;">
                        Dokumen: <strong id="modalCancelSoNo">-</strong>
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeCancelSoModal()" style="border: none; background: transparent; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formCancelSo" method="POST" action="">
            @csrf

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.85rem; color: #475569; margin: 0; line-height: 1.4;">
                    Apakah Anda yakin ingin membatalkan pesanan penjualan ini? Tindakan ini akan mengubah status pesanan menjadi <strong>CANCELLED (Dibatalkan)</strong>.
                </p>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Pembatalan <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="reason" rows="3" class="form-control" placeholder="Contoh: Pembatalan sepihak oleh pelanggan, perubahan kuantitas PO, dll..." required style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.825rem;"></textarea>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeCancelSoModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Kembali
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; font-size: 0.825rem; padding: 0.45rem 1.15rem; font-weight: 700;">
                    Ya, Batalkan Pesanan
                </button>
            </div>
        </form>
    </div>
</div>
