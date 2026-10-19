{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL PERBARUI CEPAT SELURUH DAFTAR HARGA KEMASAN (EXCEL MATRIX SYNC)     --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalQuickUpdateTarif" class="modal-overlay">
    <div class="modal-card wide">
        <div class="modal-card-header" style="background: #f0fdf4; border-bottom: 1.5px solid #bbf7d0;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #14532d; margin: 0;">
                        Sinkronisasi Cepat Daftar Harga Kemasan (Format Excel)
                    </h3>
                    <span style="font-size: 0.75rem; color: #166534;">
                        Perbarui angka acuan seluruh komponen bahan kemasan sekaligus sesuai lembar kerja pabrik
                    </span>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeQuickUpdateModal()">&times;</button>
        </div>

        <form id="formQuickUpdateTarif" method="POST" action="{{ route('master.tarif_kemasan.quick-update') }}">
            @csrf

            <div class="modal-card-body">
                <div class="matrix-grid">
                    @if(!empty($tarifs))
                        @foreach($tarifs as $item)
                            <div class="matrix-item">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                    <label for="rate_{{ $item->kode_tarif }}" class="matrix-label">
                                        {{ $item->nama_tarif }}
                                    </label>
                                    <span style="font-size: 0.685rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1px 4px; border-radius: 4px;">
                                        {{ $item->satuan_label }}
                                    </span>
                                </div>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.8rem;">Rp</span>
                                    <input type="number" 
                                           step="any" 
                                           min="0" 
                                           id="rate_{{ $item->kode_tarif }}" 
                                           name="rates[{{ $item->kode_tarif }}]" 
                                           value="{{ $item->nilai_tarif }}" 
                                           class="form-control" 
                                           style="padding-left: 2.2rem; font-weight: 700; font-family: monospace; font-size: 0.9rem; color: #0f172a; height: 36px;">
                                </div>
                                @if($item->nilai_tarif_sekunder !== null)
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem;">
                                        Alternatif: Rp {{ number_format($item->nilai_tarif_sekunder, 2, ',', '.') }} {{ $item->satuan_sekunder_label }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="modal-card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <a href="{{ route('master.tarif_kemasan.index') }}" target="_blank" style="font-size: 0.8rem; color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="Buka master data lengkap di tab baru">
                    <span>Kelola Master Data Lengkap</span>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeQuickUpdateModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" style="font-size: 0.825rem; padding: 0.45rem 1.35rem; background: #059669; border-color: #059669; color: #ffffff; font-weight: 700;">
                        Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
