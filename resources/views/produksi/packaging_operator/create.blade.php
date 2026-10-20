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
                    @endphp
                    <option value="{{ $wip->output_id }}"
                            data-produksi-id="{{ $wip->produksi_id }}"
                            data-barang-id="{{ $wip->barang_id }}"
                            data-batch="{{ $batchStr }}"
                            data-barang-nama="{{ $barangNm }}"
                            data-qty="{{ $qtyKg }}"
                            {{ old('output_id') == $wip->output_id ? 'selected' : '' }}>
                        [{{ $prodNo }}] {{ $barangNm }} &bull; Batch: {{ $batchStr }} (Tersedia: {{ number_format($qtyKg, 0, ',', '.') }} Kg)
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

    {{-- SEKSI 4: PEMAKAIAN BUMBU TABUR --}}
    <div class="pkg-card-section">
        <h2 class="pkg-section-title">
            <span class="pkg-section-num">4</span>
            <span>Pemakaian Bumbu Tabur</span>
        </h2>

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

        {{-- B. Karton Box Dus --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    B. Karton Box (Dus Luar)
                </span>
            </div>
            <div class="pkg-form-group">
                <select id="karton_barang_id" name="karton_barang_id" class="pkg-select">
                    <option value="">-- Pilih Dus Karton --</option>
                    @foreach ($kartonList as $ktn)
                        <option value="{{ $ktn->barang_id }}" {{ old('karton_barang_id') == $ktn->barang_id ? 'selected' : '' }}>
                            {{ $ktn->barang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="pkg-grid-2">
                <div>
                    <label class="pkg-label" for="karton_qty">Jml Dus (Lembar)</label>
                    <input type="number" step="any" min="0" id="karton_qty" name="karton_qty" class="pkg-input" value="{{ old('karton_qty', 0) }}">
                </div>
                <div>
                    <label class="pkg-label pkg-label-req" for="karton_rusak_lembar">Dus Rusak (Lembar)</label>
                    <input type="number" step="any" min="0" id="karton_rusak_lembar" name="karton_rusak_lembar" class="pkg-input pkg-input-danger" placeholder="Afkir" value="{{ old('karton_rusak_lembar', 0) }}">
                </div>
            </div>
        </div>

        {{-- C. Lakban (Selotip / Tape Dus) --}}
        <div class="pkg-sub-card">
            <div class="pkg-sub-card-header">
                <span class="pkg-sub-card-title">
                    C. Lakban (Selotip / Tape Dus)
                </span>
                <span id="badgeLakbanTotal" class="pkg-badge-ruby">
                    Total: <span id="lbl_lakban_total">0</span> Gram
                </span>
            </div>
            <div class="pkg-form-group">
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
            <div class="pkg-grid-2">
                <div class="pkg-form-group">
                    <label class="pkg-label" for="lakban_atas_gr">Lakban Atas (Gram)</label>
                    <input type="number" step="any" min="0" id="lakban_atas_gr" name="lakban_atas_gr" class="pkg-input" placeholder="Misal: 1250" value="{{ old('lakban_atas_gr', 0) }}">
                </div>
                <div class="pkg-form-group">
                    <label class="pkg-label" for="lakban_bawah_gr">Lakban Bawah (Gram)</label>
                    <input type="number" step="any" min="0" id="lakban_bawah_gr" name="lakban_bawah_gr" class="pkg-input" placeholder="Misal: 800" value="{{ old('lakban_bawah_gr', 0) }}">
                </div>
            </div>
            <input type="hidden" id="lakban_qty" name="lakban_qty" value="{{ old('lakban_qty', 0) }}">
        </div>

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
