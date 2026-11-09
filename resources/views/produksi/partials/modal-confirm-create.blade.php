{{-- 
  Modal Konfirmasi Penyimpanan Lembar Produksi Harian
  ERP PT Mirasa Food Industry (Standard Factory Floor Operational Verification)
--}}
<div id="modalConfirmCreate" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closeModalConfirmCreate()">
    <div class="modal-dialog-custom modal-confirm-enterprise">
        {{-- Header Dialog Enterprise --}}
        <div class="modal-confirm-header">
            <div>
                <h3 class="modal-confirm-title">
                    Konfirmasi Penyimpanan Lembar Produksi
                </h3>
                <div class="modal-confirm-meta">
                    <span class="meta-doc-badge" id="modalSummaryDocNo">-</span>
                    <span class="meta-separator">•</span>
                    <span id="modalSummaryTglShift">-</span>
                    <span class="meta-separator">•</span>
                    <span id="modalSummaryLini">-</span>
                </div>
            </div>
            <button type="button" onclick="closeModalConfirmCreate()" id="btnCloseModalX" class="btn-modal-close" title="Tutup">
                &times;
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="modal-confirm-body">
            {{-- KPI Summary Bar --}}
            <div class="modal-kpi-bar">
                <div class="kpi-cell">
                    <span class="kpi-label">Singkong Masuk (BPPB)</span>
                    <strong class="kpi-value" id="modalSummarySingkong">0,00 kg</strong>
                    <small class="kpi-sub" id="modalSummaryGudang">Gudang Bahan</small>
                </div>
                <div class="kpi-cell">
                    <span class="kpi-label">Total Output Hasil</span>
                    <strong class="kpi-value kpi-output" id="modalSummaryOutput">0,00 kg</strong>
                    <small class="kpi-sub" id="modalSummaryItemCount">0 Baris Olahan</small>
                </div>
                <div class="kpi-cell">
                    <span class="kpi-label">Rendemen Olahan</span>
                    <strong class="kpi-value kpi-rendemen" id="modalSummaryRendemen">0,00%</strong>
                    <small class="kpi-sub" id="modalSummaryRendemenStatus">Optimal (≥ 33%)</small>
                </div>
            </div>

            {{-- Tabel Rincian Barang Jadi & Sampingan --}}
            <div class="modal-table-wrap">
                <div class="modal-table-caption">
                    Rincian Barang Jadi &amp; Sampingan Hasil Penggorengan:
                </div>
                <div class="table-responsive-modal">
                    <table class="table-modal-items">
                        <thead>
                            <tr>
                                <th style="width: 36px; text-align: center;">No</th>
                                <th>Nama Barang Hasil</th>
                                <th style="width: 90px; text-align: right; white-space: nowrap;">Qty Hasil</th>
                                <th style="width: 75px; text-align: center; white-space: nowrap;">Satuan</th>
                                <th style="width: 115px; text-align: right; white-space: nowrap;">Total Berat</th>
                                <th style="width: 120px; text-align: center; white-space: nowrap;">No. Batch</th>
                            </tr>
                        </thead>
                        <tbody id="modalSummaryItemsTableBody">
                            {{-- Diisi secara dinamis oleh JS --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Catatan Verifikasi Subtil & Profesional --}}
            <p class="modal-confirm-note">
                Pastikan data timbangan fisik di atas telah sesuai sebelum dibukukan ke kartu stok persediaan.
            </p>
        </div>

        {{-- Footer Modal --}}
        <div class="modal-confirm-footer">
            <button type="button" onclick="closeModalConfirmCreate()" id="btnCancelConfirm" class="btn btn-secondary btn-modal-cancel">
                Batal
            </button>
            <button type="button" onclick="submitCreateForm()" id="btnSubmitConfirm" class="btn btn-primary btn-modal-submit">
                <span id="textSubmitConfirm">Simpan Dokumen</span>
            </button>
        </div>
    </div>
</div>


