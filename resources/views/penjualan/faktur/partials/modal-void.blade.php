{{-- MODAL VOID FAKTUR PENJUALAN (STANDAR AGENTS.md ERP MIRASA) --}}
<div id="modalVoidFaktur" class="modal-danger-overlay">
    <div class="modal-danger-box">
        <div class="modal-danger-header">
            <div class="modal-danger-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="modal-danger-title">Konfirmasi Pembatalan Dokumen Penjualan</h3>
                <div style="font-size: 0.775rem; color: #b91c1c; font-weight: 600;">
                    Aksi ini akan membatalkan tagihan &amp; pengakuan piutang komersial
                </div>
            </div>
        </div>

        <form id="formVoidFaktur" method="POST" action="">
            @csrf
            <div class="modal-danger-body">
                <p style="margin: 0 0 0.75rem 0;">
                    Apakah Anda yakin ingin membatalkan Dokumen Penjualan <strong id="voidFakturNoText" style="color: #0f172a; font-family: monospace;">-</strong>?
                </p>
                <div style="background: #fef2f2; border-left: 3px solid #ef4444; padding: 0.65rem 0.85rem; font-size: 0.785rem; color: #991b1b; border-radius: 4px; margin-bottom: 1rem;">
                    <strong>Dampak Operasional:</strong>
                    <ul style="margin: 0.35rem 0 0 1.15rem; padding: 0;">
                        <li>Status dokumen penjualan menjadi <strong>BATAL</strong> dan saldo piutang dinolkan.</li>
                        <li>Dokumen Surat Jalan terkait akan otomatis dikembalikan ke status <strong>TERKIRIM</strong> agar dapat diterbitkan tagihan revisi jika diperlukan.</li>
                    </ul>
                </div>

                <div class="fk-field-group">
                    <label class="fk-label" for="voidReason">
                        Alasan Pembatalan Dokumen <span class="required">*</span>
                    </label>
                    <textarea id="voidReason" name="reason" class="fk-textarea" required 
                              placeholder="Contoh: Terjadi salah harga satuan pada item keripik ubi / revisi diskon customer"></textarea>
                </div>
            </div>

            <div class="modal-danger-footer">
                <button type="button" class="btn btn-secondary" onclick="closeVoidModal()" style="font-size: 0.825rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #b91c1c; color: #ffffff; font-weight: 700; font-size: 0.825rem;">
                    Ya, Batalkan Faktur Ini
                </button>
            </div>
        </form>
    </div>
</div>
