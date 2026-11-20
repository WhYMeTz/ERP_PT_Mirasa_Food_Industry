{{-- Modal Konfirmasi Setujui PO (macOS Style) --}}
<div id="modalApprovePo" class="modal-review-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalApproveTitle">
    <div class="modal-review-content">
        {{-- macOS Window Titlebar --}}
        <div class="macos-doc-modal-header">
            {{-- Left: macOS Traffic Lights (Window Controls) --}}
            <div class="mac-traffic-lights" aria-label="Window Controls">
                <button type="button" class="mac-traffic-dot red" onclick="closeReviewModal('modalApprovePo')" title="Tutup (Esc)">
                    <span class="mac-traffic-icon">&times;</span>
                </button>
                <button type="button" class="mac-traffic-dot yellow" onclick="closeReviewModal('modalApprovePo')" title="Minimalkan">
                    <span class="mac-traffic-icon">&minus;</span>
                </button>
                <button type="button" class="mac-traffic-dot green" title="Disetujui">
                    <span class="mac-traffic-icon">&plus;</span>
                </button>
            </div>

            {{-- Center: macOS Document Proxy & Title --}}
            <div class="mac-window-title-center" id="modalApproveTitle">
                <svg class="mac-window-file-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="mac-window-filename">PO #{{ $po->po_no }} &mdash; Persetujuan Resmi</span>
                <span class="modal-status-chip chip-approved">✓ Siap Disahkan</span>
            </div>

            {{-- Right: Esc indicator --}}
            <div class="modal-header-actions">
                <button type="button" class="btn-mac-seg" onclick="closeReviewModal('modalApprovePo')" title="Tutup (Esc)" style="font-size: 0.75rem; padding: 0.2rem 0.65rem; border-radius: 6px;">
                    Esc
                </button>
            </div>
        </div>
        
        <form id="formApprovePo" action="{{ route('gudang.po.approve_internal', $po->po_id) }}" method="POST">
            @csrf
            <div class="modal-review-body" style="padding: 1.35rem 1.5rem;">
                
                {{-- Dialog Hero --}}
                <div class="mac-dialog-hero">
                    <div class="mac-dialog-icon-box approve-icon">
                        <svg width="24" height="24" fill="none" stroke="#4ade80" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="mac-dialog-text">
                        <h4>Sahkan Pengadaan Dokumen PO?</h4>
                        <p>Dokumen Purchase Order ini akan disetujui secara resmi, diterbitkan tanda tangan digital &amp; QR-Code legalitas untuk dilanjutkan proses pengadaan gudang.</p>
                    </div>
                </div>

                {{-- Key Details Card --}}
                <div class="mac-dialog-kv-card">
                    <div class="mac-dialog-kv-row">
                        <span class="mac-dialog-kv-key">Nomor Dokumen</span>
                        <span class="mac-dialog-kv-val" style="color: #0284c7;">{{ $po->po_no }}</span>
                    </div>
                    <div class="mac-dialog-kv-row">
                        <span class="mac-dialog-kv-key">Mitra Supplier</span>
                        <span class="mac-dialog-kv-val">{{ $po->supplier?->supplier_nm ?? '-' }}</span>
                    </div>
                    <div class="mac-dialog-kv-row">
                        <span class="mac-dialog-kv-key">Total Anggaran Belanja</span>
                        <span class="mac-dialog-kv-val" style="color: #059669; font-size: 0.95rem;">
                            Rp {{ number_format($po->total_tagihan ?? $po->grand_total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <label for="approval_notes" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #09090b; margin-bottom: 0.35rem;">
                        Catatan Persetujuan untuk Tim Gudang &amp; Purchasing (Opsional):
                    </label>
                    <textarea 
                        name="notes" 
                        id="approval_notes" 
                        class="modal-textarea" 
                        rows="3"
                        placeholder="Contoh: Disetujui sesuai alokasi anggaran bulan ini. Pastikan barang diterima tepat waktu."></textarea>
                </div>
            </div>

            <div class="modal-review-footer">
                <button type="button" class="btn-executive btn-executive-secondary" onclick="closeReviewModal('modalApprovePo')">
                    Batal
                </button>
                <button type="submit" class="btn-executive btn-executive-approve" id="btnSubmitApprove">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Setujui &amp; Terbitkan PO
                </button>
            </div>
        </form>
    </div>
</div>
