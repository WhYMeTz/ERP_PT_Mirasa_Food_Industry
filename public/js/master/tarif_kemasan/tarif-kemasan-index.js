/**
 * =========================================================================
 * ERP PT MIRASA - MASTER STANDAR HARGA KEMASAN & PACKAGING JS
 * Standar Enterprise ERP - Smart Action Dropdown & Dynamic Modal Engine
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllSmartDropdowns();
        }
    });

    // Tutup dropdown saat scroll tabel
    window.addEventListener('scroll', closeAllSmartDropdowns, true);

    // Escape key untuk modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditTarifModal();
            closeQuickUpdateModal();
        }
    });
});

/**
 * Smart Action Dropdown dengan posisi fixed anti-terpotong overflow
 */
let activeMenuEl = null;

function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activeMenuEl && activeMenuEl !== menu) {
        closeAllSmartDropdowns();
    }

    const isOpen = menu.classList.contains('show');
    if (isOpen) {
        menu.classList.remove('show');
        menu.style.display = 'none';
        activeMenuEl = null;
        return;
    }

    // Hitung posisi tombol di viewport
    const rect = button.getBoundingClientRect();
    menu.style.position = 'fixed';
    menu.style.zIndex = '99999';
    menu.style.display = 'block';
    menu.classList.add('show');

    // Cek posisi atas / bawah
    const menuHeight = menu.offsetHeight || 120;
    const spaceBelow = window.innerHeight - rect.bottom;

    if (spaceBelow < menuHeight && rect.top > menuHeight) {
        // Tampilkan di atas tombol
        menu.style.top = (rect.top - menuHeight - 4) + 'px';
    } else {
        // Tampilkan di bawah tombol
        menu.style.top = (rect.bottom + 4) + 'px';
    }

    // Alignment kanan
    const menuWidth = menu.offsetWidth || 190;
    menu.style.left = Math.max(10, rect.right - menuWidth) + 'px';

    activeMenuEl = menu;
}

function closeAllSmartDropdowns() {
    document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
        m.classList.remove('show');
        m.style.display = 'none';
    });
    activeMenuEl = null;
}

/**
 * Modal Edit Tarif Tunggal
 */
function openEditTarifModal(item) {
    closeAllSmartDropdowns();

    const form = document.getElementById('formEditTarifKemasan');
    if (!form) return;

    // Set action URL
    form.action = `/master-tarif-kemasan/${item.tarif_kemasan_id}`;

    // Isi data modal
    document.getElementById('edit_tarif_id').value = item.tarif_kemasan_id;
    document.getElementById('edit_kode_tarif_badge').textContent = item.kode_tarif;
    document.getElementById('edit_nama_tarif_display').textContent = item.nama_tarif;
    document.getElementById('edit_kategori_display').textContent = item.kategori_bahan;
    document.getElementById('edit_satuan_label_display').textContent = item.satuan_label;

    document.getElementById('edit_nilai_tarif').value = item.nilai_tarif;
    
    const sekunderIn = document.getElementById('edit_nilai_tarif_sekunder');
    if (sekunderIn) {
        sekunderIn.value = item.nilai_tarif_sekunder !== null ? item.nilai_tarif_sekunder : '';
    }

    const tglIn = document.getElementById('edit_berlaku_sejak');
    if (tglIn) {
        if (item.berlaku_sejak) {
            const d = new Date(item.berlaku_sejak);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            tglIn.value = `${yyyy}-${mm}-${dd}`;
        } else {
            tglIn.value = '';
        }
    }

    const ketIn = document.getElementById('edit_keterangan');
    if (ketIn) {
        ketIn.value = item.keterangan || '';
    }

    const modal = document.getElementById('modalEditTarifKemasan');
    if (modal) {
        modal.classList.add('show');
        setTimeout(() => document.getElementById('edit_nilai_tarif')?.focus(), 100);
    }
}

function closeEditTarifModal() {
    const modal = document.getElementById('modalEditTarifKemasan');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Modal Quick Matrix Update (Perbarui Cepat Seluruh Daftar Harga Seperti Excel)
 */
function openQuickUpdateModal() {
    closeAllSmartDropdowns();
    const modal = document.getElementById('modalQuickUpdateTarif');
    if (modal) {
        modal.classList.add('show');
    }
}

function closeQuickUpdateModal() {
    const modal = document.getElementById('modalQuickUpdateTarif');
    if (modal) {
        modal.classList.remove('show');
    }
}

/**
 * Helper format rupiah
 */
function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}
