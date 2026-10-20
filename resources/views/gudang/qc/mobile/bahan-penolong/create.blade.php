@extends('layouts.qc-mobile')

@section('title', 'Sampling Mutu Bahan Penolong - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/bahan-penolong/bahan-penolong.css') }}">
@endpush

@section('content')
@php
    $classifyBarang = function ($d) {
        $barang = ($d instanceof \App\Models\MasterData\MstBarang) ? $d : ($d?->barang ?? $d);
        if (!$barang) return 'LAINNYA';

        $nm = strtoupper($barang->barang_nm ?? '');
        $cd = strtoupper($barang->barang_cd ?? '');
        $jnsCd = strtoupper($barang->jenisBarang?->jenis_barang_cd ?? '');

        // Abaikan komoditas non-bahan penolong & barang setengah jadi/jadi
        if (
            str_starts_with($cd, 'WIP') || str_starts_with($cd, 'FG-') || str_starts_with($cd, 'BB-') ||
            str_contains($nm, 'MINYAK') || str_contains($nm, 'SINGKONG') || str_contains($nm, 'PUYUR') ||
            str_contains($nm, 'OPAK') || str_contains($nm, 'UBI') || str_contains($nm, 'PLASTIK') ||
            str_contains($nm, 'KARTON') || str_contains($nm, 'BOX') || str_contains($nm, 'DUS')
        ) {
            return 'LAINNYA';
        }

        // BUMBU dicek sebelum GARAM agar bumbu dengan nama seasalt tidak tertukar dengan garam
        if (str_contains($nm, 'BUMBU') || str_starts_with($cd, 'BP-BM') || str_starts_with($cd, 'BBL') || str_starts_with($cd, 'BCS') || str_starts_with($cd, 'BCL') || str_starts_with($cd, 'BSS') || str_starts_with($cd, 'BBQ')) {
            return 'BUMBU';
        }

        if (str_contains($nm, 'MSG') || str_contains($nm, 'MONOSODIUM') || str_contains($nm, 'PENYEDAP') || str_starts_with($cd, 'BP-MS') || str_starts_with($cd, 'MSG')) {
            return 'MSG';
        }

        if (str_contains($nm, 'GARAM') || str_contains($nm, 'SALT') || str_starts_with($cd, 'BP-GR') || str_starts_with($cd, 'GRM')) {
            return 'GARAM';
        }

        if (str_contains($nm, 'PERENYAH') || str_starts_with($cd, 'BP-PR') || str_starts_with($cd, 'PRH') || str_contains($nm, 'STPP')) {
            return 'PERENYAH';
        }

        if (str_starts_with($cd, 'BP-') || $jnsCd === 'BP') {
            return 'BAHAN_PENOLONG';
        }

        return 'LAINNYA';
    };

    $getPoCommodities = function ($p) use ($classifyBarang) {
        $cats = [];
        foreach ($p->details as $d) {
            $cat = $classifyBarang($d);
            $cats[] = $cat;
        }
        return array_values(array_unique($cats));
    };

    $curKomoditas = strtoupper($initialKomoditas ?? request('kategori_barang', 'MSG'));
    if (!in_array($curKomoditas, ['MSG', 'GARAM', 'PERENYAH', 'BUMBU'])) {
        $curKomoditas = 'MSG';
    }

    $docNumbers = [
        'MSG'      => 'MFI/HACCP-04/FRM-03/032/VIII/2021',
        'GARAM'    => 'MFI/HACCP-04/FRM-03/033/VIII/2021',
        'PERENYAH' => 'MFI/HACCP-04/FRM-03/063/IX/2023',
        'BUMBU'    => 'MFI/HACCP-04/FRM-03/032/VIII/2021',
    ];
    $docNo = $docNumbers[$curKomoditas] ?? 'MFI/HACCP-04/FRM-03/032/VIII/2021';

    $docCodes = [
        'MSG'      => 'QC-MSG-032',
        'GARAM'    => 'QC-GRM-033',
        'PERENYAH' => 'QC-PRY-063',
        'BUMBU'    => 'QC-BMB-032',
    ];
    $docCode = $docCodes[$curKomoditas] ?? 'QC-BP-032';

    // Filter PO KHUSUS komoditas terpilih (MSG / GARAM / PERENYAH / BUMBU)
    $bpPos = $pos->filter(function($p) use ($getPoCommodities, $curKomoditas) {
        $cats = $getPoCommodities($p);
        return in_array($curKomoditas, $cats);
    });

    // Mapping Data PO Lengkap ke Format JSON untuk Auto-Fill Instan Tanpa Delay
    $poListData = $bpPos->mapWithKeys(function ($p) use ($getPoCommodities, $classifyBarang, $curKomoditas) {
        return [
            $p->po_id => [
                'po_id'       => $p->po_id,
                'po_no'       => $p->po_no,
                'supplier_id' => $p->supplier_id,
                'supplier_nm' => $p->supplier?->supplier_nm,
                'gudang_id'   => $p->gudang_id,
                'komoditas'   => $getPoCommodities($p),
                'items'       => $p->details->filter(function ($d) use ($classifyBarang, $curKomoditas) {
                    return $classifyBarang($d) === $curKomoditas;
                })->map(function ($d) {
                    return [
                        'podtl_id'  => $d->podtl_id,
                        'barang_id' => $d->barang_id,
                        'barang_nm' => $d->barang?->barang_nm,
                        'satuan'    => $d->barang?->satuanDasar?->satuan_nm ?? 'KG',
                        'satuan_cd' => strtoupper($d->barang?->satuanDasar?->satuan_cd ?? 'KG'),
                        'pesan_qty' => (float) $d->pesan_qty,
                        'terima_qty'=> (float) $d->terima_qty,
                        'sisa_qty'  => max(0, (float) $d->pesan_qty - (float) $d->terima_qty),
                    ];
                })->values(),
            ]
        ];
    });

    // Filter Supplier khusus Bahan Penolong yang relevan dengan komoditas terpilih
    // Filter Supplier non-petani/raw, prioritaskan vendor yang memiliki PO komoditas ini di paling atas
    $bpSuppliers = $suppliers->filter(function($s) {
        $cd = strtoupper($s->supplier_cd ?? '');
        $isPetani = str_starts_with($cd, 'SKG-') || ($s->jenisSupplier?->jenis_supplier_cd === 'RAW');
        return !$isPetani;
    })->sortByDesc(function($s) use ($bpPos, $selectedPo) {
        if ($selectedPo && $selectedPo->supplier_id == $s->supplier_id) return 2;
        return $bpPos->contains('supplier_id', $s->supplier_id) ? 1 : 0;
    })->values();
