@extends('layouts.app')

@section('title', 'Riwayat & Tiket QC Bahan Baku Masuk')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 2rem;">
    {{-- PAGE HEADER --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">
                🔬 Tiket Quality Control (QC Inbound)
            </h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                Daftar inspeksi mutu bahan baku dari sampling lapangan &amp; jembatan timbang sebelum masuk ke gudang.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            @if (Auth::user()->canCreateQc())
                <a href="{{ route('qc.inbound.create') }}" class="btn btn-primary" style="border-radius: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Input Uji Lapangan (QC)</span>
                </a>
            @endif
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.25rem; border-radius: 10px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.25rem; border-radius: 10px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- FILTER CARD --}}
    <div class="card" style="margin-bottom: 1.5rem; border-radius: 12px; padding: 1.25rem;">
        <form action="{{ route('qc.inbound.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Pencarian (No QC, SJ, Truk, Sopir)</label>
                <input type="text" name="search" class="form-control" placeholder="Cari tiket..." value="{{ $filters['search'] ?? '' }}">
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Komoditas Masuk</label>
                <select name="kategori_barang" class="form-control">
                    <option value="">-- Semua Komoditas --</option>
                    <option value="SINGKONG" {{ ($filters['kategori_barang'] ?? '') === 'SINGKONG' ? 'selected' : '' }}>🥔 Singkong</option>
                    <option value="MINYAK" {{ ($filters['kategori_barang'] ?? '') === 'MINYAK' ? 'selected' : '' }}>🛢️ Minyak Goreng</option>
                    <option value="PLASTIK" {{ ($filters['kategori_barang'] ?? '') === 'PLASTIK' ? 'selected' : '' }}>🛍️ Plastik Kemasan</option>
                    <option value="KARTON" {{ ($filters['kategori_barang'] ?? '') === 'KARTON' ? 'selected' : '' }}>📦 Karton Box</option>
                    <option value="MSG" {{ ($filters['kategori_barang'] ?? '') === 'MSG' ? 'selected' : '' }}>🧂 MSG</option>
                    <option value="GARAM" {{ ($filters['kategori_barang'] ?? '') === 'GARAM' ? 'selected' : '' }}>🧂 Garam</option>
                    <option value="PERENYAH" {{ ($filters['kategori_barang'] ?? '') === 'PERENYAH' ? 'selected' : '' }}>✨ Perenyah</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Status Tiket QC</label>
                <select name="status_qc" class="form-control">
                    <option value="">-- Semua Status --</option>
                    <option value="SIAP_GUDANG" {{ ($filters['status_qc'] ?? '') === 'SIAP_GUDANG' ? 'selected' : '' }}>⏳ Siap Terima di Gudang</option>
                    <option value="DITERIMA_GUDANG" {{ ($filters['status_qc'] ?? '') === 'DITERIMA_GUDANG' ? 'selected' : '' }}>✅ Selesai Diterima Gudang</option>
                    <option value="DITOLAK_TOTAL" {{ ($filters['status_qc'] ?? '') === 'DITOLAK_TOTAL' ? 'selected' : '' }}>❌ Ditolak Total (Reject)</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Status Uji Goreng (Lab)</label>
                <select name="status_uji_goreng" class="form-control">
                    <option value="">-- Semua Uji Goreng --</option>
                    <option value="MENUNGGU_LAB" {{ ($filters['status_uji_goreng'] ?? '') === 'MENUNGGU_LAB' ? 'selected' : '' }}>⏳ Menyusul di Lab</option>
                    <option value="SELESAI" {{ ($filters['status_uji_goreng'] ?? '') === 'SELESAI' ? 'selected' : '' }}>🍟 Uji Goreng Selesai</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Mitra Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">-- Semua Supplier --</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $s->supplier_id ? 'selected' : '' }}>
                            {{ $s->supplier_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; border-radius: 8px;">
                    Filter
                </button>
                <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary" style="border-radius: 8px;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABEL DATA TIKET QC --}}
    <div class="card" style="border-radius: 12px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.825rem; color: #475569;">
                        <th style="padding: 0.85rem 1rem; text-align: left;">No. Tiket QC</th>
                        <th style="padding: 0.85rem 1rem; text-align: left;">Waktu &amp; Penguji</th>
                        <th style="padding: 0.85rem 1rem; text-align: left;">Supplier &amp; PO</th>
                        <th style="padding: 0.85rem 1rem; text-align: left;">Armada / Sopir</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Gross (KG)</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Refraksi</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Reject</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Netto Lolos</th>
                        <th style="padding: 0.85rem 1rem; text-align: center;">Status</th>
                        <th style="padding: 0.85rem 1rem; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspeksiList as $qc)
                        @php
                            $totalGross = $qc->details->sum('qty_timbang_gross');
                            $totalRefraksi = $qc->details->sum('qty_refraksi');
                            $totalReject = $qc->details->sum('qty_reject');
                            $totalNetto = $qc->details->sum('qty_netto_lolos');
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.875rem;">
                            <td style="padding: 0.85rem 1rem; font-weight: 700; color: #0284c7;">
                                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" style="color: inherit; text-decoration: none;">
                                    {{ $qc->qc_no }}
                                </a>
                                <div style="display: flex; gap: 0.35rem; align-items: center; margin-top: 3px; flex-wrap: wrap;">
                                    <span style="font-size: 0.7rem; font-weight: 800; background: #e0f2fe; color: #0284c7; padding: 0.1rem 0.45rem; border-radius: 10px;">
                                        {{ $qc->kategori_barang ?? 'SINGKONG' }}
                                    </span>
                                    @if ($qc->terima)
                                        <span style="font-size: 0.7rem; color: #059669; font-weight: 700;">
                                            🔗 GRN: {{ $qc->terima->terima_no }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 600; color: #0f172a;">{{ $qc->tgl_periksa->format('d/m/Y H:i') }}</div>
                                <div style="font-size: 0.775rem; color: #64748b;">Oleh: {{ $qc->petugas_qc_nama }}</div>
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 600; color: #0f172a;">{{ $qc->supplier?->supplier_nm ?? '-' }}</div>
                                @if ($qc->po)
                                    <div style="font-size: 0.775rem; color: #0284c7;">PO: {{ $qc->po->po_no }}</div>
                                @else
                                    <span style="font-size: 0.725rem; color: #94a3b8;">(Tanpa PO)</span>
                                @endif
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 600; color: #334155;">{{ $qc->plat_nomor_truk ?: '-' }}</div>
                                <div style="font-size: 0.775rem; color: #64748b;">{{ $qc->sopir_nama ?: 'Sopir -' }}</div>
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 600;">
                                {{ number_format($totalGross, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #ca8a04;">
                                {{ number_format($totalRefraksi, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #dc2626;">
                                {{ number_format($totalReject, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 800; color: #15803d;">
                                {{ number_format($totalNetto, 2, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center;">
                                @if ($qc->status_qc === 'SIAP_GUDANG')
                                    <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; border: 1px solid #fde68a;">
                                        ⏳ Siap Gudang
                                    </span>
                                @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700; border: 1px solid #bbf7d0;">
                                        ✅ Diterima Gudang
                                    </span>
                                @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700; border: 1px solid #fecaca;">
                                        ❌ Reject Total
                                    </span>
                                @else
                                    <span class="badge badge-info">{{ $qc->status_qc }}</span>
                                @endif

                                @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                                    <div style="font-size: 0.675rem; color: #b45309; font-weight: 700; margin-top: 3px;">
                                        ⏳ Goreng: Menyusul
                                    </div>
                                @elseif ($qc->status_uji_goreng === 'SELESAI')
                                    <div style="font-size: 0.675rem; color: #15803d; font-weight: 700; margin-top: 3px;">
                                        🍟 Goreng: Selesai
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center;">
                                <div style="display: flex; gap: 0.35rem; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn btn-sm btn-secondary" title="Lihat Lembar Uji">
                                        Detail
                                    </a>
                                    @if (Auth::user()?->canEditQc() && (!$qc->terima || Auth::user()?->isSuperAdmin()))
                                        <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="btn btn-sm btn-secondary" title="Edit / Koreksi Tiket" style="color: #1d4ed8; background: #eff6ff; border-color: #bfdbfe;">
                                            ✏️
                                        </a>
                                    @endif
                                    @if (Auth::user()?->canDeleteQc() && (!$qc->terima || Auth::user()?->isSuperAdmin()))
                                        <form action="{{ route('qc.inbound.destroy', $qc->qc_id) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Hapus / batalkan tiket QC #{{ $qc->qc_no }}? {{ $qc->terima ? 'PERHATIAN: Tiket ini terhubung dengan GRN #' . $qc->terima->terima_no . '.' : '' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Hapus Tiket" style="color: #dc2626; background: #fef2f2; border-color: #fecaca; padding: 0.25rem 0.5rem;">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                    @if ($qc->status_qc === 'SIAP_GUDANG' && Auth::user()?->canAccessTerima())
                                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn btn-sm btn-primary" title="Tarik ke Penerimaan Gudang">
                                            📦 Terima
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding: 3rem; text-align: center; color: #64748b;">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔬</div>
                                <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Belum Ada Tiket Inspeksi QC</div>
                                <div style="font-size: 0.85rem; margin-top: 0.25rem;">Klik tombol "Input Uji Lapangan (QC)" untuk mulai mencatat sampling bahan baku.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inspeksiList->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
                {{ $inspeksiList->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
