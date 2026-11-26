{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL PENANDAAN PIUTANG TAK TERTAGIH (BAD DEBT AUDIT TRAIL)                --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalMarkMacet" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #e2e8f0; animation: modalPop 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="background: #fff1f2; border-bottom: 1.5px solid #fecdd3; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #881337; margin: 0;">
                        Tandai Piutang Tak Tertagih
                    </h3>
                    <span style="font-size: 0.75rem; color: #9f1239;">
                        Faktur: <strong id="macetModalFakturNoText">-</strong> &bull; <span id="macetModalCustomerNmText">-</span>
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeMacetModal()" style="border: none; background: transparent; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        {{-- FORM PENANDAAN MACET --}}
        <form id="formMarkMacet" method="POST" action="">
            @csrf

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                
                {{-- Banner Edukasi & Penjelasan --}}
                <div style="background: #fff8f8; border: 1px solid #fed7aa; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.8125rem; color: #7c2d12; line-height: 1.45;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.75rem; color: #9a3412;">Nominal Piutang Tak Tertagih:</span>
                        <strong style="color: #b91c1c; font-family: monospace; font-size: 0.95rem;" id="macetModalSisaPiutangText">Rp 0</strong>
                    </div>
                    <p style="margin: 0; font-size: 0.75rem; color: #9a3412;">
                        <strong>Catatan Sistem:</strong> Tindakan ini <em>tidak menghapus</em> data faktur. Faktur akan dialihkan dari daftar tagihan aktif dan dicatat sebagai rekam jejak risiko kredit customer saat pemesanan baru.
                    </p>
                </div>

                {{-- Pilihan Alasan Cepat --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Kategori Alasan <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="macetKategoriSelect" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;" onchange="handleMacetCategoryChange(this.value)">
                        <option value="Toko Tutup / Pelanggan Bangkrut">Toko Tutup / Usaha Bangkrut</option>
                        <option value="Pelanggan Tidak Dapat Dihubungi (Kabur)">Pelanggan Tidak Dapat Dihubungi (Kabur)</option>
                        <option value="Sengketa Kualitas Barang / Retur">Sengketa Kualitas Barang / Retur Tertolak</option>
                        <option value="Wanprestasi / Menolak Bayar">Wanprestasi / Menolak Membayar</option>
                        <option value="LAINNYA">Lainnya (Ketik Manual)</option>
                    </select>
                </div>

                {{-- Detail Penjelasan Alasan (Audit Trail) --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Rincian Penjelasan / Alasan Audit <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="macetAlasanText" name="alasan_macet" rows="3" class="form-control" 
                              style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 0.75rem; font-size: 0.85rem; resize: vertical;" 
                              placeholder="Jelaskan alasan penandaan piutang tak tertagih ini secara rinci..." required>Toko Tutup / Pelanggan Bangkrut</textarea>
                </div>

            </div>

            {{-- FOOTER MODAL --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" onclick="closeMacetModal()" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="font-size: 0.825rem; font-weight: 700; background: #be123c; border-color: #9f1239; color: #ffffff; padding: 0.45rem 1.25rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Rekam Jejak Tak Tertagih</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openMacetModal(fakturId, fakturNo, customerNm, sisaPiutang) {
        const modal = document.getElementById('modalMarkMacet');
        const form = document.getElementById('formMarkMacet');
        if (!modal || !form) return;

        // Set action form
        form.action = `/penjualan/faktur/${fakturId}/mark-macet`;

        // Tampilkan data
        document.getElementById('macetModalFakturNoText').innerText = fakturNo;
        document.getElementById('macetModalCustomerNmText').innerText = customerNm;
        document.getElementById('macetModalSisaPiutangText').innerText = 'Rp ' + Number(sisaPiutang).toLocaleString('id-ID');

        // Reset default input
        const selKat = document.getElementById('macetKategoriSelect');
        if (selKat) selKat.value = 'Toko Tutup / Pelanggan Bangkrut';
        const txt = document.getElementById('macetAlasanText');
        if (txt) txt.value = 'Toko Tutup / Pelanggan Bangkrut';

        modal.style.display = 'flex';
    }

    function closeMacetModal() {
        const modal = document.getElementById('modalMarkMacet');
        if (modal) modal.style.display = 'none';
    }

    function handleMacetCategoryChange(val) {
        const txt = document.getElementById('macetAlasanText');
        if (!txt) return;
        if (val === 'LAINNYA') {
            txt.value = '';
            txt.focus();
        } else {
            txt.value = val;
        }
    }

    // Close on click outside
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalMarkMacet');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeMacetModal();
            });
        }
    });
</script>
