@extends('layouts.app')

@section('title', 'Daftar Purchase Order - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Purchase Order (PO) Pengadaan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola dokumen pemesanan bahan baku singkong, minyak, bumbu, dan kemasan ke mitra supplier.</p>
    </div>
    <div>
        <a href="{{ route('gudang.po.create') }}" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat PO Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('gudang.po.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 600px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor PO atau nama supplier..." class="form-control" style="padding: 0.5rem 0.75rem; max-width: 280px;">
            <select name="status" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 160px;" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="APPROVED" {{ ($status ?? '') == 'APPROVED' ? 'selected' : '' }}>Disetujui</option>
                <option value="PARTIAL" {{ ($status ?? '') == 'PARTIAL' ? 'selected' : '' }}>Sebagian Diterima</option>
                <option value="COMPLETED" {{ ($status ?? '') == 'COMPLETED' ? 'selected' : '' }}>Selesai</option>
                <option value="CLOSED" {{ ($status ?? '') == 'CLOSED' ? 'selected' : '' }}>Ditutup</option>
                <option value="DRAFT" {{ ($status ?? '') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                <option value="CANCELLED" {{ ($status ?? '') == 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($search) || !empty($status))
                <a href="{{ route('gudang.po.index') }}" class="btn btn-secondary" title="Reset Filter">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total PO: <strong>{{ $poList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nomor PO</th>
                    <th>Tanggal</th>
                    <th>Supplier Mitra</th>
                    <th>Gudang Tujuan</th>
                    <th>Status</th>
                    <th style="text-align: right;">Total Nominal</th>
                    <th style="width: 200px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($poList as $index => $po)
                    <tr>
                        <td>{{ $poList->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('gudang.po.show', $po->po_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                                {{ $po->po_no }}
                            </a>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($po->po_tgl)->format('d/m/Y') }}
                            @if ($po->tgl_estimasi_datang)
                                <small style="display: block; font-size: 0.725rem; color: #64748b;">
                                    Tiba: {{ \Carbon\Carbon::parse($po->tgl_estimasi_datang)->format('d/m/Y') }}
                                </small>
                            @endif
                        </td>
                        <td style="font-weight: 600;">{{ $po->supplier?->supplier_nm ?? '-' }}</td>
                        <td>{{ $po->gudang?->gudang_nm ?? '-' }}</td>
                        <td>
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
                        </td>
                        <td style="text-align: right; font-weight: 700;">
                            Rp {{ number_format((float) $po->total_nominal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <a href="{{ route('gudang.po.show', $po->po_id) }}" class="btn btn-secondary btn-sm" title="Lihat Detail">
                                    Detail
                                </a>
                                @if (in_array($po->status_cd, ['APPROVED', 'PARTIAL']))
                                    <a href="{{ route('gudang.terima.create', ['po_id' => $po->po_id]) }}" class="btn btn-primary btn-sm" style="background:#059669;" title="Terima Barang Fisik">
                                        Terima
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada transaksi Purchase Order. Klik tombol <strong>"Buat PO Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($poList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $poList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
