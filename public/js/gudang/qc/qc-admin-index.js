/**
 * QC ADMIN INDEX - DESKTOP ERP JAVASCRIPT
 * PT Mirasa Food Industry
 * Mematuhi Standar AGENTS.md (Smart Fixed Dropdown & Modal Konfirmasi Bahaya)
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllSmartDropdowns();
        }
    });

    // Tutup dropdown saat scroll tabel atau window resize
    window.addEventListener('scroll', function () {
        closeAllSmartDropdowns();
    }, true);

    window.addEventListener('resize', function () {
        closeAllSmartDropdowns();
    });
});

/**
 * Toggle Smart Action Dropdown dengan posisi fixed agar tidak terpotong oleh overflow-x tabel
 */
function toggleSmartActionDropdown(triggerBtn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    var menu = document.getElementById(menuId);
    if (!menu) return;

    var isCurrentlyOpen = menu.classList.contains('show');

    // Tutup semua dropdown lain terlebih dahulu
    closeAllSmartDropdowns();

    if (!isCurrentlyOpen) {
        // Ambil bounding rectangle tombol trigger
        var rect = triggerBtn.getBoundingClientRect();
        var menuWidth = 185; // estimasi lebar default dropdown
        var left = rect.right - menuWidth;

        // Pastikan tidak tembus sisi kiri layar
        if (left < 10) {
            left = 10;
        }

        // Posisi vertikal: buka ke bawah jika cukup ruang, atau ke atas jika mepet bawah
        var top = rect.bottom + 4;
        if (top + 220 > window.innerHeight) {
            top = Math.max(10, rect.top - 210);
        }

        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.classList.add('show');
    }
}

function closeAllSmartDropdowns() {
    var menus = document.querySelectorAll('.action-dropdown-menu.show');
    menus.forEach(function (m) {
        m.classList.remove('show');
    });
}

/**
 * Modal Konfirmasi Batal / Hapus Tiket QC (Warna Merah Bahaya)
 */
function openDeleteQcModal(qcId, qcNo) {
    closeAllSmartDropdowns();
    var modal = document.getElementById('modalDeleteQc');
    var form = document.getElementById('formDeleteQc');
    var labelNo = document.getElementById('deleteQcNoText');

    if (!modal || !form) return;

    labelNo.textContent = qcNo;
    form.action = '/qc/inbound/' + qcId;
    modal.style.display = 'flex';
}

function closeDeleteQcModal() {
    var modal = document.getElementById('modalDeleteQc');
    if (modal) modal.style.display = 'none';
}

/**
 * Modal Cepat Uji Fryer Lab Singkong
 */
function openModalUjiFryer(qcId, qcNo, qcdtlId) {
    closeAllSmartDropdowns();
    var modal = document.getElementById('modalUjiFryer');
    var form = document.getElementById('formUjiFryer');
    var labelNo = document.getElementById('ujiFryerQcNoText');
    var inputDtl = document.getElementById('ujiFryerQcdtlId');

    if (!modal || !form) return;

    labelNo.textContent = qcNo;
    if (inputDtl) inputDtl.value = qcdtlId;
    form.action = '/qc/inbound/' + qcId + '/uji-goreng';
    modal.style.display = 'flex';
}

function closeModalUjiFryer() {
    var modal = document.getElementById('modalUjiFryer');
    if (modal) modal.style.display = 'none';
}
