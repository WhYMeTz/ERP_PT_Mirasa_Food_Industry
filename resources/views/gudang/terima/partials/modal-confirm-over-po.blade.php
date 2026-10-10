{{-- MODAL KONFIRMASI PENERIMAAN OVER PO (KUANTITAS MELEBIHI SISA PESANAN PO) --}}
<div id="modalConfirmOverPoTerima" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:540px; overflow:hidden; border: 1.5px solid #fde68a; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Modal Amber Warning --}}
        <div style="background:#fffbeb; border-bottom:1px solid #fde68a; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:34px; height:34px; border-radius:50%; background:#f59e0b; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(245,158,11,0.3);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#92400e;">Konfirmasi Penerimaan Over PO</h3>
                    <div style="font-size:0.75rem; color:#b45309; margin-top:2px;">Kuantitas Fisik Melebihi Sisa Pesanan Purchase Order</div>
                </div>
            </div>
            <button type="button" onclick="closeConfirmOverPoModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.25rem;">
            <p style="font-size:0.875rem; color:#334155; margin-top:0; line-height:1.5;">
                Terdapat barang yang kuantitas terimanya <strong>melebihi sisa kuota pesanan Purchase Order</strong>:
            </p>

            {{-- Container Tabel Barang Over --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; margin-bottom:1rem; max-height:220px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;">
                    <thead style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;">
                        <tr>
                            <th style="padding:0.5rem 0.75rem;">Nama Barang</th>
                            <th style="padding:0.5rem 0.75rem; text-align:right;">Sisa PO</th>
                            <th style="padding:0.5rem 0.75rem; text-align:right;">Diterima</th>
                            <th style="padding:0.5rem 0.75rem; text-align:right; color:#b45309;">Selisih Lebih</th>
                        </tr>
                    </thead>
                    <tbody id="overPoItemsListBody">
                        {{-- Diisi secara dinamis oleh JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Info Box Operasional & Keuangan --}}
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:0.75rem 0.85rem; margin-bottom:1.25rem; font-size:0.78rem; color:#92400e; line-height:1.45;">
                <div style="display:flex; gap:0.5rem; align-items:flex-start;">
                    <span style="font-size:1rem; line-height:1;">💡</span>
                    <div>
                        <strong>Catatan Sistem:</strong> Seluruh stok fisik yang diinput akan <strong>tetap dicatat 100% akurat</strong> ke kartu stok gudang. Dokumen penerimaan ini akan diberi penanda <em>Over-Delivery</em> untuk rekonsiliasi faktur tagihan bersama bagian Keuangan &amp; Purchasing.
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:flex-end; gap:0.6rem; align-items:center;">
                <button type="button" onclick="closeConfirmOverPoModal()" class="btn btn-secondary" style="font-size:0.85rem; padding:0.5rem 1rem; border-radius:6px; font-weight:600;">
                    Periksa Kembali
                </button>
                <button type="button" onclick="proceedSubmitOverPo()" class="btn btn-warning" style="background:#f59e0b; border-color:#d97706; color:#ffffff; font-size:0.85rem; font-weight:700; padding:0.5rem 1.15rem; border-radius:6px; box-shadow:0 2px 4px rgba(245,158,11,0.25);">
                    ✓ Lanjutkan Terima Over PO
                </button>
            </div>
        </div>
    </div>
</div>
