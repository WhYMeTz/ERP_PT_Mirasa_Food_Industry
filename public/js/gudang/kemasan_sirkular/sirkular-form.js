/**
 * Logika Interaktif Penerimaan Kemasan Sirkular - ERP PT Mirasa
 */

document.addEventListener('DOMContentLoaded', function () {
    // Dropdown Smart Action Menu Position Fixed
    window.toggleSmartActionDropdown = function (button, event, menuId) {
        event.stopPropagation();
        const menu = document.getElementById(menuId);
        if (!menu) return;

        const isVisible = menu.style.display === 'block';
        document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');

        if (!isVisible) {
            const rect = button.getBoundingClientRect();
            menu.style.display = 'block';
            menu.style.top = `${rect.bottom + window.scrollY + 4}px`;
            menu.style.left = `${rect.right + window.scrollX - menu.offsetWidth}px`;
        }
    };

    document.addEventListener('click', function () {
        document.querySelectorAll('.action-dropdown-menu').forEach(m => m.style.display = 'none');
    });

    // Auto-fill dari Surat Jalan Pengiriman (DO)
    const selPengeluaran = document.getElementById('pengeluaran_id');
    if (selPengeluaran) {
        selPengeluaran.addEventListener('change', function () {
            const id = this.value;
            if (!id) {
                resetPengeluaranInfo();
                return;
            }

            const url = window.appConfig?.ajaxPengeluaranUrl?.replace(':id', id) || `/gudang/kemasan-sirkular/pengeluaran-detail/${id}`;
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        applyPengeluaranData(res.data);
                    }
                })
                .catch(err => console.error('Gagal mengambil data pengeluaran:', err));
        });
    }

    function applyPengeluaranData(data) {
        if (data.customer_id) {
            const custSel = document.getElementById('customer_id');
            if (custSel) custSel.value = data.customer_id;
        }
        if (data.supir_nm) {
            const supirInp = document.getElementById('supir_nm');
            if (supirInp) supirInp.value = data.supir_nm;
        }
        if (data.nopol_kendaraan) {
            const nopolInp = document.getElementById('nopol_kendaraan');
            if (nopolInp) nopolInp.value = data.nopol_kendaraan;
        }
        if (data.ekspedisi_nm) {
            const ekspedisiInp = document.getElementById('ekspedisi_nm');
            if (ekspedisiInp) ekspedisiInp.value = data.ekspedisi_nm;
        }

        const kirimBox = parseFloat(data.total_box_kirim) || 0;
        const inpKirim = document.getElementById('kirim_qty_0');
        if (inpKirim) {
            inpKirim.value = kirimBox;
        }

        const statKirim = document.getElementById('stat_total_kirim');
        if (statKirim) statKirim.textContent = `${kirimBox.toLocaleString('id-ID')} Box`;

        window.calcSortir(0);
    }

    function resetPengeluaranInfo() {
        const statKirim = document.getElementById('stat_total_kirim');
        if (statKirim) statKirim.textContent = `0 Box`;
        const inpKirim = document.getElementById('kirim_qty_0');
        if (inpKirim) inpKirim.value = 0;
        window.calcSortir(0);
    }

    // Kalkulasi Rekonsiliasi Sortir
    window.calcSortir = function (idx) {
        const inpKirim = document.getElementById(`kirim_qty_${idx}`);
        const inpKembali = document.getElementById(`kembali_qty_${idx}`);
        const inpMulus = document.getElementById(`mulus_qty_${idx}`);
        const inpBmg = document.getElementById(`bmg_qty_${idx}`);
        const inpRusak = document.getElementById(`rusak_qty_${idx}`);
        const spanSelisih = document.getElementById(`selisih_qty_${idx}`);

        const kirim = parseFloat(inpKirim?.value) || 0;
        const mulus = parseFloat(inpMulus?.value) || 0;
        const bmg = parseFloat(inpBmg?.value) || 0;
        const rusak = parseFloat(inpRusak?.value) || 0;

        const totalKembali = mulus + bmg + rusak;
        if (inpKembali) inpKembali.value = totalKembali;

        const selisih = Math.max(0, kirim - totalKembali);
        if (spanSelisih) {
            spanSelisih.textContent = selisih.toLocaleString('id-ID');
            spanSelisih.style.color = selisih > 0 ? '#dc2626' : '#059669';
        }

        // Update KPI Live Header
        const statKirim = document.getElementById('stat_total_kirim');
        const statKembali = document.getElementById('stat_total_kembali');
        const statMulus = document.getElementById('stat_total_mulus');
        const statBmg = document.getElementById('stat_total_bmg');
        const statRusak = document.getElementById('stat_total_rusak');
        const statSelisih = document.getElementById('stat_total_selisih');

        if (statKirim && kirim > 0) statKirim.textContent = `${kirim.toLocaleString('id-ID')} Box`;
        if (statKembali) statKembali.textContent = `${totalKembali.toLocaleString('id-ID')} Box`;
        if (statMulus) statMulus.textContent = `${mulus.toLocaleString('id-ID')} Box`;
        if (statBmg) statBmg.textContent = `${bmg.toLocaleString('id-ID')} Box`;
        if (statRusak) statRusak.textContent = `${rusak.toLocaleString('id-ID')} Box`;
        if (statSelisih) {
            statSelisih.textContent = `${selisih.toLocaleString('id-ID')} Box`;
            statSelisih.style.color = selisih > 0 ? '#dc2626' : '#059669';
        }
    };
});

