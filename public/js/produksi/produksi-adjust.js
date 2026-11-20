/**
 * JavaScript Worksheet Penyesuaian HPP & Biaya Manufaktur
 * Path: public/js/produksi/produksi-adjust.js
 * PT Mirasa Food Industry - Standar Enterprise
 */

(function () {
    'use strict';

    /**
     * Konfigurasi Pemetaan Kuantitas Input ke Field Nominal & Tarif Satuan
     */
    const qtyConfigMap = {
        // 1. Bahan Baku Langsung (BBL)
        'singkong_qty': { configKey: 'singkongQtyAwal', nominalKey: 'singkong_nilai', unit: 'Kg', precision: 2, fallbackPrice: 4500 },
        'minyak_qty': { configKey: 'minyakQtyAwal', nominalKey: 'minyak_nilai', unit: 'Kg', precision: 2, fallbackPrice: 17500 },
        'bumbu_qty': { configKey: 'bumbuQtyAwal', nominalKey: 'bumbu_nilai', unit: 'Kg', precision: 2, fallbackPrice: 35000 },

        // 2. Kemasan & Packing (KMS)
        'karton_baru_qty': { configKey: 'kartonBaruQtyAwal', nominalKey: 'karton_baru_nilai', unit: 'Pcs', precision: 0, fallbackPrice: 10000 },
        'karton_bekas_qty': { configKey: 'kartonBekasQtyAwal', nominalKey: 'karton_bekas_nilai', unit: 'Pcs', precision: 0, fallbackPrice: 5000 },
        'plastik_hd_qty': { configKey: 'plastikHdQtyAwal', nominalKey: 'plastik_hd_nilai', unit: 'Roll', precision: 2, fallbackPrice: 29400 },
        'lakban_besar_qty': { configKey: 'lakbanBesarQtyAwal', nominalKey: 'lakban_besar_nilai', unit: 'Roll', precision: 2, fallbackPrice: 15000 },
        'lakban_kecil_qty': { configKey: 'lakbanKecilQtyAwal', nominalKey: 'lakban_kecil_nilai', unit: 'Roll', precision: 2, fallbackPrice: 6140 },
        'tali_rafia_qty': { configKey: 'taliRafiaQtyAwal', nominalKey: 'tali_rafia_nilai', unit: 'Roll', precision: 2, fallbackPrice: 10000 },

        // 3. Tenaga Kerja & Energi
        'tk_jumlah_org': { configKey: 'tkJumlahOrgAwal', nominalKey: 'tk_total_nilai', unit: 'Org', precision: 0, fallbackPrice: 150000 },
        'cng_mmbtu': { configKey: 'cngMmbtuAwal', nominalKey: 'cng_nilai', unit: 'MMBTU', precision: 4, fallbackPrice: 450000 },

        // 4. Overhead APD (FOH)
        'sarung_tangan_plastik_qty': { configKey: 'sarungTanganPlastikQtyAwal', nominalKey: 'sarung_tangan_plastik_nilai', unit: 'Box', precision: 0, fallbackPrice: 25000 },
        'sarung_tangan_kain_qty': { configKey: 'sarungTanganKainQtyAwal', nominalKey: 'sarung_tangan_kain_nilai', unit: 'Psg', precision: 0, fallbackPrice: 5000 },
    };

    /**
     * Dapatkan Harga / Tarif Satuan Komponen Masukan
     */
    function getUnitPrice(key) {
        const mapping = qtyConfigMap[key];
        if (!mapping) return 0;
        const cfg = window.adjProdConfig || {};

        if (cfg.unitPrices && cfg.unitPrices[key] !== undefined) {
            return parseFloat(cfg.unitPrices[key]) || 0;
        }

        const initQty = parseFloat(cfg[mapping.configKey]) || 0;
        const initNominal = parseFloat(cfg[mapping.nominalKey]) || 0;
        if (initQty > 0 && initNominal > 0) {
            return initNominal / initQty;
        }
        return mapping.fallbackPrice || 0;
    }
    window.getUnitPrice = getUnitPrice;

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
        const subgroups = document.querySelectorAll('.adj-subgroup-row');

        if (category === 'all') {
            rows.forEach(r => {
                r.style.display = (r.getAttribute('data-hidden-initial') === 'true') ? 'none' : '';
            });
            headers.forEach(h => h.style.display = '');
            subtotals.forEach(s => s.style.display = '');
            subgroups.forEach(sg => sg.style.display = '');
        } else {
            rows.forEach(r => {
                if (r.getAttribute('data-hidden-initial') === 'true') {
                    r.style.display = 'none';
                } else {
                    r.style.display = (r.getAttribute('data-group') === category) ? '' : 'none';
                }
            });
            headers.forEach(h => {
                h.style.display = (h.getAttribute('data-group-header') === category) ? '' : 'none';
            });
            subtotals.forEach(s => {
                s.style.display = (s.getAttribute('data-group-subtotal') === category) ? '' : 'none';
            });
            subgroups.forEach(sg => {
                sg.style.display = (sg.getAttribute('data-subgroup') === category) ? '' : 'none';
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
     * Reset Kuantitas 1 Baris Output WIP ke Nilai Awal
     */
    function resetAdjOutputField(outId) {
        const cfg = window.adjProdConfig || {};
        const items = cfg.outputItems || [];
        const item = items.find(it => String(it.output_id) === String(outId));
        const input = document.getElementById('input_adj_output_' + outId);
        if (input && item) {
            input.value = parseFloat(item.qty_kg).toFixed(2);
            recalculateHppSimulation();
        }
    }
    window.resetAdjOutputField = resetAdjOutputField;

    /**
     * Reset Kuantitas 1 Komponen Masukan ke Nilai Awal & Kembalikan Nominal Pasangannya
     */
    function resetAdjQtyField(fieldKey) {
        const cfg = window.adjProdConfig || {};
        const mapping = qtyConfigMap[fieldKey];
        const input = document.getElementById('input_adj_' + fieldKey);
        if (!input) return;

        if (mapping) {
            const initialVal = cfg[mapping.configKey] !== undefined ? cfg[mapping.configKey] : 0;
            input.value = mapping.precision === 0 ? parseInt(initialVal) : parseFloat(initialVal).toFixed(mapping.precision);

            // Juga kembalikan nominal pasangannya ke nilai awal orisinal dokumen
            if (mapping.nominalKey) {
                const nomInput = document.getElementById('input_adj_' + mapping.nominalKey);
                if (nomInput && cfg[mapping.nominalKey] !== undefined) {
                    nomInput.value = Math.round(cfg[mapping.nominalKey]);
                }
            }
        }
        recalculateHppSimulation();
    }
    window.resetAdjQtyField = resetAdjQtyField;

    /**
     * Reset Seluruh Parameter (Output WIP, Seluruh Kuantitas Masukan & 20 Komponen Biaya) ke Nilai Orisinal
     */
    function resetAllAdjFields() {
        const cfg = window.adjProdConfig || {};

        // 1. Reset Nominal 20 Komponen Biaya
        document.querySelectorAll('.adj-calc-input').forEach(input => {
            const key = input.getAttribute('data-key');
            if (key && cfg[key] !== undefined) {
                input.value = Math.round(cfg[key]);
            }
        });

        // 2. Reset Output WIP Items
        const items = cfg.outputItems || [];
        document.querySelectorAll('.adj-calc-output-qty').forEach(input => {
            const outId = input.getAttribute('data-out-id');
            if (outId) {
                const item = items.find(it => String(it.output_id) === String(outId));
                if (item) {
                    input.value = parseFloat(item.qty_kg).toFixed(2);
                }
            } else if (input.id === 'input_adj_total_wip_qty') {
                input.value = parseFloat(cfg.totalWipQty || 0).toFixed(2);
            }
        });

        // 3. Reset Seluruh Kuantitas Masukan (BBL, Kemasan, Energi, BTKL, FOH APD)
        for (const [key, mapping] of Object.entries(qtyConfigMap)) {
            const input = document.getElementById('input_adj_' + key);
            if (input) {
                const initialVal = cfg[mapping.configKey] !== undefined ? cfg[mapping.configKey] : 0;
                input.value = mapping.precision === 0 ? parseInt(initialVal) : parseFloat(initialVal).toFixed(mapping.precision);
            }
        }

        recalculateHppSimulation();
    }
    window.resetAllAdjFields = resetAllAdjFields;

    /**
     * Format Angka Kuantitas
     */
    function formatQty(num, maxDecimals = 2) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: maxDecimals }).format(num);
    }

    /**
     * Format Angka Persentase
     */
    function formatPercent(num) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }

    /**
     * Format Rupiah Bulat
     */
    function formatRupiahWhole(num) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(num));
    }

    /**
     * Format Rupiah Desimal Presisi (HPP)
     */
    function formatRupiahDecimal(num) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }

    /**
     * Kalkulasi Live Simulasi HPP Baru pada Lembar Kerja Penyesuaian
     */
    function recalculateHppSimulation() {
        const cfg = window.adjProdConfig || {};
        const wipQtyAwal = parseFloat(cfg.totalWipQty) || 0;
        const biayaAwal = parseFloat(cfg.totalBiayaAwal) || 0;
        const hppAwal = parseFloat(cfg.hppAwal) || 0;
        const singkongQtyAwal = parseFloat(cfg.singkongQtyAwal) || 0;
        const rendemenAwal = parseFloat(cfg.rendemenAwal) || 0;

        let deltaTotal = 0;
        let changedCount = 0;

        // --- A. KOREKSI KUANTITAS OUTPUT HASIL PRODUKSI (WIP) ---
        let newTotalWip = 0;
        const outputInputs = document.querySelectorAll('.adj-calc-output-qty[data-out-id]');
        const singleWipInput = document.getElementById('input_adj_total_wip_qty');
        const items = cfg.outputItems || [];

        if (outputInputs.length > 0) {
            outputInputs.forEach(input => {
                const outId = input.getAttribute('data-out-id');
                const newQty = parseFloat(input.value) || 0;
                const item = items.find(it => String(it.output_id) === String(outId));
                const initialQty = item ? parseFloat(item.qty_kg) : newQty;
                const deltaOut = newQty - initialQty;

                newTotalWip += newQty;

                const badge = document.getElementById('badge_delta_output_' + outId);
                const resetBtn = document.getElementById('btn_reset_output_' + outId);

                if (badge) {
                    if (deltaOut > 0.001) {
                        badge.className = 'adj-variance-badge adj-variance-up';
                        badge.textContent = '+' + formatQty(deltaOut, 2) + ' Kg';
                    } else if (deltaOut < -0.001) {
                        badge.className = 'adj-variance-badge adj-variance-down';
                        badge.textContent = '-' + formatQty(Math.abs(deltaOut), 2) + ' Kg';
                    } else {
                        badge.className = 'adj-variance-badge adj-variance-zero';
                        badge.textContent = '0 Kg';
                    }
                }

                if (resetBtn) {
                    resetBtn.style.display = Math.abs(deltaOut) > 0.001 ? 'inline-flex' : 'none';
                }

                if (Math.abs(deltaOut) > 0.001) {
                    changedCount++;
                }
            });
        } else if (singleWipInput) {
            newTotalWip = parseFloat(singleWipInput.value) || 0;
            const deltaWip = newTotalWip - wipQtyAwal;
            const badge = document.getElementById('badge_delta_total_wip_qty');

            if (badge) {
                if (deltaWip > 0.001) {
                    badge.className = 'adj-variance-badge adj-variance-up';
                    badge.textContent = '+' + formatQty(deltaWip, 2) + ' Kg';
                } else if (deltaWip < -0.001) {
                    badge.className = 'adj-variance-badge adj-variance-down';
                    badge.textContent = '-' + formatQty(Math.abs(deltaWip), 2) + ' Kg';
                } else {
                    badge.className = 'adj-variance-badge adj-variance-zero';
                    badge.textContent = '0 Kg';
                }
            }

            if (Math.abs(deltaWip) > 0.001) {
                changedCount++;
            }
        } else {
            newTotalWip = wipQtyAwal;
        }

        const deltaWipOverall = newTotalWip - wipQtyAwal;

        // Update Ringkasan Output WIP di Card dan Footer
        const summaryTotalWipBaruText = document.getElementById('summaryTotalWipBaruText');
        const summaryDeltaWipBadge = document.getElementById('summaryDeltaWipBadge');
        const simulasiWipFooter = document.getElementById('simulasiWipFooter');

        if (summaryTotalWipBaruText) {
            summaryTotalWipBaruText.textContent = formatQty(newTotalWip, 2) + ' Kg';
        }
        if (simulasiWipFooter) {
            simulasiWipFooter.textContent = formatQty(newTotalWip, 2) + ' Kg';
        }
        if (summaryDeltaWipBadge) {
            if (deltaWipOverall > 0.001) {
                summaryDeltaWipBadge.className = 'adj-variance-badge adj-variance-up';
                summaryDeltaWipBadge.textContent = '+' + formatQty(deltaWipOverall, 2) + ' Kg';
            } else if (deltaWipOverall < -0.001) {
                summaryDeltaWipBadge.className = 'adj-variance-badge adj-variance-down';
                summaryDeltaWipBadge.textContent = '-' + formatQty(Math.abs(deltaWipOverall), 2) + ' Kg';
            } else {
                summaryDeltaWipBadge.className = 'adj-variance-badge adj-variance-zero';
                summaryDeltaWipBadge.textContent = '0 Kg';
            }
        }

        // --- B. KOREKSI SELURUH KUANTITAS MASUKAN (BBL, KEMASAN, ENERGI, BTKL, APD) ---
        let currentSingkongQty = singkongQtyAwal;

        for (const [key, mapping] of Object.entries(qtyConfigMap)) {
            const input = document.getElementById('input_adj_' + key);
            if (!input) continue;

            const newVal = parseFloat(input.value) || 0;
            const initialVal = parseFloat(cfg[mapping.configKey]) || 0;
            const delta = newVal - initialVal;

            if (key === 'singkong_qty') {
                currentSingkongQty = newVal;
            }

            const badge = document.getElementById('badge_delta_qty_' + key);
            const resetBtn = document.getElementById('btn_reset_qty_' + key);
            const threshold = mapping.precision === 0 ? 0.0001 : (mapping.precision >= 4 ? 0.00005 : 0.001);

            if (badge) {
                if (delta > threshold) {
                    badge.className = 'adj-variance-badge adj-variance-up';
                    badge.textContent = '+' + formatQty(delta, mapping.precision) + ' ' + mapping.unit;
                } else if (delta < -threshold) {
                    badge.className = 'adj-variance-badge adj-variance-down';
                    badge.textContent = '-' + formatQty(Math.abs(delta), mapping.precision) + ' ' + mapping.unit;
                } else {
                    badge.className = 'adj-variance-badge adj-variance-zero';
                    badge.textContent = '0 ' + mapping.unit;
                }
            }

            if (resetBtn) {
                resetBtn.style.display = Math.abs(delta) > threshold ? 'inline-flex' : 'none';
            }

            if (Math.abs(delta) > threshold) {
                changedCount++;
            }
        }

        // --- C. LIVE SIMULASI RENDEMEN SINGKONG (%) ---
        const rendemenBaru = (currentSingkongQty > 0) ? ((newTotalWip / currentSingkongQty) * 100) : rendemenAwal;
        const summaryRendemenBaruText = document.getElementById('summaryRendemenBaruText');
        const simulasiRendemenFooter = document.getElementById('simulasiRendemenFooter');
        if (summaryRendemenBaruText) {
            summaryRendemenBaruText.textContent = formatPercent(rendemenBaru) + '%';
        }
        if (simulasiRendemenFooter) {
            simulasiRendemenFooter.textContent = formatPercent(rendemenBaru) + '%';
        }

        // --- D. LIVE SIMULASI NOMINAL BIAYA (20 KOMPONEN) ---
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

        // Helper update badge variance subtotal
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

        // --- E. KALKULASI TOTAL BIAYA & HPP PER KG BARU ---
        const biayaBaru = Math.round((biayaAwal + deltaTotal) * 100) / 100;
        const hppBaru = newTotalWip > 0 ? Math.round((biayaBaru / newTotalWip) * 100) / 100 : 0;
        const deltaHpp = Math.round((hppBaru - hppAwal) * 100) / 100;
        const totalValuasiDampak = Math.round(deltaHpp * newTotalWip);

        // Update panel ringkasan eksekutif & tabel footer
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
                countBadge.textContent = '0 Parameter Diubah';
                countBadge.style.color = '#64748b';
                countBadge.style.background = '#f1f5f9';
                countBadge.style.borderColor = '#e2e8f0';
            } else {
                countBadge.textContent = changedCount + ' Parameter Diubah';
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

    /**
     * Inisialisasi saat DOM siap
     */
    document.addEventListener('DOMContentLoaded', function () {
        // Event input real-time untuk seluruh field kalkulasi nominal
        document.querySelectorAll('.adj-calc-input').forEach(input => {
            input.addEventListener('input', recalculateHppSimulation);
            input.addEventListener('change', recalculateHppSimulation);
        });

        // Event input real-time untuk kuantitas output hasil produksi
        document.querySelectorAll('.adj-calc-output-qty').forEach(input => {
            input.addEventListener('input', recalculateHppSimulation);
            input.addEventListener('change', recalculateHppSimulation);
        });

        // Event input real-time untuk seluruh kuantitas masukan bahan & utilitas (Otomatis hitung nominal = Qty × Harga Satuan)
        document.querySelectorAll('.adj-calc-qty-input').forEach(input => {
            const handleQtyInput = function () {
                const key = input.getAttribute('data-key');
                const mapping = qtyConfigMap[key];
                if (mapping && mapping.nominalKey) {
                    const unitPrice = getUnitPrice(key);
                    if (unitPrice > 0) {
                        const newQty = parseFloat(input.value) || 0;
                        const nomInput = document.getElementById('input_adj_' + mapping.nominalKey);
                        if (nomInput) {
                            nomInput.value = Math.round(newQty * unitPrice);
                        }
                    }
                }
                recalculateHppSimulation();
            };

            input.addEventListener('input', handleQtyInput);
            input.addEventListener('change', handleQtyInput);
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
    // Helper template alasan cepat untuk memudahkan staf audit dan operasional
    window.setAdjTemplate = function (type) {
        const textarea = document.getElementById('alasan_txt');
        if (!textarea) return;

        let templateText = '';
        if (type === 'timbangan') {
            templateText = 'Koreksi kuantitas timbangan fisik bahan baku (penyesuaian selisih timbang riil lantai produksi vs bukti penerimaan gudang).';
        } else if (type === 'tambahan') {
            templateText = 'Penambahan kuantitas bahan baku untuk pemenuhan target shift produksi (mengambil tambahan bahan dari gudang).';
        } else if (type === 'faktur') {
            templateText = 'Penyesuaian nilai nominal rupiah sesuai faktur tagihan riil dari supplier yang diterima akuntansi.';
        } else if (type === 'wip') {
            templateText = 'Koreksi kuantitas hasil panen keripik singkong jadi (WIP) pasca proses sortir mutu dan pengemasan akhir.';
        }

        if (templateText) {
            textarea.value = templateText;
            textarea.focus();
        }
    };

    // Metadata Komponen Ber-Batch untuk Alokasi Stok Gudang Universal
    const batchComponentMeta = {
        'singkong':     { qtyKey: 'singkongQtyAwal', nilaiKey: 'singkong_nilai', unitPriceKey: 'singkong_qty', defaultPrice: 4500, label: 'Singkong Mentah Basah' },
        'minyak':       { qtyKey: 'minyakQtyAwal', nilaiKey: 'minyak_nilai', unitPriceKey: 'minyak_qty', defaultPrice: 20000, label: 'Minyak Goreng' },
        'bumbu':        { qtyKey: 'bumbuQtyAwal', nilaiKey: 'bumbu_nilai', unitPriceKey: 'bumbu_qty', defaultPrice: 5800, label: 'Bumbu Masak & Perenyah' },
        'karton_baru':  { qtyKey: 'kartonBaruQtyAwal', nilaiKey: 'karton_baru_nilai', unitPriceKey: 'karton_baru_qty', defaultPrice: 3500, label: 'Karton Box Baru' },
        'karton_bekas': { qtyKey: 'kartonBekasQtyAwal', nilaiKey: 'karton_bekas_nilai', unitPriceKey: 'karton_bekas_qty', defaultPrice: 875, label: 'Karton Box Bekas' },
        'plastik_hd':   { qtyKey: 'plastikHdQtyAwal', nilaiKey: 'plastik_hd_nilai', unitPriceKey: 'plastik_hd_qty', defaultPrice: 15000, label: 'Plastik HD' },
        'lakban_besar': { qtyKey: 'lakbanBesarQtyAwal', nilaiKey: 'lakban_besar_nilai', unitPriceKey: 'lakban_besar_qty', defaultPrice: 12000, label: 'Lakban Besar' },
        'lakban_kecil': { qtyKey: 'lakbanKecilQtyAwal', nilaiKey: 'lakban_kecil_nilai', unitPriceKey: 'lakban_kecil_qty', defaultPrice: 5000, label: 'Lakban Kecil' },
        'tali_rafia':   { qtyKey: 'taliRafiaQtyAwal', nilaiKey: 'tali_rafia_nilai', unitPriceKey: 'tali_rafia_qty', defaultPrice: 10000, label: 'Tali Rafia' },
    };

    window.activeBatchModalComponent = null;

    // Modal Universal: Ambil Tambahan Fisik dari Batch Gudang (Pop-Up Melayang di Tengah)
    window.openModalAlokasiBatch = function (key, nama, kode, unit) {
        window.activeBatchModalComponent = { key, nama, kode, unit };
        const modal = document.getElementById('modalAmbilBatchMaterial');
        if (!modal) return;

        // 1. Set Label Header & Satuan
        const elNama = document.getElementById('modalBatchKomponenNama');
        const elKode = document.getElementById('modalBatchKomponenKode');
        const elAwalSatuan = document.getElementById('modalBatchAwalSatuan');
        const elAddon = document.getElementById('modalBatchInputAddon');

        if (elNama) elNama.textContent = nama;
        if (elKode) elKode.textContent = `(${kode})`;
        if (elAwalSatuan) elAwalSatuan.textContent = unit;
        if (elAddon) elAddon.textContent = unit;

        // 2. Set Nilai Awal Dokumen
        const cfg = window.adjProdConfig || {};
        const meta = batchComponentMeta[key] || {};
        const qtyAwal = parseFloat(cfg[meta.qtyKey] || 0);
        const elAwalQty = document.getElementById('modalBatchAwalQty');
        if (elAwalQty) {
            elAwalQty.textContent = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(qtyAwal);
        }

        // 3. Set Batch Utama Aktif Saat Ini
        const batchMap = window.adjBahanBakuBatchMap || {};
        const activeBatches = batchMap[key] || [];
        const elBatchAwal = document.getElementById('modalBatchAwalList');
        if (elBatchAwal) {
            if (activeBatches.length > 0) {
                elBatchAwal.textContent = activeBatches.join(', ');
                elBatchAwal.style.background = '#ecfdf5';
                elBatchAwal.style.color = '#065f46';
                elBatchAwal.style.borderColor = '#a7f3d0';
            } else {
                elBatchAwal.textContent = 'Tidak Ada Batch Awal';
                elBatchAwal.style.background = '#f1f5f9';
                elBatchAwal.style.color = '#64748b';
                elBatchAwal.style.borderColor = '#cbd5e1';
            }
        }

        // 4. Isi Opsi Dropdown Batch Gudang Sesuai Komponen
        const select = document.getElementById('modal_select_batch');
        if (select) {
            select.innerHTML = '<option value="">-- Pilih Nomor Batch Gudang --</option>';
            const batches = (window.adjBatchesMap && window.adjBatchesMap[key]) ? window.adjBatchesMap[key] : [];

            if (batches.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = `(Tidak ada batch ${nama} yang tersedia di gudang)`;
                opt.disabled = true;
                select.appendChild(opt);
            } else {
                batches.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.batch_no;
                    opt.setAttribute('data-sisa', b.sisa_qty);
                    opt.setAttribute('data-harga', b.harga_satuan);
                    opt.textContent = `Batch: ${b.batch_no} • Sisa: ${new Intl.NumberFormat('id-ID').format(b.sisa_qty)} ${b.satuan_nm} • Rp ${new Intl.NumberFormat('id-ID').format(b.harga_satuan)}/${b.satuan_nm} (${b.gudang_nm})`;
                    select.appendChild(opt);
                });
            }
        }

        // 5. Reset Field Form & Simulasi
        const qtyInput = document.getElementById('modal_input_qty');
        if (qtyInput) qtyInput.value = '';
        const hint = document.getElementById('modalBatchHint');
        if (hint) hint.style.display = 'none';
        const summary = document.getElementById('modalSummaryBox');
        if (summary) summary.style.display = 'none';
        const btn = document.getElementById('btnApplyBatchModal');
        if (btn) btn.disabled = true;

        modal.style.setProperty('display', 'flex', 'important');
        document.body.style.overflow = 'hidden';
        if (select) select.focus();
    };

    // Alias kompatibilitas jika masih dipanggil
    window.openModalAmbilBatchSingkong = function () {
        window.openModalAlokasiBatch('singkong', 'Singkong Mentah Basah', 'BBL-01', 'Kg');
    };

    window.closeModalAmbilBatchMaterial = function () {
        const modal = document.getElementById('modalAmbilBatchMaterial') || document.getElementById('modalAmbilBatchSingkong');
        if (modal) {
            modal.style.setProperty('display', 'none', 'important');
            document.body.style.overflow = '';
        }
        window.activeBatchModalComponent = null;
    };

    window.closeModalAmbilBatchSingkong = window.closeModalAmbilBatchMaterial;

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            window.closeModalAmbilBatchMaterial();
        }
    });

    window.onModalBatchChange = function (selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const sisa = parseFloat(opt ? opt.getAttribute('data-sisa') : 0) || 0;
        const harga = parseFloat(opt ? opt.getAttribute('data-harga') : 0) || 0;
        const batchNo = selectEl.value;

        const hint = document.getElementById('modalBatchHint');
        const hintText = document.getElementById('modalBatchHintText');
        const qtyInput = document.getElementById('modal_input_qty');
        const activeComp = window.activeBatchModalComponent || { unit: 'Unit' };

        if (batchNo && hint) {
            hint.style.display = 'flex';
            const msg = `Sisa tersedia di gudang: <strong>${new Intl.NumberFormat('id-ID').format(sisa)} ${activeComp.unit}</strong> &bull; Tarif: <strong>Rp ${new Intl.NumberFormat('id-ID').format(harga)}/${activeComp.unit}</strong>`;
            if (hintText) {
                hintText.innerHTML = msg;
            } else {
                hint.innerHTML = msg;
            }
            if (qtyInput) {
                qtyInput.max = sisa;
                qtyInput.focus();
            }
        } else if (hint) {
            hint.style.display = 'none';
        }

        window.updateModalBatchSummary();
    };

    window.onModalQtyChange = function (qtyVal) {
        window.updateModalBatchSummary();
    };

    window.updateModalBatchSummary = function () {
        const select = document.getElementById('modal_select_batch');
        const opt = select && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
        const batchNo = select ? select.value : '';
        const hargaBatch = opt ? (parseFloat(opt.getAttribute('data-harga')) || 0) : 0;
        const qtyInput = document.getElementById('modal_input_qty');
        const addQty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;

        const btnApply = document.getElementById('btnApplyBatchModal');
        const summaryBox = document.getElementById('modalSummaryBox');
        const txtTotalQty = document.getElementById('modalSummaryTotalQty');
        const txtAddCost = document.getElementById('modalSummaryAddCost');

        const activeComp = window.activeBatchModalComponent || {};
        const key = activeComp.key || 'singkong';
        const meta = batchComponentMeta[key] || {};
        const cfg = window.adjProdConfig || {};
        const qtyAwal = parseFloat(cfg[meta.qtyKey] || 0);

        if (batchNo && addQty > 0) {
            if (btnApply) btnApply.disabled = false;
            if (summaryBox) summaryBox.style.display = 'block';
            const unitPrice = hargaBatch > 0 ? hargaBatch : (cfg.unitPrices?.[meta.unitPriceKey] || meta.defaultPrice);
            const totalQtyBaru = qtyAwal + addQty;
            const addCost = addQty * unitPrice;

            if (txtTotalQty) txtTotalQty.textContent = `${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalQtyBaru)} ${activeComp.unit || 'Kg'}`;
            if (txtAddCost) txtAddCost.textContent = `+Rp ${new Intl.NumberFormat('id-ID').format(Math.round(addCost))}`;
        } else {
            if (btnApply) btnApply.disabled = true;
            if (summaryBox) summaryBox.style.display = 'none';
        }
    };

    // Terapkan hasil modal ke tabel lembar kerja
    window.applyModalBatchToWorksheet = function () {
        const select = document.getElementById('modal_select_batch');
        const opt = select && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
        const batchNo = select ? select.value : '';
        const hargaBatch = opt ? (parseFloat(opt.getAttribute('data-harga')) || 0) : 0;
        const qtyInput = document.getElementById('modal_input_qty');
        const addQty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;

        if (!batchNo || addQty <= 0) return;

        const activeComp = window.activeBatchModalComponent || {};
        const key = activeComp.key || 'singkong';
        const meta = batchComponentMeta[key] || {};
        const cfg = window.adjProdConfig || {};

        const qtyAwal = parseFloat(cfg[meta.qtyKey] || 0);
        const nilaiAwal = parseFloat(cfg[meta.nilaiKey] || 0);
        const unitPrice = hargaBatch > 0 ? hargaBatch : (cfg.unitPrices?.[meta.unitPriceKey] || meta.defaultPrice);

        // 1. Set Hidden Inputs untuk Backend
        const hBatch = document.getElementById(`additional_batch_no_${key}`);
        const hQty = document.getElementById(`additional_batch_qty_${key}`);
        const hHarga = document.getElementById(`additional_batch_harga_${key}`);
        if (hBatch) hBatch.value = batchNo;
        if (hQty) hQty.value = addQty;
        if (hHarga) hHarga.value = unitPrice;

        // Khusus backward compatibility singkong
        if (key === 'singkong') {
            const hAction = document.getElementById('singkong_stok_action');
            const hLegacyBatch = document.getElementById('additional_batch_no');
            const hLegacyQty = document.getElementById('additional_batch_qty');
            if (hAction) hAction.value = 'deduct_batch';
            if (hLegacyBatch) hLegacyBatch.value = batchNo;
            if (hLegacyQty) hLegacyQty.value = addQty;
        }

        // 2. Set Nilai Input Form Worksheet
        const inputQty = document.getElementById(`input_adj_${key}_qty`);
        const inputNilai = document.getElementById(`input_adj_${key}_nilai`);
        if (inputQty) {
            inputQty.value = (qtyAwal + addQty).toFixed(2);
        }
        if (inputNilai) {
            inputNilai.value = Math.round(nilaiAwal + (addQty * unitPrice));
        }

        // 3. Tampilkan Badge Aktif di Baris Tabel Komponen
        const badgeBox = document.getElementById(`badgeActiveAdditionalBatch_${key}`);
        const textBadge = document.getElementById(`textActiveAdditionalBatch_${key}`);
        if (badgeBox && textBadge) {
            badgeBox.style.display = 'block';
            textBadge.textContent = `Tambahan: +${new Intl.NumberFormat('id-ID').format(addQty)} ${activeComp.unit || 'Kg'} (Batch: ${batchNo})`;
        }

        // 4. Update Alasan Dokumen Otomatis jika Diperlukan
        const textarea = document.getElementById('alasan_txt');
        const notifTxt = `Tambah ${addQty} ${activeComp.unit || 'Kg'} ${activeComp.nama || key} dari Batch ${batchNo} gudang.`;
        if (textarea && !textarea.value.includes(batchNo)) {
            textarea.value = textarea.value.trim() ? `${textarea.value.trim()}\n- ${notifTxt}` : `Penyesuaian bahan: ${notifTxt}`;
        }

        window.closeModalAmbilBatchMaterial();

        if (typeof recalculateHppSimulation === 'function') {
            recalculateHppSimulation();
        }
    };

    // Fungsi Batalkan Tambahan Batch Tertentu
    window.cancelAdditionalBatch = function (key) {
        key = key || 'singkong';
        const meta = batchComponentMeta[key] || {};
        const cfg = window.adjProdConfig || {};

        // Kosongkan hidden input
        const hBatch = document.getElementById(`additional_batch_no_${key}`);
        const hQty = document.getElementById(`additional_batch_qty_${key}`);
        const hHarga = document.getElementById(`additional_batch_harga_${key}`);
        if (hBatch) hBatch.value = '';
        if (hQty) hQty.value = '0';
        if (hHarga) hHarga.value = '0';

        if (key === 'singkong') {
            const hAction = document.getElementById('singkong_stok_action');
            const hLegacyBatch = document.getElementById('additional_batch_no');
            const hLegacyQty = document.getElementById('additional_batch_qty');
            if (hAction) hAction.value = 'none';
            if (hLegacyBatch) hLegacyBatch.value = '';
            if (hLegacyQty) hLegacyQty.value = '0';
        }

        // Sembunyikan badge
        const badgeBox = document.getElementById(`badgeActiveAdditionalBatch_${key}`);
        if (badgeBox) badgeBox.style.display = 'none';

        // Kembalikan input ke nilai awal
        const inputQty = document.getElementById(`input_adj_${key}_qty`);
        const inputNilai = document.getElementById(`input_adj_${key}_nilai`);
        if (inputQty && meta.qtyKey) {
            inputQty.value = (parseFloat(cfg[meta.qtyKey]) || 0).toFixed(2);
        }
        if (inputNilai && meta.nilaiKey) {
            inputNilai.value = Math.round(parseFloat(cfg[meta.nilaiKey]) || 0);
        }

        if (typeof recalculateHppSimulation === 'function') {
            recalculateHppSimulation();
        }
    };

    // Batalkan pengambilan batch lain
    window.cancelAdditionalBatch = function () {
        const hAction = document.getElementById('singkong_stok_action');
        const hBatch = document.getElementById('additional_batch_no');
        const hQty = document.getElementById('additional_batch_qty');
        if (hAction) hAction.value = 'none';
        if (hBatch) hBatch.value = '';
        if (hQty) hQty.value = '0';

        const badgeBox = document.getElementById('badgeActiveAdditionalBatch');
        if (badgeBox) badgeBox.style.display = 'none';

        if (typeof resetAdjField === 'function') {
            resetAdjField('singkong_qty');
            resetAdjField('singkong_nilai');
        }
    };
})();
