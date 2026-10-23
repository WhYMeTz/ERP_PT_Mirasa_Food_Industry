{{-- MODAL CATAT RETUR PENJUALAN --}}
<div id="modalReturPenjualan" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-container" style="background: #ffffff; border-radius: 12px; max-width: 820px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #fff7ed;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #ea580c; color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(234,88,12,0.3);">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4m12 11a4 4 0 004-4v-1a4 4 0 00-4-4h-4"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #9a3412; margin: 0;">Catat Retur Penjualan (Customer Return)</h3>
                    <p style="font-size: 0.8rem; color: #c2410c; margin: 0.15rem 0 0 0;">
                        No. SO: <strong>{{ $order->so_no }}</strong> &bull; Customer: <strong>{{ $order->customer?->customer_nm }}</strong>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeReturModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1; padding: 0.25rem;">&times;</button>
        </div>

        {{-- FORM RETUR --}}
        <form id="formReturPenjualan" action="{{ route('penjualan.so.retur', $order->so_id) }}" method="POST" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
            @csrf
            
            <div style="padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem; max-height: calc(90vh - 140px);">
                
                {{-- BARIS 1: TANGGAL & GUDANG TUJUAN MASUK --}}
                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Penerimaan Retur <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" name="retur_tgl" value="{{ date('Y-m-d') }}" required class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Gudang Masuk Retur <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="gudang_id" required class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; background: #ffffff;">
                            @foreach($gudangs ?? [] as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ $gdg->gudang_cd === 'GDG-FG' ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; display: block;">
                            *Stok fisik akan dicatat ke kartu stok dengan Batch <code style="background:#f1f5f9; padding:2px 4px; border-radius:3px; color:#c2410c;">RET-{{ $order->so_no }}</code>
                        </span>
                    </div>
                </div>

                {{-- BARIS 2: TINDAKAN KOMERSIAL RETUR --}}
                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">
                        Tindakan Komersial Retur <span style="color: #dc2626;">*</span>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.65rem; padding: 0.75rem; border: 1.5px solid #ea580c; border-radius: 8px; background: #fff7ed; cursor: pointer;">
                            <input type="radio" name="tindakan_cd" value="POTONG_TAGIHAN" checked style="margin-top: 0.2rem; accent-color: #ea580c;">
                            <div>
                                <strong style="display: block; font-size: 0.85rem; color: #9a3412;">Potong Piutang / Tagihan (Credit Note)</strong>
                                <span style="font-size: 0.75rem; color: #c2410c; display: block; margin-top: 0.15rem; line-height: 1.3;">
                                    Sisa tagihan invoice SO otomatis dikurangi sejumlah nominal barang yang diretur.
                                </span>
                            </div>
                        </label>

                        <label style="display: flex; align-items: flex-start; gap: 0.65rem; padding: 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #f8fafc; cursor: pointer;">
                            <input type="radio" name="tindakan_cd" value="TUKAR_GULING" style="margin-top: 0.2rem; accent-color: #ea580c;">
                            <div>
                                <strong style="display: block; font-size: 0.85rem; color: #334155;">Tukar Guling / Ganti Barang Fisik</strong>
                                <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 0.15rem; line-height: 1.3;">
                                    Nilai invoice tetap utuh. Barang cacat ditarik dan akan diganti pengiriman barang baru.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- BARIS 3: ALASAN RETUR --}}
                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Retur / Berita Acara Kerusakan
                    </label>
                    <textarea name="alasan_txt" rows="2" placeholder="Contoh: 5 karton kardus basah saat pengiriman ekspedisi, isi keripik remuk..." style="width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; resize: vertical;"></textarea>
                </div>

                {{-- BARIS 4: TABEL ITEM BARANG YANG DIRETUR --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label style="font-size: 0.825rem; font-weight: 700; color: #1e293b; margin: 0;">
                            Pilih Barang &amp; Masukkan Jumlah Retur <span style="color: #dc2626;">*</span>
                        </label>
                        <span style="font-size: 0.75rem; color: #64748b;">Hanya barang yang terkirim yang dapat diretur</span>
                    </div>

                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <th style="padding: 0.5rem 0.75rem; text-align: left; width: 40px;">No</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: left;">Nama Produk / Varian</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: right; width: 85px;">Terkirim</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: right; width: 85px;">Sisa Kirim</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: right; width: 110px;">Qty Retur</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: left; width: 140px;">Kondisi Barang</th>
                                    <th style="padding: 0.5rem 0.75rem; text-align: right; width: 120px;">Subtotal Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->details as $idx => $dtl)
                                    @php
                                        $alreadyRetur = (float) $dtl->total_retur_qty;
                                        $maxReturnable = max(0, (float) $dtl->kirim_qty - $alreadyRetur);
                                        $effectivePrice = (float) $dtl->pesan_qty > 0 
                                            ? ((float) $dtl->subtotal_tagihan / (float) $dtl->pesan_qty) 
                                            : (float) $dtl->harga_satuan;
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $maxReturnable <= 0 ? '#f8fafc' : '#ffffff' }};">
                                        <td style="padding: 0.6rem 0.75rem; text-align: center; color: #64748b;">
                                            {{ $idx + 1 }}
                                            <input type="hidden" name="items[{{ $idx }}][sodtl_id]" value="{{ $dtl->sodtl_id }}">
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem;">
                                            <strong style="color: #0f172a; display: block;">{{ $dtl->barang?->barang_nm }}</strong>
                                            <span style="font-family: monospace; font-size: 0.725rem; color: #64748b;">{{ $dtl->barang?->barang_cd }}</span>
                                            @if($alreadyRetur > 0)
                                                <span style="display: block; font-size: 0.7rem; color: #ea580c; font-weight: 600;">(Telah diretur sebelumnya: {{ number_format($alreadyRetur, 0) }} unit)</span>
                                            @endif
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem; text-align: right; font-family: monospace; color: #475569;">
                                            {{ number_format((float) $dtl->kirim_qty, 0) }}
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem; text-align: right; font-family: monospace; font-weight: 700; color: {{ $maxReturnable > 0 ? '#15803d' : '#94a3b8' }};">
                                            {{ number_format($maxReturnable, 0) }}
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem; text-align: right;">
                                            @if($maxReturnable > 0)
                                                <input type="number" 
                                                       name="items[{{ $idx }}][qty_retur]" 
                                                       min="0" 
                                                       max="{{ $maxReturnable }}" 
                                                       step="1" 
                                                       value="0" 
                                                       data-price="{{ $effectivePrice }}" 
                                                       data-idx="{{ $idx }}"
                                                       oninput="recalcReturRow(this)"
                                                       style="width: 90px; text-align: right; padding: 0.35rem 0.5rem; border: 1.5px solid #cbd5e1; border-radius: 4px; font-weight: 700; font-family: monospace;">
                                            @else
                                                <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Nihil</span>
                                            @endif
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem;">
                                            @if($maxReturnable > 0)
                                                <select name="items[{{ $idx }}][kondisi_cd]" style="width: 100%; padding: 0.35rem 0.4rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.775rem;">
                                                    <option value="RUSAK_KEMASAN">Kemasan Sobek / Rusak</option>
                                                    <option value="BOCOR_KEMPES">Bocor / Kempes</option>
                                                    <option value="CACAT_PRODUKSI">Cacat / Gosong</option>
                                                    <option value="EXPIRED">Dekat Kadaluarsa</option>
                                                    <option value="LAINNYA">Lainnya / Sortir</option>
                                                </select>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem; text-align: right; font-family: monospace; font-weight: 700; color: #ea580c;">
                                            <span id="returRowSubtotal_{{ $idx }}">Rp 0</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background: #f8fafc; border-top: 2px solid #e2e8f0; font-weight: 700;">
                                <tr>
                                    <td colspan="4" style="padding: 0.65rem 0.75rem; text-align: right; color: #475569;">
                                        Total Retur:
                                    </td>
                                    <td style="padding: 0.65rem 0.75rem; text-align: right; font-family: monospace; color: #0f172a;">
                                        <span id="lblTotalReturQty">0</span> Karton
                                    </td>
                                    <td></td>
                                    <td style="padding: 0.65rem 0.75rem; text-align: right; font-family: monospace; color: #ea580c; font-size: 0.95rem;">
                                        <span id="lblTotalReturNilai">Rp 0</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            {{-- FOOTER BUTTONS --}}
            <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeReturModal()" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background: #ea580c; border-color: #ea580c; color: #ffffff; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(234,88,12,0.2);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Retur Penjualan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL VOID / BATALKAN RETUR PENJUALAN --}}
<div id="modalVoidRetur" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-container" style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; justify-content: space-between; background: #fef2f2;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #dc2626; color: #ffffff; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #991b1b; margin: 0;">Batalkan Retur Penjualan (Void)</h3>
                    <p style="font-size: 0.75rem; color: #b91c1c; margin: 0.15rem 0 0 0;" id="modalVoidReturNo">-</p>
                </div>
            </div>
            <button type="button" onclick="closeVoidReturModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formVoidRetur" method="POST" style="margin: 0;">
            @csrf
            <div style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.75rem; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; font-size: 0.8rem; color: #c53030; line-height: 1.4;">
                    <strong>Perhatian:</strong> Pembatalan retur akan otomatis memotong / mengeluarkan kembali stok fisik barang retur dari gudang dan mengembalikan tagihan faktur ke kondisi semula.
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Pembatalan Retur <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Salah input data retur / customer membatalkan klaim retur..." style="width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; resize: vertical;"></textarea>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeVoidReturModal()" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626; color: #ffffff; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight: 700;">
                    Konfirmasi Void Retur
                </button>
            </div>
        </form>
    </div>
</div>
