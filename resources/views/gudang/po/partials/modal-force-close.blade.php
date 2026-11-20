{{-- MODAL KONFIRMASI BATALKAN SISA & SELESAIKAN PO --}}
<div id="modalForceClose" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e2e8f0; animation: modalFadeIn 0.2s ease-out;">
        
        {{-- Header Modal --}}
        <div style="background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%); border-bottom: 1.5px solid #fecaca; padding: 1.15rem 1.35rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; border: 1px solid #fca5a5; display: flex; align-items: center; justify-content: center; color: #b91c1c;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <strong style="color: #991b1b; font-size: 1.05rem; display: block; line-height: 1.2;">Batalkan Sisa &amp; Selesaikan PO</strong>
                    <span style="font-size: 0.775rem; color: #b91c1c;">Barang yang sudah datang tetap sah &bull; Sisa dibatalkan</span>
                </div>
            </div>
            <button type="button" onclick="closeForceCloseModal()" style="background: transparent; border: none; font-size: 1.35rem; color: #991b1b; cursor: pointer; line-height: 1; padding: 0.2rem 0.4rem; border-radius: 4px;">&times;</button>
        </div>

        {{-- Form Body --}}
        <form id="formForceClose" action="{{ isset($po) ? route('gudang.po.force_close', $po->po_id) : '#' }}" method="POST" style="padding: 1.35rem;">
            @csrf

            {{-- Kartu Ringkasan Realisasi & Sisa --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.15rem; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.4rem; border-bottom: 1px dashed #cbd5e1; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Nomor Dokumen PO:</span>
                    <strong id="mfcPoNo" style="color: #0f172a; font-family: monospace;">{{ $po->po_no ?? '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.4rem; border-bottom: 1px dashed #cbd5e1; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Supplier Rekanan:</span>
                    <strong id="mfcSupplier" style="color: #0f172a;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.4rem; border-bottom: 1px dashed #cbd5e1; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Barang Sudah Masuk (GRN):</span>
                    <span id="mfcTerimaQty" style="font-weight: 700; color: #047857;">
                        {{ isset($po) ? number_format((float)$po->details->sum('terima_qty'), 2) : '0' }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 0.15rem;">
                    <span style="color: #991b1b; font-weight: 600;">Sisa Kuota Dibatalkan:</span>
                    <span id="mfcSisaQty" style="font-weight: 800; color: #dc2626; font-size: 0.95rem;">
                        {{ isset($po) ? number_format((float)$po->total_sisa_qty, 2) : '0' }}
                    </span>
                </div>
            </div>

            <div class="alert" style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; padding: 0.65rem 0.85rem; font-size: 0.8rem; border-radius: 6px; margin-bottom: 1.15rem; line-height: 1.45;">
                <strong>Dampak Operasional:</strong> Seluruh barang yang <em>sudah diterima</em> tetap tersimpan aman di kartu stok gudang. Hanya sisa kuota yang belum datang yang akan <strong>dibatalkan</strong>, dan status PO resmi berubah menjadi <strong>SELESAI</strong>.
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="closed_reason" class="form-label" style="font-weight: 700; color: #0f172a; font-size: 0.85rem; margin-bottom: 0.35rem; display: block;">
                    Alasan Pembatalan Sisa Barang <span style="color:#ef4444;">*</span>
                </label>
                <textarea id="closed_reason" 
                          name="closed_reason" 
                          rows="3" 
                          class="form-control" 
                          placeholder="Contoh: Supplier mengonfirmasi stok singkong/bahan baku habis dan sisa kuota tidak dapat dikirim lagi..." 
                          required 
                          minlength="5"
                          style="font-size: 0.85rem; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem;"></textarea>
                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Wajib diisi minimal 5 karakter sebagai audit trail resmi.</small>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.65rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <button type="button" onclick="closeForceCloseModal()" class="btn btn-secondary" style="padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; border-radius: 6px;">
                    Kembali
                </button>
                <button type="submit" class="btn" style="background: #dc2626; color: #ffffff; border: none; padding: 0.45rem 1.15rem; font-size: 0.85rem; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Batalkan Sisa &amp; Selesaikan PO</span>
                </button>
            </div>
        </form>
    </div>
</div>
