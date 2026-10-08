{{-- MODAL INPUT / LANJUTKAN HASIL UJI GORENG (FRYER) --}}
<div id="modalUjiFryer" class="qc-modal-backdrop" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); align-items: flex-end; justify-content: center; padding: 0;">
    <div class="qc-modal-sheet" style="background: #ffffff; width: 100%; max-width: 550px; border-radius: 20px 20px 0 0; max-height: 90vh; overflow-y: auto; box-shadow: 0 -10px 25px rgba(0,0,0,0.2); animation: slideUp 0.25s ease-out; display: flex; flex-direction: column;">
        
        {{-- MODAL HEADER --}}
        <div style="padding: 1.15rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: #ffffff; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.5rem;">🍟</span>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a;">Pengujian II &bull; Uji Goreng (Fryer)</h3>
                    <span id="modalQcNoLabel" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Lengkapi hasil uji lab fryer</span>
                </div>
            </div>
            <button type="button" onclick="closeModalUjiFryer()" style="background: #f1f5f9; border: none; border-radius: 50%; width: 34px; height: 34px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;">
                &times;
            </button>
        </div>

        {{-- FORM BODY --}}
        <form id="formUjiFryer" method="POST" action="" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.15rem;">
            @csrf

            <input type="hidden" name="qc_id" id="modalQcIdInput" value="">
            <input type="hidden" name="qcdtl_id" id="modalQcDtlIdInput" value="">

            {{-- ALERT INFO --}}
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 0.75rem 0.9rem; font-size: 0.8rem; color: #1e40af; line-height: 1.4;">
                ℹ️ <strong>Pengujian Lanjutan:</strong> Masukkan hasil penggorengan sampel lab singkong untuk rasa, tekstur, penampakan, dan persentase cacat penggorengan.
            </div>

            {{-- 1. UJI SENSORIK (RASA, TEKSTUR, PENAMPAKAN) --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div style="font-size: 0.825rem; font-weight: 800; color: #0284c7; text-transform: uppercase;">
                    1. Uji Sensorik &amp; Fisik Goreng
                </div>

                {{-- RASA --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Rasa Keripik Singkong:
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.55rem 0.75rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #15803d;">
                            <input type="radio" name="fryer_rasa" value="TIDAK_PAHIT" checked style="accent-color: #15803d;">
                            <span>✔ Tidak Pahit (Gurih)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.55rem 0.75rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #dc2626;">
                            <input type="radio" name="fryer_rasa" value="PAHIT" style="accent-color: #dc2626;">
                            <span>✖ Pahit</span>
                        </label>
                    </div>
                </div>

                {{-- TEKSTUR --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Tekstur Keripik:
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem;">
                        <label style="display: flex; align-items: center; gap: 0.3rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 700; color: #059669;">
                            <input type="radio" name="fryer_tekstur" value="RENYAH" checked style="accent-color: #059669;">
                            <span>Renyah</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.3rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 700; color: #d97706;">
                            <input type="radio" name="fryer_tekstur" value="ALOT" style="accent-color: #d97706;">
                            <span>Alot</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.3rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.5rem; cursor: pointer; font-size: 0.8rem; font-weight: 700; color: #dc2626;">
                            <input type="radio" name="fryer_tekstur" value="LEMBEK" style="accent-color: #dc2626;">
                            <span>Lembek</span>
                        </label>
                    </div>
                </div>

                {{-- PENAMPAKAN --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Penampakan / Kerapatan Minyak:
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.55rem 0.75rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #0284c7;">
                            <input type="radio" name="fryer_penampakan" value="TIDAK_OILSOAKED" checked style="accent-color: #0284c7;">
                            <span>✔ Normal (Kering)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.55rem 0.75rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #d97706;">
                            <input type="radio" name="fryer_penampakan" value="OILSOAKED" style="accent-color: #d97706;">
                            <span>✖ Oilsoaked (Berminyak)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. DEFECT FRYING (%) --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="font-size: 0.825rem; font-weight: 800; color: #475569; text-transform: uppercase;">
                    2. Persentase Cacat Goreng (Defect %)
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                            Breakage (%)
                        </label>
                        <input type="number" step="0.1" min="0" max="100" name="defect_breakage_persen" id="modalBreakage" value="0.0" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; text-align: center;">
                        <span style="font-size: 0.68rem; color: #64748b;">Pecahan</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                            Cluster (%)
                        </label>
                        <input type="number" step="0.1" min="0" max="100" name="defect_cluster_persen" id="modalCluster" value="0.0" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; text-align: center;">
                        <span style="font-size: 0.68rem; color: #64748b;">Nempel</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                            Gambos (%)
                        </label>
                        <input type="number" step="0.1" min="0" max="100" name="defect_gambos_persen" id="modalGambos" value="0.0" class="form-control" style="width: 100%; border-radius: 8px; font-weight: 700; text-align: center;">
                        <span style="font-size: 0.68rem; color: #64748b;">Gabus</span>
                    </div>
                </div>
            </div>

            {{-- 3. CATATAN LAB --}}
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Lab / Uji Goreng Tambahan:
                </label>
                <textarea name="catatan_umum" id="modalCatatanFryer" rows="2" class="form-control" placeholder="Contoh: Singkong digoreng suhu 160°C, hasil warna kuning keemasan, renyah gurih standar Mirasa." style="width: 100%; border-radius: 8px; font-size: 0.85rem;"></textarea>
            </div>

            {{-- TOMBOL SUBMIT --}}
            <div style="display: flex; gap: 0.6rem; margin-top: 0.5rem; position: sticky; bottom: 0; background: #ffffff; padding-top: 0.5rem;">
                <button type="button" onclick="closeModalUjiFryer()" style="flex: 1; padding: 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 12px; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.9rem; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitFryer" style="flex: 2; padding: 0.85rem; border: none; border-radius: 12px; background: #059669; color: #ffffff; font-weight: 800; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(5,150,105,0.3);">
                    ✅ Simpan Hasil Fryer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalUjiFryer(qcId, qcNo, dtlId, data = {}) {
    const modal = document.getElementById('modalUjiFryer');
    const form = document.getElementById('formUjiFryer');
    
    document.getElementById('modalQcNoLabel').textContent = `Tiket #${qcNo}`;
    document.getElementById('modalQcIdInput').value = qcId;
    document.getElementById('modalQcDtlIdInput').value = dtlId || '';
    
    form.action = `/qc/inbound/${qcId}/uji-goreng`;

    // Pasang input items hidden jika ada dtlId
    let itemsHidden = document.getElementById('modalItemsHolder');
    if (!itemsHidden) {
        itemsHidden = document.createElement('div');
        itemsHidden.id = 'modalItemsHolder';
        form.appendChild(itemsHidden);
    }
    itemsHidden.innerHTML = '';

    if (dtlId) {
        // Input hidden untuk mapping items[dtlId]
        const inputRasa = document.createElement('input');
        inputRasa.type = 'hidden';
        inputRasa.name = `items[${dtlId}][fryer_rasa]`;
        inputRasa.id = 'hiddenFryerRasa';
        itemsHidden.appendChild(inputRasa);

        const inputTekstur = document.createElement('input');
        inputTekstur.type = 'hidden';
        inputTekstur.name = `items[${dtlId}][fryer_tekstur]`;
        inputTekstur.id = 'hiddenFryerTekstur';
        itemsHidden.appendChild(inputTekstur);

        const inputPenampakan = document.createElement('input');
        inputPenampakan.type = 'hidden';
        inputPenampakan.name = `items[${dtlId}][fryer_penampakan]`;
        inputPenampakan.id = 'hiddenFryerPenampakan';
        itemsHidden.appendChild(inputPenampakan);

        const inputBreakage = document.createElement('input');
        inputBreakage.type = 'hidden';
        inputBreakage.name = `items[${dtlId}][defect_breakage_persen]`;
        inputBreakage.id = 'hiddenBreakage';
        itemsHidden.appendChild(inputBreakage);

        const inputCluster = document.createElement('input');
        inputCluster.type = 'hidden';
        inputCluster.name = `items[${dtlId}][defect_cluster_persen]`;
        inputCluster.id = 'hiddenCluster';
        itemsHidden.appendChild(inputCluster);

        const inputGambos = document.createElement('input');
        inputGambos.type = 'hidden';
        inputGambos.name = `items[${dtlId}][defect_gambos_persen]`;
        inputGambos.id = 'hiddenGambos';
        itemsHidden.appendChild(inputGambos);
    }

    // Set nilai awal jika ada
    if (data.rasa) {
        const rEl = form.querySelector(`input[name="fryer_rasa"][value="${data.rasa}"]`);
        if (rEl) rEl.checked = true;
    }
    if (data.tekstur) {
        const tEl = form.querySelector(`input[name="fryer_tekstur"][value="${data.tekstur}"]`);
        if (tEl) tEl.checked = true;
    }
    if (data.penampakan) {
        const pEl = form.querySelector(`input[name="fryer_penampakan"][value="${data.penampakan}"]`);
        if (pEl) pEl.checked = true;
    }
    document.getElementById('modalBreakage').value = data.breakage || '0.0';
    document.getElementById('modalCluster').value = data.cluster || '0.0';
    document.getElementById('modalGambos').value = data.gambos || '0.0';
    if (data.catatan) {
        document.getElementById('modalCatatanFryer').value = data.catatan;
    }

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModalUjiFryer() {
    const modal = document.getElementById('modalUjiFryer');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Sinkronkan input radio ke hidden field saat submit & cegah double click
let isFryerSubmitting = false;
document.getElementById('formUjiFryer')?.addEventListener('submit', function (e) {
    if (isFryerSubmitting) {
        e.preventDefault();
        return false;
    }

    const dtlId = document.getElementById('modalQcDtlIdInput').value;
    if (dtlId) {
        const selRasa = document.querySelector('input[name="fryer_rasa"]:checked')?.value || 'TIDAK_PAHIT';
        const selTekstur = document.querySelector('input[name="fryer_tekstur"]:checked')?.value || 'RENYAH';
        const selPenampakan = document.querySelector('input[name="fryer_penampakan"]:checked')?.value || 'TIDAK_OILSOAKED';
        const breakage = document.getElementById('modalBreakage')?.value || 0;
        const cluster = document.getElementById('modalCluster')?.value || 0;
        const gambos = document.getElementById('modalGambos')?.value || 0;

        const hRasa = document.getElementById('hiddenFryerRasa');
        if (hRasa) hRasa.value = selRasa;
        const hTekstur = document.getElementById('hiddenFryerTekstur');
        if (hTekstur) hTekstur.value = selTekstur;
        const hPenampakan = document.getElementById('hiddenFryerPenampakan');
        if (hPenampakan) hPenampakan.value = selPenampakan;
        const hBreakage = document.getElementById('hiddenBreakage');
        if (hBreakage) hBreakage.value = breakage;
        const hCluster = document.getElementById('hiddenCluster');
        if (hCluster) hCluster.value = cluster;
        const hGambos = document.getElementById('hiddenGambos');
        if (hGambos) hGambos.value = gambos;
    }

    isFryerSubmitting = true;
    const btn = document.getElementById('btnSubmitFryer');
    if (btn) {
        btn.disabled = true;
        btn.innerText = '⏳ Menyimpan...';
        btn.style.opacity = '0.6';
        btn.style.pointerEvents = 'none';
    }
});
</script>
