@extends('layouts.app')

@section('title', 'Penerimaan Kas & Bank (Bukti Kas Masuk - BKM)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_masuk/kas-masuk-index.css') }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                📥 Bukti Kas Masuk (BKM)
            </h1>
            <span style="background: #f0fdf4; color: #166534; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">
                Cash &amp; Bank Receipt
            </span>
        </div>
        <p style="font-size: 0.825rem; color: #64748b; margin: 0.25rem 0 0 0;">
            Pencatatan uang masuk operasional pabrik (penjualan limbah minyak/kulit singkong, modal owner, piutang customer, dll).
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('keuangan.kas_bank.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 0.95rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>🏦 Saldo Kas &amp; Bank</span>
        </a>

        @if (Auth::user()->canCreateKasMasuk())
            <a href="{{ route('keuangan.kas_masuk.create') }}" class="btn btn-primary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1.15rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25); background: #059669; border-color: #059669;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Catat Kas Masuk (BKM)</span>
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- TOTAL KAS MASUK BULAN INI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Kas Masuk Bulan Ini
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format($kpi['total_bulan_ini'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            {{ $kpi['count_transaksi'] ?? 0 }} transaksi berhasil dibukukan
        </div>
    </div>

    {{-- PENJUALAN LIMBAH (JELANTAH & KULIT) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #047857;">
                    Penjualan Limbah / By-Product
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #047857; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format($kpi['total_limbah'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; color: #047857;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Minyak jelantah, kulit singkong &amp; ampas
        </div>
    </div>

    {{-- SETORAN MODAL OWNER --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #7e22ce;">
                    Setoran Modal Owner
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #7e22ce; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format($kpi['total_modal'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #faf5ff; display: flex; align-items: center; justify-content: center; color: #7e22ce;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Injeksi modal kerja operasional pabrik
        </div>
    </div>

    {{-- PENJUALAN TUNAI & LAIN-LAIN --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #2563eb;">
                    Penjualan Tunai / Lainnya
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #2563eb; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    Rp {{ number_format(($kpi['total_tunai'] ?? 0) + ($kpi['total_lain'] ?? 0), 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #2563eb;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Remah keripik, eceran &amp; pendapatan jasa
        </div>
    </div>
</div>

{{-- DATA TABEL KAS MASUK (BKM) --}}
<div class="card">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <form method="GET" action="{{ route('keuangan.kas_masuk.index') }}" id="filterForm" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; flex: 1; max-width: 950px;">
            <input type="text" 
                   name="search" 
                   class="form-control" 
                   placeholder="Cari No. BKM / Penyetor / Ref..." 
                   value="{{ $filters['search'] ?? '' }}" 
                   style="padding: 0.45rem 0.75rem; max-width: 240px; font-size: 0.85rem; height: 36px;">

            <select name="kategori" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 185px; font-size: 0.85rem; height: 36px;" onchange="this.form.submit()">
                <option value="">-- Semua Kategori --</option>
                <option value="PENJUALAN_LIMBAH" {{ ($filters['kategori'] ?? '') === 'PENJUALAN_LIMBAH' ? 'selected' : '' }}>♻️ Penjualan Limbah</option>
                <option value="PENJUALAN_TUNAI" {{ ($filters['kategori'] ?? '') === 'PENJUALAN_TUNAI' ? 'selected' : '' }}>💵 Penjualan Tunai</option>
                <option value="SETORAN_MODAL" {{ ($filters['kategori'] ?? '') === 'SETORAN_MODAL' ? 'selected' : '' }}>💼 Setoran Modal</option>
                <option value="PELUNASAN_PIUTANG" {{ ($filters['kategori'] ?? '') === 'PELUNASAN_PIUTANG' ? 'selected' : '' }}>🤝 Pelunasan Piutang</option>
                <option value="PENDAPATAN_LAIN" {{ ($filters['kategori'] ?? '') === 'PENDAPATAN_LAIN' ? 'selected' : '' }}>📦 Pendapatan Lain</option>
            </select>

            <select name="kas_bank_id" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 180px; font-size: 0.85rem; height: 36px;" onchange="this.form.submit()">
                <option value="">-- Rekening Tujuan --</option>
                @foreach ($kasBanks as $kb)
                    <option value="{{ $kb->kas_bank_id }}" {{ ($filters['kas_bank_id'] ?? '') == $kb->kas_bank_id ? 'selected' : '' }}>
                        {{ $kb->kas_bank_nm }}
                    </option>
                @endforeach
            </select>

            <select name="metode_terima" class="form-control" style="padding: 0.45rem 0.75rem; max-width: 140px; font-size: 0.85rem; height: 36px;" onchange="this.form.submit()">
                <option value="">-- Metode --</option>
                <option value="CASH" {{ ($filters['metode_terima'] ?? '') === 'CASH' ? 'selected' : '' }}>💵 Tunai (Cash)</option>
                <option value="TRANSFER" {{ ($filters['metode_terima'] ?? '') === 'TRANSFER' ? 'selected' : '' }}>🏦 Transfer</option>
                <option value="GIRO_CEK" {{ ($filters['metode_terima'] ?? '') === 'GIRO_CEK' ? 'selected' : '' }}>📄 Giro / Cek</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem; height: 36px;">Filter</button>
            @if (!empty($filters['kategori']) || !empty($filters['kas_bank_id']) || !empty($filters['metode_terima']) || !empty($filters['search']))
                <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn btn-secondary btn-sm" title="Reset Filter" style="padding: 0.45rem 0.65rem; height: 36px;">Reset</a>
            @endif
        </form>

        <span style="color: #64748b; font-size: 0.85rem; white-space: nowrap;">
            Total Data: <strong style="color: #0f172a;">{{ $bkmList->total() }}</strong> voucher
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em;">
                    <th style="width: 45px; text-align: center; padding: 0.85rem 0.5rem;">No</th>
                    <th style="min-width: 150px; padding: 0.85rem 0.75rem;">No. BKM &amp; Tgl</th>
                    <th style="min-width: 170px; padding: 0.85rem 0.75rem;">Penyetor / Dari</th>
                    <th style="min-width: 160px; padding: 0.85rem 0.75rem;">Rekening Tujuan</th>
                    <th style="min-width: 140px; padding: 0.85rem 0.75rem;">Kategori Penerimaan</th>
                    <th style="min-width: 130px; padding: 0.85rem 0.75rem;">Metode &amp; Ref</th>
                    <th style="min-width: 150px; text-align: right; padding: 0.85rem 0.75rem;">Total Diterima (Rp)</th>
                    <th style="width: 95px; text-align: center; padding: 0.85rem 0.5rem;">Status</th>
                    <th style="width: 80px; text-align: center; padding: 0.85rem 0.5rem;">Aksi</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.85rem; color: #334155;">
                @forelse ($bkmList as $index => $bkm)
                    @php
                        $isVoid = ($bkm->status_cd === 'VOID');
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" class="{{ $isVoid ? 'row-void' : '' }}">
                        {{-- NO URUT TABEL --}}
                        <td style="text-align: center; padding: 0.75rem 0.5rem; color: #64748b; font-weight: 600;">
                            {{ $bkmList->firstItem() + $index }}
                        </td>

                        {{-- NO BKM & TGL --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}" style="color: #059669; text-decoration: none;">
                                    {{ $bkm->bkm_no }}
                                </a>
                            </div>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                📅 {{ \Carbon\Carbon::parse($bkm->tgl_terima)->format('d/m/Y') }}
                            </div>
                        </td>

                        {{-- PENYETOR / DARI --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0f172a;">
                                {{ $bkm->penyetor_nama ?: '-' }}
                            </div>
                            @if ($bkm->customer)
                                <div style="font-size: 0.725rem; color: #2563eb; margin-top: 2px;">
                                    🏢 {{ $bkm->customer->customer_nm }}
                                </div>
                            @endif
                        </td>

                        {{-- REKENING TUJUAN --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0f172a;">
                                {{ $bkm->kasBank->kas_bank_nm ?? '-' }}
                            </div>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                {{ $bkm->kasBank && $bkm->kasBank->nomor_rekening ? 'Rek. ' . $bkm->kasBank->nomor_rekening : 'Kas Fisik Kantor' }}
                            </div>
                        </td>

                        {{-- KATEGORI --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            @php
                                $badge = $bkm->kategori_badge;
                            @endphp
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.725rem; font-weight: 700; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                {{ $badge['label'] }}
                            </span>
                        </td>

                        {{-- METODE & REF --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.725rem; font-weight: 700; background: #f1f5f9; color: #475569;">
                                {{ $bkm->metode_terima }}
                            </span>
                            @if ($bkm->no_referensi_bank)
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 3px; font-family: ui-monospace, SFMono-Regular, monospace;">
                                    Ref: {{ $bkm->no_referensi_bank }}
                                </div>
                            @endif
                        </td>

                        {{-- TOTAL MASUK (RP) --}}
                        <td style="padding: 0.75rem 0.75rem; text-align: right;">
                            <div style="font-weight: 800; color: #059669; font-size: 0.95rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}
                            </div>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                {{ $bkm->details->count() }} item rincian
                            </div>
                        </td>

                        {{-- STATUS --}}
                        <td style="padding: 0.75rem 0.5rem; text-align: center;">
                            @if ($isVoid)
                                <span class="badge-status-void">
                                    VOID
                                </span>
                            @else
                                <span class="badge-status-posted">
                                    POSTED
                                </span>
                            @endif
                        </td>

                        {{-- AKSI (SMART ACTION DROPDOWN) --}}
                        <td style="padding: 0.75rem 0.5rem; text-align: center; position: relative;">
                            @php
                                $menuId = 'actionMenuBkm_' . $bkm->bkm_id;
                            @endphp
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                {{-- LIHAT DETAIL & CETAK --}}
                                <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Voucher BKM</span>
                                </a>

                                <a href="{{ route('keuangan.kas_masuk.show', $bkm->bkm_id) }}?print=true" target="_blank" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak Slip Voucher</span>
                                </a>

                                {{-- VOID BKM --}}
                                @if (!$isVoid && Auth::user()->canVoidKasMasuk())
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="openModalVoidBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}', 'Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan (Void BKM)</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
                                <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div style="font-weight: 700; color: #1e293b; font-size: 1rem;">Belum Ada Bukti Kas Masuk (BKM)</div>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; max-width: 420px;">
                                    Belum ada transaksi penerimaan kas atau transfer bank yang tercatat sesuai kriteria filter saat ini.
                                </p>
                                @if (Auth::user()->canCreateKasMasuk())
                                    <a href="{{ route('keuangan.kas_masuk.create') }}" class="btn btn-primary" style="margin-top: 0.5rem; font-weight: 700; font-size: 0.825rem; background: #059669; border-color: #059669;">
                                        + Catat Bukti Kas Masuk Baru
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($bkmList->hasPages())
        <div style="padding: 0.875rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $bkmList->links() }}
        </div>
    @endif
</div>

{{-- MODAL VOID BKM PARTIAL --}}
@include('keuangan.kas_masuk.partials.modal-void-bkm')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_masuk/kas-masuk-index.js') }}"></script>
@endpush
