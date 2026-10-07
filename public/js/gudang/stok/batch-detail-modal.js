/**
 * ==========================================================================
 * BATCH DOSSIER & TRACEABILITY MODAL SCRIPT
 * File: public/js/gudang/stok/batch-detail-modal.js
 * Standard: PT Mirasa Food Industry (Separation of Concerns)
 * ==========================================================================
 */

let currentBatchNo = null;
let currentBarangId = null;

/**
 * Buka modal penelusuran batch dan ambil data via AJAX
 */
window.showBatchDetailModal = function (batchNo, barangId = null) {
    if (!batchNo) return;

    currentBatchNo = batchNo;
    currentBarangId = barangId;

    const modal = document.getElementById('batchDetailModal');
    if (!modal) {
        console.error('Elemen #batchDetailModal tidak ditemukan di DOM.');
        return;
    }

    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    // Set batch no di header segera
    document.getElementById('bmBatchNoText').textContent = batchNo;

    // Reset tab ke Tab 1 (Saldo)
    switchBatchTab('tabSaldo');

    // Tampilkan loading state
    document.getElementById('bmLoadingState').style.display = 'block';
    document.getElementById('bmErrorState').style.display = 'none';
    document.getElementById('bmContentWrapper').style.display = 'none';

    fetchBatchData(batchNo, barangId);
};

/**
 * Tutup modal
 */
window.closeBatchDetailModal = function () {
    const modal = document.getElementById('batchDetailModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
};

/**
 * Handler klik backdrop
 */
window.handleBackdropClick = function (event) {
    if (event.target.id === 'batchDetailModal') {
        closeBatchDetailModal();
    }
};

/**
 * Handler ganti tab di dalam modal
 */
window.switchBatchTab = function (tabKey) {
    // 1. Update tab item active
    const tabs = document.querySelectorAll('.batch-tab-item');
    const panes = document.querySelectorAll('.batch-tab-pane');

    tabs.forEach(t => t.classList.remove('active'));
    panes.forEach(p => p.classList.remove('active'));

    const tabMap = {
        'tabSaldo': 0,
        'tabInbound': 1,
        'tabOutbound': 2,
        'tabLedger': 3
    };

    const targetIdx = tabMap[tabKey] ?? 0;
    if (tabs[targetIdx]) tabs[targetIdx].classList.add('active');

    const targetPane = document.getElementById('pane-' + tabKey);
    if (targetPane) targetPane.classList.add('active');
};

/**
 * Salin nomor batch ke clipboard
 */
window.copyBatchNumber = function () {
    if (!currentBatchNo) return;
    navigator.clipboard.writeText(currentBatchNo).then(() => {
        const btn = document.querySelector('.batch-copy-btn');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Disalin!';
            setTimeout(() => { btn.innerHTML = original; }, 1500);
        }
    }).catch(err => {
        console.warn('Gagal menyalin:', err);
    });
};

/**
 * Coba lagi jika error
 */
window.retryFetchBatch = function () {
    if (currentBatchNo) {
        window.showBatchDetailModal(currentBatchNo, currentBarangId);
    }
};

/**
 * Request AJAX ke endpoint penelusuran batch
 */
function fetchBatchData(batchNo, barangId) {
    const isProduksi = window.location.pathname.includes('/produksi/');
    const baseUrl = isProduksi ? '/produksi/stok/batch-detail/' : '/gudang/stok/batch-detail/';
    let url = baseUrl + encodeURIComponent(batchNo);
    if (barangId) {
        url += '?barang_id=' + encodeURIComponent(barangId);
    }

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Gagal memuat riwayat batch.');
        }
        renderBatchData(data);
    })
    .catch(err => {
        console.error('Batch detail fetch error:', err);
        document.getElementById('bmLoadingState').style.display = 'none';
        document.getElementById('bmContentWrapper').style.display = 'none';
        document.getElementById('bmErrorState').style.display = 'block';
        document.getElementById('bmErrorMessage').textContent = err.message || 'Terjadi kesalahan saat memuat data batch.';
    });
}

/**
 * Render seluruh data batch ke elemen modal
 */
