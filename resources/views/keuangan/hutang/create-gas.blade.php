@extends('layouts.app')

@section('title', 'Tagihan Gas Utilitas Bulanan (Konsinyasi/Pasca-Bayar)')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.hutang.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                &larr; Riwayat Hutang
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                🔥 Tagihan Gas Utilitas Bulanan (Konsinyasi Pabrik)
            </h1>
        </div>
        <p style="font-size: 0.8125rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Penerbitan invoice bulanan pasca-bayar berdasarkan meteran riil pemakaian dapur penggorengan keripik.
        </p>
    </div>

    <div>
        <a href="{{ route('keuangan.hutang.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
            Batal
        </a>
    </div>
</div>

<form action="{{ route('keuangan.hutang.store_gas') }}" method="POST">
    @csrf

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: flex-start;">
        <div>
            {{-- KARTU 1: PERIODE & VENDOR --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0;">
                    1. Periode Pemakaian &amp; Rekanan Vendor Gas
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Bulan Periode Pemakaian <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="month" 
                               name="periode_bulan" 
                               id="periodeBulanInput" 
                               value="{{ old('periode_bulan', $periode) }}" 
                               class="form-control" 
                               style="font-size: 0.85rem; height: 38px; font-weight: 700;" 
                               onchange="window.location.href='{{ route('keuangan.hutang.create_gas') }}?periode=' + this.value"
                               required>
                        <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                            Mengubah bulan akan memuat ulang data rekap pemakaian.
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Vendor Pemasok Gas Industri <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="supplier_id" class="form-control" style="font-size: 0.85rem; height: 38px;" required>
                            <option value="">-- Pilih Vendor Gas (PGN / Samator / Rekanan) --</option>
                            @foreach ($suppliers as $supp)
                                <option value="{{ $supp->supplier_id }}" {{ old('supplier_id') == $supp->supplier_id ? 'selected' : '' }}>
                                    {{ $supp->supplier_nm }} ({{ $supp->supplier_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            No. Faktur / Tagihan Vendor
                        </label>
                        <input type="text" 
                               name="no_faktur_supplier" 
                               value="{{ old('no_faktur_supplier') }}" 
                               class="form-control" 
                               placeholder="INV/GAS/2026/..." 
                               style="font-size: 0.85rem; height: 38px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Jatuh Tempo <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" 
                               name="tgl_jatuh_tempo" 
                               value="{{ old('tgl_jatuh_tempo', date('Y-m-d', strtotime('+14 days'))) }}" 
                               class="form-control" 
                               style="font-size: 0.85rem; height: 38px;" 
                               required>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: REKAP PEMAKAIAN FISIK & HARGA SATUAN --}}
            <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">
                        2. Rekap Meteran Pemakaian Gas Periode {{ $periode }}
                    </h3>
                    <span style="font-size: 0.75rem; background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 6px; font-weight: 700;">
                        {{ $gasSummary['jumlah_batch'] ?? 0 }} Hari Masak Terdata
                    </span>
                </div>

                <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                    <div style="font-size: 0.8rem; color: #9a3412; line-height: 1.4;">
                        <strong>Informasi Sinkronisasi Dapur Produksi:</strong><br>
                        Nilai kuantitas di bawah ini ditarik otomatis dari buku pemakaian gas dapur penggorengan pada modul produksi. Anda dapat menyesuaikan angkanya jika terdapat selisih pembacaan meteran fisik dari vendor.
                    </div>
                </div>

                <table class="table-compact" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase;">
                            <th style="padding: 0.75rem;">Jenis Pasokan Gas</th>
                            <th style="padding: 0.75rem; text-align: right; width: 140px;">Pemakaian Terdata</th>
                            <th style="padding: 0.75rem; text-align: center; width: 80px;">Satuan</th>
                            <th style="padding: 0.75rem; text-align: right; width: 160px;">Tarif / Satuan (Rp)</th>
                            <th style="padding: 0.75rem; text-align: right; width: 170px;">Subtotal Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- GAS CNG --}}
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem;">
                                <div style="font-weight: 800; color: #0f172a;">Gas CNG (Compressed Natural Gas)</div>
                                <div style="font-size: 0.7rem; color: #64748b;">Pipa / Skid tank untuk wajan penggorengan kontinu</div>
                            </td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="qty_m3" 
                                       id="qtyM3Input" 
                                       value="{{ old('qty_m3', $gasSummary['total_gas_m3'] ?? 0) }}" 
                                       class="form-control" 
                                       style="text-align: right; font-weight: 700; height: 34px;" 
                                       oninput="calcGasTotals()">
                            </td>
                            <td style="padding: 0.75rem; text-align: center; font-weight: 700; color: #475569;">
                                M³
                            </td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       name="harga_m3" 
                                       id="hargaM3Input" 
                                       value="{{ old('harga_m3', $gasSummary['tarif_m3'] ?? 11500) }}" 
                                       class="form-control" 
                                       style="text-align: right; font-weight: 700; height: 34px;" 
                                       oninput="calcGasTotals()">
                            </td>
                            <td style="padding: 0.75rem; text-align: right; font-weight: 800; color: #0284c7; font-size: 0.9rem;" id="subtotalM3Text">
                                Rp 0
                            </td>
                        </tr>

                        {{-- GAS LPG --}}
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem;">
                                <div style="font-weight: 800; color: #0f172a;">Gas LPG Industri</div>
                                <div style="font-size: 0.7rem; color: #64748b;">Tabung gas operasional pabrik</div>
                            </td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="qty_kg" 
                                       id="qtyKgInput" 
                                       value="{{ old('qty_kg', $gasSummary['total_gas_kg'] ?? 0) }}" 
                                       class="form-control" 
                                       style="text-align: right; font-weight: 700; height: 34px;" 
                                       oninput="calcGasTotals()">
                            </td>
                            <td style="padding: 0.75rem; text-align: center; font-weight: 700; color: #475569;">
                                KG
                            </td>
                            <td style="padding: 0.75rem; text-align: right;">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       name="harga_kg" 
                                       id="hargaKgInput" 
                                       value="{{ old('harga_kg', $gasSummary['tarif_kg'] ?? 18000) }}" 
                                       class="form-control" 
                                       style="text-align: right; font-weight: 700; height: 34px;" 
                                       oninput="calcGasTotals()">
                            </td>
                            <td style="padding: 0.75rem; text-align: right; font-weight: 800; color: #0284c7; font-size: 0.9rem;" id="subtotalKgText">
                                Rp 0
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- KARTU 3: CATATAN --}}
            <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Tagihan Gas
                </label>
                <textarea name="catatan" 
                          rows="2" 
                          class="form-control" 
                          style="font-size: 0.8125rem;">{{ old('catatan', "Tagihan Gas Utilitas Pabrik Periode {$periode}") }}</textarea>
            </div>
        </div>

        {{-- SISI KANAN: SUMMARY & TOMBOL --}}
        <div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: sticky; top: 80px;">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                    Total Tagihan Gas
                </h3>

                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.85rem; color: #475569; border-bottom: 1px dashed #f1f5f9;">
                    <span>Subtotal Pemakaian</span>
                    <strong id="gasTotalBrutoText" style="color: #0f172a;">Rp 0</strong>
                </div>

                <div style="padding: 0.75rem 0; border-bottom: 1px dashed #e2e8f0;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                        PPN (Jika Ada)
                    </label>
                    <input type="number" 
                           step="1" 
                           min="0" 
                           name="total_ppn" 
                           id="gasPpnInput" 
                           value="0" 
                           class="form-control" 
                           style="font-size: 0.85rem; height: 34px; text-align: right;" 
                           oninput="calcGasTotals()">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #e2e8f0; margin-top: 0.5rem; padding-top: 0.75rem; font-size: 1.15rem; font-weight: 800; color: #0284c7;">
                    <span>Grand Total</span>
                    <strong id="gasGrandTotalText">Rp 0</strong>
                </div>

                <input type="hidden" name="tgl_tagihan" value="{{ date('Y-m-d') }}">

                <div style="margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 800; font-size: 0.9rem; border-radius: 8px; box-shadow: 0 3px 6px rgba(2, 132, 199, 0.3);">
                        🔥 Terbitkan Tagihan Gas
                    </button>
                    <div style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 0.5rem; line-height: 1.3;">
                        Tagihan akan masuk ke daftar hutang dan siap dijadwalkan bayar ke vendor gas.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function calcGasTotals() {
        const qM3 = parseFloat(document.getElementById('qtyM3Input')?.value || 0);
        const pM3 = parseFloat(document.getElementById('hargaM3Input')?.value || 0);
        const subM3 = qM3 * pM3;

        const qKg = parseFloat(document.getElementById('qtyKgInput')?.value || 0);
        const pKg = parseFloat(document.getElementById('hargaKgInput')?.value || 0);
        const subKg = qKg * pKg;

        const ppn = parseFloat(document.getElementById('gasPpnInput')?.value || 0);
        const bruto = subM3 + subKg;
        const total = bruto + ppn;

        document.getElementById('subtotalM3Text').textContent = 'Rp ' + Math.round(subM3).toLocaleString('id-ID');
        document.getElementById('subtotalKgText').textContent = 'Rp ' + Math.round(subKg).toLocaleString('id-ID');
        document.getElementById('gasTotalBrutoText').textContent = 'Rp ' + Math.round(bruto).toLocaleString('id-ID');
        document.getElementById('gasGrandTotalText').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', calcGasTotals);
</script>
@endsection
