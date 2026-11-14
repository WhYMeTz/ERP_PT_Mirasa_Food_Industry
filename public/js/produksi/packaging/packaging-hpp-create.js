/**
 * public/js/produksi/packaging/packaging-hpp-create.js
 * Logika Kalkulasi & Penetapan Lembar HPP Pengemasan Barang Jadi (FG)
 * ERP PT Mirasa Food Industry
 */

let currentPackagingData = null;

document.addEventListener('DOMContentLoaded', function () {
    const selectPack = document.getElementById('pack_id');
    if (selectPack && selectPack.value) {
        onPackagingSelectChange(selectPack.value);
    }
});

function onPackagingSelectChange(packId) {
    const wipFoot = document.getElementById('wipTableFoot');
    const kemasanFoot = document.getElementById('kemasanTableFoot');

    if (!packId) {
        currentPackagingData = null;
        document.getElementById('packParamBox').style.display = 'none';
        document.getElementById('wipTableBody').innerHTML = `
            <tr>
                <td colspan="6" style="padding: 2.5rem 1.5rem; text-align: center; background: #ffffff;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; max-width: 420px; margin: 0 auto; gap: 0.5rem;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; border: 1px solid #e2e8f0;">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <strong style="font-size: 0.875rem; color: #1e293b;">Menunggu Pemilihan Dokumen Pengemasan</strong>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4;">
                            Pilih nomor dokumen fisik pada <strong>Card 1</strong> di atas untuk memuat rincian keripik curah dan kalkulasi HPP dapur secara otomatis.
                        </p>
                    </div>
                </td>
            </tr>
        `;
        document.getElementById('kemasanTableBody').innerHTML = `
            <tr>
                <td colspan="6" style="padding: 2.5rem 1.5rem; text-align: center; background: #ffffff;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; max-width: 420px; margin: 0 auto; gap: 0.5rem;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; border: 1px solid #e2e8f0;">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <strong style="font-size: 0.875rem; color: #1e293b;">Menunggu Pemilihan Dokumen Pengemasan</strong>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4;">
                            Pilih dokumen pada <strong>Card 1</strong> di atas untuk memuat rincian dus karton, plastik roll, lakban, dan label.
                        </p>
                    </div>
                </td>
            </tr>
        `;
        if (wipFoot) wipFoot.style.display = 'none';
        if (kemasanFoot) kemasanFoot.style.display = 'none';
        resetHppDisplays();
        return;
    }

    const loadUrl = window.appConfig.urlPackagingData.replace('__ID__', packId);

    // Show loading state
    document.getElementById('wipTableBody').innerHTML = `
        <tr>
            <td colspan="6" style="text-align: center; padding: 2rem; color: #0284c7;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="animate-spin" style="display: inline-block; vertical-align: middle; margin-right: 0.5rem;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity: 0.75;"></path></svg>
                <span>Menarik data dokumen pengemasan &amp; HPP WIP dapur...</span>
            </td>
        </tr>
    `;

    fetch(loadUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Gagal memuat data dari server');
        return res.json();
    })
    .then(json => {
        if (json.status !== 'success') {
            throw new Error(json.message || 'Gagal memuat rincian pengemasan');
        }
        currentPackagingData = json.data;
        renderPackagingData(json.data);
    })
    .catch(err => {
        alert('Terjadi kesalahan: ' + err.message);
        console.error(err);
    });
}

