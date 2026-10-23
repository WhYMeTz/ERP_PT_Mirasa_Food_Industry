{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL BATALKAN PENGIRIMAN & KEMBALIKAN STOK FISIK KE GUDANG FG             --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalVoidDelivery" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e2e8f0;">
        
        <div style="background: #fffbeb; border-bottom: 1.5px solid #fde68a; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #92400e; margin: 0;">
                        Batalkan Pengiriman (Restore Stok)
                    </h3>
                    <span style="font-size: 0.75rem; color: #b45309;">
                        Kembalikan seluruh stok barang jadi ke Gudang FG
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeVoidDeliveryModal()" style="border: none; background: transparent; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formVoidDelivery" method="POST" action="">
            @csrf

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 0.75rem 0.95rem; font-size: 0.8rem; color: #991b1b; line-height: 1.4;">
                    <strong>Perhatian:</strong> Tindakan ini akan mengembalikan seluruh kuantitas karton yang terpotong ke Gudang Barang Jadi (GDG-FG), dan status dokumen SO akan dikembalikan ke <strong>APPROVED (Menunggu Pengiriman)</strong>.
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Pembatalan Pengiriman <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="reason" rows="3" class="form-control" placeholder="Contoh: Armada mengalami kendala operasional, jadwal kirim diundur, atau salah item..." required style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.825rem;"></textarea>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeVoidDeliveryModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Tutup
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; font-size: 0.825rem; padding: 0.45rem 1.15rem; font-weight: 700;">
                    Ya, Batalkan &amp; Kembalikan Stok
                </button>
            </div>
        </form>
    </div>
</div>
