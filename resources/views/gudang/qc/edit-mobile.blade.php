@extends('layouts.qc-mobile')

@section('title', 'Koreksi Sampling QC: ' . $qc->qc_no . ' - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-mobile-detail.css') }}">
@endpush

@php
    $kat = strtoupper((string)($qc->kategori_barang ?: 'SINGKONG'));
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;
    $barangId = $firstDetail?->barang_id ?? ($barangs->firstWhere('barang_nm', 'like', '%Singkong%')?->barang_id ?? $barangs->first()?->barang_id);
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $totalReject = $qc->details->sum('qty_reject');
@endphp

@section('content')
<div class="qc-detail-wrap" style="max-width: 650px;">

    {{-- TOP APP NAV --}}
    <div class="qc-detail-top-nav">
        <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="qc-detail-back-btn">
            <span>&larr; Batal &amp; Kembali</span>
        </a>
        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
            QC Lapangan &bull; Edit Sampling
        </span>
    </div>

    {{-- HEADER CARD MOBILE --}}
    <div class="qc-detail-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="qc-detail-hero-top">
            <div>
                <span class="badge-tag-commodity" style="background: rgba(2, 132, 199, 0.4); color: #38bdf8;">
                    @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                    {{ $kat }}
                </span>
                <h1 class="qc-detail-ticket-no" style="margin-top: 0.35rem; color: #ffffff;">Edit #{{ $qc->qc_no }}</h1>
            </div>
            <span style="background: #fef08a; color: #854d0e; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 20px;">
                Mode Mobile
            </span>
        </div>
        <p style="font-size: 0.8rem; margin: 0.4rem 0 0; opacity: 0.85; line-height: 1.4;">
            Koreksi parameter sampling, hasil uji laboratorium, atau tonase timbangan kedatangan langsung dari smartphone.
        </p>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error" style="margin-bottom: 1rem; border-radius: 10px; background: #fef2f2; border: 1.5px solid #fecaca; padding: 0.75rem 1rem; color: #991b1b;">
            <div style="font-weight: 800; font-size: 0.85rem; margin-bottom: 0.25rem;">⚠️ Periksa isian berikut:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM UTAMA MOBILE EDIT --}}
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditMobileForm" style="display: flex; flex-direction: column; gap: 1rem;">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="kategori_barang" value="{{ $qc->kategori_barang }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][barang_id]" value="{{ $barangId }}">

        {{-- KARTU 1: INFO PENGIRIMAN & ARMADA --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🚚</span> <span>1. Info Armada &amp; Pengiriman</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Mitra Supplier (Pengirim) *</label>
                    <select name="supplier_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->supplier_id }}" {{ old('supplier_id', $qc->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Gudang Bongkar *</label>
                    <select name="gudang_id" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                        @foreach ($gudangs as $g)
                            <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->gudang_nm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">No. Surat Jalan / DO</label>
                        <input type="text" name="nomor_do" value="{{ old('nomor_do', $qc->nomor_do ?? $qc->surat_jalan_supplier) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">No. Plat Truk</label>
                        <input type="text" name="plat_nomor_truk" value="{{ old('plat_nomor_truk', $qc->plat_nomor_truk) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Nama Sopir</label>
                        <input type="text" name="sopir_nama" value="{{ old('sopir_nama', $qc->sopir_nama) }}" class="form-control" style="width: 100%; border-radius: 8px;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Waktu Periksa</label>
                        <input type="datetime-local" name="tgl_periksa" value="{{ old('tgl_periksa', $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d\TH:i') : date('Y-m-d\TH:i')) }}" class="form-control" style="width: 100%; border-radius: 8px; font-size: 0.8rem;">
                    </div>
                </div>

                {{-- AUDIT HALAL & KEBERSIHAN --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.65rem 0.85rem; margin-top: 0.25rem;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: #475569; margin-bottom: 0.4rem;">AUDIT TRANSPORTASI &amp; JAMINAN HALAL:</div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.8rem;">
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="bebas_cemaran_st" value="1" {{ old('bebas_cemaran_st', $qc->bebas_cemaran_st) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span style="font-weight: 700;">Truk Bersih (Bebas Najis / Kotoran / Cemaran)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="angkut_barang_haram_st" value="1" {{ old('angkut_barang_haram_st', $qc->angkut_barang_haram_st) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                            <span style="font-weight: 700; color: #dc2626;">Truk Membawa Barang Haram (Centang jika ada)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: TIMBANGAN & TONASE --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>⚖️</span> <span>2. Hasil Timbangan &amp; Tonase</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Jumlah Surat Jalan (kg)</label>
                        <input type="number" step="any" name="jumlah_surat_jalan" value="{{ old('jumlah_surat_jalan', $qc->jumlah_surat_jalan) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Jumlah di Pabrik (kg)</label>
                        <input type="number" step="any" name="jumlah_di_pabrik" value="{{ old('jumlah_di_pabrik', $qc->jumlah_di_pabrik ?? $totalGross) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; color: #0284c7;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Bruto (kg) *</label>
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_timbang_gross]" id="inputGross" value="{{ old('items.'.$qcdtlId.'.qty_timbang_gross', $firstDetail?->qty_timbang_gross ?? $totalGross) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 800;" oninput="recalcMobileNetto()" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Refraksi (%)</label>
                        <input type="number" step="0.1" name="items[{{ $qcdtlId }}][refraksi_persen]" id="inputRefraksiPersen" value="{{ old('items.'.$qcdtlId.'.refraksi_persen', $firstDetail?->refraksi_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 8px; color: #d97706; font-weight: 700;" oninput="recalcMobileNetto()">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Reject (kg)</label>
                        <input type="number" step="any" name="items[{{ $qcdtlId }}][qty_reject]" id="inputReject" value="{{ old('items.'.$qcdtlId.'.qty_reject', $firstDetail?->qty_reject ?? $totalReject) }}" class="form-control" style="width: 100%; border-radius: 8px; color: #dc2626; font-weight: 700;" oninput="recalcMobileNetto()">
                    </div>
                </div>

                <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 0.85rem 1rem; text-align: center; margin-top: 0.25rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #065f46; text-transform: uppercase;">Estimasi Netto Lolos Diterima Pabrik</div>
                    <div style="font-size: 1.45rem; font-weight: 900; color: #059669; margin-top: 0.2rem;" id="displayNettoText">
                        {{ number_format($totalNetto, 0, ',', '.') }} kg
                    </div>
                    <input type="hidden" name="items[{{ $qcdtlId }}][qty_netto_lolos]" id="inputNettoLolos" value="{{ old('items.'.$qcdtlId.'.qty_netto_lolos', $firstDetail?->qty_netto_lolos ?? $totalNetto) }}">
                </div>
            </div>
        </div>

        {{-- KARTU 3: PARAMETER MUTU & HASIL FRYER LENGKAP --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>🔬</span> <span>3. Parameter Mutu &amp; Pengujian ({{ $kat }})</span>
            </h2>

            @if ($kat === 'SINGKONG')
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    {{-- DIAMETER UMBI --}}
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 800; color: #0284c7; margin-bottom: 0.35rem;">PENGUJIAN I &bull; DIAMETER UMBI:</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                            <div>
                                <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Diameter &lt; 4 cm (% Max 5%)</label>
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][diameter_kurang_4cm_persen]" value="{{ old('items.'.$qcdtlId.'.diameter_kurang_4cm_persen', $firstDetail?->diameter_kurang_4cm_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 8px;">
                            </div>
                            <div>
                                <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Diameter &ge; 4 cm (% Min 95%)</label>
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][diameter_lebih_4cm_persen]" value="{{ old('items.'.$qcdtlId.'.diameter_lebih_4cm_persen', $firstDetail?->diameter_lebih_4cm_persen ?? 100) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- HASIL UJI GORENG (LAB FRYER) --}}
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem;">
                        <div style="font-size: 0.78rem; font-weight: 800; color: #d97706; margin-bottom: 0.45rem;">PENGUJIAN II &bull; HASIL UJI GORENG (LAB FRYER):</div>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 0.65rem;">
                            <div>
                                <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem;">Rasa</label>
                                <select name="items[{{ $qcdtlId }}][fryer_rasa]" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                    <option value="TIDAK_PAHIT" {{ old('items.'.$qcdtlId.'.fryer_rasa', $firstDetail?->fryer_rasa ?? 'TIDAK_PAHIT') === 'TIDAK_PAHIT' ? 'selected' : '' }}>Tidak Pahit</option>
                                    <option value="PAHIT" {{ old('items.'.$qcdtlId.'.fryer_rasa', $firstDetail?->fryer_rasa ?? '') === 'PAHIT' ? 'selected' : '' }}>Pahit</option>
                                </select>
                            </div>
                            <div>
                                <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem;">Tekstur</label>
                                <select name="items[{{ $qcdtlId }}][fryer_tekstur]" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                    <option value="RENYAH" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur ?? 'RENYAH') === 'RENYAH' ? 'selected' : '' }}>Renyah</option>
                                    <option value="ALOT" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur ?? '') === 'ALOT' ? 'selected' : '' }}>Alot</option>
                                    <option value="LEMBEK" {{ old('items.'.$qcdtlId.'.fryer_tekstur', $firstDetail?->fryer_tekstur ?? '') === 'LEMBEK' ? 'selected' : '' }}>Lembek</option>
                                </select>
                            </div>
                            <div>
                                <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.2rem;">Penampakan</label>
                                <select name="items[{{ $qcdtlId }}][fryer_penampakan]" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem; font-weight: 700;">
                                    <option value="TIDAK_OILSOAKED" {{ old('items.'.$qcdtlId.'.fryer_penampakan', $firstDetail?->fryer_penampakan ?? 'TIDAK_OILSOAKED') === 'TIDAK_OILSOAKED' ? 'selected' : '' }}>Normal</option>
                                    <option value="OILSOAKED" {{ old('items.'.$qcdtlId.'.fryer_penampakan', $firstDetail?->fryer_penampakan ?? '') === 'OILSOAKED' ? 'selected' : '' }}>Oilsoaked</option>
                                </select>
                            </div>
                        </div>

                        <div style="font-size: 0.725rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem;">DEFECT FRYING (%):</div>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                            <div>
                                <label class="qc-info-label">Breakage %</label>
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_breakage_persen]" value="{{ old('items.'.$qcdtlId.'.defect_breakage_persen', $firstDetail?->defect_breakage_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                            </div>
                            <div>
                                <label class="qc-info-label">Cluster %</label>
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_cluster_persen]" value="{{ old('items.'.$qcdtlId.'.defect_cluster_persen', $firstDetail?->defect_cluster_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                            </div>
                            <div>
                                <label class="qc-info-label">Gambos %</label>
                                <input type="number" step="0.1" name="items[{{ $qcdtlId }}][defect_gambos_persen]" value="{{ old('items.'.$qcdtlId.'.defect_gambos_persen', $firstDetail?->defect_gambos_persen ?? 0) }}" class="form-control" style="width: 100%; border-radius: 6px; font-size: 0.8rem;">
                            </div>
                        </div>
                    </div>
                </div>

            @elseif ($kat === 'MINYAK')
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">FFA COA</label>
                        <input type="number" step="0.001" name="items[{{ $qcdtlId }}][ffa_coa]" value="{{ old('items.'.$qcdtlId.'.ffa_coa', $firstDetail?->ffa_coa ?? '') }}" class="form-control" style="width: 100%; border-radius: 8px;">
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">FFA Cek QC Mirasa</label>
                        <input type="number" step="0.001" name="items[{{ $qcdtlId }}][ffa_qc]" value="{{ old('items.'.$qcdtlId.'.ffa_qc', $firstDetail?->ffa_qc ?? '') }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; color: #0284c7;">
                    </div>
                </div>
            @endif
        </div>

        {{-- KARTU 4: KEPUTUSAN KESIMPULAN & CATATAN --}}
        <div class="qc-card-section">
            <h2 class="qc-card-title">
                <span>💬</span> <span>4. Keputusan &amp; Catatan QC</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.35rem;">Keputusan Akhir QC:</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                        <label style="display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer;">
                            <input type="radio" name="kesimpulan_qc" value="TERIMA" {{ old('kesimpulan_qc', $qc->status_qc !== 'DITOLAK_TOTAL' ? 'TERIMA' : '') === 'TERIMA' ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span style="font-weight: 900; color: #15803d; font-size: 0.95rem;">✔ TERIMA</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; background: #fef2f2; border: 2px solid #fca5a5; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer;">
                            <input type="radio" name="kesimpulan_qc" value="TOLAK" {{ old('kesimpulan_qc', $qc->status_qc === 'DITOLAK_TOTAL' ? 'TOLAK' : '') === 'TOLAK' ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span style="font-weight: 900; color: #b91c1c; font-size: 0.95rem;">✖ TOLAK</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Catatan Petugas QC</label>
                    <textarea name="catatan_umum" rows="2" class="form-control" style="width: 100%; border-radius: 8px;" placeholder="Catatan hasil sampling...">{{ old('catatan_umum', $firstDetail?->catatan_dtl ?: $qc->catatan_umum) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">Petugas QC</label>
                        <input type="text" name="petugas_qc_nama" value="{{ old('petugas_qc_nama', $qc->petugas_qc_nama) }}" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700;" required>
                    </div>
                    <div>
                        <label class="qc-info-label" style="display: block; font-weight: 700; margin-bottom: 0.25rem;">QC Supervisor</label>
                        <input type="text" name="qc_supervisor_nama" value="{{ old('qc_supervisor_nama', $qc->qc_supervisor_nama) }}" class="form-control" style="width: 100%; border-radius: 8px;" placeholder="Supervisor QC">
                    </div>
                </div>
            </div>
        </div>

        {{-- TOMBOL SUBMIT FIXED BOTTOM --}}
        <div class="qc-mobile-bottom-bar">
            <button type="submit" class="qc-btn-mobile-edit" style="font-size: 0.95rem; padding: 0.8rem 1rem;">
                <span>💾 Simpan Perubahan Uji Mutu</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function recalcMobileNetto() {
        const gross = parseFloat(document.getElementById('inputGross')?.value) || 0;
        const refPersen = parseFloat(document.getElementById('inputRefraksiPersen')?.value) || 0;
        const reject = parseFloat(document.getElementById('inputReject')?.value) || 0;

        const refKg = (gross * refPersen) / 100;
        let netto = gross - refKg - reject;
        if (netto < 0) netto = 0;

        const displayEl = document.getElementById('displayNettoText');
        const hiddenEl = document.getElementById('inputNettoLolos');

        if (displayEl) {
            displayEl.innerText = Math.round(netto).toLocaleString('id-ID') + ' kg';
        }
        if (hiddenEl) {
            hiddenEl.value = Math.round(netto);
        }
    }
</script>
@endpush
