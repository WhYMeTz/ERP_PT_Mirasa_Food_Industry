/**
 * =========================================================================
 * ERP PT MIRASA - PENGEMASAN BARANG JADI & HPP (PACKAGING) INDEX JS
 * Standard Separation of Concerns & Clean DOM Handling
 * =========================================================================
 */

let activePckDropdownMenu = null;
let activePckTriggerButton = null;

/**
 * Hitung posisi dropdown secara dinamis dan responsif
 */
function positionPckActionDropdown(button, menu) {
    if (!button || !menu) return;

    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 190;
    const menuHeight = menu.offsetHeight || 135;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    let left = rect.right - menuWidth;
    if (left < 10) left = 10;
    if (left + menuWidth > windowWidth - 10) {
        left = windowWidth - menuWidth - 10;
    }

    const spaceBelow = windowHeight - rect.bottom;
    const spaceAbove = rect.top;

    let top;
    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        top = rect.top - menuHeight - 4;
    } else {
        top = rect.bottom + 4;
    }

    menu.style.top = top + 'px';
    menu.style.left = left + 'px';
}

/**
 * Tutup seluruh dropdown aksi yang sedang aktif
 */
function closeAllPckActionDropdowns() {
    if (activePckDropdownMenu) {
        activePckDropdownMenu.classList.remove('show');
        activePckDropdownMenu = null;
    }
    if (activePckTriggerButton) {
        activePckTriggerButton.classList.remove('active');
        activePckTriggerButton = null;
    }
    document.querySelectorAll('.action-dropdown-menu.show').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('.btn-action-trigger.active').forEach(b => b.classList.remove('active'));
}

/**
 * Smart Action Dropdown Trigger (Position Fixed untuk mencegah overflow/scroll clipping)
 */
function toggleSmartActionDropdown(btn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    if (activePckDropdownMenu === menu) {
        closeAllPckActionDropdowns();
        return;
    }

    closeAllPckActionDropdowns();

    activePckDropdownMenu = menu;
    activePckTriggerButton = btn;
    btn.classList.add('active');

    menu.classList.add('show');
    positionPckActionDropdown(btn, menu);
}

document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
            closeAllPckActionDropdowns();
        }
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllPckActionDropdowns();
            closeVoidPackagingModal();
        }
    });

    window.addEventListener('scroll', function () {
        if (activePckDropdownMenu && activePckTriggerButton) {
            positionPckActionDropdown(activePckTriggerButton, activePckDropdownMenu);
        }
    }, true);

    window.addEventListener('resize', function () {
        closeAllPckActionDropdowns();
    });
});

/**
 * Buka Modal Konfirmasi Pembatalan Dokumen Packaging (Void)
 */
function openVoidPackagingModal(packId, packNo, targetNm) {
    const modal = document.getElementById('modalVoidPackaging');
    if (!modal) return;

    const form = document.getElementById('formVoidPackaging');
    if (form) {
        form.action = '/produksi/packaging/' + packId + '/void';
    }

    const labelNo = document.getElementById('voidPackNoLabel');
    if (labelNo) {
        labelNo.textContent = packNo + ' (' + targetNm + ')';
    }

    const inputAlasan = document.getElementById('inputAlasanVoid');
    if (inputAlasan) {
        inputAlasan.value = '';
        setTimeout(() => inputAlasan.focus(), 150);
    }

    modal.style.display = 'flex';
}

/**
 * Tutup Modal Konfirmasi Pembatalan Packaging
 */
function closeVoidPackagingModal() {
    const modal = document.getElementById('modalVoidPackaging');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Modal Quick Matrix Update (Perbarui Cepat Seluruh Daftar Harga Seperti Excel)
 */
function openQuickUpdateModal() {
    closeAllPckActionDropdowns();
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

// Inisialisasi AJAX submit handler untuk Modal Quick Update Tarif Kemasan pada Halaman Index & Rekap
document.addEventListener('DOMContentLoaded', function () {
    const formQuick = document.getElementById('formQuickUpdateTarif');
    if (formQuick) {
        formQuick.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const origHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Menyimpan...</span>';
            }

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }

                if (res.success) {
                    closeQuickUpdateModal();
                    // Reload halaman agar seluruh rumus, metrik, dan tabel rekapitulasi ter-refresh dengan angka baru
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal menyimpan perubahan tarif kemasan.');
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }
                console.error(err);
                alert('Terjadi kendala jaringan saat menyimpan standar tarif.');
            });
        });
    }
});
