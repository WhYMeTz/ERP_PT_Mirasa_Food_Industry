/**
 * =========================================================================
 * ERP PT MIRASA - PURCHASE ORDER FORM : INPUT COMFORT LAYER (JS)
 * -------------------------------------------------------------------------
 * Melengkapi form PO (Create & Edit) agar nyaman dipakai input cepat.
 * TIDAK mengubah struktur kolom / field apa pun. Hanya menambah:
 *   1. Sticky header + footer tabel & sticky kolom nomor
 *   2. Sorotan baris aktif
 *   3. Bar inspektur: ringkasan live baris yang sedang diisi
 *   4. Hint format Rupiah di bawah input harga
 *   5. Deteksi baris bermasalah & barang duplikat
 *   6. Shortcut keyboard: Alt+A (tambah baris), Ctrl+Enter (simpan)
 * =========================================================================
 */
(function () {
    'use strict';

    var PO_UX = {
        table: null,
        tbody: null,
        inspector: null,
        mode: null,      // 'create' | 'edit'
        activeRow: null,
        rafId: null,
    };

    /* ------------------------------------------------------------------
     * Helper umum
     * ------------------------------------------------------------------ */

    function fmtRupiah(n) {
        var num = Math.round(Number(n) || 0);
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    function fmtQty(n) {
        var num = Number(n) || 0;
        return num.toLocaleString('id-ID', { maximumFractionDigits: 4 });
    }

    function q(row, selector) {
        return row ? row.querySelector(selector) : null;
    }

    function val(row, selector) {
        var el = q(row, selector);
        return el ? el.value : '';
    }

    function num(row, selector) {
        return Math.max(0, parseFloat(val(row, selector)) || 0);
    }

    /* ------------------------------------------------------------------
     * Deteksi tabel (create vs edit)
     * ------------------------------------------------------------------ */

    function detectTable() {
        var createTable = document.getElementById('tableItems');
        if (createTable) {
            PO_UX.mode = 'create';
            PO_UX.table = createTable;
            PO_UX.tbody = document.getElementById('itemsContainer');
            return true;
        }

        var editTable = document.getElementById('tablePoItems');
        if (editTable) {
            PO_UX.mode = 'edit';
            PO_UX.table = editTable;
            PO_UX.tbody = document.getElementById('tbodyPoItems');
            return true;
        }

        return false;
    }

    function rowSelector() {
        return PO_UX.mode === 'edit' ? 'tr.po-item-row' : 'tr.item-row';
    }

    function getRows() {
        if (!PO_UX.tbody) return [];
        return Array.prototype.slice.call(PO_UX.tbody.querySelectorAll(rowSelector()));
    }

    /* Selector field yang dipakai di kedua halaman */
    var SEL = {
        barang: '.item-barang, .item-barang-select',
        barangHidden: 'input[type="hidden"][name$="[barang_id]"]',
        qty: '.item-qty, .input-qty',
        harga: '.item-harga, .input-harga',
        diskon: '.item-diskon, .input-diskon',
        potongan: '.item-potongan, .input-potongan',
        subtotal: '.row-subtotal',
        rowNum: '.row-num',
        satuan: '.row-satuan, .satuan-badge',
    };

    /**
     * Ambil info barang dari sebuah baris.
     * Mendukung dua bentuk:
     *  - Baris normal  : <select class="item-barang">
     *  - Baris terkunci: <input type="hidden" name="items[x][barang_id]"> + teks nama barang
     *    (dipakai pada form Edit untuk item yang sudah diterima gudang)
     */
    function getBarangInfo(row) {
        var select = q(row, SEL.barang);

        if (select && select.tagName === 'SELECT') {
            var opt = select.options ? select.options[select.selectedIndex] : null;
            return {
                id: select.value || '',
                name: (select.value && opt) ? (opt.textContent || '').trim() : '',
                locked: false,
            };
        }

        // Baris terkunci (sudah diterima): pakai hidden input + label teks
        var hidden = q(row, SEL.barangHidden);
        if (hidden && hidden.value) {
            var labelEl = hidden.parentElement ? hidden.parentElement.querySelector('div') : null;
            var label = labelEl ? (labelEl.textContent || '').trim() : '';
            return {
                id: hidden.value,
                name: label.replace(/\s+/g, ' '),
                locked: true,
            };
        }

        return { id: '', name: '', locked: false };
    }

    /* ------------------------------------------------------------------
     * 1. Siapkan area scroll + toolbar
     * ------------------------------------------------------------------ */

    function setupLayout() {
        var table = PO_UX.table;
        var wrapper = table.parentElement;

        if (wrapper && !wrapper.classList.contains('ux-table-scroll')) {
            wrapper.classList.add('ux-table-scroll');
        }

        // Sisipkan toolbar bantu di atas area tabel
        if (wrapper && !document.getElementById('poUxToolbar')) {
            var toolbar = document.createElement('div');
            toolbar.id = 'poUxToolbar';
            toolbar.className = 'ux-table-toolbar';
            toolbar.innerHTML =
                '<span style="font-weight:700;color:#334155;">Input Cepat:</span>' +
                '<button type="button" class="ux-toolbar-btn ux-toolbar-primary" id="poUxAddRow">' +
                    '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>' +
                    'Tambah Baris <span style="opacity:.6;font-weight:600;">Alt+A</span>' +
                '</button>' +
                '<button type="button" class="ux-toolbar-btn" id="poUxFocusFirst">' +
                    '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg>' +
                    'Isi Baris Kosong' +
                '</button>' +
                '<span class="ux-toolbar-spacer"></span>' +
                '<span id="poUxStats" class="ux-toolbar-stat">0 baris</span>';

            wrapper.parentNode.insertBefore(toolbar, wrapper);

            document.getElementById('poUxAddRow').addEventListener('click', function () {
                addRowSmart();
            });

            document.getElementById('poUxFocusFirst').addEventListener('click', function () {
                focusFirstEmptyRow();
            });
        }
    }

    /* ------------------------------------------------------------------
     * 2. Tambah baris & fokus
     * ------------------------------------------------------------------ */

    function addRowSmart() {
        var newRow = null;

        if (PO_UX.mode === 'create' && typeof window.addRow === 'function') {
            newRow = window.addRow(false);
        } else if (PO_UX.mode === 'edit' && typeof window.addNewItemRow === 'function') {
            window.addNewItemRow();
            var rows = getRows();
            newRow = rows[rows.length - 1] || null;
        }

        if (newRow) {
            setActiveRow(newRow);
            var barang = q(newRow, SEL.barang);
            if (barang) barang.focus();
            // Scroll baris baru ke dalam pandangan
            if (newRow.scrollIntoView) {
                newRow.scrollIntoView({ block: 'nearest' });
            }
        }
        scheduleRefresh();
    }

    function focusFirstEmptyRow() {
        var rows = getRows();
        for (var i = 0; i < rows.length; i++) {
            var info = getBarangInfo(rows[i]);
            // Lewati baris terkunci (item sudah diterima gudang)
            if (info.locked) continue;
            if (!info.id) {
                var barang = q(rows[i], SEL.barang);
                setActiveRow(rows[i]);
                if (barang) barang.focus();
                rows[i].scrollIntoView({ block: 'nearest' });
                return;
            }
        }
        // Semua baris sudah terisi -> tambah baris baru
        addRowSmart();
    }

    /* ------------------------------------------------------------------
     * 3. Sorotan baris aktif
     * ------------------------------------------------------------------ */

    function setActiveRow(row) {
        if (PO_UX.activeRow === row) return;

        if (PO_UX.activeRow) {
            PO_UX.activeRow.classList.remove('ux-active-row');
        }

        PO_UX.activeRow = row || null;

        if (PO_UX.activeRow) {
            PO_UX.activeRow.classList.add('ux-active-row');
        }

        renderInspector();
    }

    function rowFromEvent(e) {
        var t = e.target;
        if (!t || !t.closest) return null;
        var tr = t.closest(rowSelector());
        if (!tr || !PO_UX.tbody || !PO_UX.tbody.contains(tr)) return null;
        return tr;
    }

    /* ------------------------------------------------------------------
     * 4. Bar inspektur (ringkasan live baris aktif)
     * ------------------------------------------------------------------ */

    function setupInspector() {
        var placeholder = document.getElementById('poRowInspector');
        if (!placeholder) return;
        PO_UX.inspector = placeholder;
        renderInspector();
    }

    function readRowData(row) {
        var info = getBarangInfo(row);

        var satuanEl = q(row, SEL.satuan);
        var satuan = satuanEl ? (satuanEl.textContent || '').trim() : '';

        return {
            rowNum: (q(row, SEL.rowNum) || {}).textContent || '',
            barangId: info.id,
            barangName: info.name,
            locked: info.locked,
            satuan: satuan === '-' ? '' : satuan,
            qty: num(row, SEL.qty),
            harga: num(row, SEL.harga),
            diskon: num(row, SEL.diskon),
            potongan: num(row, SEL.potongan),
            subtotalText: (q(row, SEL.subtotal) || {}).textContent || 'Rp 0',
        };
    }

    function renderInspector() {
        var box = PO_UX.inspector;
        if (!box) return;

        var row = PO_UX.activeRow;

        if (!row || !document.body.contains(row)) {
            PO_UX.activeRow = null;
            box.classList.remove('ux-has-row');
            box.innerHTML =
                '<span class="ux-inspector-hint">' +
                    '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' +
                    '</svg>' +
                    '<span>Klik kolom mana pun pada tabel untuk melihat ringkasan baris yang sedang diisi.' +
                        '<span class="ux-inspector-kbd"><kbd>Alt</kbd>+<kbd>A</kbd> tambah baris &bull; <kbd>Ctrl</kbd>+<kbd>Enter</kbd> simpan</span>' +
                    '</span>' +
                '</span>';
            return;
        }

        var d = readRowData(row);
        var chips = [];

        chips.push(
            '<span class="ux-inspector-chip ux-chip-rowno">Baris ' + (d.rowNum || '?') + '</span>'
        );

        if (d.barangName) {
            chips.push(
                '<span class="ux-inspector-chip ux-chip-barang" title="' + escapeAttr(d.barangName) + '">' +
                    '<span class="ux-chip-value">' + escapeHtml(d.barangName) + '</span>' +
                '</span>'
            );
        } else {
            chips.push(
                '<span class="ux-inspector-chip ux-chip-warning">Barang belum dipilih</span>'
            );
        }

        if (d.locked) {
            chips.push(
                '<span class="ux-inspector-chip ux-chip-warning">🔒 Sudah diterima gudang</span>'
            );
        }

        if (d.qty > 0) {
            chips.push(
                '<span class="ux-inspector-chip">' +
                    '<span class="ux-chip-label">Qty</span>' +
                    '<span class="ux-chip-value">' + fmtQty(d.qty) + (d.satuan ? ' ' + escapeHtml(d.satuan) : '') + '</span>' +
                '</span>'
            );
        }

        if (d.harga > 0) {
            chips.push(
                '<span class="ux-inspector-chip">' +
                    '<span class="ux-chip-label">Harga</span>' +
                    '<span class="ux-chip-value">' + fmtRupiah(d.harga) + '</span>' +
                '</span>'
            );
        }

        if (d.diskon > 0) {
            chips.push(
                '<span class="ux-inspector-chip">' +
                    '<span class="ux-chip-label">Disc</span>' +
                    '<span class="ux-chip-value">' + fmtQty(d.diskon) + '%</span>' +
                '</span>'
            );
        }

        if (d.potongan > 0) {
            chips.push(
                '<span class="ux-inspector-chip">' +
                    '<span class="ux-chip-label">Potongan</span>' +
                    '<span class="ux-chip-value">' + fmtRupiah(d.potongan) + '</span>' +
                '</span>'
            );
        }

        chips.push(
            '<span class="ux-inspector-chip ux-chip-subtotal">' +
                '<span class="ux-chip-label">Subtotal</span>' +
                '<span class="ux-chip-value">' + escapeHtml(d.subtotalText.trim()) + '</span>' +
            '</span>'
        );

        box.classList.add('ux-has-row');
        box.innerHTML = '<div class="ux-inspector-content">' + chips.join('') + '</div>';
    }

    /* ------------------------------------------------------------------
     * 4b. Auto-scroll: pastikan sel yang sedang diisi selalu terlihat
     * ------------------------------------------------------------------ */

    function scrollCellIntoView(el) {
        if (!el || !PO_UX.table) return;
        var wrapper = PO_UX.table.parentElement;
        if (!wrapper) return;

        var cell = el.closest ? el.closest('td, th') : null;
        var target = cell || el;

        var wRect = wrapper.getBoundingClientRect();
        var cRect = target.getBoundingClientRect();

        // Hanya perlu scroll bila kontainer benar-benar bisa di-scroll
        if (wrapper.scrollWidth <= wrapper.clientWidth + 2) return;

        var pad = 24;

        // Geser horizontal bila kolom aktif keluar dari area pandang
        if (cRect.left < wRect.left + pad) {
            wrapper.scrollLeft -= (wRect.left + pad - cRect.left);
        } else if (cRect.right > wRect.right - pad) {
            wrapper.scrollLeft += (cRect.right - (wRect.right - pad));
        }

        // Geser vertikal bila baris aktif keluar dari area pandang
        if (wrapper.scrollHeight > wrapper.clientHeight + 2) {
            var headH = 0;
            var thead = PO_UX.table.querySelector('thead');
            if (thead) headH = thead.getBoundingClientRect().height || 0;

            if (cRect.top < wRect.top + headH + pad) {
                wrapper.scrollTop -= (wRect.top + headH + pad - cRect.top);
            } else if (cRect.bottom > wRect.bottom - pad) {
                wrapper.scrollTop += (cRect.bottom - (wRect.bottom - pad));
            }
        }
    }

    /* ------------------------------------------------------------------
     * 5. Hint format Rupiah pada input harga
     * ------------------------------------------------------------------ */

    function setupMoneyHints() {
        getRows().forEach(function (row) {
            attachMoneyHint(row);
        });
    }

    function attachMoneyHint(row) {
        var harga = q(row, SEL.harga);
        if (!harga || !harga.parentElement) return;

        var hint = harga.parentElement.querySelector('.ux-money-hint');
        if (!hint) {
            hint = document.createElement('span');
            hint.className = 'ux-money-hint';
            harga.parentElement.appendChild(hint);
        }
        updateMoneyHint(row);
    }

    function updateMoneyHint(row) {
        var harga = q(row, SEL.harga);
        if (!harga) return;
        var hint = harga.parentElement ? harga.parentElement.querySelector('.ux-money-hint') : null;
        if (!hint) return;

        var v = parseFloat(harga.value) || 0;

        if (!harga.value || v <= 0) {
            hint.textContent = '';
            hint.classList.add('ux-money-zero');
            return;
        }

        hint.classList.remove('ux-money-zero');

        // Tampilkan nilai rupiah penuh supaya digit nol tidak salah baca
        var formatted = Math.round(v).toLocaleString('id-ID');
        var qty = num(row, SEL.qty);

        if (qty > 0) {
            hint.textContent = 'Rp ' + formatted + ' × ' + fmtQty(qty);
        } else {
            hint.textContent = 'Rp ' + formatted;
        }
    }

    /* ------------------------------------------------------------------
     * 6. Validasi ringan: baris bermasalah & duplikat
     * ------------------------------------------------------------------ */

    function refreshRowStates() {
        var rows = getRows();
        var seen = {};
        var problemCount = 0;

        // Pass 1: kumpulkan barang id untuk deteksi duplikat
        rows.forEach(function (row) {
            var info = getBarangInfo(row);
            if (!info.id) return;
            seen[info.id] = (seen[info.id] || 0) + 1;
        });

        // Pass 2: tandai baris
        rows.forEach(function (row) {
            row.classList.remove('ux-row-invalid', 'ux-row-duplicate');

            var info = getBarangInfo(row);
            var qtyEl = q(row, SEL.qty);
            var qty = qtyEl ? parseFloat(qtyEl.value) || 0 : 0;

            // Baris terkunci (item sudah diterima) tidak perlu divalidasi
            var isInvalid = !info.locked && (!info.id || qty <= 0);
            if (isInvalid) {
                row.classList.add('ux-row-invalid');
                problemCount++;
            }

            // Badge duplikat hanya untuk baris yang bisa diubah
            var cell = info.locked
                ? null
                : (q(row, SEL.barang) ? q(row, SEL.barang).parentElement : null);

            if (cell) {
                var oldBadge = cell.querySelector('.ux-duplicate-badge');
                if (oldBadge) oldBadge.remove();

                if (info.id && seen[info.id] > 1) {
                    row.classList.add('ux-row-duplicate');
                    var badge = document.createElement('span');
                    badge.className = 'ux-duplicate-badge';
                    badge.textContent = '⚠ Barang duplikat (' + seen[info.id] + ' baris)';
                    cell.appendChild(badge);
                }
            }
        });

        // Statistik toolbar
        var stats = document.getElementById('poUxStats');
        if (stats) {
            var text = rows.length + ' baris';
            if (problemCount > 0) {
                text += ' · ' + problemCount + ' perlu dilengkapi';
            }
            stats.textContent = text;
            stats.style.color = problemCount > 0 ? '#b45309' : '#0f172a';
        }
    }

    /* ------------------------------------------------------------------
     * 7. Refresh terpusat (di-debounce pakai rAF)
     * ------------------------------------------------------------------ */

    function scheduleRefresh() {
        if (PO_UX.rafId) return;
        PO_UX.rafId = window.requestAnimationFrame(function () {
            PO_UX.rafId = null;
            refresh();
        });
    }

    function refresh() {
        if (!PO_UX.table) return;
        refreshRowStates();
        getRows().forEach(updateMoneyHint);
        renderInspector();
    }

    /* ------------------------------------------------------------------
     * 8. Patch fungsi kalkulasi bawaan agar inspektur ikut ter-update
     * ------------------------------------------------------------------ */

    function patchCalculator(fnName) {
        var original = window[fnName];
        if (typeof original !== 'function' || original.__poUxPatched) return;

        var patched = function () {
            var result = original.apply(this, arguments);
            scheduleRefresh();
            return result;
        };
        patched.__poUxPatched = true;
        window[fnName] = patched;
    }

    /* ------------------------------------------------------------------
     * 9. Event binding
     * ------------------------------------------------------------------ */

    function bindEvents() {
        var root = PO_UX.table;

        // Fokus masuk -> jadikan baris aktif
        root.addEventListener('focusin', function (e) {
            var row = rowFromEvent(e);
            if (!row) return;
            setActiveRow(row);

            // Pastikan sel yang difokus selalu terlihat (tabel lebar 12 kolom)
            scrollCellIntoView(e.target);

            // Input angka: pilih seluruh isi hanya bila masih kosong / bernilai 0.
            // Kalau sudah ada nilai, biarkan kursor normal agar bisa koreksi digit.
            var t = e.target;
            if (t && t.tagName === 'INPUT' && t.type === 'number') {
                var v = (t.value || '').trim();
                if (v === '' || parseFloat(v) === 0) {
                    try { t.select(); } catch (err) { /* noop */ }
                }
            }
        }, true);

        // Klik di mana pun dalam baris -> baris aktif
        root.addEventListener('click', function (e) {
            var row = rowFromEvent(e);
            if (row) setActiveRow(row);
        }, true);

        // Ketikan -> update hint & inspektur
        root.addEventListener('input', function (e) {
            var row = rowFromEvent(e);
            if (!row) return;
            setActiveRow(row);
            updateMoneyHint(row);
            scheduleRefresh();
        }, true);

        // Perubahan select (barang / pajak) -> update
        root.addEventListener('change', function (e) {
            var row = rowFromEvent(e);
            if (!row) return;
            setActiveRow(row);
            scheduleRefresh();
        }, true);

        // Shortcut keyboard global
        document.addEventListener('keydown', function (e) {
            // Alt + A : tambah baris
            if (e.altKey && !e.ctrlKey && !e.metaKey && (e.key === 'a' || e.key === 'A')) {
                var active = document.activeElement;
                var tag = active ? active.tagName : '';
                // Jangan ganggu saat mengetik di textarea / input teks biasa
                if (tag === 'TEXTAREA') return;
                if (tag === 'INPUT' && active.type === 'text') return;

                e.preventDefault();
                addRowSmart();
                return;
            }

            // Ctrl + Enter : submit form
            if (e.ctrlKey && e.key === 'Enter') {
                var form = document.getElementById('formPo') || (PO_UX.table ? PO_UX.table.closest('form') : null);
                if (form) {
                    e.preventDefault();
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            }
        });

        // Pantau penambahan / penghapusan baris (template di-clone via JS)
        if (window.MutationObserver && PO_UX.tbody) {
            var observer = new MutationObserver(function (mutations) {
                var changed = mutations.some(function (m) {
                    return m.addedNodes.length > 0 || m.removedNodes.length > 0;
                });
                if (changed) {
                    getRows().forEach(attachMoneyHint);
                    scheduleRefresh();
                }
            });
            observer.observe(PO_UX.tbody, { childList: true });
        }
    }

    /* ------------------------------------------------------------------
     * 10. Escape helper
     * ------------------------------------------------------------------ */

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeAttr(text) {
        return escapeHtml(text);
    }

    /* ------------------------------------------------------------------
     * 11. Boot
     * ------------------------------------------------------------------ */

    function boot() {
        if (!detectTable()) return;

        setupLayout();
        setupInspector();
        setupMoneyHints();
        bindEvents();

        // Patch kalkulator bawaan (create: calculateGrandTotal, edit: calculateAllTotals)
        patchCalculator('calculateGrandTotal');
        patchCalculator('calculateAllTotals');
        patchCalculator('calculateSubtotal');
        patchCalculator('calculateRow');

        refresh();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Ekspos untuk debugging / integrasi lanjutan
    window.PO_UX = PO_UX;
    window.PO_UX_refresh = scheduleRefresh;
})();
