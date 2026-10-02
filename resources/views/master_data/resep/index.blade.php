@extends('layouts.app')

@section('title', 'Formula Resep Produksi (BOM) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/resep/resep-index.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Formula Resep Produksi (BOM)</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Daftar standar komposisi bahan baku &amp; penolong (*Bill of Materials*) untuk otomatisasi pengeluaran gudang (FIFO) dan kalkulasi HPP.
        </p>
    </div>
    @if (Auth::user()?->canCreateMasterResep())
        <a href="{{ route('master.resep.create') }}" class="btn btn-primary" style="background: #2563eb; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; font-weight: 700;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Buat Formula Resep Baru
        </a>
    @endif
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
                    <th style="padding: 0.85rem 1rem; text-align: right; width: 110px;">Aksi</th>
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
                        <td style="padding: 0.85rem 1rem; text-align: right;">
                            <div style="position: relative; display: inline-block;">
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $bom->bom_id }}')">
                                    Aksi ▼
                                </button>

                                <div id="action-menu-{{ $bom->bom_id }}" class="action-dropdown-menu">
                                    <a href="{{ route('master.resep.show', $bom->bom_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Rincian Formula
                                    </a>

                                    @if (Auth::user()?->canEditMasterResep())
                                        <a href="{{ route('master.resep.edit', $bom->bom_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit Formula
                                        </a>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterResep())
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="closeAllActionDropdowns(); openDeleteResepModal({{ $bom->bom_id }}, '{{ addslashes($bom->bom_no) }}', '{{ addslashes($bom->bom_nm) }}', '{{ addslashes($bom->barangJadi?->barang_nm ?? '-') }}', '{{ number_format((float) $bom->batch_ukuran_qty, 0, ',', '.') }} {{ addslashes($bom->barangJadi?->satuanDasar?->satuan_nm ?? 'Unit') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus Formula
                                        </button>
                                    @endif
                                </div>
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
                            @if (Auth::user()?->canCreateMasterResep())
                                <a href="{{ route('master.resep.create') }}" class="btn btn-primary" style="margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    + Buat Formula Baru
                                </a>
                            @endif
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

{{-- MODALS --}}
@include('master_data.resep.partials.modal-delete')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            resepBaseUrl: "{{ url('master-resep') }}"
        };
    </script>
    <script src="{{ asset('js/master/resep/resep-index.js') }}"></script>
@endpush
