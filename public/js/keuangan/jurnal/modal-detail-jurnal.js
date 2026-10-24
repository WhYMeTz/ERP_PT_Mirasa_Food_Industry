/**
 * Global Modal Detail Jurnal Umum (Interactive Double-Entry Audit)
 * ERP PT Mirasa Food Industry
 */

window.openModalDetailJurnal = function (jurnalId) {
    const modal = document.getElementById('modalDetailJurnal');
    const loading = document.getElementById('modalJurnalLoading');
    const content = document.getElementById('modalJurnalContent');

    if (!modal) {
        console.warn('Elemen modalDetailJurnal tidak ditemukan di DOM.');
        return;
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';

    fetch(`/keuangan/jurnal/${jurnalId}?format=json`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat detail jurnal (' + res.status + ')');
        return res.json();
    })
    .then(res => {
        if (loading) loading.style.display = 'none';
        if (res.status === 'success' && res.data) {
            populateGlobalModalJurnalData(res.data);
            if (content) content.style.display = 'block';
        } else {
            throw new Error(res.message || 'Format data jurnal tidak valid');
        }
    })
    .catch(err => {
        if (loading) {
            loading.innerHTML = `
                <div style="color: #dc2626; padding: 1rem;">
                    <p style="font-weight: 700;">Gagal memuat rincian jurnal!</p>
                    <p style="font-size: 0.75rem; color: #64748b;">${err.message}</p>
                    <button type="button" class="btn btn-secondary" onclick="closeModalDetailJurnal()" style="margin-top: 0.5rem; font-size: 0.75rem;">Tutup</button>
                </div>
            `;
        }
        console.error('Error openModalDetailJurnal:', err);
    });
};

window.closeModalDetailJurnal = function () {
    const modal = document.getElementById('modalDetailJurnal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
};

function populateGlobalModalJurnalData(j) {
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

// Handler keyboard ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modalJurnal = document.getElementById('modalDetailJurnal');
        if (modalJurnal && modalJurnal.style.display !== 'none') {
            closeModalDetailJurnal();
        }
    }
});
