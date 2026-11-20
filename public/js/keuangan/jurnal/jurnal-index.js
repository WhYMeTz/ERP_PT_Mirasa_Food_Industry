/**
 * JavaScript Logika Halaman Jurnal Umum (General Journal Ledger) - ERP PT Mirasa
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi event listener jika ada
});

/**
 * Toggle Expand/Collapse baris rincian baris debet & kredit
 */
window.toggleJurnalDetails = function (jurnalId, event) {
    if (event) {
        event.stopPropagation();
    }

    const subRow = document.getElementById(`subrow-jurnal-${jurnalId}`);
    const icon = document.getElementById(`icon-toggle-${jurnalId}`);

    if (!subRow) return;

    if (subRow.style.display === 'none' || subRow.style.display === '') {
        subRow.style.display = 'table-row';
        if (icon) icon.style.transform = 'rotate(180deg)';
    } else {
        subRow.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
};

/**
 * Buka atau Tutup Semua Rincian Jurnal Sekaligus
 */
let isAllExpanded = false;
window.toggleExpandAll = function () {
    isAllExpanded = !isAllExpanded;
    const subRows = document.querySelectorAll('.jurnal-sub-row');
    const icons = document.querySelectorAll('.toggle-arrow-icon');
    const btnText = document.getElementById('textToggleExpandAll');

    subRows.forEach(row => {
        row.style.display = isAllExpanded ? 'table-row' : 'none';
    });

    icons.forEach(ic => {
        ic.style.transform = isAllExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    if (btnText) {
        btnText.textContent = isAllExpanded ? 'Tutup Semua Rincian' : 'Buka Semua Rincian';
    }
};

/**
 * Modal Detail Jurnal Pop-up
 */
window.openModalDetailJurnal = function (jurnalId) {
    // Tutup smart action dropdown jika sedang terbuka
    if (window.globalActiveActionDropdown) {
        window.globalActiveActionDropdown.style.display = 'none';
        document.querySelectorAll('.btn-action-trigger.active').forEach(b => b.classList.remove('active'));
        window.globalActiveActionDropdown = null;
    }
    document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');

    const modal = document.getElementById('modalDetailJurnal');
    const loading = document.getElementById('modalJurnalLoading');
    const content = document.getElementById('modalJurnalContent');

    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';

    fetch(`${window.appConfig.baseUrl}/keuangan/jurnal/${jurnalId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (loading) loading.style.display = 'none';
        if (res.status === 'success' && res.data) {
            populateModalData(res.data);
            if (content) content.style.display = 'block';
        }
    })
    .catch(err => {
        if (loading) loading.style.display = 'none';
        console.error('Gagal mengambil detail jurnal:', err);
    });
};

window.closeModalDetailJurnal = function () {
    const modal = document.getElementById('modalDetailJurnal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
};

function populateModalData(j) {
    const elNo = document.getElementById('modalJurnalNo');
    const elTgl = document.getElementById('modalJurnalTgl');
    const elTipe = document.getElementById('modalJurnalTipe');
    const elRef = document.getElementById('modalJurnalRef');
    const elKet = document.getElementById('modalJurnalKeterangan');
    const elUser = document.getElementById('modalJurnalCreatedBy');
    const elDebet = document.getElementById('modalJurnalTotalDebet');
    const elKredit = document.getElementById('modalJurnalTotalKredit');
    const tbody = document.getElementById('modalJurnalLinesTbody');

    if (elNo) elNo.textContent = j.jurnal_no;
    if (elTgl) elTgl.textContent = new Date(j.tgl_jurnal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    if (elTipe) elTipe.textContent = j.tipe_transaksi;
    if (elRef) elRef.textContent = j.ref_no || '-';
    if (elKet) elKet.textContent = j.keterangan || '-';
    if (elUser) elUser.textContent = j.created_by || 'SYSTEM';

    if (elDebet) elDebet.textContent = 'Rp ' + Math.round(j.total_debet).toLocaleString('id-ID');
    if (elKredit) elKredit.textContent = 'Rp ' + Math.round(j.total_kredit).toLocaleString('id-ID');

    if (tbody && j.details) {
        let linesHtml = '';
        j.details.forEach(dtl => {
            const coaCode = dtl.coa ? dtl.coa.kode_akun : '-';
            const coaName = dtl.coa ? dtl.coa.nama_akun : 'Akun';
            const debet = parseFloat(dtl.debet || 0);
            const kredit = parseFloat(dtl.kredit || 0);

            linesHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.8rem;">
                    <td style="padding: 0.6rem 0.75rem;">
                        <span class="coa-code-pill">${coaCode}</span>
                        <strong style="color: #0f172a; margin-left: 6px;">${coaName}</strong>
                    </td>
                    <td style="padding: 0.6rem 0.75rem; color: #64748b;">
                        ${dtl.keterangan || '-'}
                    </td>
                    <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 700; color: ${debet > 0 ? '#0f172a' : '#cbd5e1'}; font-family: monospace;">
                        ${debet > 0 ? 'Rp ' + Math.round(debet).toLocaleString('id-ID') : '-'}
                    </td>
                    <td style="padding: 0.6rem 0.75rem; text-align: right; font-weight: 700; color: ${kredit > 0 ? '#0f172a' : '#cbd5e1'}; font-family: monospace;">
                        ${kredit > 0 ? 'Rp ' + Math.round(kredit).toLocaleString('id-ID') : '-'}
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = linesHtml;
    }
}

/**
 * Export Lembar Lajur Spreadsheet ke File CSV / Excel
 * Format persis 13 Kolom Lembar Lajur CV Bahtera Mandiri Bersama
 */
window.exportSpreadsheetToCsv = function () {
    const table = document.getElementById('spreadsheetJurnalTable');
    if (!table) {
        alert('Tabel spreadsheet tidak ditemukan.');
        return;
    }

    const rows = table.querySelectorAll('tbody tr.sheet-row');
    if (rows.length === 0) {
        alert('Tidak ada baris transaksi untuk diexport.');
        return;
    }

    const headers = [
        'TGL', 'NAMA AKUN', 'NO AKUN', 'KETERANGAN', 'DEBET', 'KREDIT', 
        'SALDO', 'JP', 'BULAN', 'SN', 'POSISI', 'KEPALA AKUN', 'TAHUN'
    ];

    const csvLines = [];
    csvLines.push(headers.map(h => `"${h}"`).join(','));

    rows.forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length < 13) return;

        const rowData = [
            cells[0]?.innerText.trim() || '',
            cells[1]?.innerText.trim() || '',
            cells[2]?.innerText.trim() || '',
            cells[3]?.querySelector('span:first-child')?.innerText.trim() || cells[3]?.innerText.trim() || '',
            cleanNumberText(cells[4]?.innerText),
            cleanNumberText(cells[5]?.innerText),
            cleanNumberText(cells[6]?.innerText),
            cells[7]?.innerText.trim() || '',
            cells[8]?.innerText.trim() || '',
            cells[9]?.innerText.trim() || '',
            cells[10]?.innerText.trim() || '',
            cells[11]?.innerText.trim() || '',
            cells[12]?.innerText.trim() || ''
        ];

        csvLines.push(rowData.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','));
    });

    // Tambahkan BOM untuk kompatibilitas penuh Microsoft Excel Windows
    const blob = new Blob(["\uFEFF" + csvLines.join("\r\n")], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    link.href = url;
    link.setAttribute('download', `Jurnal_Umum_Mirasa_${dateStr}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

function cleanNumberText(text) {
    if (!text) return '';
    const clean = text.trim();
    if (clean === '-') return '0';
    return clean;
}
