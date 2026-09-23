@extends('layouts.app')

@section('title', 'Tambah Barang Baru - ERP PT Mirasa')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('master.barang.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Barang
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Tambah Barang Baru</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Lengkapi data formulir di bawah ini untuk menambahkan barang/material.</p>
    </div>

    <div class="card">
        <form action="{{ route('master.barang.store') }}" method="POST" style="padding: 1.5rem;">
            @csrf

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                    <label for="barang_cd" class="form-label" style="margin-bottom: 0;">Kode Barang <span style="color:#ef4444;">*</span></label>
                    <button type="button" class="btn btn-secondary btn-sm" data-target="barang_cd" onclick="const sel = document.getElementById('jenis_barang_id'); const opt = sel.options[sel.selectedIndex]; fetchNextCode('barang', 'barang_cd', {jenis: opt?.dataset?.cd || ''})" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;" title="Generate Ulang Nomor Urut Otomatis">
                        ↺ Auto Generate
                    </button>
                </div>
                <input type="text" id="barang_cd" name="barang_cd" value="{{ old('barang_cd', $nextBarangCode ?? '') }}" class="form-control @error('barang_cd') border-red-500 @enderror" placeholder="Contoh: BRG-RAW-0001" required>
                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Kode otomatis terisi nomor urut berikutnya, namun tetap bisa Anda ubah manual.</small>
                @error('barang_cd')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="barang_nm" class="form-label">Nama Barang <span style="color:#ef4444;">*</span></label>
                <input type="text" id="barang_nm" name="barang_nm" value="{{ old('barang_nm') }}" class="form-control @error('barang_nm') border-red-500 @enderror" placeholder="Contoh: Singkong Manis Grade A" required>
                @error('barang_nm')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="jenis_barang_id" class="form-label">Jenis Barang <span style="color:#ef4444;">*</span></label>
                <select id="jenis_barang_id" name="jenis_barang_id" class="form-control @error('jenis_barang_id') border-red-500 @enderror" onchange="const opt = this.options[this.selectedIndex]; fetchNextCode('barang', 'barang_cd', {jenis: opt.dataset.cd || ''})" required>
                    <option value="">-- Pilih Jenis Barang --</option>
                    @foreach($jenisBarangList as $jenis)
                        <option value="{{ $jenis->jenis_barang_id }}" data-cd="{{ $jenis->jenis_barang_cd }}" {{ old('jenis_barang_id') == $jenis->jenis_barang_id ? 'selected' : '' }}>
                            {{ $jenis->jenis_barang_nm }} ({{ $jenis->jenis_barang_cd }})
                        </option>
                    @endforeach
                </select>
                @error('jenis_barang_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="satuan_dasar_id" class="form-label">Satuan Dasar (Terkecil) <span style="color:#ef4444;">*</span></label>
                    <select id="satuan_dasar_id" name="satuan_dasar_id" class="form-control @error('satuan_dasar_id') border-red-500 @enderror" required>
                        <option value="">-- Pilih Satuan Dasar --</option>
                        @foreach($satuanList as $satuan)
                            <option value="{{ $satuan->satuan_id }}" {{ old('satuan_dasar_id') == $satuan->satuan_id ? 'selected' : '' }}>
                                {{ $satuan->satuan_nm }} ({{ $satuan->satuan_cd }})
                            </option>
                        @endforeach
                    </select>
                    @error('satuan_dasar_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="satuan_besar_id" class="form-label">Satuan Besar (Kemasan/Beli)</label>
                    <select id="satuan_besar_id" name="satuan_besar_id" class="form-control @error('satuan_besar_id') border-red-500 @enderror">
                        <option value="">-- Tidak Ada / Sama --</option>
                        @foreach($satuanList as $satuan)
                            <option value="{{ $satuan->satuan_id }}" {{ old('satuan_besar_id') == $satuan->satuan_id ? 'selected' : '' }}>
                                {{ $satuan->satuan_nm }} ({{ $satuan->satuan_cd }})
                            </option>
                        @endforeach
                    </select>
                    @error('satuan_besar_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="konversi_qty" class="form-label">Nilai Konversi Qty <span style="color:#ef4444;">*</span></label>
                <input type="number" step="0.0001" min="1" id="konversi_qty" name="konversi_qty" value="{{ old('konversi_qty', '1.0000') }}" class="form-control @error('konversi_qty') border-red-500 @enderror" required>
                <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">
                    Berapa banyak satuan dasar dalam 1 satuan besar? Contoh: 1 Sak = 50.0000 KG. Jika tidak ada satuan besar, isi 1.0000.
                </span>
                @error('konversi_qty')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('master.barang.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>
@endsection
