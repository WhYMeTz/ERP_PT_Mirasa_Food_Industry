{{-- MODAL KONFIRMASI SELISIH KUANTITAS PO (OVER PO & PENERIMAAN PARSIAL) --}}
<div id="modalConfirmPoDiscrepancyTerima" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:580px; overflow:hidden; border: 1.5px solid #cbd5e1; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Modal --}}
        <div style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div id="poDiscrepancyIconWrap" style="width:34px; height:34px; border-radius:50%; background:#0f172a; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#0f172a;" id="poDiscrepancyModalTitle">Konfirmasi Penyesuaian Kuantitas PO</h3>
                    <div style="font-size:0.75rem; color:#64748b; margin-top:2px;" id="poDiscrepancyModalSubtitle">Terdeteksi selisih kuantitas penerimaan fisik terhadap pesanan Purchase Order</div>
                </div>
            </div>
            <button type="button" onclick="closeConfirmPoDiscrepancyModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.25rem;">
            <p style="font-size:0.875rem; color:#334155; margin-top:0; margin-bottom:0.75rem; line-height:1.5;" id="poDiscrepancyIntroText">
                Terdapat komoditas yang kuantitas terimanya <strong>berbeda dari kuota pesanan Purchase Order</strong>:
            </p>

            {{-- Container Tabel Barang --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; margin-bottom:1rem; max-height:220px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;">
                    <thead style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;">
                        <tr>
                            <th style="padding:0.5rem 0.75rem;">Nama Barang</th>
                            <th style="padding:0.5rem 0.75rem; text-align:right;">Pesanan PO</th>
                            <th style="padding:0.5rem 0.75rem; text-align:right;">Diterima (Netto)</th>
                            <th style="padding:0.5rem 0.75rem; text-align:right;">Status Selisih</th>
                        </tr>
                    </thead>
                    <tbody id="poDiscrepancyItemsListBody">
                        {{-- Diisi secara dinamis oleh JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Info Box Operasional & Keuangan --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #0284c7; border-radius:6px; padding:0.75rem 0.85rem; margin-bottom:1.25rem; font-size:0.78rem; color:#334155; line-height:1.45;">
                <div style="display:flex; gap:0.5rem; align-items:flex-start;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:2px; color:#0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <strong>Dampak Operasional &amp; Stok:</strong> Seluruh kuantitas fisik yang disimpan akan <strong>langsung dicatat 100% akurat</strong> ke kartu stok gudang (FIFO). Dokumen ini akan diberi penanda status penerimaan (<em>Over-Delivery</em> atau <em>Parsial</em>) sebagai dasar rekonsiliasi faktur tagihan bersama Departemen Keuangan.
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:flex-end; gap:0.6rem; align-items:center;">
                <button type="button" onclick="closeConfirmPoDiscrepancyModal()" class="btn-pill-outline" style="font-size:0.8125rem;">
                    Periksa Kembali
                </button>
                <button type="button" onclick="proceedSubmitPoDiscrepancy()" class="btn-pill-dark" id="btnProceedPoDiscrepancy" style="font-size:0.8125rem;">
                    Lanjutkan Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
