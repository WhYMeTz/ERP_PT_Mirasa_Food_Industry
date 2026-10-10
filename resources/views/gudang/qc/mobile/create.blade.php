@extends('layouts.qc-mobile')

@section('title', 'Form Uji QC Bahan Masuk (HACCP 7 Komoditas) - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/singkong/singkong.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/minyak/minyak.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/plastik/plastik.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/karton/karton.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong.css') }}">
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

    $curKomoditas = old('kategori_barang', $initialKomoditas ?? 'SINGKONG');
    $isMinyak = ($curKomoditas === 'MINYAK');
    $isSingkong = ($curKomoditas === 'SINGKONG');
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
                    <span id="badgeDocNo" style="display: {{ $isMinyak ? 'inline-block' : 'none' }}; background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: {{ $isMinyak ? 'MFI/HACCP-04/FRM-03/029/VIII/2021' : '' }}
                    </span>
                </div>
                <h1 id="titleFormHaccp" style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    {{ $isMinyak ? 'Sampling Mutu Minyak Goreng' : 'Sampling Mutu Singkong' }}
                </h1>
                <p id="descFormHaccp" style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    {{ $isMinyak ? 'Pemeriksaan FFA di COA, Kebersihan Tangki & Suhu' : 'Pencatatan sampling mutu kedatangan bahan baku di lapangan' }}
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
            <button type="button" onclick="selectKomoditas('SINGKONG')" class="btn komoditas-btn {{ $isSingkong ? 'active-komoditas' : '' }}" id="btnKomoditas_SINGKONG" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: {{ $isSingkong ? '2px solid #0284c7' : '1.5px solid #cbd5e1' }}; background: {{ $isSingkong ? '#0284c7' : '#ffffff' }}; color: {{ $isSingkong ? '#ffffff' : '#334155' }}; text-align: center; cursor: pointer; transition: all 0.15s;">
                🥔 Singkong
            </button>
            <button type="button" onclick="selectKomoditas('MINYAK')" class="btn komoditas-btn {{ $isMinyak ? 'active-komoditas' : '' }}" id="btnKomoditas_MINYAK" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: {{ $isMinyak ? '2px solid #0284c7' : '1.5px solid #cbd5e1' }}; background: {{ $isMinyak ? '#0284c7' : '#ffffff' }}; color: {{ $isMinyak ? '#ffffff' : '#334155' }}; text-align: center; cursor: pointer; transition: all 0.15s;">
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

    {{-- PILIH TAHAP PENGUJIAN QC SINGKONG (PENGUJIAN 1 ATAU PENGUJIAN 2 - KHUSUS SINGKONG) --}}
    <div id="groupTahapPengujian" style="display: {{ $isSingkong ? 'block' : 'none' }}; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 0.95rem 1.15rem; margin-bottom: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.6rem; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 0.35rem;">
                <span>🔬</span> <span>Tahap Pengujian Singkong :</span>
            </span>
            <span id="labelTahapBadge" style="font-size: 0.75rem; color: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#7e22ce' : '#0284c7' }}; font-weight: 800; background: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#f3e8ff' : '#e0f2fe' }}; padding: 2px 10px; border-radius: 6px;">
                {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '🍟 Pengujian 2 (Lanjutan)' : '🚛 Pengujian 1 (Awal)' }}
            </span>
        </div>
        <div class="qc-tahap-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
            <button type="button" onclick="selectTahapUji('PENGUJIAN_1')" class="btn" id="btnTahap_1" style="font-size: 0.85rem; font-weight: 800; padding: 0.75rem 0.6rem; border-radius: 10px; border: 2px solid {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? '#0284c7' : '#cbd5e1' }}; background: {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? '#0284c7' : '#ffffff' }}; color: {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? '#ffffff' : '#334155' }}; text-align: center; cursor: pointer; transition: all 0.15s; display: flex; flex-direction: column; align-items: center; gap: 0.15rem;">
                <span>🚛 PENGUJIAN 1</span>
                <span style="font-size: 0.7rem; font-weight: 600; opacity: 0.9;">Awal (Setengah Bak 1)</span>
            </button>
            <button type="button" onclick="selectTahapUji('PENGUJIAN_2')" class="btn" id="btnTahap_2" style="font-size: 0.85rem; font-weight: 800; padding: 0.75rem 0.6rem; border-radius: 10px; border: 2px solid {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#9333ea' : '#cbd5e1' }}; background: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#9333ea' : '#ffffff' }}; color: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#ffffff' : '#334155' }}; text-align: center; cursor: pointer; transition: all 0.15s; display: flex; flex-direction: column; align-items: center; gap: 0.15rem;">
                <span>🍟 PENGUJIAN 2</span>
                <span style="font-size: 0.7rem; font-weight: 600; opacity: 0.9;">Lanjutan (Sisa Bak 2)</span>
            </button>
        </div>
        <div id="descTahapUji" style="font-size: 0.775rem; color: #475569; margin-top: 0.65rem; background: #f8fafc; border-radius: 8px; padding: 0.6rem 0.85rem; border-left: 3px solid {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '#9333ea' : '#0284c7' }}; line-height: 1.45;">
            @if (($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2')
                🍟 <strong>Pengujian 2:</strong> Pengujian lanjutan saat pembongkaran sisa setengah bak kedua. Sampel cuplikan ~7 kg dari lapisan bawah/dalam bak untuk verifikasi sebelum bongkar tuntas.
            @else
                🚛 <strong>Pengujian 1:</strong> Pengujian awal saat truk singkong tiba di pos penerimaan. Sampel cuplikan ~7 kg dari lapisan awal/atas bak sebelum mulai pembongkaran.
            @endif
        </div>

        {{-- AUTO-FILL DARI KEDATANGAN PENGUJIAN 1 (KHUSUS PENGUJIAN 2) --}}
        <div id="wrapPendingP1" style="display: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'block' : 'none' }}; margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1.5px dashed #cbd5e1;">
            <div style="background: #faf5ff; border: 1.5px solid #d8b4fe; border-radius: 10px; padding: 0.85rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.3rem;">
                    <label style="font-size: 0.825rem; font-weight: 800; color: #581c87; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                        <span>🚚</span> <span>Pilih Truk Kedatangan Hari Ini:</span>
                    </label>
                    <span style="font-size: 0.7rem; font-weight: 800; color: #7e22ce; background: #ede9fe; padding: 2px 8px; border-radius: 6px;">
                        {{ isset($pendingPengujian1) ? $pendingPengujian1->count() : 0 }} Truk Siap Uji 2
                    </span>
                </div>
                <select id="selectPendingP1" class="form-control" style="font-size: 0.85rem; font-weight: 700; border-color: #c084fc; background-color: #ffffff;" onchange="onSelectPendingArrival(this)">
                    <option value="">-- Pilih Truk Kedatangan (Auto-fill Data Uji 1) --</option>
                    @if(isset($pendingPengujian1))
                        @foreach($pendingPengujian1 as $p1)
                            @php
                                $firstDtl = $p1->details->first();
                                $p1Data = [
                                    'qc_id'        => $p1->qc_id,
                                    'qc_no'        => $p1->qc_no,
                                    'po_id'        => $p1->po_id,
                                    'supplier_id'  => $p1->supplier_id,
                                    'supplier_nm'  => $p1->supplier?->supplier_nm,
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
                                $optSjQty = (float) $p1->jumlah_surat_jalan;
                                $optGross = (float) ($firstDtl?->qty_timbang_gross ?? $p1->jumlah_di_pabrik);
                                $optIsFull = ($optSjQty > 0 && $optGross >= $optSjQty);
                                $optSisa = max(0, $optSjQty - $optGross);
                            @endphp
                            <option value="{{ $p1->qc_id }}"
                                data-json="{{ json_encode($p1Data) }}"
                                {{ ($parentQc && $parentQc->qc_id == $p1->qc_id) ? 'selected' : '' }}>
                                #{{ $p1->qc_no }} &bull; {{ $p1->supplier?->supplier_nm }} &bull; 🚛 {{ $p1->plat_nomor_truk ?: 'Plat -' }} 
                                @if($optIsFull)
                                    [✅ Sudah Turun Penuh {{ number_format($optGross, 0, ',', '.') }} kg / Sisa 0 kg]
                                @elseif($optSjQty > 0)
                                    [SJ: {{ number_format($optSjQty, 0, ',', '.') }} kg &bull; Sisa Bak: {{ number_format($optSisa, 0, ',', '.') }} kg]
                                @else
                                    [Uji 1: {{ number_format($optGross, 0, ',', '.') }} kg]
                                @endif
                            </option>
                        @endforeach
                    @endif
                </select>

                <div id="promptSelectP1" style="display: {{ $parentQc ? 'none' : 'block' }}; margin-top: 0.5rem; font-size: 0.75rem; color: #7e22ce; background: #ffffff; border: 1px dashed #d8b4fe; border-radius: 6px; padding: 0.45rem 0.65rem;">
                    💡 Pilih truk di atas agar data surat jalan, supir, plat nomor, dan petani otomatis terisi dari Pengujian 1.
                </div>

                <div id="bannerSelectedP1" style="display: {{ $parentQc ? 'flex' : 'none' }}; flex-direction: column; gap: 0.45rem; background: #ffffff; border: 1.5px solid #a855f7; border-radius: 8px; padding: 0.75rem 0.85rem; margin-top: 0.65rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #6b21a8; display: flex; align-items: center; gap: 0.35rem;">
                            <span>🔗</span> <span>TERHUBUNG DENGAN TRUK PENGUJIAN 1</span>
                        </span>
                        <button type="button" onclick="clearSelectedPendingArrival()" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.72rem; font-weight: 700; border-radius: 4px; padding: 2px 8px; cursor: pointer;">
                            ✕ Lepas / Ganti
                        </button>
                    </div>
                    <div id="labelSelectedP1Desc" style="font-size: 0.8rem; color: #3b0764; font-weight: 600; line-height: 1.4;">
                        @if($parentQc)
                            <strong>#{{ $parentQc->qc_no }}</strong> &bull; 🚛 {{ $parentQc->plat_nomor_truk ?: 'Plat -' }} &bull; {{ $parentQc->supplier?->supplier_nm }}
                        @endif
                    </div>
                </div>
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
    <div style="display: grid; grid-template-columns: {{ $isMinyak ? 'repeat(2, 1fr)' : 'repeat(3, 1fr)' }}; gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchQcTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #0284c7; background: #0284c7; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchQcTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            {{ $isMinyak ? '🛢️ 2. Mutu Minyak & FFA' : '📏 2. Fisik & Diameter' }}
        </button>
        <button type="button" class="btn" id="tabBtn3" onclick="switchQcTab(3)" style="display: {{ $isMinyak ? 'none' : 'block' }}; border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🍟 3. Uji Rasa &amp; Keputusan
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="{{ old('kategori_barang', $curKomoditas) }}">
        <input type="hidden" name="status_uji_goreng" id="statusUjiGorengInput" value="SELESAI">
        <input type="hidden" name="tahap_uji" id="tahapUjiInput" value="{{ old('tahap_uji', $defaultTahap ?? 'PENGUJIAN_1') }}">
        <input type="hidden" name="parent_qc_id" id="parentQcIdInput" value="{{ old('parent_qc_id', $parentQc?->qc_id) }}">
        <input type="hidden" name="batch_no" id="batchNoInput" value="{{ old('batch_no', $parentQc?->batch_no) }}">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" class="card" style="display: flex !important; flex-direction: column !important; width: 100% !important; border-radius: 14px; border-top: 5px solid #0284c7; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; width: 100%; box-sizing: border-box;">
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.45rem; margin-bottom: 0.2rem;">
                        <span style="font-size: 0.7rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;" id="badgeTab1Step">
                            {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'PENGUJIAN 2 • LANJUTAN' : 'PENGUJIAN 1 • AWAL' }}
                        </span>
                        <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;" id="titleTab1Header">Laporan Kedatangan &amp; Armada</h2>
                    </div>
                    <span style="font-size: 0.775rem; color: #64748b; display: block; line-height: 1.35;" id="descTab1Header">Surat jalan, timbangan muatan, rekanan &amp; audit kebersihan armada</span>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="switchQcTab(2)" style="border-radius: 8px; font-weight: 700; padding: 0.45rem 0.85rem; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.35rem; flex-shrink: 0;" title="Lanjut ke Pemeriksaan Parameter (Tahap 2)">
                    <span>Lanjut</span> &rarr;
                </button>
            </div>

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.1rem; width: 100%; box-sizing: border-box;">
                {{-- PILIH DARI PO AKTIF DENGAN SMART FILTER --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                        <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                            📄 Referensi Purchase Order (PO) <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">(Opsional - auto-fill data)</span>
                        </label>
                        <span id="poFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">
                            {{ $isMinyak ? 'Filter: 🛢️ Minyak Goreng' : 'Filter: 🥔 Singkong & Bahan Baku' }}
                        </span>
                    </div>

                    {{-- FILTER CHIPS & QUICK SEARCH UNTUK PO --}}
                    <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                        <div class="supplier-filter-chips" id="poFilterChips">
                            <button type="button" class="btn-supplier-chip {{ $isMinyak ? '' : 'active-chip' }}" id="chipPo_AUTO" onclick="setPoCategoryFilter('AUTO')">
                                ✨ Sesuai Komoditas
                            </button>
                            <button type="button" class="btn-supplier-chip" id="chipPo_SINGKONG" onclick="setPoCategoryFilter('SINGKONG')">
                                🥔 Singkong &amp; Bahan Baku
                            </button>
                            <button type="button" class="btn-supplier-chip {{ $isMinyak ? 'active-chip' : '' }}" id="chipPo_MINYAK" onclick="setPoCategoryFilter('MINYAK')">
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

                {{-- 2. NOMOR DOKUMEN: SURAT JALAN, NOMOR DO & WAKTU KEDATANGAN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">No. Surat Jalan <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="No Surat Jalan..." value="{{ old('surat_jalan_supplier') }}" style="font-weight: 700;">
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

                {{-- 3. MITRA SUPPLIER / PETANI & GUDANG TUJUAN BONGKAR --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    {{-- DROPDOWN MITRA SUPPLIER / PRODUSEN DENGAN FILTER CEPAT --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap; gap: 0.25rem;">
                            <label class="form-label" style="font-weight: 800; margin: 0; color: #0f172a;">
                                🏢 Mitra Supplier / Produsen <span style="color:#ef4444;">*</span>
                            </label>
                            <span id="supplierFilterBadge" style="font-size: 0.7rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">
                                {{ $isMinyak ? 'Filter: 🏭 Vendor Minyak' : 'Filter: Singkong' }}
                            </span>
                        </div>

                        {{-- FILTER CHIPS & QUICK SEARCH --}}
                        <div style="display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.45rem;">
                            <div class="supplier-filter-chips" id="supplierFilterChips">
                                <button type="button" class="btn-supplier-chip {{ $isMinyak ? '' : 'active-chip' }}" id="chipSupplier_AUTO" onclick="setSupplierCategoryFilter('AUTO')">
                                    ✨ Sesuai Komoditas
                                </button>
                                <button type="button" class="btn-supplier-chip" id="chipSupplier_RAW" onclick="setSupplierCategoryFilter('RAW')">
                                    🌾 Petani Singkong (35)
                                </button>
                                <button type="button" class="btn-supplier-chip {{ $isMinyak ? 'active-chip' : '' }}" id="chipSupplier_VENDOR" onclick="setSupplierCategoryFilter('VENDOR')">
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
                    </div>
                </div>

                {{-- IDENTITAS BAHAN BAKU / VARIETAS SINGKONG --}}
                <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <label class="form-label" style="font-weight: 800; margin: 0;" id="labelNamaJenis">{{ $isMinyak ? 'NAMA JENIS (Varian / Fraksi Minyak) :' : ($isSingkong ? '🌱 NAMA JENIS / VARIETAS SINGKONG :' : 'NAMA JENIS :') }}</label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px;">Form HACCP MFI</span>
                        </div>
                        <input type="text" name="nama_jenis" id="namaJenisInput" list="listGlobalNamaJenis" class="form-control" style="font-weight: 800;" placeholder="{{ $isSingkong ? 'Contoh: Singkong Segar / Ketan / Gajah' : 'Contoh: RBD Palm Olein CP8 / Curah Sawit / Filma' }}" value="{{ old('nama_jenis', $isMinyak ? 'RBD Palm Olein / Curah Sawit' : ($isSingkong ? 'Singkong Segar' : '')) }}" oninput="if (document.getElementById('minyakNamaJenisInput')) document.getElementById('minyakNamaJenisInput').value = this.value;">
                        <datalist id="listGlobalNamaJenis">
                            <option value="Singkong Segar (Basah)">
                            <option value="Singkong Ketan">
                            <option value="Singkong Gajah">
                            <option value="Singkong Manis">
                            <option value="Singkong Mentega">
                            <option value="Singkong Manggu">
                            <option value="RBD Palm Olein (CP8)">
                            <option value="RBD Palm Olein (CP10)">
                            <option value="Minyak Curah Kelapa Sawit">
                            <option value="Minyak Kelapa (RBD CNO)">
                            <option value="Minyak Sawit Super (Filma)">
                            <option value="Minyak Sawit Jerigen Sania">
                            <option value="Minyak Goreng SunCo">
                            <option value="Minyak Goreng Bimoli">
                            <option value="Minyak Goreng Kunci Mas">
                        </datalist>
                        <span style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: block;">Spesifikasi teknis, grade fraksinasi, varian atau merk fisik kemasan</span>
                    </div>
                </div>

                {{-- NAMA PRODUSEN & NEGARA PRODUSEN (KHUSUS KOMODITAS VENDOR / NON-SINGKONG) --}}
                <div id="wrapProdusenNegara" style="display: {{ ($isMinyak || ($curKomoditas ?? '') !== 'SINGKONG') ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.85rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Produsen (Pabrik Pembuat)</label>
                        <input type="text" name="nama_produsen" id="namaProdusenInput" class="form-control" placeholder="Contoh: PT. SMART Tbk / Wilmar / Musim Mas" value="{{ old('nama_produsen') }}">
                        <span style="font-size: 0.7rem; color: #64748b; margin-top: 2px; display: block;">Terisi otomatis dari supplier (dapat diubah jika produsennya berbeda)</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Negara Produsen</label>
                        <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}">
                    </div>
                </div>

                {{-- 4. ARMADA: PLAT TRUK & SOPIR --}}
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

                {{-- 5. KUANTITAS KEDATANGAN: SURAT JALAN, PABRIK, SAMPLE --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;" id="labelJumlahSJ">
                            {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '📄 Jumlah di Surat Jalan (KG)' : '📄 Jumlah di Surat Jalan (KG) *' }}
                        </label>
                        <input type="number" step="0.01" min="0" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="0.00" value="{{ old('jumlah_surat_jalan') }}" oninput="syncQuantityFields()" style="font-weight: 800; font-size: 1.05rem;">
                        <span id="hintJumlahSJ" style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'Total muatan seluruh armada pada Surat Jalan (auto-fill dari Uji 1).' : 'Total muatan seluruh armada pada Surat Jalan (contoh: 7.000 KG).' }}
                        </span>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;" id="labelJumlahPabrik">
                            {{ $isMinyak ? 'Jumlah di Pabrik (Netto Masuk Pabrik)' : (($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '⚖️ Muatan Uji 2 / Sisa Bak (KG) *' : '⚖️ Muatan Uji 1 / Setengah Bak 1 (KG) *') }}
                        </label>
                        <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="0.00" value="{{ old('jumlah_di_pabrik') }}" oninput="syncQuantityFields()" style="font-weight: 800; font-size: 1.05rem; color: #0369a1;">
                        <span id="hintJumlahPabrik" style="font-size: 0.72rem; color: #0284c7; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            {{ $isMinyak ? 'Timbangan muatan tangki / jerigen minyak yang masuk pabrik (KG).' : (($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'Pengujian 2: Sisa muatan setengah bak yang dibongkar tuntas (contoh: 3.500 KG).' : 'Pengujian 1: Muatan setengah bak pertama yang turun ditimbang (contoh: 3.500 KG).') }}
                        </span>
                    </div>

                    {{-- Dynamic Sample Input berdasarkan komoditas --}}
                    <div class="form-group" id="groupSampleKg" style="display: {{ $isMinyak ? 'none' : 'flex' }}; margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">
                            ⚖️ Berat Sampel Uji (KG) <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="number" step="0.1" min="0.1" name="jumlah_sample_kg" id="inputJumlahSampleKg" class="form-control" placeholder="7.0" value="{{ old('jumlah_sample_kg', 7.0) }}" style="font-weight: 800; font-size: 1.05rem; color: #059669;">
                        <span id="hintSampleKg" style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'Cuplikan sampel ~7.0 kg dari lapisan dalam/bawah bak yang tersisa.' : 'Format standar 7.0 KG (sampel gabungan cuplikan bak depan, tengah, belakang).' }}
                        </span>
                    </div>

                    <div class="form-group" id="groupSampleGr" style="display: {{ $isMinyak ? 'flex' : 'none' }}; margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">
                            🧪 Jumlah Sampel Uji Lab (gr)
                        </label>
                        <input type="number" step="1" name="jumlah_sample_gr" class="form-control" placeholder="250" value="{{ old('jumlah_sample_gr', 250) }}" style="font-weight: 700;">
                        <span style="font-size: 0.72rem; color: #64748b; margin-top: 0.25rem; display: block; line-height: 1.35;">
                            Format standar <strong>250 gram</strong> (cuplikan lab tangki/jerigen).
                        </span>
                    </div>

                    <div class="form-group" id="groupSamplePcs" style="display: none; margin-bottom: 0;">
                        <label class="form-label">Jumlah Sample (pcs)</label>
                        <input type="number" step="1" name="jumlah_sample_pcs" class="form-control" placeholder="Contoh: 50" value="{{ old('jumlah_sample_pcs', 50) }}">
                    </div>
                </div>

                {{-- 6. KHUSUS SINGKONG: LOKASI PANEN, UMUR SINGKONG, TANGGAL PANEN (ISOLATED PARTIAL) --}}
                @include('gudang.qc.partials.singkong.form-singkong-panen')

                {{-- ========================================================================= --}}
                {{-- STANDAR AUDIT TRANSPORTASI & 4 PERTANYAAN JAMINAN HALAL (FORM RESMI HACCP) --}}
                {{-- ========================================================================= --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 1.1rem; margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.95rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; flex-wrap: wrap; gap: 0.35rem;">
                        <span style="font-weight: 800; font-size: 0.9rem; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                            <span>🛡️</span> <span>Standar Kebersihan Transportasi &amp; Audit Halal</span>
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">
                            Wajib Terpenuhi (HACCP MFI)
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        {{-- 1. KONDISI TRANSPORTASI --}}
                        <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                            <div style="font-size: 0.825rem; font-weight: 800; color: #1e293b;">
                                1. Kondisi Fisik Bak Truk / Transportasi :
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 150px;">
                                    <input type="radio" name="bebas_cemaran_st" value="1" checked style="accent-color: #15803d;">
                                    <span style="color: #15803d;">✅ Bebas Cemaran / Najis</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 150px;">
                                    <input type="radio" name="bebas_cemaran_st" value="0" style="accent-color: #dc2626;">
                                    <span style="color: #dc2626;">❌ Ada Kotoran / Oli / Najis</span>
                                </label>
                            </div>
                        </div>

                        {{-- 2. DIANGKUT BERSAMA BARANG HARAM --}}
                        <div style="display: flex; flex-direction: column; gap: 0.45rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                            <div style="font-size: 0.825rem; font-weight: 800; color: #1e293b;">
                                2. Apakah singkong diangkut bersamaan dengan barang haram / najis?
                            </div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 120px;">
                                    <input type="radio" name="angkut_barang_haram_st" value="0" checked style="accent-color: #15803d;">
                                    <span style="color: #15803d;">🟢 Tidak (Aman / Halal)</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 120px;">
                                    <input type="radio" name="angkut_barang_haram_st" value="1" style="accent-color: #dc2626;">
                                    <span style="color: #dc2626;">🔴 Ya (Tercampur)</span>
                                </label>
                            </div>
                            <div style="margin-top: 0.25rem;">
                                <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar / catatan kebersihan transportasi (opsional)..." style="font-size: 0.825rem; min-height: 42px;">
                            </div>
                        </div>

                        {{-- 3, 4, 5: AUDIT HALAL SERTIFIKASI (KHUSUS KOMODITAS OLAHAN / NON-SINGKONG SESUAI DOKUMEN RESMI HACCP) --}}
                        <div id="auditHalalNonSingkong" style="display: none; flex-direction: column; gap: 0.95rem;">
                            {{-- 3. TERDAFTAR & DISETUJUI LPPOM MUI / BPJPH --}}
                            <div style="display: flex; flex-direction: column; gap: 0.45rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                                <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                    3. Apakah bahan tersebut terdaftar &amp; disetujui oleh LPPOM MUI/BPJPH?
                                </div>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 100px;">
                                        <input type="radio" name="terdaftar_lppom_st" value="1" checked style="accent-color: #15803d;">
                                        <span style="color: #15803d;">Ya</span>
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 100px;">
                                        <input type="radio" name="terdaftar_lppom_st" value="0" style="accent-color: #dc2626;">
                                        <span style="color: #dc2626;">Tidak</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="komentar_lppom" class="form-control" placeholder="Komentar LPPOM (opsional)..." style="font-size: 0.825rem; min-height: 42px;">
                                </div>
                            </div>

                            {{-- 4. MEMPUNYAI SERTIFIKAT HALAL --}}
                            <div style="display: flex; flex-direction: column; gap: 0.45rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                                <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                    4. Apakah barang tersebut mempunyai sertifikat halal?
                                </div>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 100px;">
                                        <input type="radio" name="ada_sertifikat_halal_st" value="1" checked style="accent-color: #15803d;">
                                        <span style="color: #15803d;">Ya</span>
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 100px;">
                                        <input type="radio" name="ada_sertifikat_halal_st" value="0" style="accent-color: #dc2626;">
                                        <span style="color: #dc2626;">Tidak</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="komentar_sertifikat" class="form-control" placeholder="Komentar sertifikat (opsional)..." style="font-size: 0.825rem; min-height: 42px;">
                                </div>
                            </div>

                            {{-- 5. SERTIFIKAT HALAL MASIH BERLAKU --}}
                            <div style="display: flex; flex-direction: column; gap: 0.45rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem;">
                                <div style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                                    5. Apakah sertifikat halal barang tersebut masih berlaku?
                                </div>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 100px;">
                                        <input type="radio" name="sertifikat_halal_berlaku_st" value="1" checked style="accent-color: #15803d;">
                                        <span style="color: #15803d;">Ya</span>
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; font-size: 0.825rem; font-weight: 700; flex: 1; min-width: 100px;">
                                        <input type="radio" name="sertifikat_halal_berlaku_st" value="0" style="accent-color: #dc2626;">
                                        <span style="color: #dc2626;">Tidak</span>
                                    </label>
                                </div>
                                <div>
                                    <input type="text" name="komentar_berlaku" class="form-control" placeholder="Komentar masa berlaku (opsional)..." style="font-size: 0.825rem; min-height: 42px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN PARAMETER & MUTU SESUAI KOMODITAS                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #10b981; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <h2 id="section2Title" style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                        {{ $isMinyak ? 'Tahap 2: Sampling Mutu Minyak Goreng & FFA' : 'Tahap 2: Pengujian I • Sampling Fisik & Parameter' }}
                    </h2>
                    <span id="section2Sub" style="font-size: 0.8rem; color: #64748b;">
                        {{ $isMinyak ? 'Formulir Cheklist HACCP PT Mirasa Food Industry (FFA, Wadah & Netto)' : 'Standar mutu bahan baku, cacat fisik, dan timbangan kedatangan' }}
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <button type="button" id="btnAddItemRow" class="btn btn-sm btn-secondary" onclick="addItemRow()" style="display: {{ $isMinyak ? 'none' : 'inline-block' }}; border-radius: 8px; padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                        + Tambah Item
                    </button>
                </div>
            </div>

            {{-- 1. FORM KHUSUS SINGKONG (MULTIPLE ITEMS / DYNAMIC CARDS) --}}
            @include('gudang.qc.partials.singkong.form-singkong')

            {{-- 2. FORM KHUSUS MINYAK GORENG (REAL-TIME WIDGET & PARAMETER) --}}
            @include('gudang.qc.partials.minyak.form-minyak')

            {{-- 3. FORM KHUSUS PLASTIK KEMASAN --}}
            @include('gudang.qc.partials.plastik.form-plastik')

            {{-- 4. FORM KHUSUS KARTON BOX --}}
            @include('gudang.qc.partials.karton.form-karton')

            {{-- 5. FORM KHUSUS BAHAN PENOLONG (MSG, GARAM, PERENYAH) --}}
            @include('gudang.qc.partials.bahan-penolong.form-bahan-penolong')
        </div>

        {{-- TAHAP 3: UJI CEPAT RASA FRYER & KESIMPULAN DUA PERSETUJUAN (KHUSUS SINGKONG) --}}
        @include('gudang.qc.partials.singkong.form-singkong-fryer')

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

@push('scripts')
    <script>
        window.qcConfig = {
            rawMaterials: {!! json_encode($rawMaterialsData) !!},
            poList: {!! json_encode($poListData) !!},
            stokGradeA: {{ (float) ($stokSingkongA ?? 0) }},
            stokGradeB: {{ (float) ($stokSingkongB ?? 0) }},
            initialKomoditas: '{{ old("kategori_barang", $initialKomoditas ?? "SINGKONG") }}',
            defaultTahap: '{{ old("tahap_uji", $defaultTahap ?? "PENGUJIAN_1") }}',
            hasParentQc: {{ !empty($parentQc) ? 'true' : 'false' }},
            parentQcItem: @if(!empty($parentQc)) {
                gross: {{ (float) ($parentQc->details->first()?->qty_timbang_gross ?? $parentQc->jumlah_di_pabrik) }},
                barang_id: '{{ $parentQc->details->first()?->barang_id }}',
                grade_cd: '{{ $parentQc->details->first()?->grade_cd ?? "A" }}',
                refraksi_persen: '{{ (float) ($parentQc->details->first()?->refraksi_persen ?? 0) }}',
            } @else null @endif,
        };
    </script>
    <script src="{{ asset('js/gudang/qc/singkong/singkong.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/minyak/minyak.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/plastik/plastik.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/karton/karton.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/bahan-penolong/bahan-penolong.js') }}"></script>
    <script src="{{ asset('js/gudang/qc/mobile/qc-create.js') }}"></script>
@endpush
@endsection
