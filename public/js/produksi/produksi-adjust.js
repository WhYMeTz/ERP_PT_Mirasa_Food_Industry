/**
 * JavaScript Worksheet Penyesuaian HPP & Biaya Manufaktur
 * Path: public/js/produksi/produksi-adjust.js
 * PT Mirasa Food Industry - Standar Enterprise
 */

(function () {
    'use strict';

    /**
     * Filter Kategori Lembar Kerja Komponen Biaya
     */
    function filterAdjCategory(category, btnEl) {
        document.querySelectorAll('.adj-nav-btn').forEach(btn => btn.classList.remove('active'));
        if (btnEl) {
            btnEl.classList.add('active');
        }

        const rows = document.querySelectorAll('.adj-item-row');
        const headers = document.querySelectorAll('.adj-group-row');
        const subtotals = document.querySelectorAll('.adj-group-subtotal-row');

        if (category === 'all') {
            rows.forEach(r => r.style.display = '');
            headers.forEach(h => h.style.display = '');
            subtotals.forEach(s => s.style.display = '');
        } else {
            rows.forEach(r => {
                r.style.display = (r.getAttribute('data-group') === category) ? '' : 'none';
            });
            headers.forEach(h => {
                h.style.display = (h.getAttribute('data-group-header') === category) ? '' : 'none';
            });
            subtotals.forEach(s => {
                s.style.display = (s.getAttribute('data-group-subtotal') === category) ? '' : 'none';
            });
        }
    }
    window.filterAdjCategory = filterAdjCategory;

    /**
     * Reset Nilai 1 Field Tertentu ke Nilai Awal Dokumen
     */
    function resetAdjField(fieldKey) {
        const cfg = window.adjProdConfig || {};
        const initialVal = cfg[fieldKey] !== undefined ? cfg[fieldKey] : 0;
        const input = document.getElementById('input_adj_' + fieldKey);
        if (input) {
            input.value = Math.round(initialVal);
            recalculateHppSimulation();
        }
    }
    window.resetAdjField = resetAdjField;

    /**
     * Reset Seluruh 20 Input Komponen ke Nilai Orisinal
     */
    function resetAllAdjFields() {
        const cfg = window.adjProdConfig || {};
        document.querySelectorAll('.adj-calc-input').forEach(input => {
            const key = input.getAttribute('data-key');
            if (key && cfg[key] !== undefined) {
                input.value = Math.round(cfg[key]);
            }
        });
        recalculateHppSimulation();
    }
    window.resetAllAdjFields = resetAllAdjFields;

    /**
     * Kalkulasi Live Simulasi HPP Baru pada Lembar Kerja Penyesuaian
     */
    function recalculateHppSimulation() {
        const cfg = window.adjProdConfig || {};
        const wipQty = parseFloat(cfg.totalWipQty) || 0;
        const biayaAwal = parseFloat(cfg.totalBiayaAwal) || 0;
        const hppAwal = parseFloat(cfg.hppAwal) || 0;

        let deltaTotal = 0;
        let changedCount = 0;

        // Subtotal penampung
        let subtotalBbl = 0;
        let subtotalKms = 0;
        let subtotalEng = 0;
        let subtotalFoh = 0;

        const bblKeys = ['singkong_nilai', 'minyak_nilai', 'bumbu_nilai'];
        const kmsKeys = ['karton_baru_nilai', 'karton_bekas_nilai', 'plastik_hd_nilai', 'lakban_besar_nilai', 'lakban_kecil_nilai', 'tali_rafia_nilai'];
        const engKeys = ['tk_total_nilai', 'cng_nilai', 'listrik_air_telp_nilai'];

        document.querySelectorAll('.adj-calc-input').forEach(input => {
            const key = input.getAttribute('data-key');
            if (!key) return;

            const newVal = parseFloat(input.value) || 0;
            const initialVal = parseFloat(cfg[key]) || 0;
            const delta = newVal - initialVal;

            // Klasifikasi subtotal
            if (bblKeys.includes(key)) {
                subtotalBbl += newVal;
            } else if (kmsKeys.includes(key)) {
                subtotalKms += newVal;
            } else if (engKeys.includes(key)) {
                subtotalEng += newVal;
            } else {
                subtotalFoh += newVal;
            }

            // Update badge per baris
            const badge = document.getElementById('badge_delta_' + key);
            const resetBtn = document.getElementById('btn_reset_' + key);
            const roundedDelta = Math.round(delta);

            if (badge) {
                if (roundedDelta > 0) {
                    badge.className = 'adj-variance-badge adj-variance-up';
                    badge.textContent = '+Rp ' + formatRupiahWhole(roundedDelta);
                } else if (roundedDelta < 0) {
                    badge.className = 'adj-variance-badge adj-variance-down';
                    badge.textContent = '-Rp ' + formatRupiahWhole(Math.abs(roundedDelta));
                } else {
                    badge.className = 'adj-variance-badge adj-variance-zero';
                    badge.textContent = 'Rp 0';
                }
            }

            if (resetBtn) {
                resetBtn.style.display = Math.abs(roundedDelta) > 0 ? 'inline-flex' : 'none';
            }

            deltaTotal += delta;
            if (Math.abs(roundedDelta) > 0) {
                changedCount++;
            }
        });

        // Helper update badge variance
        function updateSubtotalBadge(badgeId, delta) {
            const badge = document.getElementById(badgeId);
            if (!badge) return;
            const rounded = Math.round(delta);
            if (rounded > 0) {
                badge.className = 'adj-variance-badge adj-variance-up';
                badge.textContent = '+Rp ' + formatRupiahWhole(rounded);
            } else if (rounded < 0) {
                badge.className = 'adj-variance-badge adj-variance-down';
                badge.textContent = '-Rp ' + formatRupiahWhole(Math.abs(rounded));
            } else {
                badge.className = 'adj-variance-badge adj-variance-zero';
                badge.textContent = 'Rp 0';
            }
        }

        // Hitung subtotal awal per grup
        let bblAwal = 0, kmsAwal = 0, engAwal = 0, fohAwal = 0;
        bblKeys.forEach(k => bblAwal += (parseFloat(cfg[k]) || 0));
        kmsKeys.forEach(k => kmsAwal += (parseFloat(cfg[k]) || 0));
        engKeys.forEach(k => engAwal += (parseFloat(cfg[k]) || 0));
        document.querySelectorAll('.adj-calc-input').forEach(input => {
            const key = input.getAttribute('data-key');
            if (key && !bblKeys.includes(key) && !kmsKeys.includes(key) && !engKeys.includes(key)) {
                fohAwal += (parseFloat(cfg[key]) || 0);
            }
        });

        // Update nilai subtotal baru & badge selisih per grup
        const bblTxt = document.getElementById('subtotal_bbl_text');
        const kmsTxt = document.getElementById('subtotal_kms_text');
        const engTxt = document.getElementById('subtotal_eng_text');
        const fohTxt = document.getElementById('subtotal_foh_text');

        if (bblTxt) bblTxt.textContent = 'Rp ' + formatRupiahWhole(subtotalBbl);
        if (kmsTxt) kmsTxt.textContent = 'Rp ' + formatRupiahWhole(subtotalKms);
        if (engTxt) engTxt.textContent = 'Rp ' + formatRupiahWhole(subtotalEng);
        if (fohTxt) fohTxt.textContent = 'Rp ' + formatRupiahWhole(subtotalFoh);

        updateSubtotalBadge('subtotal_bbl_delta', subtotalBbl - bblAwal);
        updateSubtotalBadge('subtotal_kms_delta', subtotalKms - kmsAwal);
        updateSubtotalBadge('subtotal_eng_delta', subtotalEng - engAwal);
        updateSubtotalBadge('subtotal_foh_delta', subtotalFoh - fohAwal);

        // Hitung biaya baru dan HPP baru
        const biayaBaru = Math.round((biayaAwal + deltaTotal) * 100) / 100;
        const hppBaru = wipQty > 0 ? Math.round((biayaBaru / wipQty) * 100) / 100 : 0;
        const deltaHpp = Math.round((hppBaru - hppAwal) * 100) / 100;
        const totalValuasiDampak = Math.round(deltaHpp * wipQty);

        // Update panel ringkasan eksekutif
        const hppEl = document.getElementById('simulasiHppBaruText');
        const badgeEl = document.getElementById('simulasiSelisihHppBadge');
        const totalEl = document.getElementById('simulasiTotalBiayaBaruText');
        const netEl = document.getElementById('simulasiNetSelisihBiayaText');
        const statusEl = document.getElementById('adjFooterStatus');
        const countBadge = document.getElementById('adjCountBadge');
        const valuasiEl = document.getElementById('simulasiValuasiStokText');

        if (hppEl) {
            hppEl.textContent = 'Rp ' + formatRupiahDecimal(hppBaru);
        }
        if (totalEl) {
            totalEl.textContent = 'Rp ' + formatRupiahWhole(biayaBaru);
        }
        if (netEl) {
            const roundedDeltaTotal = Math.round(deltaTotal);
            if (roundedDeltaTotal > 0) {
                netEl.className = 'adj-variance-badge adj-variance-up';
                netEl.textContent = '+Rp ' + formatRupiahWhole(roundedDeltaTotal);
            } else if (roundedDeltaTotal < 0) {
                netEl.className = 'adj-variance-badge adj-variance-down';
                netEl.textContent = '-Rp ' + formatRupiahWhole(Math.abs(roundedDeltaTotal));
            } else {
                netEl.className = 'adj-variance-badge adj-variance-zero';
                netEl.textContent = 'Rp 0';
            }
        }

        if (badgeEl) {
            if (deltaHpp > 0.001) {
                badgeEl.className = 'adj-variance-badge adj-variance-up';
                badgeEl.textContent = 'Selisih: +Rp ' + formatRupiahDecimal(deltaHpp) + ' / kg';
            } else if (deltaHpp < -0.001) {
                badgeEl.className = 'adj-variance-badge adj-variance-down';
                badgeEl.textContent = 'Selisih: -Rp ' + formatRupiahDecimal(Math.abs(deltaHpp)) + ' / kg';
            } else {
                badgeEl.className = 'adj-variance-badge adj-variance-zero';
                badgeEl.textContent = 'Selisih: Rp 0,00 / kg';
            }
        }

        if (valuasiEl) {
            if (totalValuasiDampak > 0) {
                valuasiEl.textContent = '+Rp ' + formatRupiahWhole(totalValuasiDampak);
                valuasiEl.style.color = '#b45309';
            } else if (totalValuasiDampak < 0) {
                valuasiEl.textContent = '-Rp ' + formatRupiahWhole(Math.abs(totalValuasiDampak));
                valuasiEl.style.color = '#047857';
            } else {
                valuasiEl.textContent = 'Rp 0';
                valuasiEl.style.color = '#64748b';
            }
        }

        if (countBadge) {
            countBadge.style.display = 'inline-block';
            if (changedCount === 0) {
                countBadge.textContent = '0 Komponen Diubah';
                countBadge.style.color = '#64748b';
                countBadge.style.background = '#f1f5f9';
                countBadge.style.borderColor = '#e2e8f0';
            } else {
                countBadge.textContent = changedCount + ' Komponen Diubah';
                countBadge.style.color = '#0369a1';
                countBadge.style.background = '#e0f2fe';
                countBadge.style.borderColor = '#7dd3fc';
            }
        }

        if (statusEl) {
            if (changedCount === 0) {
                statusEl.textContent = 'Nilai awal dokumen aktif';
                statusEl.style.color = '#64748b';
                statusEl.style.fontWeight = '500';
            } else {
                const tanda = deltaTotal >= 0 ? '+' : '-';
                statusEl.textContent = `Net Dampak: ${tanda}Rp ${formatRupiahWhole(Math.abs(deltaTotal))}`;
                statusEl.style.color = '#059669';
                statusEl.style.fontWeight = '700';
            }
        }
    }
    window.recalculateHppSimulation = recalculateHppSimulation;

    function formatRupiahWhole(num) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(num));
    }

    function formatRupiahDecimal(num) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }

    /**
     * Inisialisasi saat DOM siap
     */
    document.addEventListener('DOMContentLoaded', function () {
        // Event input real-time untuk seluruh field kalkulasi
        document.querySelectorAll('.adj-calc-input').forEach(input => {
            input.addEventListener('input', recalculateHppSimulation);
            input.addEventListener('change', recalculateHppSimulation);
        });

        // Trigger kalkulasi awal untuk konsistensi nilai awal
        recalculateHppSimulation();

        // Validasi Submit Form
        const form = document.getElementById('formAdjustHppWorksheet');
        if (form) {
            form.addEventListener('submit', function (e) {
                const alasanInput = document.getElementById('alasan_txt');
                const alasanVal = alasanInput ? alasanInput.value.trim() : '';

                if (alasanVal.length < 5) {
                    e.preventDefault();
                    alert('Mohon isi Alasan Penyesuaian / Dasar Dokumen minimal 5 karakter untuk kelengkapan audit trail akuntansi.');
                    if (alasanInput) {
                        alasanInput.focus();
                    }
                    return false;
                }

                const submitBtn = document.getElementById('btnSubmitAdjust');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="margin-right: 6px; display: inline-block;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Menyimpan Berita Acara...</span>
                    `;
                }
            });
        }
    });
})();
