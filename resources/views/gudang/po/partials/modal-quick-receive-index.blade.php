{{-- MODAL CEPAT CATAT BARANG MASUK (IN-PLACE QUICK RECEIVE DI INDEX) --}}
<div id="modalQuickReceiveIndex" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 10px; width: 100%; max-width: 780px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="color: #0f172a; font-size: 1.05rem;" id="modalPoTitle">Catat Penerimaan Barang</strong>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;" id="modalPoSubtitle">
                    -
                </div>
            </div>
            <button type="button" onclick="closeQuickReceiveIndexModal()" style="background: transparent; border: none; font-size: 1.35rem; color: #64748b; cursor: pointer; padding: 0 0.25rem;">&times;</button>
        </div>

        <form action="{{ route('gudang.terima.store') }}" method="POST" style="padding: 1.25rem; overflow-y: auto; flex: 1;" id="formQuickReceiveIndex">
            @csrf
            <input type="hidden" name="po_id" id="quick_po_id">
            <input type="hidden" name="supplier_id" id="quick_supplier_id">
            <input type="hidden" name="gudang_id" id="quick_gudang_id">
            <input type="hidden" name="redirect_to" value="po_index">

            <div style="margin-bottom: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="quick_terima_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal Masuk <span style="color:#ef4444;">*</span></label>
                    <input type="date" id="quick_terima_tgl" name="terima_tgl" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 0;">
                        Kuantitas Tiba Hari Ini:
                    </label>
                    <div style="display: flex; gap: 0.35rem;">
                        <button type="button" onclick="fillAllQuickSisa()" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            Terima Semua Sisa
                        </button>
                        <button type="button" onclick="clearAllQuickInputs()" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                            Kosongkan (0)
                        </button>
                    </div>
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead style="background: #f8fafc; font-size: 0.8rem; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 0.6rem 0.75rem; text-align: left;">Nama Barang</th>
                                <th style="padding: 0.6rem 0.75rem; text-align: right; width: 80px;">Sisa PO</th>
                                <th style="padding: 0.6rem 0.75rem; text-align: left; width: 175px;">No. Batch Supplier <span style="color:#ef4444;">*</span></th>
                                <th style="padding: 0.6rem 0.75rem; text-align: right; width: 120px;">Masuk Hari Ini</th>
                            </tr>
                        </thead>
                        <tbody id="quickReceiveItemsBody">
                            {{-- Diisi secara dinamis via JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.35rem; display: block;">
                    * Masukkan kuantitas yang benar-benar tiba saat ini. Jika sebagian, sisa kuota akan tetap disimpan untuk kedatangan berikutnya.
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="quick_catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Penerimaan (Opsional)</label>
                <input type="text" id="quick_catatan_txt" name="catatan_txt" placeholder="Contoh: Pengiriman termin 1, supir Pak Joko truk AB 8122 AA" class="form-control">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <a href="#" id="linkFullForm" style="color: #0284c7; text-decoration: none; font-size: 0.825rem;">
                    Formulir Lengkap (Batch Manual &amp; QC) &rarr;
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" onclick="closeQuickReceiveIndexModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background:#059669;">
                        Simpan Penerimaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
