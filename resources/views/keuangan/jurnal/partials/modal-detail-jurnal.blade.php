{{-- MODAL DETAIL JURNAL UMUM (MACOS NATIVE WINDOW) --}}
<div id="modalDetailJurnal" 
     onclick="if(event.target.id === 'modalDetailJurnal') closeModalDetailJurnal();"
     style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 1060; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(4px);">
    <div onclick="event.stopPropagation()" class="macos-window-frame">
        
        {{-- MACOS WINDOW TITLE BAR --}}
        <div class="macos-title-bar">
            <div class="macos-traffic-lights">
                <span class="traffic-dot dot-close" onclick="closeModalDetailJurnal()" title="Tutup (Esc)"></span>
                <span class="traffic-dot dot-minimize" onclick="closeModalDetailJurnal()" title="Tutup"></span>
                <span class="traffic-dot dot-expand" title="Jurnal Transaksi"></span>
            </div>

            <div class="macos-title-center">
                <span class="macos-title-icon">📑</span>
                <span class="macos-title-heading">Rincian Jurnal Umum</span>
                <span id="modalJurnalNo" class="macos-title-mono">-</span>
            </div>

            <div class="macos-title-esc">
                <span>esc</span>
            </div>
        </div>

        {{-- MODAL BODY --}}
        <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; background: #ffffff;">
            {{-- LOADING STATE --}}
            <div id="modalJurnalLoading" style="text-align: center; padding: 3rem 1rem; color: #0284c7; font-weight: 700; font-size: 0.875rem;">
                ⏳ Memuat rincian jurnal...
            </div>

            {{-- CONTENT CONTAINER --}}
            <div id="modalJurnalContent" style="display: none;">
                {{-- INFO META TRANSAKSI (MACOS CLEAN GRID) --}}
                <div class="macos-meta-box">
                    <div class="macos-meta-cell">
                        <span class="macos-meta-lbl">Tanggal Jurnal</span>
                        <div id="modalJurnalTgl" class="macos-meta-val">-</div>
                    </div>
                    <div class="macos-meta-cell">
                        <span class="macos-meta-lbl">Tipe Transaksi</span>
                        <div id="modalJurnalTipe" class="macos-meta-val" style="color: #0284c7;">-</div>
                    </div>
                    <div class="macos-meta-cell">
                        <span class="macos-meta-lbl">No. Referensi / Dokumen</span>
                        <div id="modalJurnalRef" class="macos-meta-val" style="font-family: monospace;">-</div>
                    </div>
                    <div class="macos-meta-cell">
                        <span class="macos-meta-lbl">Otoritas / Pembuat</span>
                        <div id="modalJurnalCreatedBy" class="macos-meta-val" style="color: #475569;">-</div>
                    </div>
                </div>

                {{-- DESKRIPSI JURNAL --}}
                <div class="macos-desc-box">
                    <span class="macos-desc-lbl">Deskripsi / Memo:</span>
                    <span id="modalJurnalKeterangan" class="macos-desc-text">-</span>
                </div>

                {{-- TABEL ENTRI DOUBLE-ENTRY --}}
                <div class="macos-table-card">
                    <table class="macos-entry-table">
                        <thead>
                            <tr>
                                <th>Akun Bagan (COA)</th>
                                <th>Keterangan Baris</th>
                                <th style="text-align: right; width: 140px;">Debet</th>
                                <th style="text-align: right; width: 140px;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="modalJurnalLinesTbody">
                            {{-- Populated via AJAX --}}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right; font-weight: 800; color: #1e293b; text-transform: uppercase;">Total:</td>
                                <td id="modalJurnalTotalDebet" style="text-align: right; font-weight: 800; font-family: monospace; color: #15803d; font-size: 0.9rem;">Rp 0</td>
                                <td id="modalJurnalTotalKredit" style="text-align: right; font-weight: 800; font-family: monospace; color: #7e22ce; font-size: 0.9rem;">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- AUDIT BALANCE STATUS --}}
                <div class="macos-audit-banner">
                    <div style="display: flex; align-items: center; gap: 0.45rem;">
                        <span class="macos-audit-check">✓</span>
                        <span style="font-size: 0.775rem; font-weight: 700; color: #166534;">Jurnal berpasangan seimbang (100% Balanced).</span>
                    </div>
                    <span style="font-size: 0.725rem; font-weight: 700; color: #15803d;">Double Entry Audit Valid</span>
                </div>
            </div>
        </div>

        {{-- MACOS FOOTER ACTIONS --}}
        <div class="macos-footer-bar">
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="closeModalDetailJurnal()" 
                    style="font-size: 0.8rem; font-weight: 700; padding: 0.4rem 1.15rem; border-radius: 6px;">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>
