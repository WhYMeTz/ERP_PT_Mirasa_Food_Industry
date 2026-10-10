@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Singkong - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/singkong/singkong.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($nm, $cd) {
        $nm = strtoupper($nm ?? '');
        $cd = strtoupper($cd ?? '');
        if (str_contains($nm, 'SINGKONG') || str_starts_with($cd, 'BB-SK') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR') || str_starts_with($cd, 'BB-OP') || str_starts_with($cd, 'BB-PY') || str_starts_with($cd, 'BB-')) {
            return 'SINGKONG';
        }
        return 'LAINNYA';
    };

    $getPoCommodities = function ($p) use ($classifyBarang) {
        $cats = [];
        foreach ($p->details as $d) {
            $cat = $classifyBarang($d->barang?->barang_nm, $d->barang?->barang_cd);
            $cats[] = $cat;
        }
        return array_values(array_unique($cats));
    };
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Singkong --}}
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span id="badgeDocNo" style="display: inline-block; background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: MFI/HACCP-04/FRM-03/048/VIII/2021
                    </span>
                </div>
                <h1 id="titleFormHaccp" style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    Sampling Mutu Singkong
                </h1>
                <p id="descFormHaccp" style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pencatatan sampling mutu kedatangan bahan baku di lapangan (Pengujian 1 &amp; Pengujian 2)
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'SINGKONG']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0284c7; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat Singkong
                </a>
            </div>
        </div>
    </div>

    {{-- SELECTOR 7 KOMODITAS (LINK NAVIGASI LANGSUNG) --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-top: none; border-radius: 0 0 14px 14px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏷️</span> <span>PILIH JENIS BAHAN DATANG:</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(115px, 1fr)); gap: 0.45rem;">
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'SINGKONG']) }}" class="btn komoditas-btn active-komoditas" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid #0284c7; background: #0284c7; color: #ffffff; text-align: center; text-decoration: none;">
                🥔 Singkong
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MINYAK']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🛢️ Minyak
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PLASTIK']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🛍️ Plastik
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'KARTON']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                📦 Karton
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MSG']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🧂 MSG
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'GARAM']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                🧂 Garam
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PERENYAH']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
                ✨ Perenyah
            </a>
        </div>
    </div>

    {{-- PILIH TAHAP PENGUJIAN QC SINGKONG (PENGUJIAN 1 ATAU PENGUJIAN 2) --}}
    <div id="groupTahapPengujian" class="qc-tahap-card">
        <div class="qc-tahap-header">
            <div class="qc-tahap-title-wrap">
                <span class="qc-tahap-icon">🔬</span>
                <div>
                    <h3 class="qc-tahap-title">Tahap Pengujian Singkong</h3>
                    <p class="qc-tahap-desc">Pilih alur inspeksi penerimaan muatan bak truk</p>
                </div>
            </div>
            <span id="labelTahapBadge" class="qc-tahap-status-badge {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'purple' : 'blue' }}">
                {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? '🍟 Pengujian 2 (Lanjutan)' : '🚛 Pengujian 1 (Awal)' }}
            </span>
        </div>

        <div class="qc-tahap-grid">
            <button type="button" onclick="selectTahapUji('PENGUJIAN_1')" class="qc-tahap-btn {{ ($defaultTahap ?? 'PENGUJIAN_1') !== 'PENGUJIAN_2' ? 'active blue' : '' }}" id="btnTahap_1">
                <div class="qc-tahap-btn-icon">🚛</div>
                <div class="qc-tahap-btn-content">
                    <div class="qc-tahap-btn-name">PENGUJIAN 1</div>
                    <div class="qc-tahap-btn-sub">Awal Kedatangan (Setengah Bak 1)</div>
                </div>
            </button>
            <button type="button" onclick="selectTahapUji('PENGUJIAN_2')" class="qc-tahap-btn {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'active purple' : '' }}" id="btnTahap_2">
                <div class="qc-tahap-btn-icon">🍟</div>
                <div class="qc-tahap-btn-content">
                    <div class="qc-tahap-btn-name">PENGUJIAN 2</div>
                    <div class="qc-tahap-btn-sub">Lanjutan (Bongkar Tuntas Sisa Bak)</div>
                </div>
            </button>
        </div>

        <div id="descTahapUji" class="qc-tahap-info">
            @if (($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2')
                🍟 <strong>Pengujian 2:</strong> Pengujian lanjutan setelah setengah bak diturunkan. Petugas mengambil sampel gabungan ~7 kg dari lapisan dalam/bawah bak untuk verifikasi sebelum bongkar tuntas.
            @else
                🚛 <strong>Pengujian 1:</strong> Pengujian awal saat truk singkong tiba di pos penerimaan (sebelum bongkar muatan). Sampel gabungan ~7 kg diambil dari bak belakang, tengah, depan.
            @endif
        </div>

        {{-- AUTO-FILL DARI KEDATANGAN PENGUJIAN 1 (KHUSUS PENGUJIAN 2) --}}
        <div id="wrapPendingP1" class="qc-pending-p1-wrap" style="display: {{ ($defaultTahap ?? 'PENGUJIAN_1') === 'PENGUJIAN_2' ? 'block' : 'none' }};">
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

    {{-- STEP / TAB SWITCHER FOR MOBILE (3 TAB LENGKAP SINGKONG) --}}
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
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="SINGKONG">
        <input type="hidden" name="status_uji_goreng" id="statusUjiGorengInput" value="SELESAI">
        <input type="hidden" name="tahap_uji" id="tahapUjiInput" value="{{ old('tahap_uji', $defaultTahap ?? 'PENGUJIAN_1') }}">
        <input type="hidden" name="parent_qc_id" id="parentQcIdInput" value="{{ old('parent_qc_id', $parentQc?->qc_id) }}">
        <input type="hidden" name="batch_no" id="batchNoInput" value="{{ old('batch_no', $parentQc?->batch_no) }}">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- WIDGET WAKTU INSPEKSI LAPANGAN --}}
            <div class="qc-inspection-bar">
                <div class="inspection-bar-left">
                    <div class="inspection-bar-icon">📅</div>
                    <div>
                        <div class="inspection-bar-label">Waktu Inspeksi Lapangan</div>
                        <div class="inspection-bar-val" id="textInspectionBarTime">{{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') }} WIB</div>
                    </div>
                </div>
                <span class="inspection-bar-badge">QC-RM-048</span>
            </div>

            {{-- CARD 1: INFORMASI BAHAN & PENGIRIMAN (KONSISTEN PERSIS SESUAI STANDAR) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge">1</span>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.25;">Informasi Bahan &amp; Pengiriman</h2>
                        </div>
                    </div>
                    <span class="card-step-tag">DO / SURAT JALAN</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- 1. REFERENSI PO & GUDANG BONGKAR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Referensi PO Singkong <span style="font-size: 0.72rem; color: #64748b; font-weight: normal;">(Opsional)</span></label>
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

                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Gudang Bongkar <span style="color:#ef4444;">*</span></label>
                            </div>
                            <select name="gudang_id" id="gudangSelect" class="form-control" required style="font-size: 0.85rem; font-weight: 700;">
                                @foreach ($gudangs as $g)
                                    <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? auth()->user()?->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                        {{ $g->display_name ?? $g->gudang_nm }} @if(!empty($g->tipe_gudang_cd) && $g->tipe_gudang_cd !== 'Pusat') ({{ $g->tipe_gudang_cd }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 2. NO. DO (SURAT JALAN) & TANGGAL DATANG (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">No. DO (Surat Jalan)</label>
                            </div>
                            <input type="text" name="surat_jalan_supplier" class="form-control" placeholder="DO-SAM/001" value="{{ old('surat_jalan_supplier') }}" style="font-weight: 700;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Tanggal Datang</label>
                                <button type="button" onclick="setCurrentDateTime()" style="background: none; border: none; color: #0284c7; font-size: 0.72rem; font-weight: 800; cursor: pointer; padding: 0;">🕒 Sekarang</button>
                            </div>
                            <input type="datetime-local" name="tgl_periksa" id="inputTglDatang" class="form-control" data-has-old="{{ old('tgl_periksa') ? '1' : '0' }}" value="{{ old('tgl_periksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" style="font-weight: 700;">
                        </div>
                    </div>

                    {{-- 3. MITRA VENDOR / SUPPLIER & BAHAN BAKU (SUBCARD KELOMPOK) --}}
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Mitra Vendor / Supplier Singkong <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="supplier_id" id="supplierSelect" class="form-control" required onchange="onSupplierSelected(this)" style="font-weight: 700; font-size: 0.85rem;">
                                <option value="">-- Pilih Mitra Vendor / Supplier Singkong --</option>
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
                                        {{ $s->supplier_nm }} ({{ $s->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- NAMA BAHAN BAKU (MASTER BARANG SINGKONG SATUAN KILOGRAM) --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Nama Bahan Baku <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="singkong_barang_id" id="singkongBarangSelect" class="form-control" style="font-weight: 700;" onchange="onSingkongBarangChanged(this)">
                                @foreach ($barangs as $b)
                                    @if (stripos($b->barang_nm, 'singkong') !== false || $b->kategori === 'SINGKONG')
                                        @php
                                            $satNm = $b->satuanDasar?->satuan_nm ?? ($b->satuanDasar?->satuan_cd ?? 'Kilogram');
                                            $satCd = strtoupper($b->satuanDasar?->satuan_cd ?? 'KG');
                                        @endphp
                                        <option value="{{ $b->barang_id }}"
                                                data-nama="{{ $b->barang_nm }}"
                                                data-cd="{{ $b->barang_cd }}"
                                                data-satuan="{{ $satNm }}"
                                                data-satuan-cd="{{ $satCd }}"
                                                {{ (stripos($b->barang_cd, 'BB-SK001') !== false || $loop->first) ? 'selected' : '' }}>
                                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) &bull; Satuan: {{ $satNm }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Produsen <span style="font-size: 0.72rem; color: #0284c7; font-weight: normal;">(Bisa diedit/ketik)</span></label>
                                </div>
                                <input type="text" name="nama_produsen" id="namaProdusenInput" class="form-control" placeholder="Petani Mitra / Kelompok Tani" value="{{ old('nama_produsen') }}" style="font-weight: 700;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Negara Produsen <span style="font-size: 0.72rem; color: #0284c7; font-weight: normal;">(Bisa diedit)</span></label>
                                </div>
                                <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}" style="font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- 4. PLAT TRUK / KENDARAAN & NAMA SOPIR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Plat Truk / Tangki</label>
                            </div>
                            <input type="text" name="plat_nomor_truk" class="form-control" placeholder="B 9876 XYZ" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase; font-weight: 800;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Nama Sopir</label>
                            </div>
                            <input type="text" name="sopir_nama" class="form-control" placeholder="Nama pengemudi" value="{{ old('sopir_nama') }}">
                        </div>
                    </div>

                    {{-- 5. SUBCARD: KUANTITAS & VERIFIKASI TIMBANGAN --}}
                    <div class="singkong-subcard">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span class="qc-subcard-title" style="margin-bottom: 0;">KUANTITAS &amp; VERIFIKASI TIMBANGAN</span>
                            <div class="satuan-toggle-pills">
                                <button type="button" class="btn-satuan-pill active" style="cursor: default;">
                                    <span class="pill-icon">⚖️</span>
                                    <span class="pill-text">KG</span>
                                </button>
                            </div>
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah Surat Jalan (KG)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="0.01" min="0" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="16000" value="{{ old('jumlah_surat_jalan') }}" oninput="syncQuantityFields('sj')" style="font-weight: 800;">
                                    <span class="input-suffix-text">KG</span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah di Pabrik (KG)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="15985" value="{{ old('jumlah_di_pabrik') }}" oninput="syncQuantityFields('pabrik')" style="font-weight: 800;">
                                    <span class="input-suffix-text">KG</span>
                                </div>
                            </div>
                        </div>

                        {{-- Sampel QC Diambil & Netto Real-Time --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.25rem; border-top: 1px dashed #e2e8f0; padding-top: 0.45rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.8rem; font-weight: 800; color: #1e293b;">Sampel QC Diambil:</span>
                                <div class="input-suffix-wrap" style="max-width: 120px;">
                                    <input type="number" step="0.1" min="0.1" name="jumlah_sample_kg" id="inputJumlahSampleKg" class="form-control" placeholder="7.0" value="{{ old('jumlah_sample_kg', 7.0) }}" style="text-align: right; font-weight: 900; color: #0284c7; height: 38px !important; min-height: 38px !important;">
                                    <span class="input-suffix-text">KG</span>
                                </div>
                            </div>
                            <span id="labelSubcardNetto" style="font-size: 0.75rem; font-weight: 800; color: #059669;">Netto: 0,00 KG</span>
                        </div>

                        {{-- NAMA JENIS : (Spesifikasi Fraksi / Grade) di dalam Subcard Timbangan --}}
                        <div class="form-group" style="margin-top: 0.65rem; margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 800; font-size: 0.775rem; color: #475569;">NAMA JENIS : (Spesifikasi Fraksi / Grade)</label>
                            <input type="text" name="nama_jenis" id="namaJenisInput" list="listSingkongJenisPage" class="form-control" placeholder="Singkong Basah Curah / Singkong Gajah" value="{{ old('nama_jenis', 'Singkong Basah Curah') }}">
                            <datalist id="listSingkongJenisPage">
                                <option value="Singkong Basah Curah">
                                <option value="Singkong Ketan">
                                <option value="Singkong Gajah">
                                <option value="Singkong Manggu">
                                <option value="Singkong Mentega / Kuning">
                                <option value="Singkong Manis Super">
                            </datalist>
                        </div>
                    </div>

                    {{-- 8. FIELD KHUSUS SINGKONG: IDENTITAS PANEN (DARI DOKUMEN HACCP SINGKONG) --}}
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span class="qc-subcard-title" style="margin-bottom: 0;">🌾 IDENTITAS ASAL PANEN (HACCP SINGKONG)</span>
                            <span style="font-size: 0.7rem; color: #64748b; font-weight: 700;">Dokumen Panen</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">Lokasi Asal Panen</label>
                                <input type="text" name="lokasi_panen" class="form-control" placeholder="Wonosobo / Mitra" value="{{ old('lokasi_panen') }}">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">Umur Singkong (Bulan)</label>
                                <input type="number" step="0.5" name="umur_singkong_bln" class="form-control" placeholder="9.0" value="{{ old('umur_singkong_bln', 9.0) }}">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">Tanggal Panen</label>
                                <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', date('Y-m-d', strtotime('-1 day'))) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: STANDAR TRANSPORTASI & JAMINAN HALAL (IDENTIK DENGAN MINYAK) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #bbf7d0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #f0fdf4; padding: 1rem 1.25rem; border-bottom: 1px solid #dcfce7; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge green">2</span>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: #166534; margin: 0; line-height: 1.25;">Standar Transportasi &amp; Jaminan Halal</h2>
                            <span style="font-size: 0.725rem; color: #15803d; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; Kebersihan Armada</span>
                        </div>
                    </div>
                    <span style="font-size: 1.25rem;">🛡️</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                    {{-- ITEM 1: KONDISI TRANSPORTASI (TRUK / BAK SINGKONG) --}}
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin-bottom: 0.45rem;">
                            1. Kondisi Transportasi (Truk / Bak Terbuka Singkong)
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <label class="transport-radio-card active" id="cardTransportBebas">
                                <input type="radio" name="bebas_cemaran_st" value="1" checked onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #15803d;">Tidak ada cemaran, Najis / Kotoran</div>
                                    <div class="transport-card-desc">Bak truk bersih, terpal utuh, tidak berbau busuk/asing</div>
                                </div>
                            </label>

                            <label class="transport-radio-card is-danger" id="cardTransportCemar">
                                <input type="radio" name="bebas_cemaran_st" value="0" onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #dc2626;">Ada cemaran / Kotor / Najis</div>
                                    <div class="transport-card-desc">Ditemukan indikasi kontaminasi, kotoran najis, atau bau menyengat</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 2: DIANGKUT BERSAMA DENGAN BARANG HARAM? --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">2. Apakah barang tersebut diangkut bersama dengan barang haram?</div>
                                <div class="halal-qa-sub">Persyaratan mutlak integrasi logistik halal HACCP MFI</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btnHaramTidak" onclick="setHalalToggle('angkut_barang_haram', 0)">Tidak</button>
                                <button type="button" class="segmented-toggle-btn" id="btnHaramYa" onclick="setHalalToggle('angkut_barang_haram', 1)">Ya</button>
                            </div>
                            <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ old('angkut_barang_haram_st', 0) }}">
                        </div>
                        <input type="text" name="komentar_transportasi" class="form-control" placeholder="Komentar transportasi / armada..." value="{{ old('komentar_transportasi') }}" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN PARAMETER & DIAMETER SINGKONG                        --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border: 1.5px solid #a7f3d0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #ecfdf5; padding: 1rem 1.25rem; border-bottom: 1px solid #d1fae5; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span class="card-step-badge emerald">2</span>
                    <div>
                        <h2 id="section2Title" style="font-size: 1.05rem; font-weight: 800; color: #065f46; margin: 0; line-height: 1.25;">Tahap 2: Pengujian 1 &bull; Sampling Fisik &amp; Diameter</h2>
                        <span id="section2Sub" style="font-size: 0.725rem; color: #047857; font-weight: 700;">Standar diameter, kebersihan tanah &amp; kondisi visual singkong</span>
                    </div>
                </div>
            </div>

            {{-- FORM KHUSUS SINGKONG (MULTIPLE ITEMS & PARAMETER FISIK) --}}
            @include('gudang.qc.partials.singkong.form-singkong')
        </div>

        {{-- TAHAP 3: UJI CEPAT RASA FRYER & KESIMPULAN (KHUSUS SINGKONG) --}}
        @include('gudang.qc.partials.singkong.form-singkong-fryer')

        {{-- FLOATING ACTION DOCK UNTUK MOBILE --}}
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

        {{-- FULLSCREEN LOADING OVERLAY --}}
        <div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.72); z-index: 999999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; padding: 1.75rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); max-width: 320px; margin: 1rem; width: 90%;">
                <div style="width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: qcSpin 0.8s linear infinite; margin: 0 auto 1.15rem;"></div>
                <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.4rem;" id="overlayTitle">Menyimpan Data QC Singkong...</div>
                <div style="font-size: 0.8rem; color: #64748b; line-height: 1.45;">Sedang memproses inspeksi mutu &amp; meneruskan ke gudang. Mohon tunggu.</div>
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
            initialKomoditas: 'SINGKONG',
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
    <script src="{{ asset('js/gudang/qc/mobile/qc-create.js') }}"></script>
@endpush
@endsection
