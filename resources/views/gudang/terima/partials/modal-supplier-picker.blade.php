{{-- ========================================================================= --}}
{{-- MODAL PILIH MASTER SUPPLIER (STANDAR TABEL EXCEL ERP)                     --}}
{{-- ========================================================================= --}}
<div id="modalPilihSupplier" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 960px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
            <div>
                <h2 class="modal-title" style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Daftar Supplier</h2>
                <p style="color: #64748b; font-size: 0.8rem; margin: 0.2rem 0 0 0;">Pilih supplier rekanan untuk penerimaan fisik barang masuk.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalPilihSupplier')">&times;</button>
        </div>

        {{-- Toolbar Pencarian & Filter Tab Kategori --}}
        <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <div style="position: relative; margin-bottom: 0.6rem;">
                <input type="text" id="modal_supplier_search" class="form-control" placeholder="Cari berdasarkan nama supplier, kode, no telepon, atau alamat..." style="padding-left: 2.1rem !important; height: 36px; font-size: 0.85rem;" oninput="modalCurrentPage = 1; renderSupplierModalTable();">
                <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            {{-- Tabs Filter Kategori (Standar ERP Bersih) --}}
            <div style="display: flex; gap: 0.35rem; overflow-x: auto; padding-bottom: 0.15rem;">
                <button type="button" class="sup-modal-tab active" data-tab="ALL" onclick="filterSupplierModalTab('ALL', this)">
                    Semua Supplier ({{ $supplierList->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="RAW" onclick="filterSupplierModalTab('RAW', this)">
                    Bahan Baku ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'RAW')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="BUMBU" onclick="filterSupplierModalTab('BUMBU', this)">
                    Bumbu &amp; Penolong ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BUMBU')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="KEMASAN" onclick="filterSupplierModalTab('KEMASAN', this)">
                    Kemasan &amp; Karton ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'KEMASAN')->count() }})
                </button>
                <button type="button" class="sup-modal-tab" data-tab="BP" onclick="filterSupplierModalTab('BP', this)">
                    Penolong Industri ({{ $supplierList->filter(fn($s) => $s->jenisSupplier?->jenis_supplier_cd === 'BP')->count() }})
                </button>
            </div>
        </div>

        {{-- Tabel Standar Excel Grid --}}
        <div style="flex: 1; overflow-y: auto; padding: 0.75rem 1.25rem; min-height: 250px; max-height: 52vh;">
            <table class="excel-grid-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center; position: sticky; top: 0; z-index: 5;">No</th>
                        <th style="width: 110px; text-align: left; position: sticky; top: 0; z-index: 5;">Kode Supplier</th>
                        <th style="text-align: left; position: sticky; top: 0; z-index: 5;">Nama Supplier</th>
                        <th style="width: 140px; text-align: left; position: sticky; top: 0; z-index: 5;">Jenis Kategori</th>
                        <th style="width: 130px; text-align: left; position: sticky; top: 0; z-index: 5;">No. Kontak / Telp</th>
                        <th style="text-align: left; position: sticky; top: 0; z-index: 5;">Alamat / Lokasi</th>
                        <th style="width: 75px; text-align: center; position: sticky; top: 0; z-index: 5;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="supplier_modal_tbody">
                    {{-- Diisi secara dinamis via JS --}}
                </tbody>
            </table>
        </div>

        {{-- Footer Modal dengan Navigasi Halaman (Pagination) --}}
        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.65rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <span id="modal_supplier_count_text" style="font-size: 0.8rem; color: #64748b;">Menampilkan 0 supplier</span>
            
            <div id="modal_supplier_pagination" style="display: flex; align-items: center; gap: 0.35rem;">
                <button type="button" id="modal_prev_btn" class="btn btn-secondary btn-sm" onclick="changeSupplierModalPage(-1)" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                    &larr; Sebelumnya
                </button>
                <span id="modal_page_info" style="font-size: 0.75rem; font-weight: 600; color: #334155; padding: 0 0.35rem;">
                    Hal 1 dari 1
                </span>
                <button type="button" id="modal_next_btn" class="btn btn-secondary btn-sm" onclick="changeSupplierModalPage(1)" style="padding: 0.25rem 0.6rem; font-size: 0.75rem;">
                    Selanjutnya &rarr;
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalPilihSupplier')">Tutup</button>
        </div>
    </div>
</div>
