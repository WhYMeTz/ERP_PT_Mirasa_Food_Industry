{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL KONFIRMASI PENGIRIMAN & PEMOTONGAN STOK BARANG JADI (FEFO)          --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalConfirmDelivery" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e2e8f0; animation: modalPop 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="background: #f0fdf4; border-bottom: 1.5px solid #bbf7d0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #14532d; margin: 0;">
                        Konfirmasi Pengiriman &amp; Potong Stok
                    </h3>
                    <span style="font-size: 0.75rem; color: #166534;">
                        Dokumen: <strong id="modalDeliverySoNo">-</strong> &bull; <span id="modalDeliveryCustomer">-</span>
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeConfirmDeliveryModal()" style="border: none; background: transparent; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        {{-- FORM KONFIRMASI --}}
        <form id="formConfirmDelivery" method="POST" action="">
            @csrf

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                {{-- Banner Penjelasan Operasional --}}
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 0.75rem 0.95rem; font-size: 0.8rem; color: #1e40af; line-height: 1.4; display: flex; gap: 0.5rem; align-items: flex-start;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        Dengan mengonfirmasi pengiriman, sistem akan <strong>otomatis memotong stok fisik</strong> produk jadi di <strong>Gudang Barang Jadi (GDG-FG)</strong> secara <strong>FEFO</strong> (First Expired First Out) serta mencatat mutasi keluar resmi pada Kartu Stok.
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Tanggal Pengiriman / Surat Jalan Diberangkatkan <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="tgl_kirim_aktual" id="deliveryTglInput" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Nama Supir / Pengemudi
                        </label>
                        <input type="text" name="supir_nm" id="deliverySupirInput" value="{{ $order->supir_nm ?? '' }}" class="form-control" placeholder="Contoh: Budi Santoso" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Nomor Polisi Armada / Truk
                        </label>
                        <input type="text" name="nopol_kendaraan" id="deliveryNopolInput" value="{{ $order->nopol_kendaraan ?? '' }}" class="form-control" placeholder="Contoh: N 8123 AB" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem; text-transform: uppercase;">
                    </div>
                </div>

                @if(isset($order) && $order->details && $order->details->count() > 0)
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Rincian Barang yang Dikirim
                    </label>
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; max-height: 180px; overflow-y: auto;">
                        <table style="width: 100%; font-size: 0.785rem; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                                    <th style="padding: 6px 10px; text-align: left;">Produk</th>
                                    <th style="padding: 6px 10px; text-align: right; width: 85px;">Sisa Pesan</th>
                                    <th style="padding: 6px 10px; text-align: right; width: 100px;">Kirim Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->details as $idx => $dtl)
                                    @php $sisa = max(0, (float)$dtl->pesan_qty - (float)$dtl->kirim_qty); @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 6px 10px; font-weight: 600; color: #0f172a;">
                                            {{ $dtl->barang?->barang_nm ?? '-' }}
                                            <input type="hidden" name="items[{{ $idx }}][sodtl_id]" value="{{ $dtl->sodtl_id }}">
                                        </td>
                                        <td style="padding: 6px 10px; text-align: right; color: #64748b;">
                                            {{ number_format($sisa, 0, ',', '.') }} {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton' }}
                                        </td>
                                        <td style="padding: 4px 10px; text-align: right;">
                                            <input type="number" step="any" min="0" max="{{ $sisa }}" name="items[{{ $idx }}][kirim_qty]" value="{{ $sisa }}" class="form-control" style="padding: 3px 6px; font-size: 0.8rem; text-align: right; font-weight: 700; width: 85px; display: inline-block;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <span style="color: #059669; font-size: 0.725rem; margin-top: 3px; display: block; font-weight: 600;">
                        ✓ Sistem otomatis memprioritaskan batch tertua &amp; mencatat nomor batch fisik riil (FEFO).
                    </span>
                </div>
                @endif
            </div>

            {{-- FOOTER MODAL --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmDeliveryModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem; font-weight: 600;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669; font-size: 0.825rem; padding: 0.45rem 1.15rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Konfirmasi &amp; Terbitkan Surat Jalan (FEFO)</span>
                </button>
            </div>
        </form>
    </div>
</div>
