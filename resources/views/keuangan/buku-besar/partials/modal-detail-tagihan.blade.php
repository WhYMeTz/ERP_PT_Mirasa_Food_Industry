{{-- Modal Pop-up Detail Tagihan Hutang Pembelian (General Ledger / Buku Besar) --}}
<div id="modalDetailTagihan" class="gl-modal-backdrop" style="display: none;" onclick="handleModalBackdropClick(event)">
    <div class="gl-modal-dialog" onclick="event.stopPropagation()">
        
        {{-- Header Modal --}}
        <div class="gl-modal-header">
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <div class="gl-modal-icon">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h3 id="modalTagihanNo" style="margin: 0; font-size: 1.15rem; font-weight: 800; font-family: monospace; color: #0f172a;">
                            AP-202610-0000
                        </h3>
                        <span id="modalTagihanBadge" class="gl-badge-status">MEMUAT</span>
                    </div>
                    <span id="modalTagihanSupplier" style="font-size: 0.8125rem; color: #64748b; font-weight: 600;">
                        Nama Rekanan Supplier
                    </span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <a id="modalTagihanFullLink" href="#" target="_blank" class="gl-btn-subtle" title="Buka di tab baru">
                    <span>Buka Halaman Penuh</span>
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <button type="button" class="gl-btn-close" onclick="closeDetailTagihanModal()" title="Tutup Modal (Esc)">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Body Modal --}}
        <div class="gl-modal-body">

            {{-- State: Loading --}}
            <div id="modalTagihanLoading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1rem; color: #64748b;">
                <svg class="animate-spin" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669; margin-bottom: 0.75rem;">
                    <circle cx="12" cy="12" r="10" stroke-width="4" style="opacity: 0.2;"></circle>
                    <path d="M4 12a8 8 0 018-8" stroke-width="4" style="opacity: 0.8;"></path>
                </svg>
                <span style="font-size: 0.875rem; font-weight: 600;">Mengambil rincian tagihan faktur...</span>
            </div>

            {{-- State: Content Loaded --}}
            <div id="modalTagihanContent" style="display: none;">
                
                {{-- Info Ringkas Meta --}}
                <div class="gl-info-grid">
                    <div class="gl-info-card">
                        <span class="gl-info-label">Tanggal Tagihan</span>
                        <strong id="modalTagihanTgl" class="gl-info-val">-</strong>
                    </div>
                    <div class="gl-info-card">
                        <span class="gl-info-label">Jatuh Tempo</span>
                        <strong id="modalTagihanTempo" class="gl-info-val">-</strong>
                    </div>
                    <div class="gl-info-card">
                        <span class="gl-info-label">No. Faktur Fisik Supplier</span>
                        <strong id="modalTagihanFakturFisik" class="gl-info-val" style="font-family: monospace;">-</strong>
                    </div>
                    <div class="gl-info-card">
                        <span class="gl-info-label">Gudang / Lokasi Terima</span>
                        <strong id="modalTagihanGudang" class="gl-info-val">-</strong>
                    </div>
                </div>

                {{-- Tabel Item Tagihan --}}
                <div class="gl-section-title">
                    <h4>Rincian Barang & Penerimaan (GRN)</h4>
                    <span id="modalTagihanItemCount" style="font-size: 0.75rem; color: #64748b;">0 Barang</span>
                </div>

                <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 1.25rem;">
                    <table class="gl-modal-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Item / Bahan Baku</th>
                                <th style="width: 140px;">No. GRN</th>
                                <th style="width: 90px; text-align: right;">Kuantitas</th>
                                <th style="width: 120px; text-align: right;">Harga Satuan</th>
                                <th style="width: 135px; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modalTagihanItemBody">
                            {{-- Diisi via JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- Rekapitulasi Finansial & Jurnal Akuntansi --}}
                <div class="gl-rekap-grid">
                    {{-- Kolom Kiri: Jurnal Double-Entry Terkait --}}
                    <div class="gl-rekap-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 4px;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                                Jurnal Akuntansi Otomatis
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span id="modalTagihanJurnalNo" style="font-size: 0.75rem; font-family: monospace; font-weight: 700; color: #0284c7;">
                                    -
                                </span>
                                <button type="button" 
                                        id="btnBukaDetailJurnalModal" 
                                        onclick="openJurnalFromCurrentTagihan()" 
                                        class="gl-btn-subtle" 
                                        style="font-size: 0.7rem; padding: 2px 8px; display: none; cursor: pointer;" 
                                        title="Buka Pop-up Detail Jurnal Umum Lengkap">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Pop-up Jurnal</span>
                                </button>
                            </div>
                        </div>
                        <div id="modalTagihanJurnalLines" style="font-size: 0.8rem; display: flex; flex-direction: column; gap: 4px;">
                            {{-- Diisi via JS --}}
                        </div>
                    </div>

                    {{-- Kolom Kanan: Rincian Angka Keuangan --}}
                    <div class="gl-rekap-box finance-summary">
                        <div class="gl-summary-row">
                            <span>Subtotal Barang:</span>
                            <strong id="modalTagihanSubtotal">Rp 0</strong>
                        </div>
                        <div class="gl-summary-row" id="modalTagihanOngkirRow">
                            <span>Ongkos Angkut (Ekspedisi):</span>
                            <strong id="modalTagihanOngkir" style="color: #0284c7;">+ Rp 0</strong>
                        </div>
                        <div class="gl-summary-row" id="modalTagihanPpnRow">
                            <span>PPN Masukan (11%):</span>
                            <strong id="modalTagihanPpn" style="color: #0284c7;">+ Rp 0</strong>
                        </div>
                        <div class="gl-summary-row" id="modalTagihanReturRow">
                            <span>Potongan Retur:</span>
                            <strong id="modalTagihanRetur" style="color: #dc2626;">- Rp 0</strong>
                        </div>
                        <div class="gl-summary-row total-row">
                            <span>Total Kewajiban Tagihan:</span>
                            <strong id="modalTagihanTotal">Rp 0</strong>
                        </div>
                        <div class="gl-summary-row" style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #cbd5e1;">
                            <span style="color: #166534;">Sudah Dibayar (BKK):</span>
                            <strong id="modalTagihanDibayar" style="color: #166534;">Rp 0</strong>
                        </div>
                        <div class="gl-summary-row" style="font-size: 0.95rem;">
                            <span style="color: #b91c1c; font-weight: 800;">Sisa Hutang:</span>
                            <strong id="modalTagihanSisa" style="color: #b91c1c; font-weight: 900;">Rp 0</strong>
                        </div>
                    </div>
                </div>

                {{-- Riwayat Pembayaran BKK (Jika Ada) --}}
                <div id="modalTagihanBayarSection" style="margin-top: 1rem; display: none;">
                    <div class="gl-section-title">
                        <h4>Riwayat Pembayaran Kas / Bank (BKK)</h4>
                    </div>
                    <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <table class="gl-modal-table">
                            <thead>
                                <tr>
                                    <th>No. Bukti Kas Keluar</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Akun Kas / Bank</th>
                                    <th style="text-align: right;">Nominal Bayar</th>
                                </tr>
                            </thead>
                            <tbody id="modalTagihanBayarBody">
                                {{-- Diisi via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="gl-modal-footer">
            <span style="font-size: 0.75rem; color: #94a3b8;">
                Tekan tombol <kbd style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; color: #334155;">ESC</kbd> untuk menutup pop-up.
            </span>
            <button type="button" class="btn btn-secondary" onclick="closeDetailTagihanModal()" style="font-size: 0.8125rem; font-weight: 700;">
                Tutup
            </button>
        </div>

    </div>
</div>
