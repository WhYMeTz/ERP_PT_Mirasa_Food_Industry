{{-- MODAL DETAIL JURNAL UMUM (DOUBLE ENTRY AUDIT) --}}
<div id="modalDetailJurnal" 
     onclick="if(event.target.id === 'modalDetailJurnal') closeModalDetailJurnal();"
     style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 1060; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div onclick="event.stopPropagation()" 
         style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 780px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; border: 1px solid #cbd5e1;">
        
        {{-- MODAL HEADER --}}
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.35rem;">📑</span>
                <div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a;">
                        Rincian Jurnal Umum (<span id="modalJurnalNo" style="font-family: monospace; color: #0284c7;">-</span>)
                    </h3>
                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                        Buku Jurnal Transaksi Keuangan &amp; Akuntansi Berpasangan
                    </div>
                </div>
            </div>

            <button type="button" 
                    onclick="closeModalDetailJurnal()" 
                    style="background: transparent; border: none; font-size: 1.35rem; color: #94a3b8; cursor: pointer; padding: 4px; line-height: 1; border-radius: 6px;"
                    onmouseover="this.style.color='#0f172a'" 
                    onmouseout="this.style.color='#94a3b8'">
                &times;
            </button>
        </div>

        {{-- MODAL BODY --}}
        <div style="padding: 1.5rem; overflow-y: auto; flex: 1;">
            {{-- LOADING STATE --}}
            <div id="modalJurnalLoading" style="text-align: center; padding: 3rem 1rem; color: #0284c7; font-weight: 700; font-size: 0.875rem;">
                ⏳ Memuat rincian jurnal akuntansi...
            </div>

            {{-- CONTENT CONTAINER --}}
            <div id="modalJurnalContent" style="display: none;">
                {{-- INFO HEADER TRANSAKSI --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <span style="font-size: 0.725rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Tanggal Jurnal</span>
                        <div id="modalJurnalTgl" style="font-weight: 800; color: #0f172a; font-size: 0.85rem; margin-top: 2px;">-</div>
                    </div>
                    <div>
                        <span style="font-size: 0.725rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Tipe Transaksi</span>
                        <div id="modalJurnalTipe" style="font-weight: 800; color: #0284c7; font-size: 0.85rem; margin-top: 2px;">-</div>
                    </div>
                    <div>
                        <span style="font-size: 0.725rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Ref Dokumen</span>
                        <div id="modalJurnalRef" style="font-weight: 800; font-family: monospace; color: #0f172a; font-size: 0.85rem; margin-top: 2px;">-</div>
                    </div>
                    <div>
                        <span style="font-size: 0.725rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Dicatat Oleh</span>
                        <div id="modalJurnalCreatedBy" style="font-weight: 700; color: #334155; font-size: 0.85rem; margin-top: 2px;">-</div>
                    </div>
                    <div style="grid-column: 1 / -1; border-top: 1px dashed #e2e8f0; padding-top: 0.5rem; margin-top: 0.25rem;">
                        <span style="font-size: 0.725rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Keterangan / Memo</span>
                        <div id="modalJurnalKeterangan" style="font-weight: 600; color: #1e293b; font-size: 0.825rem; margin-top: 2px;">-</div>
                    </div>
                </div>

                {{-- TABEL BARIS DEBET / KREDIT --}}
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8rem;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 1.5px solid #cbd5e1; font-size: 0.725rem; color: #475569; text-transform: uppercase;">
                                <th style="padding: 0.65rem 0.75rem;">Kode &amp; Nama Akun COA</th>
                                <th style="padding: 0.65rem 0.75rem;">Keterangan Baris</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">Debet (Rp)</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="modalJurnalLinesTbody">
                            {{-- Di-render via JS --}}
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                                <td colspan="2" style="padding: 0.75rem; text-align: right; color: #334155;">
                                    Total Keseimbangan:
                                </td>
                                <td id="modalJurnalTotalDebet" style="padding: 0.75rem; text-align: right; color: #0284c7; font-family: monospace; font-size: 0.85rem;">
                                    Rp 0
                                </td>
                                <td id="modalJurnalTotalKredit" style="padding: 0.75rem; text-align: right; color: #0284c7; font-family: monospace; font-size: 0.85rem;">
                                    Rp 0
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <span style="font-size: 0.75rem; color: #166534; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        ✓ Jurnal Seimbang (Debet = Kredit)
                    </span>
                    <span style="font-size: 0.7rem; color: #64748b;">
                        Tercatat permanen di Buku Besar Akuntansi
                    </span>
                </div>
            </div>
        </div>

        {{-- MODAL FOOTER --}}
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="closeModalDetailJurnal()" 
                    style="font-size: 0.825rem; font-weight: 700; padding: 0.45rem 1rem;">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/keuangan/jurnal/modal-detail-jurnal.js') }}?v={{ file_exists(public_path('js/keuangan/jurnal/modal-detail-jurnal.js')) ? filemtime(public_path('js/keuangan/jurnal/modal-detail-jurnal.js')) : time() }}"></script>
@endpush