function renderBatchData(data) {
    const barang = data.barang || {};
    const stok = data.stok || {};
    const inbound = data.inbound || {};
    const outbound = data.outbound || {};
    const ledger = data.ledger || [];

    // 1. Header & Snapshot
    document.getElementById('bmBarangNm').textContent = barang.barang_nm || '-';
    document.getElementById('bmBarangCd').textContent = barang.barang_cd || '-';
    document.getElementById('bmJenisNm').textContent = barang.jenis_nm || '-';
    document.getElementById('bmSatuanNm').textContent = barang.satuan_nm || 'Unit';

    const satuan = barang.satuan_nm || 'Unit';
    const totalSisa = stok.total_sisa_qty ?? 0;
    const totalAwal = stok.total_qty_awal ?? 0;
    const totalKeluar = stok.total_qty_keluar ?? 0;
    const pct = stok.pct_terpakai ?? 0;

    document.getElementById('bmTotalSisa').textContent = totalSisa.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    document.getElementById('bmTotalAwal').textContent = totalAwal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + satuan;
    document.getElementById('bmTotalKeluar').textContent = totalKeluar.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + satuan;
    document.getElementById('bmPctTerpakai').textContent = pct + '%';

    // Status Badge & Desc
    const status = stok.status_batch || 'TERSEDIA';
    const statusBadge = document.getElementById('bmStatusBadge');
    const statusDesc = document.getElementById('bmStatusDesc');

    statusBadge.style.display = 'inline-block';
    if (status === 'EXPIRED') {
        statusBadge.className = 'badge';
        statusBadge.style.cssText = 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.75rem; padding: 3px 8px;';
        statusBadge.textContent = '❌ EXPIRED';
        statusDesc.innerHTML = '<span style="color: #dc2626; font-weight: 700;">Sudah Kadaluarsa</span>';
    } else if (status === 'HABIS') {
        statusBadge.className = 'badge';
        statusBadge.style.cssText = 'background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-weight: 800; font-size: 0.75rem; padding: 3px 8px;';
        statusBadge.textContent = '⚪ STOK HABIS';
        statusDesc.innerHTML = '<span style="color: #64748b; font-weight: 700;">Stok Telah Habis Terpakai</span>';
    } else if (status === 'MENIPIS') {
        statusBadge.className = 'badge';
        statusBadge.style.cssText = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800; font-size: 0.75rem; padding: 3px 8px;';
        statusBadge.textContent = '⚠️ MENIPIS';
        statusDesc.innerHTML = '<span style="color: #d97706; font-weight: 700;">Stok Kritis Segera Restock</span>';
    } else {
        statusBadge.className = 'badge';
        statusBadge.style.cssText = 'background: #dcfce7; color: #166534; border: 1px solid #86efac; font-weight: 800; font-size: 0.75rem; padding: 3px 8px;';
        statusBadge.textContent = '🟢 TERSEDIA';
        statusDesc.innerHTML = '<span style="color: #059669; font-weight: 700;">Stok Aman &amp; Siap Digunakan</span>';
    }

    // Progress Bar Fill
    const progressBar = document.getElementById('bmProgressBar');
    progressBar.style.width = Math.min(100, Math.max(0, pct)) + '%';
    if (pct >= 90) {
        progressBar.style.background = '#dc2626';
    } else if (pct >= 60) {
        progressBar.style.background = 'linear-gradient(90deg, #d97706 0%, #dc2626 100%)';
    } else {
        progressBar.style.background = 'linear-gradient(90deg, #0284c7 0%, #059669 100%)';
    }

    // Origin Snapshot
    if (data.origin_type === 'MANUFACTURE' && inbound.produksi) {
        document.getElementById('bmOriginLabel').textContent = 'Asal Olahan Pabrik';
        document.getElementById('bmOriginTitle').textContent = inbound.produksi.lini_produksi || 'PRODUKSI PABRIK';
        document.getElementById('bmOriginSub').innerHTML = `SPK: <strong style="color: #1e293b;">${inbound.produksi.produksi_no}</strong> &bull; Tgl: ${inbound.produksi.produksi_tgl}`;
    } else {
        document.getElementById('bmOriginLabel').textContent = 'Asal Kedatangan (Supplier)';
        const firstTerima = (inbound.terima_list && inbound.terima_list.length > 0) ? inbound.terima_list[0] : null;
        const firstQc = (inbound.qc_list && inbound.qc_list.length > 0) ? inbound.qc_list[0] : null;
        const supplierNm = firstTerima?.supplier_nm || firstQc?.supplier_nm || 'Supplier Eksternal';
        const dateStr = firstTerima?.terima_tgl || firstQc?.tgl_periksa || '-';

        document.getElementById('bmOriginTitle').textContent = supplierNm;
        document.getElementById('bmOriginSub').innerHTML = `Tgl Masuk: <strong style="color: #1e293b;">${dateStr}</strong>`;
    }

    // Link Buku Kartu Stok
    const isProduksi = window.location.pathname.includes('/produksi/');
    const ledgerBase = isProduksi ? '/produksi/stok/ledger' : '/gudang/stok/ledger';
    const ledgerLink = document.getElementById('bmBtnLedgerLink');
    if (barang.barang_id) {
        ledgerLink.href = `${ledgerBase}?barang_id=${barang.barang_id}`;
        ledgerLink.style.display = 'inline-flex';
    } else {
        ledgerLink.style.display = 'none';
    }

    // 2. Render TAB 1: Saldo Fisik & Gudang
    renderTabSaldo(stok.items || [], satuan);
    document.getElementById('bmTabCountSaldo').textContent = (stok.items || []).length;

    // 3. Render TAB 2: Asal-Usul & QC Inbound
    renderTabInbound(data.origin_type, inbound, satuan);
    const inboundCount = data.origin_type === 'MANUFACTURE' ? 1 : ((inbound.terima_list || []).length + (inbound.qc_list || []).length);
    document.getElementById('bmTabCountInbound').textContent = inboundCount;

    // 4. Render TAB 3: Outbound Pemakaian
    renderTabOutbound(outbound.pemakaian_list || [], satuan);
    document.getElementById('bmTabCountOutbound').textContent = (outbound.pemakaian_list || []).length;

    // 5. Render TAB 4: Ledger Timeline
    renderTabLedger(ledger, satuan);
    document.getElementById('bmTabCountLedger').textContent = ledger.length;

    // Selesai memuat, tampilkan konten
    document.getElementById('bmLoadingState').style.display = 'none';
    document.getElementById('bmContentWrapper').style.display = 'block';
}

