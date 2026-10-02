{{-- MODAL TAMBAH ROLE BARU --}}
<div id="modalTambahRole" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 520px;">
        <div class="modal-header">
            <h2 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Peran (Role) Baru</span>
            </h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahRole')">&times;</button>
        </div>
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_role_code" class="form-label">Kode Peran (Role Code) <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_role_code" name="role_cd" class="form-control" placeholder="Contoh: SALES, OPERATOR_LAB" style="text-transform: uppercase;" required oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '_')">
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Hanya huruf kapital, angka, dan underscore (_). Digunakan sebagai identifier sistem.</small>
                </div>

                <div class="form-group">
                    <label for="create_role_name" class="form-label">Nama Peran Lengkap <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_role_name" name="role_nm" class="form-control" placeholder="Contoh: Tim Sales & Marketing" required>
                </div>

                <div class="form-group">
                    <label for="create_role_desc" class="form-label">Deskripsi &amp; Tanggung Jawab</label>
                    <textarea id="create_role_desc" name="desc_txt" class="form-control" rows="2" placeholder="Contoh: Bertanggung jawab atas penjualan dan pemesanan pelanggan."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_copy_from" class="form-label">Salin Hak Akses Awal Dari</label>
                    <select id="create_copy_from" name="copy_from_role" class="form-control">
                        <option value="">-- Mulai dari Kosong (Semua Hak Akses Nonaktif) --</option>
                        @foreach ($allRoles as $r)
                            @if ($r->role_cd !== 'SUPERADMIN')
                                <option value="{{ $r->role_cd }}">Salin izin dari {{ $r->role_nm }} ({{ $r->role_cd }})</option>
                            @endif
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Mempercepat konfigurasi dengan menyalin centang izin dari peran yang sudah ada.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahRole')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Peran Baru</button>
            </div>
        </form>
    </div>
</div>
