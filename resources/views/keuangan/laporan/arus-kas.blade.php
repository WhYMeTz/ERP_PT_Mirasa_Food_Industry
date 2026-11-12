@extends('layouts.app')

@section('title', 'Laporan Arus Kas (Cash Flow)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/laporan/laporan.css') }}">
@endpush

@section('content')
<div class="content-header no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.laporan.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.825rem; font-weight: 600;">
                &larr; Hub Laporan
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                💵 Laporan Arus Kas (Cash Flow Statement)
            </h1>
        </div>
        <p style="font-size: 0.775rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Metode Langsung Penerimaan &amp; Pengeluaran Kas Pabrik &bull; Periode {{ $dateInfo['label'] }}
        </p>
    </div>
</div>

<div class="laporan-container">
    {{-- Navigasi Tab --}}
    @include('keuangan.laporan.partials.nav-tabs')

    {{-- Filter Bar --}}
    @include('keuangan.laporan.partials.header-filter', [
        'tableExportId' => 'tableArusKas',
        'exportFilename' => 'Arus_Kas_PT_Mirasa_' . $dateInfo['start_date'] . '_' . $dateInfo['end_date'] . '.csv'
    ])

    {{-- Lembar Akuntansi Cetak --}}
    <div class="corporate-sheet">
        @include('keuangan.laporan.partials.sheet-header', [
            'reportTitle' => 'LAPORAN ARUS KAS (METODE LANGSUNG)'
        ])

        <table class="accounting-table" id="tableArusKas">
            <thead>
                <tr>
                    <th style="width: 70%;">Aktivitas Arus Kas</th>
                    <th style="width: 30%; text-align: right;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {{-- 1. ARUS KAS DARI AKTIVITAS OPERASI --}}
                <tr class="tr-section-title">
                    <td colspan="2">1. ARUS KAS DARI AKTIVITAS OPERASIONAL</td>
                </tr>
                {{-- Kas Masuk Operasi --}}
                @forelse($report['arus_operasi_masuk'] as $in)
                    <tr>
                        <td style="padding-left: 2rem; color: #0f172a;">
                            (+) {{ $in['keterangan'] }}
                        </td>
                        <td class="cell-num" style="color: #15803d;">
                            + Rp {{ number_format($in['nominal'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 2rem; color: #94a3b8; font-style: italic;">
                            (+) Penerimaan Kas Masuk Operasional
                        </td>
                        <td class="cell-num" style="color: #94a3b8;">Rp 0</td>
                    </tr>
                @endforelse

                {{-- Kas Keluar Operasi --}}
                @forelse($report['arus_operasi_keluar'] as $out)
                    <tr>
                        <td style="padding-left: 2rem; color: #0f172a;">
                            (-) {{ $out['keterangan'] }}
                        </td>
                        <td class="cell-num" style="color: #b91c1c;">
                            (Rp {{ number_format($out['nominal'], 0, ',', '.') }})
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 2rem; color: #94a3b8; font-style: italic;">
                            (-) Pengeluaran Kas Keluar Operasional
                        </td>
                        <td class="cell-num" style="color: #94a3b8;">Rp 0</td>
                    </tr>
                @endforelse

                <tr class="tr-subtotal">
                    <td>ARUS KAS BERSIH DARI AKTIVITAS OPERASIONAL</td>
                    <td class="cell-num cell-bold" style="color: {{ $report['net_operasi'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        {{ $report['net_operasi'] >= 0 ? 'Rp ' . number_format($report['net_operasi'], 0, ',', '.') : '(Rp ' . number_format(abs($report['net_operasi']), 0, ',', '.') . ')' }}
                    </td>
                </tr>

                {{-- 2. ARUS KAS DARI AKTIVITAS INVESTASI --}}
                <tr class="tr-section-title">
                    <td colspan="2">2. ARUS KAS DARI AKTIVITAS INVESTASI</td>
                </tr>
                @forelse($report['arus_investasi_keluar'] as $inv)
                    <tr>
                        <td style="padding-left: 2rem; color: #0f172a;">
                            (-) {{ $inv['keterangan'] }}
                        </td>
                        <td class="cell-num" style="color: #b91c1c;">
                            (Rp {{ number_format($inv['nominal'], 0, ',', '.') }})
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 2rem; color: #94a3b8; font-style: italic;">
                            Tidak ada arus kas investasi (perolehan/penjualan aset tetap) pada periode ini.
                        </td>
                        <td class="cell-num" style="color: #94a3b8;">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="tr-subtotal">
                    <td>ARUS KAS BERSIH DARI AKTIVITAS INVESTASI</td>
                    <td class="cell-num cell-bold">
                        Rp {{ number_format($report['net_investasi'], 0, ',', '.') }}
                    </td>
                </tr>

                {{-- 3. ARUS KAS DARI AKTIVITAS PENDANAAN --}}
                <tr class="tr-section-title">
                    <td colspan="2">3. ARUS KAS DARI AKTIVITAS PENDANAAN</td>
                </tr>
                @forelse($report['arus_pendanaan_masuk'] as $pend)
                    <tr>
                        <td style="padding-left: 2rem; color: #0f172a;">
                            (+) {{ $pend['keterangan'] }}
                        </td>
                        <td class="cell-num" style="color: #15803d;">
                            + Rp {{ number_format($pend['nominal'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td style="padding-left: 2rem; color: #94a3b8; font-style: italic;">
                            Tidak ada arus kas pendanaan (setoran modal / hutang bank) pada periode ini.
                        </td>
                        <td class="cell-num" style="color: #94a3b8;">Rp 0</td>
                    </tr>
                @endforelse
                <tr class="tr-subtotal">
                    <td>ARUS KAS BERSIH DARI AKTIVITAS PENDANAAN</td>
                    <td class="cell-num cell-bold">
                        Rp {{ number_format($report['net_pendanaan'], 0, ',', '.') }}
                    </td>
                </tr>

                {{-- REKONSILIASI KENAIKAN/PENURUNAN KAS & SALDO AKHIR --}}
                <tr class="tr-subtotal" style="background: #f8fafc; font-size: 0.85rem;">
                    <td style="font-weight: 800;">KENAIKAN / (PENURUNAN) KAS BERSIH PERIODE INI</td>
                    <td class="cell-num cell-bold" style="color: {{ $report['kenaikan_bersih_kas'] >= 0 ? '#15803d' : '#b91c1c' }};">
                        {{ $report['kenaikan_bersih_kas'] >= 0 ? '+ Rp ' . number_format($report['kenaikan_bersih_kas'], 0, ',', '.') : '- Rp ' . number_format(abs($report['kenaikan_bersih_kas']), 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 700; color: #334155; padding-top: 0.65rem;">
                        Saldo Kas &amp; Setara Kas Awal Periode
                    </td>
                    <td class="cell-num cell-bold" style="padding-top: 0.65rem;">
                        Rp {{ number_format($report['saldo_awal_total'], 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="tr-grand-total">
                    <td>SALDO KAS &amp; SETARA KAS AKHIR PERIODE</td>
                    <td class="cell-num">
                        Rp {{ number_format($report['saldo_akhir_total'], 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- TABEL REKONSILIASI KAS & BANK FISIK --}}
        <div style="margin-top: 2rem;">
            <div style="font-size: 0.8125rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; text-transform: uppercase;">
                Rincian Saldo Kas &amp; Rekening Bank Penutup:
            </div>
            <table class="accounting-table">
                <thead>
                    <tr>
                        <th>Rekening Kas / Bank</th>
                        <th>Kode COA</th>
                        <th style="text-align: right;">Saldo Awal</th>
                        <th style="text-align: right;">Total Masuk</th>
                        <th style="text-align: right;">Total Keluar</th>
                        <th style="text-align: right;">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['rekening_breakdown'] as $rb)
                        <tr>
                            <td>
                                <strong style="color: #0f172a;">{{ $rb['kas_bank']->nama_akun }}</strong>
                                <div style="font-size: 0.7rem; color: #64748b;">{{ $rb['kas_bank']->tipe_akun }} &bull; {{ $rb['kas_bank']->nomor_rekening ?: 'Brankas' }}</div>
                            </td>
                            <td>
                                <span class="coa-code-badge">{{ $rb['kas_bank']->getCoaKodeAkun() }}</span>
                            </td>
                            <td class="cell-num">Rp {{ number_format($rb['saldo_awal'], 0, ',', '.') }}</td>
                            <td class="cell-num" style="color: #15803d;">Rp {{ number_format($rb['masuk'], 0, ',', '.') }}</td>
                            <td class="cell-num" style="color: #b91c1c;">Rp {{ number_format($rb['keluar'], 0, ',', '.') }}</td>
                            <td class="cell-num cell-bold" style="color: #047857;">Rp {{ number_format($rb['saldo_akhir'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Tanda Tangan Resmi Akuntansi --}}
        <div style="margin-top: 3rem; display: flex; justify-content: space-between; text-align: center; font-size: 0.8125rem;">
            <div style="width: 200px;">
                <div style="color: #64748b; margin-bottom: 3.5rem;">Dibuat Oleh:</div>
                <div style="font-weight: 800; border-top: 1px solid #94a3b8; padding-top: 0.25rem;">Staff Akuntansi</div>
            </div>
            <div style="width: 200px;">
                <div style="color: #64748b; margin-bottom: 3.5rem;">Diperiksa Oleh:</div>
                <div style="font-weight: 800; border-top: 1px solid #94a3b8; padding-top: 0.25rem;">Manajer Keuangan</div>
            </div>
            <div style="width: 200px;">
                <div style="color: #64748b; margin-bottom: 3.5rem;">Disetujui Oleh:</div>
                <div style="font-weight: 800; border-top: 1px solid #94a3b8; padding-top: 0.25rem;">Direktur Utama</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/laporan/laporan.js') }}"></script>
@endpush

