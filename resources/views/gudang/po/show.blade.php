@extends('layouts.app')

@section('title', 'Detail Purchase Order ' . $po->po_no . ' - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.po.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar PO
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $po->po_no }}</h1>
            @if ($po->status_cd == 'COMPLETED')
                <span class="badge badge-success">Selesai</span>
            @elseif ($po->status_cd == 'PARTIAL')
                <span class="badge badge-info">Sebagian Diterima ({{ $po->persentase_terima }}%)</span>
            @elseif ($po->status_cd == 'CLOSED')
                <span class="badge" style="background:#f1f5f9; color:#475569; border: 1px solid #cbd5e1;">Ditutup ({{ $po->persentase_terima }}%)</span>
            @elseif ($po->status_cd == 'APPROVED')
                <span class="badge" style="background:#e0f2fe; color:#0369a1;">Disetujui</span>
            @elseif ($po->status_cd == 'DRAFT')
                <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
            @else
                <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']))
            <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" class="btn btn-primary" style="background:#059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terima Barang
            </a>
        @endif

        @if ($po->status_cd == 'PARTIAL')
            <button type="button" onclick="openForceCloseModal()" class="btn btn-secondary" style="border: 1px solid #cbd5e1;">
                Tutup PO
            </button>
        @endif

        @if (in_array($po->status_cd, ['DRAFT', 'APPROVED']) && $po->details->sum('terima_qty') == 0)
            <form action="{{ route('gudang.po.cancel', $po->po_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?')">
                @csrf
                <button type="submit" class="btn btn-danger">Batalkan PO</button>
            </form>
        @endif
    </div>
</div>

