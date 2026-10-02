@extends('layouts.qc-mobile')

@php
    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
    $firstDetail = $qc->details->first();
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalRefraksi = $qc->details->sum('qty_refraksi');
    $totalReject = $qc->details->sum('qty_reject');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();

    $revisi = '1';
    $tglTerbit = '11-09-2023';

    if ($kat === 'MINYAK') {
        $docNo = 'MFI/HACCP-04/FRM-03/029/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Minyak Goreng';
    } elseif ($kat === 'PLASTIK') {
        $docNo = 'MFI/HACCP-04/FRM-03/030/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Plastik';
    } elseif ($kat === 'KARTON') {
        $docNo = 'MFI/HACCP-04/FRM-03/031/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Karton';
    } elseif ($kat === 'MSG') {
        $docNo = 'MFI/HACCP-04/FRM-03/032/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan MSG';
    } elseif ($kat === 'GARAM') {
        $docNo = 'MFI/HACCP-04/FRM-03/033/VIII/2021';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Garam';
    } elseif ($kat === 'PERENYAH') {
        $docNo = 'MFI/HACCP-04/FRM-03/063/IX/2023';
        $docTitle = 'Cheklist Pemeriksaan Kedatangan Perenyah';
        $revisi = '0';
        $tglTerbit = '14-09-2023';
    } else {
        $docNo = 'MFI/HACCP-04/FRM-03/048/VIII/2021';
        $docTitle = 'Cheklist Standar Kebeterimaan Bahan Baku';
    }
@endphp

@section('title', 'Dokumen QC: ' . $qc->qc_no . ' - PT Mirasa')

@section('content')
<div style="max-width: 950px; margin: 0 auto; padding-bottom: 3.5rem;">
    {{-- TOP ACTION TOOLBAR (NO PRINT) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('qc.inbound.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                &larr; Riwayat Tiket QC
            </a>
            <span style="font-size: 0.85rem; color: #64748b;">|</span>
            <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">Tiket #{{ $qc->qc_no }}</span>
            <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0284c7; padding: 0.2rem 0.55rem; border-radius: 12px;">
                {{ $kat }}
            </span>
            @if ($qc->terima)
                <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" style="font-size: 0.75rem; font-weight: 800; background: #dcfce7; color: #15803d; padding: 0.2rem 0.55rem; border-radius: 12px; text-decoration: none; border: 1px solid #86efac;">
                    📦 GRN #{{ $qc->terima->terima_no }}
                </a>
            @endif
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 700;">
                📱 Ringkas Mobile
            </a>

            @if (Auth::user()?->canEditQc() && !$isLocked)
                <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="btn btn-sm" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 700; border-radius: 8px;">
                    ✏️ Edit / Koreksi
                </a>
            @endif

            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 700;">
                🖨️ Cetak Dokumen HACCP (A4)
            </button>
        </div>
    </div>

    {{-- KOTAK UPDATE SUSULAN UJI GORENG LAB (KHUSUS SINGKONG JIKA MENUNGGU LAB) --}}
    @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
        <div class="no-print" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fde68a; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.08);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.4rem; font-weight: 800; font-size: 0.95rem; color: #b45309;">
                        <span>⏳</span> <span>Pengujian I Selesai &bull; Hasil Uji Goreng (Lab) Masih Tertunda</span>
                    </div>
                    <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: #78350f;">
                        Truk sudah lolos tahap sampling. Masukkan hasil uji penggorengan lab di bawah ini jika sampel goreng sudah selesai diuji di fryer.
                    </p>
                </div>
                <button type="button" onclick="toggleUjiGorengForm()" class="btn btn-sm" style="background: #b45309; color: #ffffff; font-weight: 700; border-radius: 8px; border: none; padding: 0.45rem 0.9rem;">
                    🍟 Input Hasil Uji Goreng Sekarang
                </button>
            </div>

            <div id="ujiGorengFormBox" style="display: none; margin-top: 1.25rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
                <form action="{{ route('qc.inbound.update_uji_goreng', $qc->qc_id) }}" method="POST">
                    @csrf
                    <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.75rem;">
                        Laboratorium QC &bull; Formulir Pengujian II (Uji Goreng Fryer)
                    </div>

                    @foreach ($qc->details as $d)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="font-weight: 800; font-size: 0.85rem; color: #0284c7; margin-bottom: 0.75rem;">
                                🍟 {{ $d->barang?->barang_nm ?? 'Singkong' }} (Grade: {{ $d->grade_cd }})
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">RASA (Standar: Tdk Pahit)</label>
                                    <select name="items[{{ $d->qcdtl_id }}][fryer_rasa]" class="form-control" style="font-size: 0.825rem; font-weight: 600;">
                                        <option value="TIDAK_PAHIT" {{ ($d->fryer_rasa ?? 'TIDAK_PAHIT') === 'TIDAK_PAHIT' ? 'selected' : '' }}>✅ Tidak Pahit</option>
                                        <option value="PAHIT" {{ ($d->fryer_rasa ?? '') === 'PAHIT' ? 'selected' : '' }}>❌ Pahit / Sianida</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">TEKSTUR (Standar: Renyah)</label>
                                    <select name="items[{{ $d->qcdtl_id }}][fryer_tekstur]" class="form-control" style="font-size: 0.825rem; font-weight: 600;">
                                        <option value="RENYAH" {{ ($d->fryer_tekstur ?? 'RENYAH') === 'RENYAH' ? 'selected' : '' }}>✅ Renyah</option>
                                        <option value="ALOT" {{ ($d->fryer_tekstur ?? '') === 'ALOT' ? 'selected' : '' }}>❌ Alot / Keras</option>
                                        <option value="LEMBEK" {{ ($d->fryer_tekstur ?? '') === 'LEMBEK' ? 'selected' : '' }}>⚠️ Lembek</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" style="font-size: 0.775rem; font-weight: 700;">PENAMPAKAN</label>
                                    <select name="items[{{ $d->qcdtl_id }}][fryer_penampakan]" class="form-control" style="font-size: 0.825rem; font-weight: 600;">
                                        <option value="TIDAK_OILSOAKED" {{ ($d->fryer_penampakan ?? 'TIDAK_OILSOAKED') === 'TIDAK_OILSOAKED' ? 'selected' : '' }}>✅ Tidak Oilsoaked</option>
                                        <option value="OILSOAKED" {{ ($d->fryer_penampakan ?? '') === 'OILSOAKED' ? 'selected' : '' }}>❌ Oilsoaked</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                        <button type="button" onclick="toggleUjiGorengForm()" class="btn btn-secondary btn-sm" style="border-radius: 8px;">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px; font-weight: 800; background: #059669; border-color: #059669;">
                            💾 Simpan Hasil Uji Goreng Lab
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- WRAPPER RESPONSIVE AGAR TABEL DOKUMEN DAPAT DI-PAN DI HP TANPA HANCUR --}}
    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 1rem;">
        @if ($kat === 'SINGKONG')
            @include('gudang.qc.partials.doc-singkong')
        @elseif ($kat === 'MINYAK')
            @include('gudang.qc.partials.doc-minyak')
        @elseif ($kat === 'PLASTIK')
            @include('gudang.qc.partials.doc-plastik')
        @elseif ($kat === 'KARTON')
            @include('gudang.qc.partials.doc-karton')
        @else
            @include('gudang.qc.partials.doc-bahan-penolong')
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleUjiGorengForm() {
        const box = document.getElementById('ujiGorengFormBox');
        if (box) {
            box.style.display = (box.style.display === 'none' || !box.style.display) ? 'block' : 'none';
            if (box.style.display === 'block') {
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }
</script>
@endpush
