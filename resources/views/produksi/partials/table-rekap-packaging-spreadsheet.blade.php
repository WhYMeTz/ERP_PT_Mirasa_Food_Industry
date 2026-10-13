{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{--  TABEL REKAPITULASI HPP PENGEMASAN - FORMAT SPREADSHEET MATRIX ENTERPRISE  --}}
{{--  ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div class="card" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                    Spreadsheet Matriks Pengemasan &amp; Beban HPP Riil
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Buku Besar Jurnal Kemas Harian
                </span>
            </div>
            <span style="font-size: 0.725rem; background: #eff6ff; color: #1d4ed8; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 700; border: 1px solid #bfdbfe;">
                {{ $report['count'] }} Hari Aktif &bull; Format Spreadsheet Lengkap
            </span>
        </div>

        {{-- Switch Mode Tampilan --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="view-mode-pill-group">
                <button type="button" id="btn-mode-compact-sp" class="btn-view-mode" onclick="switchHppViewMode('compact')" title="Tampilan ringkas pas di layar">
                    <span>Ringkasan Eksekutif</span>
                </button>
                <button type="button" id="btn-mode-spreadsheet-sp" class="btn-view-mode active" onclick="switchHppViewMode('spreadsheet')" title="Tampilan spreadsheet matrix lengkap">
                    <span>Spreadsheet Lengkap</span>
                </button>
            </div>
        </div>
    </div>

    <div style="overflow-x: auto; width: 100%;">
        <table class="table-compact-hpp" style="min-width: 1250px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="width: 80px; text-align: center;">HARI</th>
                    <th style="width: 95px; text-align: center;">TANGGAL</th>
                    <th style="width: 130px;">NO DOKUMEN</th>
                    <th style="width: 170px;">PRODUK JADI (FG)</th>
                    <th style="width: 110px; text-align: center;">BATCH NO</th>
                    <th style="text-align: right;">BIAYA WIP (RP)</th>
                    <th style="text-align: right;">KEMASAN (RP)</th>
                    <th style="text-align: right;">UPAH PACKING (RP)</th>
                    <th style="text-align: right;">LAIN / FOH (RP)</th>
                    <th style="text-align: right; background: #fefce8; color: #854d0e;">TOTAL BIAYA (RP)</th>
                    <th style="text-align: center; width: 90px;">HASIL (KTN)</th>
                    <th style="text-align: right; width: 90px;">HASIL (PCS)</th>
                    <th style="text-align: right; background: #ecfdf5; color: #065f46;">HPP / KARTON</th>
                    <th style="text-align: right;">HPP / PCS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['days'] as $d)
                    @if ($d['has_data'])
                        <tr>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $d['hari_nm'] }}</td>
                            <td style="text-align: center; font-size: 0.8rem;">{{ date('d/m/Y', strtotime($d['date'])) }}</td>
                            <td>
                                @if(!empty($d['primary_pack_id']))
                                    <a href="{{ route('produksi.packaging.show', $d['primary_pack_id']) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                                        {{ $d['primary_pack_no'] }}
                                    </a>
                                @else
                                    <span style="color: #64748b;">-</span>
                                @endif
                            </td>
                            <td style="font-weight: 700; color: #1e293b;">{{ $d['primary_product_nm'] }}</td>
                            <td style="text-align: center; font-family: monospace; font-size: 0.775rem;">{{ $d['primary_batch_no'] }}</td>
                            <td style="text-align: right;">Rp {{ number_format($d['total_biaya_wip'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($d['total_biaya_kemasan'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($d['total_biaya_tenaga_kerja'], 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($d['total_biaya_lain'], 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: 800; background: #fffbeb; color: #92400e;">
                                Rp {{ number_format($d['total_biaya_produksi'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #059669;">
                                {{ number_format($d['hasil_karton'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right;">
                                {{ number_format($d['hasil_pcs'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 800; font-family: monospace; background: #f0fdf4; color: #166534;">
                                Rp {{ number_format($d['hpp_per_karton'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: #475569;">
                                Rp {{ number_format($d['hpp_per_pcs'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @else
                        <tr style="background: #fbfcfd; color: #cbd5e1;">
                            <td style="text-align: center;">{{ $d['hari_nm'] }}</td>
                            <td style="text-align: center; font-size: 0.8rem;">{{ date('d/m/Y', strtotime($d['date'])) }}</td>
                            <td colspan="12" style="text-align: center; font-size: 0.75rem; color: #94a3b8; font-style: italic;">
                                &mdash; Tidak ada proses pengemasan &mdash;
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
            @php
                $tot = $report['totals'];
            @endphp
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                    <td colspan="5" style="text-align: center; text-transform: uppercase;">
                        TOTAL BULAN {{ strtoupper($monthName) }} {{ $year }}
                    </td>
                    <td style="text-align: right;">Rp {{ number_format($tot['total_biaya_wip'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($tot['total_biaya_kemasan'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($tot['total_biaya_tenaga_kerja'], 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($tot['total_biaya_lain'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: 800; background: #fef3c7; color: #92400e;">
                        Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; color: #059669;">
                        {{ number_format($tot['total_karton'], 0, ',', '.') }} Ktn
                    </td>
                    <td style="text-align: right;">
                        {{ number_format($tot['total_pcs'], 0, ',', '.') }} Pcs
                    </td>
                    <td style="text-align: right; font-family: monospace; background: #d1fae5; color: #065f46;">
                        Rp {{ number_format($tot['rata_hpp_karton'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-family: monospace;">
                        Rp {{ number_format($tot['rata_hpp_pcs'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
