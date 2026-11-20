{{-- WORKSHEET PENYESUAIAN GAS CNG HARIAN --}}
{{-- TOOLBAR CEPAT (BULK OFFSET HELPER) --}}
<div class="quick-bulk-toolbar">
    <div class="bulk-toolbar-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
        <span>Alat Bantu Penyesuaian Serentak (Bulk Offset):</span>
    </div>

    <div class="bulk-action-controls">
        <label for="input_bulk_offset" style="font-size: 0.775rem; color: #475569; font-weight: 600; margin: 0;">
            Tambah / Kurang:
        </label>
        <input type="number" step="any" 
               id="input_bulk_offset" 
               class="input-bulk-step" 
               placeholder="0.0000" 
               title="Ketik angka selisih penyesuaian">
        <select id="select_bulk_unit" style="font-size: 0.75rem; font-weight: 700; color: #334155; border: 1.5px solid #cbd5e1; border-radius: 4px; padding: 0.15rem 0.35rem; height: 30px; background: #ffffff;">
            <option value="MMBTU">MMBTU / hari</option>
            <option value="RP">Rp (Nominal) / hari</option>
        </select>

        <button type="button" onclick="applyBulkOffset()" class="btn-bulk-apply" title="Tambahkan angka di atas ke seluruh hari kerja secara serentak">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            <span>Terapkan ke Seluruh Hari</span>
        </button>

        <button type="button" onclick="resetAllDailyInputs()" class="btn-bulk-reset" title="Kembalikan semua angka ke catatan awal lapangan">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Reset Awal</span>
        </button>
    </div>
</div>

