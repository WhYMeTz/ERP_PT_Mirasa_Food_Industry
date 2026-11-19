{{-- Modal Dialog Konfirmasi Void Transfer Antar-Gudang --}}
<div id="modalVoidTransfer" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 10px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; border: 1px solid #cbd5e1;">
        
        {{-- Header Bahaya Merah --}}
        <div style="background: #fef2f2; border-bottom: 1.5px solid #fecaca; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #dc2626;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #991b1b; margin: 0;">Konfirmasi Pembatalan (VOID)</h3>
                <span style="font-size: 0.775rem; color: #b91c1c;">Tindakan ini akan mengembalikan mutasi stok barang</span>
            </div>
        </div>

        <form method="POST" id="formVoidTransfer">
            @csrf
            <div style="padding: 1.5rem;">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.8125rem; color: #92400e; line-height: 1.45;">
                    Perhatian: Seluruh stok barang pada dokumen <strong><span id="voidTransferNoDisplay">TRF-XXXX</span></strong> yang telah diterima di Gudang Tujuan akan ditarik kembali dan dikembalikan ke Gudang Asal.
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <label for="voidAlasanInput" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Alasan Pembatalan Operasional <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="alasan_void" id="voidAlasanInput" rows="3" required
                              style="width: 100%; font-size: 0.8125rem; padding: 0.65rem 0.75rem; border-radius: 6px; border: 1.5px solid #cbd5e1; box-sizing: border-box;"
                              placeholder="Contoh: Salah pilih batch barang, pembatalan jadwal kirim truk armada..."></textarea>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeVoidTransferModal()" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" style="background: #dc2626; border: none; color: #ffffff; font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Ya, Batalkan (VOID)</span>
                </button>
            </div>
        </form>
    </div>
</div>
