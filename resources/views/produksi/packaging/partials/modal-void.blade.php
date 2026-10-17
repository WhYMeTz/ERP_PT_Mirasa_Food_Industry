{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  MODAL KONFIRMASI PEMBATALAN DOKUMEN PENGEMASAN (VOID PACKAGING) --}}
{{--  ERP PT Mirasa Food Industry - Sesuai Standar AGENTS.md          --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<div id="modalVoidPackaging" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 999999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: modalFadeIn 0.18s ease-out;">
        <form id="formVoidPackaging" method="POST" action="">
            @csrf
            
            {{-- Header Bahaya (Merah Lembut Konsisten) --}}
            <div class="modal-danger-header" style="background: #fef2f2; border-bottom: 1.5px solid #fecaca; padding: 1.1rem 1.35rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; font-size: 1.1rem; flex-shrink: 0;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="modal-danger-title" style="font-size: 1rem; font-weight: 800; color: #991b1b; margin: 0;">
                            Konfirmasi Pembatalan Packaging
                        </h3>
                        <p style="font-size: 0.75rem; color: #b91c1c; margin: 0.15rem 0 0 0;">
                            Status dokumen akan berubah menjadi VOID secara permanen.
                        </p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeVoidPackagingModal()" style="background: none; border: none; font-size: 1.3rem; color: #991b1b; cursor: pointer; padding: 0.2rem 0.4rem; line-height: 1;">&times;</button>
            </div>

            {{-- Body Modal --}}
            <div class="modal-body" style="padding: 1.25rem 1.35rem;">
                <p style="font-size: 0.85rem; color: #334155; line-height: 1.5; margin: 0 0 1rem 0;">
                    Anda akan membatalkan dokumen: <strong id="voidPackNoLabel" style="color: #0f172a; font-family: monospace;">-</strong>
                </p>

                <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.775rem; color: #9f1239; margin-bottom: 1rem; line-height: 1.45;">
                    <strong style="display: block; margin-bottom: 0.25rem;">Dampak Operasional:</strong>
                    <ul style="margin: 0; padding-left: 1.15rem;">
                        <li>Stok barang jadi (FG) hasil pengemasan akan ditarik kembali dari sistem.</li>
                        <li>Stok keripik curah (WIP) dan material kemasan akan dikembalikan ke saldo gudang.</li>
                        <li>Dokumen yang sudah dibatalkan tidak dapat dipulihkan kembali.</li>
                    </ul>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="inputAlasanVoid" style="font-size: 0.8rem; font-weight: 700; color: #475569;">
                        Alasan Pembatalan <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="inputAlasanVoid" name="alasan_batal" class="form-control" rows="3" required placeholder="Contoh: Kesalahan input quantity karton / salah nomor lot batch..." style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem; font-size: 0.825rem; font-family: inherit; box-sizing: border-box;"></textarea>
                </div>
            </div>

            {{-- Footer Tombol Aksi --}}
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.35rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" class="btn btn-secondary" onclick="closeVoidPackagingModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Tutup
                </button>
                <button type="submit" class="btn btn-danger" style="font-size: 0.825rem; padding: 0.45rem 1rem; background: #dc2626; border: none; color: #ffffff; border-radius: 6px; cursor: pointer; font-weight: 700; box-shadow: 0 1px 2px rgba(220, 38, 38, 0.2);">
                    Ya, Batalkan Dokumen Ini
                </button>
            </div>
        </form>
    </div>
</div>
