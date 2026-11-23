{{-- Modal Konfirmasi VOID Pembayaran Piutang / BKM AR (Sesuai Standar AGENTS.md) --}}
<div id="modalVoidBayarPiutang"
     class="modal-backdrop-custom"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalVoidBayarPiutangTitle"
     style="display: none;">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="#dc2626" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 id="modalVoidBayarPiutangTitle" class="modal-title-custom" style="color: #991b1b;">
                    Batalkan Pembayaran Customer (VOID BKM)
                </h3>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModalVoid()" aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formVoidBayarPiutang" method="POST" action="">
            @csrf

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: #334155; margin: 0 0 1rem 0; line-height: 1.5;">
                    Apakah Anda yakin ingin membatalkan transaksi Bukti Kas Masuk <strong id="voidBkmNoText" style="color: #0369a1; font-family: monospace;">-</strong> sebesar <strong id="voidNominalText" style="color: #b91c1c;">-</strong>?
                </p>

                <div style="margin-bottom: 1rem;">
                    <label for="inputAlasanVoid" style="font-size: 0.775rem; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">
                        Alasan Pembatalan (Opsional):
                    </label>
                    <input type="text"
                           id="inputAlasanVoid"
                           name="alasan"
                           class="form-control"
                           placeholder="Contoh: Salah alokasi nominal / Bilyet giro ditolak bank"
                           style="width: 100%; font-size: 0.8125rem; padding: 0.45rem 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 8px; padding: 0.85rem; font-size: 0.775rem; color: #9a3412; line-height: 1.45;">
                    <strong>Dampak Pembatalan Operasional:</strong>
                    <ul style="margin: 4px 0 0 0; padding-left: 1.2rem;">
                        <li>Sisa piutang pada faktur penjualan terkait akan <strong>dikembalikan utuh</strong>.</li>
                        <li>Saldo kas/bank akan <strong>dikurangi kembali</strong> senilai transaksi ini.</li>
                        <li>Entri jurnal umum penerimaan piutang akan <strong>dibatalkan secara otomatis</strong>.</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer-custom" style="display: flex; justify-content: flex-end; gap: 0.5rem; background: #f8fafc; padding: 0.85rem 1.25rem; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" onclick="closeModalVoid()" style="font-size: 0.825rem; font-weight: 700;">
                    Kembali
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; color: #ffffff; font-size: 0.825rem; font-weight: 800; padding: 0.45rem 1rem; border-radius: 6px;">
                    Ya, Batalkan Transaksi (VOID)
                </button>
            </div>
        </form>
    </div>
</div>
