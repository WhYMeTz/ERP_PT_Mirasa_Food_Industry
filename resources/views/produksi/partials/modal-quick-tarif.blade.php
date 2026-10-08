{{-- 
  Modal Konfigurasi Cepat Standar Pengali FOH & Tarif Produksi
  ERP PT Mirasa Food Industry - Executive Modern Clean Redesign
--}}

<div id="modalQuickTarif" class="modal-backdrop-custom">
    <div class="quick-tarif-dialog">
        
        {{-- HEADER MODAL --}}
        <div class="quick-tarif-header">
            <div class="quick-tarif-header-left">
                <div class="quick-tarif-header-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="quick-tarif-header-title">Konfigurasi Cepat Standar Pengali &amp; Tarif</h3>
                    <div class="quick-tarif-header-subtitle">
                        Standar FOH otomatis dikalikan dengan Total KG WIP. CNG &amp; Upah TK menjadi acuan form Bagian 4 &amp; 5.
                    </div>
                </div>
            </div>
            <button type="button" class="quick-tarif-btn-close" onclick="closeQuickTarifModal()" title="Tutup">&times;</button>
        </div>

        {{-- BODY MODAL --}}
        <div class="quick-tarif-body">
            <form id="formQuickTarif">
                {{-- SECTION 1: STANDAR PENGALI FOH (PER KG WIP) --}}
                <div class="quick-tarif-section">
                    <div class="quick-tarif-sec-head">
                        <span class="quick-tarif-sec-title">
                            <span>1. Standar Pengali FOH Variabel</span>
                        </span>
                        <span class="quick-tarif-sec-badge">
                            Pengali × Total KG WIP
                        </span>
                    </div>

                    <div class="quick-tarif-grid-2">
                        {{-- 1. Pengawasan Mutu (QC) --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_QC">Pengawasan Mutu (QC)</label>
                                <span class="quick-tarif-pill">× / Kg</span>
                            </div>
                            <div class="quick-tarif-card-desc">Uji lab mutu &amp; kontrol sanitasi pangan</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_QC]" id="quick_FOH_QC" class="quick-tarif-input" value="{{ $fohRates['qc'] ?? 49.97 }}">
                                <span class="quick-tarif-addon quick-tarif-addon-right">/ Kg</span>
                            </div>
                        </div>

                        {{-- 2. Listrik, Air & Telp --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_LISTRIK">Listrik, Air &amp; Telp</label>
                                <span class="quick-tarif-pill">× / Kg</span>
                            </div>
                            <div class="quick-tarif-card-desc">Daya PLN operasional, genset &amp; air utilitas</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_LISTRIK]" id="quick_FOH_LISTRIK" class="quick-tarif-input" value="{{ $fohRates['listrik'] ?? 223.80 }}">
                                <span class="quick-tarif-addon quick-tarif-addon-right">/ Kg</span>
                            </div>
                        </div>

                        {{-- 3. Pemeliharaan Mesin --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_PEMELIHARAAN">Pemeliharaan Mesin</label>
                                <span class="quick-tarif-pill">× / Kg</span>
                            </div>
                            <div class="quick-tarif-card-desc">Pelumas, sparepart &amp; servis mesin fryer</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_PEMELIHARAAN]" id="quick_FOH_PEMELIHARAAN" class="quick-tarif-input" value="{{ $fohRates['pemeliharaan'] ?? 23.34 }}">
                                <span class="quick-tarif-addon quick-tarif-addon-right">/ Kg</span>
                            </div>
                        </div>

                        {{-- 4. Penyusutan Mesin / Gedung --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_PENYUSUTAN">Penyusutan Mesin &amp; Gedung</label>
                                <span class="quick-tarif-pill">× / Kg</span>
                            </div>
                            <div class="quick-tarif-card-desc">Amortisasi &amp; depresiasi fasilitas pabrik</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_PENYUSUTAN]" id="quick_FOH_PENYUSUTAN" class="quick-tarif-input" value="{{ $fohRates['penyusutan'] ?? 66.44 }}">
                                <span class="quick-tarif-addon quick-tarif-addon-right">/ Kg</span>
                            </div>
                        </div>

                        {{-- 5. Bahan Kimia IPAL --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_KIMIA_IPAL">Bahan Kimia IPAL (Limbah)</label>
                                <span class="quick-tarif-pill">× / Kg</span>
                            </div>
                            <div class="quick-tarif-card-desc">Pengolahan limbah cair &amp; netralisasi air</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">x</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_KIMIA_IPAL]" id="quick_FOH_KIMIA_IPAL" class="quick-tarif-input" value="{{ $fohRates['kimia'] ?? 45.09 }}">
                                <span class="quick-tarif-addon quick-tarif-addon-right">/ Kg</span>
                            </div>
                        </div>

                        {{-- 6. Fotocopy & ATK --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_FOTOCOPY">Fotocopy &amp; ATK</label>
                                <span class="quick-tarif-pill">Rp / Lembar</span>
                            </div>
                            <div class="quick-tarif-card-desc">2 stiker label per box karton (Rp/lembar)</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">Rp</span>
                                <input type="number" step="0.01" min="0" name="rates[FOH_FOTOCOPY]" id="quick_FOH_FOTOCOPY" class="quick-tarif-input" value="{{ $fohRates['fotocopy'] ?? 28.00 }}">
                                <span class="quick-tarif-addon quick-tarif-addon-right">/ Lbr</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: BIAYA FLAT OPERASIONAL, GAS & UPAH TENAGA KERJA --}}
                <div class="quick-tarif-section" style="margin-bottom: 0;">
                    <div class="quick-tarif-sec-head">
                        <span class="quick-tarif-sec-title">
                            <span>2. Biaya Flat Operasional, Gas &amp; Upah TK</span>
                        </span>
                        <span class="quick-tarif-sec-badge quick-tarif-sec-badge-flat">
                            Nominal Acuan (Rp)
                        </span>
                    </div>

                    <div class="quick-tarif-grid-3">
                        {{-- 1. Limbah Padat (Flat per Shift) --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_FOH_LIMBAH_PADAT">Limbah Padat</label>
                                <span class="quick-tarif-pill pill-green">Flat / Shift</span>
                            </div>
                            <div class="quick-tarif-card-desc">Retribusi sampah per shift</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">Rp</span>
                                <input type="number" step="1000" min="0" name="rates[FOH_LIMBAH_PADAT]" id="quick_FOH_LIMBAH_PADAT" class="quick-tarif-input" value="{{ $fohRates['limbah_padat'] ?? 180000 }}">
                            </div>
                        </div>

                        {{-- 2. Gas CNG (Rp/MMBTU) --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_TARIF_CNG">Gas Alam CNG</label>
                                <span class="quick-tarif-pill">/ MMBTU</span>
                            </div>
                            <div class="quick-tarif-card-desc">Acuan flow meter gas burner</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">Rp</span>
                                <input type="number" step="100" min="0" name="rates[TARIF_CNG]" id="quick_TARIF_CNG" class="quick-tarif-input" value="{{ $energiTkRates['cng_tarif'] ?? 226800 }}">
                            </div>
                        </div>

                        {{-- 3. Upah TK (Rp/Orang) --}}
                        <div class="quick-tarif-card">
                            <div class="quick-tarif-card-head">
                                <label class="quick-tarif-card-title" for="quick_TARIF_TK_HARIAN">Upah Harian TK</label>
                                <span class="quick-tarif-pill">/ Orang</span>
                            </div>
                            <div class="quick-tarif-card-desc">Tarif dasar harian per regu</div>
                            <div class="quick-tarif-input-box">
                                <span class="quick-tarif-addon quick-tarif-addon-left">Rp</span>
                                <input type="number" step="100" min="0" name="rates[TARIF_TK_HARIAN]" id="quick_TARIF_TK_HARIAN" class="quick-tarif-input" value="{{ $energiTkRates['tk_tarif_per_org'] ?? 91300 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- FOOTER MODAL --}}
        <div class="quick-tarif-footer">
            <a href="{{ route('master.tarif_produksi.index') }}" target="_blank" class="quick-tarif-footer-link" title="Buka master data tarif lengkap di tab baru">
                <span>Kelola Master Data Lengkap</span>
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <div style="display: flex; gap: 0.65rem;">
                <button type="button" class="quick-tarif-btn-cancel" onclick="closeQuickTarifModal()">
                    Batal
                </button>
                <button type="button" id="btnSaveQuickTarif" class="quick-tarif-btn-save" onclick="submitQuickTarif()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Terapkan ke Form</span>
                </button>
            </div>
        </div>

    </div>
</div>
