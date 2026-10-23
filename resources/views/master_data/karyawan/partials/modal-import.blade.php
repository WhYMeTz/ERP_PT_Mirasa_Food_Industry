{{-- MODAL IMPORT DATA KARYAWAN DARI EXCEL --}}
<div id="modalImportExcel" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 520px;">
        <div class="modal-header">
            <h2 class="modal-title">Import Data Karyawan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalImportExcel')">&times;</button>
        </div>
        <form action="{{ route('master.karyawan.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.8rem; color: #075985; line-height: 1.5;">
                    <strong>Belum punya format file?</strong> Unduh template yang sudah berisi petunjuk pengisian dan pilihan dropdown.
                    <div style="margin-top: 0.6rem;">
                        <a href="{{ route('master.karyawan.template-import') }}" class="btn btn-secondary" style="text-decoration: none; font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                            &#8681; Download Template Import
                        </a>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <label for="import_file_excel" class="form-label">File Excel <span style="color:#ef4444;">*</span></label>
                    <input type="file" id="import_file_excel" name="file_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <small style="color: #64748b; font-size: 0.75rem;">Format: .xlsx, .xls, atau .csv. Maksimal 5 MB.</small>
                </div>

                <ul style="margin: 0; padding-left: 1.1rem; font-size: 0.75rem; color: #64748b; line-height: 1.6;">
                    <li>ID Karyawan yang sudah terdaftar akan <strong>diperbarui</strong>, bukan dobel.</li>
                    <li>Baris tanpa ID Karyawan atau Nama akan dilewati.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalImportExcel')">Batal</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>