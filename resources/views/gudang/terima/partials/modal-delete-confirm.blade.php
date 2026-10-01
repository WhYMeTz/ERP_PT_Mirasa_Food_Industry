{{-- MODAL KONFIRMASI HAPUS / BATALKAN PENERIMAAN BARANG --}}
<div id="modal-delete-terima" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.6); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:480px; overflow:hidden; border: 1px solid #fecaca;">
        <div style="background:#fee2e2; border-bottom:1px solid #fca5a5; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <div style="width:32px; height:32px; border-radius:50%; background:#ef4444; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#991b1b;">Hapus Dokumen Penerimaan Barang</h3>
            </div>
            <button type="button" onclick="closeDeleteTerimaModal()" style="background:none; border:none; font-size:1.4rem; color:#64748b; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div style="padding:1.25rem;">
            <p style="font-size:0.875rem; color:#334155; margin-top:0;">
                Apakah Anda yakin ingin MENGHAPUS dokumen penerimaan ini:
            </p>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:0.75rem 1rem; margin-bottom:1rem;">
                <div style="font-size:0.85rem; color:#64748b;">No. Penerimaan (GRN):</div>
                <div id="delete-terima-no" style="font-size:1.1rem; font-weight:800; color:#0f172a; font-family:monospace;">-</div>
                <div id="delete-terima-supplier" style="font-size:0.85rem; color:#475569; margin-top:0.25rem;">-</div>
            </div>
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:0.65rem 0.85rem; margin-bottom:1.25rem; font-size:0.8rem; color:#92400e;">
                ⚠️ <strong>Perhatian:</strong> Penghapusan ini akan otomatis menarik kembali (mengurangi) saldo fisik stok di gudang dan mengembalikan kuantitas pesanan yang belum datang pada PO terkait.
            </div>
            <form id="form-delete-terima" method="POST" action="">
                @csrf
                @method('DELETE')
                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                    <button type="button" onclick="closeDeleteTerimaModal()" class="btn btn-secondary" style="font-size:0.85rem;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="background:#dc2626; border-color:#dc2626; color:#fff; font-size:0.85rem; font-weight:700;">
                        Ya, Hapus Dokumen Ini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