@endphp
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner QC Lapangan Khusus Bahan Penolong (Aksen Royal Violet / Indigo) --}}
    <div style="background: linear-gradient(135deg, #7c3aed 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.25rem 1.5rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 QC LAPANGAN &bull; INBOUND
                    </span>
                    <span id="badgeDocNo" style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        No. Dok: {{ $docNo }}
                    </span>
                </div>
                <h1 style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    🧂 Sampling Mutu Bahan Penolong ({{ $curKomoditas }})
                </h1>
                <p style="font-size: 0.825rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Pemeriksaan Fisik Kering/Gumpal, Kemasan Zak, CoA &amp; Higienitas (Formulir HACCP MFI)
                </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); border-radius: 8px; font-weight: 700;" title="Kembali ke Web ERP Desktop">
                        🖥️ Ke Web ERP
                    </a>
                @endif
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => $curKomoditas]) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #7c3aed; border-radius: 8px; font-weight: 700;">
                    📋 Riwayat {{ $curKomoditas }}
                </a>
            </div>
        </div>
    </div>

    {{-- SELECTOR 7 KOMODITAS (LINK NAVIGASI LANGSUNG ANTAR HALAMAN MODUL) --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-top: none; border-radius: 0 0 14px 14px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏷️</span> <span>PILIH JENIS BAHAN DATANG:</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(115px, 1fr)); gap: 0.45rem;">
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'SINGKONG']) }}" class="btn komoditas-btn" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none;">
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
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'MSG']) }}" class="btn komoditas-btn {{ $curKomoditas === 'MSG' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'MSG' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'MSG' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'MSG' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
                🧂 MSG
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'GARAM']) }}" class="btn komoditas-btn {{ $curKomoditas === 'GARAM' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'GARAM' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'GARAM' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'GARAM' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
                🧂 Garam
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'PERENYAH']) }}" class="btn komoditas-btn {{ $curKomoditas === 'PERENYAH' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'PERENYAH' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'PERENYAH' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'PERENYAH' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
                ✨ Perenyah
            </a>
            <a href="{{ route('qc.inbound.create', ['kategori_barang' => 'BUMBU']) }}" class="btn komoditas-btn {{ $curKomoditas === 'BUMBU' ? 'active-komoditas' : '' }}" style="font-size: 0.8rem; font-weight: 800; padding: 0.5rem 0.4rem; border-radius: 8px; border: 2px solid {{ $curKomoditas === 'BUMBU' ? '#7c3aed' : '#cbd5e1' }}; background: {{ $curKomoditas === 'BUMBU' ? '#7c3aed' : '#ffffff' }}; color: {{ $curKomoditas === 'BUMBU' ? '#ffffff' : '#334155' }}; text-align: center; text-decoration: none;">
                🌶️ Bumbu
            </a>
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

    {{-- STEP / TAB SWITCHER KHUSUS BAHAN PENOLONG --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;" id="qcTabNav">
        <button type="button" class="btn active-tab-btn" id="tabBtn1" onclick="switchBpTab(1)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #7c3aed; background: #7c3aed; color: #ffffff; cursor: pointer; transition: all 0.15s;">
            🚚 1. Dokumen &amp; Armada
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchBpTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🧂 2. Mutu Bahan &amp; Fisik
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcBpForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="form_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        <input type="hidden" name="view" value="mobile">
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="{{ $curKomoditas }}">
        <input type="hidden" name="status_uji_goreng" value="SELESAI">
        <input type="hidden" name="tahap_uji" value="PENGUJIAN_1">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- WIDGET WAKTU INSPEKSI LAPANGAN --}}
            <div class="bp-inspection-bar">
                <div class="inspection-bar-left">
                    <div class="inspection-bar-icon">📅</div>
                    <div>
                        <div class="inspection-bar-label">Waktu Inspeksi Lapangan</div>
                        <div class="inspection-bar-val" id="textInspectionBarTime">{{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') }} WIB</div>
                    </div>
                </div>
                <span class="inspection-bar-badge" id="badgeInspectionDocCode">{{ $docCode }}</span>
            </div>

            {{-- CARD 1: INFORMASI BAHAN & PENGIRIMAN --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <span class="card-step-badge violet" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">1</span>
                        <div>
                            <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.25;">Informasi Bahan &amp; Pengiriman</h2>
                            <span style="font-size: 0.75rem; color: #64748b;">Verifikasi surat jalan, produsen &amp; audit halal transportasi</span>
                        </div>
                    </div>
                    <span class="card-step-tag" style="background: #ede9fe; color: #6d28d9; font-weight: 800; font-size: 0.725rem; padding: 3px 8px; border-radius: 6px;">DO / SURAT JALAN</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- 1. REFERENSI PO & GUDANG BONGKAR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Referensi PO {{ $curKomoditas }} <span style="font-size: 0.72rem; color: #7c3aed; font-weight: normal;">(Opsional)</span></label>
                            </div>
                            <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 700;">
                                @if ($bpPos->isEmpty())
                                    <option value="">-- Tidak Ada PO {{ $curKomoditas }} Aktif / Kiriman Langsung --</option>
                                @else
                                    <option value="">-- Tanpa PO / Kiriman Langsung {{ $curKomoditas }} --</option>
                                    @foreach ($bpPos as $p)
                                        @php
                                            $poCats = $getPoCommodities($p);
                                            $curDetails = $p->details->filter(fn($d) => $classifyBarang($d) === $curKomoditas);
                                            $itemNames = $curDetails->map(fn($d) => $d->barang?->barang_nm)->filter()->unique()->implode(', ');
                                            $totalSisa = $curDetails->sum(fn($d) => max(0, (float)$d->pesan_qty - (float)$d->terima_qty));
                                        @endphp
                                        <option value="{{ $p->po_id }}"
                                                data-komoditas="{{ implode(',', $poCats) }}"
                                                data-supplier-id="{{ $p->supplier_id }}"
                                                data-gudang-id="{{ $p->gudang_id }}"
                                                data-search="{{ strtolower($p->po_no . ' ' . ($p->supplier?->supplier_nm ?? '') . ' ' . $itemNames) }}"
                                                {{ old('po_id', $selectedPo?->po_id) == $p->po_id ? 'selected' : '' }}>
                                            {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} ({{ $itemNames ?: $curKomoditas }} - Sisa: {{ number_format($totalSisa, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Gudang Bongkar <span style="color:#ef4444;">*</span></label>
                            </div>
                            <select name="gudang_id" id="gudangSelect" class="form-control" required style="font-size: 0.85rem; font-weight: 700;">
                                @foreach ($gudangs as $g)
                                    <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? auth()->user()?->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                        {{ $g->display_name ?? $g->gudang_nm }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- SELECTOR ITEM BARANG DARI PO (JIKA PO MEMILIKI MULTI-ITEM) --}}
                    <input type="hidden" name="podtl_id" id="inputSelectedPodtlId" value="{{ old('podtl_id') }}">
                    <div id="boxPoItemsSelector" style="display: none; background: #faf5ff; border: 1.5px solid #ddd6fe; border-radius: 12px; padding: 0.85rem 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.25rem;">
                            <label style="font-size: 0.8rem; font-weight: 800; color: #6d28d9; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                <span>🧂</span> <span>Item Bahan dalam PO Ini (<span id="countPoItemsBadge">0</span> Item):</span>
                            </label>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #6d28d9; background: #ede9fe; padding: 2px 7px; border-radius: 6px;">
                                1 Dokumen QC per Item
                            </span>
                        </div>
                        <p style="font-size: 0.725rem; color: #581c87; margin: 0 0 0.65rem 0; line-height: 1.35;">
                            Silakan pilih item yang sedang dibongkar/diperiksa. Item lainnya dapat dibuatkan dokumen QC tersendiri setelah ini.
                        </p>
                        <div id="poItemsCardsList" class="po-items-list-grid">
                            {{-- Di-render secara dinamis oleh JS saat PO dipilih --}}
                        </div>
                    </div>

                    {{-- 2. NO. DO (SURAT JALAN) & TANGGAL DATANG (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">No. DO / Surat Jalan</label>
                            </div>
                            <input type="text" name="surat_jalan_supplier" id="inputNoSJ" class="form-control" placeholder="Contoh: SJ-BP-4432" value="{{ old('surat_jalan_supplier') }}" style="font-weight: 700;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Tanggal Datang</label>
                                <button type="button" onclick="setCurrentDateTime()" style="background: none; border: none; color: #7c3aed; font-size: 0.72rem; font-weight: 800; cursor: pointer; padding: 0;">🕒 Sekarang</button>
                            </div>
                            <input type="datetime-local" name="tgl_periksa" id="inputTglDatang" class="form-control" value="{{ old('tgl_periksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" style="font-weight: 700;">
                        </div>
                    </div>

                    {{-- 3. MITRA VENDOR & SPESIFIKASI BAHAN (SUBCARD KELOMPOK) --}}
                    <div class="bp-subcard">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                    Mitra Vendor / Supplier Bahan Penolong <span style="color:#ef4444;">*</span>
                                </label>
                            </div>
                            <select name="supplier_id" id="supplierSelect" class="form-control" required onchange="onSupplierSelected(this)" style="font-weight: 700; font-size: 0.85rem;">
                                <option value="">-- Pilih Mitra Vendor Bahan --</option>
                                @foreach ($bpSuppliers as $s)
                                    <option value="{{ $s->supplier_id }}"
                                            data-supplier-cd="{{ $s->supplier_cd }}"
                                            data-supplier-nm="{{ $s->supplier_nm }}"
                                            {{ old('supplier_id', $selectedPo?->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                                        {{ $s->supplier_nm }} ({{ $s->supplier_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- IDENTITAS BAHAN BAKU & NAMA JENIS SPESIFIKASI BAHAN PENOLONG --}}
                        <div class="qc-grid-2col">
                            {{-- NAMA BAHAN BAKU (MASTER BARANG) --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label" style="font-weight: 800; color: #0f172a;">
                                        Nama Bahan Baku {{ $curKomoditas }} <span style="color:#ef4444;">*</span>
                                    </label>
                                </div>
                                <select name="bp_barang_id" id="bpBarangSelect" class="form-control" style="font-weight: 700;" onchange="onBpBarangChanged(this)" required>
                                    @php
                                        $filteredBarangs = $barangs->filter(function($b) use ($classifyBarang, $curKomoditas) {
                                            return $classifyBarang($b) === $curKomoditas;
                                        });
                                        if ($filteredBarangs->isEmpty()) {
                                            $filteredBarangs = $barangs->filter(function($b) {
                                                $nm = strtoupper($b->barang_nm);
                                                $cd = strtoupper($b->barang_cd);
                                                return str_contains($nm, 'MSG') || str_contains($nm, 'GARAM') || str_contains($nm, 'PERENYAH') || str_contains($nm, 'BUMBU') || str_starts_with($cd, 'BP-');
                                            });
                                        }
                                    @endphp
                                    @foreach ($filteredBarangs as $b)
                                        <option value="{{ $b->barang_id }}"
                                                data-nama="{{ $b->barang_nm }}"
                                                data-satuan="{{ $b->satuanDasar?->satuan_nm ?? 'KG' }}"
                                                data-satuan-cd="{{ strtoupper($b->satuanDasar?->satuan_cd ?? 'KG') }}"
                                                {{ $loop->first ? 'selected' : '' }}>
                                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) &bull; Satuan: {{ $b->satuanDasar?->satuan_nm ?? 'KG' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- NAMA JENIS (SPESIFIKASI / GRADE BAHAN PENOLONG) --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label" style="font-weight: 800; color: #6d28d9;">
                                        Nama Jenis (Spesifikasi / Grade) <span style="font-size: 0.72rem; color: #7c3aed; font-weight: normal;">(Bisa diedit/ketik)</span>
                                    </label>
                                </div>
                                @php
                                    $defaultNamaJenis = match($curKomoditas) {
                                        'MSG'      => 'Monosodium Glutamat Murni 99%',
                                        'GARAM'    => 'Garam Halus Beryodium K-40',
                                        'PERENYAH' => 'Perenyah Kerupuk STPP Food Grade',
                                        'BUMBU'    => 'Bumbu Tabur Balado Spesial',
                                        default    => 'Bahan Penolong Murni'
                                    };
                                @endphp
                                <input type="text" name="nama_jenis" id="bpNamaJenisInput" list="listBpJenis" class="form-control" placeholder="Contoh: {{ $defaultNamaJenis }}" value="{{ old('nama_jenis', $defaultNamaJenis) }}" style="font-weight: 700;">
                                <datalist id="listBpJenis">
                                    @if($curKomoditas === 'MSG')
                                        <option value="Monosodium Glutamat Murni 99%">
                                        <option value="MSG Miwon Crystal Mesh 40-60">
                                        <option value="MSG Ajinomoto Food Grade">
                                        <option value="MSG Sasa Kristal Halus">
                                    @elseif($curKomoditas === 'GARAM')
                                        <option value="Garam Halus Beryodium K-40">
                                        <option value="Garam Industri Non-Yodium">
                                        <option value="Garam Kristal Refina 99.5%">
                                        <option value="Garam Dapur Cap Kapal">
                                    @elseif($curKomoditas === 'PERENYAH')
                                        <option value="Perenyah Kerupuk STPP Food Grade">
                                        <option value="Sodium Tripolyphosphate Food Grade">
                                        <option value="Sodium Bicarbonate Pengembang">
                                        <option value="STPP Brataco Chemical Grade A">
                                    @elseif($curKomoditas === 'BUMBU')
                                        <option value="Bumbu Balado Spesial">
                                        <option value="Bumbu Chilli Seasalt Extra Pedas">
                                        <option value="Bumbu Chilli Lemon Segar">
                                        <option value="Bumbu Seasalt Gurih Original">
                                        <option value="Bumbu BBQ Panggang">
                                        <option value="Bumbu Kaldu Rasa Ayam Gurih">
                                    @endif
                                </datalist>
                            </div>
                        </div>

                        {{-- NAMA PRODUSEN & NEGARA PRODUSEN (SESUAI DOKUMEN HACCP BARIS 96-115) --}}
                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Produsen <span style="font-size: 0.72rem; color: #7c3aed; font-weight: normal;">(Bisa diketik)</span></label>
                                </div>
                                <input type="text" name="nama_produsen" id="inputNamaProdusen" class="form-control" placeholder="Contoh: PT Ajinomoto Indonesia" value="{{ old('nama_produsen') }}" style="font-weight: 700;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Negara Produsen</label>
                                </div>
                                <input type="text" name="negara_produsen" id="inputNegaraProdusen" class="form-control" placeholder="Indonesia" value="{{ old('negara_produsen', 'Indonesia') }}" style="font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    {{-- 4. PLAT TRUK / KENDARAAN & NAMA SOPIR (2 KOLOM SEJAJAR) --}}
                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Plat Truk / Mobil Box</label>
                            </div>
                            <input type="text" name="plat_nomor_truk" id="inputPlatNomor" class="form-control" placeholder="AD 8234 QP" value="{{ old('plat_nomor_truk') }}" style="text-transform: uppercase; font-weight: 800;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="qc-field-header">
                                <label class="qc-field-label">Nama Sopir</label>
                            </div>
                            <input type="text" name="sopir_nama" id="inputNamaSupir" class="form-control" placeholder="Nama sopir pengantar" value="{{ old('sopir_nama') }}">
                        </div>
                    </div>

                    {{-- 5. SUBCARD: KUANTITAS & CONTOH SAMPLE (VIOLET THEME - PERSIS FORMAT DOKUMEN MFI) --}}
                    <div class="bp-subcard violet-accent">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                            <span class="bp-subcard-title" style="margin-bottom: 0;">
                                <span>⚖️</span> <span>KUANTITAS PENGIRIMAN &amp; CONTOH SAMPLE</span>
                            </span>
                            <span id="labelSubcardNetto" style="font-size: 0.775rem; font-weight: 800; color: #6d28d9; background: #ffffff; padding: 3px 8px; border-radius: 6px; border: 1px solid #ddd6fe;">
                                Netto Lolos: 0.00 KG
                            </span>
                        </div>

                        <div class="qc-grid-2col">
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah Surat Jalan (KG) <span style="color:#ef4444;">*</span></label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="0.01" min="0.01" name="jumlah_surat_jalan" id="inputJumlahSJ" class="form-control" placeholder="1000.00" value="{{ old('jumlah_surat_jalan') }}" style="font-weight: 800;" required>
                                    <span class="input-suffix-text">KG</span>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Jumlah di Pabrik (KG)</label>
                                </div>
                                <div class="input-suffix-wrap">
                                    <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="1000.00" value="{{ old('jumlah_di_pabrik') }}" style="font-weight: 800;">
                                    <span class="input-suffix-text">KG</span>
                                </div>
                            </div>
                        </div>

                        {{-- Jumlah Sample Diperiksa (Default 200 gr sesuai Dokumen HACCP) --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-top: 0.25rem; border-top: 1px dashed #ddd6fe; padding-top: 0.55rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.8rem; font-weight: 800; color: #0f172a;">Jumlah Sample Diperiksa:</span>
                                <div class="input-suffix-wrap" style="max-width: 140px;">
                                    <input type="number" step="any" min="1" name="jumlah_sample_gr" id="inputJumlahSampleGr" class="form-control" placeholder="200" value="{{ old('jumlah_sample_gr', 200) }}" style="text-align: right; font-weight: 900; color: #7c3aed; height: 38px !important; min-height: 38px !important;">
                                    <span class="input-suffix-text">gr</span>
                                </div>
                            </div>
                            <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">
                                * Standar HACCP sampling: 200 gram
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: STANDAR TRANSPORTASI & JAMINAN HALAL (PERSIS SESUAI DOKUMEN MFI BARIS 177-332) --}}
            <div class="card" style="border-radius: 12px; border: 1.5px solid #ddd6fe; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="card-step-badge violet" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">2</span>
                        <div>
                            <h2 style="font-size: 1.1rem; font-weight: 800; color: #581c87; margin: 0; line-height: 1.25;">Standar Transportasi &amp; Jaminan Halal</h2>
                            <span style="font-size: 0.75rem; color: #6d28d9; font-weight: 700;">Sistem Jaminan Halal (SJH) &amp; Kebersihan Armada Pengangkut</span>
                        </div>
                    </div>
                    <span style="font-size: 1.35rem;">🛡️</span>
                </div>

                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                    {{-- ITEM 1: KONDISI TRANSPORTASI (SESUAI DOKUMEN MFI BARIS 180-199) --}}
                    <div>
                        <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin-bottom: 0.55rem;">
                            1. Kondisi Transportasi (Truk Pengangkut Bahan) <span style="color:#ef4444;">*</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                            <label class="transport-radio-card active" id="cardTransportClean">
                                <input type="radio" name="bebas_cemaran_st" value="1" checked style="display:none;" onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #15803d;">✔ Tidak Ada Cemaran / Najis / Kotoran</div>
                                    <div class="transport-card-desc">Bebas dari bau menyengat, air, lumpur, oli, najis &amp; cemaran kimia berbahaya</div>
                                </div>
                            </label>
                            <label class="transport-radio-card" id="cardTransportDirty">
                                <input type="radio" name="bebas_cemaran_st" value="0" style="display:none;" onchange="updateTransportCard(this)">
                                <div class="transport-card-text">
                                    <div class="transport-card-title" style="color: #dc2626;">✖ Ada Cemaran / Tercemar</div>
                                    <div class="transport-card-desc">Truk basah, berbau tajam, berdebu kotor, atau terdapat ceceran kotoran/minyak</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- ITEM 2: 4 PERTANYAAN JAMINAN HALAL STANDAR HACCP MFI --}}
                    {{-- 1. Mengangkut barang haram / najis --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">2. Apakah barang tersebut diangkut bersama dengan barang haram?</div>
                                <div class="halal-qa-sub">Armada tidak pernah digunakan bersamaan untuk mengangkut babi, bangkai, atau najis</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_angkut_haram_no" onclick="setHalalToggle('angkut_barang_haram', 0)">
                                    <span>✔</span> <span>Tidak</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_angkut_haram_yes" onclick="setHalalToggle('angkut_barang_haram', 1)">
                                    <span>✖</span> <span>Ya (Tercemar)</span>
                                </button>
                            </div>
                            <input type="hidden" name="angkut_barang_haram_st" id="input_angkut_barang_haram" value="{{ old('angkut_barang_haram_st', 0) }}">
                        </div>
                        <input type="text" name="komentar_transportasi" id="inputKomentarTransportasi" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_transportasi') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- 2. Terdaftar LPPOM MUI / BPJPH --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">3. Apakah bahan tersebut terdaftar &amp; disetujui oleh LPPOM MUI / BPJPH?</div>
                                <div class="halal-qa-sub">Bahan penolong resmi terdaftar dalam daftar bahan baku halal yang disetujui</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_lppom_yes" onclick="setHalalToggle('terdaftar_lppom', 1)">
                                    <span>✔</span> <span>Ya</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_lppom_no" onclick="setHalalToggle('terdaftar_lppom', 0)">
                                    <span>✖</span> <span>Tidak</span>
                                </button>
                            </div>
                            <input type="hidden" name="terdaftar_lppom_st" id="input_terdaftar_lppom" value="{{ old('terdaftar_lppom_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_lppom" id="inputKomentarLppom" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_lppom') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- 3. Mempunyai Sertifikat Halal --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">4. Apakah barang tersebut mempunyai sertifikat halal?</div>
                                <div class="halal-qa-sub">Salinan sertifikat halal resmi atau logo halal tertera jelas pada dokumen &amp; kemasan</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_sertifikat_yes" onclick="setHalalToggle('ada_sertifikat_halal', 1)">
                                    <span>✔</span> <span>Ya / Ada</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_sertifikat_no" onclick="setHalalToggle('ada_sertifikat_halal', 0)">
                                    <span>✖</span> <span>Tidak</span>
                                </button>
                            </div>
                            <input type="hidden" name="ada_sertifikat_halal_st" id="input_ada_sertifikat_halal" value="{{ old('ada_sertifikat_halal_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_sertifikat" id="inputKomentarSertifikat" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_sertifikat') }}" style="font-size: 0.85rem;">
                    </div>

                    {{-- 4. Sertifikat Halal Masih Berlaku --}}
                    <div class="halal-qa-row">
                        <div class="halal-qa-header">
                            <div class="halal-qa-info">
                                <div class="halal-qa-title">5. Apakah sertifikat halal barang tersebut masih berlaku?</div>
                                <div class="halal-qa-sub">Masa aktif sertifikat halal belum kadaluarsa saat kedatangan barang</div>
                            </div>
                            <div class="segmented-toggle">
                                <button type="button" class="segmented-toggle-btn active ok" id="btn_berlaku_yes" onclick="setHalalToggle('sertifikat_halal_berlaku', 1)">
                                    <span>✔</span> <span>Berlaku</span>
                                </button>
                                <button type="button" class="segmented-toggle-btn" id="btn_berlaku_no" onclick="setHalalToggle('sertifikat_halal_berlaku', 0)">
                                    <span>✖</span> <span>Kadaluarsa</span>
                                </button>
                            </div>
                            <input type="hidden" name="sertifikat_halal_berlaku_st" id="input_sertifikat_halal_berlaku" value="{{ old('sertifikat_halal_berlaku_st', 1) }}">
                        </div>
                        <input type="text" name="komentar_berlaku" id="inputKomentarBerlaku" class="form-control" placeholder="Komentar : -" value="{{ old('komentar_berlaku') }}" style="font-size: 0.85rem;">
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 2: PEMERIKSAAN KUALITAS BAHAN PENOLONG (FORM HACCP RESMI)             --}}
        {{-- ========================================================================= --}}
        <div id="qcSection2" class="card" style="display: none; border-radius: 12px; border: 1.5px solid #ddd6fe; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="card-step-badge violet" style="width: 28px; height: 28px; font-size: 0.85rem; font-weight: 800; border-radius: 50%; display: flex; align-items: center; justify-content: center;">2</span>
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 800; color: #581c87; margin: 0; line-height: 1.25;">Tahap 2: Pengujian Mutu Fisik Bahan Penolong</h2>
                        <span style="font-size: 0.75rem; color: #6d28d9; font-weight: 700;">Analisa kondisi isi, kekeringan, kemasan zak &amp; toleransi reject</span>
                    </div>
                </div>
            </div>

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.15rem;">
                {{-- INFO STRIP ITEM & SPESIFIKASI JENIS --}}
                <div style="background: #faf5ff; border: 1.5px solid #ddd6fe; border-radius: 10px; padding: 0.65rem 0.95rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                        <span style="font-weight: 800; color: #6d28d9;">🧂 Bahan Baku:</span>
                        <span id="tab2InfoBarang" style="font-weight: 700; color: #1e293b;">-</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                        <span style="font-weight: 800; color: #7c3aed;">🏷️ Nama Jenis:</span>
                        <span id="tab2InfoJenis" style="font-weight: 800; color: #7c3aed;">-</span>
                    </div>
                </div>

                {{-- SUBCARD 1: ISI RAW MATERIAL & KEMASAN STATUS (MODERN CHOICE BUTTONS) --}}
                <div class="bp-subcard">
                    <span class="bp-subcard-title">
                        <span>🔍</span> <span>2. ISI RAW MATERIAL &amp; KONDISI KEMASAN</span>
                    </span>
                    <div class="qc-grid-2col">
                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.45rem;">2. ISI RAW MATERIAL :</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn active ok" id="btnRawOk" onclick="setRawMaterialStatus('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn" id="btnRawTdkStd" onclick="setRawMaterialStatus('TDK_STD')">
                                    <span>✖</span> <span>TDK STD</span>
                                </button>
                            </div>
                            <input type="hidden" name="bp_status_raw_material" id="inputRawMaterial" value="OK">
                        </div>

                        <div>
                            <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.45rem;">KEMASAN :</label>
                            <div class="modern-choice-grid">
                                <button type="button" class="modern-choice-btn active ok" id="btnKemasanOk" onclick="setKemasanKondisi('OK')">
                                    <span>✔</span> <span>OK</span>
                                </button>
                                <button type="button" class="modern-choice-btn" id="btnKemasanTdkStd" onclick="setKemasanKondisi('TIDAK_STANDARD')">
                                    <span>✖</span> <span>TIDAK STANDARD</span>
                                </button>
                            </div>
                            <input type="hidden" name="bp_kemasan_kondisi" id="inputKemasanKondisi" value="OK">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 2: PARAMETER FISIK KONDISI ISI (2x2 GRID PERSIS DOKUMEN MFI BARIS 384-420) --}}
                <div class="bp-subcard">
                    <span class="bp-subcard-title" style="color: #6d28d9;">
                        <span>🧪</span> <span>KONDISI FISIK ISI (RAW MATERIAL):</span>
                    </span>
                    <div class="modern-checklist-grid">
                        {{-- KERING (Default Standar OK) --}}
                        <div class="modern-check-card is-ok is-checked" id="card_bp_isi_kering" onclick="toggleModernCheck('checkIsiKering')">
                            <div class="modern-check-label">
                                <span>🌾</span> <span>KERING</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiKering">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_isi_kering" id="checkIsiKering" value="1" checked style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        {{-- BASAH --}}
                        <div class="modern-check-card is-danger" id="card_bp_isi_basah" onclick="toggleModernCheck('checkIsiBasah')">
                            <div class="modern-check-label">
                                <span>💧</span> <span>BASAH</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiBasah">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_isi_basah" id="checkIsiBasah" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        {{-- GUMPAL --}}
                        <div class="modern-check-card is-warning" id="card_bp_isi_gumpal" onclick="toggleModernCheck('checkIsiGumpal')">
                            <div class="modern-check-label">
                                <span>🧱</span> <span>GUMPAL</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiGumpal">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_isi_gumpal" id="checkIsiGumpal" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        {{-- BERMINYAK --}}
                        <div class="modern-check-card is-warning" id="card_bp_isi_berminyak" onclick="toggleModernCheck('checkIsiBerminyak')">
                            <div class="modern-check-label">
                                <span>🛢️</span> <span>BERMINYAK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkIsiBerminyak">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_isi_berminyak" id="checkIsiBerminyak" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 3: PARAMETER FISIK KEMASAN (2x2 GRID PERSIS DOKUMEN MFI BARIS 422-460) --}}
                <div class="bp-subcard">
                    <span class="bp-subcard-title" style="color: #475569;">
                        <span>📦</span> <span>KONDISI KEMASAN / WADAH ZAK (Ketuk jika ditemukan cacat):</span>
                    </span>
                    <div class="modern-checklist-grid">
                        {{-- KOTOR --}}
                        <div class="modern-check-card is-danger" id="card_bp_kemasan_kotor" onclick="toggleModernCheck('checkKemasanKotor')">
                            <div class="modern-check-label">
                                <span>🧼</span> <span>KOTOR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanKotor">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_kemasan_kotor" id="checkKemasanKotor" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        {{-- APEK --}}
                        <div class="modern-check-card is-danger" id="card_bp_kemasan_apek" onclick="toggleModernCheck('checkKemasanApek')">
                            <div class="modern-check-label">
                                <span>👃</span> <span>APEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanApek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_kemasan_apek" id="checkKemasanApek" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        {{-- JAMUR --}}
                        <div class="modern-check-card is-danger" id="card_bp_kemasan_jamur" onclick="toggleModernCheck('checkKemasanJamur')">
                            <div class="modern-check-label">
                                <span>🍄</span> <span>JAMUR</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanJamur">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_kemasan_jamur" id="checkKemasanJamur" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>

                        {{-- SOBEK --}}
                        <div class="modern-check-card is-danger" id="card_bp_kemasan_sobek" onclick="toggleModernCheck('checkKemasanSobek')">
                            <div class="modern-check-label">
                                <span>✂️</span> <span>SOBEK</span>
                            </div>
                            <div class="modern-check-indicator" id="indicator_checkKemasanSobek">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <input type="checkbox" name="bp_kemasan_sobek" id="checkKemasanSobek" value="1" style="display:none;" onchange="onModernCheckboxChanged(this)">
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 4: HASIL PEMERIKSAAN KUANTITAS --}}
                <div class="bp-subcard violet-accent">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.35rem;">
                        <span class="bp-subcard-title">
                            <span>⚖️</span> <span>HASIL PEMERIKSAAN KUANTITAS</span>
                        </span>
                        <span id="labelBpNetto" style="font-size: 0.95rem; font-weight: 900; color: #7c3aed; background: #ffffff; padding: 4px 12px; border-radius: 8px; border: 1.5px solid #ddd6fe;">
                            0.00 KG
                        </span>
                    </div>

                    <div class="qc-grid-2col">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 800; color: #0f172a;">Jumlah Lolos / Gross (KG) <span style="color:#ef4444;">*</span></label>
                            <div class="input-suffix-wrap">
                                <input type="number" step="0.01" min="0" name="bp_qty_gross" id="bpQtyGross" class="form-control" placeholder="0.00" value="{{ old('bp_qty_gross') }}" style="font-weight: 800;" required>
                                <span class="input-suffix-text">KG</span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="color: #dc2626; font-weight: 800;">Qty Reject / Rusak (KG)</label>
                            <div class="input-suffix-wrap">
                                <input type="number" step="0.01" min="0" name="bp_qty_reject" id="bpQtyReject" class="form-control" placeholder="0.00" value="{{ old('bp_qty_reject', 0) }}" style="font-weight: 800;">
                                <span class="input-suffix-text">KG</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBCARD 5: KESIMPULAN & KOMENTAR PEMERIKSAAN (SESUAI DOKUMEN MFI BARIS 463-503) --}}
                <div class="bp-subcard">
                    <input type="hidden" name="bp_kesimpulan" id="inputBpKesimpulan" value="{{ old('bp_kesimpulan', 'TERIMA') }}">
                    <input type="hidden" name="kesimpulan_qc" id="inputKesimpulanQc" value="{{ old('bp_kesimpulan', 'TERIMA') }}">

                    <div class="qc-disposisi-grid">
                        <!-- KARTU TERIMA -->
                        <div class="qc-disposisi-card active terima" id="cardDisposisiTerima" onclick="setKesimpulanBp('TERIMA')">
                            <div class="qc-disposisi-head">
                                <div class="qc-disposisi-title-wrap">
                                    <span class="qc-disposisi-radio-icon"></span>
                                    <span class="qc-disposisi-title">TERIMA</span>
                                </div>
                                <span class="qc-disposisi-status-badge">✓</span>
                            </div>
                            <div class="qc-disposisi-qty-wrap">
                                <span class="qc-disposisi-qty-label">Kuantitas Diterima:</span>
                                <div class="qc-disposisi-input-box">
                                    <input type="number" step="any" min="0" name="qty_terima" id="inputQtyTerima" class="qc-disposisi-input" value="0" readonly>
                                    <span class="qc-disposisi-unit">kg</span>
                                </div>
                            </div>
                        </div>

                        <!-- KARTU TOLAK -->
                        <div class="qc-disposisi-card tolak" id="cardDisposisiTolak" onclick="setKesimpulanBp('TOLAK')">
                            <div class="qc-disposisi-head">
                                <div class="qc-disposisi-title-wrap">
                                    <span class="qc-disposisi-radio-icon"></span>
                                    <span class="qc-disposisi-title">TOLAK</span>
                                </div>
                                <span class="qc-disposisi-status-badge">✕</span>
                            </div>
                            <div class="qc-disposisi-qty-wrap">
                                <span class="qc-disposisi-qty-label">Kuantitas Ditolak:</span>
                                <div class="qc-disposisi-input-box">
                                    <input type="number" step="any" min="0" name="qty_tolak" id="inputQtyTolak" class="qc-disposisi-input" value="0" readonly>
                                    <span class="qc-disposisi-unit">kg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CATATAN / KOMENTAR PENGUJIAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                            KOMENTAR PEMERIKSAAN :
                        </label>
                        <textarea name="bp_komentar" id="inputBpKomentar" rows="3" class="form-control" placeholder="Komentar hasil uji mutu bahan penolong..." style="font-size: 0.85rem; line-height: 1.45;">{{ old('bp_komentar') }}</textarea>
                    </div>
                </div>

                {{-- SUBCARD 6: OTORISASI & PEMERIKSA (SESUAI DOKUMEN MFI BARIS 504-542) --}}
                <div class="bp-subcard" style="padding: 0; background: transparent; border: none; box-shadow: none;">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        {{-- PETUGAS QC RAW MATERIAL --}}
                        <div class="qc-auth-card">
                            <div class="qc-auth-header">
                                <div>
                                    <div class="qc-auth-title">QC RAW MATERIAL</div>
                                    <div class="qc-auth-sub">Petugas pemeriksa kedatangan bahan penolong</div>
                                </div>
                                <span class="qc-auth-badge verified">Terverifikasi ID</span>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Nama Petugas QC (Bisa Diedit/Ketik Langsung)</label>
                                </div>
                                <input type="text" name="petugas_qc_nama" id="inputPetugasQc" class="form-control" placeholder="Nama Petugas QC" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Petugas QC') }}" style="font-weight: 800; color: #0f172a;">
                            </div>
                        </div>

                        {{-- QC SUPERVISOR --}}
                        <div class="qc-auth-card">
                            <div class="qc-auth-header">
                                <div>
                                    <div class="qc-auth-title">QC Supervisor</div>
                                    <div class="qc-auth-sub">Peninjau &amp; Persetujuan Rilis Bahan</div>
                                </div>
                                <span class="qc-auth-badge pending">Menunggu Review</span>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div class="qc-field-header">
                                    <label class="qc-field-label">Tugaskan Supervisor Peninjau (Bisa Diedit/Ketik Langsung)</label>
                                </div>
                                <input type="text" name="qc_supervisor_nama" id="inputSupervisorQc" list="listSupervisorQc" class="form-control" placeholder="Pilih atau ketik nama Supervisor..." value="{{ old('qc_supervisor_nama', 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" style="font-weight: 800; color: #0f172a;">
                                <datalist id="listSupervisorQc">
                                    <option value="Hendrawan, S.TP (SPV QC Shift Pagi)">
                                    <option value="Rina Wijaya, S.Si (Supervisor QC Lab)">
                                    <option value="Dedi (QC Bahan Baku)">
                                    <option value="Bambang (Produksi Magelang)">
                                    <option value="Super Administrator">
                                </datalist>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM DOCK NAVIGATION --}}
        <div class="bottom-dock-nav">
            <button type="button" class="btn btn-secondary" id="dockBtnPrev" onclick="switchBpTab(1)" style="display: none; border-radius: 8px; font-weight: 700;">
                &larr; Tahap 1
            </button>
            <button type="button" class="btn btn-primary" id="dockBtnNext" onclick="switchBpTab(2)" style="background: #7c3aed; border-color: #7c3aed; border-radius: 8px; font-weight: 800; margin-left: auto;">
                <span id="dockBtnNextText">Lanjut: Mutu Bahan</span>
                <span id="dockBtnNextIcon">&rarr;</span>
            </button>
        </div>
    </form>
</div>

{{-- SUBMIT OVERLAY LOADING --}}
<div id="qcSubmitOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(4px); color: #ffffff;">
    <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); text-align: center; max-width: 320px; width: 90%;">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem; animation: pulse 1.5s infinite;">🧂</div>
        <h3 id="overlayTitle" style="font-size: 1.1rem; font-weight: 800; margin: 0 0 0.5rem;">Menyimpan QC Bahan Penolong...</h3>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0;">Memvalidasi mutu &amp; memperbarui sistem.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            poDetailsUrl: "{{ url('gudang/qc/inbound/po') }}",
            poListData: @json($poListData)
        };
    </script>
    <script src="{{ asset('js/gudang/qc/bahan-penolong/bahan-penolong-create.js') }}"></script>
@endpush
