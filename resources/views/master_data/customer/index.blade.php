@extends('layouts.app')

@section('title', 'Master Data Customer - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Customer</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data klien B2B, mitra pembeli seperti PT Indofood, dan jaringan distributor.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahCustomer')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Customer Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.customer.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode, nama, atau kontak..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.customer.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $customers->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Kode Customer</th>
                    <th>Nama Customer</th>
                    <th>Kontak / No Telp</th>
                    <th>Alamat Lengkap</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $index => $item)
                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7;">{{ $item->customer_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->customer_nm }}</td>
                        <td>{{ $item->kontak_no ?? '-' }}</td>
                        <td>{{ $item->alamat_txt ?? '-' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editCustomer({{ $item->customer_id }}, '{{ addslashes($item->customer_cd) }}', '{{ addslashes($item->customer_nm) }}', '{{ addslashes($item->kontak_no ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.customer.destroy', $item->customer_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan customer ini?')">
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
                        <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data customer. Klik tombol <strong>"Tambah Customer Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $customers->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH CUSTOMER --}}
<div id="modalTambahCustomer" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Customer Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahCustomer')">&times;</button>
        </div>
        <form action="{{ route('master.customer.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                        <label for="create_customer_cd" class="form-label" style="margin-bottom: 0;">Kode Customer <span style="color:#ef4444;">*</span></label>
                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" class="btn btn-secondary btn-sm" data-target="create_customer_cd" onclick="fetchNextCode('customer', 'create_customer_cd', {name: document.getElementById('create_customer_nm').value})" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Buat kode dari singkatan nama">
                                ✨ Dari Singkatan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" data-target="create_customer_cd" onclick="fetchNextCode('customer', 'create_customer_cd')" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Reset ke nomor urut standar">
                                ↺ Reset
                            </button>
                        </div>
                    </div>
                    <input type="text" id="create_customer_cd" name="customer_cd" value="{{ $nextCustomerCode ?? '' }}" class="form-control" placeholder="Contoh: CUST-ICBP-01" style="text-transform: uppercase;" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Otomatis mengikuti singkatan nama (misal: Sumber Rezeki → CUST-SR-01) atau nomor urut.</small>
                </div>
                <div class="form-group">
                    <label for="create_customer_nm" class="form-label">Nama Perusahaan / Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_customer_nm" name="customer_nm" class="form-control" placeholder="Contoh: PT Indofood CBP Sukses Makmur Tbk" oninput="debounceCodeFromName('customer', 'create_customer_nm', 'create_customer_cd')" required>
                </div>
                <div class="form-group">
                    <label for="create_customer_kontak" class="form-label">Kontak PIC / No Telepon</label>
                    <input type="text" id="create_customer_kontak" name="kontak_no" class="form-control" placeholder="Contoh: 021-57958822 (Bpk. Hendra)">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_customer_alamat" class="form-label">Alamat Pengiriman</label>
                    <textarea id="create_customer_alamat" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Kawasan Industri Indofood, Blok B No. 4, Cikarang"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahCustomer')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Customer</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT CUSTOMER --}}
<div id="modalEditCustomer" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Customer</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditCustomer')">&times;</button>
        </div>
        <form id="formEditCustomer" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_customer_cd" class="form-label">Kode Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_customer_cd" name="customer_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="edit_customer_nm" class="form-label">Nama Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_customer_nm" name="customer_nm" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_customer_kontak" class="form-label">Kontak PIC / No Telepon</label>
                    <input type="text" id="edit_customer_kontak" name="kontak_no" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_customer_alamat" class="form-label">Alamat Pengiriman</label>
                    <textarea id="edit_customer_alamat" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditCustomer')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editCustomer(id, kode, nama, kontak, alamat) {
        document.getElementById('edit_customer_cd').value = kode;
        document.getElementById('edit_customer_nm').value = nama;
        document.getElementById('edit_customer_kontak').value = kontak || '';
        document.getElementById('edit_customer_alamat').value = alamat || '';
        document.getElementById('formEditCustomer').action = '{{ url("master-customer") }}/' + id;
        openModal('modalEditCustomer');
    }
</script>
@endsection
