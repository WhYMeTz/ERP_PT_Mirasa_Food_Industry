/**
 * =========================================================================
 * ERP PT MIRASA - PEMAKAIAN BAHAN INDEX JAVASCRIPT
 * =========================================================================
 */

// Toggle expandable item row tanpa reload halaman
function togglePakaiRow(rowId, btn) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const isHidden = row.style.display === 'none' || row.style.display === '';
    row.style.display = isHidden ? 'table-row' : 'none';

    const icon = btn.querySelector('.chevron-icon');
    if (icon) {
        icon.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
    }

    if (isHidden) {
        btn.style.background = '#e2e8f0';
        btn.style.borderColor = '#dc2626';
        btn.style.color = '#dc2626';
    } else {
        btn.style.background = '#f1f5f9';
        btn.style.borderColor = '#cbd5e1';
        btn.style.color = '#475569';
    }
}

// Global Smart Action Dropdown Controller
let activeSmartDropdown = null;

function toggleSmartActionDropdown(triggerBtn, event, menuId) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const menu = document.getElementById(menuId);
    if (!menu) return;

    // Jika menu ini sudah aktif, tutup
    if (activeSmartDropdown === menu) {
        closeAllActionDropdowns();
        return;
    }

    closeAllActionDropdowns();

    // Hitung posisi trigger button
    const rect = triggerBtn.getBoundingClientRect();
    const menuWidth = 210;
    const menuHeight = 220; // Estimasi tinggi menu
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    // Posisi horizontal: utamakan rata kanan tombol
    let left = rect.right - menuWidth;
    if (left < 10) left = rect.left;
    if (left + menuWidth > windowWidth - 10) left = windowWidth - menuWidth - 10;

    // Posisi vertikal: jika ruang bawah tidak cukup, letakkan di atas tombol
    let top = rect.bottom + 4;
    if (top + menuHeight > windowHeight - 10 && rect.top > menuHeight) {
        top = rect.top - menuHeight - 4;
    }

    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
    menu.style.display = 'block';

    activeSmartDropdown = menu;
}

function closeAllActionDropdowns() {
    if (activeSmartDropdown) {
        activeSmartDropdown.style.display = 'none';
        activeSmartDropdown = null;
    }
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
}

// Tutup dropdown jika klik di luar atau saat scrolling
document.addEventListener('click', (e) => {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-action-trigger')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', () => {
    closeAllActionDropdowns();
}, { passive: true });

window.addEventListener('resize', () => {
    closeAllActionDropdowns();
});

// =========================================================================
// MODAL POPUP DETAIL DOKUMEN PEMAKAIAN BAHAN (ALA PO PEMBELIAN)
// =========================================================================

function openPakaiDetailModal(pakaiId) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modalDetailPemakaian');
    const spinner = document.getElementById('pakaiDetailLoadingSpinner');
    const content = document.getElementById('pakaiDetailContentBody');

    if (!modal) {
        console.error('Modal #modalDetailPemakaian tidak ditemukan di DOM!');
        return;
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (spinner) spinner.style.display = 'block';
    if (content) content.style.display = 'none';

    // Link tombol Full Page di titlebar & footer
    const fullUrl = `/gudang/pemakaian/${pakaiId}`;
    const btnFullHeader = document.getElementById('btnPakaiDetailFullHeader');
    const btnFullFooter = document.getElementById('btnPakaiDetailFullView');
    const btnPdf = document.getElementById('btnPakaiDetailPdf');

    if (btnFullHeader) btnFullHeader.href = fullUrl;
    if (btnFullFooter) btnFullFooter.href = fullUrl;
    if (btnPdf) btnPdf.href = `/gudang/pemakaian/export-pdf/${pakaiId}`;

    fetch(fullUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error(`HTTP error ${res.status}`);
        return res.json();
    })
    .then(res => {
        if (res.status === 'success' && res.data) {
            renderPakaiDetail(res.data);
        } else {
            alert(res.message || 'Gagal memuat rincian dokumen.');
            closePakaiDetailModal();
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan koneksi saat memuat rincian dokumen.');
        closePakaiDetailModal();
    });
}

function closePakaiDetailModal() {
    const modal = document.getElementById('modalDetailPemakaian');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        const dialog = modal.querySelector('.mac-po-dialog');
        if (dialog) dialog.classList.remove('is-maximized');
    }
}

