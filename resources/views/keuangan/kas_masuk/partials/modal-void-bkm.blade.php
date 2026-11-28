{{-- MODAL VOID / PEMBATALAN BUKTI KAS MASUK (BKM) --}}
<div id="modalVoidBkm" class="bkm-modal-backdrop" style="display: none;">
    <div class="bkm-modal-dialog">
        
        {{-- Header Bahaya --}}
        <div class="bkm-modal-header bkm-modal-header-rose">
            <div class="bkm-modal-icon-badge bkm-icon-badge-rose">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="bkm-modal-title bkm-text-rose">
                    Batalkan Bukti Kas Masuk (Void)
                </h3>
                <p class="bkm-modal-desc bkm-text-rose-subtle">
                    Tindakan ini akan membatalkan voucher, <strong>mengurangi kembali saldo rekening kas/bank</strong>, dan membalik jurnal GL jika sudah diterbitkan.
                </p>
            </div>
        </div>

        {{-- Form Body --}}
        <form id="formVoidBkm" method="POST" action="">
            @csrf
            <div class="bkm-modal-body">
                <div class="bkm-info-card">
                    <div class="bkm-info-row">
                        <span class="bkm-info-label">No. Bukti Kas Masuk:</span>
                        <strong id="voidBkmNo" class="bkm-info-value-mono">-</strong>
                    </div>
                    <div class="bkm-info-row">
                        <span class="bkm-info-label">Nominal Dikoreksi:</span>
                        <strong id="voidBkmTotal" class="bkm-info-total bkm-text-rose">Rp 0</strong>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="voidAlasan" class="bkm-field-label">
                        Alasan Pembatalan / Void <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="alasan" 
                              id="voidAlasan" 
                              rows="3" 
                              class="form-control" 
                              placeholder="Contoh: Salah input rekening tujuan / transaksi dibatalkan oleh penyetor..." 
                              required 
                              style="font-size: 0.85rem; line-height: 1.4; resize: vertical;"></textarea>
                    <span class="bkm-field-help">
                        Alasan pembatalan akan tercatat secara permanen di buku mutasi kas dan audit trail.
                    </span>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="bkm-modal-footer">
                <button type="button" 
                        class="btn-pill-outline" 
                        onclick="closeModalVoidBkm()" 
                        style="padding: 0.5rem 1rem; font-size: 0.825rem;">
                    Batal
                </button>
                <button type="submit" 
                        class="btn-pill-rose" 
                        style="padding: 0.5rem 1.25rem; font-size: 0.825rem;">
                    Ya, Batalkan Voucher
                </button>
            </div>
        </form>
    </div>
</div>
