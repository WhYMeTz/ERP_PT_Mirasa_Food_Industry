{{-- MODAL PREVIEW DETAIL AUDIT TRAIL / REVISI PO (macOS Style ERP Mirasa) --}}
<div id="modalRevisiPo" class="modal-backdrop" onclick="if(event.target === this) closePoRevisiModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10050; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(5px);">
    <div class="modal-dialog mac-po-dialog" style="max-width: 800px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;">
        
        {{-- 1. macOS Titlebar --}}
        <div class="mac-po-titlebar" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; font-size: 0.9rem;">
                    📝
                </div>
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">Rincian Komparasi Audit Revisi PO</strong>
                    <span id="revModalNo" style="font-family: monospace; font-size: 0.8rem; font-weight: 700; color: #0284c7; margin-left: 0.35rem;">-</span>
                </div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closePoRevisiModal()" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                Tutup (Esc)
            </button>
        </div>

        {{-- 2. Modal Body --}}
        <div style="padding: 1.25rem; overflow-y: auto; flex: 1; background: #ffffff;">
            
            {{-- Info Header Pelaku Edit --}}
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.825rem;">
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Waktu Koreksi / Edit:</span>
                        <strong id="revModalDate" style="color: #0f172a;">-</strong>
                    </div>
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Diedit Oleh (Operator):</span>
                        <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 2px;">
                            <strong id="revModalUser" style="color: #0284c7;">-</strong>
                            <span id="revModalRole" class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.65rem; padding: 1px 5px;">-</span>
                        </div>
                    </div>
                    <div>
                        <span style="color: #64748b; display: block; font-size: 0.75rem;">Kategori Perubahan:</span>
                        <span id="revModalCategory" class="badge" style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.7rem; padding: 2px 6px;">-</span>
                    </div>
                </div>

                <div style="margin-top: 0.75rem; padding-top: 0.65rem; border-top: 1px solid #e2e8f0;">
                    <span style="color: #64748b; font-size: 0.75rem; display: block;">Alasan / Catatan Perubahan:</span>
                    <p id="revModalAlasan" style="margin: 0.2rem 0 0; font-size: 0.85rem; color: #334155; font-weight: 600; font-style: italic;">
                        -
                    </p>
                </div>
            </div>

            {{-- Tabel Komparasi Perubahan (Sebelum vs Sesudah) --}}
            <div>
                <strong style="color: #0f172a; font-size: 0.875rem; display: block; margin-bottom: 0.5rem;">
                    Daftar Field &amp; Komoditas yang Diubah:
                </strong>

                <div style="overflow-x: auto; border: 1.5px solid #e2e8f0; border-radius: 8px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                                <th style="padding: 0.65rem 0.85rem; text-align: left; color: #475569; width: 35%;">Parameter / Komoditas</th>
                                <th style="padding: 0.65rem 0.85rem; text-align: left; color: #dc2626; width: 32%; background: #fff5f5;">Nilai SEBELUM</th>
                                <th style="padding: 0.65rem 0.85rem; text-align: left; color: #166534; width: 33%; background: #f0fdf4;">Nilai SESUDAH</th>
                            </tr>
                        </thead>
                        <tbody id="revModalDiffsTbody">
                            {{-- Diisi secara dinamis via JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- 3. Modal Footer --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.75rem 1rem; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closePoRevisiModal()" style="padding: 0.4rem 1rem; font-size: 0.825rem;">
                Tutup Jendela
            </button>
        </div>

    </div>
</div>

