/**
 * JavaScript Interaktif Buku Besar (Interactive General Ledger) - ERP PT Mirasa
 * Sesuai Pedoman AGENTS.md (No inline JS, Clean Canvas Chart, AJAX Navigation)
 */

let glChartInstance = null;
let currentCoaId = null;

document.addEventListener('DOMContentLoaded', function () {
    const activeItem = document.querySelector('.gl-account-item.active');
    if (activeItem) {
        currentCoaId = activeItem.dataset.coaId;
    }

    // Inisialisasi Grafik Awal jika ada data
    if (window.appConfig && window.appConfig.initialChart) {
        renderDailyBalanceChart(window.appConfig.initialChart.labels, window.appConfig.initialChart.values);
    }

    // 1. Event Listener Klik Akun di Sidebar
    document.querySelectorAll('.gl-account-item').forEach(item => {
        item.addEventListener('click', function () {
            const coaId = this.dataset.coaId;
            if (coaId === currentCoaId) return;

            document.querySelectorAll('.gl-account-item').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
            currentCoaId = coaId;

            loadLedgerData(coaId);
        });
    });

    // 2. Realtime Search Akun di Sidebar
    const searchInput = document.getElementById('glAccountSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            const activeTab = document.querySelector('.gl-tab-chip.active')?.dataset.kategori || 'ALL';

            filterSidebarAccounts(keyword, activeTab);
        });
    }

    // 3. Filter Tab Kategori Akun
    document.querySelectorAll('.gl-tab-chip').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.gl-tab-chip').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const kategori = this.dataset.kategori;
            const keyword = document.getElementById('glAccountSearch')?.value.toLowerCase().trim() || '';

            filterSidebarAccounts(keyword, kategori);
        });
    });

    // 4. Filter Periode Tanggal
    const formFilter = document.getElementById('glPeriodFilterForm');
    if (formFilter) {
        formFilter.addEventListener('submit', function (e) {
            e.preventDefault();
            if (currentCoaId) {
                loadLedgerData(currentCoaId);
            }
        });
    }

    const monthInput = document.getElementById('glMonthSelect');
    if (monthInput) {
        monthInput.addEventListener('change', function () {
            // Update start_date & end_date input
            const val = this.value; // YYYY-MM
            if (val) {
                const parts = val.split('-');
                const y = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const lastDay = new Date(y, m, 0).getDate();

                const startInput = document.getElementById('glStartDate');
                const endInput = document.getElementById('glEndDate');

                if (startInput) startInput.value = `${val}-01`;
                if (endInput) endInput.value = `${val}-${String(lastDay).padStart(2, '0')}`;

                if (currentCoaId) {
                    loadLedgerData(currentCoaId);
                }
            }
        });
    }
});

/**
 * Filter item akun di sidebar berdasarkan teks dan kategori
 */
