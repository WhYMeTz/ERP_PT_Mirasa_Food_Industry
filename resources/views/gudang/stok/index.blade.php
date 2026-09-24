@extends('layouts.app')

@section('title', 'Lacak Stok Gudang per Batch - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <span>Lacak Stok Gudang per Batch</span>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem;">Sheet Lacak Stok</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Pelacakan saldo kuantitas awal, mutasi keluar, sisa fisik barang, dan nilai persediaan per nomor batch (FIFO).
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary" style="background:#059669;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Terima Barang Masuk
        </a>
        <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background:#dc2626;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            - Catat Barang Keluar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: #f8fafc;">
        {{-- Status Filter Tabs --}}
        <div style="display: inline-flex; background: #e2e8f0; padding: 0.25rem; border-radius: 8px; gap: 0.25rem;">
            <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => ''])) }}" 
               class="btn btn-sm" 
               style="background: {{ empty($status) ? '#ffffff' : 'transparent' }}; color: {{ empty($status) ? '#0f172a' : '#64748b' }}; box-shadow: {{ empty($status) ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 600; padding: 0.35rem 0.75rem;">
                Semua Batch
            </a>
            <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => 'tersedia'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $status === 'tersedia' ? '#ffffff' : 'transparent' }}; color: {{ $status === 'tersedia' ? '#059669' : '#64748b' }}; box-shadow: {{ $status === 'tersedia' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 600; padding: 0.35rem 0.75rem;">
                🟢 Tersedia
            </a>
            <a href="{{ route('gudang.stok.index', array_merge(request()->except('status'), ['status' => 'habis'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $status === 'habis' ? '#ffffff' : 'transparent' }}; color: {{ $status === 'habis' ? '#dc2626' : '#64748b' }}; box-shadow: {{ $status === 'habis' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 600; padding: 0.35rem 0.75rem;">
                🔴 Habis
            </a>
        </div>

        {{-- Search & Gudang Filters --}}
        <form action="{{ route('gudang.stok.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="status" value="{{ $status ?? '' }}">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari batch, kode, nama barang..." class="form-control" style="padding: 0.45rem 0.75rem; width: 220px; font-size: 0.85rem;">
            
            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; width: 180px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Gudang --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                        {{ $gdg->gudang_nm }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($gudangId) || !empty($status))
                <a href="{{ route('gudang.stok.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter">&times;</a>
            @endif
        </form>
    </div>

    {{-- TABEL SESUAI FORMAT EXCEL LACAK STOK (FOTO 4) --}}
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr style="background: #0f394c; color: #ffffff;">
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Kode Batch</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Kode Barang</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Nama Barang</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Qty Awal</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Harga Satuan</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Qty Keluar</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Sisa Qty</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Sisa Nilai</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: center;">Status</th>
                    <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stokList as $item)
                    @php
                        $isHabis = (float) $item->sisa_qty <= 0;
                        $qtyAwal = (float) ($item->qty_awal > 0 ? $item->qty_awal : $item->sisa_qty);
                        $qtyKeluar = max(0, $qtyAwal - (float) $item->sisa_qty);
                        $hargaSatuan = (float) $item->harga_satuan;
                        $sisaNilai = (float) $item->sisa_qty * $hargaSatuan;
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $isHabis ? '#fafafa' : '#ffffff' }};">
                        <td>
                            <span style="font-family: monospace; font-size: 0.85rem; background: {{ $isHabis ? '#fee2e2' : '#f0fdf4' }}; color: {{ $isHabis ? '#991b1b' : '#166534' }}; border: 1px solid {{ $isHabis ? '#fecaca' : '#bbf7d0' }}; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700;">
                                {{ $item->batch_no }}
                            </span>
                            @if ($item->expired_tgl)
                                <span style="display: block; font-size: 0.725rem; color: #64748b; margin-top: 0.2rem;">
                                    Exp: {{ \Carbon\Carbon::parse($item->expired_tgl)->format('d/m/Y') }}
                                </span>
                            @endif
                        </td>
                        <td style="font-family: monospace; font-size: 0.85rem; font-weight: 600; color: #475569;">
                            {{ $item->barang?->barang_cd ?? '-' }}
                        </td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->barang?->barang_nm ?? '-' }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">
                                {{ $item->gudang?->gudang_nm }} • {{ $item->barang?->satuanDasar?->satuan_nm }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #334155;">
                            {{ number_format($qtyAwal, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; color: #334155; font-size: 0.875rem;">
                            Rp {{ number_format($hargaSatuan, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #dc2626;">
                            {{ number_format($qtyKeluar, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 0.95rem; color: {{ $isHabis ? '#94a3b8' : '#059669' }};">
                            {{ number_format((float) $item->sisa_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                            Rp {{ number_format($sisaNilai, 2, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @if ($isHabis)
                                <span style="background: #dc2626; color: #ffffff; padding: 0.25rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                    Habis
                                </span>
                            @else
                                <span style="background: #059669; color: #ffffff; padding: 0.25rem 0.65rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                    Tersedia
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $item->gudang_id]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" title="Lihat Kartu Riwayat Mutasi">
                                Kartu Stok &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Tidak ada data batch stok yang cocok dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($stokList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $stokList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
