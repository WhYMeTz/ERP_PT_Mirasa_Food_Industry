@extends('layouts.app')

@section('title', 'Detail Resep ' . $bom->bom_no . ' - ERP PT Mirasa')

@section('content')
<style>
    @media print {
        header, .no-print, nav, footer, .btn {
            display: none !important;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .print-header {
            display: block !important;
        }
    }
    .print-header {
        display: none;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
</style>

<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('master.resep.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem;">
            &larr; Kembali ke Daftar Formula Resep
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.5rem;">
            Formula Resep: {{ $bom->bom_no }}
        </h1>
        <p style="color: #64748b; font-size: 0.875rem;">
            {{ $bom->bom_nm }}
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-secondary" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Standar Resep
        </button>
        <a href="{{ route('master.resep.edit', $bom->bom_id) }}" class="btn btn-primary" style="background: #2563eb; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Formula
        </a>
    </div>
</div>

{{-- KOP SURAT PRINT --}}
<div class="print-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: #000;">PT MIRASA FOOD INDUSTRY</h2>
            <p style="margin: 0; font-size: 0.85rem; color: #333;">Pabrik Pengolahan F&B Singkong • Ambartawang, Magelang</p>
        </div>
        <div style="text-align: right;">
            <h3 style="font-size: 1rem; font-weight: 700; margin: 0;">FORMULA RESEP PRODUKSI (BOM)</h3>
            <p style="margin: 0; font-size: 0.85rem; font-family: monospace;">{{ $bom->bom_no }}</p>
        </div>
    </div>
</div>

{{-- INFORMASI RESEP --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Informasi Spesifikasi Formula</h2>
        <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 700;">Aktif & Terdaftar</span>
    </div>
    <div style="padding: 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; font-size: 0.875rem;">
        <div>
            <span style="color: #64748b; display: block; font-size: 0.75rem;">Nomor Resep / Dokumen</span>
            <strong style="font-family: monospace; font-size: 1rem; color: #0284c7;">{{ $bom->bom_no }}</strong>
        </div>
        <div>
            <span style="color: #64748b; display: block; font-size: 0.75rem;">Nama Formula Resep</span>
            <strong style="color: #0f172a;">{{ $bom->bom_nm }}</strong>
        </div>
        <div>
            <span style="color: #64748b; display: block; font-size: 0.75rem;">Produk Target (Output)</span>
            <strong style="color: #0f172a;">
                @if ($bom->barangJadi)
                    [{{ $bom->barangJadi->barang_cd }}] {{ $bom->barangJadi->barang_nm }}
                @else
                    -
                @endif
            </strong>
        </div>
        <div>
            <span style="color: #64748b; display: block; font-size: 0.75rem;">Ukuran Batch Standar</span>
            <strong style="font-size: 1.05rem; color: #0f172a;">
                {{ number_format((float) $bom->batch_ukuran_qty, 0, ',', '.') }}
                <span style="font-size: 0.85rem; font-weight: normal; color: #64748b;">{{ $bom->barangJadi?->satuanDasar?->satuan_nm ?? 'Unit' }}</span>
            </strong>
        </div>
        <div style="grid-column: 1 / -1;">
            <span style="color: #64748b; display: block; font-size: 0.75rem;">Catatan Khusus / Deskripsi QC</span>
            <div style="color: #334155; margin-top: 0.2rem;">{{ $bom->catatan_txt ?: '-' }}</div>
        </div>
    </div>
</div>

{{-- TABEL KOMPOSISI BAHAN --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">
            Daftar Komposisi Bahan (Per {{ number_format((float) $bom->batch_ukuran_qty, 0, ',', '.') }} {{ $bom->barangJadi?->satuanDasar?->satuan_nm ?? 'Unit' }})
        </h2>
        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">{{ $bom->details->count() }} Komponen</span>
    </div>
    <div style="overflow-x: auto; padding: 1rem;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0; text-align: left; font-size: 0.825rem; color: #475569; text-transform: uppercase;">
                    <th style="padding: 0.65rem 0.75rem; width: 5%;">No</th>
                    <th style="padding: 0.65rem 0.75rem; width: 15%;">Kode Bahan</th>
                    <th style="padding: 0.65rem 0.75rem; width: 35%;">Nama Bahan Baku / Penolong</th>
                    <th style="padding: 0.65rem 0.75rem; width: 15%;">Kategori</th>
                    <th style="padding: 0.65rem 0.75rem; width: 15%; text-align: right;">Takaran Qty Standar</th>
                    <th style="padding: 0.65rem 0.75rem; width: 15%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bom->details as $idx => $dtl)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem; color: #64748b;">{{ $idx + 1 }}</td>
                        <td style="padding: 0.75rem; font-family: monospace; font-weight: 600; color: #0284c7;">
                            {{ $dtl->barangMentah?->barang_cd ?? '-' }}
                        </td>
                        <td style="padding: 0.75rem; font-weight: 600; color: #0f172a;">
                            {{ $dtl->barangMentah?->barang_nm ?? '-' }}
                        </td>
                        <td style="padding: 0.75rem; color: #64748b; font-size: 0.85rem;">
                            {{ $dtl->barangMentah?->jenisBarang?->jenis_barang_nm ?? '-' }}
                        </td>
                        <td style="padding: 0.75rem; text-align: right; font-weight: 700; color: #0f172a;">
                            {{ number_format((float) $dtl->kebutuhan_qty, 2, ',', '.') }}
                            <small style="color: #64748b; font-weight: normal;">{{ $dtl->barangMentah?->satuanDasar?->satuan_nm }}</small>
                        </td>
                        <td style="padding: 0.75rem; color: #64748b; font-size: 0.85rem;">
                            {{ $dtl->catatan_txt ?: '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- WIDGET SIMULATOR INTERAKTIF --}}
<div class="card no-print" style="margin-bottom: 2rem; border: 1.5px solid #bfdbfe; background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);">
    <div class="card-header" style="background: transparent; border-bottom: 1px solid #dbeafe;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.25rem;">🧮</span>
            <div>
                <h2 style="font-size: 1.05rem; font-weight: 700; color: #1e3a8a; margin: 0;">
                    Kalkulator Simulasi Kebutuhan Rencana Produksi
                </h2>
                <p style="color: #3b82f6; font-size: 0.78rem; margin: 0;">
                    Ketik jumlah rencana produksi untuk melihat takaran kuantitas bahan yang perlu disiapkan oleh tim gudang.
                </p>
            </div>
        </div>
    </div>
    <div style="padding: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
            <label style="font-weight: 700; font-size: 0.875rem; color: #1e3a8a;">
                Target Rencana Produksi:
            </label>
            <div style="display: flex; align-items: center; gap: 0.4rem; max-width: 200px;">
                <input type="number" id="simTargetQty" value="{{ (float) $bom->batch_ukuran_qty }}" min="1" step="1" class="form-control" style="font-weight: 700; font-size: 1.05rem; text-align: right; border-color: #93c5fd;" oninput="updateSimulation()">
                <span style="font-weight: 600; color: #475569; font-size: 0.875rem;">{{ $bom->barangJadi?->satuanDasar?->satuan_nm ?? 'Unit' }}</span>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 1px solid #cbd5e1; text-align: left; color: #475569; font-size: 0.8rem;">
                    <th style="padding: 0.5rem;">Nama Bahan</th>
                    <th style="padding: 0.5rem; text-align: right;">Takaran Standar (Per {{ number_format((float) $bom->batch_ukuran_qty, 0) }})</th>
                    <th style="padding: 0.5rem; text-align: right; color: #0284c7;">Total Kebutuhan Bahan</th>
                    <th style="padding: 0.5rem;">Satuan</th>
                </tr>
            </thead>
            <tbody id="simTableBody">
                {{-- Live updated via JS --}}
            </tbody>
        </table>
    </div>
</div>

@php
    $rawMaterialsData = $bom->details->map(function($d) {
        return [
            'nama'     => $d->barangMentah?->barang_nm,
            'kode'     => $d->barangMentah?->barang_cd,
            'qty_base' => (float) $d->kebutuhan_qty,
            'satuan'   => $d->barangMentah?->satuanDasar?->satuan_nm ?? 'Unit',
        ];
    })->values();
@endphp

<script>
    const BATCH_BASE = {{ (float) $bom->batch_ukuran_qty }};
    const RAW_MATERIALS = @json($rawMaterialsData);

    function updateSimulation() {
        const input = document.getElementById('simTargetQty');
        const target = parseFloat(input.value) || 0;
        const ratio = BATCH_BASE > 0 ? (target / BATCH_BASE) : 0;
        const tbody = document.getElementById('simTableBody');

        tbody.innerHTML = '';
        RAW_MATERIALS.forEach(m => {
            const total = m.qty_base * ratio;
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 0.5rem; font-weight: 600; color: #0f172a;">[${m.kode}] ${m.nama}</td>
                <td style="padding: 0.5rem; text-align: right; color: #64748b;">${m.qty_base.toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                <td style="padding: 0.5rem; text-align: right; font-weight: 700; color: #0284c7; font-size: 0.95rem;">${total.toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                <td style="padding: 0.5rem; color: #475569;">${m.satuan}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateSimulation();
    });
</script>
@endsection
