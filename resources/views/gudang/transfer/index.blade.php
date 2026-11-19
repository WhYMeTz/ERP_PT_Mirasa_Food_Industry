@extends('layouts.app')

@section('title', 'Transfer Antar-Gudang - ERP PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/transfer/transfer-index.css') }}">
@endpush

@section('content')
<div class="transfer-full-container">
    {{-- TOP ACTION BAR --}}
    <div class="transfer-header-bar">
        <div>
            <div class="transfer-header-title">
                <h1>Transfer Antar-Gudang</h1>
                <span style="background: #f0fdfa; color: #0f766e; border: 1.5px solid #99f6e4; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px;">
                    Mutasi Fisik Stok
                </span>
            </div>
            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">
                Pemindahan fisik bahan baku, kemasan, atau produk antar-lokasi simpan (Gudang Pusat, Gudang Cabang, Sortir &amp; Produksi).
            </p>
        </div>

        <div>
            <a href="{{ route('gudang.transfer.create') }}" class="btn btn-primary" style="background: #0d9488; border-color: #0f766e; font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1rem; border-radius: 6px; color: #ffffff; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Transfer Baru</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- FILTER FORM --}}
    <div class="transfer-filter-card">
        <form method="GET" action="{{ route('gudang.transfer.index') }}">
            <div class="transfer-filter-grid">
                <div>
                    <label class="transfer-filter-label">Pencarian Kata Kunci</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="transfer-filter-input" placeholder="No. Transfer / Surat Jalan / Sopir / Nopol...">
                </div>
                <div>
                    <label class="transfer-filter-label">Gudang Asal</label>
                    <select name="gudang_asal_id" class="transfer-filter-select">
                        <option value="">-- Semua Asal --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ ($filters['gudang_asal_id'] ?? '') == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="transfer-filter-label">Gudang Tujuan</label>
                    <select name="gudang_tujuan_id" class="transfer-filter-select">
                        <option value="">-- Semua Tujuan --</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ ($filters['gudang_tujuan_id'] ?? '') == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="transfer-filter-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="transfer-filter-input">
                </div>
                <div>
                    <label class="transfer-filter-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="transfer-filter-input">
                </div>
                <div style="display: flex; gap: 0.35rem;">
                    <button type="submit" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 0.85rem;">
                        Filter
                    </button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('gudang.transfer.index') }}" class="btn" style="background: #f1f5f9; color: #64748b; font-size: 0.8125rem; font-weight: 600; padding: 0.5rem 0.75rem; text-decoration: none;">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- DATA TABLE --}}
    <div class="transfer-table-card">
        <div class="transfer-table-responsive">
            <table class="transfer-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 14%;">No. Transfer</th>
                        <th style="width: 11%;">Tanggal</th>
                        <th style="width: 18%;">Gudang Asal</th>
                        <th style="width: 18%;">Gudang Tujuan</th>
                        <th style="width: 10%; text-align: right;">Total Item</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                        <th style="width: 8%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $idx => $row)
                        @php $menuId = 'actionMenuTransfer_' . $row->transfer_id; @endphp
                        <tr>
                            <td>{{ $transfers->firstItem() + $idx }}</td>
                            <td>
                                <a href="{{ route('gudang.transfer.show', $row->transfer_id) }}" style="font-family: monospace; font-weight: 800; color: #0d9488; text-decoration: none;">
                                    {{ $row->transfer_no }}
                                </a>
                                @if($row->no_surat_jalan)
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                        SJ: {{ $row->no_surat_jalan }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ Carbon\Carbon::parse($row->transfer_tgl)->format('d/m/Y') }}</td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $row->gudangAsal?->gudang_nm ?? '-' }}
                                </div>
                                <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">
                                    [{{ $row->gudangAsal?->gudang_cd ?? '-' }}]
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0369a1;">
                                    {{ $row->gudangTujuan?->gudang_nm ?? '-' }}
                                </div>
                                <span style="font-size: 0.7rem; color: #0284c7; font-family: monospace;">
                                    [{{ $row->gudangTujuan?->gudang_cd ?? '-' }}]
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace;">
                                {{ number_format($row->total_qty, 1, ',', '.') }}
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: normal;">
                                    {{ $row->details->count() }} Baris
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($row->isPosted())
                                    <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                                        POSTED
                                    </span>
                                @else
                                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 4px;">
                                        VOID
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center; position: relative;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.transfer.show', $row->transfer_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Dokumen</span>
                                    </a>
                                    @if($row->isPosted())
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openVoidTransferModal({{ $row->transfer_id }}, '{{ $row->transfer_no }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Batalkan (VOID)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    <span style="font-weight: 600; color: #64748b;">Belum ada dokumen transfer antar-gudang</span>
                                    <span style="font-size: 0.775rem;">Klik tombol "Catat Transfer Baru" untuk memindahkan stok fisik antar-gudang.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0;">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>

@include('gudang.transfer.partials.modal-void')
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/transfer/transfer-index.js') }}"></script>
@endpush
