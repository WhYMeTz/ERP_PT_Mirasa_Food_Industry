{{-- 
    Partial: Dossier & Traceability Batch Modal
    File: resources/views/gudang/stok/partials/modal-batch-detail.blade.php
--}}
<div id="batchDetailModal" class="batch-modal-backdrop" onclick="handleBackdropClick(event)">
    <div class="batch-modal-dialog" onclick="event.stopPropagation()">
        {{-- 1. HEADER MODAL --}}
        <div class="batch-modal-header">
            <div class="batch-modal-title-box">
                <span class="batch-modal-title">
                    <span>📦 Dossier &amp; Traceability Batch</span>
                </span>
                <span class="batch-no-pill">
                    <span id="bmBatchNoText">-</span>
                    <button type="button" class="batch-copy-btn" onclick="copyBatchNumber()" title="Salin nomor batch">
                        📋
                    </button>
                </span>
                <span id="bmGradeBadge" class="badge" style="display: none;"></span>
                <span id="bmStatusBadge" class="badge" style="display: none;"></span>
            </div>
            <button type="button" class="batch-modal-close-btn" onclick="closeBatchDetailModal()" title="Tutup">
                &times;
            </button>
        </div>

        {{-- 2. QUICK SNAPSHOT METRICS BAR --}}
        <div class="batch-snapshot-bar">
            {{-- Kolom 1: Barang & SKU --}}
            <div class="batch-snapshot-col">
                <div class="batch-snapshot-label">Komoditas &amp; Jenis</div>
                <div class="batch-snapshot-val-main" id="bmBarangNm">-</div>
                <div class="batch-snapshot-sub">
                    <span id="bmBarangCd" style="font-family: monospace; font-weight: 700; color: #0284c7;">-</span>
                    &bull; <span id="bmJenisNm">-</span>
                </div>
            </div>

            {{-- Kolom 2: Total Sisa Stok --}}
            <div class="batch-snapshot-col">
                <div class="batch-snapshot-label">Sisa Stok Fisik</div>
                <div class="batch-snapshot-val-main" style="color: #059669;">
                    <span id="bmTotalSisa">0</span>
                    <span id="bmSatuanNm" style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Unit</span>
                </div>
                <div class="batch-snapshot-sub" id="bmStatusDesc">
                    Stok Tersedia
                </div>
            </div>

            {{-- Kolom 3: Pemakaian & Gauge --}}
            <div class="batch-snapshot-col">
                <div class="batch-snapshot-label">Rasio Pemakaian</div>
                <div class="batch-progress-wrapper">
                    <div class="batch-progress-track">
                        <div id="bmProgressBar" class="batch-progress-fill" style="width: 0%;"></div>
                    </div>
                    <div class="batch-progress-meta">
                        <span>Awal: <strong id="bmTotalAwal">0</strong></span>
                        <span>Keluar: <strong id="bmTotalKeluar" style="color: #dc2626;">0</strong> (<span id="bmPctTerpakai">0%</span>)</span>
                    </div>
                </div>
            </div>

            {{-- Kolom 4: Asal-Usul (Origin) --}}
            <div class="batch-snapshot-col">
                <div class="batch-snapshot-label" id="bmOriginLabel">Asal Kedatangan</div>
                <div class="batch-snapshot-val-main" id="bmOriginTitle" style="font-size: 0.875rem;">-</div>
                <div class="batch-snapshot-sub" id="bmOriginSub">-</div>
            </div>
        </div>

        {{-- 3. NAV TABS --}}
        <div class="batch-modal-tabs">
            <div class="batch-tab-item active" onclick="switchBatchTab('tabSaldo')">
                <span>📊 Saldo Gudang &amp; Grade</span>
                <span class="batch-tab-badge" id="bmTabCountSaldo">0</span>
            </div>
            <div class="batch-tab-item" onclick="switchBatchTab('tabInbound')">
                <span id="bmTabInboundTitle">🚚 Asal Penerimaan &amp; QC</span>
                <span class="batch-tab-badge" id="bmTabCountInbound">0</span>
            </div>
            <div class="batch-tab-item" onclick="switchBatchTab('tabOutbound')">
                <span>🏭 Riwayat Pemakaian</span>
                <span class="batch-tab-badge" id="bmTabCountOutbound">0</span>
            </div>
            <div class="batch-tab-item" onclick="switchBatchTab('tabLedger')">
                <span>📜 Kartu Mutasi (Ledger)</span>
                <span class="batch-tab-badge" id="bmTabCountLedger">0</span>
            </div>
        </div>

        {{-- 4. MODAL BODY (CONTENT PANES) --}}
        <div class="batch-modal-body">
            {{-- Loading State --}}
            <div id="bmLoadingState" style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 2rem; margin-bottom: 0.75rem; animation: spin 1s linear infinite; display: inline-block;">⏳</div>
                <div style="font-weight: 700; color: #334155;">Memuat Riwayat Dossier Batch...</div>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.25rem;">Mengumpulkan data inbound QC, saldo gudang, dan pemakaian bahan</div>
            </div>

            {{-- Error State --}}
            <div id="bmErrorState" style="display: none; text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem; color: #ef4444;">⚠️</div>
                <div style="font-weight: 700; color: #991b1b;" id="bmErrorMessage">Gagal memuat rincian batch.</div>
                <button type="button" class="btn btn-secondary btn-sm" style="margin-top: 1rem;" onclick="retryFetchBatch()">
                    Coba Lagi
                </button>
            </div>

            {{-- Content Wrapper --}}
            <div id="bmContentWrapper" style="display: none;">
                {{-- TAB 1: Saldo Fisik & Gudang --}}
                <div id="pane-tabSaldo" class="batch-tab-pane active">
                    <div style="margin-bottom: 0.75rem; font-weight: 700; font-size: 0.85rem; color: #0f172a; display: flex; justify-content: space-between; align-items: center;">
                        <span>Rincian Saldo Batch per Lokasi Gudang &amp; Grade:</span>
                        <span style="font-size: 0.75rem; color: #64748b;">Mendukung multi-grade dalam satu nomor batch</span>
                    </div>

                    <table class="batch-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Lokasi Gudang</th>
                                <th style="text-align: center; width: 100px;">Grade</th>
                                <th style="text-align: center;">Tgl Expired</th>
                                <th style="text-align: right;">Qty Awal</th>
                                <th style="text-align: right;">Terpakai</th>
                                <th style="text-align: right;">Sisa Qty</th>
                                <th style="text-align: right;">Harga Satuan</th>
                                <th style="text-align: right;">Sisa Nilai</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="bmSaldoTbody">
                            {{-- Populated via JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- TAB 2: Asal-Usul Kedatangan & QC Masuk --}}
                <div id="pane-tabInbound" class="batch-tab-pane">
                    {{-- Bagian A: Jika Batch Hasil Produksi WIP / FG --}}
                    <div id="bmInboundProduksiBox" style="display: none; margin-bottom: 1.25rem;">
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="font-weight: 800; color: #166534; font-size: 0.95rem;">
                                    🏭 Diproduksi dari Lembar Kerja Produksi Harian
                                </div>
                                <span id="bmProdNoBadge" style="font-family: monospace; font-weight: 800; background: #ffffff; color: #15803d; padding: 2px 8px; border-radius: 4px; border: 1px solid #86efac;">
                                    -
                                </span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; font-size: 0.8rem; margin-top: 0.75rem;">
                                <div>Tgl Produksi: <strong id="bmProdTgl">-</strong></div>
                                <div>Lini: <strong id="bmProdLini">-</strong></div>
                                <div>Varietas Singkong: <strong id="bmProdVarietas">-</strong></div>
                                <div>Rendemen: <strong id="bmProdRendemen" style="color: #047857;">-</strong></div>
                                <div>HPP per Kg: <strong id="bmProdHpp" style="color: #0f172a;">-</strong></div>
                                <div>Hasil Output: <strong id="bmProdQtyHasil">-</strong></div>
                            </div>
                        </div>

                        {{-- Bahan Baku yang Digunakan --}}
                        <div style="font-weight: 700; font-size: 0.825rem; color: #334155; margin-bottom: 0.5rem;">
                            Bahan Baku &amp; Batch yang Digunakan untuk Memasak Batch Ini:
                        </div>
                        <table class="batch-table">
                            <thead>
                                <tr>
                                    <th>Nama Bahan Baku</th>
                                    <th>Nomor Batch Bahan</th>
                                    <th style="text-align: center;">Grade</th>
                                    <th style="text-align: right;">Jumlah Terpakai</th>
                                </tr>
                            </thead>
                            <tbody id="bmProdBahanTbody">
                                {{-- Populated via JS --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Bagian B: Penerimaan dari Supplier (GRN) --}}
                    <div id="bmInboundTerimaBox">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
                            🚚 Dokumen Penerimaan Barang (Good Receipt):
                        </div>
                        <table class="batch-table" style="margin-bottom: 1.25rem;">
                            <thead>
                                <tr>
                                    <th>No. GRN</th>
                                    <th>Tgl Terima</th>
                                    <th>Supplier</th>
                                    <th>No. Surat Jalan</th>
                                    <th>No. PO</th>
                                    <th>Gudang</th>
                                    <th style="text-align: center;">Grade</th>
                                    <th style="text-align: right;">Qty Masuk</th>
                                    <th style="text-align: right;">Harga Beli</th>
                                </tr>
                            </thead>
                            <tbody id="bmTerimaTbody">
                                {{-- Populated via JS --}}
                            </tbody>
                        </table>

                        {{-- Bagian C: Hasil Inspeksi QC Masuk --}}
                        <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span>🔬 Hasil Uji Kualitas Bahan Datang (Incoming QC):</span>
                            <span id="bmQcCountBadge" style="font-size: 0.725rem; background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 4px; font-weight: 700;">
                                0 Tiket QC
                            </span>
                        </div>
                        <div id="bmQcCardsContainer">
                            {{-- Populated via JS --}}
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Riwayat Pemakaian (Outbound) --}}
                <div id="pane-tabOutbound" class="batch-tab-pane">
                    <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span>Daftar Pengeluaran &amp; Pemakaian Bahan Batch Ini:</span>
                        <span style="font-size: 0.75rem; color: #64748b;">Melacak alur pemakaian ke SPK Produksi atau packing</span>
                    </div>

                    <table class="batch-table">
                        <thead>
                            <tr>
                                <th>No. Pemakaian</th>
                                <th>Tgl Keluar</th>
                                <th>Gudang Asal</th>
                                <th>Tujuan Pemakaian</th>
                                <th style="text-align: center;">Grade</th>
                                <th style="text-align: right;">Qty Keluar</th>
                                <th style="text-align: right;">Total Nilai</th>
                                <th>Keterangan / SPK Terkait</th>
                            </tr>
                        </thead>
                        <tbody id="bmPakaiTbody">
                            {{-- Populated via JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- TAB 4: Kartu Mutasi Stok (Ledger Timeline) --}}
                <div id="pane-tabLedger" class="batch-tab-pane">
                    <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
                        Jejak Kronologis Kartu Stok (Audit Trail Saldo Berjalan):
                    </div>
                    <div id="bmLedgerTimeline" class="batch-timeline">
                        {{-- Populated via JS --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. MODAL FOOTER --}}
        <div class="batch-modal-footer">
            <div>
                <a id="bmBtnLedgerLink" href="#" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <span>📖 Buka Buku Kartu Stok Lengkap</span>
                    <span>&rarr;</span>
                </a>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeBatchDetailModal()" style="font-size: 0.8rem; padding: 0.4rem 1rem;">
                Tutup
            </button>
        </div>
    </div>
</div>
