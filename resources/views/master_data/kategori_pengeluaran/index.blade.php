@extends('layouts.app')

@section('title', 'Master Kategori Kas Keluar - ERP Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/kategori_pengeluaran/kategori-pengeluaran.css') }}?v={{ file_exists(public_path('css/master/kategori_pengeluaran/kategori-pengeluaran.css')) ? filemtime(public_path('css/master/kategori_pengeluaran/kategori-pengeluaran.css')) : time() }}">
@endpush

@section('content')
<div class="kategori-page-container">
    {{-- Breadcrumb Navigation --}}
    <div class="breadcrumb-nav">
        <a href="{{ url('/') }}">Beranda</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('master.barang.index') }}">Master &amp; Sistem</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Kategori Kas Keluar</span>
    </div>

    {{-- Page Header (WaveDash Clean Enterprise Minimalism) --}}
    <div class="page-header-wrapper">
        <div>
            <h1 class="page-title">Master Kategori Kas Keluar</h1>
            <p class="page-description">
                Klasifikasi pos pengeluaran kas dan pemetaan default akun beban pembukuan untuk Bukti Kas Keluar (BKK).
            </p>
        </div>
        <div>
            @if(Auth::user()->canCreateMasterKategoriPengeluaran())
                <button type="button" class="btn-primary-dark" onclick="openModal('modalCreateKategori')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kategori</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="alert-content">
                <strong>Berhasil:</strong> {{ session('success') }}
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="alert-content">
                <strong>Gagal:</strong> {{ session('error') }}
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-warning">
            <div class="alert-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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

    {{-- Metric Cards Grid --}}
    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-info">
                <span class="metric-label">Total Kategori</span>
                <h3 class="metric-value">{{ $summary['total'] ?? 0 }}</h3>
                <span class="metric-sub">Pos pengeluaran terdaftar</span>
            </div>
            <div class="metric-icon-wrap icon-blue">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-info">
                <span class="metric-label">Kategori Aktif</span>
                <h3 class="metric-value">{{ $summary['aktif'] ?? 0 }}</h3>
                <span class="metric-sub">Siap digunakan pada bukti kas</span>
            </div>
            <div class="metric-icon-wrap icon-emerald">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-info">
                <span class="metric-label">Terhubung ke Akun COA</span>
                <h3 class="metric-value">{{ $summary['coa_mapped'] ?? 0 }}</h3>
                <span class="metric-sub">Otomatisasi jurnal beban aktif</span>
            </div>
            <div class="metric-icon-wrap icon-indigo">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Content Card & Table --}}
    <div class="content-card">
        {{-- Filter Toolbar --}}
        <div class="filter-toolbar">
            <form action="{{ route('master.kategori_pengeluaran.index') }}" method="GET" class="filter-form">
                <div class="search-input-wrapper">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Cari kode, nama, atau keterangan..." 
                        value="{{ request('search') }}" 
                        autocomplete="off">
                </div>

                <select name="status" class="select-filter">
                    <option value="SEMUA" {{ request('status') === 'SEMUA' ? 'selected' : '' }}>Semua Status</option>
                    <option value="AKTIF" {{ request('status') === 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                    <option value="NONAKTIF" {{ request('status') === 'NONAKTIF' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="btn-filter">Cari</button>

                @if(request()->hasAny(['search', 'status']) && (request('search') != '' || request('status') != 'SEMUA'))
                    <a href="{{ route('master.kategori_pengeluaran.index') }}" class="btn-reset">Reset</a>
                @endif
            </form>

            <span style="color: #64748b; font-size: 0.8125rem;">
                Total: <strong style="color: #0f172a;">{{ $kategoriList->total() }}</strong> kategori
            </span>
        </div>

        {{-- Data Table --}}
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">No</th>
                        <th style="width: 170px;">Kode Kategori</th>
                        <th style="min-width: 220px;">Nama Kategori</th>
                        <th style="min-width: 250px;">Akun Beban COA Default</th>
                        <th style="min-width: 200px;">Keterangan</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                        <th style="width: 90px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoriList as $index => $item)
                        @php
                            $menuId = 'menuActionKat_' . $item->kategori_id;
                        @endphp
                        <tr class="{{ $item->active_st ? '' : 'row-inactive' }}">
                            <td class="text-center text-muted font-mono" style="font-size: 0.75rem;">
                                {{ $kategoriList->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="badge-code">{{ $item->kode_kategori }}</span>
                            </td>
                            <td>
                                <div class="kategori-name-wrap">
                                    <span class="kategori-name">{{ $item->nama_kategori }}</span>
                                </div>
                            </td>
                            <td>
                                @if($item->coaDefault)
                                    <div class="coa-badge-wrap">
                                        <span class="coa-code">[{{ $item->coaDefault->kode_akun }}]</span>
                                        <span class="coa-name">{{ $item->coaDefault->nama_akun }}</span>
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic; font-size: 0.75rem;">
                                        Belum Ditentukan
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="color: #64748b; font-size: 0.8125rem;">
                                    {{ $item->keterangan ? Str::limit($item->keterangan, 60) : '-' }}
                                </span>
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
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- Smart Action Dropdown (Wajib Format PO ERP Mirasa) --}}
                                <div class="action-dropdown-wrapper">
                                    <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <div id="{{ $menuId }}" class="action-dropdown-menu">
                                        @if(Auth::user()->canEditMasterKategoriPengeluaran())
                                            {{-- Edit Action --}}
                                            <button type="button" class="action-dropdown-item" onclick="openEditKategoriModal('{{ $item->kategori_id }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span>Edit Kategori</span>
                                            </button>

                                            {{-- Toggle Status Action --}}
                                            <button type="button" class="action-dropdown-item" onclick="submitToggleStatus('{{ $item->kategori_id }}')">
                                                @if($item->active_st)
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d97706;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                    <span>Nonaktifkan</span>
                                                @else
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span>Aktifkan Kembali</span>
                                                @endif
                                            </button>
                                        @endif

                                        @if(Auth::user()->canDeleteMasterKategoriPengeluaran())
                                            <div class="dropdown-divider"></div>
                                            {{-- Delete Action --}}
                                            <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteKategoriModal('{{ $item->kategori_id }}', '{{ $item->kode_kategori }}', '{{ addslashes($item->nama_kategori) }}')">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Hapus Data</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <div class="empty-title">Belum Ada Kategori Kas Keluar</div>
                                    <p class="empty-text">
                                        @if(request()->hasAny(['search', 'status']))
                                            Tidak ditemukan kategori yang sesuai dengan kriteria pencarian filter.
                                        @else
                                            Klik tombol &ldquo;Tambah Kategori&rdquo; di atas untuk membuat kategori baru.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($kategoriList->hasPages())
            <div class="table-pagination-footer">
                <div class="pagination-info">
                    Menampilkan data {{ $kategoriList->firstItem() ?? 0 }} - {{ $kategoriList->lastItem() ?? 0 }} dari total {{ $kategoriList->total() }}
                </div>
                <div>
                    {{ $kategoriList->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Hidden Form untuk Toggle Status --}}
<form id="formToggleStatus" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

{{-- Modal Partials --}}
@include('master_data.kategori_pengeluaran.partials.modal-create')
@include('master_data.kategori_pengeluaran.partials.modal-edit')
@include('master_data.kategori_pengeluaran.partials.modal-delete')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            baseUrl: "{{ route('master.kategori_pengeluaran.index') }}"
        };
    </script>
    <script src="{{ asset('js/master/kategori_pengeluaran/kategori-pengeluaran.js') }}?v={{ file_exists(public_path('js/master/kategori_pengeluaran/kategori-pengeluaran.js')) ? filemtime(public_path('js/master/kategori_pengeluaran/kategori-pengeluaran.js')) : time() }}"></script>
@endpush
