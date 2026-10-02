@extends('layouts.app')

@section('title', 'Catat Hasil Produksi Harian - ERP PT Mirasa')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- Breadcrumb & Header --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.index') }}" style="text-decoration: none; color: #0284c7; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Buku Rekap HPP Produksi
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                    Formulir Hasil Produksi &amp; Kalkulasi HPP Harian
                </h1>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
                    Input data timbangan output WIP, konsumsi energi CNG, absensi pekerja, dan kalkulasi Rendemen harian otomatis.
                </p>
            </div>
            
            <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.4rem 0.85rem; color: #166534; font-size: 0.825rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                <span>🎯 Target Rendemen: &ge; 33.00%</span>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('produksi.store') }}" method="POST" id="formProduksi">
        @csrf

        {{-- BAGIAN 1: INFORMASI DOKUMEN & INTEGRASI PEMAKAIAN BAHAN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>📋 1. Header Dokumen &amp; Referensi Pemakaian Bahan Gudang</span>
                </strong>
            </div>
            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="produksi_tgl" id="produksi_tgl" class="form-control" value="{{ old('produksi_tgl', date('Y-m-d')) }}" required onchange="updateHariLabel()">
                        <div id="hariLabel" style="font-size: 0.75rem; color: #0284c7; font-weight: 600; margin-top: 0.25rem;"></div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Lini Produksi / Tujuan <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="lini_produksi" id="lini_produksi" class="form-control" required>
                            <option value="PRODUKSI IFM" {{ old('lini_produksi') === 'PRODUKSI IFM' ? 'selected' : '' }}>PRODUKSI IFM (Indofood)</option>
                            <option value="PRODUKSI PING-PING 2000" {{ old('lini_produksi') === 'PRODUKSI PING-PING 2000' ? 'selected' : '' }}>PRODUKSI PING-PING 2000</option>
                            <option value="PRODUKSI PING-PING" {{ old('lini_produksi') === 'PRODUKSI PING-PING' ? 'selected' : '' }}>PRODUKSI PING-PING (Umum)</option>
                            <option value="PRODUKSI LAINNYA" {{ old('lini_produksi') === 'PRODUKSI LAINNYA' ? 'selected' : '' }}>PRODUKSI LAINNYA</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Perusahaan / Lokasi Simpan WIP (Hasil Jadi) <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="gudang_id" id="gudang_id" class="form-control" required>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Status Dokumen <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="status_cd" id="status_cd" class="form-control" required>
                            <option value="POSTED" {{ old('status_cd', 'POSTED') === 'POSTED' ? 'selected' : '' }}>✅ POSTED (Suntik Stok WIP Masuk)</option>
                            <option value="DRAFT" {{ old('status_cd') === 'DRAFT' ? 'selected' : '' }}>📝 DRAFT (Simpan Sementara)</option>
                        </select>
                    </div>
                </div>

                {{-- PEMILIH DOKUMEN PEMAKAIAN BAHAN UNTUK AUTO-FILL --}}
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #cbd5e1; background: #f0f9ff; padding: 0.85rem 1rem; border-radius: 8px; border: 1.5px solid #bae6fd;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.85rem; font-weight: 800; color: #0369a1; display: flex; align-items: center; gap: 0.35rem;">
                            <span>⚡ Tarik Data dari Dokumen Pemakaian Bahan Gudang:</span>
                        </span>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            Memilih dokumen pemakaian akan otomatis mengisi rincian Singkong, Minyak, Bumbu &amp; Kemasan di bawah.
                        </span>
                    </div>

                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <select name="pakai_id" id="pakai_id" class="form-control" style="flex: 1; min-width: 280px; font-size: 0.85rem;" onchange="loadPakaiData(this.value)">
                            <option value="">-- Pilih Dokumen Pengeluaran Bahan (Opsional) --</option>
                            @foreach ($pakaiList as $pk)
                                <option value="{{ $pk->pakai_id }}">
                                    [{{ $pk->pakai_no }}] {{ Carbon\Carbon::parse($pk->pakai_tgl)->format('d/m/Y') }} - {{ $pk->tujuan_pemakaian }} ({{ $pk->details->count() }} item bahan)
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="loadPakaiData(document.getElementById('pakai_id').value)" style="padding: 0.45rem 0.85rem;">
                            🔄 Tarik Ulang
                        </button>
                    </div>
                    <div id="pakaiLoading" style="display: none; font-size: 0.75rem; color: #0284c7; margin-top: 0.35rem; font-weight: 600;">
                        ⏳ Menarik data rincian bahan dari dokumen gudang...
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: BIAYA BAHAN BAKU & KEMASAN (DIRECT MATERIALS) --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>🌾 2. Biaya Bahan Baku &amp; Kemasan (Direct Materials)</span>
                </strong>
                <span id="badgeSubtotalBahan" style="font-size: 0.8rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 0.2rem 0.6rem; border-radius: 6px;">
                    Subtotal Bahan: Rp 0
                </span>
            </div>
            <div style="padding: 1.25rem;">
                {{-- Baris 1: Singkong Mentah & Minyak Goreng --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div style="background: #fefce8; padding: 0.75rem; border-radius: 8px; border: 1px solid #fef08a;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 800; color: #854d0e; margin-bottom: 0.4rem;">
                            🌾 Singkong Mentah Masuk
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.7rem; color: #713f12; font-weight: 600;">Kuantitas (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="singkong_qty" id="singkong_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('singkong_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                            <div style="flex: 1.2;">
                                <label style="font-size: 0.7rem; color: #713f12; font-weight: 600;">Nilai Rupiah (Rp)</label>
                                <input type="number" step="0.01" min="0" name="singkong_nilai" id="singkong_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('singkong_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                    </div>

                    <div style="background: #f0fdf4; padding: 0.75rem; border-radius: 8px; border: 1px solid #bbf7d0;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 800; color: #166534; margin-bottom: 0.4rem;">
                            🛢️ Minyak Sawit &amp; Kelapa
                        </span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; margin-bottom: 0.4rem;">
                            <div>
                                <label style="font-size: 0.7rem; color: #14532d; font-weight: 600;">Sawit (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="minyak_sawit_qty" id="minyak_sawit_qty" class="form-control calc-trigger" style="text-align: right;" value="{{ old('minyak_sawit_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                            <div>
                                <label style="font-size: 0.7rem; color: #14532d; font-weight: 600;">Kelapa (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="minyak_kelapa_qty" id="minyak_kelapa_qty" class="form-control calc-trigger" style="text-align: right;" value="{{ old('minyak_kelapa_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #14532d; font-weight: 600;">Total Rupiah Minyak (Rp)</label>
                            <input type="number" step="0.01" min="0" name="minyak_nilai" id="minyak_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 600;" value="{{ old('minyak_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                        </div>
                        <div id="liveMinyakRasio" style="font-size: 0.725rem; color: #0284c7; font-weight: 700; margin-top: 0.25rem;">
                            Rasio Minyak: 0.00%
                        </div>
                    </div>

                    <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 800; color: #334155; margin-bottom: 0.4rem;">
                            🧂 Bumbu &amp; Perenyah (Rp)
                        </span>
                        <label style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Nilai Bumbu/Garam/Perenyah</label>
                        <input type="number" step="0.01" min="0" name="bumbu_nilai" id="bumbu_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('bumbu_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>
                </div>

                {{-- Baris 2: Kemasan & Packaging --}}
                <div style="background: #faf5ff; padding: 0.85rem; border-radius: 8px; border: 1px solid #e9d5ff;">
                    <span style="display: block; font-size: 0.8rem; font-weight: 800; color: #6b21a8; margin-bottom: 0.5rem;">
                        📦 Rincian Bahan Kemasan &amp; Packaging (Rp)
                    </span>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                        <div>
                            <label style="font-size: 0.7rem; color: #581c87; font-weight: 600;">Karton Baru</label>
                            <input type="number" step="0.01" min="0" name="karton_baru_nilai" id="karton_baru_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('karton_baru_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #581c87; font-weight: 600;">Karton Bekas</label>
                            <input type="number" step="0.01" min="0" name="karton_bekas_nilai" id="karton_bekas_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('karton_bekas_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #581c87; font-weight: 600;">Plastik HD 90x100</label>
                            <input type="number" step="0.01" min="0" name="plastik_hd_nilai" id="plastik_hd_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('plastik_hd_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #581c87; font-weight: 600;">Lakban Besar</label>
                            <input type="number" step="0.01" min="0" name="lakban_besar_nilai" id="lakban_besar_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('lakban_besar_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #581c87; font-weight: 600;">Lakban Kecil</label>
                            <input type="number" step="0.01" min="0" name="lakban_kecil_nilai" id="lakban_kecil_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('lakban_kecil_nilai', 0) }}" oninput="calcAll()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #581c87; font-weight: 600;">Tali Rafia</label>
                            <input type="number" step="0.01" min="0" name="tali_rafia_nilai" id="tali_rafia_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('tali_rafia_nilai', 0) }}" oninput="calcAll()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 3: ENERGI (CNG) & TENAGA KERJA --}}
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            {{-- ENERGI GAS CNG --}}
            <div class="card">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">🔥 3. Gas Alam / CNG (Boiler &amp; Fryer)</strong>
                </div>
                <div style="padding: 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Meteran CNG (MMBTU)
                            </label>
                            <input type="number" step="0.0001" min="0" name="cng_mmbtu" id="cng_mmbtu" class="form-control calc-trigger" style="text-align: right; font-weight: 700;" value="{{ old('cng_mmbtu', 0) }}" placeholder="0" oninput="calcCng()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Tarif per MMBTU (Rp)
                            </label>
                            <input type="number" step="0.01" min="0" name="cng_tarif" id="cng_tarif" class="form-control calc-trigger" style="text-align: right;" value="{{ old('cng_tarif', 226800.00) }}" oninput="calcCng()">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            Total Rupiah CNG (Rp)
                        </label>
                        <input type="number" step="0.01" min="0" name="cng_nilai" id="cng_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 800; color: #c2410c; background: #fff7ed;" value="{{ old('cng_nilai', 0) }}" oninput="calcAll()">
                    </div>
                </div>
            </div>

            {{-- TENAGA KERJA --}}
            <div class="card">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">👥 4. Tenaga Kerja (Tarif Rp 91.300 / orang)</strong>
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
                            <input type="number" step="0.01" min="0" name="tk_total_nilai" id="tk_total_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 800; color: #0284c7; background: #f0f9ff;" value="{{ old('tk_total_nilai', 0) }}" oninput="calcAll()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 4: BIAYA OVERHEAD PABRIK (FACTORY OVERHEAD / FOH) --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <strong style="color: #0f172a; font-size: 0.95rem;">⚙️ 5. Biaya Overhead Pabrik (FOH)</strong>
                <span id="badgeSubtotalOverhead" style="font-size: 0.8rem; font-weight: 700; color: #475569; background: #f1f5f9; padding: 0.2rem 0.6rem; border-radius: 6px;">
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
                        <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Listrik &amp; Air + Telp</label>
                        <input type="number" step="0.01" min="0" name="listrik_air_telp_nilai" id="listrik_air_telp_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('listrik_air_telp_nilai', 0) }}" oninput="calcAll()">
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

        {{-- BAGIAN 5: TIMBANGAN OUTPUT WIP (HASIL JADI PRODUKSI) --}}
        <div class="card" style="margin-bottom: 1.5rem; border: 2px solid #86efac;">
            <div class="card-header" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <strong style="color: #166534; font-size: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>⚖️ 6. Timbangan Hasil Jadi WIP Olahan (Kg)</span>
                </strong>
                <span id="badgeTotalWip" style="font-size: 0.85rem; font-weight: 800; color: #166534; background: #dcfce7; padding: 0.25rem 0.75rem; border-radius: 6px; border: 1px solid #86efac;">
                    Total WIP: 0.00 kg
                </span>
            </div>
            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                            ASIN BARCO (Kg)
                        </label>
                        <input type="number" step="0.0001" min="0" name="asin_barco_qty" id="asin_barco_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right; font-size: 1rem;" value="{{ old('asin_barco_qty', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                            ASIN SAWIT (Kg)
                        </label>
                        <input type="number" step="0.0001" min="0" name="asin_sawit_qty" id="asin_sawit_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right; font-size: 1rem;" value="{{ old('asin_sawit_qty', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                            BERKO (Kg)
                        </label>
                        <input type="number" step="0.0001" min="0" name="berko_qty" id="berko_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('berko_qty', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                            BERKO ME (Kg)
                        </label>
                        <input type="number" step="0.0001" min="0" name="berko_me_qty" id="berko_me_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('berko_me_qty', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                            BALO GELOMBANG (Kg)
                        </label>
                        <input type="number" step="0.0001" min="0" name="balo_gelombang_qty" id="balo_gelombang_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('balo_gelombang_qty', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                            NO SALT (Kg)
                        </label>
                        <input type="number" step="0.0001" min="0" name="no_salt_qty" id="no_salt_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('no_salt_qty', 0) }}" placeholder="0" oninput="calcAll()">
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                        Catatan Produksi / Kualitas
                    </label>
                    <textarea name="catatan_txt" id="catatan_txt" class="form-control" rows="2" placeholder="Catatan shift, deviasi bumbu, atau kendala mesin penggorengan...">{{ old('catatan_txt') }}</textarea>
                </div>
            </div>
        </div>

        {{-- WIDGET LIVE PREVIEW KALKULASI REAL-TIME (STICKY DI BAWAH) --}}
        <div style="background: #ffffff; border: 2px solid #0284c7; border-radius: 12px; padding: 1.25rem; box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.2); margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                {{-- Live Biaya --}}
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #854d0e;">
                        Total Biaya Produksi (Kolom Kuning)
                    </span>
                    <div id="liveTotalBiaya" style="font-size: 1.6rem; font-weight: 800; color: #a16207;">
                        Rp 0
                    </div>
                </div>

                {{-- Live Rendemen --}}
                <div style="text-align: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #065f46;">
                        Rendemen Singkong
                    </span>
                    <div id="liveRendemen" style="font-size: 1.6rem; font-weight: 800; color: #059669;">
                        0.00%
                    </div>
                    <span id="liveRendemenStatus" style="font-size: 0.725rem; font-weight: 700; color: #64748b;">
                        Menunggu timbangan
                    </span>
                </div>

                {{-- Live HPP / Kg --}}
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #0369a1;">
                        HPP Riil per Kg WIP
                    </span>
                    <div id="liveHpp" style="font-size: 1.6rem; font-weight: 800; color: #0284c7;">
                        Rp 0 / kg
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center;">
            <a href="{{ route('produksi.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-size: 0.95rem; font-weight: 800; box-shadow: 0 4px 6px rgba(2, 132, 199, 0.25);">
                💾 Simpan Lembar Produksi Harian
            </button>
        </div>
    </form>
</div>

<script>
    function updateHariLabel() {
        const val = document.getElementById('produksi_tgl').value;
        if (!val) return;
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const d = new Date(val);
        const dayNm = days[d.getDay()];
        document.getElementById('hariLabel').textContent = `Hari: ${dayNm}`;
    }

    function calcCng() {
        const mmbtu = parseFloat(document.getElementById('cng_mmbtu').value) || 0;
        const tarif = parseFloat(document.getElementById('cng_tarif').value) || 0;
        document.getElementById('cng_nilai').value = Math.round(mmbtu * tarif);
        calcAll();
    }

    function calcTk() {
        const lgs = parseInt(document.getElementById('tk_langsung_org').value) || 0;
        const tdk = parseInt(document.getElementById('tk_tidak_langsung_org').value) || 0;
        const trn = parseInt(document.getElementById('tk_training_org').value) || 0;
        const tarif = parseFloat(document.getElementById('tk_tarif_per_org').value) || 91300;
        document.getElementById('tk_total_nilai').value = Math.round((lgs + tdk + trn) * tarif);
        calcAll();
    }

    function calcAll() {
        // 1. Bahan
        const singkongQty = parseFloat(document.getElementById('singkong_qty').value) || 0;
        const singkongNilai = parseFloat(document.getElementById('singkong_nilai').value) || 0;
        const sawitQty = parseFloat(document.getElementById('minyak_sawit_qty').value) || 0;
        const kelapaQty = parseFloat(document.getElementById('minyak_kelapa_qty').value) || 0;
        const minyakNilai = parseFloat(document.getElementById('minyak_nilai').value) || 0;

        const bumbu = parseFloat(document.getElementById('bumbu_nilai').value) || 0;
        const kBaru = parseFloat(document.getElementById('karton_baru_nilai').value) || 0;
        const kBekas = parseFloat(document.getElementById('karton_bekas_nilai').value) || 0;
        const plastik = parseFloat(document.getElementById('plastik_hd_nilai').value) || 0;
        const lakbanB = parseFloat(document.getElementById('lakban_besar_nilai').value) || 0;
        const lakbanK = parseFloat(document.getElementById('lakban_kecil_nilai').value) || 0;
        const tali = parseFloat(document.getElementById('tali_rafia_nilai').value) || 0;

        const subtotalBahan = singkongNilai + minyakNilai + bumbu + kBaru + kBekas + plastik + lakbanB + lakbanK + tali;
        document.getElementById('badgeSubtotalBahan').textContent = 'Subtotal Bahan: Rp ' + subtotalBahan.toLocaleString('id-ID');

        // Rasio Minyak
        const rasioMinyak = singkongQty > 0 ? ((sawitQty + kelapaQty) / singkongQty) * 100 : 0;
        document.getElementById('liveMinyakRasio').textContent = 'Rasio Minyak: ' + rasioMinyak.toFixed(2) + '%';

        // 2. Energi CNG & TK
        const cngNilai = parseFloat(document.getElementById('cng_nilai').value) || 0;
        const tkNilai = parseFloat(document.getElementById('tk_total_nilai').value) || 0;

        // 3. FOH Overhead
        const fc = parseFloat(document.getElementById('fotocopy_nilai').value) || 0;
        const stP = parseFloat(document.getElementById('sarung_tangan_plastik_nilai').value) || 0;
        const stK = parseFloat(document.getElementById('sarung_tangan_kain_nilai').value) || 0;
        const qc = parseFloat(document.getElementById('qc_pengawasan_nilai').value) || 0;
        const listrik = parseFloat(document.getElementById('listrik_air_telp_nilai').value) || 0;
        const pemlhr = parseFloat(document.getElementById('pemeliharaan_mesin_nilai').value) || 0;
        const penys = parseFloat(document.getElementById('penyusutan_mesin_nilai').value) || 0;
        const lmbP = parseFloat(document.getElementById('limbah_padat_nilai').value) || 0;
        const lmbK = parseFloat(document.getElementById('limbah_kimia_nilai').value) || 0;

        const subtotalOverhead = fc + stP + stK + qc + listrik + pemlhr + penys + lmbP + lmbK;
        document.getElementById('badgeSubtotalOverhead').textContent = 'Subtotal FOH: Rp ' + subtotalOverhead.toLocaleString('id-ID');

        // Total Biaya Produksi
        const totalBiaya = subtotalBahan + cngNilai + tkNilai + subtotalOverhead;
        document.getElementById('liveTotalBiaya').textContent = 'Rp ' + totalBiaya.toLocaleString('id-ID');

        // 4. Output WIP
        const barco = parseFloat(document.getElementById('asin_barco_qty').value) || 0;
        const sawit = parseFloat(document.getElementById('asin_sawit_qty').value) || 0;
        const berko = parseFloat(document.getElementById('berko_qty').value) || 0;
        const berkoMe = parseFloat(document.getElementById('berko_me_qty').value) || 0;
        const balo = parseFloat(document.getElementById('balo_gelombang_qty').value) || 0;
        const noSalt = parseFloat(document.getElementById('no_salt_qty').value) || 0;

        const totalWip = barco + sawit + berko + berkoMe + balo + noSalt;
        document.getElementById('badgeTotalWip').textContent = 'Total WIP: ' + totalWip.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + ' kg';

        // 5. Rendemen & HPP
        const rendemen = singkongQty > 0 ? (totalWip / singkongQty) * 100 : 0;
        const hppPerKg = totalWip > 0 ? (totalBiaya / totalWip) : 0;

        const liveRendemen = document.getElementById('liveRendemen');
        const liveRendemenStatus = document.getElementById('liveRendemenStatus');

        liveRendemen.textContent = rendemen.toFixed(2) + '%';
        if (rendemen >= 33.0) {
            liveRendemen.style.color = '#059669';
            liveRendemenStatus.textContent = '✅ Rendemen Bagus (Di atas target 33%)';
            liveRendemenStatus.style.color = '#059669';
        } else if (rendemen >= 30.0) {
            liveRendemen.style.color = '#d97706';
            liveRendemenStatus.textContent = '⚠️ Rendemen Sedang (Target 33%)';
            liveRendemenStatus.style.color = '#d97706';
        } else {
            liveRendemen.style.color = '#dc2626';
            liveRendemenStatus.textContent = rendemen > 0 ? '❌ Rendemen Rendah' : 'Menunggu timbangan';
            liveRendemenStatus.style.color = rendemen > 0 ? '#dc2626' : '#64748b';
        }

        document.getElementById('liveHpp').textContent = 'Rp ' + hppPerKg.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / kg';
    }

    function loadPakaiData(pakaiId) {
        if (!pakaiId) return;

        const loading = document.getElementById('pakaiLoading');
        loading.style.display = 'block';

        fetch(`{{ url('produksi/pakai-data') }}/${pakaiId}`)
            .then(res => res.json())
            .then(res => {
                loading.style.display = 'none';
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('singkong_qty').value = d.singkong_qty || 0;
                    document.getElementById('singkong_nilai').value = d.singkong_nilai || 0;
                    document.getElementById('minyak_sawit_qty').value = d.minyak_sawit_qty || 0;
                    document.getElementById('minyak_kelapa_qty').value = d.minyak_kelapa_qty || 0;
                    document.getElementById('minyak_nilai').value = d.minyak_nilai || 0;
                    document.getElementById('bumbu_nilai').value = d.bumbu_nilai || 0;
                    document.getElementById('karton_baru_nilai').value = d.karton_baru_nilai || 0;
                    document.getElementById('karton_bekas_nilai').value = d.karton_bekas_nilai || 0;
                    document.getElementById('plastik_hd_nilai').value = d.plastik_hd_nilai || 0;
                    document.getElementById('lakban_besar_nilai').value = d.lakban_besar_nilai || 0;
                    document.getElementById('lakban_kecil_nilai').value = d.lakban_kecil_nilai || 0;
                    document.getElementById('tali_rafia_nilai').value = d.tali_rafia_nilai || 0;

                    calcAll();
                } else {
                    alert('Gagal: ' + res.message);
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                console.error(err);
                alert('Terjadi kesalahan saat memuat data pemakaian bahan.');
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateHariLabel();
        calcTk();
        calcCng();
        calcAll();
    });
</script>
@endsection