/**
 * Render Tab 1: Saldo per Gudang & Grade
 */
function renderTabSaldo(items, satuan) {
    const tbody = document.getElementById('bmSaldoTbody');
    if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 2rem; color: #94a3b8;">Tidak ada data saldo aktif untuk batch ini.</td></tr>`;
        return;
    }

    let html = '';
    items.forEach((it, idx) => {
        const gradeBadge = it.grade_cd === 'B' 
            ? '<span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800; font-size: 0.72rem; padding: 2px 7px;">🟡 Grade B</span>'
            : (it.grade_cd === 'REJECT'
                ? '<span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 800; font-size: 0.72rem; padding: 2px 7px;">❌ Afkir</span>'
                : '<span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 800; font-size: 0.72rem; padding: 2px 7px;">🟢 Grade A</span>');

        const sisaColor = it.is_habis ? '#94a3b8' : '#059669';
        const expColor = it.is_expired ? '#dc2626; font-weight: 700;' : '#64748b;';

        html += `
            <tr style="background: ${it.is_habis ? '#fafafa' : '#ffffff'};">
                <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                <td><strong style="color: #0f172a;">${it.gudang_nm}</strong></td>
                <td style="text-align: center;">${gradeBadge}</td>
                <td style="text-align: center; color: ${expColor}">
                    ${it.expired_tgl || '-'}
                    ${it.is_expired ? '<br><small style="color: #dc2626;">(Kadaluarsa)</small>' : ''}
                </td>
                <td style="text-align: right; font-weight: 600;">
                    ${it.qty_awal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${satuan}
                </td>
                <td style="text-align: right; color: #dc2626;">
                    ${it.qty_keluar.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${satuan}
                </td>
                <td style="text-align: right; font-weight: 800; color: ${sisaColor};">
                    ${it.sisa_qty.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${satuan}
                </td>
                <td style="text-align: right; color: #475569;">
                    Rp ${it.harga_satuan.toLocaleString('id-ID')}
                </td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                    Rp ${it.sisa_nilai.toLocaleString('id-ID')}
                </td>
                <td style="text-align: center;">
                    ${it.is_habis 
                        ? '<span style="color: #94a3b8; font-size: 0.7rem; font-weight: 700;">HABIS</span>'
                        : '<span style="background: #059669; color: #ffffff; padding: 2px 6px; border-radius: 4px; font-size: 0.675rem; font-weight: 700;">TERSEDIA</span>'}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

/**
 * Render Tab 2: Asal-Usul Penerimaan & QC Inbound
 */
function renderTabInbound(originType, inbound, satuan) {
    const boxProd = document.getElementById('bmInboundProduksiBox');
    const boxTerima = document.getElementById('bmInboundTerimaBox');

    if (originType === 'MANUFACTURE' && inbound.produksi) {
        boxProd.style.display = 'block';
        boxTerima.style.display = 'none';

        const prod = inbound.produksi;
        document.getElementById('bmProdNoBadge').textContent = '#' + prod.produksi_no;
        document.getElementById('bmProdTgl').textContent = prod.produksi_tgl;
        document.getElementById('bmProdLini').textContent = prod.lini_produksi;
        document.getElementById('bmProdVarietas').textContent = prod.varietas_singkong || '-';
        document.getElementById('bmProdRendemen').textContent = prod.rendemen_persen ? prod.rendemen_persen + '%' : '-';
        document.getElementById('bmProdHpp').textContent = prod.hpp_satuan ? 'Rp ' + prod.hpp_satuan.toLocaleString('id-ID') : '-';
        document.getElementById('bmProdQtyHasil').textContent = prod.qty_hasil ? prod.qty_hasil.toLocaleString('id-ID') + ' ' + prod.satuan_cd : '-';

        // Render bahan baku yang dipakai
        const tbodyBahan = document.getElementById('bmProdBahanTbody');
        if (prod.raw_materials_used && prod.raw_materials_used.length > 0) {
            let htmlBahan = '';
            prod.raw_materials_used.forEach(bm => {
                htmlBahan += `
                    <tr>
                        <td><strong>${bm.barang_nm}</strong></td>
                        <td><span style="font-family: monospace; font-weight: 700; color: #0284c7;">${bm.batch_no}</span></td>
                        <td style="text-align: center;">${bm.grade_cd}</td>
                        <td style="text-align: right; font-weight: 700;">${bm.qty.toLocaleString('id-ID')} Kg</td>
                    </tr>
                `;
            });
            tbodyBahan.innerHTML = htmlBahan;
        } else {
            tbodyBahan.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 1rem; color: #94a3b8;">Rincian bahan baku tidak dicatat atau diinput via ringkasan HPP.</td></tr>`;
        }
    } else {
        boxProd.style.display = 'none';
        boxTerima.style.display = 'block';

        // Penerimaan GRN
        const terimaList = inbound.terima_list || [];
        const tbodyTerima = document.getElementById('bmTerimaTbody');
        if (terimaList.length === 0) {
            tbodyTerima.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 1.5rem; color: #94a3b8;">Tidak ada data dokumen penerimaan (GRN) terdaftar untuk batch ini.</td></tr>`;
        } else {
            let htmlTerima = '';
            terimaList.forEach(t => {
                htmlTerima += `
                    <tr>
                        <td><strong style="color: #0284c7; font-family: monospace;">${t.terima_no}</strong></td>
                        <td>${t.terima_tgl}</td>
                        <td><strong style="color: #0f172a;">${t.supplier_nm}</strong></td>
                        <td>${t.suratjalan_no}</td>
                        <td>${t.po_no ? '#' + t.po_no : '<span style="color: #94a3b8;">-</span>'}</td>
                        <td>${t.gudang_nm}</td>
                        <td style="text-align: center;">${t.grade_cd}</td>
                        <td style="text-align: right; font-weight: 800; color: #059669;">
                            +${t.terima_qty.toLocaleString('id-ID')} ${satuan}
                        </td>
                        <td style="text-align: right;">Rp ${t.harga_nominal.toLocaleString('id-ID')}</td>
                    </tr>
                `;
            });
            tbodyTerima.innerHTML = htmlTerima;
        }

        // Tiket QC
        const qcList = inbound.qc_list || [];
        const qcContainer = document.getElementById('bmQcCardsContainer');
        document.getElementById('bmQcCountBadge').textContent = qcList.length + ' Tiket QC';

        if (qcList.length === 0) {
            qcContainer.innerHTML = `<div style="text-align: center; padding: 1.5rem; color: #94a3b8; border: 1px dashed #cbd5e1; border-radius: 8px;">Tidak ada tiket inspeksi QC kedatangan terkait batch ini.</div>`;
        } else {
            let htmlQc = '';
            qcList.forEach(qc => {
                const firstDtl = (qc.details && qc.details.length > 0) ? qc.details[0] : {};
                htmlQc += `
                    <div class="batch-qc-card">
                        <div class="batch-qc-card-header">
                            <div>
                                <span style="font-weight: 800; color: #0f172a;">Tiket QC #${qc.qc_no}</span>
                                <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                                    Tgl Periksa: <strong>${qc.tgl_periksa}</strong> &bull; Petugas: <strong>${qc.petugas_qc_nama || '-'}</strong>
                                </span>
                            </div>
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.72rem; padding: 2px 8px; border-radius: 4px;">
                                ${qc.status_qc}
                            </span>
                        </div>

                        <div class="batch-qc-metrics-grid">
                            <div class="batch-qc-metric-cell">
                                <div class="batch-qc-metric-title">Gross Timbang</div>
                                <div class="batch-qc-metric-val">${(firstDtl.qty_gross || 0).toLocaleString('id-ID')} kg</div>
                            </div>
                            <div class="batch-qc-metric-cell">
                                <div class="batch-qc-metric-title">Kadar Air</div>
                                <div class="batch-qc-metric-val" style="color: #0284c7;">${firstDtl.kadar_air_persen || 0}%</div>
                            </div>
                            <div class="batch-qc-metric-cell">
                                <div class="batch-qc-metric-title">Refraksi</div>
                                <div class="batch-qc-metric-val" style="color: #d97706;">-${(firstDtl.qty_refraksi || 0).toLocaleString('id-ID')} kg <small>(${firstDtl.refraksi_persen || 0}%)</small></div>
                            </div>
                            <div class="batch-qc-metric-cell">
                                <div class="batch-qc-metric-title">Sampel Afkir</div>
                                <div class="batch-qc-metric-val" style="color: #dc2626;">${(firstDtl.qty_reject || 0).toLocaleString('id-ID')} kg</div>
                            </div>
                            <div class="batch-qc-metric-cell">
                                <div class="batch-qc-metric-title">Netto Lolos</div>
                                <div class="batch-qc-metric-val" style="color: #059669;">${(firstDtl.qty_netto_lolos || 0).toLocaleString('id-ID')} kg</div>
                            </div>
                            <div class="batch-qc-metric-cell">
                                <div class="batch-qc-metric-title">Grade / Posisi</div>
                                <div class="batch-qc-metric-val" style="font-size: 0.875rem;">
                                    ${firstDtl.grade_cd || 'A'} &bull; ${qc.posisi_bak || 'Bak Umum'}
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.75rem; color: #64748b; display: flex; justify-content: space-between; border-top: 1px dashed #f1f5f9; padding-top: 0.5rem;">
                            <span>🚛 Armada Truk: <strong>${qc.plat_nomor_truk || '-'}</strong> (Sopir: ${qc.sopir_nama || '-'})</span>
                            <span>Kondisi Fisik: <strong>${firstDtl.kondisi_fisik || 'Normal'}</strong></span>
                        </div>
                    </div>
                `;
            });
            qcContainer.innerHTML = htmlQc;
        }
    }
}

