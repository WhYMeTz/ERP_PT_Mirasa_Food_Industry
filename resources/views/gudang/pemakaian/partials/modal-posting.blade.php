{{-- MODAL KONFIRMASI VERIFIKASI & POSTING PEMAKAIAN BAHAN --}}
<div id="modalPostingPemakaian" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:580px; overflow:hidden; border: 1.5px solid #a7f3d0; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Header Modal Emerald --}}
        <div style="background:#f0fdf4; border-bottom:1px solid #bbf7d0; padding:1.15rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:38px; height:38px; border-radius:50%; background:#059669; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(5,150,105,0.3);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#065f46;">Verifikasi &amp; Terbitkan Jurnal Pemakaian</h3>
                    <div style="font-size:0.75rem; color:#047857; margin-top:2px;">Dokumen No: <span style="font-family:monospace; font-weight:700;">{{ $pakai->pakai_no }}</span></div>
                </div>
            </div>
            <button type="button" onclick="closeModalPostingPemakaian()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <form action="{{ route('gudang.pemakaian.post', $pakai->pakai_id) }}" method="POST">
            @csrf
            <div style="padding:1.25rem;">
                <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:1rem; line-height:1.5;">
                    Anda akan memverifikasi pengeluaran bahan untuk <strong>{{ $pakai->tujuan_pemakaian }}</strong> sebanyak <strong>{{ $pakai->details->count() }} item bahan</strong>.
                </p>

                {{-- Kotak Simulasi Nilai & Jurnal Akuntansi --}}
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:1rem; margin-bottom:1.25rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; padding-bottom:0.5rem; border-bottom:1px dashed #cbd5e1;">
                        <span style="font-size:0.85rem; color:#64748b; font-weight:600;">Total Biaya Pemakaian (HPP):</span>
                        <strong style="font-size:1.1rem; color:#059669; font-family:monospace;">
                            Rp {{ number_format((float) ($grandNilai ?? $pakai->details->sum('total_harga')), 0, ',', '.') }}
                        </strong>
                    </div>

                    <div style="font-size:0.775rem; color:#475569; font-weight:700; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.5px;">
                        Otomasi Jurnal Akuntansi yang Diterbitkan:
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:0.8rem; background:#fff; border:1px solid #cbd5e1; border-radius:6px; overflow:hidden;">
                        <thead>
                            <tr style="background:#f1f5f9; color:#475569;">
                                <th style="padding:0.4rem 0.6rem; text-align:left;">Akun</th>
                                <th style="padding:0.4rem 0.6rem; text-align:right;">Debet</th>
                                <th style="padding:0.4rem 0.6rem; text-align:right;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:0.45rem 0.6rem; color:#0f172a;">
                                    <strong>1151</strong> - Persediaan WIP &amp; FG
                                </td>
                                <td style="padding:0.45rem 0.6rem; text-align:right; font-family:monospace; color:#059669; font-weight:700;">
                                    Rp {{ number_format((float) ($grandNilai ?? $pakai->details->sum('total_harga')), 0, ',', '.') }}
                                </td>
                                <td style="padding:0.45rem 0.6rem; text-align:right; color:#94a3b8;">-</td>
                            </tr>
                            <tr>
                                <td style="padding:0.45rem 0.6rem; color:#0f172a;">
                                    <strong>1150</strong> - Persediaan Bahan Baku &amp; Penolong
                                </td>
                                <td style="padding:0.45rem 0.6rem; text-align:right; color:#94a3b8;">-</td>
                                <td style="padding:0.45rem 0.6rem; text-align:right; font-family:monospace; color:#dc2626; font-weight:700;">
                                    Rp {{ number_format((float) ($grandNilai ?? $pakai->details->sum('total_harga')), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Alert Peringatan Potong Stok --}}
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:0.75rem 0.85rem; margin-bottom:1rem; font-size:0.8rem; color:#1e40af; line-height:1.45; display:flex; gap:0.5rem; align-items:flex-start;">
                    <span style="font-size:1.1rem; line-height:1;">ℹ️</span>
                    <div>
                        <strong>Dampak Operasional:</strong> Setelah diverifikasi, <strong>stok fisik di gudang akan otomatis dipotong</strong> pada kartu stok batch masing-masing bahan dan status dokumen akan dikunci menjadi <strong>POSTED</strong>.
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div style="display:flex; justify-content:flex-end; gap:0.6rem; align-items:center;">
                    <button type="button" onclick="closeModalPostingPemakaian()" class="btn btn-secondary" style="padding:0.55rem 1rem; font-size:0.85rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#fff; font-size:0.85rem; font-weight:700; padding:0.55rem 1.25rem; display:inline-flex; align-items:center; gap:0.4rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Ya, Verifikasi &amp; Terbitkan Jurnal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalPostingPemakaian() {
        const modal = document.getElementById('modalPostingPemakaian');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeModalPostingPemakaian() {
        const modal = document.getElementById('modalPostingPemakaian');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Tutup saat klik di luar box modal
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modalPostingPemakaian');
        if (e.target === modal) {
            closeModalPostingPemakaian();
        }
    });
</script>
