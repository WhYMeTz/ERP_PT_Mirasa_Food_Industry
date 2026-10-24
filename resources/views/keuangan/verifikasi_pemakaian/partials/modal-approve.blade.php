{{-- MODAL PERSETUJUAN & POSTING JURNAL PEMAKAIAN BAHAN --}}
<div id="modalApprovePemakaian" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:540px; overflow:hidden; border: 1.5px solid #a7f3d0;">
        
        <div style="background:#f0fdf4; border-bottom:1px solid #bbf7d0; padding:1.15rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:38px; height:38px; border-radius:50%; background:#059669; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#065f46;">Setujui &amp; Terbitkan Jurnal HPP</h3>
                    <div style="font-size:0.75rem; color:#047857; margin-top:2px;">Dokumen No: <span id="modalApproveNo" style="font-family:monospace; font-weight:700;">-</span></div>
                </div>
            </div>
            <button type="button" onclick="closeModalApprove()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;">&times;</button>
        </div>

        <form action="" method="POST">
            @csrf
            <div style="padding:1.25rem;">
                <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:1rem; line-height:1.5;">
                    Apakah Anda yakin ingin memverifikasi dan menyetujui dokumen pengeluaran bahan ini?
                </p>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1rem; margin-bottom:1.25rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; padding-bottom:0.5rem; border-bottom:1px dashed #cbd5e1;">
                        <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Total Biaya (HPP) Pemakaian:</span>
                        <strong id="modalApproveRupiah" style="font-size:1.15rem; color:#059669; font-family:monospace;">Rp 0</strong>
                    </div>

                    <div style="font-size:0.775rem; color:#475569; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase;">
                        Jurnal Akuntansi yang Diterbitkan:
                    </div>
                    <div style="background:#fff; border:1px solid #cbd5e1; border-radius:6px; padding:0.6rem; font-size:0.8rem;">
                        <div style="display:flex; justify-content:space-between; color:#059669; font-weight:600; margin-bottom:0.25rem;">
                            <span>(Debet) 1151 - Persediaan WIP &amp; FG</span>
                            <span>[HPP Produksi]</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; color:#dc2626; font-weight:600;">
                            <span style="padding-left:1rem;">(Kredit) 1150 - Persediaan Bahan Baku &amp; Penolong</span>
                            <span>[Potong Stok Gudang]</span>
                        </div>
                    </div>
                </div>

                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:0.75rem; margin-bottom:1.25rem; font-size:0.8rem; color:#1e40af; line-height:1.45;">
                    ℹ️ <strong>Dampak:</strong> Stok fisik batch barang di gudang akan resmi dipotong dan status terkunci menjadi <strong>POSTED</strong>.
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.6rem;">
                    <button type="button" onclick="closeModalApprove()" class="btn btn-secondary" style="padding:0.55rem 1rem; font-size:0.85rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#fff; font-size:0.85rem; font-weight:700; padding:0.55rem 1.25rem;">
                        Ya, Setujui &amp; Terbitkan Jurnal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
