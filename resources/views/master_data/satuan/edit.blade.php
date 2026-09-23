@extends('layouts.app')

@section('title', 'Edit Satuan - ERP PT Mirasa')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('master.satuan.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Satuan
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Edit Data Satuan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Perbarui data unit satuan <strong>{{ $satuan->satuan_nm }}</strong>.</p>
    </div>

    <div class="card">
        <form action="{{ route('master.satuan.update', $satuan->satuan_id) }}" method="POST" style="padding: 1.5rem;">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="satuan_cd" class="form-label">Kode Satuan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="satuan_cd" name="satuan_cd" value="{{ old('satuan_cd', $satuan->satuan_cd) }}" class="form-control" style="text-transform: uppercase;" required>
                @error('satuan_cd')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="satuan_nm" class="form-label">Nama Satuan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="satuan_nm" name="satuan_nm" value="{{ old('satuan_nm', $satuan->satuan_nm) }}" class="form-control" required>
                @error('satuan_nm')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('master.satuan.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
