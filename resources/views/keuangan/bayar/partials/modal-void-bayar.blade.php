{{-- Modal Konfirmasi VOID Pembayaran BKK (Sesuai Standar AGENTS.md) --}}
<div id="modalVoidBayar" class="modal-backdrop-custom" style="display: none;">
    <div class="modal-dialog-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.35rem; color: #dc2626;">⚠️</span>
                <h3 class="modal-title-custom" style="color: #991b1b;">
                    Batalkan Pembayaran (VOID BKK)
                </h3>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModalVoid()">&times;</button>
        </div>

        <form id="formVoidBayar" method="POST" action="">
            @csrf

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: #334155; margin: 0 0 1rem 0; line-height: 1.5;">
                    Apakah Anda yakin ingin membatalkan transaksi Bukti Kas Keluar <strong id="voidBayarNoText" style="color: #0284c7; font-family: monospace;">-</strong> sebesar <strong id="voidNominalText" style="color: #dc2626;">-</strong>?
                </p>

                <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 8px; padding: 0.85rem; font-size: 0.775rem; color: #9a3412; line-height: 1.45;">
                    <strong>Dampak Pembatalan Operasional:</strong>
                    <ul style="margin: 4px 0 0 0; padding-left: 1.2rem;">
                        <li>Sisa hutang pada faktur tagihan terkait akan <strong>dikembalikan utuh</strong>.</li>
                        <li>Saldo kas/bank akan <strong>dikembalikan bertambah</strong> senilai transaksi ini.</li>
                        <li>Buku mutasi arus kas keluar akan dibatalkan secara otomatis.</li>
                    </ul>
                </div>
            </div>

            <div class="modal-footer-custom" style="display: flex; justify-content: flex-end; gap: 0.5rem; background: #f8fafc;">
                <button type="button" class="btn btn-secondary" onclick="closeModalVoid()" style="font-size: 0.825rem; font-weight: 700;">
                    Kembali
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; color: #ffffff; font-size: 0.825rem; font-weight: 800; padding: 0.45rem 1rem;">
                    Ya, Batalkan Transaksi (VOID)
                </button>
            </div>
        </form>
    </div>
</div>
