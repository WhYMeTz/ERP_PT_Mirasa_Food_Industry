{{-- Modal Rincian Bukti Kas Masuk (BKM) --}}
<div id="modalDetailBkm" class="vj-modal-backdrop" style="display: none;" aria-hidden="true">
    <div class="vj-modal-dialog vj-modal-lg" role="dialog" aria-modal="true" aria-labelledby="modalDetailBkmTitle">
        <div class="vj-modal-header">
            <div class="vj-modal-header-info">
                <span class="vj-kicker">INFORMASI TRANSAKSI</span>
                <h3 id="modalDetailBkmTitle" class="vj-modal-title">Rincian Bukti Kas Masuk</h3>
            </div>
            <button type="button" class="vj-modal-close" onclick="closeDetailModalBkm()" aria-label="Tutup modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="vj-modal-body">
            {{-- Info Ringkasan Header BKM --}}
            <div class="vj-detail-meta-grid">
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Nomor BKM</span>
                    <span class="vj-meta-value font-mono" id="dtlBkmNo">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Tanggal Penerimaan</span>
                    <span class="vj-meta-value" id="dtlBkmTgl">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Rekening Kas / Bank Tujuan</span>
                    <span class="vj-meta-value text-emerald-700 font-semibold" id="dtlBkmKasBank">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Diterima Dari (Penyetor)</span>
                    <span class="vj-meta-value" id="dtlBkmPenyetor">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Metode Penerimaan</span>
                    <span class="vj-meta-value" id="dtlBkmMetode">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Kasir Penerima</span>
                    <span class="vj-meta-value" id="dtlBkmKasir">-</span>
                </div>
            </div>

            {{-- Tabel Item Rincian BKM --}}
            <div class="vj-detail-items-wrapper">
                <h4 class="vj-section-subtitle">Daftar Item / Rincian Penerimaan</h4>
                <div class="vj-table-scroll-mini">
                    <table class="vj-table-mini">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Uraian Penerimaan</th>
                                <th style="width: 90px; text-align: center;">Qty</th>
                                <th style="width: 140px; text-align: right;">Harga Satuan</th>
                                <th style="width: 150px; text-align: right;">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="dtlBkmItemsTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Memuat rincian item...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 700; color: #0f172a;">Total Penerimaan:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: #047857;" id="dtlBkmTotalNominal">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Catatan Kasir --}}
            <div class="vj-detail-note" id="dtlBkmNoteWrapper" style="display: none;">
                <span class="vj-note-label">Catatan Penerimaan:</span>
                <p class="vj-note-text" id="dtlBkmNoteText">-</p>
            </div>
        </div>

        <div class="vj-modal-footer">
            <button type="button" class="vj-btn vj-btn-secondary" onclick="closeDetailModalBkm()">
                <span>Tutup</span>
            </button>
            <a href="#" id="dtlBkmLinkSlip" class="vj-btn vj-btn-primary" target="_blank" rel="noopener">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Buka Dokumen BKM Lengkap</span>
            </a>
        </div>
    </div>
</div>