/**
 * Render Tab 3: Outbound Pemakaian
 */
function renderTabOutbound(items, satuan) {
    const tbody = document.getElementById('bmPakaiTbody');
    if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">Batch ini belum pernah dikeluarkan atau dipakai dalam proses produksi.</td></tr>`;
        return;
    }

    let html = '';
    items.forEach(p => {
        html += `
            <tr>
                <td><strong style="font-family: monospace; color: #0284c7;">${p.pakai_no}</strong></td>
                <td>${p.pakai_tgl}</td>
                <td>${p.gudang_nm}</td>
                <td><strong style="color: #0f172a;">${p.tujuan_pemakaian}</strong></td>
                <td style="text-align: center;">${p.grade_cd}</td>
                <td style="text-align: right; font-weight: 800; color: #dc2626;">
                    -${p.qty_keluar.toLocaleString('id-ID')} ${satuan}
                </td>
                <td style="text-align: right;">
                    Rp ${p.total_harga.toLocaleString('id-ID')}
                </td>
                <td>
                    ${p.produksi_no ? '<span style="font-size: 0.75rem; background: #f0fdf4; color: #166534; padding: 2px 6px; border-radius: 4px; font-weight: 700;">SPK: ' + p.produksi_no + '</span><br>' : ''}
                    <small style="color: #64748b;">${p.keterangan_txt || '-'}</small>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

/**
 * Render Tab 4: Ledger Chronological Timeline
 */
function renderTabLedger(items, satuan) {
    const container = document.getElementById('bmLedgerTimeline');
    if (!items || items.length === 0) {
        container.innerHTML = `<div style="text-align: center; padding: 2rem; color: #94a3b8;">Belum ada jejak mutasi kartu stok untuk batch ini.</div>`;
        return;
    }

    let html = '';
    items.forEach(it => {
        const isEntryIn = it.tipe_transaksi_cd === 'IN';
        const bulletClass = isEntryIn ? 'in' : 'out';
        const qtyPrefix = isEntryIn ? '+' : '-';
        const qtyColor = isEntryIn ? '#059669' : '#dc2626';

        html += `
            <div class="batch-timeline-item">
                <div class="batch-timeline-bullet ${bulletClass}"></div>
                <div class="batch-timeline-content">
                    <div class="batch-timeline-header">
                        <div>
                            <span style="font-family: monospace; font-weight: 700; color: #0284c7; font-size: 0.85rem;">
                                ${it.dokumen_no}
                            </span>
                            <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.4rem;">
                                &bull; ${it.transaksi_tgl} &bull; <strong>${it.gudang_nm}</strong>
                            </span>
                        </div>
                        <div>
                            <span style="font-weight: 800; color: ${qtyColor}; font-size: 0.95rem;">
                                ${qtyPrefix}${it.qty.toLocaleString('id-ID')} ${satuan}
                            </span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #475569; margin-top: 0.25rem;">
                        <span>Grade: <strong>${it.grade_cd}</strong> &bull; Keterangan: ${it.keterangan_txt || '-'}</span>
                        <span>Saldo Akhir Berjalan: <strong style="color: #0f172a;">${it.saldoakhir_qty.toLocaleString('id-ID')} ${satuan}</strong></span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// ESC Key listener
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeBatchDetailModal();
    }
});

