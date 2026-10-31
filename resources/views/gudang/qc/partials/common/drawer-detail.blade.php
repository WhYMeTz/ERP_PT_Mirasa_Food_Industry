{{-- 
    Partial: Expandable Sub-Row Drawer Rincian QC & Audit Trail 
    File: resources/views/gudang/qc/partials/common/drawer-detail.blade.php
--}}
<tr id="drawer-{{ $qc->qc_id }}" class="qc-drawer-row" style="display: none;">
    <td colspan="10" class="qc-drawer-cell">
        <div class="qc-drawer-box">
            {{-- 1. HEADER DOSSIER & METADATA AUDIT (CLEAN & ELEGANT) --}}
            <div class="qc-drawer-header">
                <div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span>📋 Dossier QC: <strong>#{{ $qc->qc_no }}</strong></span>
                        <span style="font-size: 0.775rem; background: #0f172a; color: #ffffff; padding: 2px 8px; border-radius: 5px; font-weight: 700; letter-spacing: 0.02em;">
                            🚛 {{ $qc->plat_nomor_truk ?: 'Tanpa Plat' }}
                        </span>
                        @if ($qc->po)
                            <a href="{{ route('gudang.po.show', $qc->po->po_id) }}" style="font-size: 0.75rem; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; padding: 2px 8px; border-radius: 5px; font-weight: 700; text-decoration: none;">
                                PO: #{{ $qc->po->po_no }}
                            </a>
                        @endif
                    </div>
                    <div style="font-size: 0.775rem; color: #64748b; margin-top: 0.3rem;">
                        Supplier: <strong style="color: #0f172a;">{{ $qc->supplier?->supplier_nm ?: '-' }}</strong> &bull;
                        Surat Jalan: <strong style="color: #0f172a;">{{ $qc->surat_jalan_supplier ?: ($qc->nomor_do ?: '-') }}</strong> &bull;
                        Muatan SJ: <strong style="color: #059669;">{{ number_format((float)$qc->jumlah_surat_jalan, 0, ',', '.') }} kg</strong>
                    </div>
                </div>

                {{-- Metadata Audit Rapi & Ringkas --}}
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; font-size: 0.75rem; color: #475569;">
                    <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px;">
                        QC: <strong style="color: #0f172a;">{{ $qc->petugas_qc_nama ?: '-' }}</strong>
                    </div>
                    @if($qc->qc_supervisor_nama)
                        <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px;">
                            Spv: <strong style="color: #0f172a;">{{ $qc->qc_supervisor_nama }}</strong>
                        </div>
                    @endif
                    @if($qc->sopir_nama)
                        <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px;">
                            Sopir: <strong style="color: #0f172a;">{{ $qc->sopir_nama }}</strong>
                        </div>
                    @endif
                    <div style="background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px;">
                        🕒 {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : ($qc->created_at ? $qc->created_at->format('d/m/Y H:i') : '-') }} WIB
                    </div>
                </div>
            </div>

            @php
                $poObjDrawer = $qc->po;
                $poTotalPesanDrawer = $poObjDrawer ? (float) $poObjDrawer->details->sum('pesan_qty') : 0;
                $isOverPoDrawer = false;
                $selisihLebihDrawer = 0;
                $persenLebihDrawer = 0;
                if ($poObjDrawer && $poTotalPesanDrawer > 0) {
                    $muatanAcuanDrawer = max((float)$qc->jumlah_surat_jalan, (float)$totalGross);
                    if ($muatanAcuanDrawer > $poTotalPesanDrawer) {
                        $isOverPoDrawer = true;
                        $selisihLebihDrawer = $muatanAcuanDrawer - $poTotalPesanDrawer;
                        $persenLebihDrawer = round(($selisihLebihDrawer / $poTotalPesanDrawer) * 100, 1);
                    }
                }
            @endphp

            @if ($isOverPoDrawer)
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.65rem 1rem; margin: 0.85rem 1.25rem 0; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.15rem;">⚠️</span>
                        <div style="font-size: 0.775rem; color: #92400e;">
                            <strong>Kelebihan Muatan dari PO:</strong> Muatan fisik truk (<strong>{{ number_format($totalGross, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong>) melebihi PO #{{ $poObjDrawer->po_no }} ({{ number_format($poTotalPesanDrawer, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}). Selisih: <strong style="color: #b45309;">+{{ number_format($selisihLebihDrawer, 0, ',', '.') }} (+{{ $persenLebihDrawer }}%)</strong>.
                        </div>
                    </div>
                    <span style="font-size: 0.7rem; font-weight: 800; background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 5px; border: 1px solid #fcd34d;">
                        Status: Kuota Terpenuhi
                    </span>
                </div>
            @endif

            {{-- 2. GRID KOMPARASI PENGUJIAN --}}
            <div class="qc-stages-grid">
                {{-- KARTU TAHAP 1: PENGUJIAN 1 (KEDATANGAN & GERBANG TIMBANG) --}}
                <div class="qc-stage-card stage-uji1">
                    <div class="qc-stage-card-header">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span style="font-weight: 800; color: #0284c7;">
                                {{ $isSingkong ? '🚛 Pengujian 1 (Kedatangan)' : '📦 Pemeriksaan Kedatangan' }}
                            </span>
                            <span style="font-family: monospace; font-size: 0.75rem; background: #ffffff; padding: 1px 6px; border-radius: 4px; color: #475569; border: 1px solid #cbd5e1;">
                                #{{ $qc->qc_no }}
                            </span>
                        </div>
                        <div>
                            @if ($isSingkong)
                                @if ($p1Grade === 'B')
                                    <span style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Grade B
                                    </span>
                                @elseif ($p1Grade === 'REJECT')
                                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Afkir / Reject
                                    </span>
                                @else
                                    <span style="background: #f0fdf4; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Grade A
                                    </span>
                                @endif
                            @else
                                @if ($qc->status_qc === 'DITOLAK_TOTAL' || $p1Netto <= 0)
                                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Ditolak
                                    </span>
                                @elseif ($p1Reject > 0)
                                    <span style="background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Parsial
                                    </span>
                                @else
                                    <span style="background: #f0fdf4; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Lolos Mutu
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- 4 Stat Box Timbangan --}}
                    <div class="qc-stat-grid-4">
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Gross Timbang</div>
                            <div class="qc-stat-val">{{ number_format($p1Gross, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</div>
                        </div>
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">{{ $isBahanBaku ? 'Refraksi' : 'Kotoran' }}</div>
                            <div class="qc-stat-val" style="color: {{ $p1Refraksi > 0 ? '#d97706' : '#64748b' }};">
                                {{ $p1Refraksi > 0 ? '-'.number_format($p1Refraksi, 0, ',', '.').' kg ('.number_format($p1RefPersen, 1).'%)' : '0 kg (0%)' }}
                            </div>
                        </div>
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Reject / Afkir</div>
                            <div class="qc-stat-val" style="color: {{ $p1Reject > 0 ? '#dc2626' : '#64748b' }};">
                                {{ number_format($p1Reject, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}
                            </div>
                        </div>
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Netto Bersih</div>
                            <div class="qc-stat-val" style="color: #059669;">
                                {{ number_format($p1Netto, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}
                            </div>
                        </div>
                    </div>

                    {{-- Detail Parameter & Fisik & Uji Goreng Fryer --}}
                    <div style="padding: 0.85rem 1rem; font-size: 0.775rem; color: #334155; flex-grow: 1; display: flex; flex-direction: column; gap: 0.55rem;">
                        @if ($isSingkong)
                            {{-- Baris 1: Fisik & Diameter --}}
                            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <span style="color: #64748b;">Sampel:</span>
                                    <strong style="color: #0f172a;">{{ $qc->jumlah_sample_kg ? number_format((float)$qc->jumlah_sample_kg, 1).' kg' : '-' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Kadar Air:</span>
                                    <strong style="color: #0f172a;">{{ $p1FirstDtl?->kadar_air_persen !== null ? number_format((float)$p1FirstDtl->kadar_air_persen, 1).'%' : '-' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Diameter:</span>
                                    <strong style="color: #0f172a;">&lt;4cm: {{ (float)($p1FirstDtl?->diameter_kurang_4cm_persen ?? 0) }}% &bull; &ge;4cm: {{ (float)($p1FirstDtl?->diameter_lebih_4cm_persen ?? 0) }}%</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Kondisi:</span>
                                    <strong style="color: #0f172a;">
                                        @if($p1FirstDtl?->kondisi_segar) Segar @elseif($p1FirstDtl?->kondisi_layu) Layu @elseif($p1FirstDtl?->kondisi_busuk) Busuk @else Normal @endif
                                    </strong>
                                </div>
                            </div>

                            {{-- Baris 2: Hasil Lab Uji Goreng (Fryer Kedatangan) --}}
                            @php
                                $u1Rasa = $p1FirstDtl?->fryer_rasa;
                                $u1Tekstur = $p1FirstDtl?->fryer_tekstur;
                                $u1Minyak = $p1FirstDtl?->fryer_penampakan;
                                $u1Breakage = (float) ($p1FirstDtl?->defect_breakage_persen ?? 0);
                                $u1Gambos = (float) ($p1FirstDtl?->defect_gambos_persen ?? 0);
                            @endphp
                            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <span style="color: #64748b;">Rasa Goreng:</span>
                                    <strong style="color: {{ $u1Rasa === 'PAHIT' ? '#dc2626' : '#15803d' }};">
                                        {{ $u1Rasa === 'PAHIT' ? '✖ Pahit' : ($u1Rasa ? '✔ Gurih (Tidak Pahit)' : 'Normal') }}
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Tekstur:</span>
                                    <strong style="color: #0f172a;">{{ $u1Tekstur ?: 'Renyah' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Minyak:</span>
                                    <strong style="color: {{ $u1Minyak === 'OILSOAKED' ? '#dc2626' : '#0f172a' }};">
                                        {{ $u1Minyak === 'OILSOAKED' ? 'Oilsoaked' : 'Normal' }}
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Cacat (Defect):</span>
                                    <strong style="color: #0f172a;">
                                        @if ($u1Breakage > 0 || $u1Gambos > 0)
                                            @if ($u1Breakage > 0) Breakage {{ $u1Breakage }}% @endif
                                            @if ($u1Gambos > 0) {{ $u1Breakage > 0 ? '&bull;' : '' }} Gambos {{ $u1Gambos }}% @endif
                                        @else
                                            Normal (Bebas Cacat)
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        @else
                            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <span style="color: #64748b;">Komoditas:</span>
                                    <strong style="color: #0f172a;">{{ $barangNm }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Sertifikat Halal:</span>
                                    <strong style="color: {{ $qc->ada_sertifikat_halal_st ? '#15803d' : '#dc2626' }};">
                                        {{ $qc->ada_sertifikat_halal_st ? '✔ Ada & Berlaku' : '✖ Tidak Ada' }}
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Higienitas Armada:</span>
                                    <strong style="color: {{ $qc->bebas_cemaran_st ? '#15803d' : '#dc2626' }};">
                                        {{ $qc->bebas_cemaran_st ? '✔ Bebas Cemaran' : '⚠️ Tercemar' }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                        {{-- Catatan Pemeriksaan (Clean Solid Box) --}}
                        <div style="background: #f8fafc; border-radius: 6px; padding: 0.45rem 0.65rem; border: 1px solid #e2e8f0; font-size: 0.75rem; color: #475569;">
                            <strong style="color: #0f172a;">Catatan:</strong>
                            <span style="color: #334155;">{{ $p1FirstDtl?->catatan_dtl ?: ($qc->catatan_umum ?: 'Parameter mutu sesuai standar HACCP yang ditetapkan.') }}</span>
                        </div>
                    </div>

                    {{-- Action Footer Uji 1 --}}
                    <div style="padding: 0.55rem 0.95rem; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <div style="display: flex; gap: 0.4rem; align-items: center;">
                            <button type="button" class="btn btn-sm" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" style="font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>{{ $isSingkong ? 'Preview HACCP Uji 1' : 'Preview Dokumen HACCP' }}</span>
                            </button>
                            <button type="button" class="btn btn-sm" onclick="directPrintHaccp('{{ $qc->qc_id }}')" style="font-size: 0.75rem; font-weight: 600; background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak A4</span>
                            </button>
                        </div>
                        @if (!$isLocked && Auth::user()?->canEditQcWeb())
                            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'ref' => 'index']) }}" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-decoration: none;">
                                ✏ Edit Data
                            </a>
                        @endif
                    </div>
                </div>

                {{-- KARTU TAHAP 2: PENGUJIAN 2 (LANTAI PRODUKSI / SISA BAK) --}}
                <div class="qc-stage-card stage-uji2">
                    @if ($p2)
                        <div class="qc-stage-card-header">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <span style="font-weight: 800; color: #6366f1;">
                                    🍟 Pengujian 2 (Lantai Produksi)
                                </span>
                                <span style="font-family: monospace; font-size: 0.75rem; background: #ffffff; padding: 1px 6px; border-radius: 4px; color: #475569; border: 1px solid #cbd5e1;">
                                    #{{ $p2->qc_no }}
                                </span>
                            </div>
                            <div>
                                @if ($p2Grade === 'B')
                                    <span style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Grade B
                                    </span>
                                @elseif ($p2Grade === 'REJECT')
                                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Afkir / Reject
                                    </span>
                                @else
                                    <span style="background: #f0fdf4; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        Grade A
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- 4 Stat Box Timbangan --}}
                        <div class="qc-stat-grid-4">
                            <div class="qc-stat-cell">
                                <div class="qc-stat-label">Gross Timbang</div>
                                <div class="qc-stat-val">{{ number_format($p2Gross, 0, ',', '.') }} kg</div>
                            </div>
                            <div class="qc-stat-cell">
                                <div class="qc-stat-label">Refraksi</div>
                                <div class="qc-stat-val" style="color: {{ $p2Refraksi > 0 ? '#d97706' : '#64748b' }};">
                                    {{ $p2Refraksi > 0 ? '-'.number_format($p2Refraksi, 0, ',', '.').' kg ('.number_format($p2RefPersen, 1).'%)' : '0 kg (0%)' }}
                                </div>
                            </div>
                            <div class="qc-stat-cell">
                                <div class="qc-stat-label">Reject / Afkir</div>
                                <div class="qc-stat-val" style="color: {{ $p2Reject > 0 ? '#dc2626' : '#64748b' }};">
                                    {{ number_format($p2Reject, 0, ',', '.') }} kg
                                </div>
                            </div>
                            <div class="qc-stat-cell">
                                <div class="qc-stat-label">Netto Bersih</div>
                                <div class="qc-stat-val" style="color: #059669;">
                                    {{ number_format($p2Netto, 0, ',', '.') }} kg
                                </div>
                            </div>
                        </div>

                        {{-- Detail Parameter Fryer / Uji Goreng Lantai Produksi --}}
                        <div style="padding: 0.85rem 1rem; font-size: 0.775rem; color: #334155; flex-grow: 1; display: flex; flex-direction: column; gap: 0.55rem;">
                            @php
                                $u2Rasa = $p2FirstDtl?->fryer_rasa;
                                $u2Tekstur = $p2FirstDtl?->fryer_tekstur;
                                $u2Minyak = $p2FirstDtl?->fryer_penampakan;
                                $u2Breakage = (float) ($p2FirstDtl?->defect_breakage_persen ?? 0);
                                $u2Gambos = (float) ($p2FirstDtl?->defect_gambos_persen ?? 0);
                            @endphp
                            {{-- Baris 1: Status Lini & Sampel --}}
                            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <span style="color: #64748b;">Lini:</span>
                                    <strong style="color: #0f172a;">Wafel Produksi (Wajan Penggorengan)</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Sampel:</span>
                                    <strong style="color: #0f172a;">{{ $p2->jumlah_sample_kg ? number_format((float)$p2->jumlah_sample_kg, 1).' kg' : '-' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Jam Uji:</span>
                                    <strong style="color: #0f172a;">{{ $p2->tgl_periksa ? $p2->tgl_periksa->format('H:i') : '-' }} WIB</strong>
                                </div>
                            </div>

                            {{-- Baris 2: Hasil Uji Goreng Wajan Produksi --}}
                            <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                                <div>
                                    <span style="color: #64748b;">Rasa Goreng:</span>
                                    <strong style="color: {{ $u2Rasa === 'PAHIT' ? '#dc2626' : '#15803d' }};">
                                        {{ $u2Rasa === 'PAHIT' ? '✖ Pahit' : ($u2Rasa ? '✔ Gurih (Tidak Pahit)' : 'Normal') }}
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Tekstur:</span>
                                    <strong style="color: #0f172a;">{{ $u2Tekstur ?: 'Renyah' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Minyak:</span>
                                    <strong style="color: {{ $u2Minyak === 'OILSOAKED' ? '#dc2626' : '#0f172a' }};">
                                        {{ $u2Minyak === 'OILSOAKED' ? 'Oilsoaked' : 'Normal' }}
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Cacat (Defect):</span>
                                    <strong style="color: #0f172a;">
                                        @if ($u2Breakage > 0 || $u2Gambos > 0)
                                            @if ($u2Breakage > 0) Breakage {{ $u2Breakage }}% @endif
                                            @if ($u2Gambos > 0) {{ $u2Breakage > 0 ? '&bull;' : '' }} Gambos {{ $u2Gambos }}% @endif
                                        @else
                                            Normal (Bebas Cacat)
                                        @endif
                                    </strong>
                                </div>
                            </div>

                            {{-- Catatan Uji 2 --}}
                            <div style="background: #f8fafc; border-radius: 6px; padding: 0.45rem 0.65rem; border: 1px solid #e2e8f0; font-size: 0.75rem; color: #475569;">
                                <strong style="color: #0f172a;">Catatan:</strong>
                                <span style="color: #334155;">{{ $p2FirstDtl?->catatan_dtl ?: ($p2->catatan_umum ?: 'Sisa muatan bak sesuai standar mutu produksi.') }}</span>
                            </div>
                        </div>

                        {{-- Action Footer Uji 2 --}}
                        <div style="padding: 0.55rem 0.95rem; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <button type="button" class="btn btn-sm" onclick="openHaccpModal('{{ $p2->qc_id }}', '{{ $p2->qc_no }}')" style="font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Preview HACCP Uji 2</span>
                                </button>
                                <button type="button" class="btn btn-sm" onclick="directPrintHaccp('{{ $p2->qc_id }}')" style="font-size: 0.75rem; font-weight: 600; background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak A4</span>
                                </button>
                            </div>
                            @if (!$isLocked && Auth::user()?->canEditQcWeb())
                                <a href="{{ route('qc.inbound.edit', [$p2->qc_id, 'ref' => 'index']) }}" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-decoration: none;">
                                    ✏ Edit Data
                                </a>
                            @endif
                        </div>
                    @elseif ($isSingkong)
                        @if ($qc->status_qc === 'DITOLAK_TOTAL')
                            <div style="padding: 2.5rem 1.5rem; text-align: center; color: #dc2626; flex-grow: 1;">
                                <div style="font-size: 2rem; margin-bottom: 0.4rem;">🚫</div>
                                <div style="font-weight: 800; font-size: 0.875rem;">Truk Ditolak di Gerbang (Uji 1 Afkir)</div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    Pengujian 2 ditiadakan karena muatan tidak memenuhi ambang batas mutu pabrik.
                                </div>
                            </div>
                        @else
                            <div style="padding: 1.5rem 1.25rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: #ffffff;">
                                <div style="font-size: 1.4rem; margin-bottom: 0.35rem;">⏳</div>
                                <div style="font-weight: 800; font-size: 0.875rem; color: #0f172a;">
                                    Menunggu Pengujian 2 (Lantai Produksi)
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; max-width: 380px; margin-top: 0.25rem; line-height: 1.4;">
                                    Estimasi sisa muatan bak truk yang belum dibongkar: <strong style="color: #0f172a;">{{ number_format(max(0, (float)$qc->jumlah_surat_jalan - $p1Gross), 0, ',', '.') }} kg</strong>. Lakukan uji saat sisa muatan dibongkar ke lini wafel.
                                </div>

                                @if (Auth::user()?->canCreateQcMobile() || Auth::user()?->canCreateQc())
                                    <div style="margin-top: 0.85rem;">
                                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" 
                                           style="background: #0f172a; color: #ffffff; font-size: 0.775rem; font-weight: 700; padding: 6px 14px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                                            <span>🍟 Catat Pengujian 2 Sekarang &rarr;</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div style="padding: 2.5rem 1.5rem; text-align: center; color: #94a3b8; flex-grow: 1;">
                            <div style="font-size: 1.8rem; margin-bottom: 0.4rem;">📦</div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: #64748b;">Pengujian Tunggal</div>
                            <div style="font-size: 0.75rem; margin-top: 0.2rem;">
                                Komoditas ini tidak menggunakan mekanisme sampling 2 tahap.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. STATUS INTEGRASI GUDANG (GRN) & FOOTER AKSI CEPAT (CLEAN SLATE) --}}
            <div style="padding: 0.85rem 1.25rem; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    @if ($terimaObj)
                        @if ($qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0)
                            <div style="display: flex; align-items: center; gap: 0.4rem; background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; padding: 3px 8px; border-radius: 5px; font-size: 0.775rem; font-weight: 700;">
                                <span>⚠️ Diterima Parsial Gudang</span>
                                <span style="font-family: monospace;">(#{{ $terimaObj->terima_no }})</span>
                            </div>
                            <span style="font-size: 0.75rem; color: #475569;">
                                Masuk: <strong style="color: #059669;">{{ number_format($terimaObj->details->sum('qty_terima'), 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong> &bull; Ditolak: <strong style="color: #dc2626;">{{ number_format($totalReject, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong>
                            </span>
                        @else
                            <div style="display: flex; align-items: center; gap: 0.4rem; background: #f0fdf4; color: #15803d; border: 1px solid #86efac; padding: 3px 8px; border-radius: 5px; font-size: 0.775rem; font-weight: 700;">
                                <span>✅ Masuk Penuh ke Gudang</span>
                                <span style="font-family: monospace;">(#{{ $terimaObj->terima_no }})</span>
                            </div>
                            <span style="font-size: 0.75rem; color: #64748b;">
                                Diterima: <strong>{{ $terimaObj->tgl_terima ? $terimaObj->tgl_terima->format('d/m/Y H:i') : '-' }}</strong>
                            </span>
                        @endif
                    @elseif ($qc->status_qc === 'DITOLAK_TOTAL' && (!$p2 || $p2->status_qc === 'DITOLAK_TOTAL'))
                        <div style="display: flex; align-items: center; gap: 0.4rem; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 3px 8px; border-radius: 5px; font-size: 0.775rem; font-weight: 700;">
                            <span>🚫 Truk Ditolak QC</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #dc2626;">
                            Muatan tidak ditarik ke stok pabrik.
                        </span>
                    @elseif ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL')
                        <div style="display: flex; align-items: center; gap: 0.4rem; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 3px 8px; border-radius: 5px; font-size: 0.775rem; font-weight: 700;">
                            <span>⏳ Bongkar Setengah Bak Selesai</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            GRN akan menggabungkan Netto Uji 1 ({{ number_format($p1Netto, 0, ',', '.') }} kg) dan Uji 2 setelah kedua tahap selesai.
                        </span>
                    @elseif ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG'))
                        <div style="display: flex; align-items: center; gap: 0.4rem; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 3px 8px; border-radius: 5px; font-size: 0.775rem; font-weight: 700;">
                            <span>📦 Siap Ditarik ke GRN</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #0f172a;">
                            Total Netto Bersih Lolos: <strong style="color: #059669;">{{ number_format($totalNetto, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong>
                        </span>
                    @endif
                </div>

                {{-- Quick Buttons Right --}}
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    @if ($terimaObj)
                        <a href="{{ route('gudang.terima.show', $terimaObj->terima_id) }}" class="btn btn-sm btn-primary" style="font-size: 0.78rem; font-weight: 700; padding: 5px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                            <span>Buka Dokumen GRN &rarr;</span>
                        </a>
                    @elseif (!$terimaObj && ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG')) && Auth::user()?->canAccessTerima())
                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn btn-sm" style="font-size: 0.78rem; font-weight: 700; background: #059669; color: #ffffff; padding: 5px 14px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(5,150,105,0.25);">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Tarik ke GRN Gudang</span>
                        </a>
                    @endif

                    @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->details->sum('qty_reject') > 0 || ($p2 && ($p2->status_qc === 'DITOLAK_TOTAL' || $p2->details->sum('qty_reject') > 0)))
                        <a href="{{ route('qc.inbound.berita_acara', ($p2 && $p2->details->sum('qty_reject') > 0 && $qc->details->sum('qty_reject') == 0) ? $p2->qc_id : $qc->qc_id) }}" class="btn btn-sm" style="font-size: 0.78rem; font-weight: 700; background: #dc2626; color: #ffffff; padding: 5px 12px; border-radius: 6px; text-decoration: none;">
                            <span>Cetak Berita Acara</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </td>
</tr>
