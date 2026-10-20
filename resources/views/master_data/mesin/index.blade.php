@extends('layouts.app')

@section('title', 'Master Data Mesin Pabrik - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/mesin/mesin-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Master Data Mesin Pabrik</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Kelola data mesin operasional pabrik untuk lini produksi (penggorengan, perajangan) dan packaging (pengemasan, strapping).
        </p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterMesin())
            <button type="button" onclick="openModal('modalTambahMesin')" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.45rem; font-size: 0.85rem; padding: 0.55rem 1.15rem; background: #0284c7; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Mesin Baru
            </button>
        @endif
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- KPI Summary Cards --}}
<div class="mesin-stats-grid">
    {{-- Total Mesin --}}
    <div class="mesin-stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div class="stat-data">
            <span class="stat-label">Total Mesin Pabrik</span>
            <span class="stat-value">{{ $summary['total'] }}</span>
        </div>
    </div>

    {{-- Mesin Produksi --}}
    <div class="mesin-stat-card">
        <div class="stat-icon-wrapper stat-icon-orange">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
        </div>
        <div class="stat-data">
            <span class="stat-label">Mesin Produksi / Olah</span>
            <span class="stat-value" style="color: #c2410c;">{{ $summary['produksi'] }}</span>
        </div>
    </div>

    {{-- Mesin Packaging --}}
    <div class="mesin-stat-card">
        <div class="stat-icon-wrapper stat-icon-indigo">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div class="stat-data">
            <span class="stat-label">Mesin Packaging / Kemas</span>
            <span class="stat-value" style="color: #4f46e5;">{{ $summary['packaging'] }}</span>
        </div>
    </div>

    {{-- Siap Operasi --}}
    <div class="mesin-stat-card">
        <div class="stat-icon-wrapper stat-icon-green">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="stat-data">
            <span class="stat-label">Siap Operasi (Ready)</span>
            <span class="stat-value" style="color: #16a34a;">{{ $summary['siap'] }}</span>
        </div>
    </div>

    {{-- Maintenance / Rusak --}}
    <div class="mesin-stat-card">
        <div class="stat-icon-wrapper stat-icon-amber">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="stat-data">
            <span class="stat-label">Perlu Perawatan</span>
            <span class="stat-value" style="color: {{ $summary['maintenance'] > 0 ? '#dc2626' : '#64748b' }};">
                {{ $summary['maintenance'] }}
            </span>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        {{-- Kategori Filter Pills --}}
        <div class="kategori-tabs-wrapper" style="margin-bottom: 0;">
            <a href="{{ route('master.mesin.index', ['kategori' => 'SEMUA', 'search' => $search]) }}" 
               class="kategori-tab-item {{ ($kategori === 'SEMUA' || empty($kategori)) ? 'active' : '' }}">
                <span>Semua Mesin</span>
                <span class="badge-counter">{{ $summary['total'] }}</span>
            </a>
            <a href="{{ route('master.mesin.index', ['kategori' => 'PRODUKSI', 'search' => $search]) }}" 
               class="kategori-tab-item {{ $kategori === 'PRODUKSI' ? 'active' : '' }}">
                <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #ea580c;"></span>
                <span>Mesin Produksi</span>
                <span class="badge-counter">{{ $summary['produksi'] }}</span>
            </a>
            <a href="{{ route('master.mesin.index', ['kategori' => 'PACKAGING', 'search' => $search]) }}" 
               class="kategori-tab-item {{ $kategori === 'PACKAGING' ? 'active' : '' }}">
                <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #4f46e5;"></span>
                <span>Mesin Packaging</span>
                <span class="badge-counter">{{ $summary['packaging'] }}</span>
            </a>
        </div>

        {{-- Form Pencarian --}}
        <form action="{{ route('master.mesin.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 380px; width: 100%;">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode, nama, proses..." class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem;">
            <button type="submit" class="btn btn-secondary btn-sm" style="font-weight: 600;">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.mesin.index', ['kategori' => $kategori]) }}" class="btn btn-secondary btn-sm" title="Reset Pencarian">&times;</a>
            @endif
        </form>
    </div>

    {{-- Tabel Mesin Operasional --}}
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 130px;">Kode Mesin</th>
                    <th>Nama Mesin Operasional</th>
                    <th style="width: 170px;">Kategori Mesin</th>
                    <th style="width: 160px;">Jenis Proses</th>
                    <th style="width: 150px;">Kapasitas Standar</th>
                    <th style="width: 120px;">Sumber Daya</th>
                    <th style="width: 140px; text-align: center;">Kondisi Mesin</th>
                    <th style="width: 90px; text-align: center;">Status</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mesinList as $index => $item)
                    <tr>
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $mesinList->firstItem() + $index }}
                        </td>
                        <td>
                            <strong style="color: #0284c7; font-size: 0.875rem; font-family: monospace;">
                                {{ $item->mesin_cd }}
                            </strong>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                {{ $item->mesin_nm }}
                            </div>
                            @if ($item->keterangan)
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                    {{ $item->keterangan }}
                                </div>
                            @endif
                        </td>
                        <td>
                            {!! $item->kategori_badge !!}
                        </td>
                        <td>
                            <span style="font-size: 0.8rem; font-weight: 600; color: #475569;">
                                {{ str_replace('_', ' ', $item->jenis_proses ?: '-') }}
                            </span>
                        </td>
                        <td>
                            @if ($item->kapasitas_per_jam !== null)
                                <span style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                                    {{ number_format($item->kapasitas_per_jam, 0, ',', '.') }}
                                </span>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                                    {{ $item->satuan_kapasitas }}
                                </span>
                            @else
                                <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 0.8rem; font-weight: 600; color: #334155;">
                                {{ $item->tipe_energi ?: '-' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            {!! $item->status_kondisi_badge !!}
                        </td>
                        <td style="text-align: center;">
                            @if ($item->active_st)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterMesin() || Auth::user()?->canDeleteMasterMesin())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->mesin_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->mesin_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterMesin())
                                        <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="openEditMesin({{ json_encode($item) }})">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Ubah Data Mesin</span>
                                        </button>

                                        <form action="{{ route('master.mesin.toggle_status', $item->mesin_id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-dropdown-item">
                                                @if($item->active_st)
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                    <span>Set Nonaktif</span>
                                                @else
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>Set Aktif</span>
                                                @endif
                                            </button>
                                        </form>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterMesin())
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" 
                                            class="action-dropdown-item danger-item"
                                            onclick="confirmDeleteMesin('{{ route('master.mesin.destroy', $item->mesin_id) }}', '{{ $item->mesin_cd }}', '{{ $item->mesin_nm }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus Mesin</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span style="color: #94a3b8; font-size: 0.8rem;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            <div style="font-size: 1.75rem; margin-bottom: 0.5rem;">🏭</div>
                            <div style="font-weight: 600; color: #475569; font-size: 0.95rem;">Belum ada data mesin pabrik.</div>
                            <div style="font-size: 0.825rem; color: #94a3b8; margin-top: 0.25rem;">
                                @if(!empty($search) || ($kategori !== 'SEMUA' && !empty($kategori)))
                                    Tidak ada mesin yang sesuai dengan kriteria filter saat ini.
                                @else
                                    Silakan klik tombol "Tambah Mesin Baru" untuk mendaftarkan mesin operasional.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($mesinList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $mesinList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.mesin.partials.modal-create')
@include('master_data.mesin.partials.modal-edit')
@include('master_data.mesin.partials.modal-delete')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            baseUrl: "{{ url('') }}"
        };
    </script>
    <script src="{{ asset('js/master/mesin/mesin-index.js') }}"></script>
@endpush
