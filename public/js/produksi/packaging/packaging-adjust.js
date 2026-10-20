/**
 * JavaScript Worksheet Penyesuaian HPP & Biaya Pengemasan (Packaging)
 * Path: public/js/produksi/packaging/packaging-adjust.js
 * PT Mirasa Food Industry - Standar Enterprise
 */

(function () {
    'use strict';

    /**
     * Filter Kategori Lembar Kerja Komponen Biaya Kemas
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
     * Format Rupiah Utility
     */
    function formatRupiahWhole(num) {
        return Math.abs(num).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function formatRupiahDecimal(num, decimals = 2) {
        return Math.abs(num).toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    /**
     * Reset Nilai 1 Field Tertentu ke Nilai Awal Dokumen
     */
    function resetAdjField(fieldKey) {
        const cfg = window.adjPackConfig || {};
        const initialVal = cfg[fieldKey] !== undefined ? cfg[fieldKey] : 0;
        const input = document.getElementById('input_adj_' + fieldKey);
        if (input) {
            input.value = Math.round(initialVal);
            recalculateHppSimulation();
        }
    }
    window.resetAdjField = resetAdjField;

    /**
     * Reset Seluruh Input Komponen ke Nilai Orisinal
     */
    function resetAllAdjFields() {
        const cfg = window.adjPackConfig || {};
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
        const cfg = window.adjPackConfig || {};
        const hasilKarton = parseFloat(cfg.hasilKartonQty) || 0;
        const hasilPcs = parseFloat(cfg.hasilPcsQty) || 0;
        const totalBiayaAwal = parseFloat(cfg.totalBiayaAwal) || 0;
        const hppKartonAwal = parseFloat(cfg.hppKartonAwal) || 0;
        const hppPcsAwal = parseFloat(cfg.hppPcsAwal) || 0;

        let changedCount = 0;

        // Subtotal akumulasi per kelompok
        let subtotalWip = 0;
        let subtotalKms = 0;
        let subtotalLabor = 0;
        let subtotalOverhead = 0;

        document.querySelectorAll('.adj-calc-input').forEach(input => {
            const key = input.getAttribute('data-key');
            const group = input.getAttribute('data-group') || '';
            const qty = parseFloat(input.getAttribute('data-qty')) || 0;
            if (!key) return;

            const newVal = parseFloat(input.value) || 0;
            const initialVal = parseFloat(cfg[key]) || 0;
            const delta = newVal - initialVal;

            if (group === 'WIP') {
                subtotalWip += newVal;
            } else if (group === 'KEMASAN') {
                subtotalKms += newVal;
            } else if (group === 'LABOR') {
                subtotalLabor += newVal;
            } else if (group === 'OVERHEAD') {
                subtotalOverhead += newVal;
            }

            // Update badge delta per baris
            const badge = document.getElementById('badge_delta_' + key);
            const resetBtn = document.getElementById('btn_reset_' + key);
            const helperUnit = document.getElementById('helper_unit_' + key);
            const roundedDelta = Math.round(delta);

            if (badge) {
                if (roundedDelta > 0) {
                    badge.className = 'adj-variance-badge adj-variance-up';
                    badge.textContent = '+Rp ' + formatRupiahWhole(roundedDelta);
                } else if (roundedDelta < 0) {
                    badge.className = 'adj-variance-badge adj-variance-down';
                    badge.textContent = '-Rp ' + formatRupiahWhole(roundedDelta);
                } else {
                    badge.className = 'adj-variance-badge adj-variance-zero';
                    badge.textContent = 'Rp 0';
                }
            }

            // Update helper harga satuan per unit
            if (helperUnit && qty > 0) {
                const unitPrice = newVal / qty;
                helperUnit.textContent = '≈ Rp ' + formatRupiahWhole(Math.round(unitPrice)) + ' / unit';
            }

            // Tampilkan tombol reset jika nilai berbeda dari awal
            if (resetBtn) {
                resetBtn.style.display = (Math.abs(delta) >= 1) ? 'inline-flex' : 'none';
            }

            if (Math.abs(delta) >= 1) {
                changedCount++;
            }
        });

        // 1. Update Subtotal WIP
        const wipAwal = parseFloat(cfg.totalWipAwal) || 0;
        const deltaWip = Math.round(subtotalWip - wipAwal);
        const subtotalWipText = document.getElementById('subtotal_wip_text');
        const subtotalWipDelta = document.getElementById('subtotal_wip_delta');
        if (subtotalWipText) subtotalWipText.textContent = 'Rp ' + formatRupiahWhole(Math.round(subtotalWip));
        if (subtotalWipDelta) {
            updateDeltaBadge(subtotalWipDelta, deltaWip);
        }

        // 2. Update Subtotal Kemasan
        const kmsAwal = parseFloat(cfg.totalKemasanAwal) || 0;
        const deltaKms = Math.round(subtotalKms - kmsAwal);
        const subtotalKmsText = document.getElementById('subtotal_kemasan_text');
        const subtotalKmsDelta = document.getElementById('subtotal_kemasan_delta');
        if (subtotalKmsText) subtotalKmsText.textContent = 'Rp ' + formatRupiahWhole(Math.round(subtotalKms));
        if (subtotalKmsDelta) {
            updateDeltaBadge(subtotalKmsDelta, deltaKms);
        }

        // 3. Update Subtotal Labor
        const laborAwal = parseFloat(cfg.totalLaborAwal) || 0;
        const deltaLabor = Math.round(subtotalLabor - laborAwal);
        const subtotalLaborText = document.getElementById('subtotal_labor_text');
        const subtotalLaborDelta = document.getElementById('subtotal_labor_delta');
        if (subtotalLaborText) subtotalLaborText.textContent = 'Rp ' + formatRupiahWhole(Math.round(subtotalLabor));
        if (subtotalLaborDelta) {
            updateDeltaBadge(subtotalLaborDelta, deltaLabor);
        }

        // 4. Update Subtotal Overhead
        const overheadAwal = parseFloat(cfg.totalOverheadAwal) || 0;
        const deltaOverhead = Math.round(subtotalOverhead - overheadAwal);
        const subtotalOverheadText = document.getElementById('subtotal_overhead_text');
        const subtotalOverheadDelta = document.getElementById('subtotal_overhead_delta');
        if (subtotalOverheadText) subtotalOverheadText.textContent = 'Rp ' + formatRupiahWhole(Math.round(subtotalOverhead));
        if (subtotalOverheadDelta) {
            updateDeltaBadge(subtotalOverheadDelta, deltaOverhead);
        }

        // 5. Total Biaya & Net Variance
        const totalBiayaBaru = Math.round(subtotalWip + subtotalKms + subtotalLabor + subtotalOverhead);
        const netSelisihBiaya = totalBiayaBaru - Math.round(totalBiayaAwal);

        const totalBiayaBaruEl = document.getElementById('simulasiTotalBiayaBaruText');
        const netSelisihBiayaEl = document.getElementById('simulasiNetSelisihBiayaText');
        if (totalBiayaBaruEl) totalBiayaBaruEl.textContent = 'Rp ' + formatRupiahWhole(totalBiayaBaru);
        if (netSelisihBiayaEl) {
            updateDeltaBadge(netSelisihBiayaEl, netSelisihBiaya);
        }

        // 6. Estimasi HPP Karton Baru
        const newHppKarton = hasilKarton > 0 ? (totalBiayaBaru / hasilKarton) : 0;
        const deltaHppKarton = newHppKarton - hppKartonAwal;
        const hppKartonEl = document.getElementById('simulasiHppKartonBaruText');
        const hppKartonDeltaBadge = document.getElementById('simulasiSelisihHppKartonBadge');

        if (hppKartonEl) hppKartonEl.textContent = 'Rp ' + formatRupiahWhole(Math.round(newHppKarton));
        if (hppKartonDeltaBadge) {
            const roundedHppDelta = Math.round(deltaHppKarton);
            if (roundedHppDelta > 0) {
                hppKartonDeltaBadge.className = 'adj-variance-badge adj-variance-up';
                hppKartonDeltaBadge.textContent = 'Selisih: +Rp ' + formatRupiahWhole(roundedHppDelta) + ' / ktn';
            } else if (roundedHppDelta < 0) {
                hppKartonDeltaBadge.className = 'adj-variance-badge adj-variance-down';
                hppKartonDeltaBadge.textContent = 'Selisih: -Rp ' + formatRupiahWhole(roundedHppDelta) + ' / ktn';
            } else {
                hppKartonDeltaBadge.className = 'adj-variance-badge adj-variance-zero';
                hppKartonDeltaBadge.textContent = 'Selisih: Rp 0 / ktn';
            }
        }

        // 7. Estimasi HPP Pcs Baru
        const newHppPcs = hasilPcs > 0 ? (totalBiayaBaru / hasilPcs) : 0;
        const deltaHppPcs = newHppPcs - hppPcsAwal;
        const hppPcsEl = document.getElementById('simulasiHppPcsBaruText');
        const hppPcsDeltaBadge = document.getElementById('simulasiSelisihHppPcsBadge');

        if (hppPcsEl) hppPcsEl.textContent = 'Rp ' + formatRupiahWhole(Math.round(newHppPcs));
        if (hppPcsDeltaBadge) {
            const roundedPcsDelta = Math.round(deltaHppPcs);
            if (roundedPcsDelta > 0) {
                hppPcsDeltaBadge.className = 'adj-variance-badge adj-variance-up';
                hppPcsDeltaBadge.textContent = 'Selisih: +Rp ' + formatRupiahWhole(roundedPcsDelta) + ' / pcs';
            } else if (roundedPcsDelta < 0) {
                hppPcsDeltaBadge.className = 'adj-variance-badge adj-variance-down';
                hppPcsDeltaBadge.textContent = 'Selisih: -Rp ' + formatRupiahWhole(roundedPcsDelta) + ' / pcs';
            } else {
                hppPcsDeltaBadge.className = 'adj-variance-badge adj-variance-zero';
                hppPcsDeltaBadge.textContent = 'Selisih: Rp 0 / pcs';
            }
        }

        // 8. Update Counter Chip
        const countBadge = document.getElementById('adjCountBadge');
        if (countBadge) {
            countBadge.textContent = changedCount + ' Komponen Diubah';
            if (changedCount > 0) {
                countBadge.style.background = '#fef3c7';
                countBadge.style.color = '#b45309';
                countBadge.style.borderColor = '#fde68a';
            } else {
                countBadge.style.background = '#e0f2fe';
                countBadge.style.color = '#0369a1';
                countBadge.style.borderColor = '#bae6fd';
            }
        }

        // 9. Sync Executive KPI Preview Cards (jika ada)
        const kpiHppKarton = document.getElementById('kpi_simulasi_hpp_karton');
        const kpiHppPcs = document.getElementById('kpi_simulasi_hpp_pcs');
        const kpiTotalBiaya = document.getElementById('kpi_simulasi_total_biaya');
        if (kpiHppKarton) kpiHppKarton.textContent = 'Rp ' + formatRupiahWhole(Math.round(newHppKarton));
        if (kpiHppPcs) kpiHppPcs.textContent = 'Rp ' + formatRupiahWhole(Math.round(newHppPcs));
        if (kpiTotalBiaya) kpiTotalBiaya.textContent = 'Rp ' + formatRupiahWhole(totalBiayaBaru);
    }
    window.recalculateHppSimulation = recalculateHppSimulation;

    function updateDeltaBadge(badgeEl, deltaVal) {
        if (!badgeEl) return;
        if (deltaVal > 0) {
            badgeEl.className = 'adj-variance-badge adj-variance-up';
            badgeEl.textContent = '+Rp ' + formatRupiahWhole(deltaVal);
        } else if (deltaVal < 0) {
            badgeEl.className = 'adj-variance-badge adj-variance-down';
            badgeEl.textContent = '-Rp ' + formatRupiahWhole(deltaVal);
        } else {
            badgeEl.className = 'adj-variance-badge adj-variance-zero';
            badgeEl.textContent = 'Rp 0';
        }
    }

    /**
     * Inisialisasi Event Listeners saat DOM Ready
     */
    document.addEventListener('DOMContentLoaded', function () {
        // Event input pada seluruh field kalkulasi
        document.querySelectorAll('.adj-calc-input').forEach(input => {
            input.addEventListener('input', recalculateHppSimulation);
            input.addEventListener('change', recalculateHppSimulation);
        });

        // Validasi Submit Form
        const form = document.getElementById('formAdjustHppWorksheet') || document.getElementById('formAdjustHppPackaging');
        if (form) {
            form.addEventListener('submit', function (e) {
                const alasanInput = document.getElementById('alasan_txt');
                const alasanVal = (alasanInput ? alasanInput.value : '').trim();

                if (alasanVal.length < 5) {
                    e.preventDefault();
                    alert('Alasan penyesuaian wajib diisi minimal 5 karakter untuk keperluan audit trail akuntansi.');
                    if (alasanInput) alasanInput.focus();
                    return;
                }

                // Cek apakah ada perubahan
                let anyChanged = false;
                const cfg = window.adjPackConfig || {};
                document.querySelectorAll('.adj-calc-input').forEach(input => {
                    const key = input.getAttribute('data-key');
                    if (key && cfg[key] !== undefined) {
                        const newVal = parseFloat(input.value) || 0;
                        const initialVal = parseFloat(cfg[key]) || 0;
                        if (Math.abs(newVal - initialVal) >= 1) {
                            anyChanged = true;
                        }
                    }
                });

                if (!anyChanged) {
                    e.preventDefault();
                    alert('Tidak ada komponen biaya yang mengalami perubahan nilai. Silakan ubah minimal satu komponen untuk menerapkan penyesuaian.');
                    return;
                }

                // Konfirmasi Pengguna
                const totalBaruText = document.getElementById('simulasiTotalBiayaBaruText')?.textContent || '';
                const hppBaruText = document.getElementById('simulasiHppKartonBaruText')?.textContent || '';
                const confirmMsg = 'Apakah Anda yakin ingin menyimpan dan menerapkan Berita Acara Penyesuaian HPP Kemas ini?\n\n' +
                                  '• Total Biaya Baru: ' + totalBaruText + '\n' +
                                  '• Estimasi HPP/Karton Baru: ' + hppBaruText + '\n\n' +
                                  'Saldo nilai persediaan barang jadi di gudang akan otomatis disinkronkan.';

                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                }
            });
        }

        // Jalankan kalkulasi perdana
        recalculateHppSimulation();
    });

})();
