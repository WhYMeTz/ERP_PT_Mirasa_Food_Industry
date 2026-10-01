{{-- MODAL KONFIRMASI NONAKTIFKAN/HAPUS SATUAN --}}
<div id="modalDeleteSatuan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca;">
            <h3 class="modal-title" style="color: #991b1b; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Konfirmasi Nonaktifkan Satuan</span>
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteSatuan')">&times;</button>
        </div>
        <form id="formDeleteSatuan" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body" style="padding: 1.25rem;">
                <p style="font-size: 0.875rem; color: #334155; margin-bottom: 0.75rem;">
                    Apakah Anda yakin ingin menonaktifkan data satuan berikut?
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 0.85rem;" id="deleteSatuanCd"></div>
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem; margin-top: 0.2rem;" id="deleteSatuanNm"></div>
                </div>
                <small style="color: #dc2626; font-size: 0.75rem; display: block; line-height: 1.4;">
                    ⚠️ Satuan yang dinonaktifkan tidak akan dapat dipilih lagi pada saat pembuatan Master Barang baru atau konversi satuan.
                </small>
            </div>
            <div class="modal-footer" style="padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalDeleteSatuan')">Batal</button>
                <button type="submit" class="btn btn-danger btn-sm" style="background: #dc2626; color: #fff; font-weight: 700;">
                    Ya, Nonaktifkan Satuan
                </button>
            </div>
        </form>
    </div>
</div>
