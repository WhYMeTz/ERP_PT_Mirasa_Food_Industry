{{-- Modal Konfirmasi Hapus Lembar Produksi Harian (Sesuai Standar AGENTS.md) --}}
<div id="modalDeleteProduksi" class="modal-overlay" onclick="if(event.target === this) closeDeleteProduksiModal()">
    <div class="modal-dialog-custom">
        {{-- Header Bahaya (Merah Lembut) --}}
        <div style="background: #fef2f2; border-bottom: 1.5px solid #fecaca; padding: 1.1rem 1.35rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; font-size: 1.1rem; flex-shrink: 0;">
                    ⚠️
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #991b1b; margin: 0;">
                        Konfirmasi Hapus Produksi Harian
                    </h3>
                    <p style="font-size: 0.75rem; color: #b91c1c; margin: 0.15rem 0 0 0;">
                        Aksi ini akan membatalkan kalkulasi HPP dan mutasi stok WIP terkait.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeDeleteProduksiModal()" style="background: none; border: none; font-size: 1.3rem; color: #991b1b; cursor: pointer; padding: 0.2rem 0.4rem; line-height: 1;">
                &times;
            </button>
        </div>

        {{-- Form & Isi Konfirmasi --}}
        <form id="formDeleteProduksi" method="POST" action="">
            @csrf
            @method('DELETE')

            <div style="padding: 1.25rem 1.35rem;">
                <p style="font-size: 0.85rem; color: #334155; line-height: 1.5; margin: 0 0 1rem 0;">
                    Apakah Anda yakin ingin menghapus lembar produksi harian ini?
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.8rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="color: #64748b;">No. Dokumen:</span>
                        <strong id="deleteProduksiNo" style="color: #0f172a; font-family: monospace;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="color: #64748b;">Tanggal Produksi:</span>
                        <strong id="deleteProduksiTgl" style="color: #0f172a;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="color: #64748b;">Shift:</span>
                        <strong id="deleteProduksiShift" style="color: #0f172a;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Batch WIP:</span>
                        <strong id="deleteProduksiBatch" style="color: #0f172a; font-family: monospace;">-</strong>
                    </div>
                </div>

                <div style="background: #fffbeb; border-left: 3px solid #f59e0b; padding: 0.65rem 0.85rem; border-radius: 4px; font-size: 0.75rem; color: #92400e;">
                    <strong>Dampak Operasional:</strong> Jika lembar ini berstatus POSTED, kuantitas stok barang jadi WIP yang sempat tercatat akan otomatis disesuaikan kembali di kartu persediaan.
                </div>
            </div>

            {{-- Footer Tombol Aksi --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.35rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" onclick="closeDeleteProduksiModal()" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="font-size: 0.825rem; padding: 0.45rem 1.15rem; background: #dc2626; border-color: #dc2626; color: #ffffff; font-weight: 700;">
                    Ya, Hapus Catatan Ini
                </button>
            </div>
        </form>
    </div>
</div>
