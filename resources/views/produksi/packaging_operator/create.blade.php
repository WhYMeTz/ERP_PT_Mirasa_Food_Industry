@extends('layouts.packaging-mobile')

@section('title', 'Input Laporan Pengemasan Lapangan - PT Mirasa')

@section('content')
<form action="{{ route('produksi.packaging.mobile.store') }}" method="POST" id="formLaporanKemas">
    @csrf

    {{-- JUDUL HALAMAN FORM --}}
    <div style="margin-bottom: 1.25rem;">
        <h1 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
            <span>📦</span>
            <span>Laporan Pengemasan Lapangan</span>
        </h1>
        <p style="font-size: 0.8rem; color: #64748b; margin: 0.25rem 0 0 0;">
            Pengganti Google Form &bull; Langsung tersambung ke Admin Produksi Office
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
            <label class="pkg-label" for="tgl_kemas">Tanggal Pelaksanaan <span style="color: #ef4444;">*</span></label>
            <input type="date" id="tgl_kemas" name="tgl_kemas" class="pkg-input" value="{{ old('tgl_kemas', date('Y-m-d')) }}" required>
        </div>

        {{-- Shift Pilihan Tombol --}}
        <div class="pkg-form-group">
            <label class="pkg-label">Pilih Shift <span style="color: #ef4444;">*</span></label>
            <input type="hidden" name="shift" id="inputShift" value="{{ old('shift', '1') }}">
            <div class="pkg-shift-grid">
                <button type="button" class="pkg-shift-btn {{ old('shift', '1') == '1' ? 'active' : '' }}" data-shift="1">
                    Shift 1 (Pagi)
                </button>
                <button type="button" class="pkg-shift-btn {{ old('shift') == '2' ? 'active' : '' }}" data-shift="2">
                    Shift 2 (Siang)
                </button>
                <button type="button" class="pkg-shift-btn {{ old('shift') == '3' ? 'active' : '' }}" data-shift="3">
                    Shift 3 (Malam)
                </button>
            </div>
        </div>

        {{-- Nama Leader / Operator --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="karyawan_id">Nama Leader / Operator Kemas <span style="color: #ef4444;">*</span></label>
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
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
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

        {{-- Sumber Batch WIP Produksi --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="produksi_id">Batch / Lembar Produksi Goreng (WIP)</label>
            <select id="produksi_id" name="produksi_id" class="pkg-select">
                <option value="">-- Pilih Lembar Masak Dapur --</option>
                @foreach ($produksiList as $p)
                    @php
                        $qtyBerko = (float) ($p->total_berko_qty > 0 ? $p->total_berko_qty : $p->berko_qty);
                        $tglStr = $p->produksi_tgl ? $p->produksi_tgl->format('d/m/Y') : '-';
                        $batchStr = $p->batch_wip_no ?: ($p->produksi_tgl ? $p->produksi_tgl->format('d m Y') : '-');
                    @endphp
                    <option value="{{ $p->produksi_id }}" 
                            data-qty="{{ $qtyBerko }}" 
                            data-batch="{{ $batchStr }}"
                            {{ old('produksi_id') == $p->produksi_id ? 'selected' : '' }}>
                        [{{ $p->produksi_no }}] {{ $tglStr }} &bull; Curah: {{ number_format($qtyBerko, 0, ',', '.') }} Kg
                    </option>
                @endforeach
            </select>
            <div id="wipInfoBox" class="pkg-info-box" style="display: none;">
                <span id="wipInfoText"></span>
            </div>
        </div>

        {{-- Jumlah Kg WIP yang Dituang/Dikemas --}}
        <div class="pkg-form-group">
            <label class="pkg-label" for="wip_qty_kg">Jumlah Keripik Curah yang Dikemas (Kg) <span style="color: #ef4444;">*</span></label>
            <div style="position: relative;">
                <input type="number" step="any" min="0.1" id="wip_qty_kg" name="wip_qty_kg" class="pkg-input" value="{{ old('wip_qty_kg', 100) }}" required style="padding-right: 45px;">
                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.85rem;">Kg</span>
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
            <label class="pkg-label" for="barang_jadi_id">Nama Barang Jadi yang Dikemas <span style="color: #ef4444;">*</span></label>
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
            <label class="pkg-label" for="hasil_karton_qty">Jumlah Hasil Jadi Utama <span style="color: #ef4444;">*</span></label>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.5rem;">
                <input type="number" step="any" min="0.0001" id="hasil_karton_qty" name="hasil_karton_qty" class="pkg-input" placeholder="Misal: 50" value="{{ old('hasil_karton_qty') }}" required>
                <input type="hidden" name="satuan_hasil" id="inputSatuanHasil" value="{{ old('satuan_hasil', 'KARTON') }}">
                <div style="display: flex; align-items: center; justify-content: center; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; font-weight: 800; color: #1d4ed8; font-size: 0.85rem;" id="satuanDisplayLabel">
                    KARTON
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
            <div style="position: relative;">
                <input type="number" step="any" min="0" id="hasil_pack_qty" name="hasil_pack_qty" class="pkg-input" placeholder="0 jika pas kardus" value="{{ old('hasil_pack_qty', 0) }}" style="padding-right: 50px;">
                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.85rem;">Pack</span>
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
            <div style="position: relative;">
                <input type="number" step="any" min="0" id="bumbu_qty_kg" name="bumbu_qty_kg" class="pkg-input" placeholder="Misal: 12.5" value="{{ old('bumbu_qty_kg', 0) }}" style="padding-right: 45px;">
                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.85rem;">Kg</span>
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
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.875rem; margin-bottom: 0.875rem;">
            <span style="font-size: 0.8rem; font-weight: 800; color: #1e293b; display: block; margin-bottom: 0.5rem;">
                A. Plastik Kemasan Utama (Roll / Pouch)
            </span>
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
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                <div>
                    <label class="pkg-label" for="plastik_qty">Jml Terpakai (Roll/Kg)</label>
                    <input type="number" step="any" min="0" id="plastik_qty" name="plastik_qty" class="pkg-input" value="{{ old('plastik_qty', 0) }}">
                </div>
                <div>
                    <label class="pkg-label" for="plastik_rusak_pcs" style="color: #dc2626;">Plastik Rusak (Pcs)</label>
                    <input type="number" step="any" min="0" id="plastik_rusak_pcs" name="plastik_rusak_pcs" class="pkg-input" placeholder="Afkir" value="{{ old('plastik_rusak_pcs', 0) }}" style="border-color: #fca5a5;">
                </div>
            </div>
        </div>

        {{-- B. Karton Box Dus --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.875rem; margin-bottom: 0.875rem;">
            <span style="font-size: 0.8rem; font-weight: 800; color: #1e293b; display: block; margin-bottom: 0.5rem;">
                B. Karton Box (Dus Luar)
            </span>
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
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                <div>
                    <label class="pkg-label" for="karton_qty">Jml Dus (Lembar)</label>
                    <input type="number" step="any" min="0" id="karton_qty" name="karton_qty" class="pkg-input" value="{{ old('karton_qty', 0) }}">
                </div>
                <div>
                    <label class="pkg-label" for="karton_rusak_lembar" style="color: #dc2626;">Dus Rusak (Lembar)</label>
                    <input type="number" step="any" min="0" id="karton_rusak_lembar" name="karton_rusak_lembar" class="pkg-input" placeholder="Afkir" value="{{ old('karton_rusak_lembar', 0) }}" style="border-color: #fca5a5;">
                </div>
            </div>
        </div>

        {{-- C. Lakban & Kemasan Tambahan --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div class="pkg-form-group">
                <label class="pkg-label" for="lakban_qty">Lakban (Gram/Roll)</label>
                <input type="number" step="any" min="0" id="lakban_qty" name="lakban_qty" class="pkg-input" placeholder="Misal: 100" value="{{ old('lakban_qty', 0) }}">
            </div>
            <div class="pkg-form-group">
                <label class="pkg-label" for="kemasan_lain_qty">Plastik Bal / Lainnya</label>
                <input type="number" step="any" min="0" id="kemasan_lain_qty" name="kemasan_lain_qty" class="pkg-input" placeholder="Opsional" value="{{ old('kemasan_lain_qty', 0) }}">
            </div>
        </div>

        {{-- Catatan Lapangan --}}
        <div class="pkg-form-group" style="margin-top: 0.75rem;">
            <label class="pkg-label" for="catatan">Catatan / Kendala Lapangan (Opsional)</label>
            <textarea id="catatan" name="catatan" class="pkg-input" rows="2" style="height: auto; font-size: 0.85rem;" placeholder="Misal: Mesin sempat macet 15 menit, plastik sobek batch awal...">{{ old('catatan') }}</textarea>
        </div>
    </div>

    {{-- BOTTOM BAR FLOATING SUBMIT --}}
    <div class="pkg-bottom-bar">
        <div class="pkg-bottom-bar-inner">
            <a href="{{ route('produksi.packaging.mobile.index') }}" class="pkg-btn-history" title="Lihat Riwayat Laporan Hari Ini">
                <span>📋</span>
                <span>Riwayat</span>
            </a>
            <button type="submit" class="pkg-btn-submit" onclick="return confirm('Kirimkan laporan pengemasan ini ke Admin Office?');">
                <span>🚀</span>
                <span>Kirim Laporan ke Office</span>
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging_operator/packaging-operator.js') }}"></script>
@endpush
