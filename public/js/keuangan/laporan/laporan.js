/**
 * ERP PT Mirasa Food Industry - Modul Laporan Keuangan
 * Logika Interaksi UI: Filter Periode, Ekspor Data & Cetak Laporan
 */

document.addEventListener('DOMContentLoaded', function () {
    initPresetFilterButtons();
});

/**
 * Handle klik tombol preset tanggal (Bulan Ini, Bulan Lalu, dsb)
 */
function initPresetFilterButtons() {
    const presetPills = document.querySelectorAll('.filter-preset-pill');
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
}

/**
 * Trigger cetak dokumen standar akuntansi (A4 Clean Print)
 */
function printLaporan() {
    window.print();
}

/**
 * Ekspor tabel HTML ke file CSV akuntansi
 */
function exportTableToCsv(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            // Bersihkan teks dari newline dan spasi berlebih
            let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').trim();
            // Hilangkan format Rp dan titik ribuan untuk angka agar mudah dianalisa di Excel
            text = text.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    }

    const csvFile = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename || 'laporan-keuangan.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

