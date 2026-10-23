@extends('layouts.app')

@section('title', 'Buku Besar Interaktif (General Ledger)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/buku-besar/buku-besar-index.css') }}">
@endpush

@section('content')
<div class="gl-container">

    {{-- TOP BAR: JUDUL & FILTER PERIODE --}}
    <div class="gl-top-bar">
        <div class="gl-title-group">
            <h1>
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Buku Besar Interaktif</span>
            </h1>
            <p>Eksplorasi mutasi akun akuntansi, saldo berjalan, dan tren harian (Format Terpadu Zahir)</p>
        </div>

        <form method="GET" action="{{ route('keuangan.buku_besar.index') }}" id="glPeriodFilterForm" class="gl-period-controls">
            <input type="hidden" name="coa_id" id="glCurrentCoaId" value="{{ $selectedCoaId }}">
            
            <div style="display: flex; align-items: center; gap: 4px;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">Bulan:</span>
                <input type="month" 
                       name="month" 
                       id="glMonthSelect" 
                       value="{{ $month }}" 
                       class="form-control" 
                       style="font-size: 0.8rem; height: 34px; padding: 2px 8px; width: 140px;">
            </div>

            <div style="display: flex; align-items: center; gap: 4px;">
                <input type="date" 
                       name="start_date" 
                       id="glStartDate" 
                       value="{{ $startDate }}" 
                       class="form-control" 
                       style="font-size: 0.775rem; height: 34px; padding: 2px 6px; width: 125px;">
                <span style="font-size: 0.75rem; color: #94a3b8;">s/d</span>
                <input type="date" 
                       name="end_date" 
                       id="glEndDate" 
                       value="{{ $endDate }}" 
                       class="form-control" 
                       style="font-size: 0.775rem; height: 34px; padding: 2px 6px; width: 125px;">
            </div>

            <button type="submit" class="btn btn-secondary" style="height: 34px; padding: 0 0.75rem; font-size: 0.8rem; font-weight: 700;">
                Terapkan
            </button>
        </form>
    </div>

    {{-- SPLIT VIEW WRAPPER (2-PANEL) --}}
    <div class="gl-split-wrapper">

        {{-- PANEL KIRI: SIDEBAR DAFTAR AKUN (COA) --}}
        <div class="gl-sidebar">
            <div class="gl-sidebar-header">
                {{-- Search Box --}}
                <div class="gl-search-box">
                    <svg class="gl-search-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="glAccountSearch" placeholder="Cari nama atau kode akun..." autocomplete="off">
                </div>

                {{-- Kategori Filter Chips --}}
                <div class="gl-kategori-tabs">
                    <button type="button" class="gl-tab-chip active" data-kategori="ALL">Semua</button>
                    <button type="button" class="gl-tab-chip" data-kategori="ASET">Aset</button>
                    <button type="button" class="gl-tab-chip" data-kategori="KEWAJIBAN">Kewajiban</button>
                    <button type="button" class="gl-tab-chip" data-kategori="EKUITAS">Ekuitas</button>
                    <button type="button" class="gl-tab-chip" data-kategori="BEBAN">Beban</button>
                </div>
            </div>

            {{-- List Akun COA --}}
            <div class="gl-account-list" id="glAccountListContainer">
                @foreach ($accounts as $acc)
                    @php
                        $isActive = ($acc->coa_id == $selectedCoaId);
                    @endphp
                    <div class="gl-account-item {{ $isActive ? 'active' : '' }}" 
                         data-coa-id="{{ $acc->coa_id }}"
                         data-code="{{ $acc->kode_akun }}"
                         data-name="{{ $acc->nama_akun }}"
                         data-kategori="{{ $acc->kategori_akun }}">
                        <div class="gl-acc-row-top">
                            <span class="gl-acc-code">{{ $acc->kode_akun }}</span>
                            <span class="gl-acc-badge {{ $acc->mutasi_count > 0 ? 'has-mutasi' : '' }}">
                                {{ $acc->mutasi_count > 0 ? $acc->mutasi_count . ' mutasi' : '0' }}
                            </span>
                        </div>
                        <div class="gl-acc-name">{{ $acc->nama_akun }}</div>
                        <div class="gl-acc-row-bottom">
                            <span class="gl-acc-kategori">{{ $acc->sub_kategori ?: $acc->kategori_akun }}</span>
                            <span class="gl-acc-saldo">{{ $acc->formatted_saldo_berjalan }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PANEL KANAN: DETAIL BUKU BESAR & GRAFIK --}}
        <div class="gl-content-panel">

            @if ($ledgerData)
                @php
                    $coaActive = $ledgerData['coa'];
                @endphp

                {{-- 1. KARTU HEADER & METRIK AKUN --}}
                <div class="gl-summary-card">
                    <div class="gl-card-header-row">
                        <div class="gl-active-account-title">
                            <h2>
                                <span style="font-family: monospace; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; font-size: 1.05rem;" id="glActiveAccountCode">
                                    {{ $coaActive->kode_akun }}
                                </span>
                                <span id="glActiveAccountName">{{ $coaActive->nama_akun }}</span>
                            </h2>
                            <span id="glActiveAccountSub">{{ $coaActive->kategori_akun }} • {{ $coaActive->sub_kategori ?: '-' }}</span>
                        </div>

                        <div style="text-align: right;">
                            <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px;" id="glActiveAccountNormal">
                                Saldo Normal: {{ $coaActive->saldo_normal }}
                            </span>
                        </div>
                    </div>

                    {{-- 4 Kotak Metrik --}}
                    <div class="gl-metrics-grid">
                        <div class="gl-metric-box">
                            <span>Saldo Awal Periode</span>
                            <strong id="glMetricSaldoAwal">Rp {{ number_format($ledgerData['saldo_awal_periode'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="gl-metric-box debet">
                            <span>Total Debet Periode</span>
                            <strong id="glMetricTotalDebet">Rp {{ number_format($ledgerData['total_debet_periode'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="gl-metric-box kredit">
                            <span>Total Kredit Periode</span>
                            <strong id="glMetricTotalKredit">Rp {{ number_format($ledgerData['total_kredit_periode'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="gl-metric-box saldo-akhir">
                            <span>Saldo Akhir Berjalan</span>
                            <strong id="glMetricSaldoAkhir">Rp {{ number_format($ledgerData['saldo_akhir_periode'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                {{-- 2. KARTU GRAFIK TREN SALDO HARIAN --}}
                <div class="gl-chart-card">
                    <div class="gl-chart-header">
                        <h3>
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            <span>Grafik Tren Saldo Harian</span>
                        </h3>
                        <span style="font-size: 0.725rem; color: #94a3b8;">
                            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        </span>
                    </div>

                    <div class="gl-chart-container">
                        <canvas id="glDailyCanvas"></canvas>
                    </div>
                </div>

                {{-- 3. KARTU TABEL MUTASI BUKU BESAR --}}
                <div class="gl-table-card">
                    <div class="gl-table-header-bar">
                        <h3>Mutasi Buku Besar (General Ledger Details)</h3>
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                            {{ count($ledgerData['lines']) }} Transaksi
                        </span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="gl-table">
                            <thead>
                                <tr>
                                    <th style="width: 105px; text-align: center;">Tanggal</th>
                                    <th style="width: 130px;">No. Jurnal</th>
                                    <th style="width: 140px;">No. Referensi</th>
                                    <th>Keterangan Transaksi</th>
                                    <th style="width: 135px; text-align: right;">Debet</th>
                                    <th style="width: 135px; text-align: right;">Kredit</th>
                                    <th style="width: 145px; text-align: right;">Saldo</th>
                                </tr>
                            </thead>
                            <tbody id="glTableBody">
                                {{-- Baris Saldo Awal --}}
                                <tr class="gl-row-beginning">
                                    <td style="text-align: center;">-</td>
                                    <td style="font-family: monospace;">-</td>
                                    <td style="font-family: monospace;">-</td>
                                    <td style="font-weight: 700;">Saldo Awal Periode</td>
                                    <td style="text-align: right;">-</td>
                                    <td style="text-align: right;">-</td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                        Rp {{ number_format($ledgerData['saldo_awal_periode'], 0, ',', '.') }}
                                    </td>
                                </tr>

                                @forelse ($ledgerData['lines'] as $line)
                                    <tr>
                                        <td style="text-align: center; white-space: nowrap; color: #475569;">
                                            {{ $line['tgl_jurnal'] ? \Carbon\Carbon::parse($line['tgl_jurnal'])->format('d M Y') : '-' }}
                                        </td>
                                        <td style="font-family: monospace; font-size: 0.775rem; color: #64748b;">
                                            {{ $line['jurnal_no'] ?: '-' }}
                                        </td>
                                        <td>
                                            @if ($line['ref_no'] && $line['tipe_transaksi'] === 'HUTANG_PEMBELIAN' && $line['ref_id'])
                                                <button type="button" 
                                                        onclick="openDetailTagihanModal({{ $line['ref_id'] }}, '{{ $line['ref_no'] }}')" 
                                                        class="gl-ref-pill gl-ref-btn" 
                                                        title="Klik untuk lihat rincian tagihan (pop-up)">
                                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>{{ $line['ref_no'] }}</span>
                                                </button>
                                            @elseif ($line['ref_no'])
                                                <span class="gl-ref-pill">{{ $line['ref_no'] }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td style="line-height: 1.35; font-weight: 500;">
                                            {{ $line['keterangan'] }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #0284c7;">
                                            {{ $line['debet'] > 0 ? 'Rp ' . number_format($line['debet'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #d97706;">
                                            {{ $line['kredit'] > 0 ? 'Rp ' . number_format($line['kredit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: #0f172a;">
                                            Rp {{ number_format($line['saldo_berjalan'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="gl-empty-state">
                                            Tidak ada mutasi transaksi pada periode ini untuk akun terpilih.
                                        </td>
                                    </tr>
                                @endforelse

                                {{-- Baris Total Mutasi --}}
                                <tr class="gl-row-total">
                                    <td colspan="4" style="text-align: right; text-transform: uppercase; font-size: 0.75rem; color: #475569;">
                                        Total Mutasi Periode
                                    </td>
                                    <td style="text-align: right; font-weight: 800; color: #0284c7;">
                                        Rp {{ number_format($ledgerData['total_debet_periode'], 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-weight: 800; color: #d97706;">
                                        Rp {{ number_format($ledgerData['total_kredit_periode'], 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right;">-</td>
                                </tr>

                                {{-- Baris Saldo Akhir --}}
                                <tr class="gl-row-ending">
                                    <td colspan="4" style="text-align: right; text-transform: uppercase; font-size: 0.775rem;">
                                        Saldo Akhir Berjalan
                                    </td>
                                    <td colspan="2"></td>
                                    <td style="text-align: right; font-weight: 900; font-size: 0.9rem;">
                                        Rp {{ number_format($ledgerData['saldo_akhir_periode'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <div class="gl-summary-card" style="text-align: center; padding: 3rem 1rem;">
                    <p style="color: #64748b; font-size: 0.9rem;">Pilih salah satu akun di panel sebelah kiri untuk menampilkan buku besar.</p>
                </div>
            @endif

        </div>
    {{-- Modal Pop-up Detail Tagihan Hutang --}}
    @include('keuangan.buku-besar.partials.modal-detail-tagihan')
    {{-- Modal Pop-up Detail Jurnal Umum --}}
    @include('keuangan.jurnal.partials.modal-detail-jurnal')
</div>
@endsection

@push('scripts')
<script>
    window.appConfig = {
        bukuBesarUrl: "{{ route('keuangan.buku_besar.index') }}",
        hutangDetailBaseUrl: "{{ url('keuangan/hutang') }}",
        initialChart: @json($ledgerData['chart'] ?? null)
    };
</script>
<script src="{{ asset('js/keuangan/buku-besar/buku-besar-index.js') }}?v={{ file_exists(public_path('js/keuangan/buku-besar/buku-besar-index.js')) ? filemtime(public_path('js/keuangan/buku-besar/buku-besar-index.js')) : time() }}"></script>
@endpush
