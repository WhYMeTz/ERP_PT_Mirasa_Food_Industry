{{-- Modal Rincian Bukti Kas Keluar (BKK) --}}
<div id="modalDetailBkk" class="vj-modal-backdrop" style="display: none;" aria-hidden="true">
    <div class="vj-modal-dialog vj-modal-lg" role="dialog" aria-modal="true" aria-labelledby="modalDetailTitle">
        <div class="vj-modal-header">
            <div class="vj-modal-header-info">
                <span class="vj-kicker">INFORMASI TRANSAKSI</span>
                <h3 id="modalDetailTitle" class="vj-modal-title">Rincian Bukti Kas Keluar</h3>
            </div>
            <button type="button" class="vj-modal-close" onclick="closeDetailModal()" aria-label="Tutup modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="vj-modal-body">
            {{-- Info Ringkasan Header --}}
            <div class="vj-detail-meta-grid">
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Nomor BKK</span>
                    <span class="vj-meta-value font-mono" id="dtlBkkNo">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Tanggal Transaksi</span>
                    <span class="vj-meta-value" id="dtlTglKeluar">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Sumber Kas / Bank</span>
                    <span class="vj-meta-value" id="dtlKasBank">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Penerima Dana</span>
                    <span class="vj-meta-value" id="dtlPenerima">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Metode Pembayaran</span>
                    <span class="vj-meta-value" id="dtlMetodeBayar">-</span>
                </div>
                <div class="vj-detail-meta-card">
                    <span class="vj-meta-label">Pemohon / PIC</span>
                    <span class="vj-meta-value" id="dtlPemohon">-</span>
                </div>
            </div>

            {{-- Tabel Item Rincian --}}
            <div class="vj-detail-items-wrapper">
                <h4 class="vj-section-subtitle">Daftar Item Pengeluaran</h4>
                <div class="vj-table-scroll-mini">
                    <table class="vj-table-mini">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Uraian Kebutuhan</th>
                                <th style="width: 90px; text-align: center;">Qty</th>
                                <th style="width: 140px; text-align: right;">Harga Satuan</th>
                                <th style="width: 150px; text-align: right;">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="dtlItemsTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 1.5rem;">Memuat rincian item...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 700; color: #0f172a;">Total Pengeluaran:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: #0f172a;" id="dtlTotalNominal">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Catatan Operasional --}}
            <div class="vj-detail-note" id="dtlNoteWrapper" style="display: none;">
                <span class="vj-note-label">Catatan Kasir:</span>
                <p class="vj-note-text" id="dtlNoteText">-</p>
            </div>
        </div>

        <div class="vj-modal-footer">
            <button type="button" class="vj-btn vj-btn-secondary" onclick="closeDetailModal()">
                <span>Tutup</span>
            </button>
            <a href="#" id="dtlLinkSlip" class="vj-btn vj-btn-primary" target="_blank" rel="noopener">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Buka Dokumen BKK Lengkap</span>
            </a>
        </div>
    </div>
</div>
