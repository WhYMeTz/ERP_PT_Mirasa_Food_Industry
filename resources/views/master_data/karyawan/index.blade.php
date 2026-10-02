@extends('layouts.app')

@section('title', 'Master Data Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/karyawan/karyawan-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Karyawan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data profil staf operasional, produksi, gudang, purchasing, dan tim manajemen.</p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterKaryawan())
            <button type="button" onclick="openModal('modalTambahKaryawan')" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Karyawan Baru
            </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.karyawan.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 600px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari NIK, nama, jabatan, email..." class="form-control" style="padding: 0.5rem 0.75rem; max-width: 260px;">
            <select name="departemen" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 200px;" onchange="this.form.submit()">
                <option value="">-- Semua Departemen --</option>
                @foreach ($departemenList as $key => $label)
                    <option value="{{ $key }}" {{ ($departemen ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($search) || !empty($departemen))
                <a href="{{ route('master.karyawan.index') }}" class="btn btn-secondary" title="Reset Filter">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Karyawan: <strong>{{ $karyawanList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Kontak / Telp</th>
                    <th>Akun Sistem &amp; Cabang</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($karyawanList as $index => $item)
                    <tr>
                        <td>{{ $karyawanList->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: #0284c7; font-family: monospace; font-size: 0.9rem;">{{ $item->nik }}</strong>
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $item->karyawan_nm }}
                            @if ($item->email)
                                <span style="display: block; font-size: 0.75rem; color: #64748b; font-weight: normal;">{{ $item->email }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $deptBg = '#f1f5f9';
                                $deptClr = '#475569';
                                if ($item->departemen_cd === 'PRODUKSI') { $deptBg = '#ffedd5'; $deptClr = '#c2410c'; }
                                elseif ($item->departemen_cd === 'GUDANG') { $deptBg = '#e0f2fe'; $deptClr = '#0369a1'; }
                                elseif ($item->departemen_cd === 'PURCHASING') { $deptBg = '#fef3c7'; $deptClr = '#92400e'; }
                                elseif ($item->departemen_cd === 'FINANCE') { $deptBg = '#dcfce7'; $deptClr = '#15803d'; }
                                elseif ($item->departemen_cd === 'QC') { $deptBg = '#f3e8ff'; $deptClr = '#7e22ce'; }
                                elseif ($item->departemen_cd === 'HRD') { $deptBg = '#fce7f3'; $deptClr = '#be185d'; }
                                elseif ($item->departemen_cd === 'MANAJEMEN') { $deptBg = '#fef08a'; $deptClr = '#854d0e'; }
                            @endphp
                            <span class="badge" style="background: {{ $deptBg }}; color: {{ $deptClr }}; font-weight: 700;">
                                {{ $departemenList[$item->departemen_cd] ?? $item->departemen_cd }}
                            </span>
                        </td>
                        <td>{{ $item->jabatan_nm }}</td>
                        <td>{{ $item->telepon_no ?? '-' }}</td>
                        <td>
                            @if ($item->user)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 600;">
                                    ✓ Akun: {{ $item->user->role_cd }}
                                </span>
                                @if ($item->user->gudang)
                                    <span style="display: block; font-size: 0.75rem; color: #0284c7; margin-top: 0.15rem;">
                                        📍 {{ $item->user->gudang->gudang_nm }}
                                    </span>
                                @else
                                    <span style="display: block; font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem;">
                                        🌐 Seluruh Perusahaan
                                    </span>
                                @endif
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #94a3b8;">
                                    Belum ada akun
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterKaryawan() || Auth::user()?->canDeleteMasterKaryawan())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->karyawan_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->karyawan_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterKaryawan())
                                        <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="closeAllActionDropdowns(); editKaryawan({{ $item->karyawan_id }}, '{{ addslashes($item->nik) }}', '{{ addslashes($item->karyawan_nm) }}', '{{ $item->departemen_cd }}', '{{ addslashes($item->jabatan_nm) }}', '{{ addslashes($item->telepon_no ?? '') }}', '{{ addslashes($item->email ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Data Karyawan</span>
                                        </button>
                                    @endif

                                    @if (Auth::user()?->canEditMasterKaryawan() && Auth::user()?->canDeleteMasterKaryawan())
                                        <div class="action-dropdown-divider"></div>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterKaryawan())
                                        <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="closeAllActionDropdowns(); openDeleteKaryawanModal({{ $item->karyawan_id }}, '{{ addslashes($item->nik) }}', '{{ addslashes($item->karyawan_nm) }}', '{{ addslashes($departemenList[$item->departemen_cd] ?? $item->departemen_cd) }}', '{{ addslashes($item->jabatan_nm) }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Nonaktifkan Karyawan</span>
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
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data karyawan. Klik tombol <strong>"Tambah Karyawan Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($karyawanList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $karyawanList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.karyawan.partials.modal-create')
@include('master_data.karyawan.partials.modal-edit')
@include('master_data.karyawan.partials.modal-delete')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            karyawanBaseUrl: "{{ url('master-karyawan') }}",
            nextNik: "{{ $nextNik }}"
        };
    </script>
    <script src="{{ asset('js/master/karyawan/karyawan-index.js') }}"></script>
@endpush
