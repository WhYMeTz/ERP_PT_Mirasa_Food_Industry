@extends('layouts.app')

@section('title', 'Master Data Barang - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Barang</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data katalog bahan baku, barang dalam proses, dan produk jadi.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('master.barang.export.pdf') }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #e11d48; color: #e11d48; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem;" title="Buka & Cetak Katalog Dokumen PDF Resmi">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>📄 Cetak PDF</span>
        </a>
        <a href="{{ route('master.barang.export') }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-size: 0.85rem; font-weight: 600; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            📊 Eksport Excel
        </a>
        @if (Auth::user()?->canManageMasterData())
            <button type="button" onclick="openModal('modalImportBarang')" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #0284c7; color: #0284c7; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                📥 Import Excel
            </button>
            <button type="button" onclick="openModal('modalTambahBarang')" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.55rem 1rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Barang Baru
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>⚠️ {{ session('error') }}</span>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.barang.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama barang..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.barang.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $barangs->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Satuan Dasar</th>
                    <th style="text-align: right;">Batas Minimum</th>
                    <th style="text-align: right;">Harga Standar</th>
                    <th style="width: 130px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $index => $item)
                    <tr>
                        <td>{{ $barangs->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7; font-family: monospace;">{{ $item->barang_cd }}</strong></td>
                        <td style="font-weight: 600; color: #0f172a;">{{ $item->barang_nm }}</td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                {{ $item->jenisBarang->jenis_barang_nm ?? ($item->jenisBarang->jenis_barang_cd ?? '-') }}
                            </span>
                        </td>
                        <td>{{ $item->satuanDasar->satuan_nm ?? '-' }} ({{ $item->satuanDasar->satuan_cd ?? '-' }})</td>
                        <td style="text-align: right; font-weight: 600; color: {{ (float) $item->batas_minimum_qty > 0 ? '#b45309' : '#94a3b8' }};">
                            {{ number_format((float) $item->batas_minimum_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #0f172a;">
                            Rp {{ number_format((float) $item->harga_beli_standar, 2, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            @if (Auth::user()?->canManageMasterData())
                                <div style="display: inline-flex; gap: 0.35rem;">
                                    <button type="button" 
                                        class="btn btn-secondary btn-sm" 
                                        onclick="editBarang(
                                            {{ $item->barang_id }},
                                            '{{ addslashes($item->barang_cd) }}',
                                            '{{ addslashes($item->barang_nm) }}',
                                            {{ $item->jenis_barang_id }},
                                            {{ $item->satuan_dasar_id }},
                                            '{{ $item->satuan_besar_id ?? '' }}',
                                            '{{ number_format($item->konversi_qty, 4, '.', '') }}',
                                            '{{ number_format($item->batas_minimum_qty ?? 0, 4, '.', '') }}',
                                            '{{ number_format($item->harga_beli_standar ?? 0, 2, '.', '') }}'
                                        )"
                                        title="Edit">
                                        Edit
                                    </button>
                                    <form action="{{ route('master.barang.destroy', $item->barang_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan barang ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span style="color: #94a3b8; font-size: 0.8rem;">Lihat Saja</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data master barang. Klik tombol <strong>"Tambah Barang Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($barangs->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $barangs->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH BARANG --}}
<div id="modalTambahBarang" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Barang Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahBarang')">&times;</button>
        </div>
        <form action="{{ route('master.barang.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                        <label for="create_barang_cd" class="form-label" style="margin-bottom: 0;">Kode Barang <span style="color:#ef4444;">*</span></label>
                        <button type="button" class="btn btn-secondary btn-sm" data-target="create_barang_cd" onclick="const sel = document.getElementById('create_jenis_barang_id'); const opt = sel.options[sel.selectedIndex]; fetchNextCode('barang', 'create_barang_cd', {jenis: opt?.dataset?.cd || '', name: document.getElementById('create_barang_nm')?.value || ''})" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;" title="Generate Ulang Nomor Urut Otomatis">
                            ↺ Auto Generate
                        </button>
                    </div>
                    <input type="text" id="create_barang_cd" name="barang_cd" value="{{ $nextBarangCode ?? '' }}" class="form-control" placeholder="Contoh: BBL00G-BP1 atau BB-SK001" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Kode otomatis terisi nomor urut rumpun berikutnya (Bumbu, Karton, Lakban, dll), dan tetap bisa diubah manual.</small>
                </div>

                <div class="form-group">
                    <label for="create_barang_nm" class="form-label">Nama Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_barang_nm" name="barang_nm" class="form-control" placeholder="Contoh: Bumbu Jagung Bakar" oninput="const opt = document.getElementById('create_jenis_barang_id').options[document.getElementById('create_jenis_barang_id').selectedIndex]; debounceCodeFromName('barang', 'create_barang_nm', 'create_barang_cd', {jenis: opt?.dataset?.cd || ''})" required>
                </div>

                <div class="form-group">
                    <label for="create_jenis_barang_id" class="form-label">Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <select id="create_jenis_barang_id" name="jenis_barang_id" class="form-control" onchange="const opt = this.options[this.selectedIndex]; fetchNextCode('barang', 'create_barang_cd', {jenis: opt.dataset.cd || '', name: document.getElementById('create_barang_nm')?.value || ''})" required>
                        <option value="">-- Pilih Jenis Barang --</option>
                        @foreach($jenisBarangList as $jenis)
                            <option value="{{ $jenis->jenis_barang_id }}" data-cd="{{ $jenis->jenis_barang_cd }}">{{ $jenis->jenis_barang_nm }} ({{ $jenis->jenis_barang_cd }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_satuan_dasar_id" class="form-label">Satuan Dasar (Kecil) <span style="color:#ef4444;">*</span></label>
                        <select id="create_satuan_dasar_id" name="satuan_dasar_id" class="form-control" required>
                            <option value="">-- Pilih Satuan Dasar --</option>
                            @foreach($satuanList as $satuan)
                                <option value="{{ $satuan->satuan_id }}">{{ $satuan->satuan_nm }} ({{ $satuan->satuan_cd }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_satuan_besar_id" class="form-label">Satuan Besar (Kemasan)</label>
                        <select id="create_satuan_besar_id" name="satuan_besar_id" class="form-control">
                            <option value="">-- Tidak Ada / Sama --</option>
                            @foreach($satuanList as $satuan)
                                <option value="{{ $satuan->satuan_id }}">{{ $satuan->satuan_nm }} ({{ $satuan->satuan_cd }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_batas_minimum_qty" class="form-label">Batas Minimum (Safety Stock)</label>
                        <input type="number" step="0.0001" min="0" id="create_batas_minimum_qty" name="batas_minimum_qty" value="0.0000" class="form-control">
                        <small style="color: #64748b; font-size: 0.725rem;">Peringatan jika stok fisik berada di bawah batas ini.</small>
                    </div>

                    <div class="form-group">
                        <label for="create_harga_beli_standar" class="form-label">Harga Beli Standar (Rp)</label>
                        <input type="number" step="0.01" min="0" id="create_harga_beli_standar" name="harga_beli_standar" value="0" class="form-control">
                        <small style="color: #64748b; font-size: 0.725rem;">Otomatis mengisi harga pada form PO / Penerimaan.</small>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_konversi_qty" class="form-label">Nilai Konversi Qty <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.0001" min="1" id="create_konversi_qty" name="konversi_qty" value="1.0000" class="form-control" required>
                    <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">Contoh: 1 Sak = 50.0000 KG. Jika tidak ada kemasan besar, isi 1.0000.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahBarang')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT BARANG --}}
<div id="modalEditBarang" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Barang</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditBarang')">&times;</button>
        </div>
        <form id="formEditBarang" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="barang_id" id="edit_barang_id">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_barang_cd" class="form-label">Kode Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_barang_cd" name="barang_cd" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_barang_nm" class="form-label">Nama Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_barang_nm" name="barang_nm" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_jenis_barang_id" class="form-label">Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <select id="edit_jenis_barang_id" name="jenis_barang_id" class="form-control" required>
                        <option value="">-- Pilih Jenis Barang --</option>
                        @foreach($jenisBarangList as $jenis)
                            <option value="{{ $jenis->jenis_barang_id }}">{{ $jenis->jenis_barang_nm }} ({{ $jenis->jenis_barang_cd }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_satuan_dasar_id" class="form-label">Satuan Dasar (Kecil) <span style="color:#ef4444;">*</span></label>
                        <select id="edit_satuan_dasar_id" name="satuan_dasar_id" class="form-control" required>
                            <option value="">-- Pilih Satuan Dasar --</option>
                            @foreach($satuanList as $satuan)
                                <option value="{{ $satuan->satuan_id }}">{{ $satuan->satuan_nm }} ({{ $satuan->satuan_cd }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_satuan_besar_id" class="form-label">Satuan Besar (Kemasan)</label>
                        <select id="edit_satuan_besar_id" name="satuan_besar_id" class="form-control">
                            <option value="">-- Tidak Ada / Sama --</option>
                            @foreach($satuanList as $satuan)
                                <option value="{{ $satuan->satuan_id }}">{{ $satuan->satuan_nm }} ({{ $satuan->satuan_cd }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_batas_minimum_qty" class="form-label">Batas Minimum (Safety Stock)</label>
                        <input type="number" step="0.0001" min="0" id="edit_batas_minimum_qty" name="batas_minimum_qty" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_harga_beli_standar" class="form-label">Harga Beli Standar (Rp)</label>
                        <input type="number" step="0.01" min="0" id="edit_harga_beli_standar" name="harga_beli_standar" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_konversi_qty" class="form-label">Nilai Konversi Qty <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.0001" min="1" id="edit_konversi_qty" name="konversi_qty" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditBarang')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IMPORT MASTER BARANG DARI EXCEL --}}
<div id="modalImportBarang" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 540px;">
        <div class="modal-header">
            <h3 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <span>📥 Impor Master Barang dari Excel</span>
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('modalImportBarang')">&times;</button>
        </div>
        <form action="{{ route('master.barang.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                    <strong style="color: #0369a1; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">
                        💡 Petunjuk Pengisian File:
                    </strong>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem; color: #334155; line-height: 1.5;">
                        <li>Gunakan template resmi sistem agar format kolom terbaca akurat.</li>
                        <li><strong>Kode Barang</strong> boleh dikosongkan (sistem akan otomatis membuat kode).</li>
                        <li>Jika Kode Barang sudah terdaftar, data akan <strong>diperbarui otomatis (UPSERT)</strong>.</li>
                        <li>Format file yang didukung: <strong>.xlsx, .xls, .csv</strong> (maks. 10MB).</li>
                    </ul>
                    <div style="margin-top: 0.75rem; padding-top: 0.5rem; border-top: 1px dashed #7dd3fc;">
                        <a href="{{ route('master.barang.template') }}" class="btn btn-secondary btn-sm" style="background: #ffffff; border: 1px solid #0284c7; color: #0284c7; font-weight: 700; text-decoration: none;">
                            📥 Unduh Template Excel (.xlsx)
                        </a>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="excel_file" class="form-label" style="font-weight: 700;">
                        Pilih File Excel / CSV <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" class="form-control" style="padding: 0.5rem;" required>
                    <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        Pastikan data berada di sheet pertama dan dimulai dari baris ke-4 sesuai template.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportBarang')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    🚀 Mulai Impor Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editBarang(id, kode, nama, jenisId, satuanDasarId, satuanBesarId, konversi, batasMin, hargaStandar) {
        document.getElementById('edit_barang_cd').value = kode;
        document.getElementById('edit_barang_nm').value = nama;
        document.getElementById('edit_jenis_barang_id').value = jenisId;
        document.getElementById('edit_satuan_dasar_id').value = satuanDasarId;
        document.getElementById('edit_satuan_besar_id').value = satuanBesarId || '';
        document.getElementById('edit_konversi_qty').value = konversi;
        document.getElementById('edit_batas_minimum_qty').value = batasMin || 0;
        document.getElementById('edit_harga_beli_standar').value = hargaStandar || 0;
        document.getElementById('edit_barang_id').value = id;
        document.getElementById('formEditBarang').action = '{{ url("master-barang") }}/' + id;
        openModal('modalEditBarang');
    }
</script>
@endsection
