{{-- MODAL KONFIRMASI AKTIFKAN / NONAKTIFKAN PAJAK --}}
<div id="modalToggleStatusPajak" class="modal-overlay pajak-modal-overlay" style="display: none;">
    <div class="modal-dialog pajak-modal-dialog" style="max-width: 480px;">
        <div class="modal-header pajak-modal-header" id="toggleStatusModalHeader" style="background: #fffbeb; border-bottom: 1.5px solid #fde68a;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div id="toggleStatusModalIcon" style="width: 36px; height: 36px; border-radius: 8px; background: #d97706; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 id="toggleStatusModalTitle" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #92400e;">Konfirmasi Status Pajak</h3>
                    <p id="toggleStatusModalSub" style="margin: 2px 0 0; font-size: 0.775rem; color: #b45309;">Pengaturan ketersediaan tarif pada modul operasional.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn btn-close-modal" onclick="closeModal('modalToggleStatusPajak')" title="Tutup">&times;</button>
        </div>

        <form action="" method="POST" id="formToggleStatusPajak">
            @csrf
            @method('PATCH')
            <div class="modal-body pajak-modal-body" style="padding: 1.25rem;">
                <p id="toggleStatusConfirmMessage" style="font-size: 0.875rem; color: #334155; margin: 0 0 0.85rem; line-height: 1.5;">
                    Apakah Anda yakin ingin mengubah status tarif pajak ini?
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 0.95rem; font-size: 0.825rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="color: #64748b;">Kode Pajak:</span>
                        <strong id="togglePajakCd" style="font-family: monospace; color: #0f172a;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="color: #64748b;">Nama Pajak:</span>
                        <strong id="togglePajakNm" style="color: #0f172a;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Tarif Pajak:</span>
                        <strong id="togglePajakTarif" style="color: #059669; font-family: monospace;">-</strong>
                    </div>
                </div>

                <div id="toggleWarningNotice" style="margin-top: 0.85rem; font-size: 0.75rem; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    💡 Tarif yang dinonaktifkan tidak akan muncul pada dropdown pilihan baru (PO, Faktur, Kas/Bank), namun transaksi lama tetap tersimpan rapi.
                </div>
            </div>

            <div class="modal-footer pajak-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalToggleStatusPajak')">Batal</button>
                <button type="submit" id="btnSubmitToggleStatus" class="btn btn-warning" style="background: #d97706; border-color: #d97706; color: #fff; font-weight: 700;">
                    Ya, Ubah Status
                </button>
            </div>
        </form>
    </div>
</div>
