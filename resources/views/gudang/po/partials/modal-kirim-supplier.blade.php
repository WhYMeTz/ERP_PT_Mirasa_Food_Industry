<div id="modalKirimSupplier" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; display: flex; flex-direction: column; animation: modalPop 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center; color: #38bdf8;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #f8fafc;">Kirim PO ke Mitra Supplier</h3>
                    <p style="margin: 0.15rem 0 0; font-size: 0.75rem; color: #94a3b8;">Pengiriman dokumen Purchase Order resmi via Email</p>
                </div>
            </div>
            <button type="button" onclick="closeKirimSupplierModal()" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 0.25rem; border-radius: 6px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.color='#ffffff'" onmouseout="this.style.color='#94a3b8'">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- FORM BODY --}}
        <form id="formKirimSupplier" method="POST" action="" onsubmit="submitKirimSupplier(event)">
            @csrf
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.15rem; max-height: calc(85vh - 120px); overflow-y: auto;">
                
                {{-- INFO RINGKASAN PO --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.875rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">Nomor PO</span>
                        <div id="modalSendPoNo" style="font-weight: 700; color: #0284c7; font-size: 0.95rem;">-</div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">Mitra Supplier</span>
                        <div id="modalSendSupplierNm" style="font-weight: 600; color: #1e293b; font-size: 0.9rem;">-</div>
                    </div>
                </div>

                {{-- STATUS ATTACHMENT --}}
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.75rem 1rem; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div style="color: #16a34a; flex-shrink: 0; margin-top: 2px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div style="font-size: 0.775rem; color: #166534; line-height: 1.4;">
                        <strong>PDF Dokumen Resmi Tersertifikasi:</strong> Sistem akan otomatis membuat lampiran PDF bertanda-tangan digital, cap resmi <em>APPROVED</em>, dan QR Code verifikasi keabsahan dokumen.
                    </div>
                </div>

                {{-- ALAMAT EMAIL SUPPLIER --}}
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Email Supplier Tujuan <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="email" 
                           id="modalSendSupplierEmail" 
                           name="supplier_email" 
                           required 
                           class="form-control" 
                           placeholder="contoh: sales@supplier.co.id"
                           style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem;">
                    <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">
                        Alamat email utama mitra supplier yang menerima surat pesanan.
                    </small>
                </div>

                {{-- CC EMAIL --}}
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Tembusan (CC) <span style="color: #94a3b8; font-weight: 400;">(Opsional)</span>
                    </label>
                    <input type="text" 
                           id="modalSendCcEmail" 
                           name="cc_email" 
                           class="form-control" 
                           placeholder="contoh: purchasing@mirasa.co.id, finance@mirasa.co.id"
                           style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem;">
                    <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">
                        Pisahkan dengan koma jika mengirim tembusan ke lebih dari satu email.
                    </small>
                </div>

                {{-- CATATAN KHUSUS --}}
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Pesan Tambahan untuk Supplier <span style="color: #94a3b8; font-weight: 400;">(Opsional)</span>
                    </label>
                    <textarea id="modalSendCustomNotes" 
                              name="custom_notes" 
                              rows="3" 
                              class="form-control" 
                              placeholder="Contoh: Mohon barang dikirim sebelum pukul 14:00 WIB dan bawa surat jalan resmi sesuai standar HACCP."
                              style="width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; resize: vertical;"></textarea>
                </div>

                <div id="sendSupplierAlertError" style="display: none; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.8rem;"></div>
            </div>

            {{-- FOOTER TOMBOL AKSI --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.875rem 1.5rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem;">
                <button type="button" onclick="closeKirimSupplierModal()" class="btn btn-secondary" style="padding: 0.55rem 1.15rem; font-size: 0.85rem;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitSendSupplier" class="btn btn-primary" style="padding: 0.55rem 1.25rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem; background: #0284c7; border-color: #0284c7;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Kirim Email Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>
