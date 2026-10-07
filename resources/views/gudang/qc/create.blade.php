@extends('layouts.qc-mobile')

@section('title', 'Form Uji QC Bahan Masuk (HACCP 7 Komoditas) - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-form.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR') || str_starts_with($cd, 'BB-OP') || str_starts_with($cd, 'BB-PY') || str_starts_with($cd, 'BB-')) {
            return 'SINGKONG';
        } elseif (str_contains($nm, 'MINYAK') || str_starts_with($cd, 'BP-MY')) {
            return 'MINYAK';
        } elseif (str_contains($nm, 'PLASTIK') || str_contains($nm, 'ROLL') || str_contains($nm, 'KEMASAN') || str_contains($nm, 'OPP') || str_contains($nm, 'PP')) {
            return 'PLASTIK';
        } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'DUS') || str_contains($nm, 'BOX')) {
            return 'KARTON';
        } elseif (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM') || str_contains($nm, 'MICIN') || str_contains($nm, 'GLUTAMAT')) {
            return 'MSG';
        } elseif (str_contains($nm, 'GARAM') || str_contains($nm, 'SEASALT') || str_contains($nm, 'SALT')) {
            return 'GARAM';
        } elseif (str_contains($nm, 'PERENYAH')) {
            return 'PERENYAH';
        } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'BALADO') || str_contains($nm, 'CHILLI') || str_contains($nm, 'SEASONING')) {
            return 'BUMBU';
        }
        return 'LAINNYA';
    };

    $getPoCommodities = function ($p) use ($classifyBarang) {
        $cats = [];
        foreach ($p->details as $d) {
            $cat = $classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd);
            $cats[] = $cat;
            if ($cat === 'BUMBU') {
                $cats[] = 'MSG';
                $cats[] = 'GARAM';
                $cats[] = 'PERENYAH';
            }
        }
        return array_values(array_unique($cats));
    };
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan --}}
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span id="badgeDocNo" style="display: none;"></span>
                </div>
                <h1 id="titleFormHaccp" style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    Sampling Mutu Singkong
                </h1>
                <p id="descFormHaccp" style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pencatatan sampling mutu kedatangan bahan baku di lapangan
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0284c7; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat Tiket
                </a>
            </div>
        </div>
    </div>

    {{-- SELECTOR 7 KOMODITAS --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-top: none; border-radius: 0 0 14px 14px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏷️</span> <span>PILIH JENIS BAHAN DATANG:</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(115px, 1fr)); gap: 0.45rem;" id="komoditasSelector">
            <button type="button" onclick="selectKomoditas('SINGKONG')" class="btn komoditas-btn active-komoditas" id="btnKomoditas_SINGKONG" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid #0284c7; background: #0284c7; color: #ffffff; text-align: center; cursor: pointer; transition: all 0.15s;">
                🥔 Singkong
            </button>
            <button type="button" onclick="selectKomoditas('MINYAK')" class="btn komoditas-btn" id="btnKomoditas_MINYAK" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; cursor: pointer; transition: all 0.15s;">
                🛢️ Minyak
            </button>
            <button type="button" onclick="selectKomoditas('PLASTIK')" class="btn komoditas-btn" id="btnKomoditas_PLASTIK" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; cursor: pointer; transition: all 0.15s;">
                🛍️ Plastik
            </button>
            <button type="button" onclick="selectKomoditas('KARTON')" class="btn komoditas-btn" id="btnKomoditas_KARTON" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; cursor: pointer; transition: all 0.15s;">
                📦 Karton
            </button>
            <button type="button" onclick="selectKomoditas('MSG')" class="btn komoditas-btn" id="btnKomoditas_MSG" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; cursor: pointer; transition: all 0.15s;">
                🧂 MSG
            </button>
            <button type="button" onclick="selectKomoditas('GARAM')" class="btn komoditas-btn" id="btnKomoditas_GARAM" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; cursor: pointer; transition: all 0.15s;">
                🧂 Garam
            </button>
            <button type="button" onclick="selectKomoditas('PERENYAH')" class="btn komoditas-btn" id="btnKomoditas_PERENYAH" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; cursor: pointer; transition: all 0.15s;">
                ✨ Perenyah
            </button>
        </div>
    </div>

    {{-- PILIH TAHAP PENGUJIAN QC SINGKONG (PENGUJIAN 1 ATAU PENGUJIAN 2) --}}
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
            <span>🔬 PILIH TAHAP PENGUJIAN:</span>
            <span id="labelTahapBadge" style="font-size: 0.725rem; color: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#7e22ce' : '#0284c7' }}; font-weight: 800; background: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#f3e8ff' : '#e0f2fe' }}; padding: 2px 8px; border-radius: 6px;">
                {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '🍟 Pengujian 2' : '🚛 Pengujian 1' }}
            </span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <button type="button" onclick="selectTahapUji('PENGUJIAN_1')" class="btn" id="btnTahap_1" style="font-size: 0.825rem; font-weight: 800; padding: 0.65rem 0.5rem; border-radius: 8px; border: 2px solid {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? '#0284c7' : '#cbd5e1' }}; background: {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? '#0284c7' : '#ffffff' }}; color: {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? '#ffffff' : '#334155' }}; text-align: center; cursor: pointer; transition: all 0.15s;">
                🚛 PENGUJIAN 1 (Awal)
            </button>
            <button type="button" onclick="selectTahapUji('PENGUJIAN_2')" class="btn" id="btnTahap_2" style="font-size: 0.825rem; font-weight: 800; padding: 0.65rem 0.5rem; border-radius: 8px; border: 2px solid {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#9333ea' : '#cbd5e1' }}; background: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#9333ea' : '#ffffff' }}; color: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#ffffff' : '#334155' }}; text-align: center; cursor: pointer; transition: all 0.15s;">
                🍟 PENGUJIAN 2 (Lanjutan)
            </button>
        </div>
        <div id="descTahapUji" style="font-size: 0.75rem; color: #64748b; margin-top: 0.45rem;">
            @if (($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2')
                🍟 <strong>Pengujian 2:</strong> Pengujian lanjutan untuk kedatangan singkong yang sama (parameter fisik &amp; uji goreng tetap diuji lengkap).
            @else
                🚛 <strong>Pengujian 1:</strong> Pengujian awal saat truk singkong tiba di pos penerimaan (parameter fisik &amp; uji goreng diuji lengkap).
            @endif
        </div>

        {{-- AUTO-FILL DARI KEDATANGAN PENGUJIAN 1 (KHUSUS PENGUJIAN 2) --}}
        <div id="wrapPendingP1" style="display: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'block' : 'none' }}; margin-top: 0.75rem; padding-top: 0.65rem; border-top: 1px dashed #cbd5e1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.3rem;">
                <label style="font-size: 0.75rem; font-weight: 800; color: #0f172a; margin: 0;">
                    🚚 Pilih Truk Kedatangan Hari Ini (Otomatis Isi Data Armada &amp; PO):
                </label>
                <span style="font-size: 0.7rem; font-weight: 700; color: #7e22ce; background: #f3e8ff; padding: 2px 7px; border-radius: 4px;">
                    {{ isset($pendingPengujian1) ? $pendingPengujian1->count() : 0 }} Truk Siap Uji 2
                </span>
            </div>
            <select id="selectPendingP1" class="form-control" style="font-size: 0.8rem; font-weight: 600;" onchange="onSelectPendingArrival(this)">
                <option value="">-- Input Bebas (Bukan dari Kedatangan Sebelumnya) --</option>
                @if(isset($pendingPengujian1))
                    @foreach($pendingPengujian1 as $p1)
                        @php
                            $firstDtl = $p1->details->first();
                            $p1Data = [
                                'qc_id'        => $p1->qc_id,
                                'qc_no'        => $p1->qc_no,
                                'po_id'        => $p1->po_id,
                                'supplier_id'  => $p1->supplier_id,
                                'gudang_id'    => $p1->gudang_id,
                                'plat'         => $p1->plat_nomor_truk,
                                'sopir'        => $p1->sopir_nama,
                                'sj'           => $p1->surat_jalan_supplier,
                                'do'           => $p1->nomor_do,
                                'batch_no'     => $p1->batch_no,
                                'lokasi_panen' => $p1->lokasi_panen,
                                'umur_singkong'=> $p1->umur_singkong_bln,
                                'tgl_panen'    => $p1->tgl_panen ? $p1->tgl_panen->format('Y-m-d') : null,
                                'nama_jenis'   => $p1->nama_jenis,
                                'sj_qty'       => (float) $p1->jumlah_surat_jalan,
                                'pabrik_qty'   => (float) $p1->jumlah_di_pabrik,
                                'barang_id'    => $firstDtl?->barang_id,
                                'grade_cd'     => $firstDtl?->grade_cd ?? 'A',
                                'gross'        => (float) ($firstDtl?->qty_timbang_gross ?? $p1->jumlah_di_pabrik),
                                'refraksi'     => (float) ($firstDtl?->refraksi_persen ?? 0),
                            ];
                        @endphp
                        <option value="{{ $p1->qc_id }}"
                            data-json="{{ json_encode($p1Data) }}"
                            {{ ($parentQc && $parentQc->qc_id == $p1->qc_id) ? 'selected' : '' }}>
                            #{{ $p1->qc_no }} &bull; {{ $p1->supplier?->supplier_nm }} &bull; 🚛 {{ $p1->plat_nomor_truk ?: 'Plat -' }} ({{ $p1->po ? 'PO: ' . $p1->po->po_no : 'Non-PO' }})
                        </option>
                    @endforeach
                @endif
            </select>

            <div id="bannerSelectedP1" style="display: {{ $parentQc ? 'flex' : 'none' }}; align-items: center; justify-content: space-between; gap: 0.5rem; background: #faf5ff; border: 1.5px solid #d8b4fe; border-radius: 8px; padding: 0.65rem 0.85rem; margin-top: 0.65rem;">
                <div style="font-size: 0.775rem; color: #6b21a8; font-weight: 700;">
                    <span>🔗 Terhubung Kedatangan Pengujian 1: </span>
                    <span id="labelSelectedP1Desc" style="color: #581c87; font-weight: 800;">
                        @if($parentQc)
                            #{{ $parentQc->qc_no }} &bull; {{ $parentQc->supplier?->supplier_nm }} &bull; 🚛 {{ $parentQc->plat_nomor_truk ?: 'Plat -' }}
                        @endif
                    </span>
                </div>
                <button type="button" onclick="clearSelectedPendingArrival()" style="background: none; border: none; color: #a855f7; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                    ✕ Lepas
                </button>
            </div>
        </div>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error" style="margin-top: 0.5rem; margin-bottom: 1rem; border-radius: 10px;">
            <div style="font-weight: 700; margin-bottom: 0.35rem;">⚠️ Harap periksa isian formulir:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STEP / TAB SWITCHER FOR MOBILE --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchQcTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #0284c7; background: #0284c7; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchQcTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            📏 2. Fisik &amp; Diameter
        </button>
        <button type="button" class="btn" id="tabBtn3" onclick="switchQcTab(3)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🍟 3. Uji Rasa &amp; Keputusan
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="{{ old('kategori_barang', 'SINGKONG') }}">
        <input type="hidden" name="status_uji_goreng" id="statusUjiGorengInput" value="SELESAI">
        <input type="hidden" name="tahap_uji" id="tahapUjiInput" value="{{ old('tahap_uji', $defaultTahap ?? 'PENGUJIAN_1') }}">
        <input type="hidden" name="parent_qc_id" id="parentQcIdInput" value="{{ old('parent_qc_id', $parentQc?->qc_id) }}">
        <input type="hidden" name="batch_no" id="batchNoInput" value="{{ old('batch_no', $parentQc?->batch_no) }}">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" class="card" style="border-radius: 12px; border-top: 5px solid #0284c7; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 1: Laporan Kedatangan &amp; Standar Halal / Transportasi</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Verifikasi surat jalan, nomor DO, produsen &amp; jaminan bebas barang haram/najis</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="switchQcTab(2)" style="border-radius: 8px; font-weight: 700; padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0;" title="Lanjut ke Pemeriksaan Parameter (Tahap 2)">
                    <span>Lanjut: Parameter</span> &rarr;
                </button>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.1rem;">
                {{-- PILIH DARI PO AKTIF DENGAN SMART FILTER --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                        <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                            📄 Referensi Purchase Order (PO) <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">(Opsional - auto-fill data)</span>
                        </label>
                        <span id="poFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">
                            Filter: 🥔 Singkong &amp; Bahan Baku
                        </span>
                    </div>

                    {{-- FILTER CHIPS & QUICK SEARCH UNTUK PO --}}
                    <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                        <div class="supplier-filter-chips" id="poFilterChips">
                            <button type="button" class="btn-supplier-chip active-chip" id="chipPo_AUTO" onclick="setPoCategoryFilter('AUTO')">
                                ✨ Sesuai Komoditas
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_SINGKONG" onclick="setPoCategoryFilter('SINGKONG')">
                                🥔 Singkong &amp; Bahan Baku
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_MINYAK" onclick="setPoCategoryFilter('MINYAK')">
                                🛢️ Minyak
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_KEMASAN" onclick="setPoCategoryFilter('KEMASAN')">
                                📦 Kemasan (Plastik/Dus)
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_BP" onclick="setPoCategoryFilter('BP')">
                                🧂 Bahan Penolong
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_ALL" onclick="setPoCategoryFilter('ALL')">
                                🌐 Semua PO ({{ $pos->count() }})
                            </button>
                        </div>
                        <div class="supplier-search-wrap">
                            <input type="text" id="poSearchInput" class="form-control supplier-search-input" placeholder="🔍 Cari nomor PO, nama rekanan, atau item barang..." oninput="onSearchPo(this.value)">
                            <button type="button" id="btnClearPoSearch" class="supplier-search-clear" onclick="clearPoSearch()" style="display: none;">✕</button>
                        </div>
                    </div>

                    <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 700;">
                        <option value="">-- Tanpa PO / Kiriman Langsung --</option>
                        @foreach ($pos as $p)
                            @php
                                $poCats = $getPoCommodities($p);
                                $itemNames = $p->details->map(fn($d) => $d->barang?->barang_nm)->filter()->unique()->implode(', ');
                                $totalSisa = $p->details->sum(fn($d) => max(0, (float)$d->pesan_qty - (float)$d->terima_qty));
                            @endphp
                            <option value="{{ $p->po_id }}"
                                    data-komoditas="{{ implode(',', $poCats) }}"
                                    data-supplier-id="{{ $p->supplier_id }}"
                                    data-gudang-id="{{ $p->gudang_id }}"
                                    data-search="{{ strtolower($p->po_no . ' ' . ($p->supplier?->supplier_nm ?? '') . ' ' . $itemNames) }}"
                                    {{ old('po_id', $selectedPo?->po_id) == $p->po_id ? 'selected' : '' }}>
                                {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} ({{ $itemNames ?: 'Item PO' }} - Sisa: {{ number_format($totalSisa, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- NAMA PRODUSEN, SUPPLIER, GUDANG, NEGARA --}}
                {{-- MITRA SUPPLIER / PRODUSEN, GUDANG, NEGARA --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    {{-- DROPDOWN MITRA SUPPLIER / PRODUSEN DENGAN FILTER CEPAT --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                            <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                                🏢 Mitra Supplier / Produsen <span style="color:#ef4444;">*</span>
                            </label>
                            <span id="supplierFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">
                                Filter: Singkong
                            </span>
                        </div>

                        {{-- FILTER CHIPS & QUICK SEARCH --}}
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                            <div class="supplier-filter-chips" id="supplierFilterChips">
                                <button type="button" class="btn-supplier-chip active-chip" id="chipSupplier_AUTO" onclick="setSupplierCategoryFilter('AUTO')">
                                    ✨ Sesuai Komoditas
                                </button>
                                <button type="button" class="btn-supplier-chip" id="chipSupplier_RAW" onclick="setSupplierCategoryFilter('RAW')">
                                    🌾 Petani Singkong (35)
                                </button>
                                <button type="button" class="btn-supplier-chip" id="chipSupplier_VENDOR" onclick="setSupplierCategoryFilter('VENDOR')">
                                    🏭 Vendor Industri (22)
                                </button>
                                <button type="button" class="btn-supplier-chip" id="chipSupplier_ALL" onclick="setSupplierCategoryFilter('ALL')">
                                    🌐 Semua (57)
                                </button>
                            </div>
                            <div class="supplier-search-wrap">
                                <input type="text" id="supplierSearchInput" class="form-control supplier-search-input" placeholder="🔍 Cari nama supplier / petani..." oninput="onSearchSupplier(this.value)">
                                <button type="button" id="btnClearSupplierSearch" class="supplier-search-clear" onclick="clearSupplierSearch()" style="display: none;">✕</button>
                            </div>
                        </div>

                        {{-- SELECT MITRA SUPPLIER / PRODUSEN --}}
                        <select name="supplier_id" id="supplierSelect" class="form-control" required onchange="onSupplierSelected(this)" style="font-weight: 700;">
                            <option value="">-- Pilih Supplier / Produsen --</option>
                            @foreach ($suppliers as $s)
                                @php
                                    $jenisCd = $s->jenisSupplier?->jenis_supplier_cd ?: (str_starts_with($s->supplier_cd, 'SKG-') ? 'RAW' : 'BP');
                                    $isPetani = ($jenisCd === 'RAW' || str_starts_with($s->supplier_cd, 'SKG-'));
                                    $tipeLabel = $isPetani ? 'Petani Singkong' : ($s->jenisSupplier?->jenis_supplier_nm ?? 'Vendor');
                                @endphp
                                <option value="{{ $s->supplier_id }}"
                                        data-jenis-cd="{{ $jenisCd }}"
                                        data-is-petani="{{ $isPetani ? '1' : '0' }}"
                                        data-supplier-cd="{{ $s->supplier_cd }}"
                                        data-supplier-nm="{{ $s->supplier_nm }}"
                                        {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                    {{ $s->supplier_nm }} ({{ $tipeLabel }})
                                </option>
                            @endforeach
                        </select>
                        {{-- Hidden input nama_produsen agar tetap tersimpan otomatis ke database --}}
                        <input type="hidden" name="nama_produsen" id="namaProdusenInput" value="{{ old('nama_produsen') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="form-label" style="font-weight: 700; margin: 0;">🏢 Perusahaan / Cabang Tujuan Bongkar <span style="color:#ef4444;">*</span></label>
                            @if ($gudangs->count() === 1)
                                <span style="font-size: 0.7rem; font-weight: 700; color: #059669; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">
                                    Akses: 1 Perusahaan
                                </span>
                            @else
                                <span style="font-size: 0.7rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">
                                    Akses: {{ $gudangs->count() }} Perusahaan
                                </span>
                            @endif
                        </div>
                        <select name="gudang_id" id="gudangSelect" class="form-control" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? auth()->user()?->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->display_name ?? $g->gudang_nm }} @if(!empty($g->tipe_gudang_cd) && $g->tipe_gudang_cd !== 'Pusat') ({{ $g->tipe_gudang_cd }}) @endif
                                </option>
                            @endforeach
                        </select>

                        <div style="margin-top: 0.85rem;">
                            <label class="form-label" style="font-weight: 700;">Negara Produsen</label>
                            <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}">
                        </div>
                    </div>
                </div>

                {{-- NAMA JENIS & SPESIFIK SINGKONG / KOMODITAS LAIN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;" id="labelNamaJenis">Nama Jenis / Spesifikasi</label>
                        <input type="text" name="nama_jenis" id="namaJenisInput" class="form-control" placeholder="Contoh: Curah Sawit / PP 08 / Master Box / MSG Miku" value="{{ old('nama_jenis') }}">
                    </div>

                    <div class="form-group" id="groupLokasiPanen" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Lokasi Panen</label>
                        <input type="text" name="lokasi_panen" class="form-control" placeholder="Contoh: Wonosobo / Kebumen" value="{{ old('lokasi_panen') }}">
                    </div>

                    <div class="form-group" id="groupUmurSingkong" style="margin-bottom: 0;">
                        <label class="form-label">Umur Singkong (Bulan)</label>
                        <input type="number" step="0.5" name="umur_singkong_bln" class="form-control" placeholder="Contoh: 9.0" value="{{ old('umur_singkong_bln', 9.0) }}">
                    </div>

                    <div class="form-group" id="groupTglPanen" style="margin-bottom: 0;">
                        <label class="form-label">Tanggal Panen</label>
                        <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', date('Y-m-d', strtotime('-1 day'))) }}">
                    </div>
                </div>

                {{-- NOMOR DOKUMEN: SURAT JALAN & NOMOR DO --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">No. Surat Jalan</label>
                        <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="No Surat Jalan..." value="{{ old('surat_jalan_supplier') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nomor DO (Delivery Order)</label>
                        <input type="text" name="nomor_do" class="form-control" placeholder="No DO pengiriman..." value="{{ old('nomor_do') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Waktu Kedatangan</label>
                        <input type="datetime-local" name="tgl_periksa" class="form-control" value="{{ old('tgl_periksa', now()->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                {{-- KUANTITAS KEDATANGAN: SURAT JALAN, PABRIK, SAMPLE --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Jumlah di Surat Jalan</label>
                        <input type="number" step="0.01" min="0" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="0.00" value="{{ old('jumlah_surat_jalan') }}" oninput="syncQuantityFields()">
                        <span style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            Total muatan seluruh truk pada Surat Jalan (contoh: <strong>7.000 KG</strong>).
                        </span>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;" id="labelJumlahPabrik">Jumlah di Pabrik (Muatan Uji Ini)</label>
                        <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="0.00" value="{{ old('jumlah_di_pabrik') }}" oninput="syncQuantityFields()">
                        <span id="hintJumlahPabrik" style="font-size: 0.72rem; color: #0284c7; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            Pengujian 1: Muatan setengah bak pertama yang turun (contoh: <strong>3.000 KG</strong>).
                        </span>
                    </div>

                    {{-- Dynamic Sample Input berdasarkan komoditas --}}
                    <div class="form-group" id="groupSampleKg" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">
                            ⚖️ Berat Sampel Uji (KG)
                        </label>
                        <input type="number" step="0.1" min="0.1" name="jumlah_sample_kg" id="inputJumlahSampleKg" class="form-control" placeholder="7.0" value="{{ old('jumlah_sample_kg', 7.0) }}" style="font-weight: 700;">
                        <span style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            Format standar <strong>7.0 KG</strong> (gabungan cuplikan bak belakang, tengah, depan). Nilai dapat diubah jika berbeda.
                        </span>
                    </div>

                    <div class="form-group" id="groupSampleGr" style="display: none; margin-bottom: 0;">
                        <label class="form-label">Jumlah Sample (gr)</label>
                        <input type="number" step="1" name="jumlah_sample_gr" class="form-control" placeholder="Contoh: 250" value="{{ old('jumlah_sample_gr', 250) }}">
                    </div>

                    <div class="form-group" id="groupSamplePcs" style="display: none; margin-bottom: 0;">
                        <label class="form-label">Jumlah Sample (pcs)</label>
                        <input type="number" step="1" name="jumlah_sample_pcs" class="form-control" placeholder="Contoh: 50" value="{{ old('jumlah_sample_pcs', 50) }}">
                    </div>
                </div>

                {{-- ARMADA: PLAT TRUK & SOPIR --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Plat Nomor Truk / Kendaraan</label>
                        <input type="text" name="plat_nomor_truk" class="form-control" placeholder="AA 1234 XY" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase; font-weight: 700;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nama Pengemudi / Sopir</label>
                        <input type="text" name="sopir_nama" class="form-control" placeholder="Nama sopir pengantar" value="{{ old('sopir_nama') }}">
                    </div>
                </div>

                {{-- ========================================================================= --}}
                {{-- STANDAR AUDIT TRANSPORTASI & 4 PERTANYAAN JAMINAN HALAL (FORM RESMI HACCP) --}}
                {{-- ========================================================================= --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.15rem; margin-top: 0.5rem;">
                    <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.45rem;">
                        <span style="display: flex; align-items: center; gap: 0.4rem;">
                            <span>🛡️</span> <span>Standar Kebersihan Transportasi &amp; Audit Jaminan Halal</span>
                        </span>
                        <span style="font-size: 0.725rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 0.15rem 0.5rem; border-radius: 12px;">
                            Wajib Terpenuhi (HACCP / Halal MFI)
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        {{-- 1. KONDISI TRANSPORTASI --}}
                        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 1rem; align-items: center;">
                            <div>
                                <span style="font-weight: 800; font-size: 0.825rem; color: #1e293b; text-transform: uppercase;">
                                    Kondisi Transportasi :
                                </span>
                            </div>
                            <div style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap;">
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

                        {{-- 2. DIANGKUT BERSAMA BARANG HARAM --}}
                        <div style="display: grid; grid-template-columns: 280px 1fr 220px; gap: 0.75rem; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 0.65rem;">
                            <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                Apakah barang tersebut diangkut bersama dengan barang haram?
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="angkut_barang_haram_st" value="0" checked>
                                    <span style="color: #15803d; font-weight: 700;">Tidak</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                    <input type="radio" name="angkut_barang_haram_st" value="1">
                                    <span style="color: #dc2626; font-weight: 700;">Ya</span>
                                </label>
                            </div>
                            <div>
                                <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar..." style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                            </div>
                        </div>

                        {{-- 3, 4, 5: AUDIT HALAL SERTIFIKASI (KHUSUS KOMODITAS OLAHAN / NON-SINGKONG SESUAI DOKUMEN RESMI HACCP) --}}
                        <div id="auditHalalNonSingkong" style="display: none; flex-direction: column; gap: 1rem;">
                            {{-- 3. TERDAFTAR & DISETUJUI LPPOM MUI / BPJPH --}}
                            <div style="display: grid; grid-template-columns: 280px 1fr 220px; gap: 0.75rem; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 0.65rem;">
                                <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                    Apakah bahan tersebut terdaftar &amp; disetujui oleh LPPOM MUI/BPJPH?
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="radio" name="terdaftar_lppom_st" value="1" checked>
                                        <span style="color: #15803d; font-weight: 700;">Ya</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="radio" name="terdaftar_lppom_st" value="0">
                                        <span style="color: #dc2626; font-weight: 700;">Tidak</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="komentar_lppom" class="form-control" placeholder="Komentar..." style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                                </div>
                            </div>

                            {{-- 4. MEMPUNYAI SERTIFIKAT HALAL --}}
                            <div style="display: grid; grid-template-columns: 280px 1fr 220px; gap: 0.75rem; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 0.65rem;">
                                <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                    Apakah barang tersebut mempunyai sertifikat halal?
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="radio" name="ada_sertifikat_halal_st" value="1" checked>
                                        <span style="color: #15803d; font-weight: 700;">Ya</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="radio" name="ada_sertifikat_halal_st" value="0">
                                        <span style="color: #dc2626; font-weight: 700;">Tidak</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="komentar_sertifikat" class="form-control" placeholder="Komentar..." style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                                </div>
                            </div>

                            {{-- 5. SERTIFIKAT HALAL MASIH BERLAKU --}}
                            <div style="display: grid; grid-template-columns: 280px 1fr 220px; gap: 0.75rem; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 0.65rem;">
                                <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                    Apakah sertifikat halal barang tersebut masih berlaku?
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="radio" name="sertifikat_halal_berlaku_st" value="1" checked>
                                        <span style="color: #15803d; font-weight: 700;">Ya</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                                        <input type="radio" name="sertifikat_halal_berlaku_st" value="0">
                                        <span style="color: #dc2626; font-weight: 700;">Tidak</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar..." style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Tombol Lanjut ke Tahap 2 --}}
                <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="button" class="btn btn-primary" onclick="switchQcTab(2)" style="border-radius: 8px; font-weight: 700; padding: 0.6rem 1.25rem;">
                        Lanjut ke Pemeriksaan Parameter &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN PARAMETER & MUTU SESUAI KOMODITAS                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #10b981; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 id="section2Title" style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 2: Pengujian I &bull; Sampling Fisik &amp; Parameter</h2>
                    <span id="section2Sub" style="font-size: 0.8rem; color: #64748b;">Standar mutu bahan baku, cacat fisik, dan timbangan kedatangan</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="switchQcTab(1)" style="border-radius: 8px; padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                        &larr; Tahap 1
                    </button>
                    <button type="button" id="btnAddItemRow" class="btn btn-sm btn-secondary" onclick="addItemRow()" style="border-radius: 8px; padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                        + Tambah Item
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnTopNextFryer" onclick="switchQcTab(3)" style="border-radius: 8px; font-weight: 700; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        Lanjut: Fryer &rarr;
                    </button>
                    <button type="button" class="btn btn-sm" id="btnTopSimpanCepat" onclick="submitNonSingkong()" style="display: none; border-radius: 8px; font-weight: 800; background: #059669; color: #ffffff; border: none; padding: 0.4rem 0.85rem; font-size: 0.8rem; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">
                        💾 Simpan
                    </button>
                </div>
            </div>

            {{-- WIDGET INFORMASI STOK GUDANG SINGKONG GRADE A & GRADE B --}}
            <div id="singkongStockWidget" style="margin: 1rem 1.25rem 0 1.25rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem 1.15rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; flex-wrap: wrap; gap: 0.4rem;">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🏢</span> <span>STATUS STOK GUDANG SINGKONG SAAT INI (REAL-TIME):</span>
                    </div>
                    <span style="font-size: 0.7rem; color: #0284c7; font-weight: 700; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
                        Data Riil Gudang
                    </span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                    <div style="background: #ffffff; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <span style="font-size: 0.725rem; font-weight: 800; color: #166534; text-transform: uppercase;">🟢 Singkong Grade A</span>
                            <div style="font-size: 1.2rem; font-weight: 900; color: #15803d;">
                                {{ number_format($stokSingkongA ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #166534;">KG</span>
                            </div>
                        </div>
                        <span style="font-size: 0.7rem; font-weight: 700; color: #15803d; background: #dcfce7; padding: 3px 8px; border-radius: 6px;">Prioritas Produksi</span>
                    </div>
                    <div style="background: #ffffff; border: 1.5px solid #fde047; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <span style="font-size: 0.725rem; font-weight: 800; color: #854d0e; text-transform: uppercase;">🟡 Singkong Grade B</span>
                            <div style="font-size: 1.2rem; font-weight: 900; color: #b45309;" id="widgetStokGradeB">
                                {{ number_format($stokSingkongB ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #854d0e;">KG</span>
                            </div>
                        </div>
                        <span style="font-size: 0.7rem; font-weight: 700; color: {{ ($stokSingkongB ?? 0) > 1000 ? '#b91c1c' : '#854d0e' }}; background: {{ ($stokSingkongB ?? 0) > 1000 ? '#fee2e2' : '#fef3c7' }}; padding: 3px 8px; border-radius: 6px;">
                            {{ ($stokSingkongB ?? 0) > 1000 ? '⚠️ Stok Tinggi' : 'Stok Aman' }}
                        </span>
                    </div>
                </div>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.45rem; line-height: 1.35;">
                    💡 <em>Pedoman QC: Jika hasil fisik kedatangan tergolong <strong>Grade B</strong>, periksa stok Grade B di atas. Jika stok Grade B sudah menumpuk / kapasitas penuh, atasan menyarankan kedatangan ditolak.</em>
                </div>
            </div>

            {{-- 1. FORM KHUSUS SINGKONG (MULTIPLE ITEMS / DYNAMIC CARDS) --}}
            <div id="singkongContainer" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Item Cards rendered by JS --}}
            </div>

            {{-- 2. FORM KHUSUS MINYAK GORENG (SESUAI MFI/HACCP-04/FRM-03/029/VIII/2021) --}}
            <div id="minyakContainer" style="display: none; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;">Komoditas Minyak Goreng <span style="color:red;">*</span></label>
                    <select name="minyak_barang_id" id="minyakBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
                        @foreach ($barangs as $b)
                            @if (stripos($b->barang_nm, 'minyak') !== false)
                                <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'KG' }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="minyak_status_raw_material" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="minyak_status_raw_material" value="TDK_STD"> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KONDISI WADAH :</span>
                            <div style="display: flex; gap: 0.75rem; font-size: 0.8rem; font-weight: 700;">
                                <label style="cursor: pointer;"><input type="radio" name="minyak_tipe_wadah" value="TANGKI" checked> TANGKI</label>
                                <label style="cursor: pointer;"><input type="radio" name="minyak_tipe_wadah" value="JERIGEN"> JERIGEN</label>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="minyak_kondisi_wadah" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="minyak_kondisi_wadah" value="TIDAK_STANDARD"> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
                        🔬 HASIL PEMERIKSAAN ASAM LEMAK BEBAS (FFA) &amp; FISIK
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: center;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">FFA DI COA (Pabrik Produsen)</label>
                            <input type="number" step="0.001" min="0" max="100" name="minyak_ffa_coa" class="form-control" placeholder="Contoh: 0.080" value="{{ old('minyak_ffa_coa') }}">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #0284c7;">FFA CEK QC MIRASA (Lab)</label>
                            <input type="number" step="0.001" min="0" max="100" name="minyak_ffa_qc" class="form-control" placeholder="Contoh: 0.085" value="{{ old('minyak_ffa_qc') }}" style="font-weight: 800; color: #0f172a;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed #cbd5e1; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.45rem; font-weight: 700; color: #15803d; cursor: pointer;">
                            <input type="checkbox" name="minyak_jernih_st" value="1" checked> MINYAK JERNIH
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.45rem; font-weight: 700; color: #15803d; cursor: pointer;">
                            <input type="checkbox" name="minyak_tangki_bersih_st" value="1" checked> TANGKI BAGIAN DALAM BERSIH
                        </label>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚖️ KUANTITAS &amp; HASIL PENIMBANGAN
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">Timbangan Netto (KG)</label>
                            <input type="number" step="0.01" min="0" name="minyak_qty_gross" id="minyakQtyGross" class="form-control" placeholder="0.00" value="{{ old('minyak_qty_gross') }}">
                        </div>
                        <div>
                            <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Tolak (KG)</label>
                            <input type="number" step="0.01" min="0" name="minyak_qty_reject" id="minyakQtyReject" class="form-control" placeholder="0.00" value="{{ old('minyak_qty_reject', 0) }}">
                        </div>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="minyak_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji minyak goreng...">{{ old('minyak_komentar') }}</textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
                        <div style="display: flex; gap: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                                <input type="radio" name="minyak_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="minyak_kesimpulan" value="TOLAK"> ❌ TOLAK
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. FORM KHUSUS PLASTIK (SESUAI MFI/HACCP-04/FRM-03/030/VIII/2021) --}}
            <div id="plastikContainer" style="display: none; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;">Komoditas Plastik Kemasan <span style="color:red;">*</span></label>
                    <select name="plastik_barang_id" id="plastikBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
                        @foreach ($barangs as $b)
                            @if (stripos($b->barang_nm, 'plastik') !== false || stripos($b->barang_nm, 'kemasan') !== false || stripos($b->barang_nm, 'opp') !== false || stripos($b->barang_nm, 'pp') !== false || stripos($b->barang_nm, 'roll') !== false)
                                <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="plastik_status_raw_material" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="plastik_status_raw_material" value="TDK_STD"> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="plastik_kemasan_kondisi" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="plastik_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚠️ KONDISI KEMASAN (Centang jika ditemukan cacat):
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="plastik_kemasan_kotor" value="1"> KOTOR
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="plastik_kemasan_apek" value="1"> APEK
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="plastik_kemasan_basah" value="1"> BASAH
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                            <input type="checkbox" name="plastik_kemasan_sobek" value="1"> SOBEK
                        </label>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
                        🔬 ANALISA PARAMETER KETEBALAN &amp; KEUTUHAN
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 1.5px solid #cbd5e1;">
                                <th style="padding: 0.5rem; text-align: left; width: 140px;">Parameter</th>
                                <th style="padding: 0.5rem; text-align: left;">Hasil Analisa</th>
                                <th style="padding: 0.5rem; text-align: left; width: 220px;">Standard</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.5rem; font-weight: 700;">KETEBALAN</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_ketebalan_analisa" class="form-control" placeholder="Contoh: 0.08 mm" value="{{ old('plastik_ketebalan_analisa') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_ketebalan_standar" class="form-control" placeholder="Contoh: 0.08 mm" value="{{ old('plastik_ketebalan_standar', '0.08 mm') }}">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem; font-weight: 700;">KEUTUHAN</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_keutuhan_analisa" class="form-control" placeholder="Contoh: Tidak Sobek" value="{{ old('plastik_keutuhan_analisa', 'Tidak Sobek') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="plastik_keutuhan_standar" class="form-control" value="{{ old('plastik_keutuhan_standar', 'Tidak Sobek') }}" readonly style="background: #f8fafc;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚖️ KUANTITAS DITERIMA DI PABRIK
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Lolos (Pcs / Unit)</label>
                            <input type="number" step="1" min="0" name="plastik_qty_gross" id="plastikQtyGross" class="form-control" placeholder="0" value="{{ old('plastik_qty_gross') }}">
                        </div>
                        <div>
                            <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Cacat (Pcs)</label>
                            <input type="number" step="1" min="0" name="plastik_qty_reject" id="plastikQtyReject" class="form-control" placeholder="0" value="{{ old('plastik_qty_reject', 0) }}">
                        </div>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="plastik_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji plastik kemasan...">{{ old('plastik_komentar') }}</textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
                        <div style="display: flex; gap: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                                <input type="radio" name="plastik_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="plastik_kesimpulan" value="TOLAK"> ❌ TOLAK
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. FORM KHUSUS KARTON (SESUAI MFI/HACCP-04/FRM-03/031/VIII/2021) --}}
            <div id="kartonContainer" style="display: none; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;">Komoditas Karton Box <span style="color:red;">*</span></label>
                    <select name="karton_barang_id" id="kartonBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
                        @foreach ($barangs as $b)
                            @if (stripos($b->barang_nm, 'karton') !== false || stripos($b->barang_nm, 'dus') !== false || stripos($b->barang_nm, 'box') !== false)
                                <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="karton_status_raw_material" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="karton_status_raw_material" value="TDK_STD"> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="karton_kemasan_kondisi" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="karton_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚠️ KONDISI KEMASAN (Centang jika ditemukan cacat):
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="karton_kemasan_kotor" value="1"> KOTOR
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="karton_kemasan_apek" value="1"> APEK
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="karton_kemasan_basah" value="1"> BASAH
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #b45309; font-weight: 700;">
                            <input type="checkbox" name="karton_kemasan_jamur" value="1"> JAMUR
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                            <input type="checkbox" name="karton_kemasan_sobek" value="1"> SOBEK
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="karton_kemasan_berminyak" value="1"> BERMINYAK
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="karton_kemasan_berdebu" value="1"> BERDEBU
                        </label>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
                        🔬 ANALISA PARAMETER DIMENSI &amp; SPESIFIKASI KARTON
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 1.5px solid #cbd5e1;">
                                <th style="padding: 0.5rem; text-align: left; width: 140px;">Parameter</th>
                                <th style="padding: 0.5rem; text-align: left;">Hasil Analisa</th>
                                <th style="padding: 0.5rem; text-align: left; width: 220px;">Standard</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.5rem; font-weight: 700;">PANJANG</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_dimensi_panjang_analisa" class="form-control" placeholder="Contoh: 45 cm" value="{{ old('karton_dimensi_panjang_analisa') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_dimensi_panjang_standar" class="form-control" placeholder="Contoh: 45 cm" value="{{ old('karton_dimensi_panjang_standar') }}">
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.5rem; font-weight: 700;">LEBAR</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_dimensi_lebar_analisa" class="form-control" placeholder="Contoh: 30 cm" value="{{ old('karton_dimensi_lebar_analisa') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_dimensi_lebar_standar" class="form-control" placeholder="Contoh: 30 cm" value="{{ old('karton_dimensi_lebar_standar') }}">
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.5rem; font-weight: 700;">TINGGI</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_dimensi_tinggi_analisa" class="form-control" placeholder="Contoh: 25 cm" value="{{ old('karton_dimensi_tinggi_analisa') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_dimensi_tinggi_standar" class="form-control" placeholder="Contoh: 25 cm" value="{{ old('karton_dimensi_tinggi_standar') }}">
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem; font-weight: 700;">SPESIFIKASI</td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_spesifikasi_analisa" class="form-control" placeholder="Contoh: K125/M125/K125 B/F" value="{{ old('karton_spesifikasi_analisa') }}">
                                </td>
                                <td style="padding: 0.5rem;">
                                    <input type="text" name="karton_spesifikasi_standar" class="form-control" placeholder="Contoh: K125/M125/K125 B/F" value="{{ old('karton_spesifikasi_standar') }}">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚖️ KUANTITAS DITERIMA DI PABRIK
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Lolos (Pcs / Lembar)</label>
                            <input type="number" step="1" min="0" name="karton_qty_gross" id="kartonQtyGross" class="form-control" placeholder="0" value="{{ old('karton_qty_gross') }}">
                        </div>
                        <div>
                            <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Rusak (Pcs)</label>
                            <input type="number" step="1" min="0" name="karton_qty_reject" id="kartonQtyReject" class="form-control" placeholder="0" value="{{ old('karton_qty_reject', 0) }}">
                        </div>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="karton_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji karton box...">{{ old('karton_komentar') }}</textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
                        <div style="display: flex; gap: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                                <input type="radio" name="karton_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="karton_kesimpulan" value="TOLAK"> ❌ TOLAK
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. FORM KHUSUS BAHAN PENOLONG: MSG, GARAM, PERENYAH --}}
            <div id="bahanPenolongContainer" style="display: none; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;" id="labelBahanPenolongItem">Nama Bahan Penolong <span style="color:red;">*</span></label>
                    <select name="bp_barang_id" id="bpBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
                        @foreach ($barangs as $b)
                            @php
                                $nm = strtoupper($b->barang_nm);
                                $katItem = null;
                                if (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM') || str_contains($nm, 'PENYEDAP')) {
                                    $katItem = 'MSG';
                                } elseif (str_contains($nm, 'GARAM') || str_contains($nm, 'SEASALT') || str_contains($nm, 'SALT')) {
                                    $katItem = 'GARAM';
                                } elseif (str_contains($nm, 'PERENYAH')) {
                                    $katItem = 'PERENYAH';
                                } elseif (str_contains($nm, 'BUMBU') && !str_contains($nm, 'SEASALT')) {
                                    $katItem = 'BUMBU';
                                }
                            @endphp
                            @if ($katItem !== null)
                                <option value="{{ $b->barang_id }}" data-commodity="{{ $katItem }}" data-nama="{{ $b->barang_nm }}" data-name="{{ $nm }}">
                                    {{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'KG' }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- ISI RAW MATERIAL & KEMASAN STATUS --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="bp_status_raw_material" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="bp_status_raw_material" value="TDK_STD"> TDK STD
                            </label>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                                <input type="radio" name="bp_kemasan_kondisi" value="OK" checked> OK
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="bp_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                            </label>
                        </div>
                    </div>
                </div>

                {{-- CHECKBOX KONDISI ISI (BAHAN) & KONDISI KEMASAN (PERSIS FORM EXCEL MFI) --}}
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        {{-- SISI KIRI: KONDISI ISI BAHAN --}}
                        <div style="border-right: 1.5px solid #e2e8f0; padding-right: 1rem;">
                            <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.75rem;">
                                🧪 KONDISI FISIK BAHAN (Pilih kondisi):
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #15803d; font-weight: 700;">
                                    <input type="checkbox" name="bp_isi_kering" value="1" checked> KERING
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #0284c7; font-weight: 600;">
                                    <input type="checkbox" name="bp_isi_basah" value="1"> BASAH
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #b45309; font-weight: 600;">
                                    <input type="checkbox" name="bp_isi_gumpal" value="1"> GUMPAL
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #475569; font-weight: 600;">
                                    <input type="checkbox" name="bp_isi_berminyak" value="1"> BERMINYAK
                                </label>
                            </div>
                        </div>

                        {{-- SISI KANAN: KONDISI KEMASAN --}}
                        <div>
                            <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.75rem;">
                                📦 KONDISI KEMASAN / ZAK / DUS:
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.85rem;">
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                                    <input type="checkbox" name="bp_kemasan_kotor" value="1"> KOTOR
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                                    <input type="checkbox" name="bp_kemasan_apek" value="1"> APEK
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #b45309; font-weight: 700;">
                                    <input type="checkbox" name="bp_kemasan_jamur" value="1"> JAMUR
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                                    <input type="checkbox" name="bp_kemasan_sobek" value="1"> SOBEK
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KUANTITAS TIMBANGAN / DITERIMA --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
                        ⚖️ KUANTITAS DITERIMA DI PABRIK
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700;">Jumlah Lolos / Netto (KG / Zak)</label>
                            <input type="number" step="0.01" min="0" name="bp_qty_gross" id="bpQtyGross" class="form-control" placeholder="0.00" value="{{ old('bp_qty_gross') }}">
                        </div>
                        <div>
                            <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Rusak (KG / Zak)</label>
                            <input type="number" step="0.01" min="0" name="bp_qty_reject" id="bpQtyReject" class="form-control" placeholder="0.00" value="{{ old('bp_qty_reject', 0) }}">
                        </div>
                    </div>
                </div>

                {{-- KOMENTAR & KESIMPULAN --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
                        <textarea name="bp_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji mutu bahan penolong...">{{ old('bp_komentar') }}</textarea>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem;">
                        <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
                        <div style="display: flex; gap: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                                <input type="radio" name="bp_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="bp_kesimpulan" value="TOLAK"> ❌ TOLAK
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER NAVIGATION OF SECTION 2 --}}
            <div style="padding: 1rem 1.4rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="switchQcTab(1)" style="border-radius: 8px;">
                    &larr; Kembali ke Tahap 1
                </button>
                <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                    <button type="button" class="btn" id="btnSimpanCepat" onclick="submitNonSingkong()" style="display: none; border-radius: 8px; font-weight: 800; background: #059669; color: #ffffff; border: none; padding: 0.55rem 1.15rem; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">
                        💾 Simpan &amp; Teruskan ke Gudang
                    </button>
                    <button type="button" class="btn btn-primary" id="btnNextFryer" onclick="switchQcTab(3)" style="border-radius: 8px; font-weight: 700; padding: 0.6rem 1.25rem;">
                        Lanjut ke Uji Rasa Fryer &amp; Keputusan &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 3: UJI CEPAT RASA FRYER & KESIMPULAN DUA PERSETUJUAN (PENGUJIAN 1)   --}}
        {{-- ========================================================================= --}}
        <div id="qcSection3" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #f59e0b; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 3: Uji Cepat Rasa Fryer &amp; Kesimpulan QC</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Sampel langsung digoreng &amp; dicek rasanya di depan gerbang: Wajib gurih / tidak pahit. Jika pahit langsung tolak!</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="switchQcTab(2)" style="border-radius: 8px; padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                        &larr; Tahap 2
                    </button>
                    <button type="button" class="btn btn-sm" onclick="document.getElementById('btnSubmitPengujian1').click()" style="border-radius: 8px; font-weight: 800; background: #059669; color: #ffffff; border: none; padding: 0.4rem 0.85rem; font-size: 0.8rem; box-shadow: 0 2px 5px rgba(5,150,105,0.25);">
                        💾 Simpan QC
                    </button>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.25rem;">
                <div id="fryerParamsContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                    {{-- Dynamically mirrored from Tab 2 items --}}
                </div>

                {{-- Banner Peringatan Rasa Pahit --}}
                <div id="bannerPahitWarning" style="display: none; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 0.85rem 1rem; color: #991b1b; font-size: 0.825rem;">
                    <div style="font-weight: 800; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                        <span>⚠️</span> <span>PERINGATAN: RASA PAHIT TERDETEKSI!</span>
                    </div>
                    <div id="textPahitWarning">Singkong beracun sianida / tidak layak konsumsi pabrik. Keputusan otomatis dialihkan ke <strong>TOLAK TOTAL</strong>. Truk tidak diizinkan bongkar ke gudang dan Admin Gudang akan menerbitkan Berita Acara Penolakan.</div>
                </div>

                {{-- KOTAK DISKUSI DENGAN ATASAN (KHUSUS PENGUJIAN 2 ATAU KEPUTUSAN KRITIS) --}}
                <div id="boxDiskusiAtasan" style="display: none; background: #fff7ed; border: 1.5px solid #fdba74; border-radius: 10px; padding: 0.85rem 1rem; color: #9a3412; font-size: 0.825rem;">
                    <div style="font-weight: 800; display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                        <span style="display: flex; align-items: center; gap: 0.35rem;">
                            <span>🗣️</span> <span>HASIL DISKUSI DENGAN ATASAN (SUPERVISOR / KEPALA DIREKTUR)</span>
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 700; background: #ffedd5; color: #c2410c; padding: 2px 7px; border-radius: 4px;">
                            SOP Pengujian 2
                        </span>
                    </div>
                    <div style="line-height: 1.4; margin-bottom: 0.5rem;">
                        Jika hasil Pengujian 2 gagal atau terdapat cacat mutu pada sisa muatan bak, diskusikan langkah operasional dengan atasan:
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" onclick="setDiskusiAction('TOLAK_SISA')" style="background: #dc2626; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer;">
                            🚫 Tolak Sisa Muatan (Truk Dipulangkan)
                        </button>
                        <button type="button" onclick="setDiskusiAction('PENYESUAIAN_REFRAKSI')" style="background: #ea580c; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer;">
                            ⚖️ Terima Bersyarat (Potongan Refraksi Ekstra)
                        </button>
                        <button type="button" onclick="setDiskusiAction('DOWNGRADE_B')" style="background: #ca8a04; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer;">
                            🟡 Turunkan Mutu ke Grade B
                        </button>
                    </div>
                </div>

                {{-- KESIMPULAN AKHIR & DUA PERSETUJUAN --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span id="labelKesimpulanTitle">📋 KESIMPULAN AKHIR MUTU BAHAN BAKU SINGKONG</span>
                        <div style="display: flex; gap: 0.75rem; align-items: center; background: #ffffff; padding: 0.25rem 0.65rem; border-radius: 8px; border: 1.5px solid #cbd5e1;">
                            <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 800; font-size: 0.85rem; color: #15803d; cursor: pointer;">
                                <input type="radio" name="kesimpulan_qc" id="radioTerima" value="TERIMA" checked onchange="onKesimpulanChange('TERIMA')">
                                <span id="labelRadioTerima">✔ TERIMA</span>
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 800; font-size: 0.85rem; color: #dc2626; cursor: pointer;">
                                <input type="radio" name="kesimpulan_qc" id="radioTolak" value="TOLAK" onchange="onKesimpulanChange('TOLAK')">
                                <span id="labelRadioTolak">✖ TOLAK TOTAL</span>
                            </label>
                        </div>
                    </div>

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

                    {{-- DUA PERSETUJUAN (QC & KEPALA DIREKTUR) --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; border-top: 1px dashed #cbd5e1; padding-top: 0.85rem;">
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #1e293b;">1. Petugas QC Pemeriksa (Depan)</label>
                            <input type="text" name="petugas_qc_nama" class="form-control" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Petugas QC') }}" readonly style="background: #ffffff; font-weight: 700;">
                        </div>
                        <div>
                            <label class="form-label" style="font-weight: 700; color: #1e293b;">2. QC Supervisor (ACC)</label>
                            <input type="text" name="qc_supervisor_nama" class="form-control" placeholder="Nama QC Supervisor " value="{{ old('qc_supervisor_nama', 'Kepala Direktur / Supervisor QC') }}" style="font-weight: 700;">
                        </div>
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="switchQcTab(2)" style="border-radius: 8px;">
                        &larr; Kembali ke Pemeriksaan Fisik
                    </button>
                    <button type="submit" id="btnSubmitPengujian1" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 1.05rem; padding: 0.85rem; border-radius: 10px; background: #059669; border: none; font-weight: 800; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3);">
                        💾 Simpan Pengujian I (Selesai Inspeksi &amp; Siap Bongkar)
                    </button>
                </div>
            </div>
        </div>
            </div>
        {{-- FLOATING ACTION DOCK (DILAYANGKAN TEPAT DI ATAS NAVBAR BAWAH) --}}
        <div class="qc-form-floating-dock" id="qcFloatingDock">
            <button type="button" class="btn-dock-back" id="dockBtnBack" style="display: none;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span id="dockBackText">Kembali</span>
            </button>
            <button type="button" class="btn-dock-next btn-primary-state" id="dockBtnNext">
                <span id="dockNextText">Lanjut ke 2. Parameter</span>
                <svg id="dockNextIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>

        {{-- FULLSCREEN LOADING OVERLAY UNTUK MENCEGAH DOUBLE CLICK / DOUBLE SUBMISSION --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; padding: 1.75rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); max-width: 320px; margin: 1rem; width: 90%;">
                <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: qcSpin 0.8s linear infinite; margin: 0 auto 1.15rem;"></div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.4rem;" id="overlayTitle">Menyimpan Data QC...</div>
                <div style="font-size: 0.8rem; color: #64748b; line-height: 1.45;">Sedang memproses inspeksi mutu &amp; meneruskan ke gudang. Mohon tidak menekan tombol lagi.</div>
            </div>
        </div>
        <style>
            @keyframes qcSpin {
                to { transform: rotate(360deg); }
            }
        </style>
    </form>
</div>

@php
    $rawMaterialsData = $barangs->map(function ($b) {
        $cd = strtoupper($b->barang_cd ?? '');
        $nm = strtoupper($b->barang_nm ?? '');
        $kat = 'LAINNYA';
        if (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR')) {
            $kat = 'SINGKONG';
        } elseif (str_contains($nm, 'MINYAK')) {
            $kat = 'MINYAK';
        } elseif (str_contains($nm, 'PLASTIK') || str_contains($nm, 'ROLL') || str_contains($nm, 'KEMASAN') || str_contains($nm, 'OPP') || str_contains($nm, 'PP')) {
            $kat = 'PLASTIK';
        } elseif (str_contains($nm, 'KARTON') || str_contains($nm, 'DUS') || str_contains($nm, 'BOX')) {
            $kat = 'KARTON';
        } elseif (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM')) {
            $kat = 'MSG';
        } elseif (str_contains($nm, 'GARAM') || str_contains($nm, 'SEASALT') || str_contains($nm, 'SALT')) {
            $kat = 'GARAM';
        } elseif (str_contains($nm, 'PERENYAH')) {
            $kat = 'PERENYAH';
        } elseif (str_contains($nm, 'BUMBU')) {
            $kat = 'BUMBU';
        }
        return [
            'barang_id' => $b->barang_id,
            'barang_cd' => $b->barang_cd,
            'barang_nm' => $b->barang_nm,
            'kategori'  => $kat,
            'satuan'    => $b->satuanDasar?->satuan_nm ?? 'KG',
        ];
    })->values();

    $poListData = $pos->mapWithKeys(function ($p) use ($getPoCommodities) {
        return [
            $p->po_id => [
                'po_id'       => $p->po_id,
                'po_no'       => $p->po_no,
                'supplier_id' => $p->supplier_id,
                'gudang_id'   => $p->gudang_id,
                'komoditas'   => $getPoCommodities($p),
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
    const STOK_GRADE_A = {{ (float) ($stokSingkongA ?? 0) }};
    const STOK_GRADE_B = {{ (float) ($stokSingkongB ?? 0) }};

    let itemIndex = 0;
    let currentKomoditas = 'SINGKONG';

    const KOMODITAS_CONFIG = {
        'SINGKONG': {
            docNo: 'MFI/HACCP-04/FRM-03/048/VIII/2021',
            title: 'Sampling Mutu Singkong',
            desc: 'Pemeriksaan Fisik Timbangan & Uji Kematangan Fryer',
            labelNamaJenis: 'Nama Bahan / Jenis Singkong',
            hasPanenFields: true,
            sampleUnit: 'KG',
            showTab3: true,
        },
        'MINYAK': {
            docNo: 'MFI/HACCP-04/FRM-03/029/VIII/2021',
            title: 'Sampling Mutu Minyak Goreng',
            desc: 'Pemeriksaan FFA di COA, Kebersihan Tangki & Suhu',
            labelNamaJenis: 'NAMA JENIS (Contoh: Minyak Sawit Curah)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'PLASTIK': {
            docNo: 'MFI/HACCP-04/FRM-03/030/VIII/2021',
            title: 'Sampling Mutu Plastik Kemasan',
            desc: 'Pemeriksaan Ketebalan, Cacat Kemasan & Bebas Sobek',
            labelNamaJenis: 'NAMA JENIS (Contoh: PP 08, OPP, Pouch)',
            hasPanenFields: false,
            sampleUnit: 'pcs',
            showTab3: false,
        },
        'KARTON': {
            docNo: 'MFI/HACCP-04/FRM-03/031/VIII/2021',
            title: 'Sampling Mutu Karton Box',
            desc: 'Pemeriksaan Dimensi (P x L x T), Cacat & Kekuatan',
            labelNamaJenis: 'NAMA JENIS KARTON (Contoh: Master Box Balado)',
            hasPanenFields: false,
            sampleUnit: 'pcs',
            showTab3: false,
        },
        'MSG': {
            docNo: 'MFI/HACCP-04/FRM-03/032/VIII/2021',
            title: 'Sampling Mutu MSG',
            desc: 'Uji Fisik Bahan Penolong (Kering, Gumpal) & Keutuhan Kemasan',
            labelNamaJenis: 'NAMA JENIS (Contoh: MSG Miku / Ajinomoto / Miwon)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'GARAM': {
            docNo: 'MFI/HACCP-04/FRM-03/033/VIII/2021',
            title: 'Sampling Mutu Garam',
            desc: 'Uji Fisik Garam (Kering, Bebas Kotoran) & Keutuhan Kemasan',
            labelNamaJenis: 'NAMA JENIS (Contoh: Garam Halus Beryodium)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'PERENYAH': {
            docNo: 'MFI/HACCP-04/FRM-03/063/IX/2023',
            title: 'Sampling Mutu Perenyah',
            desc: 'Uji Fisik Perenyah (Kering, Homogen) & Label Halal / Kadaluarsa',
            labelNamaJenis: 'NAMA JENIS (Contoh: Perenyah Keripik Singkong)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        }
    };

    function selectKomoditas(type) {
        currentKomoditas = type;
        document.getElementById('kategoriBarangInput').value = type;

        // Update button pills styling
        ['SINGKONG', 'MINYAK', 'PLASTIK', 'KARTON', 'MSG', 'GARAM', 'PERENYAH'].forEach(k => {
            const btn = document.getElementById(`btnKomoditas_${k}`);
            if (btn) {
                if (k === type) {
                    btn.style.background = '#0284c7';
                    btn.style.borderColor = '#0284c7';
                    btn.style.color = '#ffffff';
                } else {
                    btn.style.background = '#ffffff';
                    btn.style.borderColor = '#cbd5e1';
                    btn.style.color = '#334155';
                }
            }
        });

        const cfg = KOMODITAS_CONFIG[type] || KOMODITAS_CONFIG['SINGKONG'];
        document.getElementById('badgeDocNo').innerText = 'No. Dok: ' + cfg.docNo;
        document.getElementById('titleFormHaccp').innerText = cfg.title;
        document.getElementById('descFormHaccp').innerText = cfg.desc;
        document.getElementById('labelNamaJenis').innerText = cfg.labelNamaJenis;

        // Toggle panen fields for Singkong
        const displayPanen = cfg.hasPanenFields ? 'block' : 'none';
        document.getElementById('groupLokasiPanen').style.display = displayPanen;
        document.getElementById('groupUmurSingkong').style.display = displayPanen;
        document.getElementById('groupTglPanen').style.display = displayPanen;

        // Toggle Halal Cert questions (Khusus Non-Singkong seperti Minyak, Kemasan & Bahan Penolong sesuai form resmi HACCP MFI)
        const halalCertGroup = document.getElementById('auditHalalNonSingkong');
        if (halalCertGroup) {
            halalCertGroup.style.display = (type === 'SINGKONG') ? 'none' : 'flex';
        }

        // Toggle sample input
        document.getElementById('groupSampleKg').style.display = (cfg.sampleUnit === 'KG') ? 'block' : 'none';
        document.getElementById('groupSampleGr').style.display = (cfg.sampleUnit === 'gr') ? 'block' : 'none';
        document.getElementById('groupSamplePcs').style.display = (cfg.sampleUnit === 'pcs') ? 'block' : 'none';

        // Toggle Tab 3 (Fryer) & Nav
        const tabBtn3 = document.getElementById('tabBtn3');
        const qcTabNav = document.getElementById('qcTabNav');
        const btnNextFryer = document.getElementById('btnNextFryer');
        const btnAddItemRow = document.getElementById('btnAddItemRow');
        const btnSimpanCepat = document.getElementById('btnSimpanCepat');

        const btnTopNextFryer = document.getElementById('btnTopNextFryer');
        const btnTopSimpanCepat = document.getElementById('btnTopSimpanCepat');

        if (cfg.showTab3) {
            tabBtn3.style.display = 'block';
            qcTabNav.style.gridTemplateColumns = 'repeat(3, 1fr)';
            btnNextFryer.style.display = 'inline-block';
            btnNextFryer.innerText = 'Lanjut ke Uji Rasa Fryer & Keputusan \u2192';
            if (btnTopNextFryer) btnTopNextFryer.style.display = 'inline-block';
            btnAddItemRow.style.display = 'inline-block';
            if (btnSimpanCepat) btnSimpanCepat.style.display = 'none';
            if (btnTopSimpanCepat) btnTopSimpanCepat.style.display = 'none';
            document.getElementById('section2Title').innerText = 'Tahap 2: Pengujian I \u2022 Sampling Fisik & Diameter';
            document.getElementById('section2Sub').innerText = 'Standar diameter, kebersihan tanah & kondisi visual singkong';
        } else {
            tabBtn3.style.display = 'none';
            qcTabNav.style.gridTemplateColumns = 'repeat(2, 1fr)';
            btnNextFryer.style.display = 'none';
            if (btnTopNextFryer) btnTopNextFryer.style.display = 'none';
            btnAddItemRow.style.display = 'none';
            if (btnSimpanCepat) {
                btnSimpanCepat.style.display = 'inline-block';
                btnSimpanCepat.innerText = '\uD83D\uDCBE Simpan & Teruskan ke Gudang';
            }
            if (btnTopSimpanCepat) {
                btnTopSimpanCepat.style.display = 'inline-block';
            }
            document.getElementById('section2Title').innerText = 'Tahap 2: Pemeriksaan Mutu & Parameter Kedatangan';
            document.getElementById('section2Sub').innerText = 'Formulir Cheklist HACCP PT Mirasa Food Industry';
        }

        if (typeof updateFloatingDock === 'function') {
            updateFloatingDock(currentTabNumber);
        }

        const isBP = ['MSG', 'GARAM', 'PERENYAH'].includes(type);

        // Toggle containers in Section 2
        document.getElementById('singkongContainer').style.display = (type === 'SINGKONG') ? 'flex' : 'none';
        document.getElementById('minyakContainer').style.display = (type === 'MINYAK') ? 'flex' : 'none';
        document.getElementById('plastikContainer').style.display = (type === 'PLASTIK') ? 'flex' : 'none';
        document.getElementById('kartonContainer').style.display = (type === 'KARTON') ? 'flex' : 'none';
        document.getElementById('bahanPenolongContainer').style.display = isBP ? 'flex' : 'none';

        if (isBP) {
            document.getElementById('labelBahanPenolongItem').innerText = `Komoditas Bahan Penolong (${type}) *`;
            const selectEl = document.getElementById('bpBarangSelect');
            let firstMatchedIndex = -1;

            for (let i = 0; i < selectEl.options.length; i++) {
                const opt = selectEl.options[i];
                const comm = opt.getAttribute('data-commodity');
                const optText = opt.text.toUpperCase();

                // Tampilkan hanya opsi yang sesuai dengan komoditas yang dipilih (MSG / GARAM / PERENYAH)
                const isMatch = (comm === type) || 
                                (type === 'GARAM' && (comm === 'GARAM' || optText.includes('GARAM') || optText.includes('SALT'))) ||
                                (type === 'MSG' && (comm === 'MSG' || optText.includes('MSG') || optText.includes('MONOSODIUM'))) ||
                                (type === 'PERENYAH' && (comm === 'PERENYAH' || optText.includes('PERENYAH')));

                if (isMatch) {
                    opt.style.display = '';
                    opt.disabled = false;
                    opt.hidden = false;
                    if (firstMatchedIndex === -1) {
                        firstMatchedIndex = i;
                    }
                } else {
                    opt.style.display = 'none';
                    opt.disabled = true;
                    opt.hidden = true;
                }
            }

            if (firstMatchedIndex !== -1) {
                selectEl.selectedIndex = firstMatchedIndex;
            }
        }

        syncQuantityFields();
        filterSupplierDropdown();
        filterPoDropdown();
        syncNamaRmOnKomoditasSwitch(type);
    }

    function syncNamaRmFromSelect(selectEl) {
        if (!selectEl) return;
        const opt = selectEl.selectedOptions[0];
        if (!opt) return;
        const nama = opt.getAttribute('data-nama') || opt.text.split(' - ')[1]?.split(' (')[0] || opt.text;
        const namaJenisEl = document.getElementById('namaJenisInput');
        if (namaJenisEl && nama) {
            namaJenisEl.value = nama.trim();
        }
    }

    function syncNamaRmOnKomoditasSwitch(type) {
        const isBP = ['MSG', 'GARAM', 'PERENYAH'].includes(type);
        const namaJenisEl = document.getElementById('namaJenisInput');
        if (!namaJenisEl) return;

        if (type === 'SINGKONG') {
            const firstSel = document.querySelector('.item-barang-select');
            if (firstSel && firstSel.value) {
                const mat = RAW_MATERIALS.find(b => b.barang_id == firstSel.value);
                if (mat) namaJenisEl.value = mat.barang_nm;
            } else {
                const defaultMat = RAW_MATERIALS.find(b => b.kategori === 'SINGKONG');
                if (defaultMat) namaJenisEl.value = defaultMat.barang_nm;
            }
        } else if (type === 'MINYAK') {
            syncNamaRmFromSelect(document.getElementById('minyakBarangSelect'));
        } else if (type === 'PLASTIK') {
            syncNamaRmFromSelect(document.getElementById('plastikBarangSelect'));
        } else if (type === 'KARTON') {
            syncNamaRmFromSelect(document.getElementById('kartonBarangSelect'));
        } else if (isBP) {
            syncNamaRmFromSelect(document.getElementById('bpBarangSelect'));
        }
    }

    // =========================================================================
    // LOGIKA FILTER & PENCARIAN CEPAT SUPPLIER / PRODUSEN
    // =========================================================================
    let currentSupplierCategory = 'AUTO'; // 'AUTO', 'RAW', 'VENDOR', 'ALL'
    let currentSupplierSearch = '';

    function setSupplierCategoryFilter(cat) {
        currentSupplierCategory = cat;

        ['AUTO', 'RAW', 'VENDOR', 'ALL'].forEach(c => {
            const chip = document.getElementById(`chipSupplier_${c}`);
            if (chip) {
                if (c === cat) {
                    chip.classList.add('active-chip');
                } else {
                    chip.classList.remove('active-chip');
                }
            }
        });

        filterSupplierDropdown();
    }

    function onSearchSupplier(query) {
        currentSupplierSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearSupplierSearch');
        if (clearBtn) {
            clearBtn.style.display = currentSupplierSearch ? 'block' : 'none';
        }
        filterSupplierDropdown();
    }

    function clearSupplierSearch() {
        const input = document.getElementById('supplierSearchInput');
        if (input) input.value = '';
        onSearchSupplier('');
    }

    function onSupplierSelected(select) {
        if (!select) return;
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const supNm = opt.getAttribute('data-supplier-nm') || opt.text.split('(')[0].trim();
            const produsenInput = document.getElementById('namaProdusenInput');
            if (produsenInput) {
                produsenInput.value = supNm;
            }
        }
    }

    function filterSupplierDropdown() {
        const select = document.getElementById('supplierSelect');
        if (!select) return;

        const badge = document.getElementById('supplierFilterBadge');
        let visibleCount = 0;
        let firstVisibleIndex = -1;
        const currentVal = select.value;
        let isCurrentValVisible = false;

        let targetJenis = null;
        let filterLabel = '';

        if (currentSupplierCategory === 'AUTO') {
            if (currentKomoditas === 'SINGKONG') {
                targetJenis = 'RAW';
                filterLabel = 'Filter: 🥔 Petani Singkong';
            } else if (currentKomoditas === 'MINYAK') {
                targetJenis = 'BP';
                filterLabel = 'Filter: 🛢️ Vendor Minyak';
            } else if (currentKomoditas === 'PLASTIK') {
                targetJenis = 'KEMASAN';
                filterLabel = 'Filter: 🛍️ Vendor Plastik';
            } else if (currentKomoditas === 'KARTON') {
                targetJenis = 'KEMASAN';
                filterLabel = 'Filter: 📦 Vendor Karton';
            } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
                targetJenis = 'BUMBU_BP';
                filterLabel = `Filter: 🧂 Bumbu & ${currentKomoditas}`;
            }
        } else if (currentSupplierCategory === 'RAW') {
            targetJenis = 'RAW';
            filterLabel = 'Filter: 🌾 Petani Singkong';
        } else if (currentSupplierCategory === 'VENDOR') {
            targetJenis = 'VENDOR';
            filterLabel = 'Filter: 🏭 Vendor Industri';
        } else {
            targetJenis = 'ALL';
            filterLabel = 'Filter: 🌐 Semua Supplier';
        }

        const q = currentSupplierSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const jenisCd = (opt.getAttribute('data-jenis-cd') || '').toUpperCase();
            const isPetani = opt.getAttribute('data-is-petani') === '1';
            const supNm = (opt.getAttribute('data-supplier-nm') || opt.text).toLowerCase();
            const supCd = (opt.getAttribute('data-supplier-cd') || '').toLowerCase();

            // Cek Kategori
            let matchCat = false;
            if (targetJenis === 'ALL') {
                matchCat = true;
            } else if (targetJenis === 'RAW') {
                matchCat = isPetani || jenisCd === 'RAW' || supCd.startsWith('skg-');
            } else if (targetJenis === 'VENDOR') {
                matchCat = !isPetani && !supCd.startsWith('skg-');
            } else if (targetJenis === 'BP') {
                matchCat = jenisCd === 'BP' || (!isPetani && (supNm.includes('smart') || supNm.includes('barco') || supNm.includes('karacoco') || supNm.includes('minyak')));
            } else if (targetJenis === 'KEMASAN') {
                matchCat = jenisCd === 'KEMASAN' || (!isPetani && (supNm.includes('plast') || supNm.includes('karton') || supNm.includes('purinusa') || supNm.includes('sriwahana') || supNm.includes('print') || supNm.includes('tunas')));
            } else if (targetJenis === 'BUMBU_BP') {
                matchCat = jenisCd === 'BUMBU' || jenisCd === 'BP' || (!isPetani && !supCd.startsWith('skg-'));
            } else {
                matchCat = true;
            }

            // Cek Search Query
            let matchSearch = true;
            if (q) {
                matchSearch = supNm.includes(q) || supCd.includes(q);
            }

            const isMatch = matchCat && matchSearch;

            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (firstVisibleIndex === -1) {
                    firstVisibleIndex = i;
                }
                if (opt.value === currentVal) {
                    isCurrentValVisible = true;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        if (badge) {
            badge.innerText = `${filterLabel} (${visibleCount})`;
        }

        // Sinkronisasi nilai terpilih
        if (!isCurrentValVisible && currentVal) {
            const selectedPoId = document.getElementById('poSelect')?.value;
            if (!selectedPoId) {
                select.value = '';
                const produsenInput = document.getElementById('namaProdusenInput');
                if (produsenInput) produsenInput.value = '';
            }
        } else if (isCurrentValVisible) {
            onSupplierSelected(select);
        }
    }

    let currentPoCategory = 'AUTO';
    let currentPoSearch = '';

    function setPoCategoryFilter(cat) {
        currentPoCategory = cat;
        ['AUTO', 'SINGKONG', 'MINYAK', 'KEMASAN', 'BP', 'ALL'].forEach(k => {
            const btn = document.getElementById(`chipPo_${k}`);
            if (btn) {
                if (k === cat) {
                    btn.classList.add('active-chip');
                } else {
                    btn.classList.remove('active-chip');
                }
            }
        });
        filterPoDropdown();
    }

    function onSearchPo(query) {
        currentPoSearch = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('btnClearPoSearch');
        if (clearBtn) {
            clearBtn.style.display = currentPoSearch ? 'block' : 'none';
        }
        filterPoDropdown();
    }

    function clearPoSearch() {
        const input = document.getElementById('poSearchInput');
        if (input) input.value = '';
        onSearchPo('');
    }

    function filterPoDropdown() {
        const select = document.getElementById('poSelect');
        if (!select) return;

        const badge = document.getElementById('poFilterBadge');
        let visibleCount = 0;
        const currentVal = select.value;
        let isCurrentValVisible = false;

        let targetKomoditas = [];
        let filterLabel = '';

        if (currentPoCategory === 'AUTO') {
            if (currentKomoditas === 'SINGKONG') {
                targetKomoditas = ['SINGKONG'];
                filterLabel = 'Filter: 🥔 Singkong & Bahan Baku';
            } else if (currentKomoditas === 'MINYAK') {
                targetKomoditas = ['MINYAK'];
                filterLabel = 'Filter: 🛢️ Minyak Goreng';
            } else if (currentKomoditas === 'PLASTIK') {
                targetKomoditas = ['PLASTIK'];
                filterLabel = 'Filter: 🛍️ Plastik Kemasan';
            } else if (currentKomoditas === 'KARTON') {
                targetKomoditas = ['KARTON'];
                filterLabel = 'Filter: 📦 Karton Box';
            } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
                targetKomoditas = [currentKomoditas, 'BUMBU'];
                filterLabel = `Filter: 🧂 Bahan Penolong (${currentKomoditas})`;
            }
        } else if (currentPoCategory === 'SINGKONG') {
            targetKomoditas = ['SINGKONG'];
            filterLabel = 'Filter: 🥔 Singkong & Bahan Baku';
        } else if (currentPoCategory === 'MINYAK') {
            targetKomoditas = ['MINYAK'];
            filterLabel = 'Filter: 🛢️ Minyak Goreng';
        } else if (currentPoCategory === 'KEMASAN') {
            targetKomoditas = ['PLASTIK', 'KARTON'];
            filterLabel = 'Filter: 📦 Kemasan (Plastik/Dus)';
        } else if (currentPoCategory === 'BP') {
            targetKomoditas = ['MSG', 'GARAM', 'PERENYAH', 'BUMBU'];
            filterLabel = 'Filter: 🧂 Bahan Penolong & Bumbu';
        } else {
            targetKomoditas = null; // ALL
            filterLabel = 'Filter: 🌐 Semua PO';
        }

        const q = currentPoSearch;

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (!opt.value) {
                // Placeholder
                opt.style.display = '';
                opt.disabled = false;
                continue;
            }

            const komoditasAttr = opt.getAttribute('data-komoditas') || '';
            const komoditasList = komoditasAttr.split(',').map(s => s.trim().toUpperCase());
            const searchText = (opt.getAttribute('data-search') || opt.text).toLowerCase();

            let matchCat = false;
            if (!targetKomoditas) {
                matchCat = true;
            } else {
                matchCat = targetKomoditas.some(tk => komoditasList.includes(tk));
            }

            let matchSearch = true;
            if (q) {
                matchSearch = searchText.includes(q);
            }

            const isMatch = matchCat && matchSearch;

            if (isMatch) {
                opt.style.display = '';
                opt.disabled = false;
                opt.hidden = false;
                visibleCount++;
                if (opt.value === currentVal) {
                    isCurrentValVisible = true;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                opt.hidden = true;
            }
        }

        // Placeholder text info
        const defaultOpt = select.options[0];
        if (defaultOpt) {
            if (targetKomoditas && targetKomoditas.includes('SINGKONG')) {
                defaultOpt.text = `-- Tanpa PO / Kiriman Langsung Singkong (${visibleCount} PO Tersedia) --`;
            } else if (visibleCount > 0) {
                defaultOpt.text = `-- Tanpa PO / Kiriman Langsung (${visibleCount} PO Tersedia) --`;
            } else {
                defaultOpt.text = `-- Tanpa PO / Kiriman Langsung (0 PO untuk Komoditas Ini) --`;
            }
        }

        if (badge) {
            badge.innerText = `${filterLabel} (${visibleCount} PO)`;
        }

        // Jika nilai PO yang sebelumnya terpilih tidak cocok dengan filter komoditas baru, reset ke tanpa PO
        if (currentVal && !isCurrentValVisible) {
            select.value = '';
        }
    }

    function syncQuantityFields() {
        const sj = parseFloat(document.getElementById('inputJumlahSJ').value) || 0;
        const pabrik = parseFloat(document.getElementById('inputJumlahPabrik').value) || sj;

        if (currentKomoditas === 'SINGKONG') {
            const gross0 = document.getElementById('gross_0');
            const inputPabrikEl = document.getElementById('inputJumlahPabrik');
            if (gross0 && inputPabrikEl && inputPabrikEl.value !== '') {
                gross0.value = pabrik > 0 ? pabrik : '';
                calculateCard(0);
            }
        } else if (currentKomoditas === 'MINYAK') {
            const el = document.getElementById('minyakQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? pabrik : '';
        } else if (currentKomoditas === 'PLASTIK') {
            const el = document.getElementById('plastikQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? Math.round(pabrik) : '';
        } else if (currentKomoditas === 'KARTON') {
            const el = document.getElementById('kartonQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? Math.round(pabrik) : '';
        } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
            const el = document.getElementById('bpQtyGross');
            if (el && (!el.value || el.value == 0)) el.value = pabrik > 0 ? pabrik : '';
        }
    }

    function selectTahapUji(tahap) {
        const tahapInput = document.getElementById('tahapUjiInput');
        if (tahapInput) tahapInput.value = tahap;

        const btn1 = document.getElementById('btnTahap_1');
        const btn2 = document.getElementById('btnTahap_2');
        const badge = document.getElementById('labelTahapBadge');
        const desc = document.getElementById('descTahapUji');
        const wrapPending = document.getElementById('wrapPendingP1');
        const titleHaccp = document.getElementById('titleFormHaccp');
        const descHaccp = document.getElementById('descFormHaccp');
        const sec2Title = document.getElementById('section2Title');
        const sec2Sub = document.getElementById('section2Sub');
        const btnSubmit = document.getElementById('btnSubmitPengujian1');
        const dockNext = document.getElementById('dockNextText');
        const hintPabrik = document.getElementById('hintJumlahPabrik');

        if (tahap === 'PENGUJIAN_2') {
            if (hintPabrik) {
                hintPabrik.innerHTML = 'Pengujian 2: Sisa muatan setengah bak yang dibongkar tuntas (contoh: <strong>4.000 KG</strong>).';
            }
            if (btn1) {
                btn1.style.background = '#ffffff';
                btn1.style.borderColor = '#cbd5e1';
                btn1.style.color = '#334155';
            }
            if (btn2) {
                btn2.style.background = '#9333ea';
                btn2.style.borderColor = '#9333ea';
                btn2.style.color = '#ffffff';
            }
            if (badge) {
                badge.innerText = '🍟 Pengujian 2 (Lanjutan)';
                badge.style.color = '#7e22ce';
                badge.style.background = '#f3e8ff';
            }
            if (desc) {
                desc.innerHTML = '🍟 <strong>Pengujian 2:</strong> Pengujian lanjutan setelah setengah bak diturunkan. Petugas mengambil sampel gabungan ~7 kg dari lapisan dalam/bawah bak untuk verifikasi sebelum bongkar tuntas.';
            }
            if (wrapPending) wrapPending.style.display = 'block';
            if (titleHaccp) titleHaccp.innerText = 'Sampling Mutu Singkong - Pengujian 2';
            if (descHaccp) descHaccp.innerText = 'Pemeriksaan fisik & uji fryer lanjutan setelah pembongkaran setengah bak';
            if (sec2Title) sec2Title.innerText = 'Tahap 2: Pengujian 2 • Sampling Fisik Lapisan Dalam Bak';
            if (sec2Sub) sec2Sub.innerText = 'Verifikasi kondisi visual, diameter, dan tanah dari setengah bak yang tersisa';
            if (btnSubmit) {
                btnSubmit.innerHTML = '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)';
            }
            if (dockNext && currentTabNumber === 3) {
                dockNext.innerText = '💾 Simpan Pengujian 2 (Tuntas)';
            }
        } else {
            if (hintPabrik) {
                hintPabrik.innerHTML = 'Pengujian 1: Muatan setengah bak pertama yang turun (contoh: <strong>3.000 KG</strong>). Sisa (4.000 KG) akan otomatis di Pengujian 2.';
            }
            if (btn1) {
                btn1.style.background = '#0284c7';
                btn1.style.borderColor = '#0284c7';
                btn1.style.color = '#ffffff';
            }
            if (btn2) {
                btn2.style.background = '#ffffff';
                btn2.style.borderColor = '#cbd5e1';
                btn2.style.color = '#334155';
            }
            if (badge) {
                badge.innerText = '🚛 Pengujian 1 (Awal)';
                badge.style.color = '#0284c7';
                badge.style.background = '#e0f2fe';
            }
            if (desc) {
                desc.innerHTML = '🚛 <strong>Pengujian 1:</strong> Pengujian awal saat truk singkong tiba di pos penerimaan (sebelum bongkar muatan). Sampel gabungan ~7 kg diambil dari bak belakang, tengah, depan.';
            }
            if (wrapPending) wrapPending.style.display = 'none';
            if (titleHaccp) titleHaccp.innerText = 'Sampling Mutu Singkong';
            if (descHaccp) descHaccp.innerText = 'Pencatatan sampling mutu kedatangan bahan baku di lapangan';
            if (sec2Title) sec2Title.innerText = 'Tahap 2: Pengujian 1 • Sampling Fisik & Diameter';
            if (sec2Sub) sec2Sub.innerText = 'Standar diameter, kebersihan tanah & kondisi visual singkong';
            if (btnSubmit) {
                btnSubmit.innerHTML = '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
            }
            if (dockNext && currentTabNumber === 3) {
                dockNext.innerText = '💾 Simpan Pengujian 1 (Selesai)';
            }
        }
    }

    function setTahapUji(tahap) {
        selectTahapUji(tahap);
    }

    function onSelectPendingArrival(selectEl) {
        if (!selectEl) return;
        const opt = selectEl.selectedOptions[0];
        if (!opt || !opt.value) {
            clearSelectedPendingArrival();
            return;
        }

        const dataStr = opt.getAttribute('data-json');
        if (!dataStr) return;

        let d = {};
        try {
            d = JSON.parse(dataStr);
        } catch(e) {
            console.error('Invalid json data', e);
            return;
        }

        // Set parent_qc_id & batch_no
        document.getElementById('parentQcIdInput').value = d.qc_id || '';
        document.getElementById('batchNoInput').value = d.batch_no || '';

        // Auto-fill Supplier
        if (d.supplier_id) {
            const sSelect = document.getElementById('supplierSelect');
            if (sSelect) {
                setSupplierCategoryFilter('RAW');
                sSelect.value = d.supplier_id;
                onSupplierSelected(sSelect);
            }
        }

        // Auto-fill Gudang
        if (d.gudang_id) {
            const gSelect = document.getElementById('gudangSelect');
            if (gSelect) gSelect.value = d.gudang_id;
        }

        // Auto-fill PO (or Non-PO)
        const pSelect = document.getElementById('poSelect');
        if (pSelect) {
            pSelect.value = d.po_id || '';
            if (typeof onPoSelected === 'function') onPoSelected(pSelect);
        }

        // Auto-fill Plat & Sopir
        const platInput = document.querySelector('input[name="plat_nomor_truk"]');
        if (platInput && d.plat) platInput.value = d.plat;

        const sopirInput = document.querySelector('input[name="sopir_nama"]');
        if (sopirInput && d.sopir) sopirInput.value = d.sopir;

        // Auto-fill Dokumen
        const sjInput = document.querySelector('input[name="surat_jalan_supplier"]');
        if (sjInput && d.sj) sjInput.value = d.sj;

        const doInput = document.querySelector('input[name="nomor_do"]');
        if (doInput && d.do) doInput.value = d.do;

        // Auto-fill Panen
        const lokasiInput = document.querySelector('input[name="lokasi_panen"]');
        if (lokasiInput && d.lokasi_panen) lokasiInput.value = d.lokasi_panen;

        const umurInput = document.querySelector('input[name="umur_singkong_bln"]');
        if (umurInput && d.umur_singkong) umurInput.value = d.umur_singkong;

        const tglPanenInput = document.querySelector('input[name="tgl_panen"]');
        if (tglPanenInput && d.tgl_panen) tglPanenInput.value = d.tgl_panen;

        // Hitung estimasi kuantitas sisa setengah bak untuk Pengujian 2
        const totalSJ = parseFloat(d.sj_qty || 0);
        const p1Gross = parseFloat(d.gross || 0);
        const sisaSetengahBak = Math.max(0, totalSJ - p1Gross);
        const estimasiGrossUji2 = sisaSetengahBak > 0 ? sisaSetengahBak : (p1Gross > 0 ? p1Gross : (totalSJ > 0 ? totalSJ / 2 : 4000));

        const inputPabrik = document.getElementById('inputJumlahPabrik');
        if (inputPabrik) inputPabrik.value = estimasiGrossUji2;

        // Auto-fill Singkong Item 0
        if (d.barang_id) {
            const bSel = document.getElementById('barang_select_0');
            if (bSel) {
                bSel.value = d.barang_id;
                onItemBarangChanged(0);
            }
        }
        
        // Untuk Pengujian 2: Timbangan kotor setengah bak sisa
        const gross0 = document.getElementById('gross_0');
        if (gross0) {
            gross0.value = estimasiGrossUji2;
            calculateCard(0);
        }
        if (d.refraksi) {
            const ref0 = document.getElementById('refraksi_0');
            if (ref0) {
                ref0.value = d.refraksi;
                calculateCard(0);
            }
        }

        // Tampilkan banner informatif hasil Pengujian 1 vs Pengujian 2
        const banner = document.getElementById('bannerSelectedP1');
        const descBanner = document.getElementById('labelSelectedP1Desc');
        if (banner) banner.style.display = 'flex';
        if (descBanner) {
            const p1GradeLabel = d.grade_cd === 'B' ? '🟡 Grade B' : '🟢 Grade A';
            descBanner.innerHTML = `<strong>#${d.qc_no}</strong> &bull; 🚛 ${d.plat || 'Plat -'} &bull; Hasil Uji 1: <span style="background:#e0f2fe; color:#0369a1; padding:1px 6px; border-radius:4px; font-weight:800;">${p1GradeLabel} (${p1Gross.toLocaleString('id-ID')} KG)</span> &bull; Muatan Uji 2: <span style="background:#f3e8ff; color:#7e22ce; padding:1px 6px; border-radius:4px; font-weight:800;">Sisa Setengah Bak (${estimasiGrossUji2.toLocaleString('id-ID')} KG)</span>`;
        }

        showQcToast('Data Pengujian 1 Terisi', `Data kedatangan #${d.qc_no} dimuat. Sisa muatan bak: ${estimasiGrossUji2.toLocaleString('id-ID')} KG.`, null, 1);
    }

    function clearSelectedPendingArrival() {
        document.getElementById('parentQcIdInput').value = '';
        document.getElementById('batchNoInput').value = '';
        const selectP1 = document.getElementById('selectPendingP1');
        if (selectP1) selectP1.value = '';

        const banner = document.getElementById('bannerSelectedP1');
        if (banner) banner.style.display = 'none';

        showQcToast('Pilihan Direset', 'Mode input kedatangan Pengujian 2 mandiri diaktifkan.', null, 1, true);
    }

    let currentTabNumber = 1;

    function updateFloatingDock(tabNumber) {
        currentTabNumber = tabNumber;
        const btnBack = document.getElementById('dockBtnBack');
        const btnNext = document.getElementById('dockBtnNext');
        const backText = document.getElementById('dockBackText');
        const nextText = document.getElementById('dockNextText');
        const nextIcon = document.getElementById('dockNextIcon');
        if (!btnBack || !btnNext) return;

        if (tabNumber === 1) {
            btnBack.style.display = 'none';
            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-primary-state';
            if (nextText) nextText.innerText = 'Lanjut ke 2. Parameter';
            if (nextIcon) nextIcon.style.display = 'inline-block';
            btnNext.onclick = function() { switchQcTab(2); };
        } else if (tabNumber === 2) {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Tahap 1';
            btnBack.onclick = function() { switchQcTab(1); };

            if (currentKomoditas === 'SINGKONG') {
                btnNext.style.display = 'inline-flex';
                btnNext.className = 'btn-dock-next btn-primary-state';
                if (nextText) nextText.innerText = 'Lanjut ke 3. Uji Fryer';
                if (nextIcon) nextIcon.style.display = 'inline-block';
                btnNext.onclick = function() { switchQcTab(3); };
            } else {
                btnNext.style.display = 'inline-flex';
                btnNext.className = 'btn-dock-next btn-success-state';
                if (nextText) nextText.innerText = '💾 Simpan & Teruskan ke Gudang';
                if (nextIcon) nextIcon.style.display = 'none';
                btnNext.onclick = function() { submitNonSingkong(); };
            }
        } else if (tabNumber === 3) {
            btnBack.style.display = 'inline-flex';
            if (backText) backText.innerText = 'Tahap 2';
            btnBack.onclick = function() { switchQcTab(2); };

            btnNext.style.display = 'inline-flex';
            btnNext.className = 'btn-dock-next btn-success-state';
            if (nextText) nextText.innerText = '💾 Simpan Pengujian I (Selesai)';
            if (nextIcon) nextIcon.style.display = 'none';
            btnNext.onclick = function() {
                const subBtn = document.getElementById('btnSubmitPengujian1');
                if (subBtn) subBtn.click();
            };
        }
    }

    function switchQcTab(tabNumber) {
        document.getElementById('qcSection1').style.display = (tabNumber === 1) ? 'block' : 'none';
        document.getElementById('qcSection2').style.display = (tabNumber === 2) ? 'block' : 'none';
        document.getElementById('qcSection3').style.display = (tabNumber === 3) ? 'block' : 'none';

        for (let i = 1; i <= 3; i++) {
            const btn = document.getElementById(`tabBtn${i}`);
            if (!btn) continue;
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

        updateFloatingDock(tabNumber);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function prepareCommoditySubmission() {
        const curNamaJenis = document.getElementById('namaJenisInput')?.value?.trim();

        if (currentKomoditas === 'MINYAK') {
            const bId = document.getElementById('minyakBarangSelect').value;
            const bOpt = document.getElementById('minyakBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Minyak Goreng Kelapa Sawit';
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('minyakQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('minyakQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="minyak_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="minyak_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][tipe_wadah_minyak]', document.querySelector('input[name="minyak_tipe_wadah"]:checked')?.value || 'TANGKI');
            injectHiddenInput('items[0][kondisi_tangki_jerigen]', document.querySelector('input[name="minyak_kondisi_wadah"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][ffa_coa]', document.querySelector('input[name="minyak_ffa_coa"]')?.value || '');
            injectHiddenInput('items[0][ffa_qc]', document.querySelector('input[name="minyak_ffa_qc"]')?.value || '');
            injectHiddenInput('items[0][minyak_jernih_st]', document.querySelector('input[name="minyak_jernih_st"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][tangki_bersih_st]', document.querySelector('input[name="minyak_tangki_bersih_st"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="minyak_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="minyak_komentar"]')?.value || '');
        } else if (currentKomoditas === 'PLASTIK') {
            const bId = document.getElementById('plastikBarangSelect').value;
            const bOpt = document.getElementById('plastikBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Plastik Kemasan';
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('plastikQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('plastikQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="plastik_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="plastik_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="plastik_kemasan_kondisi"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="plastik_kemasan_kotor"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="plastik_kemasan_apek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_basah]', document.querySelector('input[name="plastik_kemasan_basah"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="plastik_kemasan_sobek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][ketebalan_analisa]', document.querySelector('input[name="plastik_ketebalan_analisa"]')?.value || '');
            injectHiddenInput('items[0][ketebalan_standar]', document.querySelector('input[name="plastik_ketebalan_standar"]')?.value || '');
            injectHiddenInput('items[0][keutuhan_analisa]', document.querySelector('input[name="plastik_keutuhan_analisa"]')?.value || 'Tidak Sobek');
            injectHiddenInput('items[0][keutuhan_standar]', document.querySelector('input[name="plastik_keutuhan_standar"]')?.value || 'Tidak Sobek');
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="plastik_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="plastik_komentar"]')?.value || '');
        } else if (currentKomoditas === 'KARTON') {
            const bId = document.getElementById('kartonBarangSelect').value;
            const bOpt = document.getElementById('kartonBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : 'Karton Box';
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('kartonQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('kartonQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="karton_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="karton_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="karton_kemasan_kondisi"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="karton_kemasan_kotor"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="karton_kemasan_apek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_basah]', document.querySelector('input[name="karton_kemasan_basah"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_jamur]', document.querySelector('input[name="karton_kemasan_jamur"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="karton_kemasan_sobek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_berminyak]', document.querySelector('input[name="karton_kemasan_berminyak"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_berdebu]', document.querySelector('input[name="karton_kemasan_berdebu"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][dimensi_panjang_analisa]', document.querySelector('input[name="karton_dimensi_panjang_analisa"]')?.value || '');
            injectHiddenInput('items[0][dimensi_panjang_standar]', document.querySelector('input[name="karton_dimensi_panjang_standar"]')?.value || '');
            injectHiddenInput('items[0][dimensi_lebar_analisa]', document.querySelector('input[name="karton_dimensi_lebar_analisa"]')?.value || '');
            injectHiddenInput('items[0][dimensi_lebar_standar]', document.querySelector('input[name="karton_dimensi_lebar_standar"]')?.value || '');
            injectHiddenInput('items[0][dimensi_tinggi_analisa]', document.querySelector('input[name="karton_dimensi_tinggi_analisa"]')?.value || '');
            injectHiddenInput('items[0][dimensi_tinggi_standar]', document.querySelector('input[name="karton_dimensi_tinggi_standar"]')?.value || '');
            injectHiddenInput('items[0][spesifikasi_analisa]', document.querySelector('input[name="karton_spesifikasi_analisa"]')?.value || '');
            injectHiddenInput('items[0][spesifikasi_standar]', document.querySelector('input[name="karton_spesifikasi_standar"]')?.value || '');
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="karton_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="karton_komentar"]')?.value || '');
        } else if (['MSG', 'GARAM', 'PERENYAH'].includes(currentKomoditas)) {
            const bId = document.getElementById('bpBarangSelect').value;
            const bOpt = document.getElementById('bpBarangSelect')?.selectedOptions[0];
            const bNm = bOpt ? (bOpt.getAttribute('data-nama') || bOpt.text.split(' - ')[1]?.split(' (')[0] || bOpt.text) : currentKomoditas;
            injectHiddenInput('nama_jenis', curNamaJenis || bNm);

            const gross = parseFloat(document.getElementById('bpQtyGross').value) || parseFloat(document.getElementById('inputJumlahPabrik').value) || 0;
            const reject = parseFloat(document.getElementById('bpQtyReject').value) || 0;
            const kesimpulan = document.querySelector('input[name="bp_kesimpulan"]:checked')?.value || 'TERIMA';

            injectHiddenInput('items[0][barang_id]', bId);
            injectHiddenInput('items[0][qty_timbang_gross]', gross);
            injectHiddenInput('items[0][qty_reject]', reject);
            injectHiddenInput('items[0][keputusan_qc]', kesimpulan === 'TERIMA' ? 'PASSED' : 'REJECT_TOTAL');
            injectHiddenInput('items[0][status_raw_material]', document.querySelector('input[name="bp_status_raw_material"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][isi_kering]', document.querySelector('input[name="bp_isi_kering"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][isi_basah]', document.querySelector('input[name="bp_isi_basah"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][isi_gumpal]', document.querySelector('input[name="bp_isi_gumpal"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][isi_berminyak]', document.querySelector('input[name="bp_isi_berminyak"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_kondisi]', document.querySelector('input[name="bp_kemasan_kondisi"]:checked')?.value || 'OK');
            injectHiddenInput('items[0][kemasan_kotor]', document.querySelector('input[name="bp_kemasan_kotor"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_apek]', document.querySelector('input[name="bp_kemasan_apek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_jamur]', document.querySelector('input[name="bp_kemasan_jamur"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][kemasan_sobek]', document.querySelector('input[name="bp_kemasan_sobek"]')?.checked ? 1 : 0);
            injectHiddenInput('items[0][catatan_dtl]', document.querySelector('textarea[name="bp_komentar"]')?.value || '');
            injectHiddenInput('catatan_umum', document.querySelector('textarea[name="bp_komentar"]')?.value || '');
        } else if (currentKomoditas === 'SINGKONG') {
            const firstSel = document.querySelector('.item-barang-select');
            const firstMat = firstSel ? RAW_MATERIALS.find(b => b.barang_id == firstSel.value) : null;
            injectHiddenInput('nama_jenis', curNamaJenis || (firstMat ? firstMat.barang_nm : 'Singkong Basah Curah'));
        }
    }

    function injectHiddenInput(name, value) {
        let el = document.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            document.getElementById('qcForm').appendChild(el);
        }
        el.value = value;
    }

    let isSubmitting = false;

    // Toast Notifikasi Validasi Interaktif (Apple Glass Red Theme)
    window.showQcToast = function (title, message, targetEl = null, tabNumber = null, isWarning = false) {
        let container = document.getElementById('qcToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'qcToastContainer';
            container.className = 'qc-toast-container';
            document.body.appendChild(container);
        }

        container.innerHTML = '';

        const toast = document.createElement('div');
        toast.className = `qc-toast ${isWarning ? 'qc-toast-warning' : ''}`;
        toast.innerHTML = `
            <div class="qc-toast-icon">${isWarning ? '⚠️' : '🚫'}</div>
            <div class="qc-toast-content">
                <div class="qc-toast-title">${title}</div>
                <div class="qc-toast-msg">${message}</div>
            </div>
            <button type="button" class="qc-toast-close" onclick="this.parentElement.remove()">✕</button>
        `;
        container.appendChild(toast);

        // Jika error berada pada tab tertentu, pindah otomatis ke tab tersebut
        if (tabNumber && typeof switchQcTab === 'function') {
            switchQcTab(tabNumber);
        }

        // Highlight field yang bermasalah dengan animasi getar merah
        if (targetEl) {
            setTimeout(() => {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetEl.classList.add('field-error-pulse');
                setTimeout(() => targetEl.classList.remove('field-error-pulse'), 2500);
                if (typeof targetEl.focus === 'function') targetEl.focus();
            }, tabNumber ? 200 : 0);
        }

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 250);
            }
        }, 4500);
    };

    function validateFormQc1() {
        // 1. Validasi Tahap 1: Rekanan Supplier / Produsen
        const supplierSelect = document.getElementById('supplierSelect');
        if (!supplierSelect || !supplierSelect.value) {
            showQcToast('Supplier Belum Dipilih', 'Silakan pilih Mitra Rekanan / Produsen pemasok bahan baku!', supplierSelect, 1);
            return false;
        }

        // 2. Validasi Tahap 1: Lokasi Gudang Bongkar
        const gudangSelect = document.getElementById('gudangSelect');
        if (!gudangSelect || !gudangSelect.value) {
            showQcToast('Gudang Belum Dipilih', 'Silakan pilih lokasi cabang / gudang tujuan pembongkaran!', gudangSelect, 1);
            return false;
        }

        // 3. Validasi Tahap 1: Kuantitas di Surat Jalan
        const inputJumlahSJ = document.getElementById('inputJumlahSJ');
        if (inputJumlahSJ) {
            const sjVal = parseFloat(inputJumlahSJ.value || 0);
            if (isNaN(sjVal) || sjVal <= 0) {
                showQcToast('Kuantitas Surat Jalan Kosong', 'Kuantitas kedatangan pada Surat Jalan wajib diisi dan lebih dari 0!', inputJumlahSJ, 1);
                return false;
            }
        }

        // 4. Validasi Tahap 2: Parameter Komoditas
        if (currentKomoditas === 'SINGKONG') {
            const itemCards = document.querySelectorAll('#singkongContainer .qc-item-card');
            if (itemCards.length === 0) {
                showQcToast('Item Singkong Kosong', 'Minimal harus ada 1 item komoditas singkong yang diinspeksi!', null, 2);
                return false;
            }

            for (let i = 0; i < itemCards.length; i++) {
                const card = itemCards[i];
                const selectBarang = card.querySelector('select[name^="items["][name$="[barang_id]"]');
                if (selectBarang && !selectBarang.value) {
                    showQcToast('Jenis Singkong Kosong', 'Pilih varian / jenis singkong pada kartu inspeksi!', selectBarang, 2);
                    return false;
                }

                const inputGross = card.querySelector('input[name^="items["][name$="[qty_gross]"]');
                if (inputGross) {
                    const gross = parseFloat(inputGross.value || 0);
                    if (isNaN(gross) || gross <= 0) {
                        showQcToast('Timbangan Kotor Kosong', 'Kuantitas timbangan kotor (gross kg) wajib diisi lebih dari 0!', inputGross, 2);
                        return false;
                    }
                }

                const dKurang = card.querySelector('input[name^="items["][name$="[diameter_kurang_4cm_persen]"]');
                const dLebih = card.querySelector('input[name^="items["][name$="[diameter_lebih_4cm_persen]"]');
                if (dKurang && dLebih) {
                    const vk = parseFloat(dKurang.value || 0);
                    const vl = parseFloat(dLebih.value || 0);
                    if (isNaN(vk) || vk < 0 || vk > 100 || isNaN(vl) || vl < 0 || vl > 100) {
                        showQcToast('Persentase Diameter Tidak Valid', 'Persentase diameter singkong harus berada di antara 0% hingga 100%!', dKurang, 2);
                        return false;
                    }
                }
            }

            // 5. Validasi Tahap 3: Uji Rasa Fryer & Kesimpulan Khusus Singkong
            const kesimpulanChecked = document.querySelector('input[name="kesimpulan_qc"]:checked')?.value || 'TERIMA';
            let hasPahit = false;
            document.querySelectorAll('select[name$="[fryer_rasa]"]').forEach(sel => {
                if (sel.value === 'PAHIT') hasPahit = true;
            });

            if (kesimpulanChecked === 'TERIMA' && hasPahit) {
                showQcToast('Singkong Pahit Dilarang Diterima!', 'Terdeteksi sampel singkong berasa PAHIT (racun sianida). Keputusan harus diubah ke TOLAK TOTAL!', document.getElementById('radioTolak'), 3);
                return false;
            }
        } else if (currentKomoditas === 'MINYAK') {
            const minyakSelect = document.getElementById('minyakBarangSelect');
            if (minyakSelect && !minyakSelect.value) {
                showQcToast('Item Minyak Kosong', 'Pilih item komoditas minyak goreng yang diuji!', minyakSelect, 2);
                return false;
            }
        } else {
            // Komoditas kemasan / bahan penolong
            const bpQty = document.getElementById('bpQtyGross');
            if (bpQty) {
                const qtyVal = parseFloat(bpQty.value || 0);
                if (isNaN(qtyVal) || qtyVal <= 0) {
                    showQcToast('Kuantitas Barang Kosong', 'Masukkan jumlah kuantitas bahan yang diterima di pabrik!', bpQty, 2);
                    return false;
                }
            }
        }

        // 6. Validasi Petugas QC
        const inputPetugasQc = document.querySelector('input[name="petugas_qc_nama"]');
        if (inputPetugasQc && !inputPetugasQc.value.trim()) {
            showQcToast('Petugas QC Kosong', 'Nama petugas pemeriksa QC wajib diisi!', inputPetugasQc, 3);
            return false;
        }

        return true;
    }

    function showSubmitLoading(title = 'Menyimpan Data QC...') {
        isSubmitting = true;
        const overlay = document.getElementById('qcSubmitOverlay');
        const overlayTitle = document.getElementById('overlayTitle');
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlay) overlay.style.display = 'flex';

        // Disable submit buttons to prevent accidental double-tap during network lag
        document.querySelectorAll('button[type="submit"], #btnSimpanCepat, #btnSubmitPengujian1, #dockBtnNext, #btnTopSimpanCepat').forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
            btn.style.cursor = 'not-allowed';
        });
    }

    function submitNonSingkong() {
        if (isSubmitting) return;

        if (!validateFormQc1()) {
            return;
        }

        prepareCommoditySubmission();
        document.getElementById('statusUjiGorengInput').value = 'SELESAI';
        document.getElementById('tahapUjiInput').value = 'PENGUJIAN_1';
        showSubmitLoading('Menyimpan & Meneruskan ke Gudang...');
        document.getElementById('qcForm').submit();
    }

    function onFryerRasaChanged(val) {
        const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
        const radioTerima = document.getElementById('radioTerima');
        const radioTolak = document.getElementById('radioTolak');
        const bannerWarning = document.getElementById('bannerPahitWarning');
        const textWarning = document.getElementById('textPahitWarning');
        const boxDiskusi = document.getElementById('boxDiskusiAtasan');
        const btnSubmit = document.getElementById('btnSubmitPengujian1');

        if (val === 'PAHIT') {
            if (radioTolak) radioTolak.checked = true;
            if (bannerWarning) bannerWarning.style.display = 'block';
            if (boxDiskusi && curTahap === 'PENGUJIAN_2') boxDiskusi.style.display = 'block';

            if (textWarning) {
                textWarning.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? 'Singkong terdeteksi rasa <strong>PAHIT</strong> pada lapisan dalam bak! Sesuai instruksi Direktur, sisa muatan di atas truk <strong>DITOLAK TOTAL</strong>. Pembongkaran dihentikan segera dan lakukan koordinasi/diskusi dengan atasan (QC Supervisor).'
                    : 'Singkong beracun sianida / tidak layak konsumsi pabrik. Keputusan otomatis dialihkan ke <strong>TOLAK TOTAL</strong>. Truk tidak diizinkan bongkar ke gudang dan Admin Gudang akan menerbitkan Berita Acara Penolakan.';
            }

            if (btnSubmit) {
                btnSubmit.style.background = '#dc2626';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2') 
                    ? '❌ Simpan Penolakan Pengujian 2 (Diskusi Atasan)' 
                    : '❌ Simpan Keputusan Penolakan (Ditolak Total)';
            }
            onKesimpulanChange('TOLAK');
        } else {
            // Cek apakah ada item lain yang masih PAHIT
            let hasAnyPahit = false;
            document.querySelectorAll('select[name$="[fryer_rasa]"]').forEach(sel => {
                if (sel.value === 'PAHIT') hasAnyPahit = true;
            });

            if (!hasAnyPahit) {
                if (radioTerima) radioTerima.checked = true;
                if (bannerWarning) bannerWarning.style.display = 'none';
                if (boxDiskusi) boxDiskusi.style.display = 'none';
                if (btnSubmit) {
                    btnSubmit.style.background = '#059669';
                    btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                        ? '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)'
                        : '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
                }
                onKesimpulanChange('TERIMA');
            }
        }
    }

    function onKesimpulanChange(val) {
        const curTahap = document.getElementById('tahapUjiInput')?.value || 'PENGUJIAN_1';
        const btnSubmit = document.getElementById('btnSubmitPengujian1');
        const bannerWarning = document.getElementById('bannerPahitWarning');
        const boxDiskusi = document.getElementById('boxDiskusiAtasan');

        if (val === 'TOLAK') {
            if (btnSubmit) {
                btnSubmit.style.background = '#dc2626';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2') 
                    ? '❌ Simpan Penolakan Pengujian 2 (Diskusi Atasan)' 
                    : '❌ Simpan Keputusan Penolakan (Ditolak Total)';
            }
            if (boxDiskusi && curTahap === 'PENGUJIAN_2') {
                boxDiskusi.style.display = 'block';
            }
        } else {
            if (bannerWarning) bannerWarning.style.display = 'none';
            if (boxDiskusi) boxDiskusi.style.display = 'none';
            if (btnSubmit) {
                btnSubmit.style.background = '#059669';
                btnSubmit.innerHTML = (curTahap === 'PENGUJIAN_2')
                    ? '💾 Simpan Pengujian 2 (Lolos &amp; Bongkar Tuntas ke Gudang)'
                    : '💾 Simpan Pengujian 1 (Selesai Inspeksi &amp; Siap Bongkar Setengah Bak)';
            }
        }
        recalculateAllCards();
    }

    function setDiskusiAction(action) {
        const radioTerima = document.getElementById('radioTerima');
        const radioTolak = document.getElementById('radioTolak');
        const commentEl = document.querySelector('textarea[name="catatan_umum"]');

        if (action === 'TOLAK_SISA') {
            if (radioTolak) {
                radioTolak.checked = true;
                onKesimpulanChange('TOLAK');
            }
            if (commentEl) {
                const note = "[PENGUJIAN 2 - DISKUSI ATASAN] Hasil sampling lapisan dalam tidak memenuhi standar (cacat/pahit). Sesuai diskusi dengan atasan, sisa muatan di atas truk DITOLAK TOTAL dan truk dipulangkan.";
                if (!commentEl.value.includes('[PENGUJIAN 2 - DISKUSI ATASAN]')) {
                    commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + note;
                }
            }
            showQcToast('Tolak Sisa Muatan Dipilih', 'Kesimpulan dialihkan ke TOLAK TOTAL berdasarkan hasil diskusi atasan.', radioTolak, 3, true);
        } else if (action === 'PENYESUAIAN_REFRAKSI') {
            if (radioTerima) {
                radioTerima.checked = true;
                onKesimpulanChange('TERIMA');
            }
            // Naikkan refraksi pada item 0 jika belum ada
            const refInput = document.getElementById('refraksi_0');
            if (refInput) {
                const curRef = parseFloat(refInput.value || 0);
                refInput.value = (curRef + 5.0).toFixed(1);
                calculateCard(0);
            }
            if (commentEl) {
                const note = "[PENGUJIAN 2 - DISKUSI ATASAN] Disetujui atasan untuk DITERIMA BERSYARAT dengan kompensasi penambahan potongan refraksi tanah/cacat afkir.";
                if (!commentEl.value.includes('[PENGUJIAN 2 - DISKUSI ATASAN]')) {
                    commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + note;
                }
            }
            showQcToast('Penyesuaian Refraksi', 'Potongan refraksi ditambahkan dan dicatat pada lembar berita acara.', null, 3);
        } else if (action === 'DOWNGRADE_B') {
            const gSel = document.getElementById('grade_select_0');
            if (gSel) {
                gSel.value = 'B';
                onGradeChanged(0, 'B');
            }
            if (commentEl) {
                const note = "[PENGUJIAN 2 - DISKUSI ATASAN] Kualitas singkong diturunkan menjadi Grade B atas persetujuan atasan/supervisor.";
                if (!commentEl.value.includes('[PENGUJIAN 2 - DISKUSI ATASAN]')) {
                    commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + note;
                }
            }
            showQcToast('Downgrade ke Grade B', 'Mutu singkong diubah ke Grade B. Periksa stok gudang.', null, 3, true);
        }
    }

    document.getElementById('qcForm').addEventListener('submit', function (e) {
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }

        // Validasi input sebelum proses simpan
        if (!validateFormQc1()) {
            e.preventDefault();
            return false;
        }

        prepareCommoditySubmission();
        document.getElementById('statusUjiGorengInput').value = 'SELESAI';
        if (!document.getElementById('tahapUjiInput').value) {
            document.getElementById('tahapUjiInput').value = 'PENGUJIAN_1';
        }
        
        const isTolak = document.querySelector('input[name="kesimpulan_qc"]:checked')?.value === 'TOLAK';
        showSubmitLoading(isTolak ? 'Menyimpan Keputusan Penolakan...' : 'Menyimpan Pengujian I & Menyiapkan Gudang...');
    });

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

        // Filter komoditas Singkong secara spesifik agar tidak tercampur barang minyak/plastik/karton
        let singkongMaterials = RAW_MATERIALS.filter(b => b.kategori === 'SINGKONG' || b.barang_nm.toUpperCase().includes('SINGKONG'));
        if (selectedBarangId) {
            const targetMat = RAW_MATERIALS.find(b => b.barang_id == selectedBarangId);
            if (targetMat && !singkongMaterials.some(b => b.barang_id == selectedBarangId)) {
                singkongMaterials.push(targetMat);
            }
        }
        const materialsToUse = singkongMaterials.length > 0 ? singkongMaterials : RAW_MATERIALS;

        let effectiveBarangId = selectedBarangId;
        if (!effectiveBarangId && materialsToUse.length > 0) {
            const defaultSk = materialsToUse.find(b => b.barang_cd === 'BB-SK001') || materialsToUse[0];
            effectiveBarangId = defaultSk.barang_id;
        }

        let barangOptions = '<option value="">-- Pilih Komoditas Singkong --</option>';
        materialsToUse.forEach(b => {
            const isSel = (b.barang_id == effectiveBarangId) ? 'selected' : '';
            barangOptions += `<option value="${b.barang_id}" data-nama="${b.barang_nm}" ${isSel}>${b.barang_cd} - ${b.barang_nm} (${b.satuan})</option>`;
        });

        card.innerHTML = `
            <input type="hidden" name="items[${idx}][podtl_id]" value="${podtlId}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <span style="font-weight: 800; font-size: 0.825rem; color: #0284c7; background: #e0f2fe; padding: 0.25rem 0.65rem; border-radius: 6px;">
                    Komoditas #${idx + 1}
                </span>
                <button type="button" onclick="removeItemCard(${idx})" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                    ✕ Hapus
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
                <div>
                    <label class="form-label" style="font-size: 0.825rem; font-weight: 700;">Nama Bahan Baku <span style="color:red;">*</span></label>
                    <select name="items[${idx}][barang_id]" id="barang_select_${idx}" class="form-control item-barang-select" required onchange="onItemBarangChanged(${idx})">
                        ${barangOptions}
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.825rem; font-weight: 700;">Grade Mutu Singkong <span style="color:red;">*</span></label>
                    <select name="items[${idx}][grade_cd]" id="grade_select_${idx}" class="form-control" style="font-weight: 800; color: #0f172a;" onchange="onGradeChanged(${idx}, this.value)">
                        <option value="A" selected>🟢 Grade A (Super / Renyah)</option>
                        <option value="B">🟡 Grade B (Standar / Campur)</option>
                    </select>
                </div>
            </div>

            {{-- ALERT NOTIFIKASI STOK KHUSUS GRADE B --}}
            <div id="gradeB_warning_${idx}" style="display: none; background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 8px; padding: 0.75rem 0.85rem; margin-bottom: 1rem; color: #92400e; font-size: 0.8rem;">
                <div style="font-weight: 800; display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span>⚠️</span> <span>PERINGATAN OPERASIONAL: SINGKONG GRADE B TERPILIH</span>
                    </span>
                    <span style="font-size: 0.7rem; font-weight: 700; background: #fef3c7; color: #b45309; padding: 2px 7px; border-radius: 4px;">
                        Stok Gudang: ${STOK_GRADE_B.toLocaleString('id-ID')} KG
                    </span>
                </div>
                <div style="line-height: 1.4;">
                    Stok Grade B di gudang saat ini tercatat <strong>${STOK_GRADE_B.toLocaleString('id-ID')} KG</strong>. 
                    Jika stok Grade B di gudang sudah banyak/menumpuk, instruksi atasan adalah <strong>MENOLAK KEDATANGAN INI</strong> atau meminta konfirmasi QC Supervisor terlebih dahulu sebelum dibongkar.
                </div>
                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" onclick="quickRejectGradeB(${idx})" style="background: #dc2626; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.35rem 0.65rem; border-radius: 6px; cursor: pointer;">
                        🚨 Tolak Truk Ini (Stok Grade B Penuh)
                    </button>
                    <button type="button" onclick="document.getElementById('grade_select_${idx}').value='A'; onGradeChanged(${idx}, 'A');" style="background: #ffffff; color: #15803d; border: 1px solid #86efac; font-size: 0.75rem; font-weight: 700; padding: 0.35rem 0.65rem; border-radius: 6px; cursor: pointer;">
                        Kembalikan ke Grade A
                    </button>
                </div>
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

        document.getElementById('singkongContainer').appendChild(card);
        renderFryerParamsForCard(idx);
        calculateCard(idx);

        if (effectiveBarangId && currentKomoditas === 'SINGKONG') {
            const chosenMat = materialsToUse.find(b => b.barang_id == effectiveBarangId) || RAW_MATERIALS.find(b => b.barang_id == effectiveBarangId);
            const namaEl = document.getElementById('namaJenisInput');
            if (namaEl && chosenMat && (idx === 0 || !namaEl.value || namaEl.value === 'Singkong Basah Curah')) {
                namaEl.value = chosenMat.barang_nm;
            }
        }
    }

    function onGradeChanged(idx, val) {
        const warnEl = document.getElementById(`gradeB_warning_${idx}`);
        if (warnEl) {
            warnEl.style.display = (val === 'B') ? 'block' : 'none';
        }
        if (val === 'B' && STOK_GRADE_B > 1000) {
            showQcToast('Stok Grade B Tinggi', `Stok Grade B di gudang saat ini ${STOK_GRADE_B.toLocaleString('id-ID')} KG. Periksa kapasitas gudang sebelum menerima.`, warnEl, 2, true);
        }
    }

    function quickRejectGradeB(idx) {
        const radioTolak = document.getElementById('radioTolak');
        if (radioTolak) {
            radioTolak.checked = true;
            onKesimpulanChange('TOLAK');
        }
        const commentEl = document.querySelector('textarea[name="catatan_umum"]');
        if (commentEl) {
            const rejectMsg = `[TOLAK TOTAL] Kedatangan Singkong Grade B ditolak karena stok Grade B di gudang sudah penuh/menumpuk (${STOK_GRADE_B.toLocaleString('id-ID')} KG).`;
            if (!commentEl.value.includes('[TOLAK TOTAL]')) {
                commentEl.value = (commentEl.value ? commentEl.value + "\n" : '') + rejectMsg;
            }
        }
        showQcToast('Keputusan Dialihkan ke Tolak', 'Status QC diubah menjadi TOLAK TOTAL karena kapasitas Grade B penuh.', null, 3, true);
        switchQcTab(3);
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
                🍟 Uji Cepat Rasa Fryer (Di Depan) &bull; ${barangNm}
            </div>

            {{-- HASIL SENSORI FRYER --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.85rem; margin-bottom: 1rem;">
                <div>
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">RASA (Standar: Wajib Tidak Pahit)</label>
                    <select name="items[${idx}][fryer_rasa]" class="form-control" onchange="onFryerRasaChanged(this.value)" style="font-size: 0.85rem; font-weight: 700;">
                        <option value="TIDAK_PAHIT" selected>✅ Gurih / Tidak Pahit (Standar)</option>
                        <option value="PAHIT">❌ Pahit (Reject / Tolak Total)</option>
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

        const sel = document.getElementById(`barang_select_${idx}`);
        if (sel) {
            const opt = sel.selectedOptions[0];
            const matNama = opt?.getAttribute('data-nama') || (opt?.text.split(' - ')[1]?.split(' (')[0]);
            const namaEl = document.getElementById('namaJenisInput');
            if (namaEl && matNama) {
                namaEl.value = matNama.trim();
            }
        }
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

        if (currentKomoditas === 'SINGKONG' && idx === 0) {
            const inputPabrik = document.getElementById('inputJumlahPabrik');
            if (inputPabrik && gross > 0 && document.activeElement === grossInput) {
                inputPabrik.value = gross;
            }
        }

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

        const kesimpulanEl = document.querySelector('input[name="kesimpulan_qc"]:checked');
        const isTolakTotal = kesimpulanEl && kesimpulanEl.value === 'TOLAK';

        if (isTolakTotal) {
            document.getElementById('summaryGross').innerText = `${totalGross.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
            document.getElementById('summaryRefraksi').innerText = `- 0.00 KG`;
            document.getElementById('summaryReject').innerText = `- ${totalGross.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
            document.getElementById('summaryNetto').innerText = `0.00 KG (DITOLAK)`;
            document.getElementById('summaryNetto').style.color = '#dc2626';
        } else {
            document.getElementById('summaryGross').innerText = `${totalGross.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
            document.getElementById('summaryRefraksi').innerText = `- ${totalRefraksi.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
            document.getElementById('summaryReject').innerText = `- ${totalReject.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
            document.getElementById('summaryNetto').innerText = `${totalNetto.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} KG`;
            document.getElementById('summaryNetto').style.color = '#15803d';
        }
    }

    function onPoSelected(select) {
        const poId = select.value;
        if (!poId || !PO_LIST[poId]) {
            return;
        }

        const po = PO_LIST[poId];
        if (po.supplier_id) {
            const sSelect = document.getElementById('supplierSelect');
            if (sSelect) {
                setSupplierCategoryFilter('ALL');
                sSelect.value = po.supplier_id;
                onSupplierSelected(sSelect);
            }
        }
        if (po.gudang_id) {
            document.getElementById('gudangSelect').value = po.gudang_id;
        }

        if (po.items && po.items.length > 0) {
            const firstItemNm = (po.items[0].barang_nm || '').toLowerCase();
            const firstItemBarangNm = po.items[0].barang_nm || '';
            const namaEl = document.getElementById('namaJenisInput');

            if (firstItemNm.includes('minyak')) {
                selectKomoditas('MINYAK');
                const mSelect = document.getElementById('minyakBarangSelect');
                if (mSelect) {
                    mSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(mSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('minyakQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('karton') || firstItemNm.includes('dus') || firstItemNm.includes('box')) {
                selectKomoditas('KARTON');
                const kSelect = document.getElementById('kartonBarangSelect');
                if (kSelect) {
                    kSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(kSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('kartonQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('plastik') || firstItemNm.includes('kemasan') || firstItemNm.includes('opp') || firstItemNm.includes('pp')) {
                selectKomoditas('PLASTIK');
                const pSelect = document.getElementById('plastikBarangSelect');
                if (pSelect) {
                    pSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(pSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('plastikQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('msg') || firstItemNm.includes('micin') || firstItemNm.includes('glutamat')) {
                selectKomoditas('MSG');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(bpSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('bpQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('garam') || firstItemNm.includes('salt')) {
                selectKomoditas('GARAM');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(bpSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('bpQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('perenyah')) {
                selectKomoditas('PERENYAH');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(bpSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('bpQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('bumbu') || firstItemNm.includes('balado') || firstItemNm.includes('chilli') || firstItemNm.includes('seasoning')) {
                selectKomoditas('PERENYAH');
                const bpSelect = document.getElementById('bpBarangSelect');
                if (bpSelect) {
                    bpSelect.value = po.items[0].barang_id;
                    syncNamaRmFromSelect(bpSelect);
                }
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('bpQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else {
                selectKomoditas('SINGKONG');
                document.getElementById('singkongContainer').innerHTML = '';
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

            if (namaEl && firstItemBarangNm) {
                namaEl.value = firstItemBarangNm;
            }
        }
        if (select) {
            select.value = poId;
        }
    }

    // Inisialisasi awal saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function () {
        selectKomoditas('{{ old("kategori_barang", $initialKomoditas ?? "SINGKONG") }}');
        updateFloatingDock(1);

        const defTahap = '{{ old("tahap_uji", $defaultTahap ?? "PENGUJIAN_1") }}';
        selectTahapUji(defTahap);

        @if(!empty($parentQc))
            const p1Select = document.getElementById('selectPendingP1');
            if (p1Select && p1Select.value) {
                onSelectPendingArrival(p1Select);
            } else {
                createItemCard({
                    gross: {{ (float) ($parentQc->details->first()?->qty_timbang_gross ?? $parentQc->jumlah_di_pabrik) }},
                    barang_id: '{{ $parentQc->details->first()?->barang_id }}',
                    grade_cd: '{{ $parentQc->details->first()?->grade_cd ?? "A" }}',
                    refraksi_persen: '{{ (float) ($parentQc->details->first()?->refraksi_persen ?? 0) }}',
                });
            }
        @else
            const poSelect = document.getElementById('poSelect');
            if (poSelect && poSelect.value) {
                onPoSelected(poSelect);
            } else {
                createItemCard();
            }
        @endif
    });
</script>
@endsection