function filterSidebarAccounts(keyword, kategori) {
    document.querySelectorAll('.gl-account-item').forEach(item => {
        const name = (item.dataset.name || '').toLowerCase();
        const code = (item.dataset.code || '').toLowerCase();
        const itemKat = item.dataset.kategori || '';

        const matchText = !keyword || name.includes(keyword) || code.includes(keyword);
        const matchKat = (kategori === 'ALL') || (itemKat === kategori);

        if (matchText && matchKat) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Memuat detail buku besar akun via AJAX
 */
function loadLedgerData(coaId) {
    const startDate = document.getElementById('glStartDate')?.value || '';
    const endDate = document.getElementById('glEndDate')?.value || '';
    const month = document.getElementById('glMonthSelect')?.value || '';

    const url = new URL(window.appConfig.bukuBesarUrl, window.location.origin);
    url.searchParams.set('coa_id', coaId);
    if (startDate) url.searchParams.set('start_date', startDate);
    if (endDate) url.searchParams.set('end_date', endDate);
    if (month) url.searchParams.set('month', month);

    // Tampilkan state loading
    const tbody = document.getElementById('glTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 2.5rem; color: #64748b;">
                    <div style="display: inline-flex; align-items: center; gap: 8px;">
                        <svg class="animate-spin" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="4" style="opacity: 0.25;"></circle><path d="M4 12a8 8 0 018-8" stroke-width="4" style="opacity: 0.75;"></path></svg>
                        <span>Memuat buku besar...</span>
                    </div>
                </td>
            </tr>
        `;
    }

    fetch(url.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.success && res.ledger_data) {
            updateLedgerUI(res.ledger_data);
            // Update URL browser tanpa reload
            window.history.replaceState({}, '', url.toString());
        }
    })
    .catch(err => {
        console.error('Gagal memuat buku besar:', err);
    });
}

/**
 * Memperbarui tampilan buku besar setelah AJAX
 */
function updateLedgerUI(data) {
    const coa = data.coa;

    // 1. Header Akun Terpilih
    const elTitle = document.getElementById('glActiveAccountName');
    const elCode = document.getElementById('glActiveAccountCode');
    const elSub = document.getElementById('glActiveAccountSub');
    const elNormal = document.getElementById('glActiveAccountNormal');

    if (elTitle) elTitle.textContent = coa.nama_akun;
    if (elCode) elCode.textContent = coa.kode_akun;
    if (elSub) elSub.textContent = `${coa.kategori_akun} • ${coa.sub_kategori || '-'}`;
    if (elNormal) elNormal.textContent = `Saldo Normal: ${coa.saldo_normal}`;

    // 2. Kartu Metrik
    const elAwal = document.getElementById('glMetricSaldoAwal');
    const elDebet = document.getElementById('glMetricTotalDebet');
    const elKredit = document.getElementById('glMetricTotalKredit');
    const elAkhir = document.getElementById('glMetricSaldoAkhir');

    if (elAwal) elAwal.textContent = 'Rp ' + Math.round(data.saldo_awal_periode).toLocaleString('id-ID');
    if (elDebet) elDebet.textContent = 'Rp ' + Math.round(data.total_debet_periode).toLocaleString('id-ID');
    if (elKredit) elKredit.textContent = 'Rp ' + Math.round(data.total_kredit_periode).toLocaleString('id-ID');
    if (elAkhir) elAkhir.textContent = 'Rp ' + Math.round(data.saldo_akhir_periode).toLocaleString('id-ID');

    // 3. Render Tabel Baris Mutasi
    renderLedgerTable(data);

    // 4. Render Grafik
    if (data.chart) {
        renderDailyBalanceChart(data.chart.labels, data.chart.values);
    }
}

/**
 * Render baris tabel buku besar
 */
function renderLedgerTable(data) {
    const tbody = document.getElementById('glTableBody');
    if (!tbody) return;

    let html = '';

    // Baris Saldo Awal
    html += `
        <tr class="gl-row-beginning">
            <td style="text-align: center;">-</td>
            <td style="font-family: monospace;">-</td>
            <td style="font-family: monospace;">-</td>
            <td style="font-weight: 700;">Saldo Awal Periode</td>
            <td style="text-align: right;">-</td>
            <td style="text-align: right;">-</td>
            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                Rp ${Math.round(data.saldo_awal_periode).toLocaleString('id-ID')}
            </td>
        </tr>
    `;

    if (data.lines && data.lines.length > 0) {
        data.lines.forEach(line => {
            const tgl = line.tgl_jurnal ? new Date(line.tgl_jurnal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
            const debetStr = line.debet > 0 ? 'Rp ' + Math.round(line.debet).toLocaleString('id-ID') : '-';
            const kreditStr = line.kredit > 0 ? 'Rp ' + Math.round(line.kredit).toLocaleString('id-ID') : '-';
            const saldoStr = 'Rp ' + Math.round(line.saldo_berjalan).toLocaleString('id-ID');

            let refLink = line.ref_no || '-';
            if (line.ref_no && line.tipe_transaksi === 'HUTANG_PEMBELIAN' && line.ref_id) {
                refLink = `<button type="button" class="gl-ref-pill gl-ref-btn" onclick="openDetailTagihanModal(${line.ref_id}, '${line.ref_no}')" title="Klik untuk lihat rincian tagihan (pop-up)">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>${line.ref_no}</span>
                </button>`;
            } else if (line.ref_no) {
                refLink = `<span class="gl-ref-pill">${line.ref_no}</span>`;
            }

            html += `
                <tr>
                    <td style="text-align: center; white-space: nowrap; color: #475569;">${tgl}</td>
                    <td style="font-family: monospace; font-size: 0.775rem; color: #64748b;">${line.jurnal_no || '-'}</td>
                    <td>${refLink}</td>
                    <td style="line-height: 1.35; font-weight: 500;">${line.keterangan}</td>
                    <td style="text-align: right; font-weight: 700; color: #0284c7;">${debetStr}</td>
                    <td style="text-align: right; font-weight: 700; color: #d97706;">${kreditStr}</td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a;">${saldoStr}</td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="7" class="gl-empty-state">
                    Tidak ada mutasi transaksi pada periode ini untuk akun terpilih.
                </td>
            </tr>
        `;
    }

    // Baris Total Mutasi
    html += `
        <tr class="gl-row-total">
            <td colspan="4" style="text-align: right; text-transform: uppercase; font-size: 0.75rem; color: #475569;">
                Total Mutasi Periode
            </td>
            <td style="text-align: right; font-weight: 800; color: #0284c7;">
                Rp ${Math.round(data.total_debet_periode).toLocaleString('id-ID')}
            </td>
            <td style="text-align: right; font-weight: 800; color: #d97706;">
                Rp ${Math.round(data.total_kredit_periode).toLocaleString('id-ID')}
            </td>
            <td style="text-align: right;">-</td>
        </tr>
    `;

    // Baris Saldo Akhir
    html += `
        <tr class="gl-row-ending">
            <td colspan="4" style="text-align: right; text-transform: uppercase; font-size: 0.775rem;">
                Saldo Akhir Berjalan
            </td>
            <td colspan="2"></td>
            <td style="text-align: right; font-weight: 900; font-size: 0.9rem;">
                Rp ${Math.round(data.saldo_akhir_periode).toLocaleString('id-ID')}
            </td>
        </tr>
    `;

    tbody.innerHTML = html;
}

/**
 * Render Grafik Saldo Harian menggunakan Canvas 2D murni (Super Ringan & Responsif)
 */
function renderDailyBalanceChart(labels, values) {
    const canvas = document.getElementById('glDailyCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const width = canvas.parentElement.clientWidth;
    const height = 180;

    canvas.width = width * window.devicePixelRatio;
    canvas.height = height * window.devicePixelRatio;
    ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

    ctx.clearRect(0, 0, width, height);

    if (!values || values.length === 0) return;

    const paddingLeft = 60;
    const paddingRight = 20;
    const paddingTop = 20;
    const paddingBottom = 30;

    const graphWidth = width - paddingLeft - paddingRight;
    const graphHeight = height - paddingTop - paddingBottom;

    const minVal = Math.min(...values);
    const maxVal = Math.max(...values);
    const range = (maxVal - minVal) === 0 ? 1 : (maxVal - minVal);

    // Gambar Garis Grid Horizontal
    ctx.strokeStyle = '#f1f5f9';
    ctx.lineWidth = 1;
    ctx.fillStyle = '#94a3b8';
    ctx.font = '10px Inter, sans-serif';
    ctx.textAlign = 'right';

    for (let i = 0; i <= 3; i++) {
        const y = paddingTop + (graphHeight / 3) * i;
        ctx.beginPath();
        ctx.moveTo(paddingLeft, y);
        ctx.lineTo(width - paddingRight, y);
        ctx.stroke();

        const gridVal = maxVal - (range / 3) * i;
        let formatted = Math.round(gridVal);
        if (Math.abs(formatted) >= 1000000) {
            formatted = (formatted / 1000000).toFixed(1) + ' jt';
        } else if (Math.abs(formatted) >= 1000) {
            formatted = (formatted / 1000).toFixed(0) + ' rb';
        }
        ctx.fillText(formatted, paddingLeft - 8, y + 3);
    }

    // Hitung Koordinat Titik-titik
    const points = values.map((val, idx) => {
        const x = paddingLeft + (graphWidth / (values.length - 1 || 1)) * idx;
        const y = paddingTop + graphHeight - ((val - minVal) / range) * graphHeight;
        return { x, y, val, label: labels[idx] };
    });

    // Gambar Area Gradien
    const gradient = ctx.createLinearGradient(0, paddingTop, 0, height - paddingBottom);
    gradient.addColorStop(0, 'rgba(5, 150, 105, 0.25)');
    gradient.addColorStop(1, 'rgba(5, 150, 105, 0.00)');

    ctx.fillStyle = gradient;
    ctx.beginPath();
    ctx.moveTo(points[0].x, height - paddingBottom);
    points.forEach(pt => ctx.lineTo(pt.x, pt.y));
    ctx.lineTo(points[points.length - 1].x, height - paddingBottom);
    ctx.closePath();
    ctx.fill();

    // Gambar Garis Tren Saldo
    ctx.strokeStyle = '#059669';
    ctx.lineWidth = 2.5;
    ctx.lineJoin = 'round';
    ctx.beginPath();
    points.forEach((pt, idx) => {
        if (idx === 0) ctx.moveTo(pt.x, pt.y);
        else ctx.lineTo(pt.x, pt.y);
    });
    ctx.stroke();

    // Gambar Label Tanggal di X-Axis (ambil 5 sampel tanggal)
    ctx.fillStyle = '#64748b';
    ctx.textAlign = 'center';
    const step = Math.max(1, Math.floor(labels.length / 5));
    for (let i = 0; i < labels.length; i += step) {
        const pt = points[i];
        ctx.fillText(pt.label, pt.x, height - 10);
    }
}

window.addEventListener('resize', function () {
    if (window.appConfig && window.appConfig.initialChart) {
        renderDailyBalanceChart(window.appConfig.initialChart.labels, window.appConfig.initialChart.values);
    }
});

/* ========================================================
   MODAL POP-UP DETAIL TAGIHAN (INTERACTIVE AJAX MODAL)
======================================================== */

/**
 * Buka Modal Pop-up Detail Tagihan Hutang (AJAX Fetch)
 */
function openDetailTagihanModal(hutangId, hutangNo) {
    const modal = document.getElementById('modalDetailTagihan');
    if (!modal) return;

    // Reset UI ke state loading
    document.getElementById('modalTagihanNo').textContent = hutangNo || 'Memuat Tagihan...';
    document.getElementById('modalTagihanSupplier').textContent = 'Memuat informasi rekanan...';
    const badge = document.getElementById('modalTagihanBadge');
    badge.className = 'gl-badge-status';
    badge.textContent = 'MEMUAT...';

    // Reset error & tampilkan spinner
    document.getElementById('modalTagihanLoading').innerHTML = `
        <svg class="animate-spin" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669; margin-bottom: 0.75rem;">
            <circle cx="12" cy="12" r="10" stroke-width="4" style="opacity: 0.2;"></circle>
            <path d="M4 12a8 8 0 018-8" stroke-width="4" style="opacity: 0.8;"></path>
        </svg>
        <span style="font-size: 0.875rem; font-weight: 600;">Mengambil rincian tagihan faktur...</span>
    `;
    document.getElementById('modalTagihanLoading').style.display = 'flex';
    document.getElementById('modalTagihanContent').style.display = 'none';
    modal.style.display = 'flex';

    const baseUrl = window.appConfig.hutangDetailBaseUrl || '/keuangan/hutang';
    fetch(`${baseUrl}/${hutangId}?format=json`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat detail tagihan (' + res.status + ')');
        return res.json();
    })
    .then(res => {
        if (!res.success || !res.data) throw new Error('Format data tidak valid');
        populateModalTagihan(res.data);
    })
    .catch(err => {
        console.error('Error openDetailTagihanModal:', err);
        document.getElementById('modalTagihanLoading').innerHTML = `
            <div style="text-align: center; color: #dc2626; padding: 1.5rem;">
                <p style="font-weight: 700; margin-bottom: 0.5rem;">Gagal memuat data tagihan!</p>
                <p style="font-size: 0.8rem; color: #64748b;">${err.message}</p>
                <button type="button" class="btn btn-secondary" onclick="closeDetailTagihanModal()" style="margin-top: 1rem; font-size: 0.75rem;">Tutup</button>
            </div>
        `;
    });
}

/**
 * Render data tagihan ke dalam elemen modal
 */
function populateModalTagihan(d) {
    document.getElementById('modalTagihanNo').textContent = d.hutang_no;
    document.getElementById('modalTagihanSupplier').textContent = d.supplier_nm + (d.supplier_telp && d.supplier_telp !== '-' ? ' • ' + d.supplier_telp : '');
    document.getElementById('modalTagihanFullLink').href = d.full_url;

    // Badge status
    const badge = document.getElementById('modalTagihanBadge');
    if (d.status_pembayaran === 'LUNAS') {
        badge.className = 'gl-badge-status lunas';
        badge.textContent = '✓ LUNAS';
    } else if (d.is_overdue) {
        badge.className = 'gl-badge-status overdue';
        badge.textContent = '⚠️ JATUH TEMPO';
    } else if (d.status_pembayaran === 'SEBAGIAN') {
        badge.className = 'gl-badge-status parsial';
        badge.textContent = '⏳ DIBAYAR SEBAGIAN';
    } else {
        badge.className = 'gl-badge-status belum-bayar';
        badge.textContent = '🔴 BELUM DIBAYAR';
    }

    // Meta Info
    document.getElementById('modalTagihanTgl').textContent = d.tgl_tagihan;
    document.getElementById('modalTagihanTempo').textContent = d.tgl_jatuh_tempo;
    document.getElementById('modalTagihanFakturFisik').textContent = d.no_faktur_supplier;
    document.getElementById('modalTagihanGudang').textContent = d.gudang_nm;

    // Items Body
    const itemBody = document.getElementById('modalTagihanItemBody');
    document.getElementById('modalTagihanItemCount').textContent = `${d.items.length} Barang / Rincian`;
    let itemsHtml = '';
    if (d.items && d.items.length > 0) {
        d.items.forEach((item, idx) => {
            const harga = Math.round(item.harga_satuan).toLocaleString('id-ID');
            const subtotal = Math.round(item.subtotal).toLocaleString('id-ID');
            const grnBadge = item.penerimaan_no !== '-' 
                ? `<span style="font-family: monospace; font-size: 0.72rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569;">${item.penerimaan_no}</span>`
                : '-';

            itemsHtml += `
                <tr>
                    <td style="text-align: center; color: #94a3b8;">${idx + 1}</td>
                    <td>
                        <strong style="color: #0f172a; display: block;">${item.barang_nama}</strong>
                        <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Kode: ${item.barang_kode}</span>
                    </td>
                    <td>${grnBadge}</td>
                    <td style="text-align: right; font-weight: 700;">${item.qty.toLocaleString('id-ID')} <span style="font-size: 0.7rem; color: #64748b;">${item.satuan}</span></td>
                    <td style="text-align: right; color: #475569;">Rp ${harga}</td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a;">Rp ${subtotal}</td>
                </tr>
            `;
        });
    } else {
        itemsHtml = `<tr><td colspan="6" style="text-align: center; padding: 1rem; color: #94a3b8;">Tidak ada data rincian item.</td></tr>`;
    }
    itemBody.innerHTML = itemsHtml;

    // Rekap Finansial
    document.getElementById('modalTagihanSubtotal').textContent = 'Rp ' + Math.round(d.subtotal).toLocaleString('id-ID');
    
    // Ongkir
    const ongkirRow = document.getElementById('modalTagihanOngkirRow');
    if (d.biaya_ongkir > 0) {
        ongkirRow.style.display = 'flex';
        document.getElementById('modalTagihanOngkir').textContent = '+ Rp ' + Math.round(d.biaya_ongkir).toLocaleString('id-ID');
    } else {
        ongkirRow.style.display = 'none';
    }

    // PPN
    const ppnRow = document.getElementById('modalTagihanPpnRow');
    if (d.total_ppn > 0) {
        ppnRow.style.display = 'flex';
        document.getElementById('modalTagihanPpn').textContent = '+ Rp ' + Math.round(d.total_ppn).toLocaleString('id-ID');
    } else {
        ppnRow.style.display = 'none';
    }

    // Potongan Retur
    const returRow = document.getElementById('modalTagihanReturRow');
    if (d.total_potongan_retur > 0) {
        returRow.style.display = 'flex';
        document.getElementById('modalTagihanRetur').textContent = '- Rp ' + Math.round(d.total_potongan_retur).toLocaleString('id-ID');
    } else {
        returRow.style.display = 'none';
    }

    document.getElementById('modalTagihanTotal').textContent = 'Rp ' + Math.round(d.total_tagihan).toLocaleString('id-ID');
    document.getElementById('modalTagihanDibayar').textContent = 'Rp ' + Math.round(d.total_dibayar).toLocaleString('id-ID');
    document.getElementById('modalTagihanSisa').textContent = 'Rp ' + Math.round(d.sisa_hutang).toLocaleString('id-ID');

    // Jurnal Akuntansi
    const jurnalLinesBox = document.getElementById('modalTagihanJurnalLines');
    const btnBukaJurnal = document.getElementById('btnBukaDetailJurnalModal');
    if (d.jurnal && d.jurnal.details && d.jurnal.details.length > 0) {
        window.currentTagihanJurnalId = d.jurnal.jurnal_id || null;
        document.getElementById('modalTagihanJurnalNo').textContent = d.jurnal.jurnal_no + ' (' + d.jurnal.tgl_jurnal + ')';
        if (btnBukaJurnal) {
            btnBukaJurnal.style.display = d.jurnal.jurnal_id ? 'inline-flex' : 'none';
        }
        let jHtml = '';
        d.jurnal.details.forEach(jd => {
            const isDebet = jd.debet > 0;
            const nominal = isDebet ? jd.debet : jd.kredit;
            const nomStr = 'Rp ' + Math.round(nominal).toLocaleString('id-ID');
            jHtml += `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 3px 0; border-bottom: 1px dashed #f1f5f9;">
                    <span style="font-family: monospace; ${!isDebet ? 'padding-left: 1.25rem;' : 'font-weight: 700;'} color: #334155;">
                        ${jd.kode_akun} - ${jd.nama_akun}
                    </span>
                    <span style="font-weight: 700; color: ${isDebet ? '#0284c7' : '#d97706'}; font-family: monospace;">
                        ${isDebet ? '[D] ' : '[K] '} ${nomStr}
                    </span>
                </div>
            `;
        });
        jurnalLinesBox.innerHTML = jHtml;
    } else {
        window.currentTagihanJurnalId = null;
        document.getElementById('modalTagihanJurnalNo').textContent = 'Tidak Ada Entri Jurnal';
        if (btnBukaJurnal) btnBukaJurnal.style.display = 'none';
        jurnalLinesBox.innerHTML = '<span style="color: #94a3b8; font-style: italic;">Jurnal belum dibuat untuk tagihan ini.</span>';
    }

    // Riwayat Pembayaran BKK
    const bayarSection = document.getElementById('modalTagihanBayarSection');
    const bayarBody = document.getElementById('modalTagihanBayarBody');
    if (d.pembayaran && d.pembayaran.length > 0) {
        bayarSection.style.display = 'block';
        let bHtml = '';
        d.pembayaran.forEach(b => {
            bHtml += `
                <tr>
                    <td style="font-family: monospace; font-weight: 700; color: #0284c7;">${b.bkk_no}</td>
                    <td>${b.tgl_bayar}</td>
                    <td style="font-weight: 600;">${b.kas_bank}</td>
                    <td style="text-align: right; font-weight: 800; color: #166534;">Rp ${Math.round(b.nominal).toLocaleString('id-ID')}</td>
                </tr>
            `;
        });
        bayarBody.innerHTML = bHtml;
    } else {
        bayarSection.style.display = 'none';
    }

    // Switch view
    document.getElementById('modalTagihanLoading').style.display = 'none';
    document.getElementById('modalTagihanContent').style.display = 'block';
}

/**
 * Buka Pop-up Jurnal dari Tagihan aktif
 */
window.openJurnalFromCurrentTagihan = function () {
    if (window.currentTagihanJurnalId) {
        openModalDetailJurnal(window.currentTagihanJurnalId);
    }
};

/**
 * Modal Detail Jurnal Pop-up
 */
window.openModalDetailJurnal = function (jurnalId) {
    const modal = document.getElementById('modalDetailJurnal');
    const loading = document.getElementById('modalJurnalLoading');
    const content = document.getElementById('modalJurnalContent');

    if (!modal) return;

    modal.style.display = 'flex';

    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';

    fetch(`/keuangan/jurnal/${jurnalId}?format=json`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (loading) loading.style.display = 'none';
        if (res.status === 'success' && res.data) {
            populateModalJurnalData(res.data);
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
};

function populateModalJurnalData(j) {
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
 * Tutup Modal Detail Tagihan
 */
function closeDetailTagihanModal() {
    const modal = document.getElementById('modalDetailTagihan');
    if (modal) modal.style.display = 'none';
}

/**
 * Klik area gelap luar dialog untuk menutup
 */
function handleModalBackdropClick(event) {
    if (event.target.id === 'modalDetailTagihan') {
        closeDetailTagihanModal();
    }
}

/**
 * Event listener keyboard Esc: tutup modal paling depan
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modalJurnal = document.getElementById('modalDetailJurnal');
        if (modalJurnal && modalJurnal.style.display !== 'none') {
            closeModalDetailJurnal();
            return;
        }
        closeDetailTagihanModal();
    }
});
