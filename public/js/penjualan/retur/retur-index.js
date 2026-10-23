/**
 * ══════════════════════════════════════════════════════════════════════════
 * RETUR PENJUALAN (SALES RETURN) - INDEX SCRIPT
 * ERP PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

function toggleSmartActionDropdown(btn, e, menuId) {
    e.stopPropagation();

    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isVisible = menu.style.display === 'block';

    // Tutup semua menu yang sedang terbuka
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(b => {
        b.classList.remove('active');
    });

    if (!isVisible) {
        const rect = btn.getBoundingClientRect();
        const menuWidth = 200;
        const menuHeight = 110;

        // Posisi X (Horizontal)
        let left = rect.right - menuWidth;
        if (left < 10) left = rect.left;

        // Posisi Y (Vertical)
        let top = rect.bottom + 4;
        if (top + menuHeight > window.innerHeight) {
            top = rect.top - menuHeight - 4;
        }

        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.display = 'block';
        btn.classList.add('active');
    }
}

function openVoidModal(returId, returNo) {
    const modal = document.getElementById('modalVoidReturIndex');
    if (!modal) return;

    const form = document.getElementById('formVoidReturIndex');
    if (form) {
        form.action = `/penjualan/retur/${returId}/void`;
    }

    const lbl = document.getElementById('modalVoidReturNoLbl');
    if (lbl) {
        lbl.textContent = `Dokumen: ${returNo}`;
    }

    modal.style.display = 'flex';
}

function closeVoidModal() {
    const modal = document.getElementById('modalVoidReturIndex');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Tutup menu dan modal saat klik di luar
window.addEventListener('click', function(e) {
    // Dropdown Smart Action
    if (!e.target.closest('.btn-action-trigger') && !e.target.closest('.action-dropdown-menu')) {
        document.querySelectorAll('.action-dropdown-menu').forEach(m => {
            m.style.display = 'none';
        });
        document.querySelectorAll('.btn-action-trigger').forEach(b => {
            b.classList.remove('active');
        });
    }

    // Modal Void
    const modal = document.getElementById('modalVoidReturIndex');
    if (e.target === modal) {
        closeVoidModal();
    }
});

window.addEventListener('scroll', function() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
    document.querySelectorAll('.btn-action-trigger').forEach(b => {
        b.classList.remove('active');
    });
}, true);