@if ($po->status_cd == 'CLOSED')
    <div class="alert" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; margin-bottom: 1.5rem; display: block;">
        <strong>Informasi Penutupan PO:</strong> Dokumen PO ini telah ditutup pada {{ $po->closed_at ? \Carbon\Carbon::parse($po->closed_at)->format('d/m/Y H:i') : '-' }} oleh {{ $po->closed_by ?? '-' }}.
        @if ($po->closed_reason)
            <div style="margin-top: 0.25rem; color: #64748b; font-size: 0.85rem;">
                <strong>Alasan:</strong> {{ $po->closed_reason }}
            </div>
        @endif
    </div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    {{-- KARTU INFO SUPPLIER & GUDANG --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Informasi Pengadaan & Mitra</strong>
        </div>
        <div style="padding: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Supplier Mitra:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $po->supplier?->supplier_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $po->supplier?->alamat_txt ?? '-' }}<br>
                    Telp: {{ $po->supplier?->kontak_no ?? '-' }}
                </p>
            </div>
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Gudang Tujuan Masuk:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $po->gudang?->gudang_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    Kode: {{ $po->gudang?->gudang_cd ?? '-' }}<br>
                    Lokasi: {{ $po->gudang?->alamat_txt ?? '-' }}
                </p>
            </div>
            @if ($po->catatan_txt)
                <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.8rem; display: block;">Catatan:</span>
                    <p style="color: #334155; font-size: 0.85rem; margin-top: 0.25rem; white-space: pre-line;">{{ $po->catatan_txt }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU RINGKASAN TOTAL & PROGRES --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Ringkasan Biaya & Jadwal</strong>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Tanggal PO:</span>
                <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($po->po_tgl)->format('d F Y') }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Estimasi Tiba:</span>
                @if ($po->tgl_estimasi_datang)
                    <strong style="color: #0284c7;">{{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d F Y') }}</strong>
                @else
                    <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                @endif
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.65rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Status Penerimaan:</span>
                <strong style="color: #0f172a;">{{ $po->persentase_terima }}%</strong>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; padding-top: 0.65rem; border-top: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.875rem;">Total Nominal PO:</span>
                <strong style="color: #0f172a; font-size: 1rem;">Rp {{ number_format((float) $po->total_nominal, 0, ',', '.') }}</strong>
            </div>

            @if (in_array($po->status_cd, ['PARTIAL', 'CLOSED']))
                <div style="display: flex; justify-content: space-between; padding-top: 0.5rem; border-top: 1px dashed #e2e8f0;">
                    <span style="color: #475569; font-size: 0.875rem;">Realisasi Diterima:</span>
                    <strong style="color: #059669; font-size: 1rem;">Rp {{ number_format($po->realisasi_nominal, 0, ',', '.') }}</strong>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- TABEL DETAIL BARANG --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
        <strong style="color: #0f172a;">Rincian Barang yang Dipesan</strong>
        <span style="font-size: 0.8rem; color: #64748b;">{{ $po->details->count() }} Item</span>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th style="text-align: right;">Pesan</th>
                    <th style="text-align: right;">Diterima</th>
                    <th style="text-align: right;">Sisa</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->details as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">{{ $item->barang?->barang_cd }}</span>
                        </td>
                        <td>{{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}</td>
                        <td style="text-align: right; font-weight: 600;">
                            {{ number_format((float) $item->pesan_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #059669;">
                            {{ number_format((float) $item->terima_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: {{ (float) $item->sisa_qty > 0 ? '#b45309' : '#64748b' }};">
                            {{ number_format((float) $item->sisa_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            Rp {{ number_format((float) $item->harga_nominal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #0f172a;">
                            Rp {{ number_format((float) $item->subtotal_nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- RIWAYAT PENERIMAAN FISIK DENGAN PO INI --}}
@if ($po->penerimaan->isNotEmpty())
    <div class="card">
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <strong style="color: #0f172a;">Riwayat Penerimaan Barang (Good Receipts)</strong>
            <span style="color: #64748b; font-size: 0.8rem;">{{ $po->penerimaan->count() }} Penerimaan</span>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>No Penerimaan</th>
                        <th>Tanggal Terima</th>
                        <th>Keterangan Jadwal</th>
                        <th>No Surat Jalan</th>
                        <th>Gudang</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($po->penerimaan as $terima)
                        @php
                            $tglTerima = \Carbon\Carbon::parse($terima->terima_tgl);
                            $eta = $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang) : null;
                        @endphp
                        <tr>
                            <td>
                                <strong style="color: #0284c7;">{{ $terima->terima_no }}</strong>
                            </td>
                            <td>{{ $tglTerima->format('d/m/Y') }}</td>
                            <td>
                                @if ($eta)
                                    @if ($tglTerima->lt($eta))
                                        <span class="badge" style="background:#f0fdf4; color:#166534; font-size:0.75rem;">
                                            Lebih Cepat
                                        </span>
                                    @elseif ($tglTerima->equalTo($eta))
                                        <span class="badge" style="background:#f0f9ff; color:#0369a1; font-size:0.75rem;">
                                            Tepat Waktu
                                        </span>
                                    @else
                                        <span class="badge" style="background:#fef2f2; color:#991b1b; font-size:0.75rem;">
                                            Terlambat
                                        </span>
                                    @endif
                                @else
                                    <span style="color:#94a3b8; font-size:0.8rem;">-</span>
                                @endif
                            </td>
                            <td>{{ $terima->suratjalan_no ?? '-' }}</td>
                            <td>{{ $terima->gudang?->gudang_nm }}</td>
                            <td><span class="badge badge-success">Diterima</span></td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.terima.show', $terima->terima_id) }}" class="btn btn-secondary btn-sm">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- MODAL KONFIRMASI TUTUP PO --}}
<div id="modalForceClose" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 480px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <strong style="color: #0f172a; font-size: 1rem;">Tutup PO (Selesai Parsial)</strong>
            <button type="button" onclick="closeForceCloseModal()" style="background: transparent; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('gudang.po.force_close', $po->po_id) }}" method="POST" style="padding: 1.25rem;">
            @csrf
            <p style="color: #475569; font-size: 0.875rem; margin-top: 0; margin-bottom: 0.75rem; line-height: 1.4;">
                Gunakan fungsi ini jika sisa barang tidak akan dikirim lagi oleh supplier. Status PO akan diubah menjadi <strong>Ditutup</strong> dan sisa kuota ({{ number_format($po->total_sisa_qty, 2) }}) dibatalkan.
            </p>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="closed_reason" class="form-label" style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                    Alasan Penutupan <span style="color:#ef4444;">*</span>
                </label>
                <textarea id="closed_reason" name="closed_reason" rows="3" class="form-control" placeholder="Tuliskan alasan penutupan PO..." required minlength="5"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeForceCloseModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">
                    Simpan & Tutup PO
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openForceCloseModal() {
        document.getElementById('modalForceClose').style.display = 'flex';
    }
    function closeForceCloseModal() {
        document.getElementById('modalForceClose').style.display = 'none';
    }
</script>
@endsection
