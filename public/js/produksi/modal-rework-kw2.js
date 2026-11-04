/**
 * Modal Sortir Ulang / Rework KW 2 JavaScript Logic (Poin 23 & Poin 24)
 * Menghubungkan pemilihan batch stok, kalkulasi balance massa, dan deteksi kemasan dus.
 */

let availableBatchesData = [];

function openModalReworkKw2() {
    const modal = document.getElementById('modalReworkKw2');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Load batch options
    loadBatchesForRework();
}

function closeModalReworkKw2() {
    const modal = document.getElementById('modalReworkKw2');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Tutup modal jika klik di luar box
window.addEventListener('click', function(e) {
    const modal = document.getElementById('modalReworkKw2');
    if (e.target === modal) {
        closeModalReworkKw2();
    }
});

/**
 * Mengambil daftar stok batch yang tersedia via AJAX
 */
function loadBatchesForRework() {
    const gudangSelect = document.getElementById('reworkGudangId');
    const batchSelect = document.getElementById('reworkBatchSelect');
    if (!batchSelect) return;

    const gudangId = gudangSelect ? gudangSelect.value : '';
    batchSelect.innerHTML = '<option value="">-- Memuat Data Batch Tersedia... --</option>';

    const url = `/produksi/ajax-batches-rework?gudang_id=${encodeURIComponent(gudangId)}`;

    fetch(url)
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                availableBatchesData = res.data;
                batchSelect.innerHTML = '<option value="">-- Pilih Batch / Barang yang Disortir --</option>';

                if (availableBatchesData.length === 0) {
                    batchSelect.innerHTML = '<option value="">(Tidak ada batch WIP/FG bersisa di gudang ini)</option>';
                    return;
                }

                availableBatchesData.forEach((item, index) => {
                    const opt = document.createElement('option');
                    opt.value = index;
                    opt.textContent = `[${item.batch_no}] ${item.barang_nm} - Sisa: ${Number(item.sisa_qty).toLocaleString('id-ID', {minimumFractionDigits: 1})} ${item.satuan_nm}`;
                    batchSelect.appendChild(opt);
                });
            } else {
                batchSelect.innerHTML = '<option value="">Gagal memuat batch stok</option>';
            }
        })
        .catch(err => {
            console.error('Error fetching batches:', err);
            batchSelect.innerHTML = '<option value="">Gagal memuat batch stok (Koneksi bermasalah)</option>';
        });
}

/**
 * Event ketika pengguna memilih salah satu batch
 */
function onBatchSelected(selectEl) {
    const idx = selectEl.value;
    const infoBox = document.getElementById('reworkBatchInfo');
    const cartonSection = document.getElementById('reworkCartonSection');
    const hiddenBarangId = document.getElementById('reworkBarangId');
    const hiddenBatchNo = document.getElementById('reworkBatchNo');
    const satuanLabel = document.getElementById('reworkSatuanLabel');
    const qtyInput = document.getElementById('reworkQtyInput');

    if (idx === '' || !availableBatchesData[idx]) {
        if (infoBox) infoBox.style.display = 'none';
        if (cartonSection) cartonSection.style.display = 'none';
        if (hiddenBarangId) hiddenBarangId.value = '';
        if (hiddenBatchNo) hiddenBatchNo.value = '';
        return;
    }

    const item = availableBatchesData[idx];

    // Isi hidden input
    if (hiddenBarangId) hiddenBarangId.value = item.barang_id;
    if (hiddenBatchNo) hiddenBatchNo.value = item.batch_no;

    // Tampilkan info
    document.getElementById('infoBarangNm').textContent = item.barang_nm;
    document.getElementById('infoBarangCd').textContent = item.barang_cd;
    document.getElementById('infoSisaStok').textContent = Number(item.sisa_qty).toLocaleString('id-ID', {minimumFractionDigits: 2});
    document.getElementById('infoSatuan').textContent = item.satuan_nm;
    document.getElementById('infoHppSatuan').textContent = 'Rp ' + Number(item.harga_satuan).toLocaleString('id-ID');

    const badgeJenis = document.getElementById('infoBadgeJenis');
    if (badgeJenis) {
        badgeJenis.textContent = item.is_fg ? 'FINISH GOOD (FG)' : 'WIP OLAHAN';
        badgeJenis.style.background = item.is_fg ? '#ecfdf5' : '#f1f5f9';
        badgeJenis.style.color = item.is_fg ? '#047857' : '#0f172a';
    }

    if (satuanLabel) satuanLabel.textContent = item.satuan_nm;
    if (infoBox) infoBox.style.display = 'flex';

    // Jika barang FG atau dus (kemasan karton), munculkan section bongkar dus
    if (cartonSection) {
        if (item.is_fg || item.satuan_nm.toUpperCase().includes('DUS') || item.satuan_nm.toUpperCase().includes('KARTON')) {
            cartonSection.style.display = 'block';
            const dusInput = document.getElementById('reworkDusDibongkar');
            if (dusInput && qtyInput && Number(qtyInput.value) > 0) {
                dusInput.value = Math.floor(Number(qtyInput.value));
            }
        } else {
            cartonSection.style.display = 'none';
        }
    }

    calculateBalance();
}