function renderPackagingData(data) {
    // 1. Parameter Grid (Card 1)
    document.getElementById('packParamBox').style.display = 'block';
    document.getElementById('pvPackNo').innerText = data.pack_no;
    document.getElementById('pvPackTgl').innerText = data.pack_tgl_formatted;
    document.getElementById('pvBarangJadi').innerText = data.barang_jadi_nm;
    document.getElementById('pvGudang').innerText = data.gudang_nm;
    document.getElementById('pvBatchNo').innerText = data.batch_no || '-';
    document.getElementById('pvHasilKarton').innerText = `${Number(data.hasil_karton_qty).toLocaleString('id-ID')} Karton (${Number(data.hasil_pcs_qty).toLocaleString('id-ID')} Pcs)`;

    // 2. Info SPK Dapur Asal & Curah WIP (Card 2)
    const spkBanner = document.getElementById('spkMasakBanner');
    if (data.produksi_id) {
        spkBanner.style.display = 'flex';
        document.getElementById('lblSpkNo').innerText = data.produksi_no;
        document.getElementById('lblSpkTgl').innerText = data.produksi_tgl;
        document.getElementById('lblHppWipStatus').innerText = data.hpp_per_kg_wip > 0 
            ? `Rp ${Number(data.hpp_per_kg_wip).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2})} / kg` 
            : 'Belum Dihitung';
        document.getElementById('lblHppWipStatus').style.color = data.hpp_per_kg_wip > 0 ? '#166534' : '#b45309';
    } else {
        spkBanner.style.display = 'none';
    }

    let wipHtml = '';
    if (data.wip_items && data.wip_items.length > 0) {
        data.wip_items.forEach((item, idx) => {
            wipHtml += `
                <tr>
                    <td style="text-align: center;">${idx + 1}</td>
                    <td>
                        <strong style="color: #0f172a;">${item.barang_nm}</strong>
                        <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">Kode: ${item.barang_cd} &bull; Batch: ${item.batch_no}</div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">
                        ${Number(item.qty_aktual).toLocaleString('id-ID', {minimumFractionDigits: 1, maximumFractionDigits: 2})} kg
                    </td>
                    <td style="text-align: right;">
                        <div class="input-rp-wrap" style="max-width: 140px; margin-left: auto;">
                            <span class="prefix-rp">Rp</span>
                            <input type="number" step="any" min="0" 
                                   name="wip_items[${item.packdtl_id}][harga_satuan]" 
                                   id="wip_harga_${item.packdtl_id}" 
                                   class="hpp-input text-right" 
                                   value="${item.harga_satuan}" 
                                   data-qty="${item.qty_aktual}" 
                                   data-id="${item.packdtl_id}" 
                                   oninput="onWipPriceChange(this)">
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a;" id="wip_subtotal_${item.packdtl_id}">
                        Rp ${Number(item.subtotal).toLocaleString('id-ID')}
                    </td>
                    <td style="text-align: center;">
                        <span class="pck-hpp-badge">
                            ✓ ${data.is_wip_hpp_ready ? 'Dapur Terverifikasi' : 'Acuan Standar'}
                        </span>
                    </td>
                </tr>
            `;
        });
        document.getElementById('wipTableFoot').style.display = 'table-footer-group';
    } else {
        wipHtml = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 1.5rem; color: #64748b;">
                    Tidak ada rincian bahan keripik curah WIP yang terhubung ke dokumen ini.
                </td>
            </tr>
        `;
        document.getElementById('wipTableFoot').style.display = 'none';
    }
    document.getElementById('wipTableBody').innerHTML = wipHtml;

    // 3. Info Kemasan (Card 3)
    let kemasanHtml = '';
    if (data.kemasan_items && data.kemasan_items.length > 0) {
        data.kemasan_items.forEach((item, idx) => {
            kemasanHtml += `
                <tr>
                    <td style="text-align: center;">${idx + 1}</td>
                    <td>
                        <strong style="color: #0f172a;">${item.barang_nm}</strong>
                        <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">Kode: ${item.barang_cd} &bull; Tipe: ${item.kategori_bahan}</div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">
                        ${Number(item.qty_aktual).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2})} ${item.satuan_cd}
                    </td>
                    <td style="text-align: right;">
                        <div class="input-rp-wrap" style="max-width: 140px; margin-left: auto;">
                            <span class="prefix-rp">Rp</span>
                            <input type="number" step="any" min="0" 
                                   name="kemasan_items[${item.packdtl_id}][harga_satuan]" 
                                   id="kemasan_harga_${item.packdtl_id}" 
                                   class="hpp-input text-right" 
                                   value="${item.harga_satuan}" 
                                   data-qty="${item.qty_aktual}" 
                                   data-id="${item.packdtl_id}" 
                                   oninput="onKemasanPriceChange(this)">
                        </div>
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a;" id="kemasan_subtotal_${item.packdtl_id}">
                        Rp ${Number(item.subtotal).toLocaleString('id-ID')}
                    </td>
                    <td style="text-align: center;">
                        <span style="font-size: 0.7rem; font-weight: 600; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                            Bahan Kemas
                        </span>
                    </td>
                </tr>
            `;
        });
        document.getElementById('kemasanTableFoot').style.display = 'table-footer-group';
    } else {
        kemasanHtml = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 1.5rem; color: #64748b;">
                    Tidak ada rincian bahan kemasan yang tercatat.
                </td>
            </tr>
        `;
        document.getElementById('kemasanTableFoot').style.display = 'none';
    }
    document.getElementById('kemasanTableBody').innerHTML = kemasanHtml;

    // 4. Tenaga Kerja (BTKL Kemas - Card 4)
    document.getElementById('tk_langsung_org').value = data.jumlah_personil || 0;
    document.getElementById('tk_langsung_nilai').value = data.total_biaya_tenaga_kerja || 0;
    document.getElementById('helpTkSync').innerText = `Ditarik dari: ${data.labor_source}. Kehadiran riil tanggal ${data.pack_tgl}.`;

    recalcAllPackagingHpp();
}

