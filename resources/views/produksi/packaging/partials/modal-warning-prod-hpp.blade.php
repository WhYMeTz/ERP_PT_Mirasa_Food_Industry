{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  MODAL PERINGATAN HPP PRODUKSI BELUM DIHITUNG                   --}}
{{--  ERP PT Mirasa Food Industry - Standar Enterprise NetSuite        --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<div id="modalWarningProdHpp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 999999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.08); overflow: hidden; animation: popIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Header Modal Amber Warning --}}
        <div style="background: #fffbeb; border-bottom: 1px solid #fef3c7; padding: 1.15rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="color: #92400e; font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>HPP Produksi Asal Belum Dihitung</span>
            </div>
            <button type="button" onclick="closeWarningProdHppModal()" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #64748b; cursor: pointer; padding: 0.2rem;">
                &times;
            </button>
        </div>

        {{-- Body --}}
        <div style="padding: 1.35rem 1.5rem;">
            <div style="display: flex; gap: 0.85rem; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.25rem;">
                    ⏳
                </div>
                <div>
                    <h4 style="margin: 0 0 0.35rem 0; font-size: 0.925rem; font-weight: 700; color: #0f172a;">
                        Kalkulasi HPP Packing Terkunci
                    </h4>
                    <p style="font-size: 0.825rem; color: #475569; line-height: 1.5; margin: 0;">
                        Dokumen pengemasan <strong id="warningPackNoLabel" style="color: #0284c7; font-family: monospace;">-</strong> menggunakan keripik curah dari tiket produksi:
                    </p>
                    <div style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-weight: 700; font-size: 0.825rem; color: #b45309;">
                        SPK Asal: <span id="warningProdNoLabel">-</span>
                    </div>
                </div>
            </div>

            <div style="background: #fffdf5; border: 1.5px solid #fde68a; border-radius: 6px; padding: 0.75rem 0.95rem; font-size: 0.775rem; color: #92400e; line-height: 1.5;">
                ⚠️ <strong>Ketentuan Sistem ERP:</strong> HPP Packing adalah HPP Final (WIP + Kemasan + Biaya). HPP Packing belum dapat difinalkan sebelum Tim Keuangan menyelesaikan penetapan lembar HPP Produksi asal.
            </div>
        </div>

        {{-- Footer --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
            <button type="button" onclick="closeWarningProdHppModal()" class="btn btn-secondary" style="font-size: 0.825rem; font-weight: 700; padding: 0.45rem 1.15rem; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; border-radius: 6px; cursor: pointer;">
                Mengerti
            </button>
            <a href="{{ route('produksi.rekap') }}" class="btn btn-primary" style="font-size: 0.825rem; font-weight: 700; padding: 0.45rem 1.15rem; background: #0284c7; border-color: #0284c7; color: #ffffff; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                <span>Ke Rekap HPP Produksi &rarr;</span>
            </a>
        </div>
    </div>
</div>

