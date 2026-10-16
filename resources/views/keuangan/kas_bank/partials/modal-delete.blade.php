{{-- Modal Konfirmasi Hapus / Nonaktifkan Rekening (Danger Modal AGENTS.md) --}}
<div id="modalDeleteKasBank" class="kb-modal-backdrop">
    <div class="kb-modal">
        <div class="kb-modal-header danger-header">
            <h3 class="kb-modal-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Konfirmasi Penghapusan Rekening</span>
            </h3>
            <button type="button" class="kb-modal-close" onclick="closeModal('modalDeleteKasBank')">&times;</button>
        </div>
        <form id="formDeleteKasBank" action="" method="POST">
            @csrf
            @method('DELETE')
            <div class="kb-modal-body">
                <p style="margin: 0 0 0.75rem 0; font-size: 0.875rem; color: var(--kb-dark); line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus / menonaktifkan akun <strong id="delete_nama_akun" style="color: var(--kb-danger);">-</strong>?
                </p>
                <div style="background-color: var(--kb-danger-light); border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem; font-size: 0.78rem; color: #991b1b; line-height: 1.45;">
                    <strong>Perhatian Kebijakan Audit Keuangan:</strong>
                    <ul style="margin: 0.35rem 0 0 1.15rem; padding: 0;">
                        <li>Jika akun ini belum memiliki transaksi mutasi, akun akan dihapus secara permanen dari sistem.</li>
                        <li>Jika akun telah memiliki riwayat transaksi/pelunasan hutang, sistem akan <strong>mengarsipkan/menonaktifkan (soft-deactivate)</strong> akun agar seluruh histori laporan keuangan tetap terjaga utuh.</li>
                    </ul>
                </div>
            </div>
            <div class="kb-modal-footer">
                <button type="button" class="kb-btn kb-btn-secondary" onclick="closeModal('modalDeleteKasBank')">Batal</button>
                <button type="submit" class="kb-btn" style="background-color: var(--kb-danger); color: #ffffff; border-color: transparent;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Ya, Hapus / Nonaktifkan</span>
                </button>
            </div>
        </form>
    </div>
</div>
