@extends('layouts.app')

@section('title', 'Master Data Barang - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Barang</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data katalog bahan baku, barang dalam proses, dan produk jadi.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahBarang')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Barang Baru
        </button>
    </div>
</div>

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
                    <th style="width: 60px;">No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jenis Barang</th>
                    <th>Satuan Dasar</th>
                    <th>Satuan Besar</th>
                    <th>Konversi Qty</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $index => $item)
                    <tr>
                        <td>{{ $barangs->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7;">{{ $item->barang_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->barang_nm }}</td>
                        <td>
                            <span class="badge badge-info">{{ $item->jenisBarang->jenis_barang_nm ?? '-' }}</span>
                        </td>
                        <td>{{ $item->satuanDasar->satuan_nm ?? '-' }} ({{ $item->satuanDasar->satuan_cd ?? '-' }})</td>
                        <td>
                            @if($item->satuanBesar)
                                {{ $item->satuanBesar->satuan_nm }} ({{ $item->satuanBesar->satuan_cd }})
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->satuanBesar)
                                1 {{ $item->satuanBesar->satuan_cd }} = {{ number_format($item->konversi_qty, 2) }} {{ $item->satuanDasar->satuan_cd }}
                            @else
                                1.00 {{ $item->satuanDasar->satuan_cd ?? '' }}
                            @endif
                        </td>
                        <td style="text-align: right;">
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
                                        '{{ number_format($item->konversi_qty, 4, '.', '') }}'
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
                        <button type="button" class="btn btn-secondary btn-sm" data-target="create_barang_cd" onclick="const sel = document.getElementById('create_jenis_barang_id'); const opt = sel.options[sel.selectedIndex]; fetchNextCode('barang', 'create_barang_cd', {jenis: opt?.dataset?.cd || ''})" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;" title="Generate Ulang Nomor Urut Otomatis">
                            ↺ Auto Generate
                        </button>
                    </div>
                    <input type="text" id="create_barang_cd" name="barang_cd" value="{{ $nextBarangCode ?? '' }}" class="form-control" placeholder="Contoh: BRG-RAW-0001" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Kode otomatis terisi nomor urut berikutnya, namun tetap bisa Anda ubah manual.</small>
                </div>

                <div class="form-group">
                    <label for="create_barang_nm" class="form-label">Nama Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_barang_nm" name="barang_nm" class="form-control" placeholder="Contoh: Singkong Manis Grade A" required>
                </div>

                <div class="form-group">
                    <label for="create_jenis_barang_id" class="form-label">Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <select id="create_jenis_barang_id" name="jenis_barang_id" class="form-control" onchange="const opt = this.options[this.selectedIndex]; fetchNextCode('barang', 'create_barang_cd', {jenis: opt.dataset.cd || ''})" required>
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

<script>
    function editBarang(id, kode, nama, jenisId, satuanDasarId, satuanBesarId, konversi) {
        document.getElementById('edit_barang_cd').value = kode;
        document.getElementById('edit_barang_nm').value = nama;
        document.getElementById('edit_jenis_barang_id').value = jenisId;
        document.getElementById('edit_satuan_dasar_id').value = satuanDasarId;
        document.getElementById('edit_satuan_besar_id').value = satuanBesarId || '';
        document.getElementById('edit_konversi_qty').value = konversi;
        document.getElementById('formEditBarang').action = '{{ url("master-barang") }}/' + id;
        openModal('modalEditBarang');
    }
</script>
@endsection
