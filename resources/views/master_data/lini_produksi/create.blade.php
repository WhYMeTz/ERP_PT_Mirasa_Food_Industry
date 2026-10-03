@extends('layouts.app')

@section('title', 'Tambah Lini Produksi - ERP PT Mirasa')

@section('content')
<div style="max-width: 650px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('master.lini_produksi.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Lini Produksi
        </a>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Tambah Lini Produksi / Tujuan Baru</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Daftarkan unit lini kerja atau tujuan alokasi hasil produksi baru.
        </p>
    </div>

    <div class="card">
        <form action="{{ route('master.lini_produksi.store') }}" method="POST" style="padding: 1.5rem;">
            @csrf

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="lini_cd" class="form-label">Kode Lini <span style="color:#ef4444;">*</span></label>
                <input type="text" id="lini_cd" name="lini_cd" value="{{ old('lini_cd') }}" class="form-control" placeholder="Contoh: IFM, JUMBO20, BERKO" style="text-transform: uppercase;" required>
                @error('lini_cd')
                    <div class="form-error" style="color: #ef4444; font-size: 0.775rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="lini_nm" class="form-label">Nama Lini Produksi / Tujuan <span style="color:#ef4444;">*</span></label>
                <input type="text" id="lini_nm" name="lini_nm" value="{{ old('lini_nm') }}" class="form-control" placeholder="Contoh: PRODUKSI IFM, PRODUKSI BERKO" style="text-transform: uppercase;" required>
                @error('lini_nm')
                    <div class="form-error" style="color: #ef4444; font-size: 0.775rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="kategori_lini" class="form-label">Kategori Hasil Produksi <span style="color:#ef4444;">*</span></label>
                <select id="kategori_lini" name="kategori_lini" class="form-control" required>
                    <option value="FINISH GOOD (FG)" {{ old('kategori_lini') === 'FINISH GOOD (FG)' ? 'selected' : '' }}>
                        Finish Good (FG)
                    </option>
                    <option value="WORK IN PROGRESS (WIP)" {{ old('kategori_lini') === 'WORK IN PROGRESS (WIP)' ? 'selected' : '' }}>
                        Work In Progress (WIP)
                    </option>
                </select>
                @error('kategori_lini')
                    <div class="form-error" style="color: #ef4444; font-size: 0.775rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="tipe_batch" class="form-label">Format Penomoran Batch <span style="color:#ef4444;">*</span></label>
                <select id="tipe_batch" name="tipe_batch" class="form-control" required>
                    <option value="REGULER" {{ old('tipe_batch', 'REGULER') === 'REGULER' ? 'selected' : '' }}>
                        Reguler (Format Tanggal)
                    </option>
                    <option value="IFM" {{ old('tipe_batch') === 'IFM' ? 'selected' : '' }}>
                        IFM (Format Shift &amp; Karton Box)
                    </option>
                </select>
                @error('tipe_batch')
                    <div class="form-error" style="color: #ef4444; font-size: 0.775rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="keterangan" class="form-label">Keterangan</label>
                <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan') }}" class="form-control" placeholder="Keterangan operasional (opsional)">
                @error('keterangan')
                    <div class="form-error" style="color: #ef4444; font-size: 0.775rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('master.lini_produksi.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Lini Produksi</button>
            </div>
        </form>
    </div>
</div>
@endsection
