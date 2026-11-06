@if($pakai->is_ada_koreksi || ($pakai->revisiLogs && $pakai->revisiLogs->isNotEmpty()))
<div class="card no-print" style="border: 1.5px solid #fde68a; background: #fffdf5; border-radius: 8px; margin-bottom: 1.25rem; overflow: hidden; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.08);">
    <div style="background: #fef3c7; padding: 0.75rem 1.25rem; border-bottom: 1px solid #fde68a; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <span style="font-size: 1.25rem;">🔄</span>
            <div>
                <strong style="color: #92400e; font-size: 0.95rem; display: block;">
                    Riwayat Koreksi Verifikasi Keuangan (Audit Trail Kesalahan Input)
                </strong>
                <span style="font-size: 0.775rem; color: #b45309;">
                    Dokumen ini telah mengalami penyesuaian koreksi batch/kuantitas dari data awal petugas gudang.
                </span>
            </div>
        </div>
        <span class="badge" style="background: #fef08a; color: #854d0e; border: 1px solid #facc15; font-weight: 700; font-size: 0.75rem;">
            {{ $pakai->revisiLogs->count() }} Kali Revisi
        </span>
    </div>

    <div style="padding: 1rem 1.25rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem; background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #fef3c7;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Pembuat Input Awal (Gudang):
                </span>
                <div style="font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-top: 2px;">
                    👤 {{ $pakai->creator_name }}
                </div>
            </div>
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Status Koreksi Terakhir:
                </span>
                <div style="font-weight: 700; color: #b45309; font-size: 0.875rem; margin-top: 2px;">
                    @php $latestLog = $pakai->revisiLogs->first(); @endphp
                    Dikoreksi oleh <strong>{{ $latestLog?->verifikator_nama ?? 'Verifikator' }}</strong> 
                    ({{ $latestLog?->revisi_tgl ? $latestLog->revisi_tgl->format('d/m/Y H:i') : '-' }})
                </div>
            </div>
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Kategori Kesalahan:
                </span>
                <div style="margin-top: 2px;">
                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.75rem; border: 1px solid #fecaca;">
                        {{ $latestLog?->kategori_kesalahan ?? 'KOREKSI_FISIK' }}
                    </span>
                </div>
            </div>
        </div>

        @foreach($pakai->revisiLogs as $idx => $log)
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 0.75rem; overflow: hidden;">
                <div style="padding: 0.6rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-family: monospace; font-weight: 800; font-size: 0.8rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #334155;">
                            {{ $log->revisi_no }}
                        </span>
                        <span style="font-size: 0.775rem; color: #64748b;">
                            {{ $log->revisi_tgl ? $log->revisi_tgl->format('d M Y, H:i') : '-' }} WIB
                        </span>
                        <span style="font-size: 0.775rem; color: #0284c7; font-weight: 600;">
                            &bull; Oleh: <strong>{{ $log->verifikator_nama }}</strong>
                        </span>
                    </div>
                    <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.725rem; font-weight: 700;">
                        {{ $log->kategori_kesalahan }}
                    </span>
                </div>

                <div style="padding: 0.75rem 1rem;">
                    @if($log->catatan_koreksi)
                        <div style="margin-bottom: 0.65rem; font-size: 0.8rem; color: #334155; background: #f8fafc; padding: 0.45rem 0.75rem; border-radius: 4px; border-left: 3px solid #f59e0b;">
                            <strong>Catatan Verifikator:</strong> {{ $log->catatan_koreksi }}
                        </div>
                    @endif

                    @if(!empty($log->perubahan_json) && is_array($log->perubahan_json))
                        <div style="font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                            Rincian Perubahan Data:
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.785rem;">
                                <thead>
                                    <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                                        <th style="padding: 4px 8px; width: 30px;">#</th>
                                        <th style="padding: 4px 8px; width: 140px;">Bahan Produksi</th>
                                        <th style="padding: 4px 8px; width: 130px;">Tipe Koreksi</th>
                                        <th style="padding: 4px 8px;">Keterangan &amp; Perubahan Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($log->perubahan_json as $pIdx => $diff)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 5px 8px; color: #64748b;">{{ $pIdx + 1 }}</td>
                                            <td style="padding: 5px 8px; font-weight: 700; color: #0f172a;">{{ $diff['barang_nm'] ?? '-' }}</td>
                                            <td style="padding: 5px 8px;">
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.7rem; font-weight: 700;">
                                                    {{ $diff['tipe'] ?? 'KOREKSI' }}
                                                </span>
                                            </td>
                                            <td style="padding: 5px 8px; color: #334155;">{{ $diff['keterangan'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

