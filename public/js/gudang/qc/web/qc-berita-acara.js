/**
 * BERITA ACARA PENOLAKAN BAHAN BAKU - PT MIRASA FOOD INDUSTRY
 * JavaScript untuk interaksi edit dokumen, auto-save localStorage, dan sinkronisasi cetak
 */

(function () {
    'use strict';

    const qcId = window.baConfig?.qcId;
    if (!qcId) return;

    const storageKey = `mirasa_ba_data_${qcId}`;
    let isEditMode = false;

    // Element references
    const editBanner = document.getElementById('baEditBanner');
    const toggleBtn = document.getElementById('btnToggleBaEdit');
    const editableElements = document.querySelectorAll('.ba-editable');

    // 1. Muat data tersimpan dari localStorage saat halaman siap
    function loadSavedEdits() {
        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) return;
            const data = JSON.parse(raw);
            if (typeof data !== 'object') return;

            editableElements.forEach((el) => {
                const field = el.getAttribute('data-field');
                if (field && data[field] !== undefined) {
                    el.innerText = data[field];
                }
            });
        } catch (e) {
            console.error('Error loading saved Berita Acara edits:', e);
        }
    }

    // 2. Simpan setiap perubahan ke localStorage secara otomatis
    function saveEdits() {
        try {
            const data = {};
            editableElements.forEach((el) => {
                const field = el.getAttribute('data-field');
                if (field) {
                    data[field] = el.innerText.trim();
                }
            });
            localStorage.setItem(storageKey, JSON.stringify(data));
        } catch (e) {
            console.error('Error saving Berita Acara edits:', e);
        }
    }

    // 3. Toggle Mode Edit
    function toggleEditMode(forceState) {
        if (typeof forceState === 'boolean') {
            isEditMode = forceState;
        } else {
            isEditMode = !isEditMode;
        }

        if (isEditMode) {
            document.body.classList.add('edit-mode-active');
            editableElements.forEach((el) => {
                el.setAttribute('contenteditable', 'true');
            });
            if (toggleBtn) {
                toggleBtn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Selesai Edit</span>';
                toggleBtn.classList.remove('ba-btn-secondary', 'btn-secondary');
                toggleBtn.classList.add('ba-btn-success');
            }
        } else {
            document.body.classList.remove('edit-mode-active');
            editableElements.forEach((el) => {
                el.removeAttribute('contenteditable');
            });
            if (toggleBtn) {
                toggleBtn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg><span>Mode Edit Dokumen</span>';
                toggleBtn.classList.remove('ba-btn-success');
                toggleBtn.classList.add('ba-btn-secondary');
            }
        }

        // Kabari window parent jika berada dalam iframe modal
        if (window.parent && window.parent !== window) {
            try {
                window.parent.postMessage({
                    type: 'ba_edit_mode_changed',
                    isEditMode: isEditMode
                }, '*');
            } catch (err) {}
        }
    }

    // 4. Reset Default: Hapus edit localStorage dan muat ulang halaman
    function resetDefaults() {
        if (confirm('Kembalikan seluruh teks Berita Acara ke format standar sistem?')) {
            try {
                localStorage.removeItem(storageKey);
            } catch (e) {}
            window.location.reload();
        }
    }

    // Event Listeners pada elemen yang dapat diedit
    editableElements.forEach((el) => {
        el.addEventListener('input', () => {
            saveEdits();
        });

        // Hindari paste format styling aneh (hanya paste plain text)
        el.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.originalEvent || e).clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });
    });

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            toggleEditMode();
        });
    }

    // Inisialisasi
    document.addEventListener('DOMContentLoaded', () => {
        loadSavedEdits();
    });

    // Buka API ke global window untuk kontrol dari parent modal
    window.toggleBaEditMode = toggleEditMode;
    window.resetBaDefaults = resetDefaults;

    // Listener message dari parent popup modal
    window.addEventListener('message', (event) => {
        if (!event.data || typeof event.data !== 'object') return;
        if (event.data.action === 'toggle_edit') {
            toggleEditMode();
        } else if (event.data.action === 'reset_default') {
            resetDefaults();
        } else if (event.data.action === 'print') {
            window.print();
        }
    });

})();
