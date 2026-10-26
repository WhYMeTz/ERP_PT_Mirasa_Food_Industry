{{-- MODAL PENOLAKAN / PEMBATALAN PEMAKAIAN BAHAN --}}
<div id="modalRejectPemakaian" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:520px; overflow:hidden; border: 1.5px solid #fecaca;">
        
        <div style="background:#fef2f2; border-bottom:1px solid #fecdd3; padding:1.15rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:38px; height:38px; border-radius:50%; background:#dc2626; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#991b1b;">Tolak Pengeluaran Bahan</h3>
                    <div style="font-size:0.75rem; color:#b91c1c; margin-top:2px;">Dokumen No: <span id="modalRejectNo" style="font-family:monospace; font-weight:700;">-</span></div>
                </div>
            </div>
            <button type="button" onclick="closeModalReject()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;">&times;</button>
        </div>

        <form action="" method="POST">
            @csrf
            <div style="padding:1.25rem;">
                <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:1rem; line-height:1.5;">
                    Dokumen ini akan dibatalkan dan tidak akan memotong stok gudang. Silakan masukkan alasan penolakan untuk petugas gudang:
                </p>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">
                        Alasan Penolakan / Catatan Revisi <span style="color:#dc2626;">*</span>:
                    </label>
                    <textarea name="alasan_reject" required rows="3" class="form-control" placeholder="Contoh: Salah timbangan singkong basah / batch salah pilih..." style="width:100%; font-size:0.85rem; border:1px solid #cbd5e1; border-radius:6px; padding:0.5rem;"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.6rem;">
                    <button type="button" onclick="closeModalReject()" class="btn btn-secondary" style="padding:0.55rem 1rem; font-size:0.85rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger" style="background:#dc2626; border-color:#dc2626; color:#fff; font-size:0.85rem; font-weight:700; padding:0.55rem 1.25rem;">
                        Tolak Dokumen
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
