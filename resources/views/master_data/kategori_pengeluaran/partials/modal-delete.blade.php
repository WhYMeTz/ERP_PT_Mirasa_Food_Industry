{{-- Modal Konfirmasi Hapus Kategori Kas Keluar (Danger Theme) --}}
<div id="modalDeleteKategori" class="modal-overlay">
    <div class="modal-dialog modal-dialog-danger">
        <div class="modal-header modal-header-danger">
            <div class="modal-title-wrap">
                <div class="modal-icon-badge badge-danger">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="modal-title modal-title-danger">Hapus Kategori Kas Keluar</h3>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeModal('modalDeleteKategori')" title="Tutup Modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="" method="POST" id="formDeleteKategori">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p style="margin: 0 0 0.875rem 0; font-size: 0.875rem; color: #1e293b; line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus data master kategori berikut?
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Kode Kategori</div>
                    <div id="delete_kategori_kode" class="font-mono" style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">-</div>

                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Nama Kategori</div>
                    <div id="delete_kategori_nama" style="font-size: 0.875rem; font-weight: 600; color: #0f172a;">-</div>
                </div>

                <div style="font-size: 0.75rem; color: #b91c1c; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 0.5rem 0.75rem; line-height: 1.4;">
                    Peringatan: Kategori ini akan dinonaktifkan dari sistem. Riwayat transaksi bukti kas keluar yang sudah pernah menggunakan kategori ini tetap tersimpan untuk audit trail pembukuan.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalDeleteKategori')">
                    Batal
                </button>
                <button type="submit" class="btn-modal-danger">
                    Ya, Hapus Data
                </button>
            </div>
        </form>
    </div>
</div>
