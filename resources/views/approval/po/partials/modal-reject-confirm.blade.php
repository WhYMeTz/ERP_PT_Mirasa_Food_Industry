{{-- Modal Penolakan PO (Reject - macOS Style) --}}
<div id="modalRejectPo" class="modal-review-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalRejectTitle">
    <div class="modal-review-content">
        {{-- macOS Window Titlebar --}}
        <div class="macos-doc-modal-header">
            {{-- Left: macOS Traffic Lights (Window Controls) --}}
            <div class="mac-traffic-lights" aria-label="Window Controls">
                <button type="button" class="mac-traffic-dot red" onclick="closeReviewModal('modalRejectPo')" title="Tutup (Esc)">
                    <span class="mac-traffic-icon">&times;</span>
                </button>
                <button type="button" class="mac-traffic-dot yellow" onclick="closeReviewModal('modalRejectPo')" title="Minimalkan">
                    <span class="mac-traffic-icon">&minus;</span>
                </button>
                <button type="button" class="mac-traffic-dot green" title="Batal">
                    <span class="mac-traffic-icon">&plus;</span>
                </button>
            </div>

            {{-- Center: macOS Document Proxy & Title --}}
            <div class="mac-window-title-center" id="modalRejectTitle">
                <svg class="mac-window-file-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="mac-window-filename">PO #{{ $po->po_no }} &mdash; Tolak Pengadaan</span>
                <span class="modal-status-chip" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca;">
                    ✕ Batalkan PO
                </span>
            </div>

            {{-- Right: Esc indicator --}}
            <div class="modal-header-actions">
                <button type="button" class="btn-mac-seg" onclick="closeReviewModal('modalRejectPo')" title="Tutup (Esc)" style="font-size: 0.75rem; padding: 0.2rem 0.65rem; border-radius: 6px;">
                    Esc
                </button>
            </div>
        </div>
        
        <form id="formRejectPo" action="{{ route('gudang.po.reject_internal', $po->po_id) }}" method="POST">
            @csrf
            <div class="modal-review-body" style="padding: 1.35rem 1.5rem;">
                
                {{-- Dialog Hero --}}
                <div class="mac-dialog-hero">
                    <div class="mac-dialog-icon-box reject-icon">
                        <svg width="24" height="24" fill="none" stroke="#dc2626" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mac-dialog-text">
                        <h4>Tolak Pengadaan Dokumen PO?</h4>
                        <p>Menolak pengadaan akan membatalkan pemesanan ini secara permanen. Dokumen <strong>{{ $po->po_no }}</strong> tidak dapat diajukan kembali tanpa membuat PO baru.</p>
                    </div>
                </div>

                {{-- Key Details Card --}}
                <div class="mac-dialog-kv-card">
                    <div class="mac-dialog-kv-row">
                        <span class="mac-dialog-kv-key">Nomor Dokumen</span>
                        <span class="mac-dialog-kv-val" style="color: #dc2626;">{{ $po->po_no }}</span>
                    </div>
                    <div class="mac-dialog-kv-row">
                        <span class="mac-dialog-kv-key">Mitra Supplier</span>
                        <span class="mac-dialog-kv-val">{{ $po->supplier?->supplier_nm ?? '-' }}</span>
                    </div>
                    <div class="mac-dialog-kv-row">
                        <span class="mac-dialog-kv-key">Nilai Dibatalkan</span>
                        <span class="mac-dialog-kv-val" style="color: #dc2626;">
                            Rp {{ number_format($po->total_tagihan ?? $po->grand_total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <label for="reject_reason" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #09090b; margin-bottom: 0.35rem;">
                        Alasan Resmi Penolakan: <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea 
                        name="reject_reason" 
                        id="reject_reason" 
                        class="modal-textarea" 
                        required 
                        rows="3"
                        placeholder="Sebutkan pertimbangan manajemen mengapa pengadaan ini dibatalkan/ditolak secara resmi."></textarea>
                </div>
            </div>

            <div class="modal-review-footer">
                <button type="button" class="btn-executive btn-executive-secondary" onclick="closeReviewModal('modalRejectPo')">
                    Batal
                </button>
                <button type="submit" class="btn-executive btn-executive-reject" id="btnSubmitReject" style="background: #dc2626; color: #ffffff;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Konfirmasi Tolak Pengadaan
                </button>
            </div>
        </form>
    </div>
</div>
