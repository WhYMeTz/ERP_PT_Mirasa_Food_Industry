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
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Customer</button>
            </div>
        </form>
    </div>
</div>
