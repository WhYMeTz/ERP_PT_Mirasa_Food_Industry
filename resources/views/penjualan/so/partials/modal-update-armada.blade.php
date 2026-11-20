{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL UBAH ARMADA & SUPIR PENGIRIMAN (SALES / PO)                           --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalUpdateArmada" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 460px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e2e8f0;">
        
        <div style="background: #f0fdf4; border-bottom: 1.5px solid #bbf7d0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #166534; margin: 0;">
                        Atur Armada &amp; Pengemudi
                    </h3>
                    <span style="font-size: 0.75rem; color: #15803d;">
                        PO: <strong>{{ $order->so_no }}</strong>
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeModalArmada()" style="border: none; background: transparent; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form method="POST" action="{{ route('penjualan.so.update-armada', $order->so_id) }}">
            @csrf

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Nama Supir / Pengemudi
                    </label>
                    <input type="text" name="supir_nm" value="{{ old('supir_nm', $order->supir_nm) }}" placeholder="Contoh: Pak Slamet / Budi Santoso..." class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
                    <small style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem; display: block;">Otomatis diteruskan ke dokumen Surat Jalan Gudang.</small>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        No. Polisi Armada Truk
                    </label>
                    <input type="text" name="nopol_kendaraan" value="{{ old('nopol_kendaraan', $order->nopol_kendaraan) }}" placeholder="Contoh: AD 8234 KL" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem; text-transform: uppercase; font-family: monospace;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        No. Surat Jalan
                    </label>
                    <input type="text" name="surat_jalan_no" value="{{ old('surat_jalan_no', $order->surat_jalan_no) }}" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem; font-family: monospace;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Estimasi Pengiriman
                    </label>
                    <input type="date" name="tgl_kirim_estimasi" value="{{ old('tgl_kirim_estimasi', $order->tgl_kirim_estimasi?->format('Y-m-d')) }}" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModalArmada()" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669; font-size: 0.825rem; padding: 0.45rem 1.15rem; font-weight: 700;">
                    Simpan Perubahan Armada
                </button>
            </div>
        </form>
    </div>
</div>
