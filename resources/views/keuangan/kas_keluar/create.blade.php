@extends('layouts.app')

@section('title', 'Input Bukti Kas Keluar (BKK Operasional) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_keluar/kas-keluar-form.css') }}">
@endpush

@section('content')
<div class="bkk-full-container">
    {{-- TOP ACTION BAR --}}
    <div class="bkk-header-bar">
        <div>
            <div class="bkk-header-title">
                <a href="{{ route('keuangan.bayar.index', ['tab' => 'operasional']) }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1>Input Kas Keluar Operasional (BKK)</h1>
                <span style="background: #fffbeb; color: #b45309; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                    BKK Non-Tagihan
                </span>
                <span style="background: #f1f5f9; color: #475569; font-size: 0.725rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; font-family: monospace;">
                    {{ $suggestedBkkNo }}
                </span>
            </div>
            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 1.75rem;">
                Pencatatan pengeluaran kas langsung pabrik. Saldo kas terpotong &amp; jurnal beban operasional tercatat otomatis.
            </p>
        </div>

        <div>
            <a href="{{ route('keuangan.bayar.index', ['tab' => 'operasional']) }}" class="btn btn-secondary" style="font-weight: 600; font-size: 0.8125rem;">
                &larr; Kembali ke Daftar BKK
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                <svg width="20" height="20" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: #991b1b; margin: 0 0 0.35rem 0;">Perhatian: Terdapat data yang belum lengkap</h4>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.8125rem; color: #b91c1c;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('keuangan.kas_keluar.store') }}" id="formKasKeluar">
        @csrf

        {{-- MAIN 2-COLUMN WORKSPACE --}}
        <div class="bkk-workspace-grid">
            
            {{-- KOLOM KIRI: PARAMETER VOUCHER BKK (STICKY / COMPACT) --}}
            <div class="bkk-card">
                <div class="bkk-card-header">
                    <span class="bkk-card-title">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Parameter Voucher BKK</span>
                    </span>
                    <span style="font-size: 0.725rem; font-weight: 700; color: #0284c7;">Langkah 1</span>
                </div>
                <div class="bkk-card-body">
                    {{-- 1. SUMBER DANA KAS/BANK --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="kas_bank_id" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Dibayar Dari (Kas / Bank) <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="kas_bank_id" id="kas_bank_id" class="form-control" required style="font-weight: 600; width: 100%;">
                            <option value="">-- Pilih Rekening Kas / Bank --</option>
                            @foreach ($kasBanks as $kb)
                                <option value="{{ $kb->kas_bank_id }}" 
                                        data-saldo="{{ $kb->saldo_berjalan }}"
                                        {{ old('kas_bank_id') == $kb->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }} ({{ $kb->tipe_akun }})
                                </option>
                            @endforeach
                        </select>
                        <div class="kas-saldo-indicator">
                            <span class="saldo-label">Saldo Tersedia:</span>
                            <span class="saldo-val" id="liveSaldoDisplay">Rp 0</span>
                        </div>
                    </div>

                    {{-- 2. DIBAYARKAN KEPADA --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="penerima_nama" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Dibayarkan Kepada (Penerima) <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="penerima_nama" id="penerima_nama" class="form-control" 
                               value="{{ old('penerima_nama') }}" 
                               placeholder="Contoh: PLN Persero, SPBU, Teknisi, dll." required style="width: 100%; font-size: 0.8125rem; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    </div>

                    {{-- 3. KATEGORI PENGELUARAN (CHIP PILLS) --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">
                            Kategori Pengeluaran
                        </label>
                        @php $kategoriVal = old('kategori_pengeluaran', $defaultKategori); @endphp
                        <div class="category-chips-grid">
                            <label class="category-chip-label {{ $kategoriVal === 'LISTRIK_AIR' ? 'selected' : '' }}">
                                <input type="radio" name="kategori_pengeluaran" value="LISTRIK_AIR" {{ $kategoriVal === 'LISTRIK_AIR' ? 'checked' : '' }}>
                                <span>⚡ Listrik &amp; Air</span>
                            </label>
                            <label class="category-chip-label {{ $kategoriVal === 'BBM_TRANSPORT' ? 'selected' : '' }}">
                                <input type="radio" name="kategori_pengeluaran" value="BBM_TRANSPORT" {{ $kategoriVal === 'BBM_TRANSPORT' ? 'checked' : '' }}>
                                <span>⛽ BBM / Solar</span>
                            </label>
                            <label class="category-chip-label {{ $kategoriVal === 'PEMELIHARAAN_MESIN' ? 'selected' : '' }}">
                                <input type="radio" name="kategori_pengeluaran" value="PEMELIHARAAN_MESIN" {{ $kategoriVal === 'PEMELIHARAAN_MESIN' ? 'checked' : '' }}>
                                <span>🔧 Servis Mesin</span>
                            </label>
                            <label class="category-chip-label {{ $kategoriVal === 'PERLENGKAPAN_ATK' ? 'selected' : '' }}">
                                <input type="radio" name="kategori_pengeluaran" value="PERLENGKAPAN_ATK" {{ $kategoriVal === 'PERLENGKAPAN_ATK' ? 'checked' : '' }}>
                                <span>📦 ATK &amp; Kardus</span>
                            </label>
                            <label class="category-chip-label {{ $kategoriVal === 'KONSUMSI_RUMAH_TANGGA' ? 'selected' : '' }}">
                                <input type="radio" name="kategori_pengeluaran" value="KONSUMSI_RUMAH_TANGGA" {{ $kategoriVal === 'KONSUMSI_RUMAH_TANGGA' ? 'checked' : '' }}>
                                <span>🍽️ Makan / Dapur</span>
                            </label>
                            <label class="category-chip-label {{ $kategoriVal === 'PENGIRIMAN_EKSPEDISI' ? 'selected' : '' }}">
                                <input type="radio" name="kategori_pengeluaran" value="PENGIRIMAN_EKSPEDISI" {{ $kategoriVal === 'PENGIRIMAN_EKSPEDISI' ? 'checked' : '' }}>
                                <span>🚚 Kirim / Ekspedisi</span>
                            </label>
                            <label class="category-chip-label {{ $kategoriVal === 'BIAYA_OPERASIONAL' ? 'selected' : '' }}" style="grid-column: span 2;">
                                <input type="radio" name="kategori_pengeluaran" value="BIAYA_OPERASIONAL" {{ $kategoriVal === 'BIAYA_OPERASIONAL' ? 'checked' : '' }}>
                                <span>📝 Operasional Lainnya</span>
                            </label>
                        </div>
                    </div>

                    {{-- 4. POS AKUN BEBAN (COA) --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="coa_id" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Pos Akun Beban (COA) <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="coa_id" id="coa_id" class="form-control" required style="font-weight: 600; width: 100%;">
                            <option value="">-- Pilih Akun Beban Terkait --</option>
                            @foreach ($expenseCoas as $c)
                                <option value="{{ $c->coa_id }}" {{ old('coa_id') == $c->coa_id ? 'selected' : '' }}>
                                    {{ $c->kode_akun }} - {{ $c->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. TANGGAL & METODE --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div>
                            <label for="tgl_keluar" style="font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">
                                Tanggal <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="date" name="tgl_keluar" id="tgl_keluar" class="form-control" 
                                   value="{{ old('tgl_keluar', date('Y-m-d')) }}" required style="width: 100%;">
                        </div>
                        <div>
                            <label for="metode_bayar" style="font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">
                                Metode
                            </label>
                            <select name="metode_bayar" id="metode_bayar" class="form-control" style="width: 100%;">
                                <option value="CASH" {{ old('metode_bayar') === 'CASH' ? 'selected' : '' }}>TUNAI (CASH)</option>
                                <option value="TRANSFER" {{ old('metode_bayar') === 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
                                <option value="GIRO" {{ old('metode_bayar') === 'GIRO' ? 'selected' : '' }}>GIRO / CEK</option>
                            </select>
                        </div>
                    </div>

                    {{-- 6. NO STRUK / REF --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="no_referensi_bank" style="font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">
                            No. Struk / Ref. Transaksi
                        </label>
                        <input type="text" name="no_referensi_bank" id="no_referensi_bank" class="form-control" 
                               value="{{ old('no_referensi_bank') }}" 
                               placeholder="No. nota / no. struk SPBU / ref transfer" style="width: 100%;">
                    </div>

                    {{-- 7. CATATAN --}}
                    <div class="form-group">
                        <label for="catatan" style="font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem; display: block;">
                            Catatan Operasional
                        </label>
                        <textarea name="catatan" id="catatan" rows="2" class="form-control" 
                                  placeholder="Keterangan keperluan kas..." style="width: 100%; font-size: 0.8125rem;">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: WORKSTATION RINCIAN ITEM & RINGKASAN TOTAL (LEGA & FULL-WIDTH) --}}
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                
                {{-- CARD RINCIAN --}}
                <div class="bkk-card">
                    <div class="bkk-card-header">
                        <span class="bkk-card-title">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Rincian Pengeluaran Dana</span>
                        </span>
                        <button type="button" class="btn-add-row" id="btnAddRow">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Tambah Baris</span>
                        </button>
                    </div>
                    <div class="bkk-card-body" style="padding: 0;">
                        <div style="overflow-x: auto;">
                            <table class="items-table-bkk">
                                <thead>
                                    <tr>
                                        <th style="min-width: 280px;">Uraian / Deskripsi Pengeluaran <span style="color: #dc2626;">*</span></th>
                                        <th style="width: 85px;">Qty</th>
                                        <th style="width: 100px;">Satuan</th>
                                        <th style="width: 140px;">Harga Satuan</th>
                                        <th style="width: 160px;">Nominal (Rp) <span style="color: #dc2626;">*</span></th>
                                        <th style="width: 45px; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bkkItemsBody">
                                    <tr class="bkk-item-row">
                                        <td>
                                            <input type="text" name="items[0][uraian]" class="form-control item-uraian" placeholder="Contoh: Token Listrik Pabrik Line A" required>
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="items[0][qty]" class="form-control item-qty" value="1" min="0.01">
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][satuan]" class="form-control item-satuan" placeholder="Pcs/Ltr">
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="items[0][harga_satuan]" class="form-control item-harga" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="items[0][nominal]" class="form-control item-nominal" placeholder="0" required>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-remove-row" title="Hapus baris">&times;</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- QUICK PRESET PILLS --}}
                        <div style="padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #f1f5f9;">
                            <div style="font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.35rem;">
                                💡 Klik Cepat Template Biaya Operasional:
                            </div>
                            <div class="quick-tags-container" style="border-top: none; margin-top: 0; padding-top: 0;">
                                <span class="quick-tag-pill" data-uraian="Token Listrik Pabrik Line A" data-nominal="500000" data-kategori="LISTRIK_AIR">
                                    ⚡ Token Listrik 500rb
                                </span>
                                <span class="quick-tag-pill" data-uraian="Pembelian Solar Boiler Produksi (100 Ltr)" data-nominal="680000" data-kategori="BBM_TRANSPORT">
                                    ⛽ Solar Boiler 100L
                                </span>
                                <span class="quick-tag-pill" data-uraian="Bensin Pertalite Armada Truk Ekspedisi" data-nominal="250000" data-kategori="BBM_TRANSPORT">
                                    🚚 BBM Truk 250rb
                                </span>
                                <span class="quick-tag-pill" data-uraian="Servis Las &amp; Ganti Oli Mesin Perajang" data-nominal="350000" data-kategori="PEMELIHARAAN_MESIN">
                                    🔧 Servis Mesin 350rb
                                </span>
                                <span class="quick-tag-pill" data-uraian="Pembelian Lakban Coklat Kardus (1 Dus)" data-nominal="240000" data-kategori="PERLENGKAPAN_ATK">
                                    📦 Lakban Coklat 1 Dus
                                </span>
                                <span class="quick-tag-pill" data-uraian="Makan &amp; Minum Lembur Operator Goreng" data-nominal="180000" data-kategori="KONSUMSI_RUMAH_TANGGA">
                                    🍽️ Makan Lembur 180rb
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD TOTAL & AKSI SUBMIT --}}
                <div class="bkk-card">
                    <div class="bkk-card-body" style="padding: 1.25rem;">
                        <div class="bkk-summary-box">
                            <div>
                                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 800;">
                                    Total Pengeluaran Kas (BKK)
                                </div>
                                <div class="bkk-terbilang-text" id="bkkTerbilangDisplay">
                                    Nol Rupiah
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="bkk-total-display" id="bkkTotalDisplay">
                                    Rp 0
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem; align-items: center;">
                            <a href="{{ route('keuangan.bayar.index', ['tab' => 'operasional']) }}" class="btn btn-secondary" style="font-weight: 600; padding: 0.65rem 1.25rem;">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 0.75rem 2rem; background: #0284c7; border-color: #0284c7; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3); font-size: 0.9rem;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan &amp; Terbitkan BKK</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_keluar/kas-keluar-create.js') }}"></script>
@endpush
