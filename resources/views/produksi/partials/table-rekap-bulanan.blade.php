{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  TABEL REKAPITULASI HPP BULANAN / TAHUNAN (12 BULAN KONSOLIDASI) --}}
{{--  ERP PT Mirasa Food Industry - Analisis Eksekutif Tren Finansial --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<div class="card" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                    Konsolidasi HPP Bulanan Tahun {{ $year }}
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Buku Besar Akumulasi Biaya Manufaktur per Bulan
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.725rem; background: #f8fafc; color: #475569; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 600; border: 1px solid #e2e8f0;">
                Total: {{ $yearlyReport['annual_totals']['total_work_days'] }} Hari Kerja Terdata
            </span>
        </div>
    </div>

    {{-- KONTEN TABEL BULANAN (100% FIT DI LAYAR) --}}
    <table class="table-compact-hpp">
        <thead>
            <tr>
                <th style="width: 12%;">Bulan</th>
                <th style="width: 8%; text-align: center;">Hari Kerja</th>
                <th style="width: 12%; text-align: right;">Bahan Baku (Kg)</th>
                <th style="width: 11%; text-align: right;">Output WIP (Kg)</th>
                <th style="width: 9%; text-align: center;">Rendemen</th>
                <th style="width: 11%; text-align: right;">Biaya Bahan (Rp)</th>
                <th style="width: 11%; text-align: right;">Energi &amp; Upah (Rp)</th>
                <th style="width: 13%; text-align: right;">Total Biaya (Rp)</th>
                <th style="width: 10%; text-align: right;">HPP / Kg</th>
                <th style="width: 8%; text-align: center;">Jurnal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($yearlyReport['months'] as $m)
                @if ($m['has_data'])
                    @php
                        // Status Rendemen Bulanan
                        $rendemen = (float) $m['rendemen'];
                        if ($rendemen >= 33.0) {
                            $rBadgeBg = '#f0fdf4'; $rBadgeColor = '#166534'; $rBorder = '#bbf7d0';
                        } elseif ($rendemen >= 30.0) {
                            $rBadgeBg = '#fffbeb'; $rBadgeColor = '#92400e'; $rBorder = '#fde68a';
                        } else {
                            $rBadgeBg = '#fef2f2'; $rBadgeColor = '#991b1b'; $rBorder = '#fecaca';
                        }

                        $biayaBahanMinyak = $m['singkong_nilai'] + $m['minyak_nilai'];
                        $biayaEnergiUpah = $m['cng_nilai'] + $m['tk_nilai'];
                    @endphp

                    <tr class="main-data-row">
                        {{-- Nama Bulan --}}
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem;">
                                {{ $m['month_name'] }}
                            </div>
                            <span style="font-size: 0.675rem; color: #64748b;">
                                Periode {{ sprintf('%02d', $m['month_num']) }}/{{ $year }}
                            </span>
                        </td>

                        {{-- Hari Kerja --}}
                        <td style="text-align: center;">
                            <span style="font-size: 0.725rem; font-weight: 700; background: #f1f5f9; color: #334155; padding: 0.15rem 0.45rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                                {{ $m['work_days'] }} Hari
                            </span>
                        </td>

                        {{-- Singkong Mentah (Kg & Nilai) --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a; font-variant-numeric: tabular-nums;">
                                {{ number_format($m['singkong_qty'], 0, ',', '.') }} <span style="font-size: 0.675rem; color: #64748b; font-weight: 400;">kg</span>
                            </div>
                            <div style="color: #64748b; font-size: 0.675rem; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($m['singkong_nilai'], 0, ',', '.') }}
                            </div>
                        </td>

                        {{-- Output WIP (Kg) --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.85rem; color: #0284c7; font-variant-numeric: tabular-nums;">
                                {{ number_format($m['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.675rem; color: #64748b; font-weight: 400;">kg</span>
                            </div>
                        </td>

                        {{-- Rendemen (%) --}}
                        <td style="text-align: center;">
                            <span style="display: inline-block; font-weight: 700; font-size: 0.775rem; background: {{ $rBadgeBg }}; color: {{ $rBadgeColor }}; border: 1px solid {{ $rBorder }}; padding: 0.18rem 0.45rem; border-radius: 4px; font-variant-numeric: tabular-nums;">
                                {{ number_format($rendemen, 2, ',', '.') }}%
                            </span>
                        </td>

                        {{-- Biaya Bahan Baku & Minyak --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 600; font-size: 0.825rem; color: #334155; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($biayaBahanMinyak, 0, ',', '.') }}
                            </div>
                            <div style="color: #94a3b8; font-size: 0.65rem;">
                                Singkong + Minyak
                            </div>
                        </td>

                        {{-- Biaya Energi CNG & Upah --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 600; font-size: 0.825rem; color: #334155; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($biayaEnergiUpah, 0, ',', '.') }}
                            </div>
                            <div style="color: #94a3b8; font-size: 0.65rem;">
                                Gas + Tenaga Kerja
                            </div>
                        </td>

                        {{-- Total Biaya Produksi --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($m['total_biaya'], 0, ',', '.') }}
                            </div>
                            <div style="color: #64748b; font-size: 0.65rem;">
                                Seluruh Beban Operasional
                            </div>
                        </td>

                        {{-- HPP / Kg --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; font-family: monospace; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($m['hpp_per_kg'], 0, ',', '.') }}
                            </div>
                            <span style="font-size: 0.625rem; color: #059669; font-weight: 600; text-transform: uppercase;">
                                Beban Pokok
                            </span>
                        </td>

                        {{-- Aksi: Buka Jurnal Harian --}}
                        <td style="text-align: center;">
                            <a href="{{ route('produksi.rekap', ['mode' => 'harian', 'tahun' => $year, 'bulan' => $m['month_num']]) }}" class="btn-corp" style="padding: 0.25rem 0.5rem; font-size: 0.725rem; font-weight: 600;" title="Buka buku rincian harian bulan {{ $m['month_name'] }}">
                                <span>Harian &rarr;</span>
                            </a>
                        </td>
                    </tr>
                @else
                    {{-- BULAN KOSONG / BELUM ADA DATA --}}
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                        <td style="padding: 0.55rem 0.85rem; color: #94a3b8; font-weight: 600; font-size: 0.8rem;">
                            {{ $m['month_name'] }}
                        </td>
                        <td style="text-align: center; color: #cbd5e1; font-size: 0.75rem;">—</td>
                        <td colspan="7" style="padding: 0.55rem 0.85rem; color: #cbd5e1; font-style: italic; font-size: 0.75rem;">
                            Belum ada catatan operasional manufaktur pada bulan ini.
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('produksi.rekap', ['mode' => 'harian', 'tahun' => $year, 'bulan' => $m['month_num']]) }}" class="btn-corp" style="padding: 0.2rem 0.45rem; font-size: 0.7rem; color: #94a3b8; border-color: #e2e8f0;" title="Buka lembar input/rekap bulan {{ $m['month_name'] }}">
                                <span>Buka &rarr;</span>
                            </a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>

        {{-- FOOTER TOTAL SETAHUN --}}
        @php
            $an = $yearlyReport['annual_totals'];
        @endphp
        <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1; font-weight: 700; font-size: 0.825rem;">
            <tr>
                <td style="padding: 0.75rem 0.85rem; color: #0f172a; text-transform: uppercase; font-size: 0.775rem;">
                    TOTAL TAHUN {{ $year }}
                </td>
                <td style="text-align: center; padding: 0.75rem 0.85rem; color: #0f172a;">
                    {{ $an['total_work_days'] }} Hari
                </td>
                <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0f172a;">
                    <div>{{ number_format($an['singkong_qty'], 0, ',', '.') }} kg</div>
                    <div style="color: #64748b; font-weight: 500; font-size: 0.675rem;">Rp {{ number_format($an['singkong_nilai'], 0, ',', '.') }}</div>
                </td>
                <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0284c7;">
                    {{ number_format($an['total_wip_qty'], 2, ',', '.') }} kg
                </td>
                <td style="text-align: center; padding: 0.75rem 0.85rem; color: #0f172a;">
                    {{ number_format($an['rendemen'], 2, ',', '.') }}%
                </td>
                <td colspan="2" style="text-align: right; padding: 0.75rem 0.85rem; color: #64748b; font-weight: 500; font-size: 0.725rem;">
                    Akumulasi Biaya Setahun:
                </td>
                <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0f172a;">
                    Rp {{ number_format($an['total_biaya'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0f172a; font-family: monospace;">
                    Rp {{ number_format($an['hpp_per_kg'], 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
