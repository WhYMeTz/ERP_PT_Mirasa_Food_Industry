@extends('layouts.app')

@section('title', 'Barang Masuk & Penerimaan Fisik (Goods Receipt) - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <span>Barang Masuk (Penerimaan Fisik Gudang)</span>
            <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.75rem;">Sheet Barang Masuk</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Pencatatan fisik bongkar muat bahan baku masuk ke gudang, nomor batch, dan tanggal kadaluarsa (Inbound FIFO).
        </p>
    </div>
    <div>
        @if (Auth::user()?->canCreateTerima())
            <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary" style="background: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Catat Barang Masuk
            </a>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        {{-- View Switcher Tab --}}
        <div style="display: inline-flex; background: #e2e8f0; padding: 0.25rem; border-radius: 8px; gap: 0.25rem;">
            <a href="{{ route('gudang.terima.index', array_merge(request()->query(), ['view' => 'item'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'item' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'item' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'item' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 600;">
                📊 Rincian Item (Format Excel)
            </a>
            <a href="{{ route('gudang.terima.index', array_merge(request()->query(), ['view' => 'header'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'header' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'header' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'header' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 600;">
                📑 Daftar Dokumen GRN
            </a>
        </div>

        {{-- Filter & Search Form --}}
        <form action="{{ route('gudang.terima.index') }}" method="GET" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="view" value="{{ $viewType }}">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari batch, barang, supplier, no SJ..." class="form-control" style="padding: 0.45rem 0.75rem; width: 220px; font-size: 0.85rem;">
            
            <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; width: 180px; font-size: 0.85rem;" onchange="this.form.submit()">
                <option value="">-- Semua Gudang --</option>
                @foreach ($gudangList as $gdg)
                    <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                        {{ $gdg->gudang_nm }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
            @if(!empty($search) || !empty($gudangId))
                <a href="{{ route('gudang.terima.index', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm" title="Reset Filter">&times;</a>
            @endif
        </form>
    </div>

    @if ($viewType === 'item')
        {{-- VIEW RINCIAN PER ITEM (EXCEL BARANG MASUK) --}}
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr style="background: #0f394c; color: #ffffff;">
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Tanggal</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Kode Batch</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Kode Barang</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Nama Barang</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Jenis</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Keterangan</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Qty Masuk</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: left;">Satuan</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Harga Satuan</th>
                        <th style="padding: 0.75rem 1rem; color: #e2e8f0; font-weight: 600; text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $row)
                        @php
                            $subtotal = (float) $row->terima_qty * (float) $row->harga_nominal;
                            $keterangan = $row->catatan_txt;
                            if (empty($keterangan)) {
                                $keterangan = ($row->header?->supplier?->supplier_nm ?? 'Supplier') . ' (SJ: ' . ($row->header?->suratjalan_no ?? '-') . ')';
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="white-space: nowrap; font-size: 0.875rem;">
                                {{ \Carbon\Carbon::parse($row->header?->terima_tgl)->format('d-M-Y') }}
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.85rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700;">
                                    {{ $row->batch_no }}
                                </span>
                            </td>
                            <td style="font-family: monospace; font-size: 0.85rem; font-weight: 600; color: #475569;">
                                {{ $row->barang?->barang_cd ?? '-' }}
                            </td>
                            <td style="font-weight: 600; color: #0f172a;">
                                {{ $row->barang?->barang_nm ?? '-' }}
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem;">
                                    {{ $row->barang?->jenisBarang?->jenis_barang_nm ?? ($row->barang?->jenisBarang?->jenis_barang_cd ?? '-') }}
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: #334155;">
                                {{ $keterangan }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #059669; font-size: 0.95rem;">
                                {{ number_format((float) $row->terima_qty, 2, ',', '.') }}
                            </td>
                            <td style="color: #64748b; font-size: 0.85rem;">
                                {{ $row->barang?->satuanDasar?->satuan_nm ?? '-' }}
                            </td>
                            <td style="text-align: right; color: #334155; font-size: 0.875rem;">
                                Rp {{ number_format((float) $row->harga_nominal, 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                                Rp {{ number_format($subtotal, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat penerimaan barang masuk. Klik tombol <strong>"+ Catat Barang Masuk"</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- VIEW DOKUMEN HEADER --}}
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor Penerimaan</th>
                        <th>Tanggal Masuk</th>
                        <th>No Surat Jalan</th>
                        <th>Ref. PO</th>
                        <th>Supplier Pengirim</th>
                        <th>Gudang Penyimpanan</th>
                        <th>Status</th>
                        <th style="width: 140px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataList as $index => $item)
                        <tr>
                            <td>{{ $dataList->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('gudang.terima.show', $item->terima_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                                    {{ $item->terima_no }}
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->terima_tgl)->format('d/m/Y') }}</td>
                            <td style="font-weight: 600;">{{ $item->suratjalan_no ?? '-' }}</td>
                            <td>
                                @if ($item->po)
                                    <a href="{{ route('gudang.po.show', $item->po_id) }}" style="color: #0369a1; text-decoration: none; font-weight: 600;">
                                        {{ $item->po->po_no }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Non-PO</span>
                                @endif
                            </td>
                            <td style="font-weight: 600;">{{ $item->supplier?->supplier_nm ?? '-' }}</td>
                            <td>{{ $item->gudang?->gudang_nm ?? '-' }}</td>
                            <td><span class="badge badge-success">Stok Masuk</span></td>
                            <td style="text-align: right;">
                                <a href="{{ route('gudang.terima.show', $item->terima_id) }}" class="btn btn-secondary btn-sm">
                                    Detail GRN &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                Belum ada riwayat penerimaan barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($dataList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $dataList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
