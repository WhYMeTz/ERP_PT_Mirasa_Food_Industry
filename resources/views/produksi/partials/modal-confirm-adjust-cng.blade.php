{{-- MODAL KONFIRMASI PENYESUAIAN GAS CNG HARIAN (SESUAI AGENTS.md RULE 2: DILARANG CONFIRM BROWSER) --}}
<div id="modalConfirmAdjustCng" class="modal-backdrop-custom" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-dialog-custom" style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1.5px solid #cbd5e1; animation: modalSlideDown 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="background: #fffbeb; border-bottom: 1.5px solid #fde68a; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 0.85rem;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a;">
                    Konfirmasi Penyesuaian Gas CNG Harian
                </h3>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.775rem; color: #b45309;">
                    Perubahan kuantitas &amp; biaya gas akan disimpan ke HPP dan buku log audit trail.
                </p>
            </div>
        </div>

        {{-- BODY MODAL --}}
        <div style="padding: 1.5rem;">
            <p style="font-size: 0.825rem; color: #475569; margin: 0 0 1rem 0; line-height: 1.5;">
                Anda akan menerapkan penyesuaian pemakaian gas fisik pada periode <strong>{{ $monthsList[$month] }} {{ $year }}</strong>. Seluruh lembar produksi harian yang diubah akan dikalkulasi ulang menggunakan Kurs JISDOR tanggal masing-masing.
            </p>

            {{-- RINGKASAN DAMPAK AKUNTANSI --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.65rem;">
                    Ringkasan Dampak Finansial:
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <span style="font-size: 0.725rem; color: #64748b; display: block;">Hari Kerja Diubah:</span>
                        <strong id="modalDaysCount" style="font-size: 1.15rem; color: #0f172a; font-family: ui-monospace, monospace;">0 Hari</strong>
                    </div>
                    <div>
                        <span style="font-size: 0.725rem; color: #64748b; display: block;">Net Selisih MMBTU:</span>
                        <strong id="modalDeltaMmbtu" style="font-size: 1.15rem; color: #d97706; font-family: ui-monospace, monospace;">0.00 MMBTU</strong>
                    </div>
                    <div style="grid-column: span 2; border-top: 1px dashed #e2e8f0; padding-top: 0.5rem; margin-top: 0.25rem;">
                        <span style="font-size: 0.725rem; color: #64748b; display: block;">Net Selisih Biaya Rupiah:</span>
                        <strong id="modalDeltaBiaya" style="font-size: 1.25rem; color: #059669; font-family: ui-monospace, monospace;">Rp 0</strong>
                    </div>
                </div>
            </div>

            {{-- INPUT ALASAN / BERITA ACARA --}}
            <div>
                <label for="modal_alasan_txt" style="font-size: 0.775rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                    Alasan / Dasar Dokumen Penyesuaian:
                </label>
                <input type="text" 
                       id="modal_alasan_txt" 
                       class="form-control" 
                       style="font-size: 0.825rem; width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 0.75rem;" 
                       value="Rekonsiliasi &amp; balancing pemakaian gas CNG periode {{ $monthsList[$month] }} {{ $year }}" 
                       oninput="document.getElementById('input_hidden_alasan').value = this.value">
                <small style="font-size: 0.7rem; color: #64748b; margin-top: 0.25rem; display: block;">
                    Catatan ini akan tersimpan permanen pada dokumen Berita Acara &amp; Riwayat Audit HPP.
                </small>
            </div>
        </div>

        {{-- FOOTER MODAL --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center;">
            <button type="button" 
                    onclick="closeModalConfirmAdjustCng()" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-size: 0.825rem; font-weight: 600; padding: 0.55rem 1.15rem; border-radius: 6px; cursor: pointer;">
                Batal
            </button>
            <button type="button" 
                    id="btnModalSaveConfirm" 
                    onclick="submitFormDailyCng()" 
                    class="btn btn-primary" 
                    style="background: #059669; border: 1.5px solid #059669; color: #ffffff; font-size: 0.825rem; font-weight: 700; padding: 0.55rem 1.35rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 2px rgba(5,150,105,0.2);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Ya, Simpan &amp; Terapkan ke HPP</span>
            </button>
        </div>

    </div>
</div>