function onWipPriceChange(input) {
    const qty = parseFloat(input.dataset.qty) || 0;
    const price = parseFloat(input.value) || 0;
    const subtotal = Math.round(qty * price);
    const subtotalEl = document.getElementById(`wip_subtotal_${input.dataset.id}`);
    if (subtotalEl) {
        subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
    }
    recalcAllPackagingHpp();
}

function onKemasanPriceChange(input) {
    const qty = parseFloat(input.dataset.qty) || 0;
    const price = parseFloat(input.value) || 0;
    const subtotal = Math.round(qty * price);
    const subtotalEl = document.getElementById(`kemasan_subtotal_${input.dataset.id}`);
    if (subtotalEl) {
        subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
    }
    recalcAllPackagingHpp();
}

function calcLaborPackaging() {
    recalcAllPackagingHpp();
}

function syncLaborFromKaryawan() {
    if (!currentPackagingData || !currentPackagingData.pack_tgl) {
        alert('Silakan pilih dokumen pengemasan terlebih dahulu.');
        return;
    }

    const tgl = currentPackagingData.pack_tgl;
    const syncUrl = `${window.appConfig.urlSyncKaryawan}?tanggal=${tgl}&dept=Langsung%20Packing`;

    fetch(syncUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success' || res.jumlah_karyawan !== undefined) {
            const jml = res.jumlah_karyawan || 0;
            const upah = res.total_upah || 0;
            document.getElementById('tk_langsung_org').value = jml;
            document.getElementById('tk_langsung_nilai').value = upah;
            document.getElementById('helpTkSync').innerText = `✓ Berhasil disinkronkan dengan data absensi tanggal ${tgl} (${jml} Karyawan Hadir).`;
            recalcAllPackagingHpp();
        } else {
            alert('Gagal mengambil data absensi: ' + (res.message || 'Respon tidak valid'));
        }
    })
    .catch(err => {
        alert('Gagal sinkronisasi presensi: ' + err.message);
    });
}