function togglePakaiDetailMaximize() {
    const modal = document.getElementById('modalDetailPemakaian');
    if (modal) {
        const dialog = modal.querySelector('.mac-po-dialog');
        if (dialog) dialog.classList.toggle('is-maximized');
    }
}

function renderPakaiDetail(pakai) {
    const spinner = document.getElementById('pakaiDetailLoadingSpinner');
    const content = document.getElementById('pakaiDetailContentBody');

    if (spinner) spinner.style.display = 'none';
    if (content) content.style.display = 'block';

    // Titlebar
    const elNo = document.getElementById('pakaiDetailNo');
    if (elNo) elNo.textContent = pakai.pakai_no || '-';

    const elBadge = document.getElementById('pakaiDetailStatusBadge');
    const isDraft = (pakai.status_st === 'DRAFT');
    if (elBadge) {
        elBadge.textContent = isDraft ? '⏳ DRAFT GUDANG' : '✓ POSTED';
        elBadge.style.background = isDraft ? '#fef3c7' : '#ecfdf5';
        elBadge.style.color = isDraft ? '#92400e' : '#065f46';
        elBadge.style.borderColor = isDraft ? '#fde68a' : '#a7f3d0';
    }

    // Box 1: Gudang Asal
    const elGdgNm = document.getElementById('pakaiDetailGudangNm');
    if (elGdgNm) elGdgNm.textContent = pakai.gudang?.gudang_nm || '-';

    const elGdgCd = document.getElementById('pakaiDetailGudangCd');
    if (elGdgCd) elGdgCd.textContent = pakai.gudang?.gudang_cd || 'GUDANG';

    // Box 2: Tujuan Pemakaian
    const elTujuan = document.getElementById('pakaiDetailTujuan');
    if (elTujuan) elTujuan.textContent = pakai.tujuan_pemakaian || '-';

    const elCatatan = document.getElementById('pakaiDetailCatatan');
    if (elCatatan) elCatatan.textContent = pakai.catatan_txt ? `Catatan: ${pakai.catatan_txt}` : 'Tidak ada memo / catatan tambahan.';

    // Box 3: Tanggal & Waktu
    const formatDate = (str) => {
        if (!str) return '-';
        const d = new Date(str);
        if (isNaN(d.getTime())) return str;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    const formatTime = (str) => {
        if (!str) return '-';
        const d = new Date(str);
        if (isNaN(d.getTime())) return '-';
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
    };

    const elTgl = document.getElementById('pakaiDetailTgl');
    if (elTgl) elTgl.textContent = formatDate(pakai.pakai_tgl);

    const elWaktu = document.getElementById('pakaiDetailWaktu');
    if (elWaktu) elWaktu.textContent = formatTime(pakai.created_dt || pakai.created_at);

    const elCreator = document.getElementById('pakaiDetailCreator');
    if (elCreator) elCreator.textContent = pakai.creator?.nama_lengkap || pakai.creator?.username || pakai.created_by || 'Staff Gudang';

    // Box 4: Status Audit
    const elStatusText = document.getElementById('pakaiDetailStatusText');
    if (elStatusText) {
        elStatusText.textContent = isDraft ? 'DRAFT (Belum Selesai)' : 'POSTED (Selesai)';
        elStatusText.style.color = isDraft ? '#b45309' : '#059669';
    }

    const elStokEffect = document.getElementById('pakaiDetailStokEffect');
    if (elStokEffect) {
        elStokEffect.textContent = isDraft ? '⚠️ Stok belum terpotong dari gudang' : '✓ Stok fisik batch sudah terpotong';
        elStokEffect.style.color = isDraft ? '#b45309' : '#059669';
    }

    const elJurnalStatus = document.getElementById('pakaiDetailJurnalStatus');
    if (elJurnalStatus) {
        elJurnalStatus.textContent = pakai.jurnal_id ? `✓ Terbit Jurnal (#${pakai.jurnal_id})` : (isDraft ? '- Menunggu Approval' : '- Tanpa Jurnal');
    }

    // Render Kotak Riwayat Koreksi jika is_ada_koreksi true
    const koreksiBox = document.getElementById('pakaiDetailKoreksiBox');
    if (koreksiBox) {
        if (pakai.is_ada_koreksi && pakai.revisi_logs && pakai.revisi_logs.length > 0) {
            koreksiBox.style.display = 'block';
            const latestLog = pakai.revisi_logs[0];
            const elKCreator = document.getElementById('pakaiDetailKoreksiCreator');
            if (elKCreator) elKCreator.textContent = latestLog.pembuat_nama || pakai.creator_name || pakai.created_by || 'Staff Gudang';

            const elKVerifier = document.getElementById('pakaiDetailKoreksiVerifier');
            if (elKVerifier) elKVerifier.textContent = latestLog.verifikator_nama || 'Verifikator Keuangan';

            const elKDate = document.getElementById('pakaiDetailKoreksiDate');
            if (elKDate) elKDate.textContent = latestLog.revisi_tgl || '-';

            const elKBadge = document.getElementById('pakaiDetailKoreksiBadge');
            if (elKBadge) elKBadge.textContent = latestLog.kategori_kesalahan || 'KOREKSI_FISIK';

            const elKMemo = document.getElementById('pakaiDetailKoreksiMemo');
            if (elKMemo) {
                elKMemo.textContent = latestLog.catatan_koreksi ? `Catatan Verifikator: ${latestLog.catatan_koreksi}` : 'Catatan Verifikator: Disesuaikan saat verifikasi fisik & akuntansi.';
            }

            const listEl = document.getElementById('pakaiDetailKoreksiList');
            if (listEl) {
                const diffs = latestLog.perubahan_json || [];
                if (diffs.length > 0) {
                    listEl.innerHTML = diffs.map(d => `
                        <li><strong>${d.barang_nm || 'Item'}</strong> [${d.tipe || 'KOREKSI'}]: ${d.keterangan || '-'}</li>
                    `).join('');
                } else {
                    listEl.innerHTML = '<li>Penyesuaian kuantitas atau alokasi batch bahan.</li>';
                }
            }
        } else {
            koreksiBox.style.display = 'none';
        }
    }

    // Hitung Total Akumulasi Fisik
    const items = pakai.details || [];
    let totalQty = 0;
    let satuanNm = 'Kg';

    const tbody = document.getElementById('pakaiDetailItemsTbody');
    if (tbody) {
        tbody.innerHTML = '';
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">Tidak ada rincian bahan fisik pada dokumen ini.</td></tr>`;
        } else {
            items.forEach((item, idx) => {
                const q = parseFloat(item.qty_keluar || 0);
                totalQty += q;
                if (item.barang?.satuan_dasar?.satuan_nm) {
                    satuanNm = item.barang.satuan_dasar.satuan_nm;
                }

                const gCd = (item.grade_cd || 'A').toUpperCase();
                const gradeBadge = (gCd === 'A')
                    ? `<span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">🟢 Grade A</span>`
                    : ((gCd === 'B')
                        ? `<span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">🟡 Grade B</span>`
                        : `Grade ${gCd}`);

                const tr = document.createElement('tr');
                tr.style.background = idx % 2 === 1 ? '#fafafa' : '#ffffff';
                tr.innerHTML = `
                    <td style="text-align: center; color: #64748b; font-weight: 600; padding: 0.55rem 0.65rem;">${idx + 1}</td>
                    <td style="padding: 0.55rem 0.65rem;">
                        <strong style="color: #0f172a; font-size: 0.85rem; display: block;">${item.barang?.barang_nm || '-'}</strong>
                        <span style="font-family: monospace; font-size: 0.72rem; color: #64748b;">${item.barang?.barang_cd || '-'}</span>
                    </td>
                    <td style="padding: 0.55rem 0.65rem;">
                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.725rem; font-weight: 600;">
                            ${item.barang?.jenis_barang?.jenis_barang_nm || item.barang?.jenis_barang?.jenis_barang_cd || 'Bahan'}
                        </span>
                    </td>
                    <td style="padding: 0.55rem 0.65rem;">
                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <span style="font-family: monospace; font-size: 0.825rem; font-weight: 700; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                ${item.batch_no || '-'}
                            </span>
                            ${gradeBadge}
                        </div>
                    </td>
                    <td style="padding: 0.55rem 0.65rem;">
                        ${item.keterangan_txt ? `<span class="badge" style="background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-size: 0.725rem; font-weight: 600; padding: 2px 7px; border-radius: 4px;">${item.keterangan_txt}</span>` : '<span style="color: #94a3b8; font-size: 0.75rem;">-</span>'}
                    </td>
                    <td style="text-align: right; padding: 0.55rem 0.65rem;">
                        <strong style="color: #dc2626; font-family: monospace; font-size: 0.95rem;">
                            ${q.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </strong>
                    </td>
                    <td style="text-align: center; color: #64748b; font-size: 0.8rem; padding: 0.55rem 0.65rem;">
                        ${item.barang?.satuan_dasar?.satuan_nm || 'Kg'}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    // Update Tfoot Modal Fisik
    const tfoot = document.getElementById('pakaiDetailItemsTfoot');
    if (tfoot) {
        if (items.length > 0) {
            tfoot.style.display = 'table-footer-group';
            const fQty = document.getElementById('pakaiDetailFootQty');
            const fSat = document.getElementById('pakaiDetailFootSatuan');
            if (fQty) fQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (fSat) fSat.textContent = satuanNm;
        } else {
            tfoot.style.display = 'none';
        }
    }

    // Update Banner Angka Fisik
    const elTotalQty = document.getElementById('pakaiDetailTotalQty');
    if (elTotalQty) {
        elTotalQty.textContent = `${totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${satuanNm}`;
    }

    const elCountItems = document.getElementById('pakaiDetailCountItems');
    if (elCountItems) elCountItems.textContent = `${items.length} Bahan Fisik`;

    const elBadgeCount = document.getElementById('pakaiDetailTableBadgeCount');
    if (elBadgeCount) elBadgeCount.textContent = `${items.length} Item Terdaftar`;

    const elFootCount = document.getElementById('pakaiDetailFooterCount');
    if (elFootCount) elFootCount.textContent = `Menampilkan ${items.length} rincian bahan fisik`;
}

// Tutup modal jika tombol ESC ditekan
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closePakaiDetailModal();
        closeDeletePemakaianModal();
    }
});

// Modal Hapus Transaksi Pemakaian (DRAFT)
function openDeletePemakaianModal(id, no, tujuan) {
    closeAllActionDropdowns();
    const modal = document.getElementById('modal-delete-pemakaian');
    if (!modal) return;

    const form = document.getElementById('form-delete-pemakaian');
    if (form) {
        form.action = `/gudang/pemakaian/${id}`;
    }

    const elNo = document.getElementById('delete-pemakaian-no');
    if (elNo) elNo.textContent = no || '-';

    const elTujuan = document.getElementById('delete-pemakaian-tujuan');
    if (elTujuan) elTujuan.textContent = tujuan ? ('Tujuan: ' + tujuan) : '-';

    modal.style.display = 'flex';
}

function closeDeletePemakaianModal() {
    const modal = document.getElementById('modal-delete-pemakaian');
    if (modal) modal.style.display = 'none';
}

// Ekspor ke window global
window.togglePakaiRow = togglePakaiRow;
window.toggleSmartActionDropdown = toggleSmartActionDropdown;
window.closeAllActionDropdowns = closeAllActionDropdowns;
window.openPakaiDetailModal = openPakaiDetailModal;
window.closePakaiDetailModal = closePakaiDetailModal;
window.togglePakaiDetailMaximize = togglePakaiDetailMaximize;
window.openDeletePemakaianModal = openDeletePemakaianModal;
window.closeDeletePemakaianModal = closeDeletePemakaianModal;
