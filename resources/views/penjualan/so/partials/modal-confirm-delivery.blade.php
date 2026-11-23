{{-- MODAL KONFIRMASI PENGIRIMAN (SURAT JALAN) --}}
@php
    $hasInsufficientStock = false;
    if (isset($order) && $order->details) {
        foreach ($order->details as $d) {
            $s = max(0, (float)$d->pesan_qty - (float)$d->kirim_qty);
            $stk = (float)($stockMap[$d->barang_id] ?? 0);
            if ($s > $stk) {
                $hasInsufficientStock = true;
                break;
            }
        }
    }
@endphp

<div id="modalConfirmDelivery" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 6px; width: 100%; max-width: 560px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #cbd5e1; overflow: hidden;">
        
        {{-- Header Bersih & Netral Tanpa Icon AI --}}
        <div style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Konfirmasi Pengiriman (Surat Jalan)
                </h3>
                <span style="font-size: 0.775rem; color: #64748b;">
                    No. PO: <strong id="modalDeliverySoNo" style="font-family: monospace; color: #334155;">-</strong> &bull; <span id="modalDeliveryCustomer">-</span>
                </span>
            </div>
            <button type="button" onclick="closeConfirmDeliveryModal()" style="border: none; background: transparent; font-size: 1.25rem; color: #64748b; cursor: pointer; line-height: 1; padding: 0 0.25rem;">&times;</button>
        </div>

        <form id="formConfirmDelivery" method="POST" action="">
            @csrf

            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">

                @if ($hasInsufficientStock)
                    <div style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 0.65rem 0.85rem; font-size: 0.8rem; color: #991b1b; line-height: 1.4;">
                        <strong>Peringatan:</strong> Stok barang di gudang tidak mencukupi untuk kuantitas pesanan ini. Pengiriman belum dapat diproses sebelum barang selesai dikemas di bagian produksi.
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.775rem; font-weight: 600; color: #334155; margin-bottom: 0.25rem;">
                            Tanggal Surat Jalan <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" name="tgl_kirim_aktual" id="deliveryTglInput" class="form-control" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0.4rem 0.6rem; font-size: 0.8125rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.775rem; font-weight: 600; color: #334155; margin-bottom: 0.25rem;">
                            No. Polisi Armada
                        </label>
                        <input type="text" name="nopol_kendaraan" id="deliveryNopolInput" value="{{ $order->nopol_kendaraan ?? '' }}" class="form-control" placeholder="AA 9526 BK" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0.4rem 0.6rem; font-size: 0.8125rem; text-transform: uppercase;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 600; color: #334155; margin-bottom: 0.25rem;">
                        Nama Supir / Pengemudi
                    </label>
                    <input type="text" name="supir_nm" id="deliverySupirInput" value="{{ $order->supir_nm ?? '' }}" class="form-control" placeholder="Nama supir pengantar" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0.4rem 0.6rem; font-size: 0.8125rem;">
                </div>

                @if(isset($order) && $order->details && $order->details->count() > 0)
                <div>
                    <label style="display: block; font-size: 0.775rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                        Rincian Barang &amp; Posisi Stok
                    </label>
                    <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <table style="width: 100%; font-size: 0.8rem; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; color: #334155;">
                                    <th style="padding: 6px 10px; text-align: left; font-weight: 600;">Nama Barang</th>
                                    <th style="padding: 6px 10px; text-align: right; width: 75px; font-weight: 600;">Pesan</th>
                                    <th style="padding: 6px 10px; text-align: right; width: 110px; font-weight: 600;">Stok Gudang</th>
                                    <th style="padding: 6px 10px; text-align: right; width: 85px; font-weight: 600;">Qty Kirim</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->details as $idx => $dtl)
                                    @php 
                                        $sisa = max(0, (float)$dtl->pesan_qty - (float)$dtl->kirim_qty);
                                        $stok = (float)($stockMap[$dtl->barang_id] ?? 0);
                                        $isDeficit = ($sisa > $stok);
                                    @endphp
                                    <tr style="border-bottom: 1px solid #e2e8f0; background: {{ $isDeficit ? '#fef2f2' : '#ffffff' }};">
                                        <td style="padding: 6px 10px; color: #0f172a;">
                                            {{ $dtl->barang?->barang_nm ?? '-' }}
                                            <input type="hidden" name="items[{{ $idx }}][sodtl_id]" value="{{ $dtl->sodtl_id }}">
                                        </td>
                                        <td style="padding: 6px 10px; text-align: right; color: #475569; font-family: monospace;">
                                            {{ number_format($sisa, 0, ',', '.') }}
                                        </td>
                                        <td style="padding: 6px 10px; text-align: right; font-family: monospace;">
                                            @if($isDeficit)
                                                <span style="color: #dc2626; font-weight: 700;">
                                                    {{ number_format($stok, 0) }} (Kurang {{ number_format($sisa - $stok, 0) }})
                                                </span>
                                            @else
                                                <span style="color: #15803d; font-weight: 600;">
                                                    {{ number_format($stok, 0) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 4px 10px; text-align: right;">
                                            <input type="number" step="any" min="0" max="{{ $sisa }}" name="items[{{ $idx }}][kirim_qty]" value="{{ $sisa }}" class="form-control" style="padding: 2px 6px; font-size: 0.8rem; text-align: right; font-weight: 600; font-family: monospace; width: 75px; border: 1px solid {{ $isDeficit ? '#fca5a5' : '#cbd5e1' }}; border-radius: 4px; display: inline-block;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer Aksi Sederhana --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem;">
                <button type="button" onclick="closeConfirmDeliveryModal()" style="font-size: 0.8125rem; padding: 0.4rem 0.9rem; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; border-radius: 4px; cursor: pointer;">
                    Tutup
                </button>

                @if ($hasInsufficientStock)
                    <button type="button" disabled style="background: #e2e8f0; border: 1px solid #cbd5e1; color: #94a3b8; font-size: 0.8125rem; padding: 0.4rem 1rem; font-weight: 600; cursor: not-allowed; border-radius: 4px;">
                        Stok Tidak Mencukupi
                    </button>
                @else
                    <button type="submit" style="background: #059669; border: 1px solid #047857; font-size: 0.8125rem; padding: 0.4rem 1.15rem; font-weight: 700; color: #ffffff; border-radius: 4px; cursor: pointer;">
                        Proses Pengiriman (Surat Jalan)
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
