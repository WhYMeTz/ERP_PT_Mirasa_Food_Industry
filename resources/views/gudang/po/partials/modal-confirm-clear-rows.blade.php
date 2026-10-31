{{-- MODAL KONFIRMASI HAPUS SEMUA BARIS (BAHAYA / KRITIS) --}}
<div id="modalConfirmClearRows" class="modal-backdrop" onclick="if(event.target === this) closeConfirmClearRowsModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10002; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; max-width: 460px; width: 100%; box-shadow: 0 25px 60px rgba(0,0,0,0.3); overflow: hidden; border: 1px solid #fecaca; animation: modalZoomIn 0.15s ease-out;">
        
        {{-- Header Modal Bahaya --}}
        <div style="background: #fef2f2; border-bottom: 1px solid #fee2e2; padding: 1.15rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h3 style="font-size: 1rem; font-weight: 800; color: #991b1b; margin: 0;">
                    Hapus Semua Baris Pesanan?
                </h3>
                <small style="color: #b91c1c; font-size: 0.75rem;">Konfirmasi pengosongan seluruh item tabel PO</small>
            </div>
        </div>

        {{-- Body Modal --}}
        <div style="padding: 1.25rem; font-size: 0.85rem; color: #334155; line-height: 1.5;">
            <p style="margin: 0 0 0.75rem 0;">
                Apakah Anda yakin ingin <strong>menghapus seluruh baris komoditas</strong> yang sudah diinputkan pada tabel rincian Purchase Order?
            </p>
            <div style="background: #fff5f5; border: 1px dashed #fca5a5; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.775rem; color: #b91c1c;">
                ⚠️ <strong>Perhatian:</strong> Seluruh item, kuantitas, harga, dan diskon yang telah diketik akan direset kembali menjadi 1 baris kosong baru.
            </div>
        </div>

        {{-- Footer Modal --}}
        <div style="padding: 0.85rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeConfirmClearRowsModal()" style="font-weight: 600; padding: 0.4rem 0.85rem;">
                Batal
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="executeClearAllRows()" style="background: #dc2626; border-color: #dc2626; font-weight: 700; padding: 0.4rem 1rem; box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);">
                Ya, Hapus Semua Baris
            </button>
        </div>

    </div>
</div>

