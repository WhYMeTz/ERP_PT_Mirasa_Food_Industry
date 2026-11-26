{{-- 
    Partial: Expandable Sub-Row Drawer Rincian QC & Audit Trail 
    File: resources/views/gudang/qc/partials/common/drawer-detail.blade.php
--}}
<tr id="drawer-{{ $qc->qc_id }}" class="qc-drawer-row" style="display: none;">
    <td colspan="10" class="qc-drawer-cell">
        <div class="qc-drawer-box">
            {{-- 1. HEADER DOSSIER & METADATA AUDIT (CLEAN & ELEGANT STANDAR PO) --}}
            <div class="qc-drawer-header">
                <div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span>Dossier QC: <strong>#{{ $qc->qc_no }}</strong></span>
                        <span style="font-size: 0.75rem; background: #0f172a; color: #ffffff; padding: 2px 8px; border-radius: 9999px; font-weight: 700; letter-spacing: 0.02em;">
                            {{ $qc->plat_nomor_truk ?: 'Tanpa Plat' }}
                        </span>
                        @if ($qc->po)
                            <a href="{{ route('gudang.po.show', $qc->po->po_id) }}" style="font-size: 0.75rem; background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 9999px; font-weight: 700; text-decoration: none;">
                                PO: #{{ $qc->po->po_no }}
                            </a>
                        @endif
                    </div>
                    <div style="font-size: 0.775rem; color: #64748b; margin-top: 0.35rem;">
                        Supplier: <strong style="color: #0f172a;">{{ $qc->supplier?->supplier_nm ?: '-' }}</strong> &bull;
                        Surat Jalan: <strong style="color: #0f172a;">{{ $qc->surat_jalan_supplier ?: ($qc->nomor_do ?: '-') }}</strong> &bull;
                        Muatan SJ: <strong style="color: #059669;">{{ number_format((float)$qc->jumlah_surat_jalan, 0, ',', '.') }} kg</strong>
                    </div>
                </div>

                {{-- Metadata Audit Rapi & Ringkas (Micro-Chips Putih Border Halus) --}}
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; font-size: 0.75rem; color: #475569;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 6px;">
                        QC: <strong style="color: #0f172a;">{{ $qc->petugas_qc_nama ?: '-' }}</strong>
                    </div>
                    @if($qc->qc_supervisor_nama)
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 6px;">
                            Spv: <strong style="color: #0f172a;">{{ $qc->qc_supervisor_nama }}</strong>
                        </div>
                    @endif
                    @if($qc->sopir_nama)
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 6px;">
                            Sopir: <strong style="color: #0f172a;">{{ $qc->sopir_nama }}</strong>
                        </div>
                    @endif
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 6px;">
                        {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : ($qc->created_at ? $qc->created_at->format('d/m/Y H:i') : '-') }} WIB
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
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #b45309;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div style="font-size: 0.775rem; color: #92400e;">
                            <strong>Kelebihan Muatan dari PO:</strong> Muatan fisik truk (<strong>{{ number_format($totalGross, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong>) melebihi PO #{{ $poObjDrawer->po_no }} ({{ number_format($poTotalPesanDrawer, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}). Selisih: <strong style="color: #b45309;">+{{ number_format($selisihLebihDrawer, 0, ',', '.') }} (+{{ $persenLebihDrawer }}%)</strong>.
                        </div>
                    </div>
                    <span style="font-size: 0.7rem; font-weight: 800; background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 5px; border: 1px solid #fcd34d;">
                        Status: Kuota Terpenuhi
                    </span>
                </div>
            @endif

            {{-- 2. GRID KOMPARASI PENGUJIAN (KARTU NETRAL SERAGAM SESUAI PO_DESIGN_SYSTEM) --}}
            <div class="qc-stages-grid">
                {{-- KARTU TAHAP 1: PENGUJIAN 1 (KEDATANGAN & GERBANG TIMBANG) --}}
                <div class="qc-stage-card stage-uji1">
                    <div class="qc-stage-card-header">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span style="font-weight: 800; color: #0f172a;">
                                {{ $isSingkong ? 'Pengujian 1 (Kedatangan)' : 'Pemeriksaan Kedatangan' }}
                            </span>
                            <span style="font-family: monospace; font-size: 0.75rem; background: #f8fafc; padding: 1px 6px; border-radius: 4px; color: #475569; border: 1px solid #e2e8f0;">
                                #{{ $qc->qc_no }}
                            </span>
                        </div>
                        <div>
                            @if ($isSingkong)
                                @if ($p1Grade === 'B')
                                    <span class="qc-badge-clean grade-b">Grade B</span>
                                @elseif ($p1Grade === 'REJECT')
                                    <span class="qc-badge-clean grade-reject">Afkir / Reject</span>
                                @else
                                    <span class="qc-badge-clean grade-a">Grade A</span>
                                @endif
                            @elseif ($kat === 'KARTON')
                                @php
                                    $gADtl = $qc->details->firstWhere('grade_cd', 'A');
                                    $gBDtl = $qc->details->firstWhere('grade_cd', 'B');
                                    $hasA = $gADtl && (float)$gADtl->qty_netto_lolos > 0;
                                    $hasB = $gBDtl && (float)$gBDtl->qty_netto_lolos > 0;
                                @endphp
                                @if ($hasA && $hasB)
                                    <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                        <span class="qc-badge-clean grade-a">
                                            Gr. A: {{ number_format((float)$gADtl->qty_netto_lolos, 0, ',', '.') }}
                                        </span>
                                        <span class="qc-badge-clean grade-b">
                                            Gr. B: {{ number_format((float)$gBDtl->qty_netto_lolos, 0, ',', '.') }}
                                        </span>
                                        @if ($p1Reject > 0)
                                            <span class="qc-badge-clean grade-reject">
                                                Parsial (-{{ number_format($p1Reject, 0, ',', '.') }})
                                            </span>
                                        @endif
                                    </div>
                                @elseif ($hasB || $p1Grade === 'B')
                                    <span class="qc-badge-clean grade-b">
                                        Grade B: {{ number_format((float)($gBDtl?->qty_netto_lolos ?? $p1Netto), 0, ',', '.') }}
                                    </span>
                                @elseif ($qc->status_qc === 'DITOLAK_TOTAL' || $p1Netto <= 0)
                                    <span class="qc-badge-clean grade-reject">Ditolak</span>
                                @elseif ($p1Reject > 0)
                                    <span class="qc-badge-clean warning">Parsial</span>
                                @else
                                    <span class="qc-badge-clean grade-a">
                                        Grade A: {{ number_format((float)($gADtl?->qty_netto_lolos ?? $p1Netto), 0, ',', '.') }}
                                    </span>
                                @endif
                            @else
                                @if ($qc->status_qc === 'DITOLAK_TOTAL' || $p1Netto <= 0)
                                    <span class="qc-badge-clean grade-reject">Ditolak</span>
                                @elseif ($p1Reject > 0)
                                    <span class="qc-badge-clean warning">Parsial</span>
                                @else
                                    <span class="qc-badge-clean grade-a">Lolos Mutu</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- 4 Stat Box Timbangan (Monospace & Tabular Nums) --}}
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
                            @if ($kat === 'KARTON')
                                @php
                                    $gADtl = $gADtl ?? $qc->details->firstWhere('grade_cd', 'A');
                                    $gBDtl = $gBDtl ?? $qc->details->firstWhere('grade_cd', 'B');
                                    $hasA = $gADtl && (float)$gADtl->qty_netto_lolos > 0;
                                    $hasB = $gBDtl && (float)$gBDtl->qty_netto_lolos > 0;
                                @endphp
                                @if ($hasA && $hasB)
                                    <div style="display: flex; gap: 4px; justify-content: center; margin-top: 0.35rem; flex-wrap: wrap;">
                                        <span class="qc-badge-clean grade-a" title="Grade A (Dus Baru)">
                                            A: {{ number_format((float)$gADtl->qty_netto_lolos, 0, ',', '.') }}
                                        </span>
                                        <span class="qc-badge-clean grade-b" title="Grade B (Dus Puteran)">
                                            B: {{ number_format((float)$gBDtl->qty_netto_lolos, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @elseif ($hasB || $p1Grade === 'B')
                                    <div style="margin-top: 0.25rem;">
                                        <span class="qc-badge-clean grade-b">
                                            Gr. B: {{ number_format((float)($gBDtl?->qty_netto_lolos ?? $p1Netto), 0, ',', '.') }}
                                        </span>
                                    </div>
                                @elseif ($hasA || $p1Grade === 'A')
                                    <div style="margin-top: 0.25rem;">
                                        <span class="qc-badge-clean grade-a">
                                            Gr. A: {{ number_format((float)($gADtl?->qty_netto_lolos ?? $p1Netto), 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                            @endif
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
                                        {{ $u1Rasa === 'PAHIT' ? 'Pahit (Defect)' : ($u1Rasa ? 'Gurih (Normal)' : 'Normal') }}
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
                                @if ($kat === 'KARTON')
                                    @php
                                        $pGA = $qc->details->firstWhere('grade_cd', 'A');
                                        $pGB = $qc->details->firstWhere('grade_cd', 'B');
                                        $hasA = $pGA && (float)$pGA->qty_netto_lolos > 0;
                                        $hasB = $pGB && (float)$pGB->qty_netto_lolos > 0;
                                    @endphp
                                    @if ($hasA || $hasB)
                                        <div>
                                            <span style="color: #64748b;">Alokasi:</span>
                                            @if ($hasA)
                                                <strong style="color: #15803d;">A: {{ number_format((float)$pGA->qty_netto_lolos, 0, ',', '.') }}</strong>
                                            @endif
                                            @if ($hasA && $hasB) &bull; @endif
                                            @if ($hasB)
                                                <strong style="color: #b45309;">B: {{ number_format((float)$pGB->qty_netto_lolos, 0, ',', '.') }}</strong>
                                            @endif
                                            <span style="color: #64748b; font-size: 0.72rem;">{{ $satuanCd }}</span>
                                        </div>
                                    @endif
                                @endif
                                <div>
                                    <span style="color: #64748b;">Sertifikat Halal:</span>
                                    <strong style="color: {{ $qc->ada_sertifikat_halal_st ? '#15803d' : '#dc2626' }};">
                                        {{ $qc->ada_sertifikat_halal_st ? 'Ada & Berlaku' : 'Tidak Ada' }}
                                    </strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Higienitas Armada:</span>
                                    <strong style="color: {{ $qc->bebas_cemaran_st ? '#15803d' : '#dc2626' }};">
                                        {{ $qc->bebas_cemaran_st ? 'Bebas Cemaran' : 'Tercemar' }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                        @if ($kat === 'KARTON' && $qc->details->count() > 1)
                            {{-- KARTON MULTI-GRADE BREAKDOWN CARD --}}
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.55rem 0.85rem; font-size: 0.775rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap; gap: 0.35rem;">
                                    <span style="font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>Rincian Hasil Uji Per Grade:</span>
                                    </span>
                                    <span style="font-size: 0.7rem; color: #64748b;">
                                        Total Bersih: <strong style="color: #059669;">{{ number_format($p1Netto, 0, ',', '.') }} {{ $satuanCd }}</strong>
                                    </span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.5rem;">
                                    @foreach ($qc->details as $dtl)
                                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.45rem 0.65rem; display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                @if ($dtl->grade_cd === 'A')
                                                    <div style="display: flex; align-items: center; gap: 4px;">
                                                        <span class="qc-badge-clean grade-a">Grade A</span>
                                                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;">Dus Baru</span>
                                                    </div>
                                                @elseif ($dtl->grade_cd === 'B')
                                                    <div style="display: flex; align-items: center; gap: 4px;">
                                                        <span class="qc-badge-clean grade-b">Grade B</span>
                                                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;">Dus Puteran</span>
                                                    </div>
                                                @else
                                                    <div style="display: flex; align-items: center; gap: 4px;">
                                                        <span class="qc-badge-clean grade-reject">Afkir</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div style="text-align: right;">
                                                <strong style="color: #0f172a; font-size: 0.875rem;">{{ number_format((float)$dtl->qty_netto_lolos, 0, ',', '.') }}</strong>
                                                <span style="font-size: 0.72rem; color: #64748b;">{{ $satuanCd }}</span>
                                                @if ((float)$dtl->qty_reject > 0)
                                                    <div style="font-size: 0.68rem; color: #dc2626; font-weight: 700;">Reject: -{{ number_format((float)$dtl->qty_reject, 0, ',', '.') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($qc->catatan_umum)
                                    <div style="font-size: 0.72rem; color: #475569; margin-top: 0.35rem; padding-top: 0.3rem; border-top: 1px dashed #cbd5e1;">
                                        <strong>Catatan:</strong> {{ $qc->catatan_umum }}
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Catatan Pemeriksaan (Clean Solid Box) --}}
                            <div style="background: #f8fafc; border-radius: 6px; padding: 0.45rem 0.65rem; border: 1px solid #e2e8f0; font-size: 0.75rem; color: #475569;">
                                <strong style="color: #0f172a;">Catatan:</strong>
                                <span style="color: #334155;">{{ $p1FirstDtl?->catatan_dtl ?: ($qc->catatan_umum ?: 'Parameter mutu sesuai standar HACCP yang ditetapkan.') }}</span>
                            </div>
                        @endif
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
                            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'ref' => 'index']) }}" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit Data</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- KARTU TAHAP 2: PENGUJIAN 2 (LANTAI PRODUKSI / SISA BAK) --}}
                <div class="qc-stage-card stage-uji2">
                    @if ($p2)
                        <div class="qc-stage-card-header">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <span style="font-weight: 800; color: #0f172a;">
                                    Pengujian 2 (Lantai Produksi)
                                </span>
                                <span style="font-family: monospace; font-size: 0.75rem; background: #f8fafc; padding: 1px 6px; border-radius: 4px; color: #475569; border: 1px solid #e2e8f0;">
                                    #{{ $p2->qc_no }}
                                </span>
                            </div>
                            <div>
                                @if ($p2Grade === 'B')
                                    <span class="qc-badge-clean grade-b">Grade B</span>
                                @elseif ($p2Grade === 'REJECT')
                                    <span class="qc-badge-clean grade-reject">Afkir / Reject</span>
                                @else
                                    <span class="qc-badge-clean grade-a">Grade A</span>
                                @endif
                            </div>
                        </div>

                        {{-- 4 Stat Box Timbangan (Monospace & Tabular Nums) --}}
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
                                        {{ $u2Rasa === 'PAHIT' ? 'Pahit (Defect)' : ($u2Rasa ? 'Gurih (Normal)' : 'Normal') }}
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
                                <a href="{{ route('qc.inbound.edit', [$p2->qc_id, 'ref' => 'index']) }}" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit Data</span>
                                </a>
                            @endif
                        </div>
                    @elseif ($isSingkong)
                        @if ($qc->status_qc === 'DITOLAK_TOTAL')
                            <div style="padding: 2.5rem 1.5rem; text-align: center; color: #dc2626; flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <div style="color: #dc2626; margin-bottom: 0.5rem;">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div style="font-weight: 800; font-size: 0.875rem; color: #991b1b;">Truk Ditolak di Gerbang (Uji 1 Afkir)</div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    Pengujian 2 ditiadakan karena muatan tidak memenuhi ambang batas mutu pabrik.
                                </div>
                            </div>
                        @else
                            <div style="padding: 1.75rem 1.25rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: #ffffff;">
                                <div style="color: #a16207; margin-bottom: 0.5rem;">
                                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div style="font-weight: 800; font-size: 0.875rem; color: #0f172a;">
                                    Menunggu Pengujian 2 (Lantai Produksi)
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; max-width: 380px; margin-top: 0.25rem; line-height: 1.45;">
                                    Estimasi sisa muatan bak truk yang belum dibongkar: <strong style="color: #0f172a;">{{ number_format(max(0, (float)$qc->jumlah_surat_jalan - $p1Gross), 0, ',', '.') }} kg</strong>. Lakukan uji saat sisa muatan dibongkar ke lini wafel.
                                </div>

                                @if (Auth::user()?->canCreateQcMobile() || Auth::user()?->canCreateQc())
                                    <div style="margin-top: 0.95rem;">
                                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" 
                                           class="btn-pill-dark"
                                           style="font-size: 0.78rem;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            <span>Catat Pengujian 2 Sekarang</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div style="padding: 2.5rem 1.5rem; text-align: center; color: #64748b; flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div style="color: #94a3b8; margin-bottom: 0.5rem;">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a;">Pengujian Tunggal</div>
                            <div style="font-size: 0.75rem; margin-top: 0.25rem; color: #64748b;">
                                Komoditas ini tidak menggunakan mekanisme sampling 2 tahap.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. STATUS INTEGRASI GUDANG (GRN) & FOOTER AKSI CEPAT (CLEAN SLATE STANDAR PO) --}}
            <div style="padding: 0.85rem 1.25rem; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    @if ($terimaObj)
                        @if ($qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0)
                            <div class="qc-badge-clean warning" style="padding: 3px 10px; font-size: 0.75rem; font-weight: 700;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Diterima Parsial (#{{ $terimaObj->terima_no }})</span>
                            </div>
                            <span style="font-size: 0.75rem; color: #475569;">
                                Masuk: <strong style="color: #059669;">{{ number_format($terimaObj->details->sum('qty_terima'), 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong> &bull; Ditolak: <strong style="color: #dc2626;">{{ number_format($totalReject, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong>
                            </span>
                        @else
                            <div class="qc-badge-clean grade-a" style="padding: 3px 10px; font-size: 0.75rem; font-weight: 700;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Masuk Penuh ke Gudang (#{{ $terimaObj->terima_no }})</span>
                            </div>
                            <span style="font-size: 0.75rem; color: #64748b;">
                                Diterima: <strong>{{ $terimaObj->tgl_terima ? $terimaObj->tgl_terima->format('d/m/Y H:i') : '-' }}</strong>
                            </span>
                        @endif
                    @elseif ($qc->status_qc === 'DITOLAK_TOTAL' && (!$p2 || $p2->status_qc === 'DITOLAK_TOTAL'))
                        <div class="qc-badge-clean grade-reject" style="padding: 3px 10px; font-size: 0.75rem; font-weight: 700;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Truk Ditolak QC</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #dc2626;">
                            Muatan tidak ditarik ke stok pabrik.
                        </span>
                    @elseif ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL')
                        <div class="qc-badge-clean warning" style="padding: 3px 10px; font-size: 0.75rem; font-weight: 700;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Bongkar 1/2 Bak Selesai</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            GRN akan menggabungkan Netto Uji 1 ({{ number_format($p1Netto, 0, ',', '.') }} kg) dan Uji 2 setelah kedua tahap selesai.
                        </span>
                    @elseif ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG'))
                        <div class="qc-badge-clean grade-a" style="padding: 3px 10px; font-size: 0.75rem; font-weight: 700;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Siap Ditarik ke GRN</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #0f172a;">
                            Total Netto Bersih Lolos: <strong style="color: #059669;">{{ number_format($totalNetto, 0, ',', '.') }} {{ $isBahanBaku ? 'kg' : $satuanCd }}</strong>
                            @if ($kat === 'KARTON')
                                @php
                                    $ftGA = $qc->details->firstWhere('grade_cd', 'A');
                                    $ftGB = $qc->details->firstWhere('grade_cd', 'B');
                                @endphp
                                @if ($ftGA && $ftGB && (float)$ftGA->qty_netto_lolos > 0 && (float)$ftGB->qty_netto_lolos > 0)
                                    <span style="color: #475569; font-size: 0.72rem; margin-left: 0.35rem;">
                                        (Gr. A: <strong style="color: #15803d;">{{ number_format((float)$ftGA->qty_netto_lolos, 0, ',', '.') }}</strong> &bull; Gr. B: <strong style="color: #b45309;">{{ number_format((float)$ftGB->qty_netto_lolos, 0, ',', '.') }}</strong> {{ $satuanCd }})
                                    </span>
                                @endif
                            @endif
                        </span>
                    @endif
                </div>

                {{-- Quick Buttons Right (Capsule Style WaveDash / PO) --}}
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    @if ($terimaObj)
                        <a href="{{ route('gudang.terima.show', $terimaObj->terima_id) }}" class="btn-pill-dark" style="font-size: 0.78rem;">
                            <span>Buka Dokumen GRN</span>
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @elseif (!$terimaObj && ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG')) && Auth::user()?->canAccessTerima())
                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn-pill-grn-success" style="font-size: 0.78rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Tarik ke GRN Gudang</span>
                        </a>
                    @endif

                    @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->details->sum('qty_reject') > 0 || ($p2 && ($p2->status_qc === 'DITOLAK_TOTAL' || $p2->details->sum('qty_reject') > 0)))
                        <a href="{{ route('qc.inbound.berita_acara', ($p2 && $p2->details->sum('qty_reject') > 0 && $qc->details->sum('qty_reject') == 0) ? $p2->qc_id : $qc->qc_id) }}" class="btn-pill-danger-outline" style="font-size: 0.78rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Cetak Berita Acara</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </td>
</tr>
