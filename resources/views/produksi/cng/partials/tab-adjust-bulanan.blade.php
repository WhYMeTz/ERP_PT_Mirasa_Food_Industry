{{-- FORM PENYESUAIAN TAGIHAN BULANAN FAKTUR TOTAL VENDOR --}}
@if($workDaysCount == 0)
    <div class="card" style="padding: 3rem; text-align: center; color: #64748b; margin-bottom: 2rem;">
        <svg width="44" height="44" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="margin: 0 auto 0.75rem auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.25rem;">
            Tidak Ada Catatan Produksi
        </div>
        <p style="font-size: 0.825rem; margin: 0;">
            Belum terdapat lembar catatan produksi pada periode <strong>{{ $monthsList[$month] }} {{ $year }}</strong>.
        </p>
    </div>
@else
    <form id="formMainAdjustUtilitas" action="{{ route('produksi.adjust-utilitas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tahun" value="{{ $year }}">
        <input type="hidden" name="bulan" value="{{ $month }}">
        <input type="hidden" name="redirect_to" value="adjust">
        <input type="hidden" name="adjust_cng" value="1">
        <input type="hidden" name="adjust_listrik" value="0">

        <div class="card" style="margin-bottom: 1.25rem; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff; overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.95rem 1.35rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 style="font-size: 0.975rem; font-weight: 800; color: #0f172a; margin: 0;">
                            Rekonsiliasi Faktur Tagihan Gas Bulanan
                        </h2>
                        <span style="font-size: 0.725rem; color: #64748b;">
                            Masukkan total tagihan faktur aktual bulanan dari vendor PGN/CNG, lalu pilih metode distribusi biaya ke seluruh hari produksi.
                        </span>
                    </div>
                </div>
                <span class="badge" style="font-size: 0.725rem; font-weight: 700; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; padding: 0.25rem 0.65rem; border-radius: 4px;">
                    Fleksibel &bull; {{ $workDaysCount }} Hari Kerja
                </span>
            </div>

            <div class="card-body" style="padding: 1.35rem;">
                {{-- PARAMETER TOTAL TAGIHAN & METODE ALOKASI --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    {{-- TOTAL TAGIHAN FAKTUR --}}
                    <div style="background: #fffdf5; border: 1.5px solid #fde68a; border-radius: 8px; padding: 1rem;">
                        <label for="total_cng_tagihan" style="font-size: 0.8rem; font-weight: 700; color: #92400e; display: block; margin-bottom: 0.35rem;">
                            Total Nilai Tagihan Faktur Vendor (Rupiah): <span style="color: #ef4444;">*</span>
                        </label>
                        <div class="input-addon-group">
                            <span class="addon-prefix" style="font-weight: 800; color: #92400e; padding: 0 0.75rem;">Rp</span>
                            <input type="number" step="any" min="0" 
                                   name="total_cng_tagihan" 
                                   id="total_cng_tagihan" 
                                   class="form-control" 
                                   style="font-size: 1.25rem; font-weight: 800; font-family: ui-monospace, monospace; color: #92400e; height: 42px;"
                                   value="{{ round($totalNilaiRp, 0) }}" 
                                   placeholder="0"
                                   required>
                        </div>
                        <small style="font-size: 0.7rem; color: #78350f; margin-top: 0.35rem; display: block;">
                            Total nominal akhir pada faktur pajak / invoice resmi vendor untuk bulan {{ $monthsList[$month] }} {{ $year }}.
                        </small>
                    </div>

                    {{-- PILIHAN METODE DISTRIBUSI TAGIHAN --}}
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <label for="mode_cng" style="font-size: 0.8rem; font-weight: 700; color: #0f172a; display: block; margin-bottom: 0.35rem;">
                                Metode Pembagian Biaya Tagihan:
                            </label>
                            <select name="mode_cng" id="mode_cng" class="form-control" style="font-weight: 600; font-size: 0.825rem; height: 38px;">
                                <option value="total_tagihan" selected>1. Proporsional Pemakaian Gas (MMBTU &bull; {{ number_format($totalMmbtu, 2, ',', '.') }} MMBTU)</option>
                                <option value="proporsional_wip">2. Proporsional Hasil Produksi (Total WIP &bull; {{ number_format($totalWipKg, 0, ',', '.') }} Kg)</option>
                                <option value="bagi_rata">3. Dibagi Rata Flat (Bagi rata per {{ $workDaysCount }} Hari Kerja)</option>
                            </select>
                        </div>
                        <span style="font-size: 0.725rem; color: #64748b; margin-top: 0.5rem; display: block;">
                            Sistem otomatis menghitung beban gas dan merevisi HPP per kg di setiap dokumen produksi terkait.
                        </span>
                    </div>
                </div>

                {{-- TOMBOL AKSI ALOKASI BULANAN --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                    <button type="submit" class="btn btn-primary" style="font-size: 0.825rem; font-weight: 700; background: #0284c7; border-color: #0369a1; padding: 0.5rem 1.25rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Terapkan Rekonsiliasi Faktur Bulanan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endif
