@extends('layouts.app')

@section('title', 'Kartu Stok Bahan Baku & Penolong - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/stok/batch-detail-modal.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <span>Kartu Stok Bahan Baku &amp; Penolong</span>
            <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.75rem; font-weight: 700;">Gudang Bahan</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Histori audit trail mutasi masuk (IN dari supplier) dan keluar (OUT ke produksi) bahan mentah, bumbu, minyak &amp; kemasan.
        </p>
    </div>
    <div>
        <a href="{{ route('gudang.stok.index') }}" class="btn btn-secondary">
            &larr; Lacak Stok Bahan Baku
        </a>
    </div>
</div>

<!-- Filter Selector -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('gudang.stok.ledger') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Pilih Bahan Baku / Penolong</label>
                <select name="barang_id" class="form-control" onchange="this.form.submit()" required>
                    @foreach ($barangList as $b)
                        <option value="{{ $b->barang_id }}" {{ ($selectedBarang && $selectedBarang->barang_id == $b->barang_id) ? 'selected' : '' }}>
                            {{ $b->barang_nm }} ({{ $b->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Perusahaan / Lokasi</label>
                <select name="gudang_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Perusahaan / Cabang --</option>
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
                    <th style="text-align: center; width: 85px;">Grade</th>
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
                                <span class="batch-clickable" onclick="showBatchDetailModal('{{ $row->batch_no }}', {{ $row->barang_id }})" title="Klik untuk menelusuri isi detail & riwayat batch" style="font-family: monospace; font-size: 0.8125rem; background: #e0f2fe; border: 1px solid #bae6fd; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700; color: #0284c7;">
                                    {{ $row->batch_no }} 🔍
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if ($row->grade_cd === 'B')
                                    <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800; font-size: 0.72rem; padding: 2px 7px;">
                                        🟡 Grade B
                                    </span>
                                @elseif ($row->grade_cd === 'REJECT')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px;">
                                        ❌ Afkir
                                    </span>
                                @else
                                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px;">
                                        🟢 Grade A
                                    </span>
                                @endif
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
                                    $menuId = 'dropdown-ledger-' . $row->ledger_id;
                                @endphp
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <button type="button" class="action-dropdown-item" onclick="showBatchDetailModal('{{ $row->batch_no }}', {{ $row->barang_id }})">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        <span>Dossier Batch</span>
                                    </button>
                                    @if (str_starts_with($row->dokumen_no, 'GR-'))
                                        <a href="{{ route('gudang.terima.index', ['search' => $row->dokumen_no]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Penerimaan</span>
                                        </a>
                                    @elseif (str_starts_with($row->dokumen_no, 'PK-') || str_starts_with($row->dokumen_no, 'OUT-'))
                                        <a href="{{ route('gudang.pemakaian.index', ['search' => $row->dokumen_no]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Pemakaian</span>
                                        </a>
                                    @elseif (str_starts_with($row->dokumen_no, 'ADJ-'))
                                        <a href="{{ route('gudang.adjustment.index', ['search' => $row->dokumen_no]) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Adjustment</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat mutasi stok untuk barang dan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                @else
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
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

@include('gudang.stok.partials.modal-batch-detail')

@push('scripts')
    <script src="{{ asset('js/gudang/stok/batch-detail-modal.js') }}"></script>
@endpush
@endsection
