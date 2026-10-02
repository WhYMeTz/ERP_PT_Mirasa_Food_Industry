@extends('layouts.app')

@section('title', 'Master Perusahaan & Entitas Bisnis - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/perusahaan/perusahaan-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Perusahaan &amp; Entitas Bisnis</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola kantor pusat, pabrik, cabang, dan anak perusahaan (PT Mirasa Food Industry, CV Bahtera Mandiri Bersama, dll).</p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterPerusahaan())
            <button type="button" onclick="openModal('modalTambahPerusahaan')" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Perusahaan Baru
            </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.perusahaan.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama perusahaan..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.perusahaan.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $gudangs->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">NO</th>
                    <th>NAMA PERUSAHAAN / ENTITAS</th>
                    <th>KODE</th>
                    <th>TIPE ENTITAS</th>
                    <th>ALAMAT &amp; KONTAK</th>
                    <th style="width: 130px; text-align: center;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gudangs as $index => $item)
                    <tr>
                        <td>{{ $gudangs->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.9375rem;">{{ $item->gudang_nm }}</strong>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #0284c7; font-family: monospace; font-weight: 700; font-size: 0.85rem;">
                                {{ $item->gudang_cd }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeBg = '#f1f5f9';
                                $badgeClr = '#475569';
                                if (strcasecmp($item->tipe_gudang_cd, 'Cabang') === 0) {
                                    $badgeBg = '#fef3c7'; $badgeClr = '#b45309';
                                } elseif (strcasecmp($item->tipe_gudang_cd, 'Pusat') === 0) {
                                    $badgeBg = '#e0f2fe'; $badgeClr = '#0369a1';
                                } elseif (strcasecmp($item->tipe_gudang_cd, 'Anak Perusahaan') === 0) {
                                    $badgeBg = '#f3e8ff'; $badgeClr = '#7e22ce';
                                }
                            @endphp
                            <span class="badge" style="background: {{ $badgeBg }}; color: {{ $badgeClr }}; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                {{ $item->tipe_gudang_cd ?? 'Pusat' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem; color: #475569; display: flex; flex-direction: column; gap: 0.2rem;">
                                <div style="display: flex; align-items: flex-start; gap: 0.35rem;">
                                    <span style="color: #64748b; font-size: 0.875rem;">📍</span>
                                    <span>{{ $item->alamat_txt ?? '-' }}</span>
                                </div>
                                @if (!empty($item->telepon))
                                    <div style="display: flex; align-items: center; gap: 0.35rem; color: #16a34a; font-weight: 600;">
                                        <span style="font-size: 0.875rem;">📞</span>
                                        <span>{{ $item->telepon }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterPerusahaan() || Auth::user()?->canDeleteMasterPerusahaan())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->gudang_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->gudang_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterPerusahaan())
                                        <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="closeAllActionDropdowns(); editPerusahaan({{ $item->gudang_id }}, '{{ addslashes($item->gudang_cd) }}', '{{ addslashes($item->gudang_nm) }}', '{{ addslashes($item->tipe_gudang_cd ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}', '{{ addslashes($item->telepon ?? '') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Perusahaan</span>
                                        </button>
                                    @endif

                                    @if (Auth::user()?->canEditMasterPerusahaan() && Auth::user()?->canDeleteMasterPerusahaan())
                                        <div class="action-dropdown-divider"></div>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterPerusahaan())
                                        <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="closeAllActionDropdowns(); openDeletePerusahaanModal({{ $item->gudang_id }}, '{{ addslashes($item->gudang_cd) }}', '{{ addslashes($item->gudang_nm) }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus / Nonaktifkan</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data perusahaan. Klik tombol <strong>"Tambah Perusahaan Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($gudangs->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $gudangs->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.perusahaan.partials.modal-create')
@include('master_data.perusahaan.partials.modal-edit')
@include('master_data.perusahaan.partials.modal-delete')

@push('scripts')
<script>
    window.masterPerusahaanConfig = {
        baseUrl: "{{ url('master-perusahaan') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/master/perusahaan/perusahaan-index.js') }}"></script>
@endpush
@endsection
