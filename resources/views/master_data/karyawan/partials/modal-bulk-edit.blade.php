{{-- MODAL EDIT MASSAL KARYAWAN: ubah satu kolom untuk beberapa karyawan yang dicentang --}}
<div id="modalBulkEdit" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 520px;">
        <div class="modal-header">
            <h2 class="modal-title">Edit Massal Karyawan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalBulkEdit')">&times;</button>
        </div>
        <form id="formBulkEdit" method="POST" action="{{ route('master.karyawan.bulk-update') }}">
            @csrf
            <div class="modal-body">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.7rem .9rem;margin-bottom:1rem;">
                    <div style="font-size:.8rem;color:#64748b;margin-bottom:.35rem;">
                        Akan diubah untuk <strong id="bulkModalCount">0</strong> karyawan:
                    </div>
                    <div id="bulkModalNames" style="font-size:.85rem;color:#0f172a;max-height:110px;overflow-y:auto;line-height:1.6;"></div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="bulk_field" class="form-label">Kolom yang diubah</label>
                    <select id="bulk_field" name="field" class="form-control" required>
                        <option value="status_kerja">Status Karyawan</option>
                        <option value="shift">Shift</option>
                        <option value="kelompok">Kelompok</option>
                        <option value="departemen_cd">Departemen</option>
                        <option value="jabatan_nm">Divisi / Bagian</option>
                        <option value="bank_name">Bank</option>
                        <option value="status_ptkp">Status PTKP</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nilai baru</label>

                    <select class="form-control bulk-value" data-field="status_kerja" name="value">
                        <option value="Tetap">Tetap</option>
                        <option value="Kontrak">Kontrak</option>
                        <option value="Harian">Harian</option>
                        <option value="Borong">Borong</option>
                    </select>

                    <select class="form-control bulk-value" data-field="shift" name="value">
                        <option value="SHIFT A">Shift A</option>
                        <option value="SHIFT B">Shift B</option>
                        <option value="NON SHIFT">Non Shift</option>
                    </select>

                    <select class="form-control bulk-value" data-field="kelompok" name="value">
                        @foreach (\App\Models\MasterData\MstKaryawan::KELOMPOK as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select class="form-control bulk-value" data-field="departemen_cd" name="value">
                        @foreach ($departemenList as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select class="form-control bulk-value" data-field="jabatan_nm" name="value">
                        @foreach (\App\Models\MasterData\MstKaryawan::DIVISI as $divisi)
                            <option value="{{ $divisi }}">{{ $divisi }}</option>
                        @endforeach
                    </select>

                    <select class="form-control bulk-value" data-field="status_ptkp" name="value">
                        @foreach (\App\Models\MasterData\MstKaryawan::PTKP as $ptkp)
                            <option value="{{ $ptkp }}">{{ $ptkp }}</option>
                        @endforeach
                    </select>

                    <input type="text" class="form-control bulk-value" data-field="bank_name" name="value" maxlength="50" placeholder="Contoh: BCA">
                </div>

                <p style="font-size:.78rem;color:#64748b;margin:.75rem 0 0;">
                    Hanya kolom yang dipilih yang berubah. Kolom lain pada karyawan tersebut tidak disentuh.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalBulkEdit')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const bar      = document.getElementById('bulkBar');
        const checkAll = document.getElementById('checkAll');
        const form     = document.getElementById('formBulkEdit');
        const fieldSel = document.getElementById('bulk_field');

        const rows = () => Array.from(document.querySelectorAll('.row-check'));
        const dipilih = () => rows().filter(c => c.checked);

        function segarkan() {
            const n = dipilih().length;
            document.getElementById('bulkCount').textContent = n;
            bar.classList.toggle('on', n > 0);
            rows().forEach(c => c.closest('tr').classList.toggle('row-selected', c.checked));
            if (checkAll) {
                checkAll.checked = n > 0 && n === rows().length;
                checkAll.indeterminate = n > 0 && n < rows().length;
            }
        }

        // Hanya kontrol "Nilai baru" untuk kolom terpilih yang tampil dan ikut terkirim
        function tampilkanNilai() {
            document.querySelectorAll('.bulk-value').forEach(el => {
                const aktif = el.dataset.field === fieldSel.value;
                el.style.display = aktif ? '' : 'none';
                el.disabled = !aktif;
            });
        }

        rows().forEach(c => c.addEventListener('change', segarkan));
        if (checkAll) {
            checkAll.addEventListener('change', () => {
                rows().forEach(c => c.checked = checkAll.checked);
                segarkan();
            });
        }
        fieldSel.addEventListener('change', tampilkanNilai);

        window.batalPilih = function () {
            rows().forEach(c => c.checked = false);
            segarkan();
        };

        window.bukaEditMassal = function () {
            const terpilih = dipilih();
            if (!terpilih.length) return;
            document.getElementById('bulkModalCount').textContent = terpilih.length;
            document.getElementById('bulkModalNames').textContent = terpilih.map(c => c.dataset.nama).join(', ');
            tampilkanNilai();
            openModal('modalBulkEdit');
        };

        // Sisipkan id karyawan terpilih saat form dikirim
        form.addEventListener('submit', function () {
            form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
            dipilih().forEach(c => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = 'ids[]'; i.value = c.value;
                form.appendChild(i);
            });
        });

        segarkan();
    })();
</script>
