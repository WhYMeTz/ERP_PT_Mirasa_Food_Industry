@extends('layouts.app')

@section('title', 'Catat Bukti Kas Masuk (BKM)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_masuk/kas-masuk-form.css') }}">
@endpush

@section('content')
<div class="bkm-form-container">
    {{-- HEADER FORM --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <a href="{{ route('keuangan.kas_masuk.index') }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                    Input Bukti Kas Masuk (BKM)
                </h1>
                <span style="background: #ecfdf5; color: #059669; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                    Penerimaan Kas &amp; Bank
                </span>
            </div>
            <p style="font-size: 0.825rem; color: #64748b; margin: 0.25rem 0 0 1.75rem;">
                Pencatatan uang masuk operasional pabrik. Saldo rekening kas/bank yang dipilih akan otomatis bertambah saat voucher disimpan.
            </p>
        </div>

        <div>
            <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn btn-secondary" style="font-weight: 600; font-size: 0.8125rem;">
                Batal / Kembali
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

    <form method="POST" action="{{ route('keuangan.kas_masuk.store') }}" id="formKasMasuk">
        @csrf

        {{-- 1. PILIHAN KATEGORI PENERIMAAN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                    Langkah 1: Pilih Sumber / Kategori Penerimaan
                </span>
            </div>
            <div style="padding: 1.25rem;">
                <div class="category-selection-grid">
                    @php $kategoriVal = old('kategori_penerimaan', $defaultKategori); @endphp
                    
                    {{-- 1. PENJUALAN LIMBAH --}}
                    <label class="category-card-option {{ $kategoriVal === 'PENJUALAN_LIMBAH' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PENJUALAN_LIMBAH" {{ $kategoriVal === 'PENJUALAN_LIMBAH' ? 'checked' : '' }}>
                        <div class="category-card-title">
                            <span>♻️</span>
                            <span>Penjualan Limbah</span>
                        </div>
                        <div class="category-card-desc">
                            Minyak jelantah bekas goreng, kulit singkong, ampas pabrik.
                        </div>
                    </label>

                    {{-- 2. PENJUALAN TUNAI --}}
                    <label class="category-card-option {{ $kategoriVal === 'PENJUALAN_TUNAI' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PENJUALAN_TUNAI" {{ $kategoriVal === 'PENJUALAN_TUNAI' ? 'checked' : '' }}>
                        <div class="category-card-title">
                            <span>💵</span>
                            <span>Penjualan Tunai</span>
                        </div>
                        <div class="category-card-desc">
                            Remah keripik/afkir, penjualan eceran langsung di pabrik.
                        </div>
                    </label>

                    {{-- 3. SETORAN MODAL --}}
                    <label class="category-card-option {{ $kategoriVal === 'SETORAN_MODAL' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="SETORAN_MODAL" {{ $kategoriVal === 'SETORAN_MODAL' ? 'checked' : '' }}>
                        <div class="category-card-title">
                            <span>💼</span>
                            <span>Setoran Modal Owner</span>
                        </div>
                        <div class="category-card-desc">
                            Injeksi modal kerja operasional dari pemilik PT Mirasa.
                        </div>
                    </label>

                    {{-- 4. PELUNASAN PIUTANG --}}
                    <label class="category-card-option {{ $kategoriVal === 'PELUNASAN_PIUTANG' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PELUNASAN_PIUTANG" {{ $kategoriVal === 'PELUNASAN_PIUTANG' ? 'checked' : '' }}>
                        <div class="category-card-title">
                            <span>🤝</span>
                            <span>Pelunasan Piutang</span>
                        </div>
                        <div class="category-card-desc">
                            Pembayaran tagihan produk jadi dari mitra customer / agen.
                        </div>
                    </label>

                    {{-- 5. PENDAPATAN LAIN --}}
                    <label class="category-card-option {{ $kategoriVal === 'PENDAPATAN_LAIN' ? 'selected' : '' }}">
                        <input type="radio" name="kategori_penerimaan" value="PENDAPATAN_LAIN" {{ $kategoriVal === 'PENDAPATAN_LAIN' ? 'checked' : '' }}>
                        <div class="category-card-title">
                            <span>📦</span>
                            <span>Pendapatan Lain</span>
                        </div>
                        <div class="category-card-desc">
                            Jasa operasional, bunga bank, atau penerimaan non-usaha lainnya.
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- 2. INFORMASI TRANSAKSI & REKENING TUJUAN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                    Langkah 2: Rekening Kas/Bank &amp; Data Penyetor
                </span>
            </div>
            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    {{-- REKENING KAS / BANK TUJUAN --}}
                    <div class="form-group">
                        <label for="kas_bank_id" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Rekening / Kas Tujuan Penerimaan <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="kas_bank_id" id="kas_bank_id" class="form-control" required style="font-weight: 600;">
                            <option value="">-- Pilih Rekening Kas / Bank --</option>
                            @foreach ($kasBanks as $kb)
                                <option value="{{ $kb->kas_bank_id }}" {{ old('kas_bank_id') == $kb->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kb->kas_bank_nm }} ({{ $kb->tipe_akun }}) - Saldo: Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size: 0.725rem; color: #059669; margin-top: 0.25rem; display: block;">
                            Saldo akun ini akan bertambah secara otomatis sesuai total voucher.
                        </span>
                    </div>

                    {{-- TANGGAL PENERIMAAN --}}
                    <div class="form-group">
                        <label for="tgl_terima" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Tanggal Penerimaan <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" 
                               name="tgl_terima" 
                               id="tgl_terima" 
                               class="form-control" 
                               value="{{ old('tgl_terima', date('Y-m-d')) }}" 
                               required>
                    </div>

                    {{-- METODE PENERIMAAN --}}
                    <div class="form-group">
                        <label for="metode_terima" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Metode Pembayaran <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="metode_terima" id="metode_terima" class="form-control" required>
                            <option value="CASH" {{ old('metode_terima') === 'CASH' ? 'selected' : '' }}>💵 Tunai (Cash Fisik)</option>
                            <option value="TRANSFER" {{ old('metode_terima', 'TRANSFER') === 'TRANSFER' ? 'selected' : '' }}>🏦 Transfer Bank</option>
                            <option value="GIRO_CEK" {{ old('metode_terima') === 'GIRO_CEK' ? 'selected' : '' }}>📄 Giro / Cek</option>
                            <option value="EDC_QRIS" {{ old('metode_terima') === 'EDC_QRIS' ? 'selected' : '' }}>📱 QRIS / EDC</option>
                        </select>
                    </div>

                    {{-- NAMA PENYETOR / DARI --}}
                    <div class="form-group">
                        <label for="penyetor_nama" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Diterima Dari (Penyetor / Pembeli) <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               name="penyetor_nama" 
                               id="penyetor_nama" 
                               class="form-control" 
                               value="{{ old('penyetor_nama') }}" 
                               placeholder="Nama pembeli limbah / penyetor modal / customer" 
                               required>
                    </div>

                    {{-- MITRA CUSTOMER (KHUSUS PIUTANG) --}}
                    <div class="form-group" id="groupCustomerPelanggan" style="{{ $kategoriVal === 'PELUNASAN_PIUTANG' ? 'display: block;' : 'display: none;' }}">
                        <label for="customer_id" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Mitra Customer (Jika Pelunasan Piutang)
                        </label>
                        <select name="customer_id" id="customer_id" class="form-control">
                            <option value="">-- Pilih Customer Terdaftar --</option>
                            @foreach ($customers as $cust)
                                <option value="{{ $cust->customer_id }}" {{ old('customer_id') == $cust->customer_id ? 'selected' : '' }}>
                                    {{ $cust->customer_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NOMOR REFERENSI BANK --}}
                    <div class="form-group">
                        <label for="no_referensi_bank" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Nomor Referensi / No. Ref Transfer (Opsional)
                        </label>
                        <input type="text" 
                               name="no_referensi_bank" 
                               id="no_referensi_bank" 
                               class="form-control" 
                               value="{{ old('no_referensi_bank') }}" 
                               placeholder="Contoh: TRF-BCA-99812 / Ref Mutasi">
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. TABEL RINCIAN ITEM PENERIMAAN KAS --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        Langkah 3: Rincian Item Penerimaan Kas
                    </span>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0 0 0;">
                        Tentukan barang/jasa yang dijual beserta kuantitas dan harga satuan.
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addItemRow()" style="font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris</span>
                    </button>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="bkm-item-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="min-width: 250px;">Deskripsi / Rincian Penerimaan <span style="color: #dc2626;">*</span></th>
                            <th style="width: 120px; text-align: right;">Kuantitas (Qty)</th>
                            <th style="width: 110px;">Satuan</th>
                            <th style="width: 180px; text-align: right;">Harga Satuan (Rp)</th>
                            <th style="width: 180px; text-align: right;">Subtotal (Rp)</th>
                            <th style="width: 50px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody">
                        @php
                            $oldItems = old('items', [
                                ['deskripsi' => '', 'qty' => 1, 'satuan' => 'kg', 'harga_satuan' => 0, 'subtotal' => 0]
                            ]);
                        @endphp
                        @foreach ($oldItems as $idx => $item)
                            <tr class="item-row" id="row_{{ $idx }}">
                                <td style="text-align: center; color: #64748b; font-weight: 700;" class="row-number">
                                    {{ $idx + 1 }}
                                </td>
                                <td>
                                    <input type="text" 
                                           name="items[{{ $idx }}][deskripsi]" 
                                           class="form-control" 
                                           value="{{ $item['deskripsi'] ?? $item['uraian'] ?? '' }}" 
                                           placeholder="Misal: Penjualan Minyak Jelantah / Kulit Singkong..." 
                                           required>
                                </td>
                                <td style="width: 120px;">
                                    <input type="number" 
                                           name="items[{{ $idx }}][qty]" 
                                           class="form-control input-qty" 
                                           value="{{ $item['qty'] ?? 1 }}" 
                                           step="any" 
                                           min="0.0001" 
                                           style="text-align: right;" 
                                           oninput="calculateRowSubtotal({{ $idx }})" 
                                           required>
                                </td>
                                <td style="width: 110px;">
                                    <input type="text" 
                                           name="items[{{ $idx }}][satuan]" 
                                           class="form-control" 
                                           value="{{ $item['satuan'] ?? 'kg' }}" 
                                           placeholder="kg / sak / drum">
                                </td>
                                <td style="width: 180px;">
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: #64748b; font-weight: 600;">Rp</span>
                                        <input type="number" 
                                               name="items[{{ $idx }}][harga_satuan]" 
                                               class="form-control input-harga" 
                                               value="{{ $item['harga_satuan'] ?? 0 }}" 
                                               step="any" 
                                               min="0" 
                                               style="padding-left: 2rem; text-align: right;" 
                                               oninput="calculateRowSubtotal({{ $idx }})" 
                                               required>
                                    </div>
                                </td>
                                <td style="width: 180px; text-align: right;">
                                    <span class="subtotal-display" id="subtotalDisplay_{{ $idx }}" style="font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                        Rp {{ number_format($item['subtotal'] ?? $item['nominal'] ?? 0, 0, ',', '.') }}
                                    </span>
                                    <input type="hidden" name="items[{{ $idx }}][subtotal]" id="subtotalInput_{{ $idx }}" value="{{ $item['subtotal'] ?? $item['nominal'] ?? 0 }}">
                                </td>
                                <td style="width: 50px; text-align: center;">
                                    <button type="button" class="btn-remove-row" onclick="removeItemRow({{ $idx }})" title="Hapus Baris">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- TOTAL & TERBILANG SECTION --}}
            <div style="padding: 1.25rem; background: #fafafa; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; max-width: 550px;">
                    <label for="catatan" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Catatan Transaksi / Keterangan
                    </label>
                    <textarea name="catatan" 
                              id="catatan" 
                              rows="2" 
                              class="form-control" 
                              placeholder="Keterangan tambahan transaksi penerimaan ini (opsional)..." 
                              style="font-size: 0.85rem;">{{ old('catatan') }}</textarea>

                    <div style="margin-top: 0.75rem; font-size: 0.8rem; color: #64748b; font-style: italic;">
                        Terbilang: <span id="terbilangDisplay" style="font-weight: 700; color: #0f172a;">Nol Rupiah</span>
                    </div>
                </div>

                <div class="total-summary-card" style="min-width: 280px; text-align: right;">
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                        Total Kas Masuk (BKM)
                    </span>
                    <div class="grand-total-amount" id="grandTotalDisplay">
                        Rp 0
                    </div>
                    <div style="font-size: 0.725rem; color: #059669; margin-top: 0.3rem;">
                        ✓ Saldo Rekening Tujuan akan otomatis bertambah
                    </div>
                </div>
            </div>
        </div>

        {{-- BUTTON ACTIONS --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-bottom: 2.5rem;">
            <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn btn-secondary" style="font-weight: 600; font-size: 0.85rem; padding: 0.6rem 1.25rem;">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="font-weight: 700; font-size: 0.85rem; padding: 0.6rem 1.5rem; background: #059669; border-color: #059669; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);">
                💾 Simpan &amp; Bukukan Bukti Kas Masuk
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_masuk/kas-masuk-create.js') }}"></script>
@endpush
