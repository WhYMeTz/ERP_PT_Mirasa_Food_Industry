@extends('layouts.app')

@section('title', 'Master Pajak & Tarif - ERP Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/pajak/pajak-index.css') }}?v={{ file_exists(public_path('css/master/pajak/pajak-index.css')) ? filemtime(public_path('css/master/pajak/pajak-index.css')) : time() }}">
@endpush

@section('content')
<div class="pajak-page-container">
    {{-- Page Header (PO Style) --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Master Pajak &amp; Tarif</h1>
            <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                Kelola daftar perpajakan (PPN, PPh), persentase tarif, dan pemetaan akun pembukuan Pembelian &amp; Penjualan.
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openModal('modalTambahPajak')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pajak Baru
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="alert-content">
                <strong>Berhasil!</strong> {{ session('success') }}
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div class="alert-content">
                <strong>Gagal!</strong> {{ session('error') }}
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-warning">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="alert-content">
                <strong>Terdapat kesalahan input:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- KPI Summary Cards (Full Width Grid) --}}
    <div class="kpi-grid">
        <div class="kpi-card card-total">
            <div class="kpi-body">
                <div class="kpi-info">
                    <span class="kpi-label">Total Jenis Pajak</span>
                    <h3 class="kpi-value">{{ $summary['total'] ?? 0 }}</h3>
                    <span class="kpi-sub">Seluruh konfigurasi terdaftar</span>
                </div>
                <div class="kpi-icon-wrap bg-blue-subtle text-blue">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="kpi-card card-ppn">
            <div class="kpi-body">
                <div class="kpi-info">
                    <span class="kpi-label">Pajak Pembelian (Beli)</span>
                    <h3 class="kpi-value">{{ $summary['pembelian'] ?? 0 }}</h3>
                    <span class="kpi-sub">Aktif pada PO &amp; Faktur Beli</span>
                </div>
                <div class="kpi-icon-wrap bg-purple-subtle text-purple">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="kpi-card card-pph">
            <div class="kpi-body">
                <div class="kpi-info">
                    <span class="kpi-label">Pajak Penjualan (Jual)</span>
                    <h3 class="kpi-value">{{ $summary['penjualan'] ?? 0 }}</h3>
                    <span class="kpi-sub">Aktif pada Invoice Penjualan</span>
                </div>
                <div class="kpi-icon-wrap bg-amber-subtle text-amber">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="kpi-card card-active">
            <div class="kpi-body">
                <div class="kpi-info">
                    <span class="kpi-label">Pajak Aktif</span>
                    <h3 class="kpi-value">{{ $summary['aktif'] ?? 0 }}</h3>
                    <span class="kpi-sub">Dapat dipilih pada transaksi</span>
                </div>
                <div class="kpi-icon-wrap bg-emerald-subtle text-emerald">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="kpi-card card-inactive">
            <div class="kpi-body">
                <div class="kpi-info">
                    <span class="kpi-label">Non-Aktif</span>
                    <h3 class="kpi-value">{{ $summary['nonaktif'] ?? 0 }}</h3>
                    <span class="kpi-sub">Diturunkan dari transaksi</span>
                </div>
                <div class="kpi-icon-wrap bg-gray-subtle text-gray">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU TABEL UTAMA (PO Style: Filter Integrated in Card Header) --}}
    <div class="table-card">
        <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <form action="{{ route('master.pajak.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 750px; width: 100%;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor kode atau nama pajak..." class="form-control" style="padding: 0.45rem 0.75rem; max-width: 280px; font-size: 0.85rem;">
                
                <select name="sifat" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 175px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">-- Semua Sifat --</option>
                    <option value="TAMBAH" {{ request('sifat') === 'TAMBAH' ? 'selected' : '' }}>Menambah (PPN)</option>
                    <option value="POTONG" {{ request('sifat') === 'POTONG' ? 'selected' : '' }}>Memotong (PPh)</option>
                </select>

                <select name="status" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 160px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem;">Filter</button>
                @if(request()->hasAny(['search', 'sifat', 'status']))
                    <a href="{{ route('master.pajak.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem;">Reset</a>
                @endif
            </form>

            <span style="color: #64748b; font-size: 0.85rem;">
                Total Pajak: <strong style="color: #0f172a;">{{ $pajakList->total() }}</strong> jenis
            </span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 120px;">Kode</th>
                        <th style="min-width: 220px;">Nama Pajak</th>
                        <th style="width: 100px; text-align: right;">Tarif (%)</th>
                        <th style="width: 130px; text-align: center;">Sifat Perlakuan</th>
                        <th style="min-width: 180px;">Akun Pembelian (Beli)</th>
                        <th style="min-width: 180px;">Akun Penjualan (Jual)</th>
                        <th style="width: 100px; text-align: center;">Status</th>
                        <th style="width: 100px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pajakList as $index => $item)
                        @php
                            $menuId = 'menuActionPajak_' . $item->pajak_id;
                        @endphp
                        <tr class="{{ $item->active_st ? '' : 'row-inactive' }}">
                            <td class="text-center text-muted font-mono font-xs">
                                {{ $pajakList->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="badge-code">{{ $item->pajak_cd }}</span>
                                @if($item->is_default)
                                    <span class="badge-sub-default" title="Pajak Default Sistem">Default</span>
                                @endif
                            </td>
                            <td>
                                <div class="pajak-name-wrap">
                                    <strong class="pajak-name">{{ $item->pajak_nm }}</strong>
                                    @if($item->keterangan)
                                        <span class="pajak-notes">{{ Str::limit($item->keterangan, 50) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">
                                <span class="tarif-display">{{ number_format($item->tarif_persen, 2, ',', '.') }}%</span>
                            </td>
                            <td>
                                @if($item->sifat_pajak === 'TAMBAH')
                                    <span class="badge-sifat badge-tambah">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Menambah</span>
                                    </span>
                                @else
                                    <span class="badge-sifat badge-potong">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                        <span>Memotong</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_pembelian && $item->coaPembelian)
                                    <div class="coa-cell">
                                        <span class="coa-cd font-mono font-xs">[{{ $item->coaPembelian->kode_akun }}]</span>
                                        <span class="coa-nm">{{ $item->coaPembelian->nama_akun }}</span>
                                    </div>
                                @elseif($item->is_pembelian)
                                    <span class="text-amber font-xs italic">Aktif (Belum Pilih COA)</span>
                                @else
                                    <span class="text-muted font-xs italic">- Nonaktif -</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_penjualan && $item->coaPenjualan)
                                    <div class="coa-cell">
                                        <span class="coa-cd font-mono font-xs">[{{ $item->coaPenjualan->kode_akun }}]</span>
                                        <span class="coa-nm">{{ $item->coaPenjualan->nama_akun }}</span>
                                    </div>
                                @elseif($item->is_penjualan)
                                    <span class="text-amber font-xs italic">Aktif (Belum Pilih COA)</span>
                                @else
                                    <span class="text-muted font-xs italic">- Nonaktif -</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->active_st)
                                    <span class="status-badge status-active">
                                        <span class="status-dot"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <span class="status-dot"></span>
                                        <span>Non-Aktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- Smart Action Dropdown (Wajib PO format) --}}
                                <div class="action-dropdown-wrapper">
                                    <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div id="{{ $menuId }}" class="action-dropdown-menu">
                                        {{-- Edit Action --}}
                                        <button type="button" class="action-dropdown-item" onclick="openEditPajakModal('{{ $item->pajak_id }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Edit Pajak</span>
                                        </button>

                                        {{-- Toggle Status Action --}}
                                        <button type="button" class="action-dropdown-item" onclick="openToggleStatusModal('{{ $item->pajak_id }}', '{{ $item->pajak_cd }}', '{{ addslashes($item->pajak_nm) }}', '{{ number_format($item->tarif_persen, 2, ',', '.') }}', {{ $item->active_st ? 1 : 0 }})">
                                            @if($item->active_st)
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-amber">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                <span>Nonaktifkan</span>
                                            @else
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-emerald">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Aktifkan Kembali</span>
                                            @endif
                                        </button>

                                        <div class="dropdown-divider"></div>

                                        {{-- Delete Action --}}
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeletePajakModal('{{ $item->pajak_id }}', '{{ $item->pajak_cd }}', '{{ addslashes($item->pajak_nm) }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span>Hapus Data</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state-cell">
                                <div class="empty-state-box">
                                    <div class="empty-icon-wrap">
                                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                    </div>
                                    <h4>Tidak Ada Data Pajak</h4>
                                    <p>Belum ada data pajak yang cocok dengan kriteria filter saat ini.</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="openModal('modalTambahPajak')">
                                        Tambah Pajak Pertama
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Pagination --}}
        @if($pajakList->hasPages())
            <div class="pagination-footer">
                <div class="pagination-info">
                    Menampilkan <strong>{{ $pajakList->firstItem() }}</strong> sampai <strong>{{ $pajakList->lastItem() }}</strong> dari <strong>{{ $pajakList->total() }}</strong> jenis pajak
                </div>
                <div class="pagination-links">
                    {{ $pajakList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Include Partial Modals --}}
@include('master_data.pajak.partials.modal-create')
@include('master_data.pajak.partials.modal-edit')
@include('master_data.pajak.partials.modal-toggle-status')
@include('master_data.pajak.partials.modal-delete')

@endsection

@push('scripts')
<script>
    window.pajakConfig = {
        routes: {
            index: "{{ route('master.pajak.index') }}",
            store: "{{ route('master.pajak.store') }}",
            base: "{{ url('master-pajak') }}"
        }
    };
</script>
<script src="{{ asset('js/master/pajak/pajak-index.js') }}?v={{ file_exists(public_path('js/master/pajak/pajak-index.js')) ? filemtime(public_path('js/master/pajak/pajak-index.js')) : time() }}"></script>
@endpush
