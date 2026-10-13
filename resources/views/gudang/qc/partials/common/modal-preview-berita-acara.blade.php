{{-- MODAL POPUP PREVIEW BERITA ACARA PENOLAKAN (STANDAR WEB ERP) --}}
<div id="modalBeritaAcaraPreview" style="display: none; position: fixed; inset: 0; z-index: 9999999; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;">
    <div style="background: #ffffff; width: 100%; max-width: 960px; height: 95vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #cbd5e1; animation: modalZoomIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- HEADER MODAL --}}
        <div style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                    📋
                </div>
                <div>
                    <h3 id="modalBeritaAcaraTitle" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                        Berita Acara Penolakan
                    </h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b;">
                        Formulir resmi penolakan kedatangan bahan PT Mirasa Food Industry
                    </p>
                </div>
            </div>

            {{-- TOMBOL AKSI CEPAT DI HEADER POPUP --}}
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" id="modalBaPrintBtn" onclick="printBeritaAcaraModalIframe()" class="modal-ba-hdr-btn modal-ba-hdr-btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Lembar A4</span>
                </button>

                <button type="button" id="modalBaEditBtn" onclick="toggleBeritaAcaraModalEdit()" class="modal-ba-hdr-btn modal-ba-hdr-btn-secondary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span id="modalBaEditBtnLabel">Mode Edit Dokumen</span>
                </button>

                <button type="button" onclick="resetBeritaAcaraModalIframe()" class="modal-ba-hdr-btn modal-ba-hdr-btn-outline" title="Kembalikan isi ke standar sistem">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                </button>

                <button type="button" onclick="closeBeritaAcaraModal()" class="modal-ba-hdr-btn-close" title="Tutup Popup (Esc)">
                    &times;
                </button>
            </div>
        </div>

        {{-- BODY MODAL: IFRAME BERITA ACARA DENGAN LOADING SPINNER --}}
        <div style="flex: 1; background: #f8fafc; position: relative; overflow: hidden;">
            {{-- SPINNER OVERLAY --}}
            <div id="modalBeritaAcaraLoading" style="position: absolute; inset: 0; background: #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; z-index: 5;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" style="animation: spin 0.8s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                </svg>
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Memuat Berita Acara Penolakan...</span>
            </div>

            {{-- IFRAME VIEWPORT --}}
            <iframe id="beritaAcaraPreviewIframe" 
                    src="about:blank" 
                    style="width: 100%; height: 100%; border: none; background: #f8fafc;" 
                    onload="onBeritaAcaraIframeLoaded()">
            </iframe>
        </div>
    </div>
</div>

<style>
.modal-ba-hdr-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.9rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    cursor: pointer;
    transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1.5px solid transparent;
    line-height: 1.25;
    outline: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.modal-ba-hdr-btn svg {
    flex-shrink: 0;
}

.modal-ba-hdr-btn-primary {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
}

.modal-ba-hdr-btn-primary:hover {
    background: #b91c1c;
    border-color: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(220, 38, 38, 0.35);
}

.modal-ba-hdr-btn-secondary {
    background: #ffffff;
    color: #334155;
    border-color: #cbd5e1;
}

.modal-ba-hdr-btn-secondary:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
}

.modal-ba-hdr-btn-secondary.is-active {
    background: #ecfdf5 !important;
    color: #059669 !important;
    border-color: #a7f3d0 !important;
}

.modal-ba-hdr-btn-outline {
    background: #f8fafc;
    color: #475569;
    border-color: #e2e8f0;
}

.modal-ba-hdr-btn-outline:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.modal-ba-hdr-btn-close {
    background: #f1f5f9;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #64748b;
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1;
    transition: all 0.15s ease;
    outline: none;
}

.modal-ba-hdr-btn-close:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #dc2626;
    transform: rotate(90deg);
}
</style>