/**
 * Smart Action Dropdown (Rule #2 ERP Mirasa)
 */
(function() {
    let activeDropdownMenu = null;

    window.toggleSmartActionDropdown = function (triggerEl, event, menuId) {
        if (event) event.stopPropagation();

        const menuEl = document.getElementById(menuId);
        if (!menuEl) return;

        if (activeDropdownMenu && activeDropdownMenu !== menuEl) {
            activeDropdownMenu.style.display = 'none';
        }

        if (menuEl.style.display === 'block') {
            menuEl.style.display = 'none';
            activeDropdownMenu = null;
            return;
        }

        const rect = triggerEl.getBoundingClientRect();
        const menuWidth = 190;
        const menuHeight = 110;

        let left = rect.right - menuWidth;
        if (left < 10) left = 10;

        let top = rect.bottom + 4;
        if (top + menuHeight > window.innerHeight) {
            top = rect.top - menuHeight - 4;
        }

        menuEl.style.position = 'fixed';
        menuEl.style.top = `${top}px`;
        menuEl.style.left = `${left}px`;
        menuEl.style.display = 'block';

        activeDropdownMenu = menuEl;
    };

    document.addEventListener('click', function () {
        if (activeDropdownMenu) {
            activeDropdownMenu.style.display = 'none';
            activeDropdownMenu = null;
        }
    });

    window.addEventListener('scroll', function () {
        if (activeDropdownMenu) {
            activeDropdownMenu.style.display = 'none';
            activeDropdownMenu = null;
        }
    }, true);
})();

