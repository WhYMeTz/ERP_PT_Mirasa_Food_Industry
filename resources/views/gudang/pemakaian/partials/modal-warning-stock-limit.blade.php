{{-- MODAL PERINGATAN KUANTITAS MELEBIHI SISA STOK BATCH (STOCK LIMIT EXCEEDED) --}}
<div id="modalWarningStockLimit" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:660px; overflow:hidden; border: 1.5px solid #fca5a5; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Modal Red Warning --}}
        <div style="background:#fef2f2; border-bottom:1px solid #fca5a5; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:36px; height:36px; border-radius:50%; background:#dc2626; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(220,38,38,0.3);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#991b1b;">Kuantitas Melebihi Saldo Fisik Batch!</h3>
                    <div style="font-size:0.75rem; color:#b91c1c; margin-top:2px;">Dokumen tidak dapat disimpan karena kuantitas melebihi stok fisik yang tersedia.</div>
                </div>
            </div>
            <button type="button" onclick="closeWarningStockLimitModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.25rem;">
            <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:0.75rem; line-height:1.5;">
                Sistem mendeteksi <strong>kuantitas pengeluaran melebihi sisa fisik</strong> pada batch berikut. Anda tidak dapat mengeluarkan barang lebih dari saldo fisik batch tersebut:
            </p>

            {{-- Container Tabel Batch yang Melanggar --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; margin-bottom:1rem; max-height:250px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;">
                    <thead style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #cbd5e1;">
                        <tr>
                            <th style="padding:0.5rem 0.65rem;">Nama Bahan &amp; Baris</th>
                            <th style="padding:0.5rem 0.65rem; color:#b45309;">Batch Dipilih</th>
                            <th style="padding:0.5rem 0.65rem; text-align:right;">Diminta</th>
                            <th style="padding:0.5rem 0.65rem; text-align:right; color:#065f46;">Sisa Batch</th>
                            <th style="padding:0.5rem 0.65rem; text-align:center;">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody id="stockLimitWarningListBody">
                        {{-- Diisi secara dinamis oleh JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Solusi Otomatis --}}
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:0.75rem 0.85rem; margin-bottom:1.25rem; font-size:0.775rem; color:#166534; line-height:1.45;">
                <div style="display:flex; gap:0.5rem; align-items:flex-start;">
                    <span style="font-size:1.1rem; line-height:1;">💡</span>
                    <div>
                        <strong>Solusi Cerdas Auto-Split:</strong> Klik tombol <strong>"Pecah Otomatis Semua Baris"</strong> di bawah agar sistem otomatis mengambil sisa maksimal dari batch saat ini, lalu membuat baris baru untuk sisa kuantitas dari batch FIFO berikutnya yang tersedia di gudang!
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:space-between; gap:0.6rem; align-items:center; flex-wrap:wrap;">
                <button type="button" onclick="applyAutoSplitAllViolations()" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#ffffff; font-size:0.85rem; font-weight:700; padding:0.55rem 1rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>⚡ Pecah Otomatis Semua Baris &amp; Lanjutkan</span>
                </button>

                <button type="button" onclick="closeWarningStockLimitModal()" class="btn btn-secondary" style="font-size:0.825rem; padding:0.55rem 0.85rem; border-radius:6px; font-weight:600; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">
                    Tutup &amp; Koreksi Manual
                </button>
            </div>
        </div>
    </div>
</div>
