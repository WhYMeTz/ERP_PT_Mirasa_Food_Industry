/**
 * =========================================================================
 * ERP PT MIRASA - QUALITY CONTROL (QC) INDEX JAVASCRIPT
 * Mengikuti 100% Standar Smart Action Dropdown dari Modul PO
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

/**
 * Toggle Smart Action Dropdown dengan posisi Fixed Viewport (Mencegah terpotong overflow tabel)
 */
function toggleSmartActionDropdown(button, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;

    // Jika dropdown yang sama sedang terbuka, tutup
    if (activeDropdownMenu === targetMenu && targetMenu.style.display === 'block') {
        closeAllActionDropdowns();
        return;
    }

    // Tutup dropdown lain yang sedang terbuka
    closeAllActionDropdowns();

    // Tampilkan menu dan hitung posisinya secara pintar
    targetMenu.style.display = 'block';
    activeDropdownMenu = targetMenu;
    activeTriggerButton = button;
    button.classList.add('active');

    positionActionDropdown(button, targetMenu);
}

function positionActionDropdown(button, menu) {
    if (!button || !menu) return;

    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 210;
    const menuHeight = menu.offsetHeight || 180;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    // Cek ruang vertikal: jika ruang bawah tidak cukup, letakkan di atas tombol (Dropup)
    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        // Dropup (di atas tombol)
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        // Dropdown (di bawah tombol)
        menu.style.top = `${rect.bottom + 4}px`;
    }

    // Cek ruang horizontal: posisikan rata kanan tombol
    let leftPos = rect.right - menuWidth;
    if (leftPos < 8) {
        leftPos = 8;
    }
    if (leftPos + menuWidth > viewportWidth - 8) {
        leftPos = viewportWidth - menuWidth - 8;
    }

    menu.style.left = `${leftPos}px`;
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu, .qc-action-menu-dropdown').forEach(menu => {
        menu.style.display = 'none';
    });
    if (activeTriggerButton) {
        activeTriggerButton.classList.remove('active');
        activeTriggerButton = null;
    }
    activeDropdownMenu = null;
}

// Tutup dropdown saat scroll window/tabel atau resize
window.addEventListener('scroll', function () {
    closeAllActionDropdowns();
}, true);

window.addEventListener('resize', function () {
    closeAllActionDropdowns();
});

// Tutup dropdown saat klik di luar
document.addEventListener('click', function (e) {
    if (!e.target.closest('.btn-action-trigger') && 
        !e.target.closest('.action-dropdown-menu') && 
        !e.target.closest('.qc-action-menu-dropdown')) {
        closeAllActionDropdowns();
    }
});

// Tutup dengan Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllActionDropdowns();
        closeDeleteQcModal();
        closeModalUjiFryer();
        closeHaccpModal();
    }
});

/**
 * Modal Popup Preview Dokumen Mutu HACCP
 */
function openHaccpModal(qcId, qcNo) {
    closeAllActionDropdowns();

    const modal = document.getElementById('modalHaccpPreview');
    const iframe = document.getElementById('haccpPreviewIframe');
    const title = document.getElementById('modalHaccpTitle');
    const printBtn = document.getElementById('modalHaccpPrintBtn');
    const editBtn = document.getElementById('modalHaccpEditBtn');
    const loading = document.getElementById('modalHaccpLoading');

    if (!modal || !iframe) return;

    if (title) title.textContent = `Dokumen Mutu (HACCP): ${qcNo}`;
    if (editBtn) editBtn.href = `/qc/inbound/${qcId}/edit`;
    if (loading) loading.style.display = 'flex';

    // Set source iframe ke dokumen dengan layout popup
    iframe.src = `/qc/inbound/${qcId}?popup=1`;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHaccpModal() {
    const modal = document.getElementById('modalHaccpPreview');
    const iframe = document.getElementById('haccpPreviewIframe');

    if (modal) modal.style.display = 'none';
    if (iframe) iframe.src = 'about:blank';
    document.body.style.overflow = '';
}

function onHaccpIframeLoaded() {
    const loading = document.getElementById('modalHaccpLoading');
    const iframe = document.getElementById('haccpPreviewIframe');
    if (loading && iframe && iframe.src !== 'about:blank') {
        loading.style.display = 'none';
    }
}

function printHaccpModalIframe() {
    const iframe = document.getElementById('haccpPreviewIframe');
    if (iframe && iframe.contentWindow) {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            console.error('Print modal iframe error:', e);
        }
    }
}

/**
 * Direct Print Lembar HACCP A4 Langsung Tanpa Masuk Halaman Baru
 */
function directPrintHaccp(qcId) {
    closeAllActionDropdowns();

    let printFrame = document.getElementById('haccpDirectPrintIframe');
    if (!printFrame) {
        printFrame = document.createElement('iframe');
        printFrame.id = 'haccpDirectPrintIframe';
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        printFrame.style.opacity = '0';
        printFrame.style.pointerEvents = 'none';
        document.body.appendChild(printFrame);
    }

    printFrame.onload = function() {
        if (printFrame.src && printFrame.src !== 'about:blank') {
            setTimeout(() => {
                try {
                    printFrame.contentWindow.focus();
                    printFrame.contentWindow.print();
                } catch (err) {
                    console.error('Direct print error:', err);
                }
            }, 300);
        }
    };

    printFrame.src = `/qc/inbound/${qcId}?popup=1`;
}

/**
 * Modal Konfirmasi Batal / Hapus Tiket QC
 */
function openDeleteQcModal(qcId, qcNo) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modalDeleteQc');
    const form = document.getElementById('formDeleteQc');
    const labelNo = document.getElementById('deleteQcNoText');

    if (!modal || !form) return;

    if (labelNo) labelNo.textContent = qcNo;
    form.action = '/qc/inbound/' + qcId;
    modal.style.display = 'flex';
}

function closeDeleteQcModal() {
    const modal = document.getElementById('modalDeleteQc');
    if (modal) modal.style.display = 'none';
}
