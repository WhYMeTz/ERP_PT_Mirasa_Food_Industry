@extends('layouts.app')

@section('title', 'Edit Barang - ERP PT Mirasa')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('master.barang.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Barang
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Edit Data Barang</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Perbarui rincian katalog barang <strong>{{ $barang->barang_nm }}</strong>.</p>
    </div>

    <div class="card">
        <form action="{{ route('master.barang.update', $barang->barang_id) }}" method="POST" style="padding: 1.5rem;">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="barang_cd" class="form-label">Kode Barang <span style="color:#ef4444;">*</span></label>
                <input type="text" id="barang_cd" name="barang_cd" value="{{ old('barang_cd', $barang->barang_cd) }}" class="form-control @error('barang_cd') border-red-500 @enderror" required>
                @error('barang_cd')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="barang_nm" class="form-label">Nama Barang <span style="color:#ef4444;">*</span></label>
                <input type="text" id="barang_nm" name="barang_nm" value="{{ old('barang_nm', $barang->barang_nm) }}" class="form-control @error('barang_nm') border-red-500 @enderror" required>
                @error('barang_nm')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="jenis_barang_id" class="form-label">Jenis Barang <span style="color:#ef4444;">*</span></label>
                <select id="jenis_barang_id" name="jenis_barang_id" class="form-control @error('jenis_barang_id') border-red-500 @enderror" onchange="toggleKategoriOutputVisibility(this)" required>
                    <option value="">-- Pilih Jenis Barang --</option>
                    @foreach($jenisBarangList as $jenis)
                        <option value="{{ $jenis->jenis_barang_id }}" data-cd="{{ $jenis->jenis_barang_cd }}" {{ old('jenis_barang_id', $barang->jenis_barang_id) == $jenis->jenis_barang_id ? 'selected' : '' }}>
                            {{ $jenis->jenis_barang_nm }} ({{ $jenis->jenis_barang_cd }})
                        </option>
                    @endforeach
                </select>
                @error('jenis_barang_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" id="grp_kategori_output" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.85rem;">
                <label for="kategori_output_cd" class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <span style="font-weight: 700; color: #0f172a;">Kategori Output Produksi (Grup Hasil Olahan)</span>
                    <span style="font-size: 0.725rem; color: #64748b; font-weight: normal;">Khusus Olahan WIP / FG</span>
                </label>
                <select id="kategori_output_cd" name="kategori_output_cd" class="form-control @error('kategori_output_cd') border-red-500 @enderror">
                    <option value="">-- Bukan Output Produksi / Barang Umum --</option>
                    @foreach($kategoriOutputList as $kCd => $kNm)
                        <option value="{{ $kCd }}" {{ old('kategori_output_cd', $barang->kategori_output_cd) == $kCd ? 'selected' : '' }}>
                            {{ $kNm }}
                        </option>
                    @endforeach
                </select>
                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Menentukan grup dropdown saat input hasil penggorengan curah di modul Produksi Pabrik.
                </small>
                @error('kategori_output_cd')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="satuan_dasar_id" class="form-label">Satuan Dasar (Terkecil) <span style="color:#ef4444;">*</span></label>
                    <select id="satuan_dasar_id" name="satuan_dasar_id" class="form-control @error('satuan_dasar_id') border-red-500 @enderror" required>
                        <option value="">-- Pilih Satuan Dasar --</option>
                        @foreach($satuanList as $satuan)
                            <option value="{{ $satuan->satuan_id }}" {{ old('satuan_dasar_id', $barang->satuan_dasar_id) == $satuan->satuan_id ? 'selected' : '' }}>
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
                            <option value="{{ $satuan->satuan_id }}" {{ old('satuan_besar_id', $barang->satuan_besar_id) == $satuan->satuan_id ? 'selected' : '' }}>
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
                <input type="number" step="0.0001" min="1" id="konversi_qty" name="konversi_qty" value="{{ old('konversi_qty', number_format($barang->konversi_qty, 4, '.', '')) }}" class="form-control @error('konversi_qty') border-red-500 @enderror" required>
                <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">
                    Berapa banyak satuan dasar dalam 1 satuan besar? Contoh: 1 Sak = 50.0000 KG.
                </span>
                @error('konversi_qty')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('master.barang.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleKategoriOutputVisibility(selectElem, targetBoxId = 'grp_kategori_output', inputSelectId = 'kategori_output_cd') {
        if (!selectElem) return;
        const opt = selectElem.options[selectElem.selectedIndex];
        const cd = (opt?.dataset?.cd || opt?.textContent || '').toUpperCase();
        const isWipOrFg = cd.includes('WIP') || cd.includes('FG');
        const box = document.getElementById(targetBoxId);
        if (box) {
            box.style.display = isWipOrFg ? 'block' : 'none';
            if (!isWipOrFg) {
                const inputSelect = document.getElementById(inputSelectId);
                if (inputSelect) inputSelect.value = '';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleKategoriOutputVisibility(document.getElementById('jenis_barang_id'));
    });
</script>
@endpush
@endsection
