@extends('layouts.app')

@section('title', 'Tambah Formula Resep (BOM) Baru - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/resep/resep-form.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('master.resep.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem;">
        &larr; Kembali ke Daftar Formula Resep
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.5rem;">
        Tambah Formula Resep Produksi (BOM)
    </h1>
    <p style="color: #64748b; font-size: 0.875rem;">
        Definisikan takaran standar bahan baku dan bahan penolong per batch ukuran standar untuk otomatisasi pengeluaran gudang (FIFO) dan perhitungan HPP.
    </p>
</div>

<form action="{{ route('master.resep.store') }}" method="POST" id="bomForm">
    @csrf

    {{-- KARTU HEADER FORMULA --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">Informasi Formula Resep</h2>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">Standar Produksi</span>
        </div>
        <div style="padding: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Nomor Resep / BOM <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="bom_no" value="{{ old('bom_no', $autoNo) }}" class="form-control" style="font-family: monospace; font-weight: 700;" required>
                <small style="color: #64748b; font-size: 0.75rem;">Kode unik formula resep standar.</small>
            </div>

            <div style="grid-column: span 2;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Nama Formula Resep <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="bom_nm" value="{{ old('bom_nm') }}" placeholder="Contoh: Produksi Keripik Singkong Maksi 500 (Per 100 Karton)" class="form-control" required>
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Produk Jadi Target (Output) <span style="color: #ef4444;">*</span>
                </label>
                <select name="barang_jadi_id" id="barang_jadi_id" class="form-control" required onchange="onTargetProductChanged(this)">
                    <option value="">-- Pilih Produk Jadi / WIP --</option>
                    @foreach ($barangJadiList as $bj)
                        <option value="{{ $bj->barang_id }}" data-satuan="{{ $bj->satuanDasar?->satuan_nm }}" {{ old('barang_jadi_id') == $bj->barang_id ? 'selected' : '' }}>
                            [{{ $bj->barang_cd }}] {{ $bj->barang_nm }} ({{ $bj->satuanDasar?->satuan_nm }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Ukuran Batch Standar <span style="color: #ef4444;">*</span>
                </label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="number" step="0.0001" min="0.0001" name="batch_ukuran_qty" id="batch_ukuran_qty" value="{{ old('batch_ukuran_qty', '100') }}" class="form-control" style="font-weight: 700; text-align: right;" required>
                    <span id="targetSatuanLabel" style="font-weight: 600; color: #475569; font-size: 0.875rem; white-space: nowrap;">Unit</span>
                </div>
                <small style="color: #64748b; font-size: 0.75rem;">Basis kalkulasi takaran bahan (misal per 100 Karton).</small>
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Formula Resep (Opsional)
                </label>
                <input type="text" name="catatan_txt" value="{{ old('catatan_txt') }}" placeholder="Keterangan spesifikasi rasa, lini mesin penggorengan, standar QC, dll." class="form-control">
            </div>
        </div>
    </div>

    {{-- KARTU RINCIAN BAHAN BAKU & PENOLONG (BOM DETAIL) --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <div>
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">Rincian Bahan Baku &amp; Penolong (BOM Detail)</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.15rem;">
                    Kebutuhan bahan untuk menghasilkan 1 batch ukuran standar di atas.
                </p>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addMaterialRow()">
                + Tambah Baris Bahan
            </button>
        </div>

        <div style="overflow-x: auto; padding: 1rem;">
            <table class="materials-table" style="width: 100%; border-collapse: collapse;" id="materialsTable">
                <thead>
                    <tr>
                        <th style="width: 38%;">Bahan Baku / Penolong</th>
                        <th style="width: 22%; text-align: right;">Takaran Kebutuhan (Qty)</th>
                        <th style="width: 14%;">Satuan</th>
                        <th style="width: 20%;">Keterangan / Catatan</th>
                        <th style="width: 6%; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="materialsBody">
                    {{-- Diisi secara dinamis oleh JavaScript --}}
                </tbody>
            </table>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-bottom: 3rem;">
        <a href="{{ route('master.resep.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" style="background: #2563eb; padding: 0.65rem 1.75rem; font-size: 0.95rem; font-weight: 700;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Formula Resep (BOM)
        </button>
    </div>
</form>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            bahanBakuList: @json($bahanBakuList),
            initialDetails: []
        };
    </script>
    <script src="{{ asset('js/master/resep/resep-form.js') }}"></script>
@endpush
