{{-- Modal Minta Revisi / Tinjauan PO (macOS Style) --}}
<div id="modalRevisionPo" class="modal-review-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalRevisionTitle">
    <div class="modal-review-content modal-review-wide">
        {{-- macOS Window Titlebar --}}
        <div class="macos-doc-modal-header">
            {{-- Left: macOS Traffic Lights (Window Controls) --}}
            <div class="mac-traffic-lights" aria-label="Window Controls">
                <button type="button" class="mac-traffic-dot red" onclick="closeReviewModal('modalRevisionPo')" title="Tutup (Esc)">
                    <span class="mac-traffic-icon">&times;</span>
                </button>
                <button type="button" class="mac-traffic-dot yellow" onclick="closeReviewModal('modalRevisionPo')" title="Minimalkan">
                    <span class="mac-traffic-icon">&minus;</span>
                </button>
                <button type="button" class="mac-traffic-dot green" title="Revisi">
                    <span class="mac-traffic-icon">&plus;</span>
                </button>
            </div>

            {{-- Center: macOS Document Proxy & Title --}}
            <div class="mac-window-title-center" id="modalRevisionTitle">
                <svg class="mac-window-file-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span class="mac-window-filename">PO #{{ $po->po_no }} &mdash; Instruksi Revisi</span>
                <span class="modal-status-chip" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                    🔄 Perlu Revisi
                </span>
            </div>

            {{-- Right: Esc indicator --}}
            <div class="modal-header-actions">
                <button type="button" class="btn-mac-seg" onclick="closeReviewModal('modalRevisionPo')" title="Tutup (Esc)" style="font-size: 0.75rem; padding: 0.2rem 0.65rem; border-radius: 6px;">
                    Esc
                </button>
            </div>
        </div>
        
        <form id="formRevisionPo" action="{{ route('gudang.po.request_revision', $po->po_id) }}" method="POST">
            @csrf
            <div class="modal-review-scroll-body">
                
                {{-- 1. RINGKASAN DATA PO YANG DITINJAU --}}
                <div class="rev-summary-grid">
                    <div class="rev-summary-item">
                        <span class="rev-summary-label">Nomor Dokumen PO</span>
                        <span class="rev-summary-val" style="color: #2563eb;">{{ $po->po_no }}</span>
                    </div>
                    <div class="rev-summary-item">
                        <span class="rev-summary-label">Tanggal PO</span>
                        <span class="rev-summary-val">
                            {{ $po->po_tgl ? \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>
                    <div class="rev-summary-item">
                        <span class="rev-summary-label">Mitra Supplier</span>
                        <span class="rev-summary-val">{{ $po->supplier?->supplier_nm ?? '-' }}</span>
                    </div>
                    <div class="rev-summary-item">
                        <span class="rev-summary-label">Gudang Penerima</span>
                        <span class="rev-summary-val">{{ $po->gudang?->gudang_nm ?? '-' }}</span>
                    </div>
                    <div class="rev-summary-item">
                        <span class="rev-summary-label">Pemohon / Pengaju</span>
                        <span class="rev-summary-val">{{ $po->created_by ?? 'Admin Gudang' }}</span>
                    </div>
                    <div class="rev-summary-item">
                        <span class="rev-summary-label">Total Anggaran PO</span>
                        <span class="rev-summary-val" style="color: #059669;">
                            Rp {{ number_format((float) $po->total_tagihan, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- 2. TABEL RINCIAN ITEM BARANG YANG DIAJUKAN --}}
                <div class="rev-items-card">
                    <div class="rev-items-header">
                        <span>Rincian Barang Diajukan ({{ $po->details->count() }} Komoditas)</span>
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;">Periksa data sebelum memberi arahan</span>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="rev-table">
                            <thead>
                                <tr>
                                    <th style="width: 35px; text-align: center;">#</th>
                                    <th>Nama Komoditas / Barang</th>
                                    <th style="text-align: right;">Qty Pesan</th>
                                    <th style="text-align: right;">Harga Satuan</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($po->details as $index => $dtl)
                                    @php
                                        $qty = (float) $dtl->pesan_qty;
                                        $harga = (float) ($dtl->harga_nominal ?? 0);
                                        $subtotal = (float) ($dtl->subtotal_tagihan ?? ($qty * $harga));
                                        $satuan = $dtl->satuan_nm ?? ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Unit');
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $index + 1 }}</td>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">
                                                {{ $dtl->barang?->barang_nm ?? ($dtl->barang_nm ?? 'Item Komoditas') }}
                                            </div>
                                            <div style="font-size: 0.725rem; color: #64748b;">
                                                Kode: {{ $dtl->barang?->barang_cd ?? '-' }}
                                            </div>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; white-space: nowrap;">
                                            {{ number_format($qty, 0, ',', '.') }}
                                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">{{ $satuan }}</span>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            Rp {{ number_format($harga, 0, ',', '.') }}
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: #0f172a; white-space: nowrap;">
                                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. FORMULIR CATATAN / INSTRUKSI REVISI KE ADMIN GUDANG --}}
                <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-left: 4px solid #2563eb; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 0.85rem;">
                    <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                        <svg width="18" height="18" fill="none" stroke="#2563eb" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p style="margin: 0; font-size: 0.8125rem; color: #1e40af; line-height: 1.45;">
                            Dokumen PO ini akan dikembalikan ke status <strong>PERLU REVISI</strong>. Tuliskan poin instruksi koreksi yang jelas agar staf pengadaan / admin gudang mengetahui perbaikan yang harus dilakukan sebelum diajukan kembali.
                        </p>
                    </div>
                </div>

                <div>
                    <label for="revision_notes" style="display: block; font-size: 0.8125rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">
                        Poin Koreksi & Arahan Revisi untuk Admin Gudang: <span style="color: #dc2626;">*</span>
                    </label>

                    {{-- Chip Template Cepat --}}
                    <div class="rev-chip-group">
                        <button type="button" class="rev-quick-chip" onclick="insertRevisionTemplate('Mohon sesuaikan kuantitas pemesanan barang karena kapasitas stok gudang.')">
                            + Koreksi Kuantitas
                        </button>
                        <button type="button" class="rev-quick-chip" onclick="insertRevisionTemplate('Negosiasi ulang harga satuan penawaran supplier karena melebihi harga standar acuan.')">
                            + Negosiasi Harga
                        </button>
                        <button type="button" class="rev-quick-chip" onclick="insertRevisionTemplate('Sesuaikan jadwal estimasi tanggal kedatangan barang (ETA).')">
                            + Jadwal Kedatangan
                        </button>
                        <button type="button" class="rev-quick-chip" onclick="insertRevisionTemplate('Cek kembali syarat pembayaran (Term of Payment) dan faktur pajak.')">
                            + Termin / Pajak
                        </button>
                    </div>

                    <textarea 
                        name="revision_notes" 
                        id="revision_notes" 
                        class="modal-textarea" 
                        required 
                        rows="4"
                        placeholder="Tuliskan arahan revisi spesifik di sini, misalnya: Kuantitas Tepung Terigu dikurangi 1.000 KG dulu karena kapasitas gudang terbatas, atau negosiasi ulang harga satuan karena ada kenaikan di atas harga acuan pabrik..."></textarea>
                </div>

            </div>

            <div class="modal-review-footer">
                <button type="button" class="btn-executive btn-executive-secondary" onclick="closeReviewModal('modalRevisionPo')">
                    Batal
                </button>
                <button type="submit" class="btn-executive btn-executive-revision" id="btnSubmitRevision" style="background: #2563eb; color: #ffffff; border-color: #1d4ed8;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Instruksi Revisi ke Admin Gudang
                </button>
            </div>
        </form>
    </div>
</div>
