@extends('layouts.app')

@section('title', 'Kartu Stok Barang (Stock Ledger) - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Kartu Stok Barang (Audit Ledger)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Histori audit trail mutasi kuantitas masuk (IN) dan keluar (OUT) per transaksi fisik dan nomor batch.</p>
    </div>
    <div>
        <a href="{{ route('gudang.stok.index') }}" class="btn btn-secondary">
            &larr; Monitoring Stok Batch
        </a>
    </div>
</div>

<!-- Filter Selector -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('gudang.stok.ledger') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Pilih Barang</label>
                <select name="barang_id" class="form-control" onchange="this.form.submit()" required>
                    @foreach ($barangList as $b)
                        <option value="{{ $b->barang_id }}" {{ ($selectedBarang && $selectedBarang->barang_id == $b->barang_id) ? 'selected' : '' }}>
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Lokasi Gudang</label>
                <select name="gudang_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Lokasi Gudang --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="form-control">
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="form-control">
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Tampilkan</button>
                @if(!empty($startDate) || !empty($endDate) || !empty($gudangId))
                    <a href="{{ route('gudang.stok.ledger', ['barang_id' => $selectedBarang?->barang_id]) }}" class="btn btn-secondary" title="Reset Filter Tanggal">&times;</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Summary Info -->
    @if ($selectedBarang)
        <div style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; background: #ffffff;">
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b;">Informasi Barang Aktif</span>
                <div style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ $selectedBarang->barang_nm }}
                    <span style="font-size: 0.875rem; font-weight: 600; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.5rem;">
                        {{ $selectedBarang->barang_cd }}
                    </span>
                </div>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">
                    Kategori: <strong>{{ $selectedBarang->jenisBarang?->jenis_barang_nm ?? '-' }}</strong> | 
                    Satuan Dasar: <strong>{{ $selectedBarang->satuanDasar?->satuan_nm ?? '-' }} ({{ $selectedBarang->satuanDasar?->satuan_cd ?? '-' }})</strong>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.5rem; text-align: right;">
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b;">Total Saldo Fisik Gudang</span>
                <div style="font-size: 1.75rem; font-weight: 800; color: #059669; line-height: 1.2; margin-top: 0.25rem;">
                    {{ number_format((float) $totalStok, 2, ',', '.') }}
                    <span style="font-size: 1rem; font-weight: 600; color: #475569;">{{ $selectedBarang->satuanDasar?->satuan_cd ?? '' }}</span>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Ledger Data Table -->
<div class="card">
    <div class="card-header">
        <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">Mutasi & Kartu Stok Fisik</h3>
        <span style="color: #64748b; font-size: 0.875rem;">Total Mutasi: <strong>{{ $ledgerList ? $ledgerList->total() : 0 }}</strong> Transaksi</span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Tanggal</th>
                    <th>No. Dokumen Ref</th>
                    <th>Tipe Transaksi</th>
                    <th>Nomor Batch</th>
                    <th>Lokasi Gudang</th>
                    <th style="text-align: right; color: #059669;">Masuk (+)</th>
                    <th style="text-align: right; color: #dc2626;">Keluar (-)</th>
                    <th style="text-align: right;">Saldo Berjalan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @if ($ledgerList)
                    @forelse ($ledgerList as $index => $row)
                        <tr>
                            <td>{{ $ledgerList->firstItem() + $index }}</td>
                            <td style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($row->transaksi_tgl)->format('d/m/Y') }}
                                <span style="display: block; font-size: 0.75rem; color: #94a3b8;">
                                    {{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}
                                </span>
                            </td>
                            <td>
                                @if (str_starts_with($row->dokumen_no, 'GR-'))
                                    <a href="{{ route('gudang.terima.index', ['search' => $row->dokumen_no]) }}" style="font-weight: 700; color: #0284c7; text-decoration: underline;">
                                        {{ $row->dokumen_no }}
                                    </a>
                                @else
                                    <strong style="color: #0f172a;">{{ $row->dokumen_no }}</strong>
                                @endif
                            </td>
                            <td>
                                @if ($row->tipe_transaksi_cd === 'IN')
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700;">
                                        MASUK (IN)
                                    </span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700;">
                                        KELUAR (OUT)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.8125rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600; color: #334155;">
                                    {{ $row->batch_no }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: #0f172a;">{{ $row->gudang?->gudang_nm }}</strong>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #059669;">
                                @if ($row->tipe_transaksi_cd === 'IN')
                                    +{{ number_format((float) $row->qty, 2, ',', '.') }}
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #dc2626;">
                                @if ($row->tipe_transaksi_cd === 'OUT')
                                    -{{ number_format((float) $row->qty, 2, ',', '.') }}
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 0.9375rem; color: #0f172a;">
                                {{ number_format((float) $row->saldoakhir_qty, 2, ',', '.') }}
                            </td>
                            <td style="color: #64748b; font-size: 0.8125rem;">
                                {{ $row->keterangan_txt ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat mutasi stok untuk barang dan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                @else
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Silakan pilih barang terlebih dahulu untuk melihat kartu stok.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if ($ledgerList && $ledgerList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $ledgerList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
