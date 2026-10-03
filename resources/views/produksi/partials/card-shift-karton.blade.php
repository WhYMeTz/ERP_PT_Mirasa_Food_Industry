{{-- 
  Kartu Shift Kerja, Penomoran Batch & Kemasan Karton
  Partials Blade ERP PT Mirasa Food Industry (Unified Enterprise Grid Layout)
--}}

<div class="card" style="margin-bottom: 1.25rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <strong id="cardHeaderTitle" style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>2. Shift Kerja, Penomoran Batch &amp; Kemasan Karton</span>
        </strong>
        <span id="cardHeaderBadge" style="font-size: 0.775rem; font-weight: 700; color: #0369a1; background: #e0f2fe; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1px solid #bae6fd;">
            Format Batch: [Shift][NoAwal] - [Shift][NoAkhir]
        </span>
    </div>

    <div style="padding: 1.25rem;">
        {{-- Hidden Input Shift & Jam --}}
        <input type="hidden" name="shift_cd" id="shift_cd" value="{{ old('shift_cd', 'A') }}">
        <input type="hidden" name="jam_produksi" id="jam_produksi" value="{{ old('jam_produksi', date('H:i')) }}">

        {{-- BARIS 1: SHIFT KERJA & PENOMORAN KARTON --}}
        <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; align-items: start;">
            {{-- 1. Shift Kerja Segmented Toggle --}}
            <div id="sectionShiftSelection">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Shift Kerja Produksi <span style="color: #ef4444;">*</span>
                </label>
                <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 8px; border: 1px solid #cbd5e1; gap: 3px;">
                    <button type="button" id="btnShiftA" onclick="selectShift('A')" 
                        style="flex: 1; padding: 0.5rem 0.75rem; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                        class="{{ old('shift_cd', 'A') === 'A' ? 'shift-segmented-active active-a' : 'shift-segmented-inactive' }}">
                        Shift A
                    </button>
                    <button type="button" id="btnShiftB" onclick="selectShift('B')" 
                        style="flex: 1; padding: 0.5rem 0.75rem; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                        class="{{ old('shift_cd', 'A') === 'B' ? 'shift-segmented-active active-b' : 'shift-segmented-inactive' }}">
                        Shift B
                    </button>
                </div>
                <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.35rem; display: block;">Regu operasional lantai pabrik.</small>
            </div>

            {{-- 2. Qty Karton Selesai --}}
            <div>
                <label id="labelKartonTitle" style="display: block; font-size: 0.8rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Karton Selesai <span style="color: #ef4444;">*</span>
                </label>
                <div style="position: relative;">
                    <input type="number" step="1" min="0" name="qty_karton" id="qty_karton" 
                        value="{{ old('qty_karton', 0) }}" 
                        class="form-control" 
                        style="font-weight: 800; font-size: 1rem; color: #0f172a; padding-right: 4.5rem;" 
                        placeholder="0" 
                        oninput="updateKartonRangeAndBatch()">
                    <span style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); font-size: 0.75rem; font-weight: 700; color: #64748b; pointer-events: none;">Kemasan</span>
                </div>
                <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.35rem; display: block;">
                    Berat: <strong id="liveEstimasiKg" style="color: #0284c7;">0.00 Kg</strong> <span id="liveEstimasiDesc">(Netto 6 Kg/Box)</span>
                </small>
            </div>

            {{-- 3. No. Karton Awal --}}
            <div id="colKartonAwal">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    No. Karton Awal <span style="color: #ef4444;">*</span>
                </label>
                <input type="number" step="1" min="1" name="no_karton_awal" id="no_karton_awal" 
                    value="{{ old('no_karton_awal', 1) }}" 
                    class="form-control" 
                    style="font-family: monospace; font-weight: 700; font-size: 0.95rem;" 
                    oninput="updateKartonRangeAndBatch()">
                <small id="kartonSourceInfo" style="color: #0284c7; font-size: 0.725rem; margin-top: 0.35rem; display: block;">
                    Memuat riwayat...
                </small>
            </div>

            {{-- 4. No. Karton Akhir (Readonly) --}}
            <div id="colKartonAkhir">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    No. Karton Akhir
                </label>
                <input type="number" name="no_karton_akhir" id="no_karton_akhir" 
                    value="{{ old('no_karton_akhir', 1) }}" 
                    class="form-control" 
                    readonly 
                    style="background: #f8fafc; font-family: monospace; font-weight: 700; font-size: 0.95rem; color: #475569;">
                <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.35rem; display: block;">
                    Otomatis: Awal + Qty - 1
                </small>
            </div>
        </div>

        {{-- BARIS 2: IDENTITAS MUTU, EXP DATE & KODE BATCH RESMI --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr 1.35fr; gap: 1rem; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem 1.25rem;">
            {{-- 1. Varietas Singkong Mentah --}}
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Varietas Singkong Mentah
                </label>
                <input type="text" name="varietas_singkong" id="varietas_singkong" 
                    value="{{ old('varietas_singkong', 'STP / MGU') }}" 
                    class="form-control" 
                    placeholder="Contoh: STP / MGU" 
                    style="font-weight: 600; font-size: 0.85rem;" 
                    oninput="updateKartonRangeAndBatch()">
                <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.35rem; display: block;">
                    Singkong Tape (STP) / Manggu (MGU).
                </small>
            </div>

            {{-- 2. Tanggal Kedaluwarsa (Exp Date) --}}
            <div>
                <label for="exp_date" style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <span>Tgl Kedaluwarsa (Exp)</span>
                    <span id="expDateBadgeDesc" style="font-size: 0.7rem; color: #0284c7; font-weight: 600;">(Dapat Diedit)</span>
                </label>
                <input type="date" name="exp_date" id="exp_date" class="form-control" 
                    value="{{ old('exp_date') }}" 
                    style="font-weight: 700; font-size: 0.85rem; color: #0f172a;"
                    title="Tanggal kedaluwarsa otomatis disarankan oleh sistem, namun dapat diubah sesuai kebijakan perusahaan atau buyer.">
                <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.35rem; display: block;">
                    Disarankan sistem &amp; bisa disesuaikan.
                </small>
            </div>

            {{-- 3. Kode Batch Resmi Persadaan (Corporate Highlight Box) --}}
            <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 8px; padding: 0.75rem 1rem; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.05);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                    <span style="font-size: 0.725rem; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 0.05em;">
                        Kode Batch Resmi Persadaan
                    </span>
                    <span style="font-size: 0.675rem; background: #e0f2fe; color: #0284c7; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 4px;">
                        Otomatis Standar Lini
                    </span>
                </div>
                <div id="liveBatchCode" style="font-family: monospace; font-size: 1.35rem; font-weight: 900; color: #0284c7; letter-spacing: 0.05em; line-height: 1.2;">
                    {{ old('batch_wip_no', 'A0001 - A0001') }}
                </div>
                <input type="hidden" name="batch_wip_no" id="batch_wip_no" value="{{ old('batch_wip_no') }}">
            </div>
        </div>

    </div>
</div>
