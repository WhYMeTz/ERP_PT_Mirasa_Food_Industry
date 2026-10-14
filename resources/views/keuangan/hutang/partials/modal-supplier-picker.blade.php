{{-- Modal Pop-up Pemilih Mitra Supplier (ERP PT Mirasa) --}}
<div id="modalSupplierPicker" class="modal-backdrop-custom" style="display: none;">
    <div class="modal-dialog-custom modal-dialog-lg">
        {{-- HEADER MODAL --}}
        <div class="modal-header-custom">
            <div>
                <h3 class="modal-title-custom" style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.25rem;">🏢</span>
                    <span>Pilih Mitra Supplier / Pemasok</span>
                </h3>
                <p class="modal-subtitle-custom">
                    Pilih supplier untuk memuat seluruh Surat Jalan &amp; Penerimaan Barang (GRN) yang siap ditagihkan.
                </p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeSupplierModal()">&times;</button>
        </div>

        {{-- SEARCH & TAB FILTER --}}
        <div class="modal-body-custom" style="padding: 1rem 1.25rem 0.5rem 1.25rem;">
            <div style="display: flex; gap: 0.75rem; margin-bottom: 0.85rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 240px; position: relative;">
                    <input type="text" 
                           id="modalSupplierSearchInput" 
                           class="form-control" 
                           placeholder="🔍 Cari nama supplier atau kode..." 
                           style="height: 38px; font-size: 0.85rem; padding-left: 2.25rem; border-radius: 8px;"
                           oninput="filterSupplierModal()">
                    <span style="position: absolute; left: 10px; top: 10px; color: #94a3b8; font-size: 0.9rem; pointer-events: none;">🔍</span>
                </div>

                <div class="supplier-tabs-group" style="display: flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 8px;">
                    <button type="button" 
                            class="supplier-tab-btn active" 
                            id="tabSiapTagih"
                            onclick="switchSupplierTab('siap_tagih')">
                        ⚡ Siap Ditagih ({{ $suppliersWithGrn->count() }})
                    </button>
                    <button type="button" 
                            class="supplier-tab-btn" 
                            id="tabSemua"
                            onclick="switchSupplierTab('semua')">
                        Semua Supplier ({{ $suppliers->count() }})
                    </button>
                </div>
            </div>

            {{-- LIST KARTU SUPPLIER (SCROLLABLE) --}}
            <div id="supplierModalContainer" style="max-height: 380px; overflow-y: auto; padding-right: 4px; display: flex; flex-direction: column; gap: 0.65rem;">
                @foreach ($suppliers as $supp)
                    @php
                        $grnCnt = $unbilledSupplierSummary[$supp->supplier_id] ?? 0;
                        $hasGrn = $grnCnt > 0;
                    @endphp
                    <div class="supplier-modal-card {{ $hasGrn ? 'has-grn' : 'no-grn' }}"
                         data-supplier-id="{{ $supp->supplier_id }}"
                         data-name="{{ strtolower($supp->supplier_nm) }}"
                         data-code="{{ strtolower($supp->supplier_cd ?? '') }}"
                         data-has-grn="{{ $hasGrn ? '1' : '0' }}"
                         onclick="selectSupplierFromModal({{ $supp->supplier_id }}, '{{ addslashes($supp->supplier_nm) }}', '{{ $supp->supplier_cd }}', {{ $grnCnt }}, '{{ addslashes($supp->kontak_no ?? '-') }}', '{{ addslashes($supp->alamat_txt ?? '-') }}')">
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span class="supplier-modal-code">{{ $supp->supplier_cd }}</span>
                                    <strong class="supplier-modal-name">{{ $supp->supplier_nm }}</strong>
                                    @if ($hasGrn)
                                        <span class="badge-grn-count">
                                            ⚡ {{ $grnCnt }} GRN Siap Tagih
                                        </span>
                                    @endif
                                </div>

                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <span>📞 {{ $supp->kontak_no ?: 'Belum ada kontak' }}</span>
                                    <span>📍 {{ \Illuminate\Support\Str::limit($supp->alamat_txt ?: 'Alamat belum diatur', 45) }}</span>
                                </div>
                            </div>

                            <button type="button" class="btn-select-supplier">
                                <span>Pilih</span> &rarr;
                            </button>
                        </div>
                    </div>
                @endforeach

                <div id="supplierModalEmpty" style="display: none; padding: 2.5rem 1rem; text-align: center; color: #94a3b8;">
                    Tidak ditemukan supplier yang sesuai dengan pencarian.
                </div>
            </div>
        </div>

        {{-- FOOTER MODAL --}}
        <div class="modal-footer-custom" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.75rem; color: #64748b;">
                💡 <em>Tip: Klik pada supplier untuk otomatis memuat seluruh tagihannya.</em>
            </div>
            <button type="button" class="btn btn-secondary" onclick="closeSupplierModal()" style="font-size: 0.825rem; font-weight: 700;">
                Tutup
            </button>
        </div>
    </div>
</div>
