{{-- MODAL KONFIRMASI HAPUS / BATALKAN TIKET QC --}}
<div id="modalDeleteQc" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; width: 100%; max-width: 440px; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Bahaya --}}
        <div style="background: #fef2f2; border-bottom: 1px solid #fee2e2; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 0.85rem;">
            <div style="width: 42px; height: 42px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; border: 2px solid #fecaca;">
                ⚠️
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #991b1b; line-height: 1.2;">
                    Batalkan Tiket QC?
                </h3>
                <span style="font-size: 0.775rem; color: #b91c1c; font-weight: 600;">
                    Tindakan ini menghapus data inspeksi sampling
                </span>
            </div>
        </div>

        {{-- Body Pesan Peringatan --}}
        <div style="padding: 1.25rem 1.5rem;">
            <p style="margin: 0 0 0.85rem 0; font-size: 0.875rem; color: #334155; line-height: 1.5;">
                Apakah Anda yakin ingin menghapus tiket inspeksi QC <strong id="deleteQcNo" style="color: #0284c7;">#QC-0000</strong>?
            </p>
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.75rem 0.9rem; font-size: 0.8rem; color: #92400e; line-height: 1.4;">
                <strong style="display: block; margin-bottom: 0.2rem;">Perhatian Operasional:</strong>
                Data parameter uji mutu, timbangan sampling, dan refraksi pada tiket ini akan dibatalkan. Jika tiket sudah di-acc gudang, mutasi persediaan dapat terpengaruh.
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
            <button type="button" onclick="closeDeleteQcModal()" style="padding: 0.55rem 1rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.825rem; cursor: pointer;">
                Batal
            </button>
            <form id="formDeleteQc" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <input type="hidden" name="view" value="{{ request('view') }}">
                <button type="submit" style="padding: 0.55rem 1.15rem; border-radius: 8px; border: none; background: #dc2626; color: #ffffff; font-weight: 800; font-size: 0.825rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);">
                    <span>🗑️</span>
                    <span>Ya, Batalkan Tiket</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
