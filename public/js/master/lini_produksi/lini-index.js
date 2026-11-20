/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA LINI PRODUKSI INDEX JAVASCRIPT
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

/**
 * Toggle dropdown Aksi per baris dengan Smart Viewport Positioning
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    if (activeDropdownMenu === targetMenu && targetMenu.style.display === 'block') {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    targetMenu.style.display = 'block';
    activeDropdownMenu = targetMenu;
    activeTriggerButton = button;
    button.classList.add('active');

    positionActionDropdown(button, targetMenu);
}

/**
 * Hitung posisi dropdown secara dinamis agar tidak terpotong tepi layar/tabel
 */
function positionActionDropdown(button, menu) {
    if (!button || !menu) return;
    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 190;
    const menuHeight = menu.offsetHeight || 130;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        // Tampilkan di atas tombol (Dropup)
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        // Tampilkan di bawah tombol (Dropdown)
        menu.style.top = `${rect.bottom + 4}px`;
    }

    let leftPos = rect.right - menuWidth;
    if (leftPos < 10) leftPos = 10;
    if (leftPos + menuWidth > viewportWidth - 10) {
        leftPos = viewportWidth - menuWidth - 10;
    }
    menu.style.left = `${leftPos}px`;
}

/**
 * Menutup semua dropdown aksi yang sedang aktif
 */
function closeAllActionDropdowns() {
    if (activeDropdownMenu) {
        activeDropdownMenu.style.display = 'none';
        activeDropdownMenu = null;
    }
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
}

/**
 * Modal Generic Functions
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/**
 * Konfirmasi Nonaktifkan / Hapus Lini Produksi
 */
function confirmDeleteLini(url, cd, nm) {
    closeAllActionDropdowns();
    const form = document.getElementById('formDeleteLini');
    const cdEl = document.getElementById('deleteLiniCd');
    const nmEl = document.getElementById('deleteLiniNm');

    if (form) form.action = url;
    if (cdEl) cdEl.textContent = `KODE: ${cd}`;
    if (nmEl) nmEl.textContent = nm;

    openModal('modalDeleteLini');
}

/**
 * Tampilkan Floating Toast Notification yang Halus dan Elegan
 */
function showLiniToast(message, isError = false) {
    let container = document.querySelector('.lini-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'lini-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'lini-toast';
    if (isError) {
        toast.style.background = '#7f1d1d';
    }

    const iconSvg = isError
        ? `<svg class="lini-toast-icon" style="color: #f87171;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
        : `<svg class="lini-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;

    toast.innerHTML = `${iconSvg}<span>${message}</span>`;
    container.appendChild(toast);

    // Animasi muncul
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    // Otomatis hilang setelah 2.5 detik
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 250);
    }, 2500);
}

/**
 * Handle AJAX Toggle Status Lini Produksi (Aktif / Nonaktif)
 */
function handleToggleLiniStatus(inputEl, liniId) {
    if (!inputEl) return;
    const isChecked = inputEl.checked;
    const url = inputEl.getAttribute('data-url');
    const liniNm = inputEl.getAttribute('data-name') || 'Lini Produksi';
    const wrapperEl = document.getElementById('statusWrapper-' + liniId);
    const badgeEl = document.getElementById('statusBadge-' + liniId);
    const switchLabel = wrapperEl?.querySelector('.clean-switch');

    // Tampilkan animasi loading & nonaktifkan sementara
    inputEl.disabled = true;
    if (switchLabel) switchLabel.classList.add('is-loading');

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        inputEl.disabled = false;
        if (switchLabel) switchLabel.classList.remove('is-loading');

        if (data.success) {
            const newActive = Boolean(data.active_st);
            inputEl.checked = newActive;
            
            // Perbarui badge status (dot & text)
            if (badgeEl) {
                const textEl = badgeEl.querySelector('.status-text');
                if (textEl) textEl.textContent = newActive ? 'Aktif' : 'Nonaktif';
                badgeEl.className = 'lini-status-badge ' + (newActive ? 'status-active' : 'status-inactive');
            }

            // Perbarui tooltip
            if (wrapperEl) {
                wrapperEl.setAttribute('title', newActive ? 'Klik untuk menonaktifkan status' : 'Klik untuk mengaktifkan status');
            }

            // Toast notifikasi
            showLiniToast(data.message || `Status ${liniNm} berhasil diubah menjadi ${newActive ? 'Aktif' : 'Nonaktif'}.`);
        } else {
            // Revert state jika server gagal
            inputEl.checked = !isChecked;
            if (badgeEl) {
                const textEl = badgeEl.querySelector('.status-text');
                if (textEl) textEl.textContent = !isChecked ? 'Aktif' : 'Nonaktif';
                badgeEl.className = 'lini-status-badge ' + (!isChecked ? 'status-active' : 'status-inactive');
            }
            showLiniToast(data.message || 'Gagal mengubah status lini produksi.', true);
        }
    })
    .catch(err => {
        inputEl.disabled = false;
        if (switchLabel) switchLabel.classList.remove('is-loading');
        inputEl.checked = !isChecked;
        if (badgeEl) {
            const textEl = badgeEl.querySelector('.status-text');
            if (textEl) textEl.textContent = !isChecked ? 'Aktif' : 'Nonaktif';
            badgeEl.className = 'lini-status-badge ' + (!isChecked ? 'status-active' : 'status-inactive');
        }
        console.error('Error toggling lini status:', err);
        showLiniToast('Terjadi kesalahan koneksi saat mengubah status.', true);
    });
}

// Event Listeners Global
document.addEventListener('click', function (e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('resize', closeAllActionDropdowns);
window.addEventListener('scroll', closeAllActionDropdowns, true);

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
        document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
        document.body.style.overflow = '';
    }
});
