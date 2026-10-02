@extends('layouts.qc-mobile')

@section('title', 'Form Uji QC Bahan Masuk (HACCP 7 Komoditas) - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-form.css') }}">
@endpush

@section('content')
<div style="max-width: 880px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- Header Banner HACCP PT Mirasa --}}
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%); border-radius: 14px 14px 0 0; padding: 1.5rem 1.75rem; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 PT. MIRASA FOOD INDUSTRY &bull; HACCP-04
                    </span>
                    <span id="badgeDocNo" style="background: #10b981; color: #ffffff; font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 20px;">
                        No. Dok: MFI/HACCP-04/FRM-03/048/VIII/2021
                    </span>
                </div>
                <h1 id="titleFormHaccp" style="font-size: 1.45rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    Checklist Standar Kebeterimaan Singkong
                </h1>
                <p id="descFormHaccp" style="font-size: 0.85rem; margin: 0.35rem 0 0; opacity: 0.9;">
                    Laporan Kedatangan Bahan Masuk sesuai Standar HACCP &amp; Jaminan Halal PT Mirasa Food Industry
                </p>
            </div>
            <a href="{{ route('qc.inbound.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0284c7; border-radius: 8px; font-weight: 700;">
                📋 Riwayat Tiket
            </a>
        </div>
    </div>

    {{-- SELECTOR 7 KOMODITAS / FORM HACCP RESMI --}}
    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-top: none; border-radius: 0 0 14px 14px; padding: 0.9rem 1.25rem; margin-bottom: 1.25rem;">
        <div style="font-size: 0.775rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏷️</span> <span>PILIH FORMULIR RESMI HACCP SESUAI KOMODITAS MASUK:</span>
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
            🚚 1. Armada &amp; Halal
        </button>
        <button type="button" class="btn" id="tabBtn2" onclick="switchQcTab(2)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            📏 2. Pemeriksaan Parameter
        </button>
        <button type="button" class="btn" id="tabBtn3" onclick="switchQcTab(3)" style="border-radius: 10px; font-size: 0.825rem; font-weight: 700; padding: 0.75rem 0.5rem; text-align: center; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer; transition: all 0.15s;">
            🍟 3. Pengujian II (Fryer)
        </button>
    </div>

    <form action="{{ route('qc.inbound.store') }}" method="POST" id="qcForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf
        <input type="hidden" name="kategori_barang" id="kategoriBarangInput" value="{{ old('kategori_barang', 'SINGKONG') }}">
        <input type="hidden" name="status_uji_goreng" id="statusUjiGorengInput" value="SELESAI">

        {{-- ========================================================================= --}}
        {{-- TAHAP 1: DOKUMEN KEDATANGAN, TRANSPORTASI & AUDIT HALAL                    --}}
        {{-- ========================================================================= --}}
        <div id="qcSection1" class="card" style="border-radius: 12px; border-top: 5px solid #0284c7; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 1: Laporan Kedatangan &amp; Standar Halal / Transportasi</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Verifikasi surat jalan, nomor DO, produsen &amp; jaminan bebas barang haram/najis</span>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.1rem;">
                {{-- PILIH DARI PO AKTIF --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #1e293b;">
                        Referensi Purchase Order (PO) <span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">(Opsional - pilih PO untuk auto-fill data)</span>
                    </label>
                    <select name="po_id" id="poSelect" class="form-control" onchange="onPoSelected(this)" style="font-weight: 600;">
                        <option value="">-- Tanpa PO / Kiriman Langsung --</option>
                        @foreach ($pos as $p)
                            <option value="{{ $p->po_id }}" {{ old('po_id', $selectedPo?->po_id) == $p->po_id ? 'selected' : '' }}>
                                {{ $p->po_no }} &bull; {{ $p->supplier?->supplier_nm }} (Sisa: {{ $p->details->sum(fn($d) => max(0, $d->pesan_qty - $d->terima_qty)) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- NAMA PRODUSEN, SUPPLIER, GUDANG, NEGARA --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Mitra Supplier (Pengirim) <span style="color:#ef4444;">*</span></label>
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
                        <label class="form-label" style="font-weight: 700;">Perusahaan / Cabang Tujuan Bongkar <span style="color:#ef4444;">*</span></label>
                        <select name="gudang_id" id="gudangSelect" class="form-control" required>
                            @foreach ($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id ?? auth()->user()?->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->display_name ?? $g->gudang_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Nama Produsen (Pabrik Pembuat)</label>
                        <input type="text" name="nama_produsen" id="namaProdusenInput" class="form-control" placeholder="Contoh: PT Wilmar / Mitra Tani / Miwon" value="{{ old('nama_produsen') }}">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Negara Produsen</label>
                        <input type="text" name="negara_produsen" class="form-control" value="{{ old('negara_produsen', 'Indonesia') }}">
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
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700;">Jumlah di Pabrik (Diterima)</label>
                        <input type="number" step="0.01" min="0" name="jumlah_di_pabrik" id="inputJumlahPabrik" class="form-control" placeholder="0.00" value="{{ old('jumlah_di_pabrik') }}" oninput="syncQuantityFields()">
                    </div>

                    {{-- Dynamic Sample Input berdasarkan komoditas --}}
                    <div class="form-group" id="groupSampleKg" style="margin-bottom: 0;">
                        <label class="form-label">Jumlah Sample (KG)</label>
                        <input type="number" step="0.1" name="jumlah_sample_kg" class="form-control" placeholder="Contoh: 10" value="{{ old('jumlah_sample_kg', 10.0) }}">
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
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 id="section2Title" style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 2: Pengujian I &bull; Sampling Fisik &amp; Parameter</h2>
                    <span id="section2Sub" style="font-size: 0.8rem; color: #64748b;">Standar mutu bahan baku, cacat fisik, dan timbangan kedatangan</span>
                </div>
                <button type="button" id="btnAddItemRow" class="btn btn-sm btn-secondary" onclick="addItemRow()" style="border-radius: 8px;">
                    + Tambah Item
                </button>
            </div>

            {{-- 1. FORM KHUSUS SINGKONG (MULTIPLE ITEMS / DYNAMIC CARDS) --}}
            <div id="singkongContainer" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- Item Cards rendered by JS --}}
            </div>

            {{-- 2. FORM KHUSUS MINYAK GORENG (SESUAI MFI/HACCP-04/FRM-03/029/VIII/2021) --}}
            <div id="minyakContainer" style="display: none; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700;">Komoditas Minyak Goreng <span style="color:red;">*</span></label>
                    <select name="minyak_barang_id" id="minyakBarangSelect" class="form-control" style="font-weight: 700;">
                        @foreach ($barangs as $b)
                            @if (stripos($b->barang_nm, 'minyak') !== false)
                                <option value="{{ $b->barang_id }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'KG' }})</option>
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
                    <select name="plastik_barang_id" id="plastikBarangSelect" class="form-control" style="font-weight: 700;">
                        @foreach ($barangs as $b)
                            @if (stripos($b->barang_nm, 'plastik') !== false || stripos($b->barang_nm, 'kemasan') !== false || stripos($b->barang_nm, 'opp') !== false || stripos($b->barang_nm, 'pp') !== false || stripos($b->barang_nm, 'roll') !== false)
                                <option value="{{ $b->barang_id }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})</option>
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
                    <select name="karton_barang_id" id="kartonBarangSelect" class="form-control" style="font-weight: 700;">
                        @foreach ($barangs as $b)
                            @if (stripos($b->barang_nm, 'karton') !== false || stripos($b->barang_nm, 'dus') !== false || stripos($b->barang_nm, 'box') !== false)
                                <option value="{{ $b->barang_id }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})</option>
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
                    <select name="bp_barang_id" id="bpBarangSelect" class="form-control" style="font-weight: 700;">
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
                                <option value="{{ $b->barang_id }}" data-commodity="{{ $katItem }}" data-name="{{ $nm }}">
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
                    <button type="button" class="btn" id="btnSimpanCepat" onclick="submitPengujian1Only()" style="border-radius: 8px; font-weight: 800; background: #059669; color: #ffffff; border: none; padding: 0.55rem 1.15rem; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">
                        💾 Simpan &amp; Teruskan ke Gudang
                    </button>
                    <button type="button" class="btn btn-primary" id="btnNextFryer" onclick="switchQcTab(3)" style="border-radius: 8px; font-weight: 700;">
                        Lanjut ke Pengujian II (Uji Goreng) &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAHAP 3: PENGUJIAN II (HASIL FRYER / UJI GORENG KHUSUS SINGKONG)            --}}
        {{-- ========================================================================= --}}
        <div id="qcSection3" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #f59e0b; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 3: Pengujian II &bull; Hasil Fryer &amp; Cacat Goreng (Singkong)</h2>
                    <span style="font-size: 0.8rem; color: #64748b;">Hasil uji goreng lab: Rasa (tidak pahit), tekstur renyah &amp; % defect</span>
                </div>
            </div>

            <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.25rem;">
                <div id="fryerParamsContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                    {{-- Dynamically mirrored from Tab 2 items --}}
                </div>

                {{-- KESIMPULAN AKHIR & TANDA TANGAN ACC --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem;">
                        📋 KESIMPULAN AKHIR MUTU BAHAN BAKU SINGKONG
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

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <label class="form-label">Petugas QC Pemeriksa</label>
                            <input type="text" name="petugas_qc_nama" class="form-control" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Petugas QC') }}" readonly style="background: #ffffff; font-weight: 700;">
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
    let currentKomoditas = 'SINGKONG';

    const KOMODITAS_CONFIG = {
        'SINGKONG': {
            docNo: 'MFI/HACCP-04/FRM-03/048/VIII/2021',
            title: 'Checklist Standar Kebeterimaan Singkong',
            desc: 'Laporan Kedatangan Singkong: Pengujian I (Fisik) & Pengujian II (Hasil Fryer / Uji Goreng)',
            labelNamaJenis: 'Nama Bahan / Jenis Singkong',
            hasPanenFields: true,
            sampleUnit: 'KG',
            showTab3: true,
        },
        'MINYAK': {
            docNo: 'MFI/HACCP-04/FRM-03/029/VIII/2021',
            title: 'Cheklist Pemeriksaan Kedatangan Minyak Goreng',
            desc: 'Laporan Kedatangan Minyak Goreng: Uji Parameter FFA di COA vs QC Mirasa & Kebersihan Tangki',
            labelNamaJenis: 'NAMA JENIS (Contoh: Minyak Sawit Curah)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'PLASTIK': {
            docNo: 'MFI/HACCP-04/FRM-03/030/VIII/2021',
            title: 'Cheklist Pemeriksaan Kedatangan Plastik',
            desc: 'Laporan Kedatangan Plastik: Cacat Kemasan & Uji Ketebalan / Keutuhan (Tidak Sobek)',
            labelNamaJenis: 'NAMA JENIS (Contoh: PP 08, OPP, Pouch)',
            hasPanenFields: false,
            sampleUnit: 'pcs',
            showTab3: false,
        },
        'KARTON': {
            docNo: 'MFI/HACCP-04/FRM-03/031/VIII/2021',
            title: 'Cheklist Pemeriksaan Kedatangan Karton',
            desc: 'Laporan Kedatangan Karton: Cacat Kemasan & Uji Dimensi (Panjang, Lebar, Tinggi, Spesifikasi)',
            labelNamaJenis: 'NAMA JENIS KARTON (Contoh: Master Box Balado)',
            hasPanenFields: false,
            sampleUnit: 'pcs',
            showTab3: false,
        },
        'MSG': {
            docNo: 'MFI/HACCP-04/FRM-03/032/VIII/2021',
            title: 'Cheklist Pemeriksaan Kedatangan MSG',
            desc: 'Laporan Kedatangan MSG: Uji Kondisi Fisik Bahan Penolong (Kering, Basah, Gumpal, Minyak) & Kemasan',
            labelNamaJenis: 'NAMA JENIS (Contoh: MSG Miku / Ajinomoto / Miwon)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'GARAM': {
            docNo: 'MFI/HACCP-04/FRM-03/033/VIII/2021',
            title: 'Cheklist Pemeriksaan Kedatangan Garam',
            desc: 'Laporan Kedatangan Garam: Uji Kondisi Fisik Bahan Penolong (Kering, Basah, Gumpal, Minyak) & Kemasan',
            labelNamaJenis: 'NAMA JENIS (Contoh: Garam Halus Beryodium)',
            hasPanenFields: false,
            sampleUnit: 'gr',
            showTab3: false,
        },
        'PERENYAH': {
            docNo: 'MFI/HACCP-04/FRM-03/063/IX/2023',
            title: 'Cheklist Pemeriksaan Kedatangan Perenyah',
            desc: 'Laporan Kedatangan Perenyah: Uji Kondisi Fisik Bahan Penolong (Kering, Basah, Gumpal, Minyak) & Kemasan',
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

        // Toggle sample input
        document.getElementById('groupSampleKg').style.display = (cfg.sampleUnit === 'KG') ? 'block' : 'none';
        document.getElementById('groupSampleGr').style.display = (cfg.sampleUnit === 'gr') ? 'block' : 'none';
        document.getElementById('groupSamplePcs').style.display = (cfg.sampleUnit === 'pcs') ? 'block' : 'none';

        // Toggle Tab 3 (Fryer) & Nav
        const tabBtn3 = document.getElementById('tabBtn3');
        const qcTabNav = document.getElementById('qcTabNav');
        const btnNextFryer = document.getElementById('btnNextFryer');
        const btnAddItemRow = document.getElementById('btnAddItemRow');

        if (cfg.showTab3) {
            tabBtn3.style.display = 'block';
            qcTabNav.style.gridTemplateColumns = 'repeat(3, 1fr)';
            btnNextFryer.style.display = 'inline-block';
            btnAddItemRow.style.display = 'inline-block';
            document.getElementById('section2Title').innerText = 'Tahap 2: Pengujian I • Sampling Fisik & Parameter';
            document.getElementById('section2Sub').innerText = 'Standar diameter, kebersihan tanah & kondisi visual singkong';
            document.getElementById('btnSimpanCepat').innerText = '💾 Simpan Pengujian I (Bongkar & Teruskan ke Gudang)';
        } else {
            tabBtn3.style.display = 'none';
            qcTabNav.style.gridTemplateColumns = 'repeat(2, 1fr)';
            btnNextFryer.style.display = 'none';
            btnAddItemRow.style.display = 'none';
            document.getElementById('section2Title').innerText = 'Tahap 2: Pemeriksaan Mutu & Parameter Kedatangan';
            document.getElementById('section2Sub').innerText = 'Formulir Cheklist HACCP PT Mirasa Food Industry';
            document.getElementById('btnSimpanCepat').innerText = '💾 Simpan & Teruskan ke Antrean Gudang';
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
    }

    function syncQuantityFields() {
        const sj = parseFloat(document.getElementById('inputJumlahSJ').value) || 0;
        const pabrik = parseFloat(document.getElementById('inputJumlahPabrik').value) || sj;

        if (currentKomoditas === 'MINYAK') {
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

    function prepareCommoditySubmission() {
        if (currentKomoditas === 'MINYAK') {
            const bId = document.getElementById('minyakBarangSelect').value;
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

    function submitPengujian1Only() {
        const form = document.getElementById('qcForm');
        if (!form.reportValidity()) {
            return;
        }

        prepareCommoditySubmission();

        if (currentKomoditas === 'SINGKONG') {
            const items = document.querySelectorAll('.qc-item-card');
            if (!items || items.length === 0) {
                alert('Minimal harus ada 1 item komoditas yang diinspeksi.');
                return;
            }

            if (confirm('Simpan hasil Pengujian I (Sampling Fisik) sekarang agar truk bisa langsung dibongkar ke gudang?\n\nCatatan: Hasil Pengujian II (Uji Goreng Lab) dapat dilengkapi menyusul kapan saja di riwayat tiket QC.')) {
                document.getElementById('statusUjiGorengInput').value = 'MENUNGGU_LAB';
                form.submit();
            }
        } else {
            // Minyak, Plastik, Karton, MSG, Garam, Perenyah langsung disimpan & diteruskan ke gudang
            document.getElementById('statusUjiGorengInput').value = 'SELESAI';
            form.submit();
        }
    }

    document.getElementById('qcForm').addEventListener('submit', function (e) {
        prepareCommoditySubmission();
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

        let barangOptions = '<option value="">-- Pilih Komoditas Singkong --</option>';
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

        document.getElementById('singkongContainer').appendChild(card);
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
            const firstItemNm = (po.items[0].barang_nm || '').toLowerCase();
            if (firstItemNm.includes('minyak')) {
                selectKomoditas('MINYAK');
                document.getElementById('minyakBarangSelect').value = po.items[0].barang_id;
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('minyakQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('karton') || firstItemNm.includes('dus') || firstItemNm.includes('box')) {
                selectKomoditas('KARTON');
                document.getElementById('kartonBarangSelect').value = po.items[0].barang_id;
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('kartonQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('plastik') || firstItemNm.includes('kemasan') || firstItemNm.includes('opp') || firstItemNm.includes('pp')) {
                selectKomoditas('PLASTIK');
                document.getElementById('plastikBarangSelect').value = po.items[0].barang_id;
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('plastikQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('msg') || firstItemNm.includes('micin') || firstItemNm.includes('glutamat')) {
                selectKomoditas('MSG');
                document.getElementById('bpBarangSelect').value = po.items[0].barang_id;
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('bpQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('garam') || firstItemNm.includes('salt')) {
                selectKomoditas('GARAM');
                document.getElementById('bpBarangSelect').value = po.items[0].barang_id;
                document.getElementById('inputJumlahSJ').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('inputJumlahPabrik').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
                document.getElementById('bpQtyGross').value = po.items[0].sisa_qty > 0 ? po.items[0].sisa_qty : po.items[0].pesan_qty;
            } else if (firstItemNm.includes('perenyah')) {
                selectKomoditas('PERENYAH');
                document.getElementById('bpBarangSelect').value = po.items[0].barang_id;
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
        }
    }

    // Inisialisasi awal saat load
    document.addEventListener('DOMContentLoaded', function () {
        selectKomoditas('{{ old("kategori_barang", "SINGKONG") }}');

        const poSelect = document.getElementById('poSelect');
        if (poSelect && poSelect.value) {
            onPoSelected(poSelect);
        } else {
            createItemCard();
        }
    });
</script>
@endsection
