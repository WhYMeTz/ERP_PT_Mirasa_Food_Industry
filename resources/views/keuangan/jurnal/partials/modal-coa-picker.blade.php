{{-- MODAL SEARCH & PICKER AKUN COA CEPAT (MACOS NATIVE WINDOW) --}}
<div id="modalCoaPicker" 
     class="modal-backdrop-custom" 
     style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 1070; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(4px);"
     onclick="if(event.target.id === 'modalCoaPicker') closeCoaPickerModal()">
    <div onclick="event.stopPropagation()" class="macos-window-frame">
        
        {{-- MACOS WINDOW TITLE BAR --}}
        <div class="macos-title-bar">
            <div class="macos-traffic-lights">
                <span class="traffic-dot dot-close" onclick="closeCoaPickerModal()" title="Tutup (Esc)"></span>
                <span class="traffic-dot dot-minimize" onclick="closeCoaPickerModal()" title="Tutup"></span>
                <span class="traffic-dot dot-expand" title="Pencarian Bagan Akun"></span>
            </div>

            <div class="macos-title-center">
                <span class="macos-title-icon">🔍</span>
                <span class="macos-title-heading">Pilih Bagan Akun (Chart of Accounts)</span>
            </div>

            <div class="macos-title-esc">
                <span>esc</span>
            </div>
        </div>

        {{-- SEARCH INPUT & FILTER PILLS --}}
        <div style="padding: 1rem 1.25rem 0.5rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
            <div style="position: relative; margin-bottom: 0.65rem;">
                <input type="text" 
                       id="modalCoaSearchInput" 
                       class="jurnal-input" 
                       placeholder="🔍 Ketik kode akun (6101) atau nama (Gaji, Listrik, Kas, dll)..." 
                       style="height: 38px; font-size: 0.85rem; padding-left: 2.25rem; border-radius: 6px;"
                       oninput="filterCoaPickerModal()">
                <span style="position: absolute; left: 10px; top: 10px; color: #94a3b8; font-size: 0.9rem; pointer-events: none;">🔍</span>
            </div>

            {{-- KATEGORI PILLS --}}
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                <button type="button" class="coa-filter-pill active" onclick="filterCoaCategory('', this)">Semua ({{ $coaList->count() }})</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('BEBAN', this)">Beban &amp; Biaya</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('AKTIVA', this)">Kas, Bank &amp; Aset</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('PASIVA', this)">Hutang &amp; Kewajiban</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('MODAL', this)">Modal &amp; Ekuitas</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('PENDAPATAN', this)">Pendapatan</button>
            </div>
        </div>

        {{-- LIST CONTAINER (SCROLLABLE) --}}
        <div id="modalCoaContainer" style="padding: 0.75rem 1.25rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 0.45rem; max-height: 380px; background: #fafbfc;">
            @foreach ($coaList as $c)
                <div class="coa-picker-item" 
                     data-id="{{ $c->coa_id }}" 
                     data-kode="{{ $c->kode_akun }}" 
                     data-nama="{{ $c->nama_akun }}" 
                     data-kategori="{{ $c->kategori_akun }}" 
                     data-posisi="{{ $c->posisi_keuangan }}" 
                     data-search="{{ strtolower($c->kode_akun . ' ' . $c->nama_akun . ' ' . $c->kategori_akun) }}"
                     onclick="selectCoaFromModal(this)">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span class="coa-code-pill" style="font-size: 0.8rem; padding: 2px 7px;">{{ $c->kode_akun }}</span>
                            <strong style="color: #0f172a; font-size: 0.825rem;">{{ $c->nama_akun }}</strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span class="badge-coa-cat badge-cat-{{ strtolower($c->kategori_akun) }}">{{ $c->kategori_akun }}</span>
                            <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Saldo: {{ $c->saldo_normal }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
            <div id="coaEmptySearch" style="display: none; text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: 0.85rem;">
                Tidak ada akun yang sesuai dengan pencarian Anda.
            </div>
        </div>

        {{-- MACOS FOOTER --}}
        <div class="macos-footer-bar">
            <span style="font-size: 0.75rem; color: #64748b;">Klik pada baris akun untuk langsung memilih</span>
            <button type="button" class="btn btn-secondary" onclick="closeCoaPickerModal()" style="font-size: 0.775rem; padding: 0.35rem 0.95rem; font-weight: 700;">Tutup</button>
        </div>
    </div>
</div>
