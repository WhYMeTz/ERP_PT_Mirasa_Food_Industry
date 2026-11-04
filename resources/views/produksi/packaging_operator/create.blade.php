@extends('layouts.packaging-mobile')

@section('title', 'Input Laporan Pengemasan Lapangan - PT Mirasa')

@section('content')
<form action="{{ route('produksi.packaging.mobile.store') }}" method="POST" id="formLaporanKemas">
    @csrf

    {{-- JUDUL HALAMAN FORM --}}
    <div class="pkg-page-header">
        <div class="pkg-page-title-row">
            <h1 class="pkg-page-title">
                <span class="pkg-page-title-icon">📦</span>
                <span>Laporan Pengemasan</span>
            </h1>
        </div>
        <p class="pkg-page-subtitle">
            Pencatatan real-time operator lapangan &bull; Langsung sinkron ke Admin Office
        </p>
    </div>

    {{-- SEKSI 1: INFORMASI KERJA & SHIFT --}}
    <div class="pkg-card-section">
        <h2 class="pkg-section-title">
            <span class="pkg-section-num">1</span>
            <span>Info Kerja &amp; Shift</span>
        </h2>

        {{-- Tanggal Kemas --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="tgl_kemas">Tanggal Pelaksanaan <span class="pkg-label-req">*</span></label>
            <input type="date" id="tgl_kemas" name="tgl_kemas" class="pkg-input" value="{{ old('tgl_kemas', date('Y-m-d')) }}" required>
        </div>

        {{-- Shift Pilihan Tombol (Shift A & Shift B) --}}
        <div class="pkg-form-group">
            <label class="pkg-label">Pilih Shift Kerja <span class="pkg-label-req">*</span></label>
            <input type="hidden" name="shift" id="inputShift" value="{{ old('shift', 'A') }}">
            <div class="pkg-shift-grid">
                <button type="button" class="pkg-shift-btn {{ old('shift', 'A') == 'A' ? 'active' : '' }}" data-shift="A">
                    Shift A
                </button>
                <button type="button" class="pkg-shift-btn {{ old('shift') == 'B' ? 'active' : '' }}" data-shift="B">
                    Shift B
                </button>
            </div>
        </div>

        {{-- Nama Leader / Operator --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="karyawan_id">Nama Leader / Operator Kemas <span class="pkg-label-req">*</span></label>
            <select id="karyawan_id" name="karyawan_id" class="pkg-select">
                <option value="">-- Pilih dari Daftar Karyawan --</option>
                @foreach ($karyawanList as $k)
                    <option value="{{ $k->karyawan_id }}" {{ old('karyawan_id') == $k->karyawan_id ? 'selected' : '' }}>
                        {{ $k->karyawan_nm }} ({{ $k->nik ?: 'Operator' }})
                    </option>
                @endforeach
            </select>
            <input type="text" id="operator_nama" name="operator_nama" class="pkg-input" placeholder="Atau ketik nama jika tidak ada di daftar..." value="{{ old('operator_nama', Auth::user()->name ?? '') }}" style="margin-top: 0.5rem;" required>
        </div>

        {{-- Personil & Mesin --}}
        <div class="pkg-grid-2">
            <div class="pkg-form-group">
                <label class="pkg-label" for="jumlah_personil">Jumlah Personil (Org)</label>
                <input type="number" id="jumlah_personil" name="jumlah_personil" class="pkg-input" min="1" value="{{ old('jumlah_personil', 5) }}" required>
            </div>
            <div class="pkg-form-group">
                <label class="pkg-label" for="jumlah_mesin">Mesin Aktif (Unit)</label>
                <input type="number" id="jumlah_mesin" name="jumlah_mesin" class="pkg-input" min="1" value="{{ old('jumlah_mesin', 2) }}" required>
            </div>
        </div>
    </div>

    {{-- SEKSI 2: BAHAN WIP CURAH DAPUR --}}
    <div class="pkg-card-section">
        <h2 class="pkg-section-title">
            <span class="pkg-section-num">2</span>
            <span>Bahan Keripik Curah (WIP Dapur)</span>
        </h2>

        {{-- Sumber Varian WIP Hasil Produksi Dapur --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="output_id">Varian Keripik WIP / Hasil Masak Dapur</label>
            <input type="hidden" name="produksi_id" id="hidden_produksi_id" value="{{ old('produksi_id') }}">
            <input type="hidden" name="wip_barang_id" id="hidden_wip_barang_id" value="{{ old('wip_barang_id') }}">
            <input type="hidden" name="wip_batch_no" id="hidden_wip_batch_no" value="{{ old('wip_batch_no') }}">

            <select id="output_id" name="output_id" class="pkg-select">
                <option value="">-- Pilih Varian Keripik Curah Hasil Masak --</option>
                @foreach ($wipList as $wip)
                    @php
                        $prodNo = $wip->produksi?->produksi_no ?? 'PROD';
                        $tglStr = $wip->produksi?->produksi_tgl ? $wip->produksi->produksi_tgl->format('d/m/Y') : '-';
                        $batchStr = $wip->batch_no ?: ($wip->produksi?->batch_wip_no ?: ($wip->produksi?->produksi_tgl ? $wip->produksi->produksi_tgl->format('d m Y') : '-'));
                        $barangNm = $wip->barang?->barang_nm ?? 'Keripik Curah';
                        $qtyKg = (float) $wip->qty_kg;
                        $nmUpper = strtoupper($barangNm);
                        $hasBumbuProd = (float) ($wip->produksi?->bumbu_qty ?? 0) > 0;
                        $isBerbumbu = $hasBumbuProd
                            || str_contains($nmUpper, 'BERBUMBU')
                            || str_contains($nmUpper, 'BUMBU')
                            || str_contains($nmUpper, 'BALADO')
                            || str_contains($nmUpper, 'SEASALT')
                            || str_contains($nmUpper, 'JAGUNG');
                    @endphp
                    <option value="{{ $wip->output_id }}"
                            data-produksi-id="{{ $wip->produksi_id }}"
                            data-barang-id="{{ $wip->barang_id }}"
                            data-batch="{{ $batchStr }}"
                            data-barang-nama="{{ $barangNm }}"
                            data-qty="{{ $qtyKg }}"
                            data-is-berbumbu="{{ $isBerbumbu ? '1' : '0' }}"
                            {{ old('output_id') == $wip->output_id ? 'selected' : '' }}>
                        [{{ $prodNo }}] {{ $barangNm }} &bull; Batch: {{ $batchStr }} (Tersedia: {{ number_format($qtyKg, 0, ',', '.') }} Kg) {{ $isBerbumbu ? '✨ [Sudah Berbumbu]' : '' }}
                    </option>
                @endforeach
            </select>
            <div id="wipInfoBox" class="pkg-info-box" style="display: none;">
                <span id="wipInfoText"></span>
            </div>
        </div>

        {{-- Jumlah WIP & Pilihan Satuan (Kg / Karton) --}}
        <div class="pkg-form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                <label class="pkg-label" for="wip_qty_kg" style="margin-bottom: 0;">
                    Jumlah Keripik Curah (WIP) <span class="pkg-label-req">*</span>
                </label>
                <span style="font-size: 0.725rem; color: var(--pkg-text-muted); font-weight: 600;">Satuan Ambil Curah</span>
            </div>

            {{-- Segmented Toggle Satuan WIP (Kg / Karton) --}}
            <input type="hidden" name="wip_satuan" id="inputWipSatuan" value="{{ old('wip_satuan', 'KG') }}">
            <div class="pkg-wip-satuan-grid">
                <button type="button" class="pkg-wip-satuan-btn {{ old('wip_satuan', 'KG') == 'KG' ? 'active' : '' }}" data-satuan="KG">
                    ⚖️ Kilogram (Kg)
                </button>
                <button type="button" class="pkg-wip-satuan-btn {{ old('wip_satuan') == 'KARTON' ? 'active' : '' }}" data-satuan="KARTON">
                    📦 Dus / Karton
                </button>
            </div>

            <div class="pkg-input-suffix-wrapper">
                <input type="number" step="any" min="0.01" id="wip_qty_kg" name="wip_qty_kg" class="pkg-input" value="{{ old('wip_qty_kg', 100) }}" required style="padding-right: 75px; font-weight: 700;">
                <span id="wipSatuanSuffix" class="pkg-input-suffix">
                    {{ old('wip_satuan', 'KG') == 'KARTON' ? 'KARTON' : 'KG' }}
                </span>
            </div>
            <div class="pkg-hint-text">
                Pilih satuan: bisa timbangan Kg atau hitungan Dus / Karton penampungan.
            </div>
        </div>
    </div>

    {{-- SEKSI 3: HASIL JADI PENGEMASAN (OUTPUT FG) --}}
    <div class="pkg-card-section">
        <h2 class="pkg-section-title">
            <span class="pkg-section-num">3</span>
            <span>Hasil Jadi Barang (FG)</span>
        </h2>

        {{-- Pilihan Barang Jadi --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="barang_jadi_id">Nama Barang Jadi yang Dikemas <span class="pkg-label-req">*</span></label>
            <select id="barang_jadi_id" name="barang_jadi_id" class="pkg-select" required>
                <option value="">-- Pilih Produk Jadi --</option>
                @foreach ($barangJadiList as $b)
                    <option value="{{ $b->barang_id }}" {{ old('barang_jadi_id') == $b->barang_id ? 'selected' : '' }}>
                        {{ $b->barang_nm }} ({{ $b->barang_cd }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Jumlah Hasil Jadi & Pilihan Satuan --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="hasil_karton_qty">Jumlah Hasil Jadi Utama <span class="pkg-label-req">*</span></label>
            <div class="pkg-grid-main-satuan">
                <input type="number" step="any" min="0.0001" id="hasil_karton_qty" name="hasil_karton_qty" class="pkg-input" placeholder="Misal: 50" value="{{ old('hasil_karton_qty') }}" required>
                <input type="hidden" name="satuan_hasil" id="inputSatuanHasil" value="{{ old('satuan_hasil', 'KARTON') }}">
                <div class="pkg-satuan-display-badge" id="satuanDisplayLabel">
                    {{ old('satuan_hasil', 'KARTON') }}
                </div>
            </div>

            {{-- Segmented buttons pilih satuan --}}
            <div class="pkg-satuan-grid">
                <button type="button" class="pkg-satuan-btn {{ old('satuan_hasil', 'KARTON') == 'KARTON' ? 'active' : '' }}" data-satuan="KARTON" onclick="document.getElementById('satuanDisplayLabel').textContent='KARTON'">KARTON</button>
                <button type="button" class="pkg-satuan-btn {{ old('satuan_hasil') == 'BAL' ? 'active' : '' }}" data-satuan="BAL" onclick="document.getElementById('satuanDisplayLabel').textContent='BAL'">BAL</button>
                <button type="button" class="pkg-satuan-btn {{ old('satuan_hasil') == 'PACK' ? 'active' : '' }}" data-satuan="PACK" onclick="document.getElementById('satuanDisplayLabel').textContent='PACK'">PACK</button>
            </div>
        </div>

        {{-- Sisa Pack / Eceran (Opsional) --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="hasil_pack_qty">Sisa Eceran / Tambahan Pack (Jika Ada)</label>
            <div class="pkg-input-suffix-wrapper">
                <input type="number" step="any" min="0" id="hasil_pack_qty" name="hasil_pack_qty" class="pkg-input" placeholder="0 jika pas kardus" value="{{ old('hasil_pack_qty', 0) }}" style="padding-right: 55px;">
                <span class="pkg-input-suffix-muted">Pack</span>
            </div>
        </div>
    </div>

    {{-- SEKSI 4: PEMAKAIAN BUMBU TABUR (SMART SINKRONISASI POIN 43) --}}
    <div class="pkg-card-section" id="sectionBumbuTabur">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <h2 class="pkg-section-title" style="margin-bottom: 0;">
                <span class="pkg-section-num">4</span>
                <span>Pemakaian Bumbu Tabur</span>
            </h2>
            <span id="badgeBumbuStatus" class="pkg-badge-ruby" style="display: none; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                ✨ Sudah Berbumbu di Dapur
            </span>
        </div>

        {{-- Card Alert Info Otomatis Jika Curah Sudah Berbumbu dari Dapur (Poin 43) --}}
        <div id="alertBumbuDapur" style="display: none; padding: 0.85rem 1rem; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; margin-bottom: 0.85rem;">
            <div style="display: flex; align-items: flex-start; gap: 0.6rem;">
                <span style="font-size: 1.25rem; line-height: 1;">✨</span>
                <div>
                    <div style="font-weight: 700; color: #166534; font-size: 0.85rem;">
                        Curah Sudah Berbumbu dari Dapur (Poin 43)
                    </div>
                    <div style="font-size: 0.75rem; color: #15803d; margin-top: 3px; line-height: 1.4;">
                        Varian keripik ini sudah dibumbui saat proses dapur goreng. Input bumbu otomatis dilewati (0 Kg) agar biaya tidak terhitung ganda (*zero double cost*).
                    </div>
                    <div style="margin-top: 0.5rem;">
                        <button type="button" id="btnOverrideBumbu" onclick="toggleManualBumbuOverride()" style="background: none; border: none; padding: 0; color: #0284c7; font-size: 0.725rem; font-weight: 600; text-decoration: underline; cursor: pointer;">
                            ➕ Perlu tambah bumbu manual di meja kemas? Klik di sini
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="containerBumbuInputs">
            {{-- Pilihan Bumbu --}}
            <div class="pkg-form-group">
                <label class="pkg-label" for="bumbu_barang_id">Jenis Bumbu Tabur</label>
                <select id="bumbu_barang_id" name="bumbu_barang_id" class="pkg-select">
                    <option value="">-- Tanpa Bumbu (Original / Tidak Pakai) --</option>
                    @foreach ($bumbuList as $bm)
                        <option value="{{ $bm->barang_id }}" {{ old('bumbu_barang_id') == $bm->barang_id ? 'selected' : '' }}>
                            {{ $bm->barang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Qty Bumbu Kg --}}
            <div class="pkg-form-group">
                <label class="pkg-label" for="bumbu_qty_kg">Berat Bumbu yang Terpakai (Kg)</label>
                <div class="pkg-input-suffix-wrapper">
                    <input type="number" step="any" min="0" id="bumbu_qty_kg" name="bumbu_qty_kg" class="pkg-input" placeholder="Misal: 12.5" value="{{ old('bumbu_qty_kg', 0) }}" style="padding-right: 48px;">
                    <span class="pkg-input-suffix-muted">Kg</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SEKSI 5: BAHAN KEMASAN, KARTON, LAKBAN & AFKIR --}}
    <div class="pkg-card-section">
        <h2 class="pkg-section-title">
            <span class="pkg-section-num">5</span>
            <span>Kemasan, Karton, Lakban &amp; Afkir</span>
        </h2>

        {{-- A. Plastik Roll / Pouch --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    A. Plastik Kemasan Utama (Roll / Pouch)
                </span>
            </div>
            <div class="pkg-form-group">
                <select id="plastik_barang_id" name="plastik_barang_id" class="pkg-select">
                    <option value="">-- Pilih Plastik Kemasan --</option>
                    @foreach ($plastikList as $pls)
                        <option value="{{ $pls->barang_id }}" {{ old('plastik_barang_id') == $pls->barang_id ? 'selected' : '' }}>
                            {{ $pls->barang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="pkg-grid-2">
                <div>
                    <label class="pkg-label" for="plastik_qty">Jml Terpakai (Roll/Kg)</label>
                    <input type="number" step="any" min="0" id="plastik_qty" name="plastik_qty" class="pkg-input" value="{{ old('plastik_qty', 0) }}">
                </div>
                <div>
                    <label class="pkg-label pkg-label-req" for="plastik_rusak_pcs">Plastik Rusak (Pcs)</label>
                    <input type="number" step="any" min="0" id="plastik_rusak_pcs" name="plastik_rusak_pcs" class="pkg-input pkg-input-danger" placeholder="Afkir" value="{{ old('plastik_rusak_pcs', 0) }}">
                </div>
            </div>
        </div>

        {{-- B. Karton Box Dus (Satu Format Standar Seragam - Poin 42) --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    B. Karton Box (Dus Kemasan Luar)
                </span>
                <span id="badgeKartonTotal" class="pkg-badge-ruby" style="background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;">
                    Total: <span id="lbl_karton_total_dipakai">{{ old('karton_qty', 0) }}</span> Lembar
                </span>
            </div>

            {{-- Pilihan Master Dus Karton --}}
            <div class="pkg-form-group">
                <label class="pkg-label" for="karton_barang_id">Jenis Dus Karton <span class="pkg-label-req">*</span></label>
                <select id="karton_barang_id" name="karton_barang_id" class="pkg-select">
                    <option value="">-- Pilih Master Dus Karton --</option>
                    @foreach ($kartonList as $ktn)
                        <option value="{{ $ktn->barang_id }}" {{ old('karton_barang_id', $kartonBaru?->barang_id) == $ktn->barang_id ? 'selected' : '' }}>
                            {{ $ktn->barang_nm }} ({{ $ktn->barang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Toolbar Cepat: Tombol FIFO --}}
            <div class="pkg-karton-toolbar" style="margin-bottom: 0.5rem;">
                <button type="button" class="pkg-btn-autofill-fifo" onclick="autoAllocateKartonFifo()" title="Otomatis gunakan stok Puteran terlebih dahulu (FIFO)">
                    <span>⚡ Pakai Puteran Dulu (FIFO)</span>
                </button>
            </div>

            {{-- Alokasi 3 Jenis Karton: Puteran, Baru & Reject --}}
            <div id="kartonSirkularGrid" class="pkg-karton-card-grid">
                {{-- Input 1: Karton Puteran --}}
                <div class="pkg-karton-box-item" style="border-left: 3px solid #10b981;">
                    <div class="pkg-karton-box-header">
                        <span class="pkg-karton-box-title" style="color: #065f46;">Karton Puteran</span>
                        <span class="pkg-stock-badge green" title="Sisa Stok Gudang">
                            Stok: {{ number_format($stokKartonPuteran, 0, ',', '.') }}
                        </span>
                    </div>
                    <input type="hidden" name="karton_puteran_barang_id" value="{{ $kartonPuteran?->barang_id ?? 82 }}">
                    <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.35rem;">Karton balikan layak (Maks. 5x)</div>
                    <input type="number" step="any" min="0" id="karton_puteran_qty" name="karton_puteran_qty" class="pkg-input" placeholder="0" value="{{ old('karton_puteran_qty', 0) }}" style="font-weight: 700; color: #065f46;" oninput="updateKartonTotal()">
                </div>

                {{-- Input 2: Karton Baru --}}
                <div class="pkg-karton-box-item" style="border-left: 3px solid #0284c7;">
                    <div class="pkg-karton-box-header">
                        <span class="pkg-karton-box-title" style="color: #0369a1;">Karton Baru</span>
                        <span class="pkg-stock-badge blue" title="Sisa Stok Gudang">
                            Stok: {{ number_format($stokKartonBaru, 0, ',', '.') }}
                        </span>
                    </div>
                    <input type="hidden" name="karton_baru_barang_id" value="{{ $kartonBaru?->barang_id ?? 29 }}">
                    <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.35rem;">Karton baru belum terpakai</div>
                    <input type="number" step="any" min="0" id="karton_baru_qty" name="karton_baru_qty" class="pkg-input" placeholder="0" value="{{ old('karton_baru_qty', 0) }}" style="font-weight: 700; color: #0369a1;" oninput="updateKartonTotal()">
                </div>

                {{-- Input 3: Karton Reject / Manual --}}
                <div class="pkg-karton-box-item" style="border-left: 3px solid #f59e0b;">
                    <div class="pkg-karton-box-header">
                        <span class="pkg-karton-box-title" style="color: #b45309;">Karton Reject</span>
                        <span class="pkg-stock-badge amber" title="Sisa Stok Gudang">
                            Stok: {{ number_format($stokKartonReject, 0, ',', '.') }}
                        </span>
                    </div>
                    <input type="hidden" name="karton_reject_barang_id" value="{{ $kartonReject?->barang_id ?? 84 }}">
                    <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.35rem;">Karton sortir/afkir (HPP Rp 0)</div>
                    <input type="number" step="any" min="0" id="karton_reject_qty" name="karton_reject_qty" class="pkg-input" placeholder="0" value="{{ old('karton_reject_qty', 0) }}" style="font-weight: 700; color: #b45309;" oninput="updateKartonTotal()">
                </div>
            </div>

            {{-- Total Karton Terhitung & Dus Rusak / Afkir --}}
            <input type="hidden" id="karton_qty" name="karton_qty" value="{{ old('karton_qty', 0) }}">
            <div style="margin-top: 0.65rem;">
                <label class="pkg-label pkg-label-req" for="karton_rusak_lembar">Dus Rusak / Sobek Saat Packing (Afkir Lembar):</label>
                <input type="number" step="any" min="0" id="karton_rusak_lembar" name="karton_rusak_lembar" class="pkg-input pkg-input-danger" placeholder="0" value="{{ old('karton_rusak_lembar', 0) }}">
            </div>
        </div>

        {{-- C. Lakban Dus (Sesuai Poin 41: Otomatis cm/Meter & Roll) --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    C. Lakban Dus (Otomatis cm/Meter &amp; Roll)
                </span>
                <span id="badgeLakbanTotal" class="pkg-badge-ruby" style="background: #fdf2f8; color: #be185d; border-color: #fbcfe8;">
                    Total: <span id="lbl_lakban_meter">0</span> Meter (<span id="lbl_lakban_roll">0</span> Roll)
                </span>
            </div>
            <div class="pkg-form-group">
                <label class="pkg-label" for="lakban_barang_id">Pilih Jenis Lakban</label>
                <select id="lakban_barang_id" name="lakban_barang_id" class="pkg-select">
                    <option value="">-- Pilih Jenis Lakban --</option>
                    @foreach ($lakbanList as $lkb)
                        <option value="{{ $lkb->barang_id }}" 
                            data-satuan="{{ $lkb->satuanDasar?->satuan_nm ?? 'ROLL' }}"
                            {{ old('lakban_barang_id') == $lkb->barang_id ? 'selected' : '' }}>
                            {{ $lkb->barang_nm }} ({{ $lkb->satuanDasar?->satuan_nm ?? 'ROLL' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Acuan Standar Lakban per Karton (Default 120 cm) --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #334155;">📐 Acuan Standar Lakban:</span>
                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">1 Roll = 100 Meter</span>
                </div>
                <div class="pkg-grid-2">
                    <div>
                        <label class="pkg-label" for="lakban_standar_cm" style="font-size: 0.725rem;">Standar per Karton (cm):</label>
                        <div class="pkg-input-suffix-wrapper">
                            <input type="number" step="any" min="1" id="lakban_standar_cm" name="lakban_standar_cm" class="pkg-input" value="{{ old('lakban_standar_cm', 120) }}" style="padding-right: 45px; font-weight: 700;" oninput="recalculateLakbanFromKarton()">
                            <span class="pkg-input-suffix-muted">cm</span>
                        </div>
                        <div style="font-size: 0.65rem; color: #64748b; margin-top: 2px;">Atas ~60cm + Bawah ~60cm</div>
                    </div>
                    <div>
                        <label class="pkg-label" style="font-size: 0.725rem;">Kebutuhan Normal:</label>
                        <div style="height: 38px; display: flex; align-items: center; padding: 0 0.75rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 700; font-size: 0.85rem; color: #0f172a;">
                            <span id="lbl_lakban_kebutuhan_m">0</span>&nbsp;<span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">Meter</span>
                        </div>
                        <div style="font-size: 0.65rem; color: #64748b; margin-top: 2px;">Dari <span id="lbl_ref_karton_qty">0</span> karton jadi</div>
                    </div>
                </div>
            </div>

            {{-- Input Kerusakan / Afkir Lakban & Input Manual Roll --}}
            <div class="pkg-grid-2">
                <div class="pkg-form-group">
                    <label class="pkg-label" for="lakban_rusak_m" style="color: #b91c1c;">Lakban Rusak / Kusut (Meter)</label>
                    <div class="pkg-input-suffix-wrapper">
                        <input type="number" step="any" min="0" id="lakban_rusak_m" name="lakban_rusak_m" class="pkg-input pkg-input-danger" placeholder="0" value="{{ old('lakban_rusak_m', 0) }}" style="padding-right: 48px;" oninput="recalculateLakbanFromKarton()">
                        <span class="pkg-input-suffix-muted">Meter</span>
                    </div>
                    <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 2px;">Lakban putus/terbuang saat packing</div>
                </div>
                <div class="pkg-form-group">
                    <label class="pkg-label" for="lakban_roll_qty">Total Pemakaian (Roll)</label>
                    <div class="pkg-input-suffix-wrapper">
                        <input type="number" step="0.01" min="0" id="lakban_roll_qty" name="lakban_roll_qty" class="pkg-input" placeholder="0.00" value="{{ old('lakban_roll_qty', 0) }}" style="padding-right: 48px; font-weight: 700; color: #be185d;" oninput="onManualRollInput()">
                        <span class="pkg-input-suffix-muted">Roll</span>
                    </div>
                    <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 2px;">Bisa edit jika hitung roll fisik</div>
                </div>
            </div>

            {{-- Hidden inputs untuk kompatibilitas database & HPP --}}
            <input type="hidden" id="lakban_qty" name="lakban_qty" value="{{ old('lakban_qty', 0) }}">
            <input type="hidden" id="lakban_atas_gr" name="lakban_atas_gr" value="{{ old('lakban_atas_gr', 0) }}">
            <input type="hidden" id="lakban_bawah_gr" name="lakban_bawah_gr" value="{{ old('lakban_bawah_gr', 0) }}">

        {{-- D. Plastik / Kertas Expired (Label Batch Kadaluarsa) --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    D. Plastik / Kertas Expired (Label Batch Kadaluarsa)
                </span>
            </div>
            <div class="pkg-form-group">
                <select id="expired_barang_id" name="expired_barang_id" class="pkg-select">
                    <option value="">-- Pilih Kertas / Plastik Expired --</option>
                    @foreach ($expiredList as $exp)
                        <option value="{{ $exp->barang_id }}" {{ (old('expired_barang_id') == $exp->barang_id || (empty(old('expired_barang_id')) && $loop->first)) ? 'selected' : '' }}>
                            {{ $exp->barang_nm }} ({{ $exp->satuanDasar?->satuan_nm ?? 'LEMBAR' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="pkg-label" for="expired_qty">Jumlah Pemakaian (Lembar / Pcs)</label>
                <div class="pkg-input-suffix-wrapper">
                    <input type="number" step="any" min="0" id="expired_qty" name="expired_qty" class="pkg-input" placeholder="0" value="{{ old('expired_qty', 0) }}">
                    <span class="pkg-input-suffix-muted">Lbr/Pcs</span>
                </div>
            </div>
        </div>

        {{-- E. Kemasan Tambahan (Dinamis Multi-Input: Plastik Bal / Rafia / Klip / Stiker) --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    E. Kemasan Tambahan (Plastik Bal / Rafia / Stiker)
                </span>
            </div>
            
            {{-- Wadah Baris Dinamis --}}
            <div id="kemasanTambahanList" class="pkg-dynamic-list">
                {{-- Injected dynamically by JS --}}
            </div>

            <button type="button" class="pkg-btn-add-item" onclick="addKemasanTambahanRow()">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Tambah Kemasan Tambahan</span>
            </button>
        </div>

        {{-- Catatan Lapangan --}}
        <div class="pkg-form-group" style="margin-top: 0.85rem;">
            <label class="pkg-label" for="catatan">Catatan / Kendala Lapangan (Opsional)</label>
            <textarea id="catatan" name="catatan" class="pkg-input" rows="2" style="height: auto; font-size: 0.85rem;" placeholder="Misal: Mesin sempat macet 15 menit, plastik sobek batch awal...">{{ old('catatan') }}</textarea>
        </div>
    </div>

    {{-- TOMBOL SIMPAN DI PALING BAWAH FORM --}}
    <div class="pkg-submit-wrapper">
        <button type="submit" class="pkg-btn-submit">
            <span>🚀</span>
            <span>Kirim Laporan ke Office</span>
        </button>
    </div>
</form>
@endsection

@push('scripts')
    <script>
        window.stokKartonPuteranAvailable = {{ (float) $stokKartonPuteran }};
        window.stokKartonBaruAvailable = {{ (float) $stokKartonBaru }};
        window.stokKartonRejectAvailable = {{ (float) $stokKartonReject }};

        window.kemasanLainOptions = [
            @foreach ($kemasanLainList as $kml)
                {
                    barang_id: {{ $kml->barang_id }},
                    barang_nm: @json($kml->barang_nm),
                    satuan_nm: @json($kml->satuanDasar?->satuan_nm ?? 'PCS')
                },
            @endforeach
        ];

        document.addEventListener('DOMContentLoaded', function() {
            @if(old('kemasan_tambahan') && is_array(old('kemasan_tambahan')))
                @foreach(old('kemasan_tambahan') as $item)
                    addKemasanTambahanRow('{{ $item['barang_id'] ?? '' }}', '{{ $item['qty'] ?? '' }}');
                @endforeach
            @endif
        });
    </script>
    <script src="{{ asset('js/produksi/packaging_operator/packaging-operator.js') }}"></script>
@endpush
