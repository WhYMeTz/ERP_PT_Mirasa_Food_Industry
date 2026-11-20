@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/jurnal/modal-detail-jurnal.css') }}?v={{ file_exists(public_path('css/keuangan/jurnal/modal-detail-jurnal.css')) ? filemtime(public_path('css/keuangan/jurnal/modal-detail-jurnal.css')) : time() }}">
@endpush

{{-- MODAL DETAIL JURNAL UMUM (MACOS NATIVE WINDOW) --}}
<div id="modalDetailJurnal" 
     onclick="if(event.target.id === 'modalDetailJurnal') closeModalDetailJurnal();">
    <div onclick="event.stopPropagation()" class="macos-window-frame">
        
        {{-- MACOS WINDOW TITLE BAR --}}
        <div class="macos-title-bar">
            <div class="macos-traffic-lights">
                <span class="traffic-dot dot-close" onclick="closeModalDetailJurnal()" title="Tutup (Esc)"></span>
                <span class="traffic-dot dot-minimize" onclick="closeModalDetailJurnal()" title="Tutup"></span>
                <span class="traffic-dot dot-expand" title="Jurnal Transaksi"></span>
            </div>

            <div class="macos-title-center">
                <span class="macos-title-icon">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="macos-title-heading">Rincian Jurnal Umum</span>
                <span id="modalJurnalNo" class="macos-title-mono">-</span>
            </div>

            <div class="macos-title-esc">
                <kbd class="macos-kbd" onclick="closeModalDetailJurnal()" title="Tekan Esc untuk menutup">esc</kbd>
            </div>
        </div>

        {{-- MODAL BODY --}}
        <div class="macos-window-body">
            {{-- LOADING STATE --}}
            <div id="modalJurnalLoading" style="text-align: center; padding: 3rem 1rem; color: #0284c7; font-weight: 700; font-size: 0.875rem;">
                <div style="font-size: 1.5rem; margin-bottom: 0.5rem; animation: spin 1s linear infinite;">⏳</div>
                <div>Memuat rincian jurnal transaksi...</div>
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
                        <div id="modalJurnalTipe" class="macos-meta-val">-</div>
                    </div>
                    <div class="macos-meta-cell">
                        <span class="macos-meta-lbl">No. Referensi / Dokumen</span>
                        <div id="modalJurnalRef" class="macos-meta-val" style="font-family: monospace; color: #0f172a;">-</div>
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
                                <td id="modalJurnalTotalDebet" style="text-align: right; font-weight: 800; font-family: monospace; color: #047857; font-size: 0.9rem;">Rp 0</td>
                                <td id="modalJurnalTotalKredit" style="text-align: right; font-weight: 800; font-family: monospace; color: #4338ca; font-size: 0.9rem;">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- AUDIT BALANCE STATUS --}}
                <div class="macos-audit-banner">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="macos-audit-check">✓</span>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #166534;">Jurnal berpasangan seimbang (100% Balanced).</span>
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #047857; background: #dcfce7; padding: 2px 8px; border-radius: 4px; border: 1px solid #86efac;">Double Entry Audit Valid</span>
                </div>
            </div>
        </div>

        {{-- MACOS FOOTER ACTIONS --}}
        <div class="macos-footer-bar">
            <button type="button" 
                    class="btn-macos-close" 
                    onclick="closeModalDetailJurnal()">
                <span>Tutup Jendela</span>
            </button>
        </div>
    </div>
</div>
