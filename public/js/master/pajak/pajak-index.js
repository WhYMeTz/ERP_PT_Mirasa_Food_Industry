/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA PAJAK & TARIF JAVASCRIPT
 * =========================================================================
 */

let activeDropdownMenu = null;
let activeTriggerButton = null;

/**
 * Buka modal berdasarkan ID elemen
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/**
 * Tutup modal berdasarkan ID elemen
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Tutup modal jika user klik di backdrop luar dialog
 */
document.addEventListener('click', function(e) {
    if (e.target && (e.target.classList.contains('modal-overlay') || e.target.classList.contains('pajak-modal-overlay'))) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

/**
 * Tutup modal dengan tombol Escape
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay, .pajak-modal-overlay').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
        closeAllActionDropdowns();
    }
});

/**
 * =========================================================================
 * SMART ACTION DROPDOWN
 * =========================================================================
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

function positionActionDropdown(button, menu) {
    if (!button || !menu) return;
    const rect = button.getBoundingClientRect();
    const menuWidth = menu.offsetWidth || 195;
    const menuHeight = menu.offsetHeight || 140;
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    const spaceBelow = viewportHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
        menu.style.top = `${rect.top - menuHeight - 4}px`;
    } else {
        menu.style.top = `${rect.bottom + 4}px`;
    }

    let leftPos = rect.right - menuWidth;
    if (leftPos < 10) leftPos = 10;
    if (leftPos + menuWidth > viewportWidth - 10) {
        leftPos = viewportWidth - menuWidth - 10;
    }
    menu.style.left = `${leftPos}px`;
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');
    document.querySelectorAll('.btn-action-trigger').forEach(b => b.classList.remove('active'));
    activeDropdownMenu = null;
    activeTriggerButton = null;
}

window.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', function() {
    if (activeDropdownMenu && activeTriggerButton) {
        positionActionDropdown(activeTriggerButton, activeDropdownMenu);
    }
}, true);

window.addEventListener('resize', closeAllActionDropdowns);

/**
 * Pilihan segmented Sifat Perlakuan (TAMBAH / POTONG)
 */
function selectSifatOption(mode, value) {
    const cardTambah = document.getElementById(`card_sifat_${mode}_tambah`);
    const cardPotong = document.getElementById(`card_sifat_${mode}_potong`);
    const form = document.getElementById(mode === 'edit' ? 'formEditPajak' : 'formTambahPajak');
    if (!form) return;

    const radio = form.querySelector(`input[name="sifat_pajak"][value="${value}"]`);
    if (radio) radio.checked = true;

    if (value === 'TAMBAH') {
        if (cardTambah) cardTambah.className = 'sifat-option-card active-tambah';
        if (cardPotong) cardPotong.className = 'sifat-option-card';
    } else {
        if (cardTambah) cardTambah.className = 'sifat-option-card';
        if (cardPotong) cardPotong.className = 'sifat-option-card active-potong';
    }
}

/**
 * Toggle Channel Card (Pembelian / Penjualan) & Dropdown COA
 */
function onChannelToggleChanged(checkbox, cardId, selectId) {
    const card = document.getElementById(cardId);
    const select = document.getElementById(selectId);
    if (!checkbox || !card) return;

    if (checkbox.checked) {
        card.classList.remove('channel-inactive');
        card.classList.add('channel-active');
        if (select) {
            select.disabled = false;
        }
    } else {
        card.classList.remove('channel-active');
        card.classList.add('channel-inactive');
        if (select) {
            select.disabled = true;
            select.value = '';
        }
    }
}

/**
 * =========================================================================
 * AJAX EDIT MODAL
 * =========================================================================
 */
async function openEditPajakModal(id) {
    closeAllActionDropdowns();

    const baseUrl = (window.pajakConfig && window.pajakConfig.routes && window.pajakConfig.routes.base)
        ? window.pajakConfig.routes.base
        : '/master-pajak';

    try {
        const response = await fetch(`${baseUrl}/${id}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Gagal mengambil data pajak (HTTP ${response.status})`);
        }

        const resJson = await response.json();
        const data = (resJson && resJson.data) ? resJson.data : resJson;
        
        // Populate edit form
        const form = document.getElementById('formEditPajak');
        if (form) {
            form.action = `${baseUrl}/${id}`;
        }

        const setVal = (elemId, val) => {
            const el = document.getElementById(elemId);
            if (el) el.value = val !== null && val !== undefined ? String(val) : '';
        };

        setVal('edit_pajak_id', data.pajak_id);
        setVal('edit_pajak_cd', data.pajak_cd);
        setVal('edit_pajak_nm', data.pajak_nm);
        setVal('edit_tarif_persen', parseFloat(data.tarif_persen || 0).toString());
        selectSifatOption('edit', data.sifat_pajak || 'TAMBAH');

        const isPembelian = data.is_pembelian === true || data.is_pembelian == 1 || data.is_pembelian === '1' || data.is_pembelian === 't' || data.is_pembelian === 'true';
        const isPembelianCb = document.getElementById('edit_is_pembelian');
        if (isPembelianCb) {
            isPembelianCb.checked = isPembelian;
            onChannelToggleChanged(isPembelianCb, 'card_channel_edit_pembelian', 'edit_coa_pembelian_id');
        }
        setVal('edit_coa_pembelian_id', data.coa_pembelian_id || data.coa_id || '');

        const isPenjualan = data.is_penjualan === true || data.is_penjualan == 1 || data.is_penjualan === '1' || data.is_penjualan === 't' || data.is_penjualan === 'true';
        const isPenjualanCb = document.getElementById('edit_is_penjualan');
        if (isPenjualanCb) {
            isPenjualanCb.checked = isPenjualan;
            onChannelToggleChanged(isPenjualanCb, 'card_channel_edit_penjualan', 'edit_coa_penjualan_id');
        }
        setVal('edit_coa_penjualan_id', data.coa_penjualan_id || '');

        const activeCb = document.getElementById('edit_active_st');
        if (activeCb) {
            activeCb.checked = data.active_st === true || data.active_st == 1 || data.active_st === '1' || data.active_st === 't' || data.active_st === 'true';
        }

        openModal('modalEditPajak');

    } catch (err) {
        console.error('Error fetching pajak edit data:', err);
        alert('Terjadi kesalahan saat memuat data pajak. Silakan refresh halaman.');
    }
}

/**
 * =========================================================================
 * TOGGLE STATUS MODAL (AKTIF / NONAKTIF)
 * =========================================================================
 */
function openToggleStatusModal(id, cd, nm, tarif, currentActiveSt) {
    closeAllActionDropdowns();

    const baseUrl = (window.pajakConfig && window.pajakConfig.routes && window.pajakConfig.routes.base)
        ? window.pajakConfig.routes.base
        : '/master-pajak';

    const form = document.getElementById('formToggleStatusPajak');
    if (form) {
        form.action = `${baseUrl}/${id}/toggle-status`;
    }

    const cdEl = document.getElementById('togglePajakCd');
    const nmEl = document.getElementById('togglePajakNm');
    const tarifEl = document.getElementById('togglePajakTarif');

    if (cdEl) cdEl.innerText = cd;
    if (nmEl) nmEl.innerText = nm;
    if (tarifEl) tarifEl.innerText = `${tarif}%`;

    const titleEl = document.getElementById('toggleModalTitle');
    const subtitleEl = document.getElementById('toggleModalSubtitle');
    const descEl = document.getElementById('toggleStatusDesc');
    const bannerEl = document.getElementById('toggleActionBanner');
    const btnSubmit = document.getElementById('btnSubmitToggleStatus');
    const btnText = document.getElementById('toggleBtnText');

    if (currentActiveSt == 1) {
        // Akan dinonaktifkan
        if (titleEl) titleEl.innerText = 'Nonaktifkan Pajak';
        if (subtitleEl) subtitleEl.innerText = 'Pajak tidak akan dapat dipilih pada transaksi baru';
        if (descEl) descEl.innerHTML = '<strong>Peringatan:</strong> Menonaktifkan pajak ini akan menyembunyikannya dari daftar opsi pajak pada transaksi Pembelian (PO/Penerimaan) maupun Penjualan. Data histori transaksi lama tetap aman.';
        if (bannerEl) bannerEl.className = 'status-action-banner';
        if (btnSubmit) {
            btnSubmit.className = 'btn btn-warning';
        }
        if (btnText) btnText.innerText = 'Ya, Nonaktifkan Pajak';
    } else {
        // Akan diaktifkan kembali
        if (titleEl) titleEl.innerText = 'Aktifkan Kembali Pajak';
        if (subtitleEl) subtitleEl.innerText = 'Pajak akan kembali tersedia pada transaksi operasional';
        if (descEl) descEl.innerHTML = '<strong>Informasi:</strong> Mengaktifkan kembali pajak ini akan membuatnya muncul pada menu pemilihan pajak untuk PO, faktur pembelian, dan faktur penjualan.';
        if (bannerEl) bannerEl.className = 'status-action-banner banner-activate';
        if (btnSubmit) {
            btnSubmit.className = 'btn btn-primary';
        }
        if (btnText) btnText.innerText = 'Ya, Aktifkan Kembali';
    }

    openModal('modalToggleStatusPajak');
}

/**
 * =========================================================================
 * DELETE MODAL
 * =========================================================================
 */
function openDeletePajakModal(id, cd, nm) {
    closeAllActionDropdowns();

    const baseUrl = (window.pajakConfig && window.pajakConfig.routes && window.pajakConfig.routes.base)
        ? window.pajakConfig.routes.base
        : '/master-pajak';

    const form = document.getElementById('formDeletePajak');
    if (form) {
        form.action = `${baseUrl}/${id}`;
    }

    const cdEl = document.getElementById('deletePajakCd');
    const nmEl = document.getElementById('deletePajakNm');

    if (cdEl) cdEl.innerText = cd;
    if (nmEl) nmEl.innerText = nm;

    openModal('modalDeletePajak');
}

/**
 * Otomatisasi sifat dan default tarif saat memilih kategori pajak
 */
function onKategoriPajakChanged(selectElem, mode) {
    if (!selectElem) return;
    const kategori = selectElem.value;
    const prefix = mode === 'edit' ? 'edit_' : 'create_';

    const sifatEl = document.getElementById(`${prefix}sifat_pajak`);
    const tarifEl = document.getElementById(`${prefix}tarif_persen`);

    if (kategori === 'PPN') {
        if (sifatEl) sifatEl.value = 'TAMBAH';
        if (tarifEl && (!tarifEl.value || mode === 'create')) tarifEl.value = '11.00';
    } else if (kategori === 'BEBAS_PAJAK') {
        if (sifatEl) sifatEl.value = 'TAMBAH';
        if (tarifEl && (!tarifEl.value || mode === 'create')) tarifEl.value = '0.00';
    } else if (kategori.startsWith('PPH_')) {
        if (sifatEl) sifatEl.value = 'POTONG';
        if (mode === 'create' && tarifEl) {
            if (kategori === 'PPH_23') tarifEl.value = '2.00';
            else if (kategori === 'PPH_4_2') tarifEl.value = '10.00';
            else if (kategori === 'PPH_21') tarifEl.value = '5.00';
            else if (kategori === 'PPH_22') tarifEl.value = '0.25';
        }
    }
}

// Global exposure
window.openModal = openModal;
window.closeModal = closeModal;
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeAllActionDropdowns = closeAllActionDropdowns;
window.openEditPajakModal = openEditPajakModal;
window.openToggleStatusModal = openToggleStatusModal;
window.openDeletePajakModal = openDeletePajakModal;
window.onKategoriPajakChanged = onKategoriPajakChanged;
window.toggleAccountSelect = toggleAccountSelect;
window.selectSifatOption = selectSifatOption;
window.onChannelToggleChanged = onChannelToggleChanged;
