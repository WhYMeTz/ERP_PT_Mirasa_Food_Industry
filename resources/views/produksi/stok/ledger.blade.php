@extends('layouts.app')

@section('title', 'Kartu Stok Hasil Produksi (WIP & FG) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/stok/batch-detail-modal.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <span>Kartu Stok Hasil Produksi (Audit Ledger)</span>
            <span class="badge" style="background: #ea580c18; color: #ea580c; border: 1px solid #ea580c33; font-size: 0.75rem; font-weight: 700;">WIP &amp; Finish Good</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Histori audit trail mutasi masuk (hasil masak/goreng) dan keluar (proses kemas / kirim penjualan) untuk olahan setengah jadi (WIP) dan barang jadi (FG).
        </p>
    </div>
    <div>
        <a href="{{ route('produksi.stok.index') }}" class="btn btn-secondary">
            &larr; Lacak Stok WIP &amp; FG
        </a>
    </div>
</div>

<!-- Filter Selector -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('produksi.stok.ledger') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Pilih Hasil Olahan (WIP) / Barang Jadi (FG)</label>
                <select name="barang_id" class="form-control" onchange="this.form.submit()" required>
                    @foreach ($barangList as $b)
                        <option value="{{ $b->barang_id }}" {{ ($selectedBarang && $selectedBarang->barang_id == $b->barang_id) ? 'selected' : '' }}>
                            [{{ $b->jenisBarang?->jenis_barang_cd ?? 'PROD' }}] {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Perusahaan / Lokasi Gudang</label>
                <select name="gudang_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Lokasi Pabrik / Gudang --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
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
                <button type="submit" class="btn btn-primary" style="flex: 1; background: #ea580c; border-color: #ea580c;">Tampilkan</button>
                @if(!empty($startDate) || !empty($endDate) || !empty($gudangId))
                    <a href="{{ route('produksi.stok.ledger', ['barang_id' => $selectedBarang?->barang_id]) }}" class="btn btn-secondary" title="Reset Filter">&times;</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Active Summary Info -->
    @if ($selectedBarang)
        <div style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; background: #ffffff;">
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b;">Informasi Barang Produksi</span>
                <div style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ $selectedBarang->barang_nm }}
                    <span style="font-size: 0.875rem; font-weight: 600; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.5rem;">
                        {{ $selectedBarang->barang_cd }}
                    </span>
                    <span class="badge" style="background: {{ $selectedBarang->jenisBarang?->jenis_barang_cd === 'FG' ? '#dcfce7' : '#fef3c7' }}; color: {{ $selectedBarang->jenisBarang?->jenis_barang_cd === 'FG' ? '#15803d' : '#b45309' }}; margin-left: 0.25rem;">
                        {{ $selectedBarang->jenisBarang?->jenis_barang_nm ?? '-' }}
                    </span>
                </div>
                <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">
                    Satuan Output: <strong>{{ $selectedBarang->satuanDasar?->satuan_nm ?? '-' }} ({{ $selectedBarang->satuanDasar?->satuan_cd ?? '-' }})</strong> |
                    Batas Minimum / Buffer Stock: <strong>{{ number_format((float) $selectedBarang->batas_minimum_qty, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1.5rem; text-align: right;">
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: #64748b;">Total Saldo On-Hand</span>
                <div style="font-size: 1.75rem; font-weight: 800; color: #ea580c; line-height: 1.2; margin-top: 0.25rem;">
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
        <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">Mutasi &amp; Kartu Stok Fisik</h3>
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
                    <th>Perusahaan / Lokasi</th>
                    <th style="text-align: right; color: #059669;">Masuk (+)</th>
                    <th style="text-align: right; color: #dc2626;">Keluar (-)</th>
                    <th style="text-align: right;">Saldo Berjalan</th>
                    <th>Keterangan</th>
                    <th style="text-align: center; width: 90px;">Aksi</th>
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
                                <strong style="color: #0f172a;">{{ $row->dokumen_no }}</strong>
                            </td>
                            <td>
                                @if ($row->tipe_transaksi_cd === 'IN')
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700;">
                                        MASUK (HASIL)
                                    </span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700;">
                                        KELUAR (OUT)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="batch-clickable" onclick="showBatchDetailModal('{{ $row->batch_no }}', {{ $row->barang_id }})" title="Klik untuk menelusuri isi detail & riwayat batch" style="font-family: monospace; font-size: 0.8125rem; background: #fff7ed; border: 1px solid #fed7aa; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; color: #ea580c;">
                                    {{ $row->batch_no }} 🔍
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
                            <td style="text-align: center;">
                                @php
                                    $menuId = 'dropdown-prod-ledger-' . $row->ledger_id;
                                @endphp
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    Aksi &#9660;
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <button type="button" class="action-dropdown-item" onclick="showBatchDetailModal('{{ $row->batch_no }}', {{ $row->barang_id }})">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        <span>Dossier Batch</span>
                                    </button>
                                    @if (str_starts_with($row->dokumen_no, 'PRD-'))
                                        <a href="{{ route('produksi.index', ['search' => $row->dokumen_no]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Produksi</span>
                                        </a>
                                    @elseif (str_starts_with($row->dokumen_no, 'PK-') || str_starts_with($row->dokumen_no, 'OUT-'))
                                        <a href="{{ route('gudang.pemakaian.index', ['search' => $row->dokumen_no]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Pemakaian</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat mutasi stok untuk barang hasil produksi ini.
                            </td>
                        </tr>
                    @endforelse
                @else
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Silakan pilih barang hasil produksi terlebih dahulu.
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

@include('gudang.stok.partials.modal-batch-detail')

@push('scripts')
    <script src="{{ asset('js/gudang/stok/batch-detail-modal.js') }}"></script>
@endpush
@endsection
