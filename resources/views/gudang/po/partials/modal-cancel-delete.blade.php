{{-- MODAL KONFIRMASI BATALKAN PURCHASE ORDER --}}
<div id="modalCancelPo" class="modal-backdrop" onclick="if(event.target === this) closeCancelPoModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div class="mac-modal-dialog" style="max-width: 460px; width: 100%; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: #fffbeb; padding: 1.15rem 1.25rem; border-bottom: 1px solid #fef3c7; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #92400e;">Konfirmasi Batalkan PO</h4>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.775rem; color: #78350f;">Dokumen PO: <strong id="cancelModalPoNo">{{ $po->po_no }}</strong></p>
            </div>
        </div>
        <div style="padding: 1.25rem;">
            <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">
                Apakah Anda yakin ingin membatalkan Purchase Order ini? Dokumen yang dibatalkan tidak dapat diproses untuk penerimaan barang.
            </p>
            <form id="formCancelPo" action="{{ route('gudang.po.cancel', $po->po_id) }}" method="POST" style="margin-top: 1.25rem;">
                @csrf
                <div style="display: flex; justify-content: flex-end; gap: 0.65rem;">
                    <button type="button" class="btn-pill-outline" onclick="closeCancelPoModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-po-danger">
                        Ya, Batalkan PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS PERMANEN PURCHASE ORDER --}}
<div id="modalDeletePo" class="modal-backdrop" onclick="if(event.target === this) closeDeletePoModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 10000; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div class="mac-modal-dialog" style="max-width: 480px; width: 100%; background: #ffffff; border-radius: 12px; border: 1px solid #fee2e2; box-shadow: 0 20px 25px -5px rgba(220,38,38,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: #fef2f2; padding: 1.15rem 1.25rem; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div>
                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #991b1b;">Hapus Dokumen PO Permanen</h4>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.775rem; color: #b91c1c;">Tindakan Kritis &bull; Audit Trail</p>
            </div>
        </div>
        <div style="padding: 1.25rem;">
            <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">
                <strong style="color: #991b1b;">PERINGATAN:</strong> Dokumen Purchase Order <strong id="deleteModalPoNo">{{ $po->po_no }}</strong> beserta seluruh relasi data rincian akan dihapus dari sistem. Tindakan ini <strong>tidak dapat dibatalkan</strong>.
            </p>
            <form id="formDeletePo" action="{{ route('gudang.po.destroy', $po->po_id) }}" method="POST" style="margin-top: 1.25rem;">
                @csrf
                @method('DELETE')
                <div style="display: flex; justify-content: flex-end; gap: 0.65rem;">
                    <button type="button" class="btn-pill-outline" onclick="closeDeletePoModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-po-danger" style="background: #dc2626; border-color: #dc2626; color: #ffffff;">
                        Hapus Dokumen PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
