@extends('layouts.app')

@section('title', 'Master Data Customer - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/customer/customer-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Customer</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data klien B2B, mitra pembeli seperti PT Indofood, dan jaringan distributor.</p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterCustomer())
            <button type="button" onclick="openModal('modalTambahCustomer')" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Customer Baru
            </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.customer.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode, nama, atau kontak..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.customer.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $customers->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 45px;">No</th>
                    <th>Kode Customer</th>
                    <th>Nama Customer</th>
                    <th>Kategori</th>
                    <th style="min-width: 170px;">Alamat</th>
                    <th>TOP</th>
                    <th>Format Faktur / PPN</th>
                    <th>Kontak PIC</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $index => $item)
                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7; font-size: 0.95rem;">{{ $item->customer_cd }}</strong></td>
                        <td style="font-weight: 600;">
                            <div>{{ $item->customer_nm }}</div>
                            @if(!empty($item->kode_pelanggan))
                                <div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">Ref: {{ $item->kode_pelanggan }}</div>
                            @endif
                        </td>
                        <td>
                            @if($item->klasifikasi === 'IFM')
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">IFM (Indofood)</span>
                            @elseif($item->klasifikasi === 'MIDI_ALFA' || $item->klasifikasi === 'IDM_ALFA')
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">Midi / Alfa</span>
                            @elseif($item->klasifikasi === 'IDM')
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">IDM</span>
                            @else
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">MANUAL</span>
                            @endif
                        </td>
                        <td style="font-size: 0.8125rem; color: #334155; line-height: 1.4; max-width: 250px;">
                            @if(!empty($item->alamat_txt))
                                <div style="font-weight: 500;">{{ $item->alamat_txt }}</div>
                            @elseif(!empty($item->wilayah))
                                <div style="color: #64748b; font-style: italic;">{{ $item->wilayah }}</div>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($item->top_txt))
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; background: #fef3c7; color: #92400e; font-weight: 600;">{{ $item->top_txt }}</span>
                            @else
                                <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 500;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->klasifikasi === 'IFM')
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; background: #e0f2fe; color: #0369a1; font-weight: 600;">Format MFI (Pabrik)</span>
                            @elseif(in_array($item->klasifikasi, ['IDM', 'MIDI_ALFA', 'IDM_ALFA']))
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; background: #f3e8ff; color: #7e22ce; font-weight: 600;">Format Modern Trade (MFI)</span>
                            @elseif($item->default_ppn === 'PPN_11')
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; background: #dcfce7; color: #15803d; font-weight: 600;">PPN (PM.A)</span>
                            @else
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; background: #f1f5f9; color: #64748b; font-weight: 600;">Non-PPN (PM.B)</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($item->kontak_no))
                                <span>{{ $item->kontak_no }}</span>
                            @else
                                <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterCustomer() || Auth::user()?->canDeleteMasterCustomer())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->customer_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->customer_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterCustomer())
                                        <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="closeAllActionDropdowns(); editCustomer({{ $item->customer_id }}, '{{ addslashes($item->customer_cd) }}', '{{ addslashes($item->customer_nm) }}', '{{ addslashes($item->kontak_no ?? '') }}', '{{ addslashes(str_replace(["\r\n", "\r", "\n"], "\\n", $item->alamat_txt ?? '')) }}', '{{ addslashes($item->klasifikasi ?? 'MANUAL') }}', '{{ addslashes($item->wilayah ?? '') }}', '{{ addslashes($item->kode_pelanggan ?? '') }}', '{{ addslashes($item->top_txt ?? '') }}', '{{ addslashes($item->default_ppn ?? 'NON_PPN') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Data Customer</span>
                                        </button>
                                    @endif

                                    @if (Auth::user()?->canEditMasterCustomer() && Auth::user()?->canDeleteMasterCustomer())
                                        <div class="action-dropdown-divider"></div>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterCustomer())
                                        <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="closeAllActionDropdowns(); openDeleteCustomerModal({{ $item->customer_id }}, '{{ addslashes($item->customer_cd) }}', '{{ addslashes($item->customer_nm) }}')">
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
                        <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data customer. Klik tombol <strong>"Tambah Customer Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $customers->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.customer.partials.modal-create')
@include('master_data.customer.partials.modal-edit')
@include('master_data.customer.partials.modal-delete')

@push('scripts')
<script>
    window.masterCustomerConfig = {
        baseUrl: "{{ url('master-customer') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/master/customer/customer-index.js') }}"></script>
@endpush
@endsection