/**
 * Kalkulasi balance massa input vs output
 */
function calculateBalance() {
    const qtyInput = parseFloat(document.getElementById('reworkQtyInput')?.value) || 0;
    const qtyKw2 = parseFloat(document.getElementById('reworkQtyKw2')?.value) || 0;
    const qtyLengket = parseFloat(document.getElementById('reworkQtyLengket')?.value) || 0;
    const qtyRemahan = parseFloat(document.getElementById('reworkQtyRemahan')?.value) || 0;

    const totalAllocated = qtyKw2 + qtyLengket + qtyRemahan;
    const allocatedEl = document.getElementById('totalAllocatedVal');
    const msgEl = document.getElementById('balanceCheckMsg');

    if (allocatedEl) {
        allocatedEl.textContent = totalAllocated.toFixed(2);
    }

    if (msgEl) {
        const diff = Math.abs(qtyInput - totalAllocated);
        if (qtyInput > 0 && diff > 0.05) {
            msgEl.innerHTML = `<span style="color: #b45309;">⚠️ Total alokasi (${totalAllocated.toFixed(2)} kg) berbeda ${diff.toFixed(2)} kg dengan input (${qtyInput.toFixed(2)} kg). Pastikan susut/afkir teralokasi akurat.</span>`;
        } else if (qtyInput > 0) {
            msgEl.innerHTML = `<span style="color: #15803d;">✅ Alokasi massa seimbang (${totalAllocated.toFixed(2)} kg).</span>`;
        } else {
            msgEl.innerHTML = `Total Alokasi: <span id="totalAllocatedVal">${totalAllocated.toFixed(2)}</span> kg`;
        }
    }
}

/**
 * Penanganan perubahan pilihan alasan QC
 */
function onAlasanQcChange(selectEl) {
    const catatan = document.getElementById('reworkCatatanTxt');
    if (!catatan) return;

    if (selectEl.value.includes('Poin 23')) {
        catatan.placeholder = 'Misal: Persentase lengket pada penggorengan shift B mencapai 18%, ditarik dari rilis IFM dan disortir ulang.';
    } else if (selectEl.value.includes('Poin 24')) {
        catatan.placeholder = 'Misal: Reject QC inspeksi buyer ekspor karena standar ketebalan, dibongkar kembali untuk dijadikan KW 2.';
    } else {
        catatan.placeholder = 'Catatan hasil inspeksi QC / keterangan pengerjaan...';
    }
}

// Validasi sebelum submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formReworkKw2');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const barangId = document.getElementById('reworkBarangId')?.value;
        const batchNo = document.getElementById('reworkBatchNo')?.value;
        const qtyRework = parseFloat(document.getElementById('reworkQtyInput')?.value) || 0;
        const qtyKw2 = parseFloat(document.getElementById('reworkQtyKw2')?.value) || 0;

        if (!barangId || !batchNo) {
            e.preventDefault();
            alert('Silakan pilih batch/barang asal yang akan disortir terlebih dahulu.');
            return;
        }

        if (qtyRework <= 0) {
            e.preventDefault();
            alert('Kuantitas yang disortir ulang harus lebih besar dari 0.');
            return;
        }

        if (qtyKw2 <= 0) {
            e.preventDefault();
            alert('Hasil sortir baik ke KS CURAH SORTIR (KW 2) harus lebih besar dari 0.');
            return;
        }

        const btn = document.getElementById('btnSubmitRework');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>Menyimpan &amp; Memproses Stok...</span>';
        }
    });
});

