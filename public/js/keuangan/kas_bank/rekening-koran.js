/**
 * ERP PT Mirasa Food Industry - Rekening Koran Bank
 * Logika Filter & Ekspor Cetak
 */

document.addEventListener('DOMContentLoaded', function () {
    const presetPills = document.querySelectorAll('.rk-preset-btn, .rk-preset-pill');
    presetPills.forEach(pill => {
        pill.addEventListener('click', function (e) {
            const presetVal = this.getAttribute('data-preset');
            if (!presetVal) return;

            e.preventDefault();
            const url = new URL(window.location.href);
            url.searchParams.set('preset', presetVal);
            url.searchParams.delete('start_date');
            url.searchParams.delete('end_date');
            window.location.href = url.toString();
        });
    });
});

function printRekeningKoran() {
    window.print();
}

function exportRekeningKoranToCsv(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    }

    const csvFile = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename || 'rekening-koran.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

