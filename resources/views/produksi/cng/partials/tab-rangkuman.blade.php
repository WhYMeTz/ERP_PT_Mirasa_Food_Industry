{{-- TABEL RANGKUMAN PENGGUNAAN GAS CNG HARIAN (READ-ONLY) --}}
<div class="cng-table-card">
    <div class="cng-table-header">
        <div>
            <div class="cng-table-title">
                <span>Rangkuman Pemakaian Gas Harian ({{ $workDaysCount }} Hari Kerja &bull; {{ $monthsList[$month] }} {{ $year }})</span>
            </div>
            <p style="margin: 0.2rem 0 0 0; font-size: 0.775rem; color: #64748b;">
                Data otomatis terakumulasi dari lembar input HPP harian. Cocokkan angka ini dengan rincian faktur vendor.
            </p>
        </div>

        <div class="cng-table-actions no-print">
            @if(($pabrikFilter ?? 'ALL') !== 'ALL')
                <button type="button" onclick="switchCngTab('harian')" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Penyesuaian Harian</span>
                </button>

                <button type="button" onclick="switchCngTab('bulanan')" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Rekonsiliasi Faktur</span>
                </button>
            @endif

            <button type="button" onclick="printCngSummary()" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Rangkuman</span>
            </button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-cng-clean">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="width: 120px;">Tanggal &amp; Hari</th>
                    <th style="width: 140px;">Dokumen SPK</th>
                    <th style="text-align: right; width: 110px;">Total WIP</th>
                    <th style="text-align: right; width: 115px;">MMBTU</th>
                    <th style="text-align: right; width: 115px;">Nm³ (Meteran)</th>
                    <th style="text-align: center; width: 90px;">% Rasio</th>
                    <th style="text-align: right; width: 155px;">Tarif / MMBTU</th>
                    <th style="text-align: right; width: 145px;">Penggunaan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prodDays as $d)
                    @php
                        $dDate = $d['date'];
                        $dMmbtu = (float) $d['cng_mmbtu'];
                        $dNm3 = (float) ($d['cng_nm3'] ?? ($dMmbtu * 27.921));
                        $dNilai = (float) $d['cng_nilai'];
                        $dWip = (float) $d['total_wip_qty'];
                        $dJisdor = (float) ($d['cng_kurs_jisdor'] ?? 18138);
                        $dUsd = (float) ($d['cng_tarif_usd'] ?? $defaultCngTarifUsd);
                        $dTarifRp = (float) ($d['cng_tarif'] ?? round($dUsd * $dJisdor, 2));
                        
                        // % Rasio konsumsi gas terhadap hasil produksi fisik keripik WIP
                        $gasPercent = $dWip > 0 ? (($dMmbtu / $dWip) * 100) : 0;
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            <strong style="color: #0f172a;">{{ date('d/m/Y', strtotime($dDate)) }}</strong>
                            <div style="font-size: 0.7rem; color: #64748b;">{{ $d['hari_nm'] }}</div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                <span style="font-size: 0.75rem; font-weight: 700; color: #1e293b;">{{ $d['primary_produksi_no'] }}</span>
                                @if(!empty($d['entity_summary']['has_mfi']))
                                    <span style="font-size: 0.625rem; font-weight: 800; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 1px 4px; border-radius: 3px;" title="PT Mirasa Food Industry">MFI</span>
                                @endif
                                @if(!empty($d['entity_summary']['has_bmb']))
                                    <span style="font-size: 0.625rem; font-weight: 800; background: #faf5ff; color: #7e22ce; border: 1px solid #f3e8ff; padding: 1px 4px; border-radius: 3px;" title="CV Bahtera Mandiri Bersama">BMB</span>
                                @endif
                            </div>
                            <div style="font-size: 0.675rem; color: #64748b;">Shift {{ $d['shift_cd'] }} &bull; {{ $d['lini_produksi'] }}</div>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #0f172a; font-family: ui-monospace, monospace;">
                            {{ number_format($dWip, 0, ',', '.') }} kg
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: ui-monospace, monospace;">
                            {{ number_format($dMmbtu, 3, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #475569; font-family: ui-monospace, monospace;">
                            {{ number_format($dNm3, 3, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-family: ui-monospace, monospace; font-size: 0.775rem;">
                            @if($gasPercent > 0)
                                <span style="font-weight: 700; color: #0f172a;">
                                    {{ number_format($gasPercent, 2, ',', '.') }}%
                                </span>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; color: #475569;">
                            <strong style="color: #0f172a;">Rp {{ number_format($dTarifRp, 0, ',', '.') }}</strong>
                            @if($dJisdor > 0)
                                <div style="font-size: 0.675rem; color: #64748b; margin-top: 2px;">
                                    JISDOR: Rp {{ number_format($dJisdor, 0, ',', '.') }} &bull; ${{ number_format($dUsd, 2) }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: ui-monospace, monospace;">
                            Rp {{ number_format($dNilai, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: #64748b;">
                            <svg width="36" height="36" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="margin: 0 auto 0.5rem auto; display: block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Belum ada lembar catatan produksi untuk <strong>{{ ($pabrikFilter ?? 'ALL') === 'ALL' ? 'Semua Lokasi Pabrik' : (($pabrikFilter ?? '') === 'BMB' ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry') }}</strong> pada periode <strong>{{ $monthsList[$month] }} {{ $year }}</strong>.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($prodDays->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; text-transform: uppercase;">
                            TOTAL AKUMULASI ({{ $workDaysCount }} HARI KERJA):
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 700; color: #0f172a;">
                            {{ number_format($totalWipKg, 0, ',', '.') }} kg
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 700; color: #0f172a;">
                            {{ number_format($totalMmbtu, 3, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 600; color: #475569;">
                            {{ number_format($totalNm3, 3, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-family: ui-monospace, monospace; font-size: 0.775rem; font-weight: 800; color: #0f172a;">
                            {{ $totalWipKg > 0 ? number_format(($totalMmbtu / $totalWipKg) * 100, 2, ',', '.') . '%' : '-' }}
                        </td>
                        <td style="text-align: right; font-size: 0.725rem; color: #64748b;">
                            Rata-rata: {{ number_format($avgMmbtuPerDay, 2, ',', '.') }}/hari
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                            Rp {{ number_format($totalNilaiRp, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
