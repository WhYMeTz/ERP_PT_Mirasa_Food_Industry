{{-- 
  Modal: Beri Nomor Batch Produksi IFM (Saat Jadwal Keberangkatan Truk Tiba)
  ERP PT Mirasa Food Industry
--}}

<div id="modalAssignBatch" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 10px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #cbd5e1; animation: modalFadeIn 0.2s ease-out;">
        {{-- Header Modal --}}
        <div style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    ⚡
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;">Beri Nomor Batch Keberangkatan</h3>
                    <span id="assignBatchDocNo" style="font-size: 0.75rem; color: #64748b; font-family: monospace;">-</span>
                </div>
            </div>
            <button type="button" onclick="closeModalAssignBatch()" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer; padding: 0.25rem; line-height: 1;">&times;</button>
        </div>

        {{-- Form Assign Batch --}}
        <form id="formAssignBatch" method="POST" action="">
            @csrf
            <div style="padding: 1.25rem;">
                <p style="font-size: 0.8rem; color: #64748b; margin-top: 0; margin-bottom: 1rem; line-height: 1.45;">
                    Barang ini sebelumnya disimpan sebagai <strong>Buffer Stock</strong>. Masukkan tanggal keberangkatan truk dan nomor karton untuk menerbitkan nomor batch resmi Indofood (IFM).
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    {{-- 1. Tanggal Keberangkatan --}}
                    <div>
                        <label for="assign_tgl_keberangkatan" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Keberangkatan <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="tgl_keberangkatan" id="assign_tgl_keberangkatan" class="form-control" value="{{ date('Y-m-d') }}" required onchange="calculateAssignBatchPreview()" style="font-weight: 600;">
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Hari H truk berangkat.</small>
                    </div>

                    {{-- 2. Shift Operasional --}}
                    <div>
                        <label for="assign_shift_cd" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Shift Karton <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="shift_cd" id="assign_shift_cd" class="form-control" onchange="calculateAssignBatchPreview()" style="font-weight: 700;">
                            <option value="A">Shift A</option>
                            <option value="B">Shift B</option>
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Kode awalan nomor karton.</small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    {{-- 3. Qty Karton (Readonly) --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Jumlah Karton Box
                        </label>
                        <input type="text" id="assign_qty_karton_display" class="form-control" readonly style="background: #f8fafc; font-weight: 800; color: #0284c7;">
                        <input type="hidden" name="qty_karton" id="assign_qty_karton" value="0">
                    </div>

                    {{-- 4. No. Karton Awal --}}
                    <div>
                        <label for="assign_no_karton_awal" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            No. Karton Awal <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" step="1" min="1" name="no_karton_awal" id="assign_no_karton_awal" class="form-control" value="1" required oninput="calculateAssignBatchPreview()" style="font-family: monospace; font-weight: 700;">
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Urutan nomor karton awal.</small>
                    </div>
                </div>

                {{-- Preview Live Nomor Batch yang Terbentuk --}}
                <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.725rem; font-weight: 700; color: #166534; text-transform: uppercase;">Hasil Kode Batch Resmi:</span>
                        <span id="assignBadgeRentang" style="font-size: 0.7rem; font-weight: 700; background: #dcfce7; color: #15803d; border: 1px solid #86efac; padding: 0.1rem 0.45rem; border-radius: 4px;">-</span>
                    </div>
                    <div id="assignLiveBatchText" style="font-family: monospace; font-size: 1.15rem; font-weight: 800; color: #166534; letter-spacing: 0.04em;">
                        -
                    </div>
                    <div style="margin-top: 0.35rem; font-size: 0.725rem; color: #15803d;">
                        Exp Date: <strong id="assignLiveExpDate">-</strong> (Otomatis +6 bulan dari tanggal keberangkatan)
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div style="background: #f8fafc; border-top: 1.5px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeModalAssignBatch()" class="btn btn-secondary" style="font-weight: 600; font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitAssignBatch" class="btn btn-primary" style="background: #0284c7; border-color: #0284c7; font-weight: 700; font-size: 0.825rem; padding: 0.45rem 1.15rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Nomor Batch</span>
                </button>
            </div>
        </form>
    </div>
</div>

