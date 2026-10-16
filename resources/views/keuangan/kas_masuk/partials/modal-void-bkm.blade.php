{{-- MODAL VOID / PEMBATALAN BUKTI KAS MASUK (BKM) --}}
<div id="modalVoidBkm" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 999999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Header Bahaya --}}
        <div style="background: #fef2f2; border-bottom: 1px solid #fee2e2; padding: 1.25rem 1.5rem; display: flex; align-items: flex-start; gap: 0.875rem;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #991b1b; margin: 0;">
                    Batalkan Bukti Kas Masuk (Void)
                </h3>
                <p style="font-size: 0.8rem; color: #b91c1c; margin: 0.25rem 0 0 0; line-height: 1.4;">
                    Tindakan ini akan membatalkan voucher dan <strong>mengurangi kembali saldo rekening kas/bank</strong> yang bersangkutan.
                </p>
            </div>
        </div>

        {{-- Form Body --}}
        <form id="formVoidBkm" method="POST" action="">
            @csrf
            <div style="padding: 1.5rem;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.875rem 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8rem; color: #64748b;">No. Bukti Kas Masuk:</span>
                        <strong id="voidBkmNo" style="font-size: 0.85rem; color: #0f172a; font-family: ui-monospace, SFMono-Regular, monospace;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 0.8rem; color: #64748b;">Nominal Dikoreksi:</span>
                        <strong id="voidBkmTotal" style="font-size: 0.95rem; color: #dc2626; font-family: ui-monospace, SFMono-Regular, monospace;">Rp 0</strong>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="voidAlasan" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Alasan Pembatalan / Void <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="alasan" 
                              id="voidAlasan" 
                              rows="3" 
                              class="form-control" 
                              placeholder="Contoh: Salah input rekening tujuan / transaksi dibatalkan oleh penyetor..." 
                              required 
                              style="font-size: 0.85rem; line-height: 1.4; resize: vertical;"></textarea>
                    <span style="font-size: 0.725rem; color: #64748b; margin-top: 0.35rem; display: block;">
                        Alasan pembatalan akan tercatat secara permanen di buku mutasi kas dan audit trail.
                    </span>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.875rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" 
                        class="btn btn-secondary" 
                        onclick="closeModalVoidBkm()" 
                        style="font-size: 0.825rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 6px;">
                    Batal
                </button>
                <button type="submit" 
                        class="btn" 
                        style="background: #dc2626; color: #ffffff; border: none; font-size: 0.825rem; font-weight: 700; padding: 0.5rem 1.25rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);">
                    Ya, Batalkan Voucher
                </button>
            </div>
        </form>
    </div>
</div>
