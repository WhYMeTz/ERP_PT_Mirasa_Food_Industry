<div id="modalQuickTarif" class="modal-backdrop-custom" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 12px; width: 95%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.05); overflow: hidden; animation: modalSlideUp 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- HEADER MODAL (Clean Executive Enterprise) --}}
        <div style="background: #ffffff; padding: 1.15rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #f0fdf4; color: #059669; border: 1px solid #bbf7d0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0f172a; line-height: 1.25;">Konfigurasi Cepat Standar Pengali &amp; Tarif</h3>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">Pembaruan master tarif langsung tersimpan dan mengkalkulasi form ini tanpa reload.</div>
                </div>
            </div>
            <button type="button" onclick="closeQuickTarifModal()" style="width: 32px; height: 32px; border-radius: 6px; border: 1px solid transparent; background: transparent; color: #64748b; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; cursor: pointer;" title="Tutup">&times;</button>
        </div>

        {{-- BODY MODAL --}}
        <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1;">
            
            {{-- ALERT INFO AUDIT --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.775rem; color: #475569; display: flex; align-items: center; gap: 0.6rem;">
                <svg width="18" height="18" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    Nilai pengali FOH otomatis dikalikan dengan <strong>Total KG WIP</strong> pada formulir produksi harian.
                </div>
            </div>

            <form id="formQuickTarif">
                {{-- SECTION 1: STANDAR PENGALI FOH (PER KG WIP) --}}
                <div style="margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.35rem;">
                        <span style="font-size: 0.775rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em;">
                            1. Standar Pengali FOH (Rp / Kg WIP)
                        </span>
                        <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Basis: Total Output KG WIP</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Pengawasan Mutu (QC)
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.65rem; display: flex; align-items: center;">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_QC]" id="quick_FOH_QC" style="flex: 1; border: none; padding: 0.45rem 0.65rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['qc'] ?? 49.97 }}">
                                <span style="background: #f8fafc; border-left: 1px solid #e2e8f0; color: #64748b; font-size: 0.7rem; padding: 0.45rem 0.6rem; display: flex; align-items: center;">/ Kg</span>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Listrik, Air &amp; Telp
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.65rem; display: flex; align-items: center;">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_LISTRIK]" id="quick_FOH_LISTRIK" style="flex: 1; border: none; padding: 0.45rem 0.65rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['listrik'] ?? 223.80 }}">
                                <span style="background: #f8fafc; border-left: 1px solid #e2e8f0; color: #64748b; font-size: 0.7rem; padding: 0.45rem 0.6rem; display: flex; align-items: center;">/ Kg</span>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Pemeliharaan Mesin
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.65rem; display: flex; align-items: center;">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_PEMELIHARAAN]" id="quick_FOH_PEMELIHARAAN" style="flex: 1; border: none; padding: 0.45rem 0.65rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['pemeliharaan'] ?? 23.34 }}">
                                <span style="background: #f8fafc; border-left: 1px solid #e2e8f0; color: #64748b; font-size: 0.7rem; padding: 0.45rem 0.6rem; display: flex; align-items: center;">/ Kg</span>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Penyusutan Mesin/Gedung
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.65rem; display: flex; align-items: center;">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_PENYUSUTAN]" id="quick_FOH_PENYUSUTAN" style="flex: 1; border: none; padding: 0.45rem 0.65rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['penyusutan'] ?? 66.44 }}">
                                <span style="background: #f8fafc; border-left: 1px solid #e2e8f0; color: #64748b; font-size: 0.7rem; padding: 0.45rem 0.6rem; display: flex; align-items: center;">/ Kg</span>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Bahan Kimia IPAL
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.65rem; display: flex; align-items: center;">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_KIMIA_IPAL]" id="quick_FOH_KIMIA_IPAL" style="flex: 1; border: none; padding: 0.45rem 0.65rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['kimia'] ?? 45.09 }}">
                                <span style="background: #f8fafc; border-left: 1px solid #e2e8f0; color: #64748b; font-size: 0.7rem; padding: 0.45rem 0.6rem; display: flex; align-items: center;">/ Kg</span>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Fotocopy &amp; ATK
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.8rem; padding: 0.45rem 0.65rem; display: flex; align-items: center;">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_FOTOCOPY]" id="quick_FOH_FOTOCOPY" style="flex: 1; border: none; padding: 0.45rem 0.65rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['fotocopy'] ?? 28.00 }}">
                                <span style="background: #f8fafc; border-left: 1px solid #e2e8f0; color: #64748b; font-size: 0.7rem; padding: 0.45rem 0.6rem; display: flex; align-items: center;">/ Kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: LIMBAH FLAT, GAS CNG & UPAH TENAGA KERJA --}}
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.35rem;">
                        <span style="font-size: 0.775rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em;">
                            2. Limbah Flat, Gas CNG &amp; Upah Tenaga Kerja
                        </span>
                        <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Satuan Nominal Rupiah</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Limbah Padat (Rp/Shift)
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.75rem; padding: 0.45rem 0.55rem; display: flex; align-items: center;">Rp</span>
                                <input type="number" step="1000" min="0" name="rates[FOH_LIMBAH_PADAT]" id="quick_FOH_LIMBAH_PADAT" style="flex: 1; border: none; padding: 0.45rem 0.55rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $fohRates['limbah_padat'] ?? 180000 }}">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Gas CNG (Rp/MMBTU)
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.75rem; padding: 0.45rem 0.55rem; display: flex; align-items: center;">Rp</span>
                                <input type="number" step="100" min="0" name="rates[TARIF_CNG]" id="quick_TARIF_CNG" style="flex: 1; border: none; padding: 0.45rem 0.55rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $energiTkRates['cng_tarif'] ?? 226800 }}">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Upah TK (Rp/Orang)
                            </label>
                            <div style="display: flex; align-items: stretch; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #ffffff;">
                                <span style="background: #f8fafc; border-right: 1px solid #e2e8f0; color: #475569; font-weight: 700; font-size: 0.75rem; padding: 0.45rem 0.55rem; display: flex; align-items: center;">Rp</span>
                                <input type="number" step="100" min="0" name="rates[TARIF_TK_HARIAN]" id="quick_TARIF_TK_HARIAN" style="flex: 1; border: none; padding: 0.45rem 0.55rem; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; font-family: monospace; outline: none;" value="{{ $energiTkRates['tk_tarif_per_org'] ?? 91300 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- FOOTER MODAL --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('master.tarif_produksi.index') }}" target="_blank" style="font-size: 0.775rem; color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                <span>Buka Master Data Penuh ↗</span>
            </a>
            <div style="display: flex; gap: 0.65rem;">
                <button type="button" onclick="closeQuickTarifModal()" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 600; color: #475569; cursor: pointer;">
                    Batal
                </button>
                <button type="button" id="btnSaveQuickTarif" onclick="submitQuickTarif()" style="background: #059669; border: 1px solid #047857; border-radius: 6px; padding: 0.5rem 1.25rem; font-size: 0.8125rem; font-weight: 700; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 1px 2px rgba(0,0,0,0.06);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Terapkan ke Form</span>
                </button>
            </div>
        </div>

    </div>
</div>
