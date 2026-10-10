/**
 * =========================================================================
 * ERP PT MIRASA - MASTER DATA TARIF PRODUKSI & FOH JAVASCRIPT
 * Authentic Industrial Enterprise ERP
 * =========================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    let currentTarifItem = null;

    // Helper Format Angka & Rupiah
    function formatNumber(val, decimals = 2) {
        return parseFloat(val || 0).toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    // Live Readout Update
    window.updateLiveTarifReadout = function () {
        const input = document.getElementById('editNilaiTarif');
        const readout = document.getElementById('editReadoutText');
        if (!input || !readout || !currentTarifItem) return;

        const val = parseFloat(input.value) || 0;
        const basis = currentTarifItem.satuan_basis;

        if (basis === 'PER_KG_WIP') {
            readout.textContent = 'x ' + formatNumber(val, 2) + ' per Kg WIP';
        } else if (basis === 'PER_MMBTU') {
            readout.textContent = 'Rp ' + formatNumber(val, 2) + ' per MMBTU';
        } else if (basis === 'PER_ORANG') {
            readout.textContent = 'Rp ' + formatNumber(val, 2) + ' per Orang / Hari';
        } else if (basis === 'PER_SHIFT') {
            readout.textContent = 'Rp ' + Math.round(val).toLocaleString('id-ID') + ' per Shift (Flat)';
        } else {
            readout.textContent = 'Rp ' + formatNumber(val, 2);
        }
    };

    // Modal Edit Binding
    window.openEditTarifModal = function (item) {
        currentTarifItem = item;

        document.getElementById('editTarifId').value = item.tarif_id;
        document.getElementById('editNamaTarif').textContent = item.nama_tarif;
        document.getElementById('editKodeTarif').textContent = item.kode_tarif;
        document.getElementById('editSatuanLabel').textContent = item.satuan_label;
        document.getElementById('editNilaiTarif').value = item.nilai_tarif;
        document.getElementById('editKeterangan').value = item.keterangan || '';

        // Kategori Badge
        const katBadge = document.getElementById('editKategoriBadge');
        if (katBadge) {
            katBadge.className = 'badge-category';
            if (item.kategori === 'FOH') {
                katBadge.classList.add('badge-foh');
                katBadge.textContent = 'Overhead Pabrik (FOH)';
            } else if (item.kategori === 'ENERGI') {
                katBadge.classList.add('badge-energi');
                katBadge.textContent = 'Energi Gas Alam';
            } else {
                katBadge.classList.add('badge-tk');
                katBadge.textContent = 'Tenaga Kerja';
            }
        }

        // Addon Prefix & Suffix
        const prefix = document.getElementById('editAddonPrefix');
        const suffix = document.getElementById('editAddonSuffix');
        const nilaiLamaText = document.getElementById('editNilaiLamaText');

        if (item.satuan_basis === 'PER_KG_WIP') {
            if (prefix) prefix.textContent = 'x';
            if (suffix) suffix.textContent = '/ Kg WIP';
            if (nilaiLamaText) nilaiLamaText.textContent = 'x ' + formatNumber(item.nilai_tarif, 2);
        } else if (item.satuan_basis === 'PER_MMBTU') {
            if (prefix) prefix.textContent = 'Rp';
            if (suffix) suffix.textContent = '/ MMBTU';
            if (nilaiLamaText) nilaiLamaText.textContent = 'Rp ' + formatNumber(item.nilai_tarif, 2);
        } else if (item.satuan_basis === 'PER_ORANG') {
            if (prefix) prefix.textContent = 'Rp';
            if (suffix) suffix.textContent = '/ Orang';
            if (nilaiLamaText) nilaiLamaText.textContent = 'Rp ' + formatNumber(item.nilai_tarif, 2);
        } else if (item.satuan_basis === 'PER_SHIFT') {
            if (prefix) prefix.textContent = 'Rp';
            if (suffix) suffix.textContent = '/ Shift';
            if (nilaiLamaText) nilaiLamaText.textContent = 'Rp ' + Math.round(item.nilai_tarif).toLocaleString('id-ID');
        }

        // Trigger Live Readout
        window.updateLiveTarifReadout();

        // Form action url
        const form = document.getElementById('formEditTarif');
        form.action = `/master-tarif-produksi/${item.tarif_id}`;

        const modal = document.getElementById('modalEditTarif');
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeEditTarifModal = function () {
        const modal = document.getElementById('modalEditTarif');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    // Close on backdrop click
    const modal = document.getElementById('modalEditTarif');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeEditTarifModal();
            }
        });
    }

    // Close on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditTarifModal();
        }
    });
});
