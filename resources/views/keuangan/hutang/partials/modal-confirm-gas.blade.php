{{-- ========================================================
     MODAL: KONFIRMASI REKONSILIASI TAGIHAN GAS
     Lokasi: resources/views/keuangan/hutang/partials/modal-confirm-gas.blade.php
     ======================================================== --}}
<div id="modalConfirmGas" class="bkk-modal-backdrop" aria-hidden="true">
    <div class="gas-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalConfirmTitle">
        <div class="gas-modal-header">
            <div class="gas-modal-title-wrap">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="gas-modal-title" id="modalConfirmTitle">Konfirmasi Rekonsiliasi Tagihan Gas</h3>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeModalConfirmGas()" aria-label="Tutup dialog">&times;</button>
        </div>
        <div class="gas-modal-body">
            <p class="modal-notice-text">
                Periksa kembali rincian tagihan gas sebelum diterbitkan ke buku besar hutang usaha dan dijurnal secara otomatis:
            </p>

            <div class="gas-confirm-list">
                <div class="confirm-item">
                    <span class="confirm-label">Vendor Gas</span>
                    <strong class="confirm-val" id="modalConfSupplier">-</strong>
                </div>
                <div class="confirm-item">
                    <span class="confirm-label">Bulan Periode</span>
                    <strong class="confirm-val" id="modalConfPeriode">-</strong>
                </div>
                <div class="confirm-item">
                    <span class="confirm-label">Nomor Faktur / Invoice</span>
                    <strong class="confirm-val" id="modalConfFaktur">-</strong>
                </div>
                <div class="confirm-item">
                    <span class="confirm-label">Nilai Pemakaian Pabrik (Estimasi)</span>
                    <span class="confirm-val" id="modalConfAkrual">Rp 0</span>
                </div>
                <div class="confirm-item">
                    <span class="confirm-label">Nominal Tagihan Vendor (Sebelum PPN)</span>
                    <strong class="confirm-val" id="modalConfDpp">Rp 0</strong>
                </div>
                <div class="confirm-item">
                    <span class="confirm-label">Selisih Biaya</span>
                    <strong class="confirm-val" id="modalConfVarians">Rp 0</strong>
                </div>
                <div class="confirm-item">
                    <span class="confirm-label">Pajak PPN Masukan</span>
                    <span class="confirm-val" id="modalConfPpn">Rp 0</span>
                </div>
                <div class="confirm-item confirm-total-highlight">
                    <span class="confirm-label">Total Tagihan Hutang (Harus Dibayar)</span>
                    <span class="confirm-val" id="modalConfTotal">Rp 0</span>
                </div>
            </div>

            <p class="modal-footnote">
                Setelah dikonfirmasi, sistem akan mendebet beban akrual gas, menyesuaikan selisih beban, mendebet PPN masukan, serta mengkredit rekening Hutang Usaha Vendor Gas (COA 2100).
            </p>
        </div>
        <div class="gas-modal-footer">
            <button type="button" class="btn-dock-cancel" onclick="closeModalConfirmGas()">
                Periksa Kembali
            </button>
            <button type="button" class="btn-dock-submit" onclick="submitTagihanGas()">
                Konfirmasi &amp; Terbitkan Tagihan
            </button>
        </div>
    </div>
</div>
