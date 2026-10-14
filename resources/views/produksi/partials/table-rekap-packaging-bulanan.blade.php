{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{--  TABEL REKAPITULASI HPP PENGEMASAN BULANAN (12 BULAN KONSOLIDASI TAHUNAN) --}}
{{--  ERP PT Mirasa Food Industry                                              --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}

<div class="card" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                    Konsolidasi HPP Pengemasan Bulanan Tahun {{ $year }}
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Buku Besar Akumulasi Biaya Kemas per Bulan (Tren 12 Bulan)
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.725rem; background: #f0fdf4; color: #166534; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 700; border: 1px solid #bbf7d0;">
                Total: {{ $yearlyReport['totals']['annual_count'] }} Dokumen Pengemasan Terdata
            </span>
        </div>
    </div>

    {{-- KONTEN TABEL BULANAN --}}
    <table class="table-compact-hpp">
        <thead>
            <tr>
                <th style="width: 14%;">Bulan</th>
                <th style="width: 8%; text-align: center;">Dokumen</th>
                <th style="width: 13%; text-align: right;">Biaya Curah WIP (Rp)</th>
                <th style="width: 13%; text-align: right;">Biaya Kemasan (Rp)</th>
                <th style="width: 11%; text-align: right;">Upah Packing (Rp)</th>
                <th style="width: 14%; text-align: right;">Total Biaya Kemas (Rp)</th>
                <th style="width: 10%; text-align: center;">Hasil (Karton)</th>
                <th style="width: 11%; text-align: right;">HPP / Karton</th>
                <th style="width: 8%; text-align: center;">Jurnal Harian</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($yearlyReport['months'] as $m)
                @if ($m['has_data'])
                    <tr class="main-data-row">
                        {{-- Nama Bulan --}}
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                                {{ $m['month_name'] }} {{ $year }}
                            </div>
                            <span style="font-size: 0.685rem; color: #64748b;">
                                Periode Ke-{{ $m['month_num'] }}
                            </span>
                        </td>

                        {{-- Dokumen --}}
                        <td style="text-align: center;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1.5px 6px; border-radius: 4px;">
                                {{ $m['count_dokumen'] }} Dok
                            </span>
                        </td>

                        {{-- Biaya Curah WIP --}}
                        <td style="text-align: right; font-weight: 700;">
                            Rp {{ number_format($m['total_biaya_wip'], 0, ',', '.') }}
                        </td>

                        {{-- Biaya Kemasan --}}
                        <td style="text-align: right; font-weight: 700;">
                            Rp {{ number_format($m['total_biaya_kemasan'], 0, ',', '.') }}
                        </td>

                        {{-- Upah Packing --}}
                        <td style="text-align: right; font-weight: 700;">
                            Rp {{ number_format($m['total_biaya_tenaga_kerja'], 0, ',', '.') }}
                        </td>

                        {{-- Total Biaya Kemas --}}
                        <td style="text-align: right; font-weight: 800; color: #0f172a; font-size: 0.875rem;">
                            Rp {{ number_format($m['total_biaya_produksi'], 0, ',', '.') }}
                        </td>

                        {{-- Hasil Karton --}}
                        <td style="text-align: center;">
                            <span style="font-weight: 800; font-size: 0.825rem; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 7px; border-radius: 4px;">
                                {{ number_format($m['total_karton'], 0, ',', '.') }} Ktn
                            </span>
                        </td>

                        {{-- HPP / Karton --}}
                        <td style="text-align: right; font-family: monospace; font-size: 0.875rem; font-weight: 800; color: #059669;">
                            Rp {{ number_format($m['hpp_per_karton'], 0, ',', '.') }}
                        </td>

                        {{-- Buka Jurnal Harian Bulan Ini --}}
                        <td style="text-align: center;">
                            <a href="{{ route('produksi.rekap', ['kategori' => 'packaging', 'mode' => 'harian', 'tahun' => $year, 'bulan' => $m['month_num']]) }}" 
                               class="btn btn-secondary" 
                               style="padding: 0.25rem 0.65rem; font-size: 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; color: #0284c7; font-weight: 700; text-decoration: none;"
                               title="Buka rincian harian bulan {{ $m['month_name'] }}">
                                <span>Buka</span>
                            </a>
                        </td>
                    </tr>
                @else
                    {{-- BULAN TANPA DATA --}}
                    <tr style="background: #fbfcfd; color: #94a3b8;">
                        <td>
                            <div style="font-weight: 600; color: #94a3b8; font-size: 0.85rem;">
                                {{ $m['month_name'] }} {{ $year }}
                            </div>
                        </td>
                        <td colspan="7" style="text-align: center; font-size: 0.775rem; color: #94a3b8; font-style: italic;">
                            &mdash; Belum ada data pengemasan &mdash;
                        </td>
                        <td style="text-align: center; color: #cbd5e1;">-</td>
                    </tr>
                @endif
            @endforeach
        </tbody>

        {{-- GRAND TOTAL TAHUNAN --}}
        @php
            $tot = $yearlyReport['totals'];
        @endphp
        <tfoot>
            <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                <td colspan="2" style="font-size: 0.85rem; color: #0f172a; text-transform: uppercase;">
                    GRAND TOTAL TAHUN {{ $year }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.85rem;">
                    Rp {{ number_format($tot['annual_wip'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.85rem;">
                    Rp {{ number_format($tot['annual_kemasan'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.85rem;">
                    Rp {{ number_format($tot['annual_tk'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.95rem;">
                    Rp {{ number_format($tot['annual_biaya'], 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #059669; font-size: 0.9rem;">
                    {{ number_format($tot['annual_karton'], 0, ',', '.') }} Ktn
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #059669;">
                    Rp {{ number_format($tot['annual_hpp_karton'], 0, ',', '.') }}
                </td>
                <td style="text-align: center; font-size: 0.725rem; color: #64748b;">
                    Rp {{ number_format($tot['annual_hpp_pcs'], 0, ',', '.') }} / pcs
                </td>
            </tr>
        </tfoot>
    </table>
</div>
