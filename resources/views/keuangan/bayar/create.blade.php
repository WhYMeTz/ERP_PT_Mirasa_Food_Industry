@extends('layouts.app')

@section('title', 'Bayar Tagihan Hutang (Bukti Kas Keluar)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar/bayar-create.css') }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.bayar.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                &larr; Riwayat Kas Keluar
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                💳 Pembayaran Tagihan Hutang (Bukti Kas Keluar - BKK)
            </h1>
        </div>
        <p style="font-size: 0.8125rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Pilih rekening sumber pembayaran dan alokasikan pelunasan tagihan supplier.
        </p>
    </div>

    <div>
        <a href="{{ route('keuangan.bayar.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
            Batal
        </a>
    </div>
</div>

<form action="{{ route('keuangan.bayar.store') }}" method="POST" id="formBayarHutang">
    @csrf

    <div class="bayar-form-grid">
        {{-- SISI KIRI: INPUT DETAIL PEMBAYARAN & TABEL ALOKASI TAGIHAN --}}
        <div>
            {{-- KARTU 1: REKENING SUMBER DANA & METODE PEMBAYARAN --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.4rem;">
                    <span>🏦</span> <span>1. Rekening Sumber Dana &amp; Metode Pembayaran</span>
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Rekening Kas / Bank Pengirim <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="kas_bank_id" id="kasBankSelect" class="form-control" style="font-size: 0.85rem; height: 38px; font-weight: 700;" required>
                            @foreach ($kasBanks as $kb)
                                <option value="{{ $kb->kas_bank_id }}" 
                                        data-saldo="{{ $kb->saldo_berjalan }}"
                                        data-nama="{{ $kb->nama_akun }}"
                                        {{ old('kas_bank_id') == $kb->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }} (Saldo: Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Bayar <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" 
                               name="tgl_bayar" 
                               value="{{ old('tgl_bayar', date('Y-m-d')) }}" 
                               class="form-control" 
                               style="font-size: 0.85rem; height: 38px; font-weight: 700;" 
                               required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Metode Pembayaran <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="metode_bayar" class="form-control" style="font-size: 0.85rem; height: 38px;" required>
                            <option value="TRANSFER" {{ old('metode_bayar') == 'TRANSFER' ? 'selected' : '' }}>📲 Transfer Bank (Internet Banking / ATM)</option>
                            <option value="CASH" {{ old('metode_bayar') == 'CASH' ? 'selected' : '' }}>💵 Tunai (Kas Pabrik / COD)</option>
                            <option value="GIRO" {{ old('metode_bayar') == 'GIRO' ? 'selected' : '' }}>📜 Bilyet Giro</option>
                            <option value="CEK" {{ old('metode_bayar') == 'CEK' ? 'selected' : '' }}>📝 Cek Bank</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            No. Referensi Bank / Bukti Transfer
                        </label>
                        <input type="text" 
                               name="no_referensi_bank" 
                               value="{{ old('no_referensi_bank') }}" 
                               class="form-control" 
                               placeholder="Contoh: REF/BCA/9817293" 
                               style="font-size: 0.85rem; height: 38px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Nama Penerima / Kasir Supplier
                        </label>
                        <input type="text" 
                               name="penerima_nama" 
                               value="{{ old('penerima_nama') }}" 
                               class="form-control" 
                               placeholder="Nama kontak rekanan yang menerima dana" 
                               style="font-size: 0.85rem; height: 38px;">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: PILIH MITRA SUPPLIER --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🏢</span> <span>2. Mitra Supplier Rekanan</span>
                    </h3>

                    @if ($supplierId)
                        <a href="{{ route('keuangan.bayar.create') }}" style="font-size: 0.775rem; color: #0284c7; text-decoration: underline; font-weight: 700;">
                            Tampilkan Semua Supplier &rarr;
                        </a>
                    @endif
                </div>

                <div>
                    <select name="supplier_id" 
                            id="supplierFilterSelect" 
                            class="form-control" 
                            style="font-size: 0.85rem; height: 38px; font-weight: 600;"
                            onchange="window.location.href='{{ route('keuangan.bayar.create') }}?supplier_id=' + this.value">
                        <option value="">-- Semua Supplier (Pilih untuk Memfilter) --</option>
                        @foreach ($suppliers as $supp)
                            @php
                                $uData = $unpaidCounts[$supp->supplier_id] ?? null;
                                $uCount = $uData ? $uData->count_tagihan : 0;
                                $uSisa = $uData ? (float) $uData->total_sisa : 0;
                            @endphp
                            <option value="{{ $supp->supplier_id }}" {{ $supplierId == $supp->supplier_id ? 'selected' : '' }}>
                                {{ $uCount > 0 ? '🟢 ' : '⚪ ' }}{{ $supp->supplier_nm }} ({{ $uCount }} Tagihan Belum Lunas - Sisa Rp {{ number_format($uSisa, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- KARTU 3: TABEL ALOKASI TAGIHAN HUTANG YANG BELUM LUNAS --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                            <span>📋</span> <span>3. Alokasi Pembayaran Faktur Tagihan</span>
                        </h3>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                            Centang faktur yang hendak dibayar atau masukkan nominal cicilan/parsial
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="selectAllInvoices()" style="font-size: 0.75rem; font-weight: 700;">
                            ☑️ Bayar Penuh Semua
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllInvoices()" style="font-size: 0.75rem; font-weight: 700;">
                            ⬜ Batal Pilih
                        </button>
                    </div>
                </div>

                <div class="unpaid-table-wrapper">
                    <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8rem;">
                        <thead style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                            <tr style="color: #475569; font-size: 0.725rem; text-transform: uppercase;">
                                <th style="padding: 0.65rem 0.75rem; width: 40px; text-align: center;">Pilih</th>
                                <th style="padding: 0.65rem 0.5rem; width: 170px;">No. Tagihan (AP)</th>
                                <th style="padding: 0.65rem 0.5rem;">Supplier &amp; Faktur</th>
                                <th style="padding: 0.65rem 0.5rem; width: 120px;">Jatuh Tempo</th>
                                <th style="padding: 0.65rem 0.5rem; text-align: right; width: 120px;">Sisa Hutang</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 160px;">Nominal Bayar (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="unpaidInvoicesTbody">
                            @forelse ($unpaidHutang as $idx => $inv)
                                @php
                                    $isPreSelected = ($selectedHutang && $selectedHutang->hutang_id == $inv->hutang_id);
                                    $sisa = (float) $inv->sisa_hutang;

                                    $daysRemaining = round(now()->diffInDays(\Carbon\Carbon::parse($inv->tgl_jatuh_tempo), false));
                                    if ($daysRemaining < 0) {
                                        $tempoClass = 'overdue';
                                        $tempoText = 'Lewat ' . abs($daysRemaining) . ' hr';
                                    } elseif ($daysRemaining <= 7) {
                                        $tempoClass = 'urgent';
                                        $tempoText = $daysRemaining . ' hr lagi';
                                    } else {
                                        $tempoClass = 'safe';
                                        $tempoText = $daysRemaining . ' hr';
                                    }
                                @endphp
                                <tr class="unpaid-row {{ $isPreSelected ? 'checked-row' : '' }}" 
                                    data-hutang-id="{{ $inv->hutang_id }}"
                                    data-sisa="{{ $sisa }}">
                                    
                                    <td style="padding: 0.65rem 0.75rem; text-align: center;">
                                        <input type="checkbox" 
                                               id="chk_hutang_{{ $inv->hutang_id }}" 
                                               class="row-invoice-check"
                                               {{ $isPreSelected ? 'checked' : '' }}
                                               style="cursor: pointer; accent-color: #16a34a; width: 16px; height: 16px;"
                                               onclick="toggleRowInvoice({{ $inv->hutang_id }})">
                                    </td>

                                    <td style="padding: 0.65rem 0.5rem;">
                                        <div style="font-weight: 800; font-family: monospace; color: #0284c7;">
                                            {{ $inv->hutang_no }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b;">
                                            Tgl: {{ date('d/m/Y', strtotime($inv->tgl_tagihan)) }}
                                        </div>
                                    </td>

                                    <td style="padding: 0.65rem 0.5rem;">
                                        <div style="font-weight: 700; color: #0f172a;">
                                            {{ $inv->supplier->supplier_nm ?? '-' }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b;">
                                            Faktur: {{ $inv->no_faktur_supplier ?: '-' }}
                                        </div>
                                    </td>

                                    <td style="padding: 0.65rem 0.5rem;">
                                        <span class="badge-tempo {{ $tempoClass }}">
                                            {{ $tempoText }}
                                        </span>
                                        <div style="font-size: 0.68rem; color: #64748b; margin-top: 2px;">
                                            {{ date('d/m/Y', strtotime($inv->tgl_jatuh_tempo)) }}
                                        </div>
                                    </td>

                                    <td style="padding: 0.65rem 0.5rem; text-align: right; font-weight: 800; color: #dc2626; font-size: 0.85rem;">
                                        Rp {{ number_format($sisa, 0, ',', '.') }}
                                    </td>

                                    <td style="padding: 0.65rem 0.75rem; text-align: right;">
                                        <input type="hidden" name="allocations[{{ $idx }}][hutang_id]" value="{{ $inv->hutang_id }}">
                                        
                                        <div style="display: flex; gap: 4px; align-items: center; justify-content: flex-end;">
                                            <input type="number" 
                                                   step="1" 
                                                   min="0" 
                                                   max="{{ $sisa }}"
                                                   name="allocations[{{ $idx }}][nominal_bayar]" 
                                                   id="nominal_bayar_{{ $inv->hutang_id }}"
                                                   value="{{ $isPreSelected ? $sisa : 0 }}" 
                                                   class="form-control input-nominal-bayar" 
                                                   style="font-size: 0.825rem; height: 32px; text-align: right; font-weight: 700; width: 110px;"
                                                   placeholder="0">
                                            
                                            <button type="button" 
                                                    class="btn-pay-full" 
                                                    title="Bayar 100% lunas tagihan ini"
                                                    onclick="payFullInvoice({{ $inv->hutang_id }})">
                                                Penuh
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                        Tidak ada tagihan hutang yang belum lunas pada kriteria ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- KARTU 4: CATATAN TAMBAHAN --}}
            <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Kas Keluar / Memo BKK
                </label>
                <textarea name="catatan" 
                          rows="2" 
                          class="form-control" 
                          placeholder="Catatan pelunasan, nomor rekening tujuan supplier, dsb..." 
                          style="font-size: 0.8125rem;">{{ old('catatan') }}</textarea>
            </div>
        </div>

        {{-- SISI KANAN: STICKY SUMMARY & EKSEKUSI PEMBAYARAN --}}
        <div>
            <div class="summary-sticky-card">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                    Ringkasan Kas Keluar
                </h3>

                <div class="summary-row">
                    <span>Tagihan Dipilih</span>
                    <strong id="summaryCountInvoices" style="color: #0f172a;">0 Tagihan</strong>
                </div>

                <div class="summary-row total-row">
                    <span>Total Bayar (BKK)</span>
                    <strong id="summaryTotalBayar" style="color: #16a34a;">Rp 0</strong>
                </div>

                {{-- KOTAK MONITORING SALDO REKENING KAS/BANK --}}
                <div class="balance-preview-box" id="boxSaldoPreview">
                    <div style="font-weight: 700; color: #334155; margin-bottom: 4px; display: flex; justify-content: space-between;">
                        <span>Rekening Sumber:</span>
                        <strong id="previewAkunNama" style="color: #0284c7;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #64748b;">
                        <span>Saldo Saat Ini:</span>
                        <strong id="previewSaldoTersedia">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 4px; border-top: 1px dashed #cbd5e1; padding-top: 4px;">
                        <span>Sisa Saldo Sesudah:</span>
                        <strong id="previewSaldoSesudah" style="color: #16a34a;">Rp 0</strong>
                    </div>
                </div>

                {{-- ALERT PERINGATAN JIKA SALDO DEFISIT --}}
                <div id="boxSaldoWarning" style="display: none; background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.75rem; margin-top: 0.75rem; font-size: 0.75rem; color: #991b1b; line-height: 1.4;">
                    ⚠️ <strong>Peringatan Defisit:</strong><br>
                    Total nominal pembayaran melebihi saldo kas/bank yang tersedia pada rekening ini.
                </div>

                <div style="margin-top: 1.25rem;">
                    <button type="submit" 
                            id="btnSubmitBayar"
                            class="btn btn-primary" 
                            style="width: 100%; padding: 0.75rem; font-weight: 800; font-size: 0.9rem; border-radius: 8px; box-shadow: 0 3px 6px rgba(2, 132, 199, 0.3);"
                            disabled>
                        💾 Eksekusi &amp; Terbitkan BKK
                    </button>
                    <div style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 0.5rem; line-height: 1.3;">
                        Saldo kas/bank akan terpotong dan mutasi kas keluar langsung dibukukan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/keuangan/bayar/bayar-create.js') }}"></script>
@endpush
