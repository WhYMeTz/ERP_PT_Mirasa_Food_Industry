{{-- Modal Konfirmasi VOID BKK Operasional (Standar AGENTS.md) --}}
<div id="modalVoidKasKeluar"
     class="modal-backdrop-custom"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalVoidKasKeluarTitle"
     style="display: none;">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="#dc2626" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 id="modalVoidKasKeluarTitle" class="modal-title-custom" style="color: #991b1b;">
                    Batalkan Pengeluaran Kas (VOID)
                </h3>
            </div>
            <button type="button"
                    class="modal-close-btn"
                    onclick="closeModalVoidKasKeluar()"
                    aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formVoidKasKeluar" method="POST" action="">
            @csrf
            <input type="hidden" name="redirect_to" value="index">

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: #334155; margin: 0 0 0.85rem 0; line-height: 1.5;">
                    Apakah Anda yakin ingin membatalkan voucher
                    <strong id="voidBkkNoText" style="color: #0369a1; font-family: monospace;">-</strong>
                    sebesar <strong id="voidBkkNominalText" style="color: #b91c1c;">-</strong>?
                </p>

                <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 8px; padding: 0.85rem; font-size: 0.775rem; color: #9a3412; line-height: 1.45; margin-bottom: 1rem;">
                    <strong>Dampak Pembatalan:</strong>
                    <ul style="margin: 4px 0 0 0; padding-left: 1.2rem;">
                        <li>Saldo akun <strong id="voidBkkKasText">Kas/Bank</strong> akan <strong>dikembalikan bertambah</strong> senilai transaksi ini.</li>
                        <li>Mutasi arus kas keluar dan entri Jurnal Umum terkait akan dibatalkan otomatis.</li>
                    </ul>
                </div>

                <div>
                    <label for="alasanVoidKasKeluar" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Pembatalan <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="alasanVoidKasKeluar"
                              name="alasan_void"
                              rows="3"
                              minlength="5"
                              maxlength="255"
                              required
                              class="form-control"
                              placeholder="Tuliskan alasan pembatalan transaksi (minimal 5 karakter)..."
                              style="width: 100%; font-size: 0.8125rem; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;"></textarea>
                </div>
            </div>

            <div class="modal-footer-custom" style="display: flex; justify-content: flex-end; gap: 0.5rem; background: #f8fafc;">
                <button type="button"
                        class="btn btn-secondary"
                        onclick="closeModalVoidKasKeluar()"
                        style="font-size: 0.825rem; font-weight: 700;">
                    Kembali
                </button>
                <button type="submit"
                        class="btn btn-danger"
                        style="background: #dc2626; border-color: #dc2626; color: #ffffff; font-size: 0.825rem; font-weight: 800; padding: 0.45rem 1rem;">
                    Ya, Batalkan Voucher (VOID)
                </button>
            </div>
        </form>
    </div>
</div>
