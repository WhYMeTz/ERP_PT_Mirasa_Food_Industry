@extends('layouts.app')

@section('title', 'Detail PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@section('content')
<div class="page-container">
    {{-- Top Navigation & Action Buttons --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <a href="{{ route('penjualan.so.index') }}" style="color: #0284c7; text-decoration: none; font-size: 0.825rem; font-weight: 600;">
                    ← Kembali ke Daftar PO Penjualan
                </a>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                    {{ $order->so_no }}
                </h1>
                {!! $order->status_badge !!}
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Cetak Faktur Penjualan PDF --}}
            <a href="{{ route('penjualan.so.export-faktur', $order->so_id) }}" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1rem; font-weight: 700; border-radius: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Cetak Faktur (PDF)</span>
            </a>

            {{-- Cetak Surat Jalan PDF --}}
            <a href="{{ route('penjualan.so.export-surat-jalan', $order->so_id) }}" target="_blank" class="btn btn-success" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1rem; font-weight: 700; border-radius: 6px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>Cetak Surat Jalan (PDF)</span>
            </a>

            {{-- Edit Button --}}
            @if(!in_array($order->status_cd, ['COMPLETED', 'CANCELLED']))
                <a href="{{ route('penjualan.so.edit', $order->so_id) }}" class="btn btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 0.85rem; font-weight: 600; border-radius: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                </a>

                {{-- Batalkan Button --}}
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('cancelModal').style.display='flex'" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 0.85rem; font-weight: 600; border-radius: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Batalkan</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Main Document Card --}}
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.5rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        {{-- Header Information Grid --}}
        <div style="padding: 1.25rem; background: #fafafa; border-bottom: 1px solid #e2e8f0; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
            <div>
                <div style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Customer Pemesan</div>
                <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $order->customer?->customer_nm }}</div>
                <div style="font-size: 0.8rem; color: #475569; margin-top: 2px;">
                    Kode: <strong>{{ $order->customer?->customer_cd }}</strong>
                </div>
                <div style="font-size: 0.775rem; color: #64748b; margin-top: 2px;">
                    {{ $order->customer?->alamat_txt ?: 'Alamat tidak terdata' }}
                </div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Nomor Referensi</div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 2px;">
                    No. Faktur: <strong style="color: #0284c7;">{{ $order->faktur_no ?: '-' }}</strong>
                </div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 2px;">
                    No. Surat Jalan: <strong style="color: #16a34a;">{{ $order->surat_jalan_no ?: '-' }}</strong>
                </div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 2px;">
                    No. PO Customer: <strong>{{ $order->customer_po_no ?: '-' }}</strong>
                </div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tanggal &amp; Pengiriman</div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 2px;">
                    Tgl Pesanan: <strong>{{ $order->so_tgl?->format('d/m/Y') }}</strong>
                </div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 2px;">
                    Estimasi Kirim: <strong>{{ $order->tgl_kirim_estimasi ? $order->tgl_kirim_estimasi->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="font-size: 0.85rem; color: #334155; margin-top: 2px;">
                    Dibuat Oleh: <strong>{{ $order->created_by ?: 'System' }}</strong>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div style="overflow-x: auto;">
            <table class="table" style="margin: 0; width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="background: #0f172a; color: #f8fafc; text-align: left;">
                        <th style="padding: 0.75rem 1rem; width: 40px; text-align: center;">#</th>
                        <th style="padding: 0.75rem 1rem;">Nama &amp; Kode Barang</th>
                        <th style="padding: 0.75rem 1rem; width: 120px;">Jenis</th>
                        <th style="padding: 0.75rem 1rem; width: 100px; text-align: right;">Jumlah</th>
                        <th style="padding: 0.75rem 1rem; width: 90px;">Satuan</th>
                        <th style="padding: 0.75rem 1rem; width: 130px; text-align: right;">@Harga Satuan</th>
                        <th style="padding: 0.75rem 1rem; width: 90px; text-align: right;">Diskon %</th>
                        <th style="padding: 0.75rem 1rem; width: 110px; text-align: right;">Potongan</th>
                        <th style="padding: 0.75rem 1rem; width: 90px; text-align: center;">PPN</th>
                        <th style="padding: 0.75rem 1rem; width: 140px; text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->details as $idx => $dtl)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-weight: 700;">
                                {{ $idx + 1 }}
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 700; color: #0f172a;">{{ $dtl->barang?->barang_nm }}</div>
                                <div style="font-family: monospace; font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                    {{ $dtl->barang?->barang_cd }}
                                </div>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: #475569;">
                                {{ $dtl->barang?->jenisBarang?->jenis_barang_nm ?? '-' }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                                {{ number_format($dtl->pesan_qty, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; color: #475569;">
                                {{ $dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit' }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #334155;">
                                Rp {{ number_format($dtl->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #dc2626;">
                                {{ $dtl->diskon_persen > 0 ? number_format($dtl->diskon_persen, 1) . '%' : '-' }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #dc2626;">
                                {{ $dtl->potongan_nominal > 0 ? 'Rp ' . number_format($dtl->potongan_nominal, 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center;">
                                @if($dtl->ppn_tipe === 'PPN_11')
                                    <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.675rem; font-weight: 700;">11%</span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.675rem;">0%</span>
                                @endif
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 800; color: #0f172a;">
                                Rp {{ number_format($dtl->subtotal_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bottom Summary & Notes --}}
        <div style="padding: 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; align-items: start;">
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.35rem;">
                    Catatan Pesanan:
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; font-size: 0.825rem; color: #334155; min-height: 70px; white-space: pre-line;">
                    {{ $order->catatan_txt ?: 'Tidak ada catatan khusus.' }}
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1.25rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; color: #475569;">
                        <span>Subtotal Bruto:</span>
                        <span style="font-weight: 700; color: #0f172a;">Rp {{ number_format($order->subtotal_bruto, 0, ',', '.') }}</span>
                    </div>

                    @if($order->diskon_total > 0)
                        <div style="display: flex; justify-content: space-between; color: #475569;">
                            <span>Total Diskon Item:</span>
                            <span style="font-weight: 700; color: #dc2626;">- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if($order->potongan_nominal > 0)
                        <div style="display: flex; justify-content: space-between; color: #475569;">
                            <span>Potongan Faktur:</span>
                            <span style="font-weight: 700; color: #dc2626;">- Rp {{ number_format($order->potongan_nominal, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; color: #475569; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem;">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <span style="font-weight: 700; color: #0f172a;">Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #475569;">
                        <span>PPN ({{ $order->ppn_tipe === 'PPN_11' ? '11%' : 'Non PPN' }}):</span>
                        <span style="font-weight: 700; color: #16a34a;">+ Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #0f172a; padding-top: 0.75rem; margin-top: 0.25rem;">
                        <span style="font-size: 1rem; font-weight: 800; color: #0f172a;">TOTAL TAGIHAN:</span>
                        <span style="font-size: 1.35rem; font-weight: 800; color: #0284c7;">
                            Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Pembatalan --}}
<div id="cancelModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
        <div style="padding: 1.25rem; background: #fef2f2; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 style="font-size: 1rem; font-weight: 800; color: #991b1b; margin: 0;">Konfirmasi Pembatalan PO Penjualan</h3>
                <p style="font-size: 0.775rem; color: #7f1d1d; margin: 0;">Pesanan {{ $order->so_no }} akan ditandai dibatalkan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('penjualan.so.cancel', $order->so_id) }}" style="padding: 1.25rem;">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                    Alasan Pembatalan <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="reason" required rows="3" class="form-control" placeholder="Contoh: Permintaan pembatalan dari customer karena kendala logistik toko..." style="font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('cancelModal').style.display='none'" style="font-weight: 600; padding: 0.55rem 1rem; border-radius: 6px;">
                    Tutup
                </button>
                <button type="submit" class="btn btn-danger" style="font-weight: 700; padding: 0.55rem 1.25rem; border-radius: 6px;">
                    Ya, Batalkan Pesanan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
