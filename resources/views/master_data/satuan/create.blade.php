@extends('layouts.app')

@section('title', 'Tambah Satuan Baru - ERP PT Mirasa')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('master.satuan.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Satuan
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Tambah Satuan Baru</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Masukkan data unit satuan ukuran untuk barang.</p>
    </div>

    <div class="card">
        <form action="{{ route('master.satuan.store') }}" method="POST" style="padding: 1.5rem;">
            @csrf

            <div class="form-group">
                <label for="satuan_cd" class="form-label">Kode Satuan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="satuan_cd" name="satuan_cd" value="{{ old('satuan_cd') }}" class="form-control" placeholder="Contoh: KG, PCS, SAK, DUS" style="text-transform: uppercase;" required>
                <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">Gunakan singkatan standar huruf besar.</span>
                @error('satuan_cd')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="satuan_nm" class="form-label">Nama Satuan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="satuan_nm" name="satuan_nm" value="{{ old('satuan_nm') }}" class="form-control" placeholder="Contoh: Kilogram, Sak 50Kg, Dus Karton" required>
                @error('satuan_nm')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('master.satuan.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Satuan</button>
            </div>
        </form>
    </div>
</div>
@endsection
