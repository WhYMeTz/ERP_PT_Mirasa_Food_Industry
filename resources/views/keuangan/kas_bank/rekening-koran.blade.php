@extends('layouts.app')

@section('title', 'Rekening Koran Bank - ' . ($statement['kas_bank']->nama_akun ?? 'Kas & Bank'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_bank/rekening-koran.css') }}?v={{ filemtime(public_path('css/keuangan/kas_bank/rekening-koran.css')) }}">
@endpush

@section('content')
<div class="rk-page-container">
    {{-- 1. Page Header & Actions Toolbar --}}
    <div class="rk-header-section no-print">
        <div>
            <div class="rk-breadcrumb">
                <a href="{{ route('keuangan.kas_bank.index') }}">Keuangan &amp; Kas</a>
                <span class="rk-breadcrumb-sep">&rsaquo;</span>
                <a href="{{ route('keuangan.kas_bank.index') }}">Master Rekening</a>
                <span class="rk-breadcrumb-sep">&rsaquo;</span>
                <span style="color: #0f172a; font-weight: 700;">Rekening Koran Bank</span>
            </div>
            <h1 class="rk-page-title">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0d9488;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Rekening Koran Bank (Bank Statement)</span>
            </h1>
            <p class="rk-page-subtitle">
                Laporan mutasi rekening resmi, saldo berjalan, rekonsiliasi kas dan cetak lembar statement A4.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('keuangan.kas_bank.mutasi', ['kas_bank_id' => $selectedKasBankId]) }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Buku Mutasi Kas &amp; Bank</span>
            </a>
            <a href="{{ route('keuangan.kas_bank.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
                <span>&larr; Master Rekening</span>
            </a>
        </div>
    </div>

    {{-- 2. Bank Account Selector Cards (Executive Switcher) --}}
    <div class="rk-bank-grid no-print">
        @foreach($kasBanks as $kb)
            @php
                $isActive = ($kb->kas_bank_id == $selectedKasBankId);
                $bankTagClass = match(true) {
                    str_contains(strtoupper($kb->nama_akun), 'BCA') => 'tag-bca',
                    str_contains(strtoupper($kb->nama_akun), 'MANDIRI') => 'tag-mandiri',
                    $kb->tipe_akun === 'KAS' => 'tag-kas',
                    default => 'tag-default',
                };
                $bankTagName = match(true) {
                    str_contains(strtoupper($kb->nama_akun), 'BCA') => 'BCA',
                    str_contains(strtoupper($kb->nama_akun), 'MANDIRI') => 'MANDIRI',
                    $kb->tipe_akun === 'KAS' => 'KAS TUNAI',
                    default => $kb->bank_nama ?: 'BANK',
                };
            @endphp
            <a href="{{ route('keuangan.kas_bank.rekening_koran', ['kas_bank_id' => $kb->kas_bank_id, 'preset' => $preset]) }}" 
               class="rk-bank-card {{ $isActive ? 'active' : '' }}">
                <div class="rk-bank-card-header">
                    <span class="rk-bank-logo-tag {{ $bankTagClass }}">{{ $bankTagName }}</span>
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        @if($isActive)
                            <span class="rk-active-marker">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                <span>Aktif</span>
                            </span>
                        @endif
                        <span class="rk-card-coa-pill">COA {{ $kb->getCoaKodeAkun() }}</span>
                    </div>
                </div>

                <div>
                    <div class="rk-bank-card-name">{{ $kb->nama_akun }}</div>
                    <div class="rk-bank-card-norek">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>{{ $kb->nomor_rekening ?: ($kb->tipe_akun === 'KAS' ? 'Brankas Tunai Pabrik' : 'No. Rekening -') }}</span>
                    </div>
                </div>

                <div class="rk-bank-card-balance">
                    <div>
                        <span class="rk-balance-lbl">Saldo Buku</span>
                        <div class="rk-balance-val">Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }}</div>
                    </div>
                    <span style="font-size: 0.725rem; font-weight: 700; color: #0284c7;">
                        {{ $isActive ? 'Statement Terpilih' : 'Pilih Rekening &rarr;' }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 3. Unified Filter Toolbar --}}
    <div class="rk-filter-card no-print">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span style="font-size: 0.725rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                Periode Statement:
            </span>
            <div class="rk-preset-group">
                <a href="?kas_bank_id={{ $selectedKasBankId }}&preset=this_month" 
                   class="rk-preset-btn {{ $preset === 'this_month' ? 'active' : '' }}">
                    Bulan Ini
                </a>
                <a href="?kas_bank_id={{ $selectedKasBankId }}&preset=last_month" 
                   class="rk-preset-btn {{ $preset === 'last_month' ? 'active' : '' }}">
                    Bulan Lalu
                </a>
                <a href="?kas_bank_id={{ $selectedKasBankId }}&preset=this_year" 
                   class="rk-preset-btn {{ $preset === 'this_year' ? 'active' : '' }}">
                    Tahun Ini
                </a>
            </div>
        </div>

        <div class="rk-filter-actions">
            <form method="GET" action="{{ route('keuangan.kas_bank.rekening_koran') }}" class="filter-custom-form">
                <input type="hidden" name="kas_bank_id" value="{{ $selectedKasBankId }}">
                <input type="hidden" name="preset" value="custom">

                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Dari:</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="filter-input-date" required>

                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">S/D:</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="filter-input-date" required>

                <button type="submit" class="btn-filter-action btn-filter-primary" title="Terapkan filter tanggal kustom">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Tampilkan</span>
                </button>
            </form>

            <button type="button" 
                    onclick="exportRekeningKoranToCsv('tableRekeningKoran', 'Rekening_Koran_{{ $statement['kas_bank']->kode_akun ?? 'ACC' }}_{{ $startDate }}_{{ $endDate }}.csv')" 
                    class="btn-filter-action btn-filter-secondary" 
                    title="Ekspor mutasi ke file CSV spreadsheet">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </button>

            <button type="button" 
                    onclick="printRekeningKoran()" 
                    class="btn-filter-action btn-filter-print" 
                    title="Cetak lembar rekening koran resmi format A4">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak A4</span>
            </button>
        </div>
    </div>

    {{-- 4. Lembar Resmi Rekening Koran (Statement Paper Sheet) --}}
    @if($statement)
        @php
            $kb = $statement['kas_bank'];
            $bankNameUpper = strtoupper($kb->bank_nama ?: ($kb->tipe_akun === 'KAS' ? 'KAS TUNAI OPERASIONAL' : 'BANK'));
        @endphp
        <div class="rk-sheet">
            {{-- Kop Surat Resmi Rekening Koran --}}
            <div class="rk-header-top">
                <div class="rk-company-block">
                    <h2 class="rk-company-title">PT MIRASA FOOD INDUSTRY</h2>
                    <div class="rk-company-subtitle">Produsen Makanan Ringan &amp; Olahan Hasil Bumi</div>
                    <div class="rk-company-address">
                        Jl. Magelang - Purworejo Km. 9, Magelang, Jawa Tengah 56172<br>
                        Divisi Keuangan &amp; Perbendaharaan &bull; Telp: (0293) 364888 &bull; finance@mirasafood.com
                    </div>
                </div>

                <div class="rk-statement-badge-box">
                    <div class="rk-bank-brand-title">{{ $bankNameUpper }}</div>
                    <div class="rk-statement-subtitle">Laporan Rekening Koran Resmi &bull; Bank Statement</div>
                    <div class="rk-bank-account-pill font-tabular">
                        <span>No. Rek: <strong style="color: #0f172a;">{{ $kb->nomor_rekening ?: ($kb->tipe_akun === 'KAS' ? 'KAS-OPERASIONAL' : '-') }}</strong></span>
                        <span style="color: #cbd5e1;">&bull;</span>
                        <span style="color: #0284c7;">COA: {{ $kb->getCoaKodeAkun() }}</span>
                    </div>
                </div>
            </div>

            {{-- Metadata Box 6-Kolom --}}
            <div class="rk-meta-grid">
                <div class="rk-meta-item">
                    <span class="rk-meta-label">Nomor Rekening</span>
                    <span class="rk-meta-val font-tabular">{{ $kb->nomor_rekening ?: ($kb->tipe_akun === 'KAS' ? 'KAS-OPERASIONAL' : '-') }}</span>
                </div>
                <div class="rk-meta-item">
                    <span class="rk-meta-label">Nama Pemilik Akun</span>
                    <span class="rk-meta-val">{{ $kb->atas_nama ?: 'PT Mirasa Food Industry' }}</span>
                </div>
                <div class="rk-meta-item">
                    <span class="rk-meta-label">Cabang / Unit</span>
                    <span class="rk-meta-val">{{ $kb->cabang ?: 'Kantor Pusat Magelang' }}</span>
                </div>
                <div class="rk-meta-item">
                    <span class="rk-meta-label">Mata Uang</span>
                    <span class="rk-meta-val">IDR (Indonesian Rupiah)</span>
                </div>
                <div class="rk-meta-item">
                    <span class="rk-meta-label">Periode Mutasi</span>
                    <span class="rk-meta-val font-tabular">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                </div>
                <div class="rk-meta-item">
                    <span class="rk-meta-label">Tanggal Cetak</span>
                    <span class="rk-meta-val font-tabular">{{ now()->format('d/m/Y H:i') }} WIB</span>
                </div>
            </div>

            {{-- 4-Card Statement Summary --}}
            <div class="rk-summary-bar">
                <div class="rk-summary-card rk-card-saldo-awal">
                    <div class="rk-sum-label">Saldo Awal (Beginning)</div>
                    <div class="rk-sum-amount">
                        Rp {{ number_format($statement['saldo_awal'], 0, ',', '.') }}
                    </div>
                    <div class="rk-sum-count">Per {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</div>
                </div>

                <div class="rk-summary-card rk-card-kredit">
                    <div class="rk-sum-label">Total Kredit / Masuk (CR)</div>
                    <div class="rk-sum-amount">
                        + Rp {{ number_format($statement['total_kredit'], 0, ',', '.') }}
                    </div>
                    <div class="rk-sum-count">{{ $statement['count_kredit'] }} transaksi masuk</div>
                </div>

                <div class="rk-summary-card rk-card-debet">
                    <div class="rk-sum-label">Total Debet / Keluar (DB)</div>
                    <div class="rk-sum-amount">
                        - Rp {{ number_format($statement['total_debet'], 0, ',', '.') }}
                    </div>
                    <div class="rk-sum-count">{{ $statement['count_debet'] }} transaksi keluar</div>
                </div>

                <div class="rk-summary-card rk-card-saldo-akhir">
                    <div class="rk-sum-label">Saldo Akhir (Ending)</div>
                    <div class="rk-sum-amount">
                        Rp {{ number_format($statement['saldo_akhir'], 0, ',', '.') }}
                    </div>
                    <div class="rk-sum-count">Per {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                </div>
            </div>

            {{-- Tabel Rincian Mutasi Rekening Koran --}}
            <div class="rk-table-wrapper">
                <table class="rk-table" id="tableRekeningKoran">
                    <thead>
                        <tr>
                            <th style="width: 105px;">Tanggal &amp; Jam</th>
                            <th style="width: 135px;">No. Referensi</th>
                            <th style="width: 110px;">Jenis</th>
                            <th>Uraian / Keterangan Transaksi</th>
                            <th style="width: 145px; text-align: right;">Mutasi Keluar (DB)</th>
                            <th style="width: 145px; text-align: right;">Mutasi Masuk (CR)</th>
                            <th style="width: 155px; text-align: right;">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Baris Saldo Awal --}}
                        <tr class="rk-row-opening-balance">
                            <td class="font-tabular">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</td>
                            <td style="font-family: monospace; color: #64748b;">-</td>
                            <td>
                                <span style="font-size: 0.6875rem; font-weight: 800; background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px;">
                                    SALDO_AWAL
                                </span>
                            </td>
                            <td style="font-style: italic; color: #475569; font-weight: 600;">
                                Saldo awal sebelum tanggal transaksi periode ini
                            </td>
                            <td class="rk-num">-</td>
                            <td class="rk-num">-</td>
                            <td class="rk-num rk-running-balance">Rp {{ number_format($statement['saldo_awal'], 0, ',', '.') }}</td>
                        </tr>

                        {{-- Baris Mutasi --}}
                        @forelse($statement['rows'] as $row)
                            <tr>
                                <td class="font-tabular">
                                    <div style="font-weight: 700; color: #0f172a;">{{ \Carbon\Carbon::parse($row['tgl_mutasi'])->format('d/m/Y') }}</div>
                                    <div style="font-size: 0.6875rem; color: #64748b;">{{ $row['jam_mutasi'] }} WIB</div>
                                </td>
                                <td>
                                    <strong style="font-family: monospace; color: #0284c7; font-size: 0.8125rem;">
                                        {{ $row['no_referensi'] }}
                                    </strong>
                                </td>
                                <td>
                                    @php
                                        $badgeBg = match($row['jenis_transaksi']) {
                                            'BKK' => '#fee2e2',
                                            'BKM' => '#dcfce7',
                                            'TRANSFER' => '#e0f2fe',
                                            default => '#f1f5f9',
                                        };
                                        $badgeColor = match($row['jenis_transaksi']) {
                                            'BKK' => '#991b1b',
                                            'BKM' => '#166534',
                                            'TRANSFER' => '#0369a1',
                                            default => '#334155',
                                        };
                                    @endphp
                                    <span style="font-size: 0.6875rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: {{ $badgeBg }}; color: {{ $badgeColor }};">
                                        {{ $row['jenis_transaksi'] }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b; line-height: 1.35;">
                                        {{ $row['keterangan'] }}
                                    </div>
                                    <div style="font-size: 0.6875rem; color: #94a3b8; margin-top: 2px;">
                                        Dicatat oleh: {{ $row['created_by'] ?: 'SYSTEM' }}
                                    </div>
                                </td>
                                <td class="rk-num rk-debet">
                                    {{ $row['debet'] > 0 ? 'Rp ' . number_format($row['debet'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="rk-num rk-kredit">
                                    {{ $row['kredit'] > 0 ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="rk-num rk-running-balance">
                                    Rp {{ number_format($row['saldo_berjalan'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-style: italic;">
                                    Tidak ada mutasi transaksi pada rentang tanggal ini. (Saldo rekening tetap berjalan normal).
                                </td>
                            </tr>
                        @endforelse

                        {{-- Baris Total Bawah --}}
                        <tr class="rk-total-row">
                            <td colspan="4" style="text-align: right; padding-right: 1.5rem; letter-spacing: 0.03em;">
                                TOTAL MUTASI &amp; SALDO AKHIR:
                            </td>
                            <td class="rk-num" style="color: #dc2626;">
                                - Rp {{ number_format($statement['total_debet'], 0, ',', '.') }}
                            </td>
                            <td class="rk-num" style="color: #16a34a;">
                                + Rp {{ number_format($statement['total_kredit'], 0, ',', '.') }}
                            </td>
                            <td class="rk-num" style="color: #0369a1; font-size: 0.95rem;">
                                Rp {{ number_format($statement['saldo_akhir'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Kolom Tanda Tangan Otorisasi --}}
            <div class="rk-signature-grid">
                <div class="rk-sign-card">
                    <div class="rk-sign-role">Dibuat &amp; Diverifikasi:</div>
                    <div class="rk-sign-line">( ............................................ )</div>
                    <div class="rk-sign-date">Kasir / Perbendaharaan</div>
                </div>
                <div class="rk-sign-card">
                    <div class="rk-sign-role">Diperiksa Oleh:</div>
                    <div class="rk-sign-line">( ............................................ )</div>
                    <div class="rk-sign-date">Supervisor Akuntansi</div>
                </div>
                <div class="rk-sign-card">
                    <div class="rk-sign-role">Disetujui Oleh:</div>
                    <div class="rk-sign-line">( ............................................ )</div>
                    <div class="rk-sign-date">Manajer Keuangan &amp; Direktur</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_bank/rekening-koran.js') }}?v={{ filemtime(public_path('js/keuangan/kas_bank/rekening-koran.js')) }}"></script>
@endpush
