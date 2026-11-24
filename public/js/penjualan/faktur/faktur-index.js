/**
 * FAKTUR PENJUALAN - INDEX JAVASCRIPT
 * PT Mirasa Food Industry
 */

let activeDropdownMenu = null;

/**
 * Smart Action Dropdown dengan posisi fixed agar tidak terpotong overflow scroll tabel.
 * Standar wajib AGENTS.md ERP Mirasa.
 */
function toggleSmartActionDropdown(triggerBtn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Jika menu yang sama sedang terbuka, tutup
    if (activeDropdownMenu === menu) {
        menu.style.display = 'none';
        activeDropdownMenu = null;
        return;
    }

    // Tutup menu lain jika ada
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
    }

    // Hitung posisi trigger button terhadap viewport
    const rect = triggerBtn.getBoundingClientRect();
    const menuWidth = 190;
    
    // Tampilkan sementara untuk mendapatkan tinggi aktual
    menu.style.visibility = 'hidden';
    menu.style.display = 'block';
    const menuHeight = menu.offsetHeight || 120;
    menu.style.visibility = 'visible';

    let top = rect.bottom + 4;
    let left = rect.right - menuWidth;

    // Jika melebihi batas bawah layar, munculkan ke atas tombol
    if (top + menuHeight > window.innerHeight) {
        top = Math.max(10, rect.top - menuHeight - 4);
    }

    // Pastikan tidak keluar batas kiri layar
    if (left < 10) {
        left = 10;
    }

    menu.style.position = 'fixed';
    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
    menu.style.display = 'block';

    activeDropdownMenu = menu;
}

// Tutup dropdown jika klik di luar atau saat window discroll
document.addEventListener('click', function (e) {
    if (activeDropdownMenu && !activeDropdownMenu.contains(e.target)) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
});

window.addEventListener('scroll', function () {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
}, true);

/**
 * Modal Void Faktur Penjualan (Bahaya / Critical Action)
 */
function openVoidModal(fakturId, fakturNo) {
    const modal = document.getElementById('modalVoidFaktur');
    const form = document.getElementById('formVoidFaktur');
    const labelFakturNo = document.getElementById('voidFakturNoText');

    if (modal && form) {
        form.action = `/penjualan/faktur/${fakturId}/void`;
        if (labelFakturNo) {
            labelFakturNo.textContent = fakturNo;
        }
        modal.classList.add('active');
    }
}

function closeVoidModal() {
    const modal = document.getElementById('modalVoidFaktur');
    if (modal) {
        modal.classList.remove('active');
    }
}

/**
 * Modal Catat Pembayaran Faktur Penjualan (BKM Terpusat)
 */
let currentFakturSisaPiutang = 0;

function openFakturPaymentModal(fakturId, fakturNo, customerNm, totalTagihan, sisaPiutang) {
    const modal = document.getElementById('modalRecordFakturPayment');
    const form = document.getElementById('formRecordFakturPayment');
    const textFakturNo = document.getElementById('fkModalFakturNoText');
    const textCust = document.getElementById('fkModalCustomerNmText');
    const textTotal = document.getElementById('fkModalTotalTagihanText');
    const textSisa = document.getElementById('fkModalSisaPiutangText');
    const inputNominal = document.getElementById('fkModalNominalInput');
    const inputPenyetor = document.getElementById('fkModalPenyetorInput');
    const inputCatatan = document.getElementById('fkModalCatatanInput');
    const btnSetFull = document.getElementById('fkBtnSetFullPayment');

    if (!modal || !form) return;

    currentFakturSisaPiutang = parseFloat(sisaPiutang) || 0;

    form.action = `/penjualan/faktur/${fakturId}/record-payment`;
    if (textFakturNo) textFakturNo.textContent = fakturNo;
    if (textCust) textCust.textContent = customerNm || 'Pelanggan Umum';
    if (textTotal) textTotal.textContent = 'Rp ' + (parseFloat(totalTagihan) || 0).toLocaleString('id-ID');
    if (textSisa) textSisa.textContent = 'Rp ' + currentFakturSisaPiutang.toLocaleString('id-ID');
    
    if (inputNominal) {
        inputNominal.value = currentFakturSisaPiutang;
        inputNominal.max = currentFakturSisaPiutang;
    }

    if (btnSetFull) {
        btnSetFull.textContent = 'Set Bayar Penuh (Rp ' + currentFakturSisaPiutang.toLocaleString('id-ID') + ')';
    }

    if (inputPenyetor) inputPenyetor.value = customerNm || '';
    if (inputCatatan) inputCatatan.value = `Pelunasan Faktur ${fakturNo} - ${customerNm || ''}`;

    modal.style.display = 'flex';
}

function closeFakturPaymentModal() {
    const modal = document.getElementById('modalRecordFakturPayment');
    if (modal) modal.style.display = 'none';
}

function fillFakturFullPayment() {
    const inputNominal = document.getElementById('fkModalNominalInput');
    if (inputNominal && currentFakturSisaPiutang > 0) {
        inputNominal.value = currentFakturSisaPiutang;
    }
}

// Tutup modal jika klik di latar belakang
window.addEventListener('click', function(e) {
    const modalPayment = document.getElementById('modalRecordFakturPayment');
    if (e.target === modalPayment) {
        closeFakturPaymentModal();
    }
});
