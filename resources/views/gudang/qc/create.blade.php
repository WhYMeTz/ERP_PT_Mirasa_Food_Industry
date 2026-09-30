@extends('layouts.app')

@section('title', 'Form Uji QC Singkong (Pengujian I & II) - PT Mirasa')

@section('content')
<div style="max-width: 820px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner HACCP PT Mirasa --}}
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.5rem 1.75rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 PT. MIRASA FOOD INDUSTRY &bull; HACCP-04
                    </span>
                    <span style="background: #10b981; color: #ffffff; font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 20px;">
                        No. Dok: MFI/HACCP-04/FRM-03/048/VIII/2021
                    </span>
                </div>
                <h1 style="font-size: 1.45rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    Checklist Standar Kebeterimaan Singkong
                </h1>
                <p style="font-size: 0.85rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Laporan Kedatangan Singkong: <strong>Pengujian I (Fisik)</strong> &amp; <strong>Pengujian II (Hasil Fryer / Uji Goreng)</strong>
                </p>
            </div>
            <a href="{{ route('qc.inbound.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0284c7; border-radius: 8px; font-weight: 700;">
                📋 Riwayat Tiket
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" style="margin-top: 1rem; border-radius: 10px;">
            <div style="font-weight: 700; margin-bottom: 0.35rem;">⚠️ Harap periksa isian formulir:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STEP / TAB SWITCHER FOR MOBILE --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 1rem; margin-bottom: 0.5rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchQcTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #0284c7; background: #0284c7; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Armada &amp; Halal
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchQcTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            📏 2. Pengujian I (Fisik)
        </button>
        <button type="button" class="btn" id="tabBtn3" onclick="switchQcTab(3)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🍟 3. Pengujian II (Fryer)
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN, LOKASI PANEN & KONDISI TRANSPORTASI (HALAL)             --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" class="card" style="border-radius: 12px; border-top: 5px solid #0284c7; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 1: Laporan Kedatangan &amp; Kebersihan Transportasi</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Verifikasi asal kebun, surat jalan, dan syarat halal bebas najis</span>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.1rem;">
                {{-- PILIH DARI PO AKTIF --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b;">
                        Referensi Purchase Order (PO) <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">(Opsional)</span>
                    </label>
                    <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 600;">
                        <option value="">-- Tanpa PO / Kiriman Langsung --</option>
                        @foreach ($pos as $p)
                            <option value="{{ $p->po_id }}" {{ old('po_id', $selectedPo?->po_id) == $p->po_id ? 'selected' : '' }}>
                                {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} (Sisa: {{ $p->details->sum(fn($d) => max(0, $d->pesan_qty - $d->terima_qty)) }} KG)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- NAMA RM, PRODUSEN, LOKASI PANEN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Produsen / Mitra Supplier <span style="color:#ef4444;">*</span></label>
                        <select name="supplier_id" id="supplierSelect" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->supplier_id }}" {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                    {{ $s->supplier_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Gudang Bongkar di Pabrik <span style="color:#ef4444;">*</span></label>
                        <select name="gudang_id" id="gudangSelect" class="form-control" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? auth()->user()->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Negara Produsen</label>
                        <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Lokasi Panen</label>
                        <input type="text" name="lokasi_panen" class="form-control" placeholder="Contoh: Wonosobo / Kebumen / Magelang" value="{{ old('lokasi_panen') }}">
                    </div>
                </div>

                {{-- UMUR SINGKONG, TGL PANEN, TGL DATANG, SAMPLE --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Umur Singkong (Bulan)</label>
                        <input type="number" step="0.5" name="umur_singkong_bln" class="form-control" placeholder="Contoh: 9.5" value="{{ old('umur_singkong_bln', 9.0) }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tanggal Panen</label>
                        <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', date('Y-m-d', strtotime('-1 day'))) }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Waktu Kedatangan</label>
                        <input type="datetime-local" name="tgl_periksa" class="form-control" value="{{ old('tgl_periksa', now()->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Jumlah Sample (KG)</label>
                        <input type="number" step="0.1" name="jumlah_sample_kg" class="form-control" placeholder="Contoh: 10" value="{{ old('jumlah_sample_kg', 10.0) }}">
                    </div>
                </div>

                {{-- SURAT JALAN & PLAT TRUK --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">No. Surat Jalan</label>
                        <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="No SJ..." value="{{ old('surat_jalan_supplier') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Plat Nomor Truk</label>
                        <input type="text" name="plat_nomor_truk" class="form-control" placeholder="AA 1234 XY" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nama Pengemudi / Sopir</label>
                        <input type="text" name="sopir_nama" class="form-control" placeholder="Nama sopir" value="{{ old('sopir_nama') }}">
                    </div>
                </div>

                {{-- SYARAT HALAL & KONDISI TRANSPORTASI (HACCP) --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem; margin-top: 0.5rem;">
                    <div style="font-weight: 800; font-size: 0.875rem; color: #0f172a; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🛡️</span> <span>Standar Kebersihan Transportasi &amp; Jaminan Halal</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                        {{-- KONDISI TRANSPORTASI --}}
                        <div>
                            <span class="form-label" style="font-weight: 700; font-size: 0.825rem;">Kondisi Transportasi:</span>
                            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.35rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="bebas_cemaran_st" value="1" checked>
                                    <span style="color: #15803d; font-weight: 700;">✅ Tidak ada cemaran, Najis / Kotoran</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="bebas_cemaran_st" value="0">
                                    <span style="color: #dc2626; font-weight: 700;">❌ Ada cemaran</span>
                                </label>
                            </div>
                        </div>

                        {{-- ANGKUT BERSAMA BARANG HARAM --}}
                        <div>
                            <span class="form-label" style="font-weight: 700; font-size: 0.825rem;">Apakah Diangkut Bersama Barang Haram?</span>
                            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.35rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="angkut_barang_haram_st" value="0" checked>
                                    <span style="color: #15803d; font-weight: 700;">✅ Tidak</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="angkut_barang_haram_st" value="1">
                                    <span style="color: #dc2626; font-weight: 700;">❌ Ya (Haram)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 0.75rem;">
                        <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar transportasi / kebersihan bak truk..." style="font-size: 0.825rem;">
                    </div>
                </div>

                {{-- Action Tombol Lanjut ke Pengujian I --}}
                <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="button" class="btn btn-primary" onclick="switchQcTab(2)" style="border-radius: 8px;">
                        Lanjut ke Pengujian I (Fisik) &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PENGUJIAN I (SAMPLING FISIK, DIAMETER, REFRAKSI, TIMBANGAN)      --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #10b981; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 2: Pengujian I &bull; Sampling Fisik &amp; Diameter</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Standar diameter, kebersihan tanah/refraksi &amp; kondisi visual singkong</span>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addItemRow()" style="border-radius: 8px;">
                    + Tambah Item
                </button>
            </div>

            <div id="itemsContainer" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Item Cards rendered by JS --}}
            </div>

            <div style="padding: 1rem 1.4rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-secondary" onclick="switchQcTab(1)" style="border-radius: 8px;">
                    &larr; Kembali ke Tahap 1
                </button>
                <button type="button" class="btn btn-primary" onclick="switchQcTab(3)" style="border-radius: 8px;">
                    Lanjut ke Pengujian II (Uji Goreng) &rarr;
                </button>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 3: PENGUJIAN II (HASIL FRYER / UJI GORENG & DEFECT FRYING)           --}}
        {{-- ========================================================================= --}}
        <div id="qcSection3" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #f59e0b; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 3: Pengujian II &bull; Hasil Fryer &amp; Cacat Goreng</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Hasil uji goreng lab: Rasa (tidak pahit), tekstur renyah &amp; % defect</span>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Container Parameter Fryer per Item --}}
                <div id="fryerParamsContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                    {{-- Dynamically mirrored from Tab 2 items --}}
                </div>

                {{-- KESIMPULAN AKHIR & TANDA TANGAN ACC --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem;">
                        📋 KESIMPULAN AKHIR MUTU BAHAN BAKU
                    </div>

                    {{-- LIVE SUMMARY BOX --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Timbangan Kotor (Gross):</span>
                            <div id="summaryGross" style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">0.00 KG</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                            <span style="font-size: 0.75rem; color: #ca8a04; font-weight: 600;">Potongan Refraksi Tanah:</span>
                            <div id="summaryRefraksi" style="font-size: 1.15rem; font-weight: 800; color: #b45309;">- 0.00 KG</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                            <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600;">Afkir / Cacat Busuk (Tolak):</span>
                            <div id="summaryReject" style="font-size: 1.15rem; font-weight: 800; color: #dc2626;">- 0.00 KG</div>
                        </div>
                        <div style="background: #dcfce7; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.75rem;">
                            <span style="font-size: 0.75rem; color: #15803d; font-weight: 800;">TOTAL BERSIH TERIMA:</span>
                            <div id="summaryNetto" style="font-size: 1.35rem; font-weight: 900; color: #15803d;">0.00 KG</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 700;">Catatan / Komentar Tambahan QC</label>
                        <textarea name="catatan_umum" rows="2" class="form-control" placeholder="Contoh: Singkong panen umur 9 bulan kualitas super, rasa gurih tidak pahit, potongan refraksi tanah 2%.">{{ old('catatan_umum') }}</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <label class="form-label">Petugas QC Pemeriksa</label>
                            <input type="text" name="petugas_qc_nama" class="form-control" value="{{ old('petugas_qc_nama', auth()->user()->name) }}" readonly style="background: #ffffff; font-weight: 700;">
                        </div>
                        <div>
                            <label class="form-label">QC Supervisor (Penanggung Jawab)</label>
                            <input type="text" name="qc_supervisor_nama" class="form-control" placeholder="Nama QC Supervisor" value="{{ old('qc_supervisor_nama', 'Supervisor QC') }}">
                        </div>
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="switchQcTab(2)" style="border-radius: 8px;">
                        &larr; Kembali ke Pengujian I
                    </button>
                    <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 1.05rem; padding: 0.85rem; border-radius: 10px; background: #059669; border: none; font-weight: 800; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3);">
                        ✅ Simpan Hasil Pengujian I &amp; II (Teruskan ke Gudang)
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@php
    $rawMaterialsData = $barangs->map(function ($b) {
        return [
            'barang_id' => $b->barang_id,
            'barang_cd' => $b->barang_cd,
            'barang_nm' => $b->barang_nm,
            'satuan'    => $b->satuanDasar?->satuan_nm ?? 'KG',
        ];
    })->values();

    $poListData = $pos->mapWithKeys(function ($p) {
        return [
            $p->po_id => [
                'po_id'       => $p->po_id,
                'supplier_id' => $p->supplier_id,
                'gudang_id'   => $p->gudang_id,
                'items'       => $p->details->map(function ($d) {
                    return [
                        'podtl_id'  => $d->podtl_id,
                        'barang_id' => $d->barang_id,
                        'barang_nm' => $d->barang?->barang_nm,
                        'satuan'    => $d->barang?->satuanDasar?->satuan_nm ?? 'KG',
                        'pesan_qty' => (float) $d->pesan_qty,
                        'terima_qty'=> (float) $d->terima_qty,
                        'sisa_qty'  => max(0, (float) $d->pesan_qty - (float) $d->terima_qty),
                    ];
                })->values(),
            ]
        ];
    });
@endphp

<script>
    const RAW_MATERIALS = {!! json_encode($rawMaterialsData) !!};
    const PO_LIST = {!! json_encode($poListData) !!};

    let itemIndex = 0;

    function switchQcTab(tabNumber) {
        document.getElementById('qcSection1').style.display = (tabNumber === 1) ? 'block' : 'none';
        document.getElementById('qcSection2').style.display = (tabNumber === 2) ? 'block' : 'none';
        document.getElementById('qcSection3').style.display = (tabNumber === 3) ? 'block' : 'none';

        for (let i = 1; i <= 3; i++) {
            const btn = document.getElementById(`tabBtn${i}`);
            if (i === tabNumber) {
                btn.style.background = '#0284c7';
                btn.style.borderColor = '#0284c7';
                btn.style.color = '#ffffff';
            } else {
                btn.style.background = '#ffffff';
                btn.style.borderColor = '#cbd5e1';
                btn.style.color = '#475569';
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function createItemCard(data = {}) {
        const idx = itemIndex++;
        const card = document.createElement('div');
        card.className = 'qc-item-card';
        card.id = `item_card_${idx}`;
        card.style.cssText = 'background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.15rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03); position: relative;';

        const selectedBarangId = data.barang_id || '';
        const grossVal = data.gross || '';
        const kadarAirVal = data.kadar_air || '12.0';
        const refraksiPersenVal = data.refraksi_persen || '0.0';
        const rejectVal = data.reject || '0';
        const podtlId = data.podtl_id || '';

        let barangOptions = '<option value="">-- Pilih Bahan Baku Singkong --</option>';
        RAW_MATERIALS.forEach(b => {
            const isSel = (b.barang_id == selectedBarangId) ? 'selected' : '';
            barangOptions += `<option value="${b.barang_id}" ${isSel}>${b.barang_cd} - ${b.barang_nm} (${b.satuan})</option>`;
        });

        card.innerHTML = `
            <input type="hidden" name="items[${idx}][podtl_id]" value="${podtlId}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <span style="font-weight: 800; font-size: 0.85rem; color: #0284c7; background: #e0f2fe; padding: 0.2rem 0.6rem; border-radius: 6px;">
                    Komoditas #${idx + 1}
                </span>
                <button type="button" onclick="removeItemCard(${idx})" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                    ✕ Hapus
                </button>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="form-label" style="font-size: 0.825rem; font-weight: 700;">Nama Bahan Baku <span style="color:red;">*</span></label>
                <select name="items[${idx}][barang_id]" id="barang_select_${idx}" class="form-control item-barang-select" required onchange="onItemBarangChanged(${idx})">
                    ${barangOptions}
                </select>
            </div>

            {{-- 1. STANDAR DIAMETER SINGKONG --}}
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>📐 1. Parameter Diameter Singkong</span>
                    <span style="font-size: 0.75rem; color: #64748b;">Standar Kebeterimaan</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.775rem;">Diameter < 4 cm (Max 5.0%)</label>
                        <div style="position: relative;">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diameter_kurang_4cm_persen]" value="2.0" class="form-control" style="font-size: 0.85rem;">
                            <span style="position: absolute; right: 10px; top: 7px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.775rem;">Diameter &ge; 4 cm (Min 95.0%)</label>
                        <div style="position: relative;">
                            <input type="number" step="0.1" min="0" max="100" name="items[${idx}][diameter_lebih_4cm_persen]" value="98.0" class="form-control" style="font-size: 0.85rem; font-weight: 700; color: #15803d;">
                            <span style="position: absolute; right: 10px; top: 7px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. CHECKBOX KONDISI VISUAL SINGKONG --}}
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                    👁️ 2. Pemeriksaan Visual Singkong (Pilih yang sesuai):
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.5rem; font-size: 0.8rem;">
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; color: #15803d; font-weight: 700;">
                        <input type="checkbox" name="items[${idx}][kondisi_segar]" value="1" checked> SEGAR
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_layu]" value="1"> LAYU
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_basah]" value="1"> BASAH
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_terkelupas]" value="1"> TERKELUPAS
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                        <input type="checkbox" name="items[${idx}][kondisi_busuk]" value="1"> BUSUK
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; color: #b45309; font-weight: 700;">
                        <input type="checkbox" name="items[${idx}][kondisi_berjamur]" value="1"> BERJAMUR
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" name="items[${idx}][kondisi_lembek]" value="1"> TEKSTUR LEMBEK
                    </label>
                </div>
            </div>

            {{-- 3. TIMBANGAN GROSS, KADAR AIR, REFRAKSI, REJECT --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">
                        Timbangan Kotor (Gross) <span style="color:red;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.01" min="0.01" name="items[${idx}][qty_timbang_gross]" id="gross_${idx}" value="${grossVal}" class="form-control" placeholder="0.00" required oninput="calculateCard(${idx})" style="font-weight: 800; font-size: 1rem;">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">KG</span>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">
                        Kadar Air (%)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.1" min="0" max="100" name="items[${idx}][kadar_air_persen]" id="kadar_air_${idx}" value="${kadarAirVal}" class="form-control" placeholder="12.0" oninput="calculateCard(${idx})">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #0f172a;">
                        Refraksi Kotoran / Tanah
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.05" min="0" max="100" name="items[${idx}][refraksi_persen]" id="refraksi_${idx}" value="${refraksiPersenVal}" class="form-control" placeholder="0.0" oninput="calculateCard(${idx})">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">%</span>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #ef4444;">
                        Afkir / Busuk (Reject)
                    </label>
                    <div style="position: relative;">
                        <input type="number" step="0.01" min="0" name="items[${idx}][qty_reject]" id="reject_${idx}" value="${rejectVal}" class="form-control" placeholder="0.00" oninput="calculateCard(${idx})">
                        <span style="position: absolute; right: 10px; top: 8px; font-size: 0.75rem; color: #94a3b8; font-weight: 700;">KG</span>
                    </div>
                </div>
            </div>

            {{-- LIVE BREAKDOWN --}}
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.65rem 0.85rem; font-size: 0.825rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem;">
                <div>
                    <span style="color: #64748b;">Potongan Refraksi Tanah:</span> 
                    <strong id="label_qty_refraksi_${idx}" style="color: #b45309;">0.00 KG</strong>
                </div>
                <div>
                    <span style="color: #166534; font-weight: 700;">Netto Lolos Diterima:</span> 
                    <strong id="label_netto_${idx}" style="color: #15803d; font-size: 0.95rem;">0.00 KG</strong>
                </div>
            </div>
        `;

        document.getElementById('itemsContainer').appendChild(card);
        renderFryerParamsForCard(idx);
        calculateCard(idx);
    }

    function renderFryerParamsForCard(idx) {
        let container = document.getElementById('fryerParamsContainer');
        let block = document.getElementById(`fryer_block_${idx}`);
        if (!block) {
            block = document.createElement('div');
            block.id = `fryer_block_${idx}`;
            block.className = 'fryer-param-card';
            block.style.cssText = 'background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.1rem;';
            container.appendChild(block);
        }

        const sel = document.getElementById(`barang_select_${idx}`);
        const barangNm = sel && sel.selectedOptions[0] ? sel.selectedOptions[0].text : `Item #${idx + 1}`;

        block.innerHTML = `
            <div style="font-weight: 800; font-size: 0.9rem; color: #0284c7; margin-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.35rem;">
                🍟 Uji Goreng (Hasil Fryer Lab) &bull; ${barangNm}
            </div>

            {{-- HASIL SENSORI FRYER --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.85rem; margin-bottom: 1rem;">
                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">RASA (Standar: Tidak Pahit)</label>
                    <select name="items[${idx}][fryer_rasa]" class="form-control" style="font-size: 0.85rem; font-weight: 600;">
                        <option value="TIDAK_PAHIT" selected>✅ Tidak Pahit (Lolos)</option>
                        <option value="PAHIT">❌ Pahit / Sianida (Tolak)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">TEKSTUR (Standar: Renyah)</label>
                    <select name="items[${idx}][fryer_tekstur]" class="form-control" style="font-size: 0.85rem; font-weight: 600;">
                        <option value="RENYAH" selected>✅ Renyah (Lolos)</option>
                        <option value="ALOT">❌ Alot / Keras (Tolak)</option>
                        <option value="LEMBEK">⚠️ Kurang Kering / Lembek</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">PENAMPAKAN (Standar: Tidak Oilsoaked)</label>
                    <select name="items[${idx}][fryer_penampakan]" class="form-control" style="font-size: 0.85rem; font-weight: 600;">
                        <option value="TIDAK_OILSOAKED" selected>✅ Tidak Oilsoaked (Bagus)</option>
                        <option value="OILSOAKED">❌ Oilsoaked (Serap Minyak)</option>
                    </select>
                </div>
            </div>

            {{-- DEFECT FRYING (%) --}}
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.85rem;">
                <div style="font-weight: 800; font-size: 0.8rem; color: #b45309; margin-bottom: 0.5rem;">
                    DEFECT FRYING (%) CACAT PENGGORENGAN:
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 0.6rem; font-size: 0.775rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Breakage (Patah)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_breakage_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Cluster (Gumpal)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_cluster_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Foldover (Terlipat)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_foldover_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Oilsoaked Polos</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_oilsoaked_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.725rem;">Gambos / Kopong (%)</label>
                        <input type="number" step="0.1" name="items[${idx}][defect_gambos_persen]" value="0.0" class="form-control" style="font-size: 0.8rem;">
                    </div>
                </div>
            </div>
        `;
    }

    function onItemBarangChanged(idx) {
        renderFryerParamsForCard(idx);
        calculateCard(idx);
    }

    function removeItemCard(idx) {
        const card = document.getElementById(`item_card_${idx}`);
        if (card) {
            card.remove();
            const block = document.getElementById(`fryer_block_${idx}`);
            if (block) block.remove();
            calculateAll();
        }
    }

    function addItemRow() {
        createItemCard();
    }

    function calculateCard(idx) {
        const grossInput = document.getElementById(`gross_${idx}`);
        const refraksiInput = document.getElementById(`refraksi_${idx}`);
        const rejectInput = document.getElementById(`reject_${idx}`);
        const labelRefraksi = document.getElementById(`label_qty_refraksi_${idx}`);
        const labelNetto = document.getElementById(`label_netto_${idx}`);

        if (!grossInput) return;

        const gross = parseFloat(grossInput.value) || 0;
        const refraksiPersen = parseFloat(refraksiInput ? refraksiInput.value : 0) || 0;
        const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;

        const qtyRefraksi = (gross * (refraksiPersen / 100));
        const netto = Math.max(0, gross - qtyRefraksi - reject);

        if (labelRefraksi) labelRefraksi.innerText = `${qtyRefraksi.toFixed(2)} KG`;
        if (labelNetto) labelNetto.innerText = `${netto.toFixed(2)} KG`;

        calculateAll();
    }

    function calculateAll() {
        let totalGross = 0;
        let totalRefraksi = 0;
        let totalReject = 0;
        let totalNetto = 0;

        document.querySelectorAll('.qc-item-card').forEach(card => {
            const grossInput = card.querySelector('input[id^="gross_"]');
            const refraksiInput = card.querySelector('input[id^="refraksi_"]');
            const rejectInput = card.querySelector('input[id^="reject_"]');

            if (grossInput) {
                const gross = parseFloat(grossInput.value) || 0;
                const refraksiPersen = parseFloat(refraksiInput ? refraksiInput.value : 0) || 0;
                const reject = parseFloat(rejectInput ? rejectInput.value : 0) || 0;

                const qtyRefraksi = (gross * (refraksiPersen / 100));
                const netto = Math.max(0, gross - qtyRefraksi - reject);

                totalGross += gross;
                totalRefraksi += qtyRefraksi;
                totalReject += reject;
                totalNetto += netto;
            }
        });

        document.getElementById('summaryGross').innerText = `${totalGross.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
        document.getElementById('summaryRefraksi').innerText = `- ${totalRefraksi.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
        document.getElementById('summaryReject').innerText = `- ${totalReject.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
        document.getElementById('summaryNetto').innerText = `${totalNetto.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
    }

    function onPoSelected(select) {
        const poId = select.value;
        if (!poId || !PO_LIST[poId]) {
            return;
        }

        const po = PO_LIST[poId];
        if (po.supplier_id) {
            document.getElementById('supplierSelect').value = po.supplier_id;
        }
        if (po.gudang_id) {
            document.getElementById('gudangSelect').value = po.gudang_id;
        }

        if (po.items && po.items.length > 0) {
            document.getElementById('itemsContainer').innerHTML = '';
            document.getElementById('fryerParamsContainer').innerHTML = '';
            itemIndex = 0;
            po.items.forEach(it => {
                createItemCard({
                    podtl_id: it.podtl_id,
                    barang_id: it.barang_id,
                    gross: it.sisa_qty > 0 ? it.sisa_qty : it.pesan_qty,
                    kadar_air: '12.0',
                    refraksi_persen: '0.0',
                    reject: '0',
                });
            });
        }
    }

    // Inisialisasi awal saat load
    document.addEventListener('DOMContentLoaded', function () {
        const poSelect = document.getElementById('poSelect');
        if (poSelect && poSelect.value) {
            onPoSelected(poSelect);
        } else {
            createItemCard();
        }
    });
</script>
@endsection
