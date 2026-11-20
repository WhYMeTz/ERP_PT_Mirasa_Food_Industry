@if($pakai->is_ada_koreksi || ($pakai->revisiLogs && $pakai->revisiLogs->isNotEmpty()))
<div class="card no-print" style="border: 1.5px solid #fde68a; background: #fffdf5; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.08);">
    <div style="background: #fef3c7; padding: 0.85rem 1.25rem; border-bottom: 1px solid #fde68a; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #fde68a; color: #92400e; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                🔄
            </div>
            <div>
                <strong style="color: #92400e; font-size: 0.95rem; display: block;">
                    Catatan Koreksi Verifikasi Keuangan (Audit Kesalahan Input)
                </strong>
                <span style="font-size: 0.8rem; color: #b45309;">
                    Dokumen ini telah disesuaikan oleh Bagian Verifikasi Keuangan karena terdapat perbedaan alokasi batch atau kuantitas timbangan fisik dari input awal petugas gudang.
                </span>
            </div>
        </div>
        <span class="badge" style="background: #fef08a; color: #854d0e; border: 1px solid #facc15; font-weight: 700; font-size: 0.775rem;">
            {{ $pakai->revisiLogs->count() }} Kali Revisi
        </span>
    </div>

    <div style="padding: 1rem 1.25rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem; background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #fef3c7;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Staf Gudang Pembuat Dokumen:
                </span>
                <div style="font-weight: 800; color: #0f172a; font-size: 0.925rem; margin-top: 2px;">
                    👤 {{ $pakai->creator_name }}
                </div>
            </div>
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Diverifikasi &amp; Dikoreksi Oleh:
                </span>
                <div style="font-weight: 700; color: #b45309; font-size: 0.875rem; margin-top: 2px;">
                    @php $latestLog = $pakai->revisiLogs->first(); @endphp
                    <strong>{{ $latestLog?->verifikator_nama ?? 'Verifikator Keuangan' }}</strong> 
                    ({{ $latestLog?->revisi_tgl ? $latestLog->revisi_tgl->format('d/m/Y H:i') : '-' }})
                </div>
            </div>
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Kategori Penyesuaian:
                </span>
                <div style="margin-top: 2px;">
                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.75rem; border: 1px solid #fecaca;">
                        {{ $latestLog?->kategori_kesalahan ?? 'PENYESUAIAN_FISIK' }}
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
                        <div style="margin-bottom: 0.65rem; font-size: 0.825rem; color: #334155; background: #f8fafc; padding: 0.5rem 0.85rem; border-radius: 4px; border-left: 3px solid #f59e0b;">
                            <strong>Memo Verifikator:</strong> {{ $log->catatan_koreksi }}
                        </div>
                    @endif

                    @if(!empty($log->perubahan_json) && is_array($log->perubahan_json))
                        <div style="font-size: 0.775rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">
                            Rincian Data yang Diperbaiki:
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                                <thead>
                                    <tr style="background: #f1f5f9; text-align: left; color: #475569;">
                                        <th style="padding: 5px 8px; width: 30px;">#</th>
                                        <th style="padding: 5px 8px; width: 160px;">Bahan Produksi</th>
                                        <th style="padding: 5px 8px; width: 140px;">Tipe Koreksi</th>
                                        <th style="padding: 5px 8px;">Keterangan / Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($log->perubahan_json as $pIdx => $diff)
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 6px 8px; color: #64748b;">{{ $pIdx + 1 }}</td>
                                            <td style="padding: 6px 8px; font-weight: 700; color: #0f172a;">{{ $diff['barang_nm'] ?? '-' }}</td>
                                            <td style="padding: 6px 8px;">
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.72rem; font-weight: 700;">
                                                    {{ $diff['tipe'] ?? 'KOREKSI' }}
                                                </span>
                                            </td>
                                            <td style="padding: 6px 8px; color: #334155;">{{ $diff['keterangan'] ?? '-' }}</td>
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

