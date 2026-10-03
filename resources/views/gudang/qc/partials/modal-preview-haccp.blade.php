{{-- MODAL POPUP PREVIEW DOKUMEN HACCP (STANDAR WEB ERP) --}}
<div id="modalHaccpPreview" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1.25rem;">
    <div style="background: #ffffff; width: 100%; max-width: 1100px; height: 92vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #cbd5e1; animation: modalZoomIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- HEADER MODAL --}}
        <div style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                    📄
                </div>
                <div>
                    <h3 id="modalHaccpTitle" style="margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                        Dokumen Pemeriksaan Mutu (HACCP)
                    </h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b;">
                        Lembar formulir standar mutu kedatangan bahan baku PT Mirasa Food Industry
                    </p>
                </div>
            </div>

            {{-- TOMBOL AKSI CEPAT DI HEADER POPUP --}}
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" id="modalHaccpPrintBtn" onclick="printHaccpModalIframe()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Lembar A4</span>
                </button>

                <a id="modalHaccpEditBtn" href="#" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Dokumen</span>
                </a>

                <button type="button" onclick="closeHaccpModal()" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; font-size: 1.25rem; font-weight: 700; line-height: 1;" title="Tutup Popup (Esc)">
                    &times;
                </button>
            </div>
        </div>

        {{-- BODY MODAL: IFRAME DOKUMEN DENGAN LOADING SPINNER --}}
        <div style="flex: 1; background: #f8fafc; position: relative; overflow: hidden;">
            {{-- SPINNER OVERLAY --}}
            <div id="modalHaccpLoading" style="position: absolute; inset: 0; background: #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; z-index: 5;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" style="animation: spin 0.8s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                </svg>
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Memuat Dokumen HACCP...</span>
            </div>

            {{-- IFRAME VIEWPORT --}}
            <iframe id="haccpPreviewIframe" 
                    src="about:blank" 
                    style="width: 100%; height: 100%; border: none; background: #f8fafc;" 
                    onload="onHaccpIframeLoaded()">
            </iframe>
        </div>
    </div>
</div>

<style>
@keyframes modalZoomIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
