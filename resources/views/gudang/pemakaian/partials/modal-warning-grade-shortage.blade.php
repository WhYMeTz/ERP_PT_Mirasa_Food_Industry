{{-- MODAL PERINGATAN STOK GRADE TIDAK CUKUP & TAWARAN BERALIH KE CAMPUR --}}
<div id="modalWarningGradeShortage" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:580px; overflow:hidden; border: 1.5px solid #fde68a; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Modal Amber Warning --}}
        <div style="background:#fffbeb; border-bottom:1px solid #fde68a; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:38px; height:38px; border-radius:50%; background:#d97706; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(217,119,6,0.3);">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 id="gradeShortageModalTitle" style="margin:0; font-size:1.05rem; font-weight:700; color:#92400e;">
                        Stok Fisik Grade Terpilih Tidak Mencukupi!
                    </h3>
                    <div style="font-size:0.75rem; color:#b45309; margin-top:2px;">Kalkulasi Alokasi Resep Produksi (BOM)</div>
                </div>
            </div>
            <button type="button" onclick="closeWarningGradeShortageModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.25rem;">
            <div id="gradeShortageModalMessage" style="font-size:0.875rem; color:#334155; line-height:1.5; margin-bottom:1rem;">
                {{-- Diisi secara dinamis oleh JavaScript --}}
            </div>

            {{-- Kotak Tawaran Solusi Cerdas --}}
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:0.85rem 1rem; margin-bottom:1.25rem; font-size:0.8rem; color:#166534; line-height:1.5;">
                <div style="display:flex; gap:0.6rem; align-items:flex-start;">
                    <span style="font-size:1.2rem; line-height:1;">💡</span>
                    <div>
                        <strong style="color:#14532d; display:block; margin-bottom:2px;">Opsi Solusi Rekomendasi:</strong>
                        Klik tombol <strong>"Beralih ke Campur (Semua Grade)"</strong> agar sistem otomatis memadukan sisa kuantitas dari batch grade lain yang tersedia (FIFO) sehingga seluruh target produksi Anda tetap terpenuhi 100%!
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:space-between; gap:0.6rem; align-items:center; flex-wrap:wrap;">
                <button type="button" onclick="switchBomGradeToCampurAndReload()" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#ffffff; font-size:0.85rem; font-weight:700; padding:0.6rem 1.15rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.4rem; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>🔄 Beralih ke Campur &amp; Penuhi Seluruhnya</span>
                </button>

                <button type="button" onclick="closeWarningGradeShortageModal()" class="btn btn-secondary" style="font-size:0.825rem; padding:0.6rem 0.85rem; border-radius:6px; font-weight:600; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">
                    Tetap Gunakan Stok yang Ada
                </button>
            </div>
        </div>
    </div>
</div>
