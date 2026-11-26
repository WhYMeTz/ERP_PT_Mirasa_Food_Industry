{{-- MODAL PEMBERITAHUAN DOKUMEN TERKUNCI PEMAKAIAN STOK BATCH --}}
<div id="modal-stock-locked" class="hazard-modal-backdrop" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="stock-locked-modal-title">
    <div class="hazard-modal-container" style="max-width: 620px;">
        <div class="hazard-modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <div class="hazard-modal-header-left">
                <div class="hazard-modal-icon-badge" style="background: #fef2f2; color: #dc2626; border-color: #fecaca;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h3 id="stock-locked-modal-title" class="hazard-modal-title" style="color: #0f172a;">Penerimaan Tidak Dapat Diedit</h3>
                    <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600;">Stok pada Batch Penerimaan Ini Sudah Terpakai</span>
                </div>
            </div>
            <button type="button" onclick="closeStockLockedModal()" class="hazard-modal-close-btn" aria-label="Tutup modal">&times;</button>
        </div>
        <div class="hazard-modal-body">
            <p class="hazard-modal-intro" style="font-size: 0.8125rem; color: #334155; line-height: 1.5; margin-bottom: 1rem;">
                Dokumen Penerimaan <strong>{{ $terima->terima_no }}</strong> tidak dapat diubah atau diedit karena fisik barang pada nomor batch terkait <strong>sudah pernah digunakan / dikeluarkan</strong> dari gudang untuk proses produksi, pengemasan, atau mutasi keluar.
            </p>

            @if (isset($stockUsage) && !empty($stockUsage['items']))
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 1.25rem;">
                    <table style="width: 100%; font-size: 0.8125rem; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; text-align: left;">
                                <th style="padding: 0.5rem 0.65rem;">Batch &amp; Komoditas</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right;">Masuk GRN</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right; color: #b91c1c;">Sudah Terpakai</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right;">Sisa di Gudang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockUsage['items'] as $itemU)
                                @if ($itemU['used_qty'] > 0 || $itemU['is_locked'])
                                    @php
                                        $docRefs = collect($itemU['usage_logs'] ?? [])->pluck('dokumen_no')->filter()->unique()->take(2)->implode(', ');
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 0.5rem 0.65rem;">
                                            <strong style="color: #0f172a;">{{ $itemU['barang_nm'] }}</strong>
                                            <div style="font-family: var(--po-font-mono, monospace); font-size: 0.725rem; color: #64748b;">
                                                Batch: {{ $itemU['batch_no'] }} ({{ $itemU['grade_cd'] }})
                                                @if ($docRefs)
                                                    <span style="display: block; color: #b45309;">Ref: {{ $docRefs }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="padding: 0.5rem 0.65rem; text-align: right; font-family: var(--po-font-mono, monospace); color: #0f172a;">
                                            {{ number_format($itemU['terima_netto'], 2, ',', '.') }} {{ $itemU['satuan_nm'] }}
                                        </td>
                                        <td style="padding: 0.5rem 0.65rem; text-align: right; font-family: var(--po-font-mono, monospace); font-weight: 700; color: #dc2626; background: #fef2f2;">
                                            - {{ number_format($itemU['used_qty'], 2, ',', '.') }} {{ $itemU['satuan_nm'] }}
                                        </td>
                                        <td style="padding: 0.5rem 0.65rem; text-align: right; font-family: var(--po-font-mono, monospace); font-weight: 700; color: #0f172a;">
                                            {{ number_format($itemU['sisa_qty'], 2, ',', '.') }} {{ $itemU['satuan_nm'] }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="hazard-modal-warning-box" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; padding: 0.75rem 1rem; border-radius: 8px;">
                <div style="font-size: 0.8125rem; line-height: 1.5;">
                    <strong style="color: #0f172a;">Prosedur Standar ERP:</strong><br>
                    Untuk menjaga keakuratan kartu stok dan laporan HPP, perubahan kuantitas pada bahan baku yang sudah terpakai <strong>wajib menggunakan formulir Penyesuaian Stok (Stock Adjustment)</strong> dengan Berita Acara, bukan dengan merevisi penerimaan awal.
                </div>
            </div>

            <div class="hazard-modal-actions" style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeStockLockedModal()" class="btn-pill-outline">Saya Mengerti</button>
                <a href="{{ route('gudang.adjustment.create') }}" class="btn-pill-dark" style="text-decoration: none;">
                    Buka Penyesuaian Stok &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

