@extends('layouts.app')

@section('title', 'Formula Resep Produksi (BOM) - ERP PT Mirasa')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Formula Resep Produksi (BOM)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Daftar standar komposisi bahan baku & penolong (*Bill of Materials*) untuk otomatisasi pengeluaran gudang (FIFO) dan kalkulasi HPP.
        </p>
    </div>
    <a href="{{ route('master.resep.create') }}" class="btn btn-primary" style="background: #2563eb; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; font-weight: 700;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + Buat Formula Resep Baru
    </a>
</div>

{{-- PENCARIAN & FILTER --}}
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('master.resep.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 260px; position: relative;">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode resep, nama resep, atau nama produk target..." class="form-control" style="padding-left: 2.25rem;">
            <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button type="submit" class="btn btn-secondary">Cari</button>
        @if ($search)
            <a href="{{ route('master.resep.index') }}" class="btn btn-secondary" style="color: #64748b;">Reset</a>
        @endif
    </form>
</div>

{{-- TABEL DAFTAR RESEP --}}
<div class="card">
    <div style="overflow-x: auto;">
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.825rem; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">
                    <th style="padding: 0.85rem 1rem;">No. Resep</th>
                    <th style="padding: 0.85rem 1rem;">Nama Formula Resep</th>
                    <th style="padding: 0.85rem 1rem;">Produk Target (Output)</th>
                    <th style="padding: 0.85rem 1rem; text-align: right;">Ukuran Batch Standar</th>
                    <th style="padding: 0.85rem 1rem; text-align: center;">Komponen Bahan</th>
                    <th style="padding: 0.85rem 1rem;">Catatan</th>
                    <th style="padding: 0.85rem 1rem; text-align: center; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bomList as $bom)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 0.85rem 1rem; font-family: monospace; font-weight: 700; color: #0284c7;">
                            <a href="{{ route('master.resep.show', $bom->bom_id) }}" style="color: #0284c7; text-decoration: none;">
                                {{ $bom->bom_no }}
                            </a>
                        </td>
                        <td style="padding: 0.85rem 1rem; font-weight: 600; color: #0f172a;">
                            <a href="{{ route('master.resep.show', $bom->bom_id) }}" style="color: inherit; text-decoration: none;">
                                {{ $bom->bom_nm }}
                            </a>
                        </td>
                        <td style="padding: 0.85rem 1rem;">
                            @if ($bom->barangJadi)
                                <div style="font-weight: 600; color: #1e293b;">
                                    [{{ $bom->barangJadi->barang_cd }}] {{ $bom->barangJadi->barang_nm }}
                                </div>
                                <small style="color: #64748b;">{{ $bom->barangJadi->satuanDasar?->satuan_nm }}</small>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 700; color: #0f172a;">
                            {{ number_format((float) $bom->batch_ukuran_qty, 0, ',', '.') }}
                            <small style="color: #64748b; font-weight: normal;">{{ $bom->barangJadi?->satuanDasar?->satuan_nm ?? 'Unit' }}</small>
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.8rem; padding: 0.25rem 0.65rem;">
                                {{ $bom->details->count() }} Bahan
                            </span>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: #64748b; font-size: 0.85rem; max-width: 250px;">
                            {{ Str::limit($bom->catatan_txt ?? '-', 60) }}
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                <a href="{{ route('master.resep.show', $bom->bom_id) }}" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Lihat Komposisi Bahan">
                                    Detail
                                </a>
                                <a href="{{ route('master.resep.edit', $bom->bom_id) }}" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: #d97706;" title="Edit Formula">
                                    Edit
                                </a>
                                <form action="{{ route('master.resep.destroy', $bom->bom_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus formula resep {{ $bom->bom_no }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: #ef4444;" title="Hapus Resep">
                                        &times;
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem 1rem; text-align: center; color: #94a3b8;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🥣</div>
                            <strong style="display: block; font-size: 1rem; color: #475569;">Belum Ada Formula Resep</strong>
                            <p style="font-size: 0.875rem; margin-top: 0.25rem;">
                                Buat formula resep BOM pertama untuk otomatisasi kalkulasi kebutuhan bahan baku dan alokasi batch FIFO.
                            </p>
                            <a href="{{ route('master.resep.create') }}" class="btn btn-primary" style="margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                + Buat Formula Baru
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($bomList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0;">
            {{ $bomList->links() }}
        </div>
    @endif
</div>
@endsection
