/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER : FIT-TO-SCREEN LAYOUT (JS)
 * -------------------------------------------------------------------------
 * Membuat form input PO + tabel item muat dalam 1 layar:
 *   1. Menandai panel header (po-header-card) & panel tabel (po-items-card)
 *   2. Menghitung tinggi area kerja berdasarkan viewport
 *   3. Menyisipkan ringkasan header (untuk Mode Fokus Tabel)
 *   4. Tombol "Fokus Tabel" -> lipat header jadi 1 baris ringkasan
 *   5. Menjaga tinggi tetap benar saat resize / sidebar dibuka-tutup
 *
 * TIDAK menghapus / menyembunyikan permanen input apa pun.
 * Semua field tetap ada & tetap tersubmit.
 * =========================================================================
 */
(function () {
    'use strict';

    var FIT = {
        enabled: false,
        focusMode: false,
        grid: null,
        headerCard: null,
        itemsCard: null,
        summaryEl: null,
        observer: null,
        resizeTimer: null,
        storageKey: 'poFitFocusMode',
    };

    var MIN_HEIGHT = 420;   // tinggi minimum area kerja (px)
    var BOTTOM_GAP = 24;    // ruang aman di bawah

    /* ------------------------------------------------------------------
     * Helper
     * ------------------------------------------------------------------ */

    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /* ------------------------------------------------------------------
     * 1. Deteksi panel & tandai class
     * ------------------------------------------------------------------ */

    function detectPanels() {
        FIT.grid = qs('.order-station-grid');
        if (!FIT.grid) return false;

        var mainCol = FIT.grid.children[0];
        if (!mainCol) return false;

        var cards = Array.prototype.slice.call(mainCol.children).filter(function (el) {
            return el.classList && el.classList.contains('card');
        });

        if (cards.length < 1) return false;

        // Panel header = kartu pertama (berisi Informasi Dokumen)
        FIT.headerCard = cards[0];
        FIT.headerCard.classList.add('po-header-card');

        // Panel tabel item = kartu yang memuat tabel item
        for (var i = 1; i < cards.length; i++) {
            if (cards[i].querySelector('#tableItems, #tablePoItems')) {
                FIT.itemsCard = cards[i];
                break;
            }
        }

        if (!FIT.itemsCard) {
            // Fallback: kartu terakhir
            FIT.itemsCard = cards[cards.length - 1];
        }
        FIT.itemsCard.classList.add('po-items-card');

        // Tandai blok-blok isi header agar bisa dipadatkan / dilipat
        markHeaderBlocks();

        return true;
    }

    function markHeaderBlocks() {
        var card = FIT.headerCard;
        if (!card || card.dataset.poBlocksMarked === '1') return;

        // Tandai SEMUA anak langsung kecuali card-header sebagai blok header.
        // Termasuk wrapper padding (create) maupun blok-blok terpisah (edit).
        Array.prototype.slice.call(card.children).forEach(function (child) {
            if (child.classList.contains('card-header')) return;
            if (child.classList.contains('po-hdr-summary')) return;
            child.classList.add('po-hdr-block');
        });

        // Tandai grid di dalam blok agar gap bisa dirapatkan
        Array.prototype.slice.call(card.querySelectorAll('div[style*="grid-template-columns"]')).forEach(function (g) {
            g.classList.add('po-hdr-grid');
        });

        card.dataset.poBlocksMarked = '1';
    }

    /* ------------------------------------------------------------------
     * 2. Ringkasan header (untuk mode fokus tabel)
     * ------------------------------------------------------------------ */

    function buildSummary() {
        if (!FIT.headerCard || FIT.summaryEl) return;

        var wrap = document.createElement('div');
        wrap.className = 'po-hdr-summary';
        wrap.id = 'poHdrSummary';

        var header = FIT.headerCard.querySelector('.card-header');
        if (header && header.parentNode) {
            header.parentNode.insertBefore(wrap, header.nextSibling);
        } else {
            FIT.headerCard.appendChild(wrap);
        }

        FIT.summaryEl = wrap;
        updateSummary();
    }

    function readHeaderValue(selectors) {
        for (var i = 0; i < selectors.length; i++) {
            var el = qs(selectors[i]);
            if (!el) continue;

            if (el.tagName === 'SELECT') {
                var opt = el.options ? el.options[el.selectedIndex] : null;
                if (el.value && opt) return (opt.textContent || '').trim();
                continue;
            }

            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                if (el.value) return el.value;
                continue;
            }

            if (el.textContent && el.textContent.trim()) {
                return el.textContent.trim();
            }
        }
        return '';
    }

    function updateSummary() {
        if (!FIT.summaryEl) return;

        // Nama supplier: create pakai #selected_supplier_card, edit pakai #supplier_id
        var supplierName = '';

        var supNameEl = qs('#disp_sup_name');
        if (supNameEl && supNameEl.textContent.trim()) {
            supplierName = supNameEl.textContent.trim();
        } else {
            supplierName = readHeaderValue(['#supplier_id']);
        }

        var gudangName = readHeaderValue(['#gudang_id']);
        var approverName = readHeaderValue(['#approver_id']);
        var poDate = readHeaderValue(['#po_tgl']);

        // Ambil nilai dari hidden input nomor PO bila ada
        var poNo = readHeaderValue(['#po_no']);

        var chips = [];

        if (poNo) {
            chips.push(chip('No. PO', poNo));
        }
        if (poDate) {
            chips.push(chip('Tgl', poDate));
        }
        if (supplierName) {
            chips.push(chip('Supplier', supplierName));
        }
        if (gudangName) {
            chips.push(chip('Gudang', gudangName));
        }
        if (approverName) {
            chips.push(chip('Approver', approverName));
        }

        if (chips.length === 0) {
            chips.push('<span class="po-hdr-summary-chip" style="color:#94a3b8;">Lengkapi informasi dokumen di atas</span>');
        }

        FIT.summaryEl.innerHTML = chips.join('');
    }

    function chip(label, value) {
        return '<span class="po-hdr-summary-chip">' +
                    '<span class="po-sum-lbl">' + escapeHtml(label) + '</span>' +
                    '<strong>' + escapeHtml(value) + '</strong>' +
               '</span>';
    }

    /* ------------------------------------------------------------------
     * 3. Hitung tinggi area kerja
     * ------------------------------------------------------------------ */

    function recalcHeight() {
        if (!FIT.grid) return;

        if (window.innerWidth < 1200) {
            document.body.classList.remove('po-fit-on');
            document.body.style.removeProperty('--po-fit-h');
            return;
        }

        var top = FIT.grid.getBoundingClientRect().top + window.scrollY;

        // Hitung ulang dari posisi dokumen (bukan viewport) agar stabil
        var available = window.innerHeight - top - BOTTOM_GAP;

        if (available < MIN_HEIGHT) {
            // Layar terlalu pendek: matikan mode fit agar tidak sesak
            document.body.classList.remove('po-fit-on');
            document.body.style.removeProperty('--po-fit-h');
            return;
        }

        document.body.classList.add('po-fit-on');
        document.body.style.setProperty('--po-fit-h', Math.round(available) + 'px');
        FIT.enabled = true;
    }

    /* ------------------------------------------------------------------
     * 4. Mode Fokus Tabel
     * ------------------------------------------------------------------ */

    function setFocusMode(on) {
        FIT.focusMode = !!on;
        document.body.classList.toggle('po-fit-min', FIT.focusMode);

        var btn = document.getElementById('poUxFocusToggle');
        if (btn) {
            btn.classList.toggle('is-active', FIT.focusMode);
            var label = btn.querySelector('.ux-focus-label');
            if (label) {
                label.textContent = FIT.focusMode ? 'Tampilkan Info Dokumen' : 'Fokus Tabel';
            }
        }

        if (FIT.focusMode) {
            updateSummary();
        }

        // Simpan preferensi
        try {
            window.localStorage.setItem(FIT.storageKey, FIT.focusMode ? '1' : '0');
        } catch (e) { /* noop */ }

        recalcHeight();
    }

    function injectFocusButton() {
        var toolbar = document.getElementById('poUxToolbar');
        if (!toolbar || document.getElementById('poUxFocusToggle')) return;

        var spacer = toolbar.querySelector('.ux-toolbar-spacer');

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'poUxFocusToggle';
        btn.className = 'ux-toolbar-btn ux-focus-toggle';
        btn.title = 'Lipat panel informasi dokumen agar tabel lebih lapang';
        btn.innerHTML =
            '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>' +
            '</svg>' +
            '<span class="ux-focus-label">Fokus Tabel</span>';

        btn.addEventListener('click', function () {
            setFocusMode(!FIT.focusMode);
        });

        if (spacer && spacer.parentNode) {
            spacer.parentNode.insertBefore(btn, spacer);
        } else {
            toolbar.appendChild(btn);
        }
    }

    /* ------------------------------------------------------------------
     * 5. Sinkronisasi ringkasan saat header diubah
     * ------------------------------------------------------------------ */

    function bindSummarySync() {
        if (!FIT.headerCard) return;

        var handler = function () {
            updateSummary();
        };

        FIT.headerCard.addEventListener('input', handler, true);
        FIT.headerCard.addEventListener('change', handler, true);
    }

    /* ------------------------------------------------------------------
     * 6. Boot
     * ------------------------------------------------------------------ */

    function boot() {
        if (!detectPanels()) return;

        buildSummary();
        bindSummarySync();
        injectFocusButton();
        recalcHeight();

        // Pulihkan preferensi fokus tabel
        try {
            if (window.localStorage.getItem(FIT.storageKey) === '1') {
                setFocusMode(true);
            }
        } catch (e) { /* noop */ }

        // Recalc saat resize (debounced)
        window.addEventListener('resize', function () {
            if (FIT.resizeTimer) window.clearTimeout(FIT.resizeTimer);
            FIT.resizeTimer = window.setTimeout(function () {
                recalcHeight();
                updateSummary();
            }, 120);
        });

        // Recalc saat sidebar dilipat / layout berubah
        if (window.MutationObserver && document.body) {
            FIT.observer = new MutationObserver(function () {
                if (FIT.resizeTimer) window.clearTimeout(FIT.resizeTimer);
                FIT.resizeTimer = window.setTimeout(recalcHeight, 150);
            });
            FIT.observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class', 'style'],
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.PO_FIT = FIT;
    window.PO_FIT_recalc = recalcHeight;
})();
