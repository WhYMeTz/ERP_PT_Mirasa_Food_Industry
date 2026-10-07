{{-- 
    Partial: Expandable Sub-Row Drawer Rincian QC & Audit Trail 
    File: resources/views/gudang/qc/partials/drawer-detail.blade.php
--}}
<tr id="drawer-{{ $qc->qc_id }}" class="qc-drawer-row" style="display: none;">
    <td colspan="10" class="qc-drawer-cell">
        <div class="qc-drawer-box">
            {{-- 1. HEADER DOSSIER & METADATA AUDIT --}}
            <div class="qc-drawer-header">
                <div>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <span>📋 Dossier Teknis &amp; Audit QC Kedatangan</span>
                        <span style="font-size: 0.8rem; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px; font-family: monospace;">
                            🚛 {{ $qc->plat_nomor_truk ?: 'Tanpa Plat' }}
                        </span>
                        @if ($qc->po)
                            <a href="{{ route('gudang.po.show', $qc->po->po_id) }}" style="font-size: 0.75rem; background: #f1f5f9; color: #0284c7; padding: 2px 8px; border-radius: 6px; font-weight: 700; text-decoration: none;">
                                PO: #{{ $qc->po->po_no }}
                            </a>
                        @endif
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                        Supplier: <strong style="color: #1e293b;">{{ $qc->supplier?->supplier_nm ?: '-' }}</strong> &bull;
                        Surat Jalan: <strong style="color: #1e293b;">{{ $qc->surat_jalan_supplier ?: '-' }}</strong> &bull;
                        Muatan SJ: <strong style="color: #047857;">{{ number_format((float)$qc->jumlah_surat_jalan, 0, ',', '.') }} kg</strong>
                    </div>
                </div>

                {{-- Audit Chips --}}
                <div class="qc-drawer-chips">
                    <span class="qc-chip" title="Petugas yang melakukan sampling">
                        👤 QC: <strong>{{ $qc->petugas_qc_nama ?: '-' }}</strong>
                    </span>
                    @if($qc->qc_supervisor_nama)
                        <span class="qc-chip" title="Supervisor yang memverifikasi">
                            👔 Spv: <strong>{{ $qc->qc_supervisor_nama }}</strong>
                        </span>
                    @endif
                    @if($qc->sopir_nama)
                        <span class="qc-chip" title="Nama Pengemudi Armada">
                            🚚 Sopir: <strong>{{ $qc->sopir_nama }}</strong>
                        </span>
                    @endif
                    @if($qc->nomor_do)
                        <span class="qc-chip" title="Nomor Delivery Order">
                            📦 DO: <strong>{{ $qc->nomor_do }}</strong>
                        </span>
                    @endif
                    @if($qc->batch_no)
                        <span class="qc-chip" title="Nomor Batch / Lot">
                            🏷 Batch: <strong>{{ $qc->batch_no }}</strong>
                        </span>
                    @endif
                    <span class="qc-chip" title="Waktu dibuat di sistem">
                        🕒 {{ $qc->created_at ? $qc->created_at->format('d/m/Y H:i') : '-' }}
                    </span>
                </div>
            </div>

            {{-- 2. GRID KOMPARASI 2 TAHAP PENGUJIAN --}}
            <div class="qc-stages-grid">
                {{-- KARTU TAHAP 1: PENGUJIAN 1 (SETENGAH BAK PERTAMA) --}}
                <div class="qc-stage-card stage-uji1">
                    <div class="qc-stage-card-header">
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span>🚛 Pengujian 1 (Setengah Bak)</span>
                            <span style="font-family: monospace; font-size: 0.75rem; background: #ffffff; padding: 1px 6px; border-radius: 4px; color: #0284c7; border: 1px solid #bae6fd;">
                                #{{ $qc->qc_no }}
                            </span>
                        </div>
                        <div>
                            @if ($p1Grade === 'B')
                                <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                    🟡 Grade B
                                </span>
                            @elseif ($p1Grade === 'REJECT')
                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                    ❌ Afkir
                                </span>
                            @else
                                <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                    🟢 Grade A
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- 4 Stat Box Mini --}}
                    <div class="qc-stat-grid-4">
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Gross Timbang</div>
                            <div class="qc-stat-val">{{ number_format($p1Gross, 0, ',', '.') }} kg</div>
                        </div>
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Refraksi</div>
                            <div class="qc-stat-val" style="color: {{ $p1Refraksi > 0 ? '#d97706' : '#64748b' }};">
                                -{{ number_format($p1Refraksi, 0, ',', '.') }} kg
                                <span style="font-size: 0.68rem; font-weight: 600;">({{ number_format($p1RefPersen, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Reject / Afkir</div>
                            <div class="qc-stat-val" style="color: {{ $p1Reject > 0 ? '#dc2626' : '#64748b' }};">
                                {{ number_format($p1Reject, 0, ',', '.') }} kg
                            </div>
                        </div>
                        <div class="qc-stat-cell">
                            <div class="qc-stat-label">Netto Bersih</div>
                            <div class="qc-stat-val" style="color: #047857; font-size: 1rem;">
                                {{ number_format($p1Netto, 0, ',', '.') }} kg
                            </div>
                        </div>
                    </div>

                    {{-- Detail Parameter & Fisik --}}
                    <div style="padding: 0.85rem 1rem; font-size: 0.78rem; color: #334155; flex-grow: 1;">
                        @if ($isSingkong)
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.5rem; margin-bottom: 0.6rem;">
                                <div>
                                    <span style="color: #64748b;">Sample Diuji:</span>
                                    <strong style="color: #0f172a;">{{ $qc->jumlah_sample_kg ? number_format((float)$qc->jumlah_sample_kg, 1).' kg' : '-' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Diameter &lt;4cm:</span>
                                    <strong style="color: #0f172a;">{{ (float)($p1FirstDtl?->diameter_kurang_4cm_persen ?? 0) }}%</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Diameter &gt;4cm:</span>
                                    <strong style="color: #0f172a;">{{ (float)($p1FirstDtl?->diameter_lebih_4cm_persen ?? 0) }}%</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Kondisi:</span>
                                    <strong style="color: #0f172a;">
                                        @if($p1FirstDtl?->kondisi_segar) Segar @elseif($p1FirstDtl?->kondisi_layu) Layu @elseif($p1FirstDtl?->kondisi_busuk) Busuk @else Normal @endif
                                    </strong>
                                </div>
                            </div>
                        @endif

                        <div style="background: #f8fafc; border-radius: 6px; padding: 0.5rem 0.75rem; border: 1px dashed #cbd5e1; font-size: 0.75rem;">
                            <span style="font-weight: 700; color: #475569;">Catatan Uji 1:</span>
                            <span style="color: #1e293b;">
                                {{ $p1FirstDtl?->catatan_dtl ?: ($qc->catatan_umum ?: 'Parameter mutu sesuai SOP kedatangan.') }}
                            </span>
                        </div>
                    </div>

                    {{-- Action Footer Uji 1 --}}
                    <div style="padding: 0.6rem 1rem; background: #fafcff; border-top: 1px solid #e0f2fe; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <div style="display: flex; gap: 0.4rem; align-items: center;">
                            <button type="button" class="btn btn-sm" onclick="openHaccpModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" style="font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1px solid #cbd5e1; color: #0284c7; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Preview HACCP Uji 1</span>
                            </button>
                            <button type="button" class="btn btn-sm" onclick="directPrintHaccp('{{ $qc->qc_id }}')" style="font-size: 0.75rem; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak A4</span>
                            </button>
                        </div>
                        @if (!$isLocked && Auth::user()?->canEditQc())
                            <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-decoration: none;">
                                ✏ Edit Data
                            </a>
                        @endif
                    </div>
                </div>

                {{-- KARTU TAHAP 2: PENGUJIAN 2 (SISA BAK LANTAI PRODUKSI) --}}
                <div class="qc-stage-card stage-uji2">
                    @if ($p2)
                        <div class="qc-stage-card-header">
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span>🍟 Pengujian 2 (Sisa Bak)</span>
                                <span style="font-family: monospace; font-size: 0.75rem; background: #ffffff; padding: 1px 6px; border-radius: 4px; color: #7e22ce; border: 1px solid #e9d5ff;">
                                    #{{ $p2->qc_no }}
                                </span>
                            </div>
                            <div>
                                @if ($p2Grade === 'B')
                                    <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        🟡 Grade B
                                    </span>
                                @elseif ($p2Grade === 'REJECT')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        ❌ Afkir
                                    </span>
                                @else
                                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;">
                                        🟢 Grade A
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- 4 Stat Box Mini --}}
                        <div class="qc-stat-grid-4">
                            <div class="qc-stat-cell">
                                <div class="qc-stat-label">Gross Timbang</div>
                                <div class="qc-stat-val">{{ number_format($p2Gross, 0, ',', '.') }} kg</div>
                            </div>
                            <div class="qc-stat-cell">
                                <div class="qc-stat-label">Refraksi</div>
                                <div class="qc-stat-val" style="color: {{ $p2Refraksi > 0 ? '#d97706' : '#64748b' }};">
                                    -{{ number_format($p2Refraksi, 0, ',', '.') }} kg
                                    <span style="font-size: 0.68rem; font-weight: 600;">({{ number_format($p2RefPersen, 1) }}%)</span>
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
                                <div class="qc-stat-val" style="color: #7e22ce; font-size: 1rem;">
                                    {{ number_format($p2Netto, 0, ',', '.') }} kg
                                </div>
                            </div>
                        </div>

                        {{-- Detail Parameter Fryer / Uji Goreng --}}
                        <div style="padding: 0.85rem 1rem; font-size: 0.78rem; color: #334155; flex-grow: 1;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.5rem; margin-bottom: 0.6rem;">
                                <div>
                                    <span style="color: #64748b;">Rasa Goreng:</span>
                                    <strong style="color: #0f172a;">{{ $p2FirstDtl?->fryer_rasa ?: 'Normal' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Tekstur:</span>
                                    <strong style="color: #0f172a;">{{ $p2FirstDtl?->fryer_tekstur ?: 'Renyah' }}</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Breakage:</span>
                                    <strong style="color: #0f172a;">{{ (float)($p2FirstDtl?->defect_breakage_persen ?? 0) }}%</strong>
                                </div>
                                <div>
                                    <span style="color: #64748b;">Gambos:</span>
                                    <strong style="color: #0f172a;">{{ (float)($p2FirstDtl?->defect_gambos_persen ?? 0) }}%</strong>
                                </div>
                            </div>

                            <div style="background: #faf5ff; border-radius: 6px; padding: 0.5rem 0.75rem; border: 1px dashed #d8b4fe; font-size: 0.75rem;">
                                <span style="font-weight: 700; color: #7e22ce;">Catatan Uji 2:</span>
                                <span style="color: #1e293b;">
                                    {{ $p2FirstDtl?->catatan_dtl ?: ($p2->catatan_umum ?: 'Sisa muatan bak sesuai standar produksi.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Action Footer Uji 2 --}}
                        <div style="padding: 0.6rem 1rem; background: #fdfbff; border-top: 1px solid #f3e8ff; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <button type="button" class="btn btn-sm" onclick="openHaccpModal('{{ $p2->qc_id }}', '{{ $p2->qc_no }}')" style="font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1px solid #cbd5e1; color: #7e22ce; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Preview HACCP Uji 2</span>
                                </button>
                                <button type="button" class="btn btn-sm" onclick="directPrintHaccp('{{ $p2->qc_id }}')" style="font-size: 0.75rem; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak A4</span>
                                </button>
                            </div>
                            @if (!$isLocked && Auth::user()?->canEditQc())
                                <a href="{{ route('qc.inbound.edit', $p2->qc_id) }}" style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-decoration: none;">
                                    ✏ Edit Data
                                </a>
                            @endif
                        </div>
                    @elseif ($isSingkong)
                        @if ($qc->status_qc === 'DITOLAK_TOTAL')
                            <div style="padding: 2.5rem 1.5rem; text-align: center; color: #dc2626; flex-grow: 1;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🚫</div>
                                <div style="font-weight: 800; font-size: 0.9rem;">Truk Dipulangkan (Uji 1 Ditolak)</div>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">
                                    Pengujian 2 tidak dilaksanakan karena hasil sampling setengah bak pertama tidak memenuhi ambang batas mutu pabrik.
                                </div>
                            </div>
                        @else
                            <div style="padding: 1.5rem 1.25rem; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: #faf5ff;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: #f3e8ff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 0.5rem;">
                                    ⏳
                                </div>
                                <div style="font-weight: 800; font-size: 0.9rem; color: #7e22ce;">
                                    Menunggu Pengujian 2 (Sisa Bak)
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; max-width: 380px; margin-top: 0.35rem; line-height: 1.4;">
                                    Truk telah selesai membongkar setengah bak awal. Saat sisa bak dibongkar ke lini produksi, lakukan input pengujian kedua.
                                </div>
                                <div style="margin-top: 0.65rem; font-size: 0.78rem; color: #475569; background: #ffffff; padding: 4px 12px; border-radius: 6px; border: 1px solid #e9d5ff;">
                                    Estimasi Sisa Muatan: <strong style="color: #7e22ce;">{{ number_format(max(0, (float)$qc->jumlah_surat_jalan - $p1Gross), 0, ',', '.') }} kg</strong>
                                </div>

                                @if (Auth::user()?->canCreateQc())
                                    <div style="margin-top: 1rem;">
                                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id]) }}" 
                                           style="background: #7e22ce; color: #ffffff; font-size: 0.8rem; font-weight: 700; padding: 6px 14px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 5px rgba(126,34,206,0.3);">
                                            <span>🍟 Catat Pengujian 2 Sekarang</span>
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
                                Komoditas ini tidak menggunakan mekanisme sampling 2 tahap bak.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. STATUS INTEGRASI GUDANG (GRN) & FOOTER AKSI CEPAT --}}
            <div style="padding: 0.85rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    @if ($terimaObj)
                        @if ($qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0)
                            <div style="display: flex; align-items: center; gap: 0.4rem; background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                                <span>⚠️ Diterima Sebagian ke Gudang (Parsial)</span>
                                <span style="font-family: monospace;">(GRN: #{{ $terimaObj->terima_no }})</span>
                            </div>
                            <span style="font-size: 0.75rem; color: #c2410c; font-weight: 600;">
                                Masuk: <strong>{{ number_format($terimaObj->details->sum('qty_terima'), 0, ',', '.') }} kg</strong> &bull; Ditolak: <strong style="color: #dc2626;">{{ number_format($totalReject, 0, ',', '.') }} kg</strong> (Ada Berita Acara)
                            </span>
                        @else
                            <div style="display: flex; align-items: center; gap: 0.4rem; background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                                <span>✅ Sudah Masuk Stok Gudang (Penuh)</span>
                                <span style="font-family: monospace;">(GRN: #{{ $terimaObj->terima_no }})</span>
                            </div>
                            <span style="font-size: 0.75rem; color: #64748b;">
                                Diterima pada: <strong>{{ $terimaObj->tgl_terima ? $terimaObj->tgl_terima->format('d/m/Y H:i') : '-' }}</strong>
                            </span>
                        @endif
                    @elseif ($qc->status_qc === 'DITOLAK_TOTAL' && (!$p2 || $p2->status_qc === 'DITOLAK_TOTAL'))
                        <div style="display: flex; align-items: center; gap: 0.4rem; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                            <span>🚫 Truk Ditolak QC</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #dc2626;">
                            Muatan tidak ditarik ke stok pabrik. Silakan terbitkan Berita Acara Penolakan.
                        </span>
                    @elseif ($isSingkong && !$p2 && $qc->status_qc !== 'DITOLAK_TOTAL')
                        <div style="display: flex; align-items: center; gap: 0.4rem; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                            <span>⏳ Bongkar Setengah Bak Selesai</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            GRN akan menggabungkan total Netto Uji 1 ({{ number_format($p1Netto, 0, ',', '.') }} kg) dan Uji 2 setelah kedua tahap tervalidasi.
                        </span>
                    @elseif ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG'))
                        <div style="display: flex; align-items: center; gap: 0.4rem; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                            <span>📦 Lolos QC &bull; Siap Ditarik ke GRN</span>
                        </div>
                        <span style="font-size: 0.75rem; color: #047857; font-weight: 600;">
                            Total Netto Bersih Lolos: <strong>{{ number_format($totalNetto, 0, ',', '.') }} kg</strong>
                        </span>
                    @endif
                </div>

                {{-- Quick Buttons Right --}}
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    @if ($terimaObj)
                        <a href="{{ route('gudang.terima.show', $terimaObj->terima_id) }}" class="btn btn-sm btn-primary" style="font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                            <span>Buka Dokumen GRN &rarr;</span>
                        </a>
                    @elseif (!$terimaObj && ($qc->status_qc === 'SIAP_GUDANG' || ($p2 && $p2->status_qc === 'SIAP_GUDANG')) && Auth::user()?->canAccessTerima())
                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn btn-sm" style="font-size: 0.78rem; font-weight: 700; background: #059669; color: #ffffff; padding: 4px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(5,150,105,0.25);">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Tarik ke GRN Gudang</span>
                        </a>
                    @endif

                    @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->details->sum('qty_reject') > 0 || ($p2 && ($p2->status_qc === 'DITOLAK_TOTAL' || $p2->details->sum('qty_reject') > 0)))
                        <a href="{{ route('qc.inbound.berita_acara', ($p2 && $p2->details->sum('qty_reject') > 0 && $qc->details->sum('qty_reject') == 0) ? $p2->qc_id : $qc->qc_id) }}" class="btn btn-sm" style="font-size: 0.78rem; font-weight: 700; background: #dc2626; color: #ffffff; padding: 4px 10px; border-radius: 6px; text-decoration: none;">
                            <span>Cetak Berita Acara</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </td>
</tr>
