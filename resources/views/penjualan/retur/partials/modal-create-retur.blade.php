{{-- MODAL CATAT RETUR PENJUALAN DARI REKAP RETUR --}}
<div id="modalCreateRetur" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-container" style="background: #ffffff; border-radius: 12px; max-width: 880px; width: 100%; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: modalFadeIn 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #fff7ed;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #ea580c; color: #ffffff; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(234,88,12,0.3);">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4m12 11a4 4 0 004-4v-1a4 4 0 00-4-4h-4"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #9a3412; margin: 0;">Catat Retur Penjualan (Customer Return)</h3>
                    <p style="font-size: 0.8rem; color: #c2410c; margin: 0.15rem 0 0 0;">
                        Formulir berita acara pengembalian barang rusak/cacat dan penyesuaian piutang customer.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeCreateReturModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1; padding: 0.25rem;">&times;</button>
        </div>

        {{-- FORM RETUR --}}
        <form id="formCreateRetur" action="" method="POST" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
            @csrf
            <input type="hidden" name="redirect_to" value="retur_index">
            
            <div style="padding: 1.25rem 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.15rem; max-height: calc(92vh - 140px);">
                
                {{-- PILIH DOKUMEN PESANAN / SO TERKIRIM --}}
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.9rem 1rem;">
                    <label style="display: block; font-size: 0.825rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Pilih Dokumen Pesanan (SO Terkirim) <span style="color: #dc2626;">*</span>
                    </label>
                    <select id="createReturSoSelect" onchange="onSelectSoForRetur(this.value)" class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; background: #ffffff;">
                        <option value="">-- Pilih Nomor SO / Customer yang akan diretur --</option>
                        @foreach($eligibleSos ?? [] as $eso)
                            <option value="{{ $eso->so_id }}">
                                {{ $eso->so_no }} &bull; {{ $eso->customer?->customer_nm }} ({{ $eso->so_tgl ? $eso->so_tgl->format('d/m/Y') : '-' }} &bull; Rp {{ number_format($eso->total_tagihan, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <span style="font-size: 0.725rem; color: #64748b; margin-top: 0.35rem; display: block;">
                        *Hanya menampilkan pesanan yang statusnya telah terkirim (COMPLETED / PARTIAL).
                    </span>
                </div>

                {{-- LOADING INDICATOR --}}
                <div id="returLoadingIndicator" style="display: none; text-align: center; padding: 2rem 1rem; color: #ea580c;">
                    <div style="display: inline-block; width: 28px; height: 28px; border: 3px solid #fdba74; border-top-color: #ea580c; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                    <p style="font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem; color: #9a3412;">Memuat rincian item pesanan...</p>
                </div>

                {{-- EMPTY PROMPT SAAT BELUM MEMILIH SO --}}
                <div id="returEmptyPrompt" style="text-align: center; padding: 2.5rem 1rem; border: 2px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; color: #64748b;">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 0.5rem auto; color: #94a3b8; display: block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p style="font-size: 0.9rem; font-weight: 600; color: #475569; margin: 0;">Silakan Pilih Dokumen Pesanan (SO) di Atas</p>
                    <p style="font-size: 0.775rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Daftar rincian barang, harga, dan alokasi retur akan otomatis muncul setelah dokumen dipilih.</p>
                </div>

                {{-- FORM BODY (MUNCUL SETELAH SO DIPILIH) --}}
                <div id="returFormFields" style="display: none; flex-direction: column; gap: 1.15rem;">
                    
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
                            <span id="returBatchInfoLabel" style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; display: block;">
                                *Stok fisik akan dicatat ke kartu stok dengan Batch <code style="background:#f1f5f9; padding:2px 4px; border-radius:3px; color:#c2410c;" id="returBatchLabel">RET-SO</code>
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
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin: 0; display: block;">
                                    Rincian Barang &amp; Disposisi Fisik Retur <span style="color: #dc2626;">*</span>
                                </label>
                                <span style="font-size: 0.75rem; color: #64748b;">
                                    Pilih apakah barang retur <strong>Masuk Persediaan (Sortir KW 2)</strong> atau <strong>Dimusnahkan di Pabrik (Scrap)</strong>.
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">Setel Serentak:</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAllCreateDisposisi('MASUK_PERSEDIAAN')" style="font-size: 0.725rem; padding: 2px 8px; border-radius: 4px; font-weight: 700; color: #166534; border-color: #86efac; background: #f0fdf4;">
                                    ✓ Semua Masuk KW 2
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAllCreateDisposisi('DIMUSNAHKAN')" style="font-size: 0.725rem; padding: 2px 8px; border-radius: 4px; font-weight: 700; color: #991b1b; border-color: #fecaca; background: #fef2f2;">
                                    ✕ Semua Dimusnahkan
                                </button>
                            </div>
                        </div>

                        <div style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #ffffff;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                                <thead>
                                    <tr style="background: #0f172a; color: #f8fafc; border-bottom: 1px solid #334155;">
                                        <th style="padding: 0.6rem 0.65rem; text-align: center; width: 35px; font-size: 0.75rem; font-weight: 700;">No</th>
                                        <th style="padding: 0.6rem 0.65rem; text-align: left; font-size: 0.75rem; font-weight: 700;">Nama Produk / Varian</th>
                                        <th style="padding: 0.6rem 0.65rem; text-align: right; width: 75px; font-size: 0.75rem; font-weight: 700;">Terkirim</th>
                                        <th style="padding: 0.6rem 0.65rem; text-align: right; width: 75px; font-size: 0.75rem; font-weight: 700;">Sisa</th>
                                        <th style="padding: 0.6rem 0.65rem; text-align: right; width: 85px; font-size: 0.75rem; font-weight: 700;">Qty Retur</th>
                                        <th style="padding: 0.6rem 0.65rem; text-align: left; width: 130px; font-size: 0.75rem; font-weight: 700;">Kondisi Produk</th>
                                        <th style="padding: 0.6rem 0.75rem; text-align: left; width: 230px; font-size: 0.75rem; font-weight: 700;">Disposisi Fisik Barang</th>
                                        <th style="padding: 0.6rem 0.75rem; text-align: right; width: 115px; font-size: 0.75rem; font-weight: 700;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="createReturItemsTbody">
                                    {{-- Baris item di-render secara dinamis lewat JS saat SO dipilih --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- RINGKASAN TOTAL RETUR & DAMPAK KOMERSIAL --}}
                    <div style="background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 8px; padding: 1rem 1.25rem;">
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; align-items: center;">
                            <div>
                                <span style="font-size: 0.75rem; color: #9a3412; font-weight: 600; display: block;">Total Kuantitas Diretur:</span>
                                <strong id="createReturSumQty" style="font-size: 1.25rem; font-family: monospace; color: #ea580c;">0 Karton</strong>
                            </div>
                            <div>
                                <span style="font-size: 0.75rem; color: #166534; font-weight: 600; display: block;">Masuk Persediaan (KW 2):</span>
                                <strong id="createReturSumMasuk" style="font-size: 1.1rem; font-family: monospace; color: #15803d;">0 Karton</strong>
                            </div>
                            <div>
                                <span style="font-size: 0.75rem; color: #991b1b; font-weight: 600; display: block;">Dimusnahkan (Scrap):</span>
                                <strong id="createReturSumMusnah" style="font-size: 1.1rem; font-family: monospace; color: #dc2626;">0 Karton</strong>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 0.75rem; color: #9a3412; font-weight: 600; display: block;">Estimasi Potong Tagihan:</span>
                                <strong id="createReturSumNilai" style="font-size: 1.25rem; font-family: monospace; color: #c2410c;">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            {{-- FOOTER MODAL --}}
            <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeCreateReturModal()" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitCreateRetur" disabled class="btn btn-primary" style="background: #ea580c; border-color: #c2410c; font-size: 0.85rem; padding: 0.5rem 1.25rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Dokumen Retur</span>
                </button>
            </div>
        </form>

    </div>
</div>

