@extends('layouts.app')

@section('title', 'Catat Hasil Produksi Harian - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-create.css') }}">
@endpush

@section('content')
<div class="produksi-container">
    {{-- Breadcrumb & Header --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.index') }}" style="text-decoration: none; color: #0284c7; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.5rem; display: inline-block;">
            Kembali ke Buku Rekap HPP Produksi
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.01em;">
                    Formulir Hasil Produksi &amp; Kalkulasi HPP Harian
                </h1>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                    Pencatatan aktual hasil produksi harian, pemakaian bahan baku, energi CNG, upah operator, dan kalkulasi rendemen otomatis.
                </p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('produksi.store') }}" method="POST" id="formProduksi">
        @csrf

        {{-- BAGIAN 1: INFORMASI DOKUMEN & INTEGRASI PEMAKAIAN BAHAN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>1. Dokumen Pengeluaran Gudang &amp; Parameter Lini Produksi</span>
                </strong>
            </div>
            <div style="padding: 1.25rem;">
                {{-- Status Dokumen otomatis POSTED (Pencatatan aktual pasca-produksi final) --}}
                <input type="hidden" name="status_cd" id="status_cd" value="POSTED">

                {{-- LANGKAH UTAMA: TARIK DATA DARI DOKUMEN BPPB GUDANG --}}
                <div style="background: #f8fafc; padding: 0.95rem 1.15rem; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                            <span>Tarik Data Dokumen Pengeluaran Gudang (BPPB):</span>
                        </span>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            Memilih dokumen BPPB otomatis menyinkronkan Lini Produksi dan Rincian Bahan Baku.
                        </span>
                    </div>

                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <select name="pakai_id" id="pakai_id" class="form-control" style="flex: 1; min-width: 280px; font-size: 0.85rem;" onchange="loadPakaiData(this.value)">
                            <option value="">-- Pilih Dokumen Pengeluaran Bahan (BPPB Siap Proses) --</option>
                            @foreach ($pakaiList as $pk)
                                <option value="{{ $pk->pakai_id }}" data-tujuan="{{ $pk->tujuan_pemakaian }}" data-tgl="{{ Carbon\Carbon::parse($pk->pakai_tgl)->format('Y-m-d') }}">
                                    [{{ $pk->pakai_no }}] {{ Carbon\Carbon::parse($pk->pakai_tgl)->format('d/m/Y') }} - {{ $pk->tujuan_pemakaian }} ({{ $pk->details->count() }} item bahan)
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="loadPakaiData(document.getElementById('pakai_id').value)" style="padding: 0.45rem 0.85rem;">
                            Tarik Ulang
                        </button>
                    </div>
                    <div id="pakaiMatchNotice" style="display: none; font-size: 0.75rem; font-weight: 600; margin-top: 0.4rem;"></div>
                    <div id="pakaiLoading" style="display: none; font-size: 0.75rem; color: #0284c7; margin-top: 0.35rem; font-weight: 600;">
                        Memuat rincian bahan dari dokumen gudang...
                    </div>

                    {{-- TABEL PEMAKAIAN BAHAN BAKU (SESUAI BLUEPRINT 9.B.1) --}}
                    @include('produksi.partials.card-pemakaian-table')
                </div>

                {{-- PARAMETER PRODUKSI (OTOMATIS TERISI & DAPAT DISESUAIKAN) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="produksi_tgl" id="produksi_tgl" class="form-control" value="{{ old('produksi_tgl', date('Y-m-d')) }}" required onchange="updateHariLabel(); fetchNextKarton();">
                        <div id="hariLabel" style="font-size: 0.75rem; color: #0284c7; font-weight: 600; margin-top: 0.25rem;"></div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Lini Produksi / Tujuan <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="lini_produksi" id="lini_produksi" class="form-control" required onchange="onLiniProduksiChange()">
                            <option value="">-- Pilih Lini Produksi / Tujuan --</option>
                            @if(isset($liniList) && $liniList->isNotEmpty())
                                @foreach($liniList->groupBy(fn($item) => $item->kategori_lini ?: 'UMUM') as $kategori => $items)
                                    <optgroup label="{{ $kategori }}">
                                        @foreach($items as $lini)
                                            <option value="{{ $lini->lini_nm }}" 
                                                data-tipe="{{ $lini->tipe_batch }}" 
                                                data-kategori="{{ $lini->kategori_lini }}" 
                                                data-cd="{{ $lini->lini_cd }}" 
                                                {{ old('lini_produksi') === $lini->lini_nm ? 'selected' : '' }}>
                                                {{ $lini->lini_nm }} {{ $lini->keterangan ? '('.$lini->keterangan.')' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem;">Otomatis sinkron dari BPPB atau dapat dipilih manual.</small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Gudang Penerima Hasil Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="gudang_id" id="gudang_id" class="form-control" required>
                            <option value="">-- Pilih Gudang Penerima --</option>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem;">Lokasi gudang fisik tempat penyetoran barang jadi atau WIP.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: SHIFT KERJA, PENOMORAN BATCH & KEMASAN KARTON --}}
        @include('produksi.partials.card-shift-karton')

        {{-- BAGIAN 3: BIAYA BAHAN BAKU & KEMASAN (DIRECT MATERIALS) --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>3. Biaya Bahan Baku &amp; Kemasan (Direct Materials)</span>
                </strong>
                <span id="badgeSubtotalBahan" style="font-size: 0.8rem; font-weight: 700; color: #334155; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.2rem 0.6rem; border-radius: 4px;">
                    Subtotal Bahan: Rp 0
                </span>
            </div>
            <div style="padding: 1.25rem;">
                {{-- Baris 1: Singkong Mentah & Minyak Goreng --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div style="background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                            Singkong Mentah Masuk
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Kuantitas (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="singkong_qty" id="singkong_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('singkong_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                            <div style="flex: 1.2;">
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Nilai Rupiah (Rp)</label>
                                <input type="number" step="0.01" min="0" name="singkong_nilai" id="singkong_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('singkong_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                            Minyak Sawit &amp; Kelapa
                        </span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; margin-bottom: 0.4rem;">
                            <div>
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Sawit (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="minyak_sawit_qty" id="minyak_sawit_qty" class="form-control calc-trigger" style="text-align: right;" value="{{ old('minyak_sawit_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                            <div>
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Kelapa (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="minyak_kelapa_qty" id="minyak_kelapa_qty" class="form-control calc-trigger" style="text-align: right;" value="{{ old('minyak_kelapa_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Total Rupiah Minyak (Rp)</label>
                            <input type="number" step="0.01" min="0" name="minyak_nilai" id="minyak_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 600;" value="{{ old('minyak_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                        </div>
                        <div id="liveMinyakRasio" style="font-size: 0.725rem; color: #475569; font-weight: 600; margin-top: 0.35rem;">
                            Rasio Minyak: 0.00%
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                            Bumbu &amp; Perenyah (Rp)
                        </span>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Nilai Bumbu/Garam/Perenyah</label>
                        <input type="number" step="0.01" min="0" name="bumbu_nilai" id="bumbu_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('bumbu_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>
                </div>

                {{-- Baris 2: Kemasan & Packaging --}}
                <div style="background: #f8fafc; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                        Rincian Bahan Kemasan &amp; Packaging (Rp)
                    </span>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Karton Baru</label>
                            <input type="number" step="0.01" min="0" name="karton_baru_nilai" id="karton_baru_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('karton_baru_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Karton Bekas</label>
                            <input type="number" step="0.01" min="0" name="karton_bekas_nilai" id="karton_bekas_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('karton_bekas_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Plastik HD 90x100</label>
                            <input type="number" step="0.01" min="0" name="plastik_hd_nilai" id="plastik_hd_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('plastik_hd_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Lakban Besar</label>
                            <input type="number" step="0.01" min="0" name="lakban_besar_nilai" id="lakban_besar_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('lakban_besar_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Lakban Kecil</label>
                            <input type="number" step="0.01" min="0" name="lakban_kecil_nilai" id="lakban_kecil_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('lakban_kecil_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Tali Rafia</label>
                            <input type="number" step="0.01" min="0" name="tali_rafia_nilai" id="tali_rafia_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('tali_rafia_nilai', 0) }}" oninput="calcAll()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 4 & 5: ENERGI (CNG) & TENAGA KERJA --}}
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            {{-- ENERGI GAS CNG --}}
            <div class="card">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">4. Gas Alam / CNG (Boiler &amp; Fryer)</strong>
                    <span style="font-size: 0.7rem; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700;">Flow Meter Harian</span>
                </div>
                <div style="padding: 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Meteran CNG (MMBTU) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" step="0.0001" min="0" name="cng_mmbtu" id="cng_mmbtu" class="form-control calc-trigger" style="text-align: right; font-weight: 700;" value="{{ old('cng_mmbtu', 0) }}" placeholder="0" oninput="calcCng()">
                            <small style="color: #64748b; font-size: 0.7rem;">Delta flow meter fisik.</small>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Tarif per MMBTU (Rp)
                            </label>
                            <input type="number" step="0.01" min="0" name="cng_tarif" id="cng_tarif" class="form-control calc-trigger" style="text-align: right;" value="{{ old('cng_tarif', 226800.00) }}" oninput="calcCng()">
                            <small style="color: #64748b; font-size: 0.7rem;">Tarif acuan (dapat diedit).</small>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            Total Rupiah CNG (Rp)
                        </label>
                        <input type="number" step="0.01" min="0" name="cng_nilai" id="cng_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a; background: #f8fafc;" value="{{ old('cng_nilai', 0) }}" oninput="calcAll()">
                        <small style="color: #64748b; font-size: 0.7rem;">Otomatis (MMBTU x Tarif) atau isi manual.</small>
                    </div>
                </div>
            </div>

            {{-- TENAGA KERJA --}}
            <div class="card">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">5. Tenaga Kerja (Tarif Rp 91.300 / orang)</strong>
                </div>
                <div style="padding: 1.25rem;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Langsung (Org)
                            </label>
                            <input type="number" min="0" name="tk_langsung_org" id="tk_langsung_org" class="form-control calc-trigger" style="text-align: center; font-weight: 700;" value="{{ old('tk_langsung_org', 0) }}" placeholder="0" oninput="calcTk()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Tdk Langsung (Org)
                            </label>
                            <input type="number" min="0" name="tk_tidak_langsung_org" id="tk_tidak_langsung_org" class="form-control calc-trigger" style="text-align: center; font-weight: 700;" value="{{ old('tk_tidak_langsung_org', 0) }}" placeholder="0" oninput="calcTk()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Training (Org)
                            </label>
                            <input type="number" min="0" name="tk_training_org" id="tk_training_org" class="form-control calc-trigger" style="text-align: center;" value="{{ old('tk_training_org', 0) }}" placeholder="0" oninput="calcTk()">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Tarif/Org (Rp)
                            </label>
                            <input type="number" step="0.01" min="0" name="tk_tarif_per_org" id="tk_tarif_per_org" class="form-control calc-trigger" style="text-align: right;" value="{{ old('tk_tarif_per_org', 91300.00) }}" oninput="calcTk()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Total Upah Harian (Rp)
                            </label>
                            <input type="number" step="0.01" min="0" name="tk_total_nilai" id="tk_total_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a; background: #f8fafc;" value="{{ old('tk_total_nilai', 0) }}" oninput="calcAll()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 6: BIAYA OVERHEAD PABRIK (FACTORY OVERHEAD / FOH) --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <strong style="color: #0f172a; font-size: 0.95rem;">6. Biaya Overhead Pabrik (FOH)</strong>
                <span id="badgeSubtotalOverhead" style="font-size: 0.8rem; font-weight: 700; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.2rem 0.6rem; border-radius: 4px;">
                    Subtotal FOH: Rp 0
                </span>
            </div>
            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem;">
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Fotocopy / ATK</label>
                        <input type="number" step="0.01" min="0" name="fotocopy_nilai" id="fotocopy_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('fotocopy_nilai', 0) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Sarung Tangan Plastik</label>
                        <input type="number" step="0.01" min="0" name="sarung_tangan_plastik_nilai" id="sarung_tangan_plastik_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('sarung_tangan_plastik_nilai', 0) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Sarung Tangan Kain</label>
                        <input type="number" step="0.01" min="0" name="sarung_tangan_kain_nilai" id="sarung_tangan_kain_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('sarung_tangan_kain_nilai', 0) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Pengawasan Mutu (QC)</label>
                        <input type="number" step="0.01" min="0" name="qc_pengawasan_nilai" id="qc_pengawasan_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('qc_pengawasan_nilai', 0) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 700; margin-bottom: 0;">Listrik &amp; Air + Telp</label>
                            <span style="font-size: 0.65rem; color: #d97706; font-weight: 700;">(Tagihan Bulanan)</span>
                        </div>
                        <input type="number" step="0.01" min="0" name="listrik_air_telp_nilai" id="listrik_air_telp_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('listrik_air_telp_nilai', 0) }}" oninput="calcAll()">
                        <small style="color: #64748b; font-size: 0.675rem; display: block; margin-top: 0.2rem;">Isi 0 / estimasi. Bisa disesuaikan di Rekap HPP.</small>
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Pemeliharaan Mesin</label>
                        <input type="number" step="0.01" min="0" name="pemeliharaan_mesin_nilai" id="pemeliharaan_mesin_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('pemeliharaan_mesin_nilai', 0) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Penyusutan Mesin</label>
                        <input type="number" step="0.01" min="0" name="penyusutan_mesin_nilai" id="penyusutan_mesin_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('penyusutan_mesin_nilai', 0) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Limbah Padat</label>
                        <input type="number" step="0.01" min="0" name="limbah_padat_nilai" id="limbah_padat_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('limbah_padat_nilai', 360000.00) }}" oninput="calcAll()">
                    </div>
                    <div>
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Bahan Kimia Limbah</label>
                        <input type="number" step="0.01" min="0" name="limbah_kimia_nilai" id="limbah_kimia_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('limbah_kimia_nilai', 0) }}" oninput="calcAll()">
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 7: HASIL BARANG PRODUKSI (SESUAI BLUEPRINT 9.B - KIRI) --}}
        @include('produksi.partials.card-output-dinamis')

        {{-- RINGKASAN KALKULASI REAL-TIME --}}
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 1.15rem 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; align-items: center;">
                {{-- Live Biaya --}}
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                        Total Biaya Produksi
                    </span>
                    <div id="liveTotalBiaya" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        Rp 0
                    </div>
                </div>

                {{-- Live Rendemen --}}
                <div style="text-align: center;">
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                        Rendemen Singkong
                    </span>
                    <div id="liveRendemen" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        0.00%
                    </div>
                    <span id="liveRendemenStatus" style="font-size: 0.725rem; font-weight: 600; color: #64748b;">
                        Menunggu timbangan
                    </span>
                </div>

                {{-- Live HPP / Kg --}}
                <div style="text-align: right;">
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                        HPP Riil per Kg WIP
                    </span>
                    <div id="liveHpp" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        Rp 0 / kg
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center;">
            <a href="{{ route('produksi.index') }}" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; font-weight: 600;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem; font-weight: 700;">
                Simpan Lembar Produksi Harian
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.appConfig = {
            nextKartonUrl: "{{ route('produksi.next-karton') }}",
            pakaiDataUrl: "{{ url('produksi/pakai-data') }}",
            barangHasilList: @json($barangHasilList ?? []),
            oldOutputItems: @json(old('output_items', []))
        };
    </script>
    <script src="{{ asset('js/produksi/produksi-create.js') }}?v={{ time() }}"></script>
@endpush
@endsection
