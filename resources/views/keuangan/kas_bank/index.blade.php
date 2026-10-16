@extends('layouts.app')

@section('title', 'Master Kas & Rekening Bank - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_bank/kas-bank-index.css') }}">
@endpush

@section('content')
{{-- Page Header --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Master Kas &amp; Rekening Bank</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Kelola rekening operasional perusahaan, kas kecil pabrik, transfer internal serta mutasi saldo.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('keuangan.kas_bank.mutasi') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>Buku Mutasi Kas &amp; Bank</span>
        </a>

        @if (Auth::user()->canManageKasBank())
            <button type="button" class="btn" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 700; font-size: 0.8125rem;" onclick="openTransferModal()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Transfer Antar Rekening</span>
            </button>

            <button type="button" class="btn btn-primary" style="font-weight: 700; font-size: 0.8125rem; background: #059669; border-color: #059669;" onclick="openModal('modalCreateKasBank')">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Akun Baru</span>
            </button>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (INFORMATIF & SERAGAM DENGAN PO & TERIMA BARANG) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- TOTAL LIKUIDITAS SALDO --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Total Likuiditas Saldo
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format($totalSaldo, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            {{ $totalAkun }} Akun rekening aktif terdaftar
        </div>
    </div>

    {{-- SALDO REKENING BANK --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7;">
                    Saldo Rekening Bank
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0284c7; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format($totalBank, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total tabungan &amp; giro perbankan
        </div>
    </div>

    {{-- SALDO KAS FISIK (TUNAI) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #d97706;">
                    Saldo Kas Fisik (Tunai)
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #d97706; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format($totalKas, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Brankas &amp; kas kecil pabrik
        </div>
    </div>

    {{-- MUTASI HARI INI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                    Mutasi Hari Ini
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($mutasiHariIni) }} Transaksi
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Terbukukan tanggal {{ date('d M Y') }}
        </div>
    </div>
</div>

{{-- Section: Data Table View --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Daftar Lengkap Master Rekening &amp; Kas</span>
        </h3>
        <span style="color: #64748b; font-size: 0.85rem;">
            Total Akun: <strong style="color: #0f172a;">{{ $kasBanks->count() }}</strong> rekening
        </span>
    </div>

    <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em;">
                    <th style="width: 45px; text-align: center; padding: 0.85rem 0.5rem;">No</th>
                    <th style="min-width: 110px; padding: 0.85rem 0.75rem;">Kode Akun</th>
                    <th style="min-width: 170px; padding: 0.85rem 0.75rem;">Nama Akun</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem;">Tipe</th>
                    <th style="min-width: 160px; padding: 0.85rem 0.75rem;">Nama Bank / Rekening</th>
                    <th style="min-width: 140px; padding: 0.85rem 0.75rem;">Atas Nama</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem; text-align: right;">Saldo Awal</th>
                    <th style="min-width: 130px; padding: 0.85rem 0.75rem; text-align: right;">Saldo Berjalan</th>
                    <th style="width: 90px; padding: 0.85rem 1rem; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.8125rem; color: #1e293b;">
                @forelse ($kasBanks as $index => $item)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 0.75rem 0.5rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                            {{ $index + 1 }}
                        </td>
                        <td style="padding: 0.75rem 0.75rem; font-weight: 700; font-family: monospace; color: #0f172a;">
                            {{ $item->kode_akun }}
                        </td>
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0f172a;">{{ $item->nama_akun }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                Valuta: {{ $item->mata_uang }} &bull; Update: {{ date('d/m/Y H:i', strtotime($item->updated_dt ?? $item->created_dt)) }}
                            </div>
                        </td>
                        <td>
                            @if ($item->tipe_akun === 'KAS')
                                <span class="kb-acc-badge kb-badge-kas">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Kas Tunai</span>
                                </span>
                            @else
                                <span class="kb-acc-badge kb-badge-bank">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Rekening Bank</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($item->tipe_akun === 'BANK')
                                <div style="font-weight: 700; color: #0f172a;">{{ $item->bank_nama }}</div>
                                <div style="font-size: 0.75rem; font-family: monospace; color: #64748b;">{{ $item->nomor_rekening }}</div>
                            @else
                                <span style="color: #64748b; font-size: 0.8rem; font-weight: 600;">Kas Tunai / Pabrik</span>
                            @endif
                        </td>
                        <td>
                            {{ $item->atas_nama ?: '-' }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: #64748b;">
                            Rp {{ number_format($item->saldo_awal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.95rem; font-weight: 800; color: #059669;">
                            Rp {{ number_format($item->saldo_berjalan, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @php
                                $menuId = 'menu-action-kb-' . $item->kas_bank_id;
                            @endphp

                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('keuangan.kas_bank.mutasi', ['kas_bank_id' => $item->kas_bank_id]) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    <span>Lihat Buku Mutasi</span>
                                </a>

                                @if (Auth::user()->canManageKasBank())
                                    <button type="button" 
                                            class="action-dropdown-item" 
                                            data-account="{{ json_encode($item) }}"
                                            onclick="openModalPenyesuaian(this)">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Penyesuaian Saldo</span>
                                    </button>

                                    <button type="button" 
                                            class="action-dropdown-item" 
                                            data-account="{{ json_encode($item) }}"
                                            onclick="openModalEdit(this)">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit Akun</span>
                                    </button>

                                    <div class="action-dropdown-divider"></div>

                                    <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="openModalDelete('{{ $item->kas_bank_id }}', '{{ addslashes($item->nama_akun) }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus / Nonaktifkan</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 2rem;">
                            Belum ada data rekening kas &amp; bank.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Include All Enterprise Modals (Strict Separation of Concerns) --}}
@include('keuangan.kas_bank.partials.modal-create')
@include('keuangan.kas_bank.partials.modal-edit')
@include('keuangan.kas_bank.partials.modal-transfer')
@include('keuangan.kas_bank.partials.modal-penyesuaian')
@include('keuangan.kas_bank.partials.modal-delete')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            baseUrl: '{{ url('') }}'
        };
    </script>
    <script src="{{ asset('js/keuangan/kas_bank/kas-bank-index.js') }}"></script>
@endpush