{{-- FORM UTAMA TABEL PENYESUAIAN HARIAN --}}
<form id="formDailyCngBalancing" action="{{ route('produksi.adjust-cng-harian.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tahun" value="{{ $year }}">
    <input type="hidden" name="bulan" value="{{ $month }}">
    <input type="hidden" name="pabrik" value="{{ $pabrikFilter }}">
    <input type="hidden" name="alasan_txt" id="input_hidden_alasan" value="Rekonsiliasi &amp; balancing pemakaian gas CNG periode {{ $monthsList[$month] }} {{ $year }}">

    <div class="card" style="border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 1.25rem; background: #ffffff;">
        <div class="card-header" style="background: #f8fafc; padding: 0.85rem 1.25rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span style="font-size: 0.925rem; font-weight: 800; color: #0f172a;">
                    Lembar Kerja Penyesuaian Gas Harian ({{ $workDaysCount }} Hari Kerja &bull; {{ $monthsList[$month] }} {{ $year }})
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Ketik angka baru pada kolom <strong>MMBTU Baru</strong> untuk mengoreksi hari tertentu.
                </span>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <div style="font-size: 0.725rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.25rem 0.55rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                    <span>Kurs Terkunci Per Tanggal Produksi</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="confirmOpenBalancingModal()" style="font-size: 0.775rem; font-weight: 700; background: #059669; border-color: #047857; padding: 0.35rem 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(5, 150, 105, 0.2);">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Penyesuaian</span>
                </button>
            </div>
        </div>

        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table-cng-balancing" id="tableCngBalancing">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">No</th>
                        <th style="width: 130px;">Tanggal &amp; Hari</th>
                        <th style="width: 140px;">Dokumen SPK</th>
                        <th style="text-align: right; width: 110px;">Output WIP</th>
                        <th style="text-align: right; width: 130px;">Kurs JISDOR BI</th>
                        <th style="text-align: right; width: 110px;">MMBTU Awal</th>
                        <th style="text-align: right; width: 130px; background: #fffbeb; color: #92400e;">MMBTU Baru (Edit)</th>
                        <th style="text-align: center; width: 90px;">&Delta; MMBTU</th>
                        <th style="text-align: right; width: 125px;">Biaya Awal</th>
                        <th style="text-align: right; width: 140px; background: #fffbeb; color: #92400e;">Biaya Baru (Edit Rp)</th>
                        <th style="text-align: center; width: 100px;">&Delta; Biaya Rp</th>
                        <th style="text-align: right; width: 130px;">Estimasi HPP</th>
                        <th style="width: 160px;">Catatan Koreksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prodDays as $d)
                        @php
                            $dDate = $d['date'];
                            $dOldMmbtu = (float) $d['cng_mmbtu'];
                            $dOldNilai = (float) $d['cng_nilai'];
                            $dOldTotalBiaya = (float) $d['total_biaya_produksi'];
                            $dWip = (float) $d['total_wip_qty'];
                            $dJisdor = (float) ($d['cng_kurs_jisdor'] ?? 18138);
                            $dUsd = (float) ($d['cng_tarif_usd'] ?? $defaultCngTarifUsd);
                            $dTarifRp = (float) ($d['cng_tarif'] ?? round($dUsd * $dJisdor, 2));
                            $dHpp = (float) $d['hpp_per_kg'];
                        @endphp
                        <tr class="cng-daily-row" 
                            id="row_cng_{{ $dDate }}"
                            data-date="{{ $dDate }}"
                            data-old-mmbtu="{{ $dOldMmbtu }}"
                            data-old-cng-nilai="{{ $dOldNilai }}"
                            data-old-total-biaya="{{ $dOldTotalBiaya }}"
                            data-cng-tarif="{{ $dTarifRp }}"
                            data-cng-jisdor="{{ $dJisdor }}"
                            data-cng-usd="{{ $dUsd }}"
                            data-wip-qty="{{ $dWip }}"
                            data-old-hpp="{{ $dHpp }}">
                            
                            <td style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <strong style="color: #0f172a; font-size: 0.8rem;">{{ date('d/m/Y', strtotime($dDate)) }}</strong>
                                <div style="font-size: 0.7rem; color: #64748b;">{{ $d['hari_nm'] }}</div>
                            </td>

                            <td>
                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #334155;">{{ $d['primary_produksi_no'] }}</span>
                                    @if(!empty($d['entity_summary']['has_mfi']))
                                        <span style="font-size: 0.625rem; font-weight: 800; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 1px 4px; border-radius: 3px;" title="PT Mirasa Food Industry">MFI</span>
                                    @endif
                                    @if(!empty($d['entity_summary']['has_bmb']))
                                        <span style="font-size: 0.625rem; font-weight: 800; background: #faf5ff; color: #7e22ce; border: 1px solid #f3e8ff; padding: 1px 4px; border-radius: 3px;" title="CV Bahtera Mandiri Bersama">BMB</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.675rem; color: #64748b;">Shift {{ $d['shift_cd'] }}</div>
                            </td>

                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: ui-monospace, monospace;">
                                {{ number_format($dWip, 0, ',', '.') }} kg
                            </td>

                            <td style="text-align: right; color: #0284c7; font-weight: 700; font-family: ui-monospace, monospace;">
                                Rp {{ number_format($dJisdor, 0, ',', '.') }}
                                <div style="font-size: 0.65rem; color: #64748b; font-weight: normal;">${{ number_format($dUsd, 2) }} / MMBTU</div>
                            </td>

                            <td style="text-align: right; color: #475569; font-weight: 600; font-family: ui-monospace, monospace;">
                                {{ number_format($dOldMmbtu, 4, ',', '.') }}
                                <div style="font-size: 0.65rem; color: #0284c7; font-weight: normal;">{{ number_format($dOldMmbtu * 27.921, 2, ',', '.') }} Nm³</div>
                            </td>

                            <td style="text-align: right; background: #fffdf5;">
                                <input type="hidden" name="days[{{ $dDate }}][old_mmbtu]" value="{{ $dOldMmbtu }}">
                                <input type="hidden" name="days[{{ $dDate }}][old_nilai]" value="{{ $dOldNilai }}">
                                <input type="hidden" name="days[{{ $dDate }}][cng_kurs_jisdor]" value="{{ $dJisdor }}">
                                <input type="hidden" name="days[{{ $dDate }}][cng_tarif_usd]" value="{{ $dUsd }}">
                                <input type="number" step="any" min="0" 
                                       name="days[{{ $dDate }}][new_mmbtu]" 
                                       class="input-cng-edit input-cng-mmbtu" 
                                       value="{{ round($dOldMmbtu, 4) }}" 
                                       oninput="onCngMmbtuChange('{{ $dDate }}')"
                                       title="Ketik kuantitas fisik MMBTU baru untuk tanggal ini">
                                <div style="font-size: 0.65rem; color: #0284c7; text-align: right; margin-top: 2px;" class="new-nm3-preview" data-date="{{ $dDate }}">
                                    &approx; {{ number_format($dOldMmbtu * 27.921, 2, ',', '.') }} Nm³
                                </div>
                            </td>

                            <td style="text-align: center;">
                                <span class="badge-cng-variance badge-cng-zero badge-delta-mmbtu">0</span>
                            </td>

                            <td style="text-align: right; color: #475569; font-weight: 600; font-family: ui-monospace, monospace;">
                                Rp {{ number_format($dOldNilai, 0, ',', '.') }}
                            </td>

                            <td style="text-align: right; background: #fffdf5;">
                                <input type="number" step="1" min="0" 
                                       name="days[{{ $dDate }}][new_nilai]" 
                                       class="input-cng-edit input-cng-nilai" 
                                       value="{{ round($dOldNilai) }}" 
                                       oninput="onCngNilaiChange('{{ $dDate }}')"
                                       title="Ketik nominal rupiah biaya gas baru untuk tanggal ini">
                            </td>

                            <td style="text-align: center;">
                                <span class="badge-cng-variance badge-cng-zero badge-delta-rp">Rp 0</span>
                            </td>

                            <td style="text-align: right; font-weight: 700; color: #059669; font-family: ui-monospace, monospace;" class="cell-cng-hpp-baru">
                                Rp {{ number_format($dHpp, 2, ',', '.') }}
                            </td>

                            <td>
                                <input type="text" 
                                       name="days[{{ $dDate }}][catatan]" 
                                       class="form-control" 
                                       style="font-size: 0.75rem; padding: 0.2rem 0.4rem; height: 28px;"
                                       placeholder="Alasan selisih meter...">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 2.5rem; color: #64748b;">
                                <svg width="36" height="36" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="margin: 0 auto 0.5rem auto; display: block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Belum ada lembar catatan produksi untuk <strong>{{ $pabrikFilter === 'ALL' ? 'Semua Lokasi Pabrik' : ($pabrikFilter === 'BMB' ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry') }}</strong> pada periode <strong>{{ $monthsList[$month] }} {{ $year }}</strong>.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="cng-tfoot-row">
                        <td colspan="3" style="text-align: right; font-weight: 800; text-transform: uppercase;">
                            TOTAL BULANAN ({{ $workDaysCount }} HARI):
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace;">
                            {{ number_format($totalWipKg, 0, ',', '.') }} kg
                        </td>
                        <td></td>
                        <td style="text-align: right; font-family: ui-monospace, monospace;" id="footOldMmbtu">
                            {{ number_format($totalMmbtu, 4, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; color: #b45309; font-size: 0.95rem;" id="footNewMmbtu">
                            {{ number_format($totalMmbtu, 4, ',', '.') }}
                        </td>
                        <td style="text-align: center;" id="footDeltaMmbtu">
                            <span class="badge-cng-variance badge-cng-zero">0</span>
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; color: #475569;" id="footOldNilai">
                            Rp {{ number_format($totalNilaiRp, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; color: #d97706; font-size: 0.95rem;" id="footNewNilai">
                            Rp {{ number_format($totalNilaiRp, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;" id="footDeltaRp">
                            <span class="badge-cng-variance badge-cng-zero">Rp 0</span>
                        </td>
                        <td style="text-align: right; font-family: ui-monospace, monospace; color: #059669;" id="footAvgHpp">
                            Rp 0,00
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- BAR AKSI SIMPAN PENYESUAIAN (BOTTOM ACTION BAR ELEGAN & BERSIH) --}}
    <div class="bottom-submit-bar" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1rem 1.35rem; margin-top: 1.25rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; max-width: 65%;">
            <div style="width: 36px; height: 36px; border-radius: 6px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #cbd5e1;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <strong style="color: #0f172a; font-size: 0.825rem; display: block; margin-bottom: 0.15rem;">Catatan Audit Balancing:</strong>
                <span style="font-size: 0.775rem; color: #64748b; line-height: 1.4; display: block;">
                    Menyimpan formulir ini akan memperbarui nilai MMBTU, biaya CNG, dan mengkalkulasi ulang HPP per kg pada seluruh dokumen produksi terkait secara otomatis.
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 0.65rem; align-items: center; flex-shrink: 0;">
            <button type="button" class="btn btn-secondary" onclick="resetAllDailyInputs()" style="font-size: 0.8125rem; font-weight: 600; padding: 0.55rem 1.15rem; border-radius: 6px; background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569;">
                Batal / Reset
            </button>
            <button type="button" class="btn btn-primary" onclick="confirmOpenBalancingModal()" style="font-size: 0.8125rem; font-weight: 700; background: #059669; border-color: #047857; padding: 0.55rem 1.35rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.2);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Penyesuaian Gas Harian</span>
            </button>
        </div>
    </div>
</form>

{{-- MODAL KONFIRMASI AUDIT PENYESUAIAN CNG HARIAN --}}
@include('produksi.partials.modal-confirm-adjust-cng')