function recalcAllPackagingHpp() {
    // 1. Total WIP
    let totalWip = 0;
    document.querySelectorAll('[id^="wip_harga_"]').forEach(inp => {
        const qty = parseFloat(inp.dataset.qty) || 0;
        const price = parseFloat(inp.value) || 0;
        totalWip += Math.round(qty * price);
    });
    document.getElementById('total_biaya_wip').value = totalWip;
    document.getElementById('lblSubtotalWip').innerText = 'Rp ' + totalWip.toLocaleString('id-ID');

    // 2. Total Kemasan
    let totalKemasan = 0;
    document.querySelectorAll('[id^="kemasan_harga_"]').forEach(inp => {
        const qty = parseFloat(inp.dataset.qty) || 0;
        const price = parseFloat(inp.value) || 0;
        totalKemasan += Math.round(qty * price);
    });
    document.getElementById('total_biaya_kemasan').value = totalKemasan;
    document.getElementById('lblSubtotalKemasan').innerText = 'Rp ' + totalKemasan.toLocaleString('id-ID');

    // 3. Total BTKL Kemas
    const jmlOrg = parseInt(document.getElementById('tk_langsung_org').value) || 0;
    const upahTk = parseFloat(document.getElementById('tk_langsung_nilai').value) || 0;
    document.getElementById('disp_tk_total_nilai').innerText = upahTk.toLocaleString('id-ID');
    document.getElementById('lblTkFormula').innerText = `${jmlOrg} Orang Langsung Packing`;
    document.getElementById('total_biaya_tenaga_kerja').value = upahTk;
    document.getElementById('jumlah_personil').value = jmlOrg;

    // 4. Total Biaya Pengemasan (Murni: WIP + Kemasan + BTKL Kemas, Tanpa FOH)
    const totalBiaya = Math.round(totalWip + totalKemasan + upahTk);
    document.getElementById('total_biaya_produksi').value = totalBiaya;

    // 6. Output & HPP
    const hasilKarton = currentPackagingData ? (parseFloat(currentPackagingData.hasil_karton_qty) || 0) : 0;
    const hasilPcs = currentPackagingData ? (parseFloat(currentPackagingData.hasil_pcs_qty) || 0) : 0;

    const hppKarton = hasilKarton > 0 ? Math.round(totalBiaya / hasilKarton) : 0;
    const hppPcs = hasilPcs > 0 ? Math.round(totalBiaya / hasilPcs) : 0;

    // Update KPI Card Displays
    document.getElementById('statWipNilai').innerText = 'Rp ' + totalWip.toLocaleString('id-ID');
    document.getElementById('statKemasanNilai').innerText = 'Rp ' + totalKemasan.toLocaleString('id-ID');
    document.getElementById('statTkNilai').innerText = 'Rp ' + upahTk.toLocaleString('id-ID');
    document.getElementById('statTkMeta').innerText = `${jmlOrg} Orang Packing`;
    document.getElementById('statTotalBiaya').innerText = 'Rp ' + totalBiaya.toLocaleString('id-ID');
    document.getElementById('statTotalKarton').innerText = `${hasilKarton.toLocaleString('id-ID')} Karton`;
    document.getElementById('statTotalPcsMeta').innerText = `${hasilPcs.toLocaleString('id-ID')} Pcs Finish Good`;
    document.getElementById('statHppKarton').innerText = 'Rp ' + hppKarton.toLocaleString('id-ID');
    document.getElementById('statHppPcsMeta').innerText = `Setara Rp ${hppPcs.toLocaleString('id-ID')} / Pcs`;

    // Enable / disable submit button
    const btnSubmit = document.getElementById('btnSubmitHpp');
    if (btnSubmit) {
        btnSubmit.disabled = !currentPackagingData || hasilKarton <= 0;
    }
}

function resetHppDisplays() {
    document.getElementById('disp_tk_total_nilai').innerText = '0';
    document.getElementById('lblTkFormula').innerText = '0 Orang Langsung Packing';
    document.getElementById('statWipNilai').innerText = 'Rp 0';
    document.getElementById('statKemasanNilai').innerText = 'Rp 0';
    document.getElementById('statTkNilai').innerText = 'Rp 0';
    document.getElementById('statTkMeta').innerText = '0 Orang Packing';
    document.getElementById('statTotalBiaya').innerText = 'Rp 0';
    document.getElementById('statTotalKarton').innerText = '0 Karton';
    document.getElementById('statTotalPcsMeta').innerText = '0 Pcs Finish Good';
    document.getElementById('statHppKarton').innerText = 'Rp 0';
    document.getElementById('statHppPcsMeta').innerText = 'Setara Rp 0 / Pcs';
    const btnSubmit = document.getElementById('btnSubmitHpp');
    if (btnSubmit) btnSubmit.disabled = true;
}
