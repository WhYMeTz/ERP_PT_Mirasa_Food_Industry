@extends('layouts.app')

@section('title', 'Entri Cepat Jurnal Umum Manual - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/jurnal/jurnal-create.css') }}?v={{ filemtime(public_path('css/keuangan/jurnal/jurnal-create.css')) }}">
@endpush

@section('content')
<div class="jurnal-create-container">
    {{-- NAVIGASI BREADCRUMB & HEADER --}}
    <div>
        <a href="{{ route('keuangan.jurnal.index') }}" 
           style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem; font-weight: 600;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Jurnal Umum</span>
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <span>⚡ Entri Cepat Jurnal Umum Manual</span>
                </h1>
                <p style="color: #64748b; font-size: 0.8125rem; margin-top: 0.2rem; margin-bottom: 0;">
                    Pencatatan cepat upah kemas borongan, tagihan listrik, servis mesin, mutasi kas/bank, dan penyesuaian akuntansi.
                </p>
            </div>
        </div>
    </div>

    {{-- TEMPLATE PRESET BAR CEPAT --}}
    <div class="quick-template-bar">
        <span class="quick-template-label">
            <span>⚡ Template Cepat:</span>
        </span>
        <button type="button" class="btn-template-pill" onclick="applyQuickTemplate('upah_kemas')" title="Isi otomatis pasangan akun Upah Kemas & Kas">
            👷 Upah Tenaga Kerja Kemas
        </button>
        <button type="button" class="btn-template-pill" onclick="applyQuickTemplate('listrik_air')" title="Isi otomatis tagihan listrik PLN">
            ⚡ Listrik &amp; Air PLN
        </button>
        <button type="button" class="btn-template-pill" onclick="applyQuickTemplate('servis_mesin')" title="Isi otomatis servis fryer / sparepart">
            🔧 Pemeliharaan Mesin
        </button>
        <button type="button" class="btn-template-pill" onclick="applyQuickTemplate('tarik_tunai')" title="Isi otomatis mutasi Bank ke Kas Kecil">
            🏦 Tarik Tunai Bank &rarr; Kas
        </button>
        <span style="font-size: 0.725rem; color: #94a3b8; margin-left: auto;">
            💡 Tip: Tekan <strong>Enter</strong> di kolom nominal untuk berpindah atau menambah baris baru
        </span>
    </div>

    @if (session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; font-size: 0.875rem; font-weight: 600;">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; font-size: 0.85rem;">
            <strong style="display: block; margin-bottom: 0.35rem;">Harap periksa kembali isian formulir:</strong>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('keuangan.jurnal.store') }}" method="POST" id="formJurnalManual">
        @csrf

        {{-- CARD 1: INFORMASI HEADER JURNAL (COMPACT GRID) --}}
        <div class="jurnal-card">
            <div class="jurnal-card-header">
                <strong style="color: #0f172a; font-size: 0.875rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>1. Parameter Dokumen Transaksi</span>
                </strong>
                <span style="font-size: 0.725rem; color: #64748b; font-family: monospace;">
                    Nomor Jurnal otomatis: JRN-{{ date('Ym') }}-XXXX
                </span>
            </div>
            <div class="jurnal-card-body">
                <div class="jurnal-grid-header">
                    <div class="form-group-jurnal">
                        <label class="jurnal-label" for="tgl_jurnal">
                            <span>Tanggal</span>
                            <span class="required">*</span>
                        </label>
                        <input type="date" 
                               name="tgl_jurnal" 
                               id="tgl_jurnal" 
                               class="jurnal-input" 
                               value="{{ old('tgl_jurnal', date('Y-m-d')) }}" 
                               required>
                    </div>

                    <div class="form-group-jurnal">
                        <label class="jurnal-label" for="tipe_transaksi">
                            <span>Kategori</span>
                            <span class="required">*</span>
                        </label>
                        <select name="tipe_transaksi" id="tipe_transaksi" class="jurnal-select" required>
                            @foreach ($tipeList as $key => $lbl)
                                <option value="{{ $key }}" {{ old('tipe_transaksi', 'JURNAL_UMUM') === $key ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group-jurnal">
                        <label class="jurnal-label" for="ref_no">
                            <span>No. Ref / Bukti</span>
                        </label>
                        <input type="text" 
                               name="ref_no" 
                               id="ref_no" 
                               class="jurnal-input" 
                               placeholder="BKK, Kuitansi, Slip..." 
                               value="{{ old('ref_no') }}">
                    </div>

                    <div class="form-group-jurnal">
                        <label class="jurnal-label" for="keterangan">
                            <span>Deskripsi / Keterangan Transaksi</span>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="keterangan" 
                               id="keterangan" 
                               class="jurnal-input" 
                               placeholder="Tuliskan tujuan pencatatan jurnal (misal: Pembayaran upah borongan kemas keripik minggu ke-2)..." 
                               value="{{ old('keterangan') }}" 
                               required>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: TABEL DOUBLE ENTRY MULTI-BARIS --}}
        <div class="jurnal-card" style="margin-top: 1.15rem;">
            <div class="jurnal-card-header">
                <strong style="color: #0f172a; font-size: 0.875rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>2. Rincian Akun Berpasangan (Debet &amp; Kredit)</span>
                </strong>
                <span style="font-size: 0.725rem; color: #64748b;">
                    Klik tombol ⚖️ di baris untuk mengisi otomatis nilai penyeimbang selisih
                </span>
            </div>
            <div class="jurnal-card-body">
                <div class="table-entries-wrap">
                    <table class="table-entries">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="min-width: 320px;">Akun Bagan (COA) <span style="color: #dc2626;">*</span></th>
                                <th style="min-width: 200px;">Keterangan Baris</th>
                                <th style="width: 180px; text-align: right;">Debet (Rp)</th>
                                <th style="width: 180px; text-align: right;">Kredit (Rp)</th>
                                <th style="width: 75px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="entriesTableBody">
                            @php
                                $oldEntries = old('entries', [
                                    ['coa_id' => '', 'keterangan' => '', 'debet' => '', 'kredit' => ''],
                                    ['coa_id' => '', 'keterangan' => '', 'debet' => '', 'kredit' => ''],
                                ]);
                            @endphp

                            @foreach ($oldEntries as $idx => $entry)
                                @php
                                    $selectedCoa = !empty($entry['coa_id']) ? $coaList->firstWhere('coa_id', $entry['coa_id']) : null;
                                @endphp
                                <tr class="entry-row" data-row-idx="{{ $idx }}">
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">
                                        <span class="row-number">{{ $idx + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="coa-cell-wrapper">
                                            <input type="hidden" name="entries[{{ $idx }}][coa_id]" class="input-coa-id" value="{{ $entry['coa_id'] ?? '' }}" required>
                                            <button type="button" 
                                                    class="btn-pick-coa" 
                                                    onclick="openCoaPickerForIndex({{ $idx }})" 
                                                    title="Klik untuk memilih akun COA">
                                                <span class="coa-display-code" style="{{ $selectedCoa ? '' : 'display: none;' }}">
                                                    {{ $selectedCoa ? $selectedCoa->kode_akun : '-' }}
                                                </span>
                                                <span class="coa-display-name" style="color: {{ $selectedCoa ? '#0f172a' : '#94a3b8' }};">
                                                    {{ $selectedCoa ? $selectedCoa->nama_akun : '-- Klik untuk Pilih Akun --' }}
                                                </span>
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-left: auto; color: #94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="entries[{{ $idx }}][keterangan]" 
                                               class="jurnal-input input-keterangan" 
                                               placeholder="Keterangan baris..." 
                                               value="{{ $entry['keterangan'] ?? '' }}">
                                    </td>
                                    <td>
                                        <div class="input-currency-group">
                                            <span class="prefix-rp">Rp</span>
                                            <input type="number" 
                                                   name="entries[{{ $idx }}][debet]" 
                                                   class="jurnal-input input-currency input-debet" 
                                                   step="any" 
                                                   min="0" 
                                                   placeholder="0" 
                                                   value="{{ $entry['debet'] ?? '' }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-currency-group">
                                            <span class="prefix-rp">Rp</span>
                                            <input type="number" 
                                                   name="entries[{{ $idx }}][kredit]" 
                                                   class="jurnal-input input-currency input-kredit" 
                                                   step="any" 
                                                   min="0" 
                                                   placeholder="0" 
                                                   value="{{ $entry['kredit'] ?? '' }}">
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                                            <button type="button" 
                                                    class="btn-row-action btn-balance-helper" 
                                                    onclick="balanceThisRow({{ $idx }})" 
                                                    title="Isi otomatis nilai penyeimbang selisih (Auto Balance)">
                                                ⚖️
                                            </button>
                                            <button type="button" 
                                                    class="btn-row-action btn-remove-row" 
                                                    onclick="removeEntryRow(this)" 
                                                    {{ count($oldEntries) <= 2 ? 'disabled' : '' }}
                                                    title="Hapus baris transaksi">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: 800; color: #1e293b; text-transform: uppercase;">
                                    Total Akumulasi:
                                </td>
                                <td style="text-align: right; font-weight: 800; font-family: monospace; color: #15803d; font-size: 0.95rem;" id="lblTotalDebet">
                                    Rp 0
                                </td>
                                <td style="text-align: right; font-weight: 800; font-family: monospace; color: #7e22ce; font-size: 0.95rem;" id="lblTotalKredit">
                                    Rp 0
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="table-footer-toolbar">
                    <button type="button" class="btn-add-row" onclick="addEntryRow()">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris Akun (Alt + N)</span>
                    </button>
                    <span style="font-size: 0.75rem; color: #64748b;">
                        Gunakan tombol <strong>Tab</strong> atau <strong>Enter</strong> untuk berpindah antar kolom input
                    </span>
                </div>
            </div>
        </div>

        {{-- STICKY BOTTOM BALANCE & ACTION BAR --}}
        <div class="bottom-sticky-bar unbalanced" id="bottomStickyBar">
            <div class="balance-metric-group">
                <div class="metric-stat-box">
                    <span class="metric-stat-label">Total Debet</span>
                    <span class="metric-stat-val" id="stickyTotalDebet" style="color: #15803d;">Rp 0</span>
                </div>
                <div class="metric-stat-box">
                    <span class="metric-stat-label">Total Kredit</span>
                    <span class="metric-stat-val" id="stickyTotalKredit" style="color: #7e22ce;">Rp 0</span>
                </div>
                <div class="metric-stat-box">
                    <span class="metric-stat-label">Selisih Keseimbangan</span>
                    <span class="metric-stat-val" id="stickySelisih" style="color: #dc2626;">Rp 0</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div id="stickyBalanceBadge" class="balance-status-badge badge-unbalanced">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Belum Diisi</span>
                </div>

                <a href="{{ route('keuangan.jurnal.index') }}" 
                   class="btn btn-secondary" 
                   style="font-size: 0.825rem; font-weight: 700; padding: 0.6rem 1.15rem;">
                    Batal
                </a>

                <button type="submit" 
                        id="btnSubmitJurnal" 
                        class="btn-submit-jurnal" 
                        disabled>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Bukukan Jurnal</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- MODAL SEARCH & PICKER COA --}}
@include('keuangan.jurnal.partials.modal-coa-picker')

{{-- TEMPLATE BARIS DUKUNGAN ROW BARU --}}
@include('keuangan.jurnal.partials.entry-row-template')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/jurnal/jurnal-create.js') }}?v={{ filemtime(public_path('js/keuangan/jurnal/jurnal-create.js')) }}"></script>
@endpush
