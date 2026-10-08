<div id="modalEditTarif" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        {{-- HEADER MODAL ENTERPRISE (Clean Slate & Emerald) --}}
        <div class="modal-tarif-header">
            <div class="modal-tarif-header-left">
                <div class="modal-tarif-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="modal-tarif-title">Konfigurasi Standar Tarif</h3>
                    <div class="modal-tarif-subtitle">Pembaruan parameter kalkulasi biaya &amp; HPP pabrik</div>
                </div>
            </div>
            <button type="button" class="modal-tarif-close" onclick="closeEditTarifModal()" title="Tutup">&times;</button>
        </div>

        <form id="formEditTarif" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="tarif_id" id="editTarifId">

            <div style="padding: 1.25rem 1.5rem;">
                
                {{-- DOSSIER SUMMARY STRIP --}}
                <div class="tarif-dossier-box">
                    <div class="tarif-dossier-meta">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span id="editKategoriBadge" class="badge-category badge-energi">ENERGI</span>
                            <span id="editKodeTarif" class="tarif-dossier-code">TARIF_CNG</span>
                        </div>
                        <span style="font-size: 0.7rem; color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                            Data Aktif Sistem
                        </span>
                    </div>

                    <div id="editNamaTarif" class="tarif-dossier-name">
                        Gas Alam (CNG)
                    </div>

                    <div class="tarif-dossier-sub">
                        <span>Basis Perhitungan: <strong id="editSatuanLabel" style="color: #334155;">Rp / MMBTU</strong></span>
                        <span>Tarif Berlaku: <strong id="editNilaiLamaText" class="tarif-dossier-current-val">Rp 226.800,00</strong></span>
                    </div>
                </div>

                {{-- INPUT ADDON GROUP BERSTANDAR TINGGI --}}
                <div style="margin-bottom: 1.25rem;">
                    <div class="tarif-input-label">
                        <span>Besaran Nilai Standar Baru <span style="color: #ef4444;">*</span></span>
                        <span style="font-size: 0.7rem; color: #64748b; font-weight: normal;">Format desimal presisi</span>
                    </div>

                    <div class="tarif-input-group">
                        <span class="tarif-addon-pill" id="editAddonPrefix">Rp</span>
                        <input type="number" 
                               step="0.0001" 
                               min="0" 
                               name="nilai_tarif" 
                               id="editNilaiTarif" 
                               class="tarif-input-field" 
                               placeholder="0" 
                               required
                               oninput="updateLiveTarifReadout()">
                        <span class="tarif-addon-pill suffix" id="editAddonSuffix">/ MMBTU</span>
                    </div>

                    {{-- LIVE FORMATTED READOUT --}}
                    <div class="tarif-live-readout" id="editLiveReadout">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pratinjau Nilai: <strong id="editReadoutText">Rp 226.800,00</strong></span>
                    </div>
                </div>

                {{-- ALASAN PERUBAHAN & AUDIT TRAIL --}}
                <div style="margin-bottom: 0.25rem;">
                    <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Penyesuaian (Catatan Audit Trail)
                    </label>
                    <textarea name="keterangan" 
                              id="editKeterangan" 
                              rows="2" 
                              class="tarif-textarea-field" 
                              placeholder="Contoh: Penyesuaian tagihan PGN per Oktober 2026 atau evaluasi HPP manajemen..."></textarea>
                    <div style="display: flex; align-items: center; gap: 0.3rem; margin-top: 0.35rem; font-size: 0.7rem; color: #64748b;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Perubahan otomatis dicatat ke log audit trail sistem dengan ID akun Anda.</span>
                    </div>
                </div>

            </div>

            {{-- FOOTER BUTTONS --}}
            <div class="modal-tarif-footer">
                <button type="button" onclick="closeEditTarifModal()" class="btn-tarif-cancel">
                    Batal
                </button>
                <button type="submit" class="btn-tarif-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Standar Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>
