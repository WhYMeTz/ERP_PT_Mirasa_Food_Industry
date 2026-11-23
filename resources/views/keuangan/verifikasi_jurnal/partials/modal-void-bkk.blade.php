{{-- Modal Batalkan / Void Bukti Kas Keluar --}}
<div id="modalVoidBkk" class="vj-modal-backdrop" style="display: none;" aria-hidden="true">
    <div class="vj-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalVoidTitle">
        <div class="vj-modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <div class="vj-modal-header-info">
                <span class="vj-kicker" style="color: #b91c1c;">TINDAKAN KRITIS</span>
                <h3 id="modalVoidTitle" class="vj-modal-title" style="color: #991b1b;">Batalkan / Void Bukti Kas Keluar</h3>
            </div>
            <button type="button" class="vj-modal-close" onclick="closeVoidModal()" aria-label="Tutup modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="formVoidBkk" method="POST" action="">
            @csrf
            <input type="hidden" name="redirect_to" value="verifikasi">
            <div class="vj-modal-body">
                <div class="vj-void-warning-box">
                    <svg width="20" height="20" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <strong style="color: #991b1b; display: block; margin-bottom: 2px;">Peringatan Pembatalan Transaksi</strong>
                        <span style="font-size: 0.8125rem; color: #7f1d1d; line-height: 1.45;">
                            Anda akan membatalkan BKK <strong id="voidBkkNo">-</strong>. Saldo kas/bank akan dikembalikan dan mutasi pembalik akan dicatat ke buku kasir.
                        </span>
                    </div>
                </div>

                <div class="vj-form-field" style="margin-top: 1rem;">
                    <label for="alasanVoidInput" class="vj-form-label" style="font-weight: 700; color: #0f172a;">
                        Alasan Pembatalan / Void <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="alasanVoidInput"
                              name="alasan_void"
                              rows="3"
                              class="vj-filter-input"
                              placeholder="Tuliskan alasan pembatalan BKK operasional ini..."
                              required
                              minlength="5"
                              style="width: 100%; resize: vertical;"></textarea>
                </div>
            </div>

            <div class="vj-modal-footer">
                <button type="button" class="vj-btn vj-btn-secondary" onclick="closeVoidModal()">
                    <span>Batal</span>
                </button>
                <button type="submit" class="vj-btn vj-btn-danger">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Konfirmasi Batalkan BKK</span>
                </button>
            </div>
        </form>
    </div>
</div>
