/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER : ZAHIR-INSPIRED FLAT LAYOUT (JS)
 * -------------------------------------------------------------------------
 * Mengaktifkan layout flat ala Zahir:
 *   - Buang wrapper card visual
 *   - Header jadi inline compact grid
 *   - Tabel prominent full focus
 * 
 * Toggle dengan tombol atau localStorage preference.
 * =========================================================================
 */
(function () {
    'use strict';

    var ZAHIR = {
        enabled: false,
        storageKey: 'poZahirMode',
    };

    /* ------------------------------------------------------------------
     * Helper
     * ------------------------------------------------------------------ */

    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function qsa(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    /* ------------------------------------------------------------------
     * 1. Tandai elemen untuk styling Zahir
     * ------------------------------------------------------------------ */

    function markElements() {
        var grid = qs('.order-station-grid');
        if (!grid) return false;

        var mainCol = grid.children[0];
        if (!mainCol) return false;

        var cards = qsa('.card', mainCol);
        if (cards.length < 1) return false;

        // Panel header = kartu pertama
        var headerCard = cards[0];
        headerCard.classList.add('po-header-card');

        // Panel tabel = kartu yang ada tableItems/tablePoItems
        var itemsCard = null;
        for (var i = 1; i < cards.length; i++) {
            if (cards[i].querySelector('#tableItems, #tablePoItems')) {
                itemsCard = cards[i];
                break;
            }
        }

        if (!itemsCard) {
            itemsCard = cards[cards.length - 1];
        }
        itemsCard.classList.add('po-items-card');

        // Tandai blok-blok di header
        qsa(':scope > div:not(.card-header):not(.po-hdr-summary)', headerCard).forEach(function (blk) {
            blk.classList.add('po-hdr-block');
        });

        // Tandai grid
        qsa('div[style*="grid-template-columns"]', headerCard).forEach(function (g) {
            g.classList.add('po-hdr-grid');
        });

        // Tandai baris tanggal (untuk grid 4 kolom khusus)
        var dateRow = qs('input[name="po_tgl"]');
        if (dateRow) {
            var dateGrid = dateRow.closest('div[style*="grid-template-columns"]');
            if (dateGrid) dateGrid.classList.add('po-grid-dates');
        }

        // Tandai selector mode pemesanan
        var modeBtns = qsa('.po-mode-btn', headerCard);
        if (modeBtns.length > 0) {
            var modeParent = modeBtns[0].parentElement;
            if (modeParent) modeParent.classList.add('po-mode-selector');
        }

        // Tandai baris approver + tax
        var approverRow = qs('select[name="approver_id"]');
        if (approverRow) {
            var approverGrid = approverRow.closest('div[style*="grid-template-columns"]');
            if (approverGrid) approverGrid.classList.add('po-approver-tax-row');
        }

        // Tandai baris notes
        var noteField = qs('textarea[name="note_txt"]');
        if (noteField) {
            var noteGrid = noteField.closest('div[style*="grid-template-columns"]');
            if (noteGrid) noteGrid.classList.add('po-notes-row');
        }

        // Tandai checkbox PPN
        var ppnCheck = qs('input[name="ppn_st"]');
        if (ppnCheck) {
            var ppnParent = ppnCheck.closest('div');
            if (ppnParent) ppnParent.classList.add('po-tax-toggle');
        }

        return true;
    }

    /* ------------------------------------------------------------------
     * 2. Toggle mode Zahir
     * ------------------------------------------------------------------ */

    function setZahirMode(on) {
        ZAHIR.enabled = !!on;
        document.body.classList.toggle('po-zahir-mode', ZAHIR.enabled);

        // Update tombol toggle (bila ada)
        var btn = qs('#poZahirToggle');
        if (btn) {
            btn.classList.toggle('is-active', ZAHIR.enabled);
            var label = qs('.zahir-toggle-label', btn);
            if (label) {
                label.textContent = ZAHIR.enabled ? 'Mode Klasik' : 'Mode Zahir';
            }
        }

        // Simpan preferensi
        try {
            window.localStorage.setItem(ZAHIR.storageKey, ZAHIR.enabled ? '1' : '0');
        } catch (e) { /* noop */ }

        // Trigger recalc tinggi bila fit-screen aktif
        if (window.PO_FIT_recalc) {
            setTimeout(window.PO_FIT_recalc, 100);
        }
    }

    /* ------------------------------------------------------------------
     * 3. Inject tombol toggle (opsional, bisa dimatikan)
     * ------------------------------------------------------------------ */

    function injectToggleButton() {
        // Cari toolbar UX
        var toolbar = qs('#poUxToolbar');
        if (!toolbar || qs('#poZahirToggle')) return;

        var spacer = qs('.ux-toolbar-spacer', toolbar);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'poZahirToggle';
        btn.className = 'ux-toolbar-btn zahir-toggle';
        btn.title = 'Toggle layout Zahir (flat, minimal scroll)';
        btn.innerHTML =
            '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>' +
            '</svg>' +
            '<span class="zahir-toggle-label">Mode Zahir</span>';

        btn.addEventListener('click', function () {
            setZahirMode(!ZAHIR.enabled);
        });

        if (spacer && spacer.parentNode) {
            spacer.parentNode.insertBefore(btn, spacer);
        } else {
            toolbar.appendChild(btn);
        }
    }

    /* ------------------------------------------------------------------
     * 4. Boot
     * ------------------------------------------------------------------ */

    function boot() {
        if (!markElements()) return;

        injectToggleButton();

        // Pulihkan preferensi
        try {
            var stored = window.localStorage.getItem(ZAHIR.storageKey);
            if (stored === '1') {
                setZahirMode(true);
            } else {
                // Default: langsung aktifkan mode Zahir (karena ini yang user minta)
                setZahirMode(true);
            }
        } catch (e) {
            // Fallback: aktifkan mode Zahir
            setZahirMode(true);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.PO_ZAHIR = ZAHIR;
    window.PO_ZAHIR_toggle = function () {
        setZahirMode(!ZAHIR.enabled);
    };
})();
