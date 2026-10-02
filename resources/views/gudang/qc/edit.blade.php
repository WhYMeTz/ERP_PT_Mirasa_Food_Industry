@extends((Auth::user()?->isQc() && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isGudang() && request('view') !== 'desktop') ? 'layouts.qc-mobile' : 'layouts.app')

@section('title', 'Koreksi Dokumen QC (No. ' . $qc->qc_no . ') - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-form.css') }}">
@endpush

@php
    $kat = strtoupper($qc->kategori_barang ?? 'SINGKONG');
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;
    $totalGross = $qc->details->sum('qty_timbang_gross');
    $totalNetto = $qc->details->sum('qty_netto_lolos');
    $totalReject = $qc->details->sum('qty_reject');
    $isEdit = true;

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

@section('content')
<div style="max-width: 950px; margin: 0 auto; padding-bottom: 4rem;">
    {{-- Header Banner Edit Dokumen HACCP --}}
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0f172a 100%); border-radius: 12px; padding: 1.25rem 1.5rem; color: #ffffff; margin-bottom: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.35rem; flex-wrap: wrap;">
                    <span style="background: rgba(255,255,255,0.2); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 20px;">
                        🔬 PT. MIRASA FOOD INDUSTRY &bull; HACCP-04
                    </span>
                    <span style="background: #f59e0b; color: #ffffff; font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 20px;">
                        ✏️ Mode Edit Lembar Dokumen Excel Asli
                    </span>
                </div>
                <h1 style="font-size: 1.35rem; font-weight: 900; margin: 0; line-height: 1.25;">
                    Edit Dokumen QC: {{ $qc->qc_no }}
                </h1>
                <p style="font-size: 0.825rem; margin: 0.25rem 0 0; opacity: 0.9;">
                    Anda dapat mengedit seluruh kotak isian, checklist, dan nilai parameter langsung pada lembar dokumen Excel di bawah ini.
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.9); color: #0284c7; border-radius: 8px; font-weight: 700;">
                    &larr; Batal &amp; Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- ALERT OVERRIDE SUPER ADMIN JIKA SUDAH DITARIK KE GRN --}}
    @if ($qc->terima)
        <div style="background: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 10px; padding: 0.9rem 1.25rem; color: #92400e; font-size: 0.825rem; line-height: 1.5; margin-bottom: 1.25rem;">
            <div style="font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.45rem; color: #b45309; margin-bottom: 0.2rem;">
                <span>⚡</span> <span>MODE OVERRIDE SUPER ADMINISTRATOR</span>
            </div>
            <div>
                Tiket QC ini sudah ditarik ke Penerimaan Gudang (GRN <strong>#{{ $qc->terima->terima_no }}</strong>).
                Perubahan kuantitas atau parameter pada dokumen ini <strong>akan otomatis menyelaraskan (cascade update) data Penerimaan Gudang &amp; Batch Stok terkait</strong>.
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-error" style="margin-bottom: 1.25rem; border-radius: 10px; background: #fef2f2; border: 1.5px solid #fca5a5; padding: 0.9rem 1.25rem; color: #991b1b;">
            <div style="font-weight: 800; margin-bottom: 0.35rem;">⚠️ Harap periksa isian formulir:</div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.825rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM EDIT DOKUMEN INTERAKTIF --}}
    <form action="{{ route('qc.inbound.update', $qc->qc_id) }}" method="POST" id="qcEditDocForm">
        @csrf
        @method('PUT')
        
        {{-- Hidden Fields Dasar --}}
        <input type="hidden" name="supplier_id" value="{{ old('supplier_id', $qc->supplier_id) }}">
        <input type="hidden" name="gudang_id" value="{{ old('gudang_id', $qc->gudang_id) }}">
        <input type="hidden" name="po_id" value="{{ old('po_id', $qc->po_id) }}">
        <input type="hidden" name="kategori_barang" value="{{ old('kategori_barang', $qc->kategori_barang ?? 'SINGKONG') }}">
        <input type="hidden" name="status_uji_goreng" value="{{ old('status_uji_goreng', $qc->status_uji_goreng ?? 'SELESAI') }}">

        {{-- Hidden Detail Row Keys --}}
        <input type="hidden" name="items[{{ $qcdtlId }}][qcdtl_id]" value="{{ $qcdtlId }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][barang_id]" value="{{ old('items.'.$qcdtlId.'.barang_id', $firstDetail?->barang_id) }}">
        <input type="hidden" name="items[{{ $qcdtlId }}][podtl_id]" value="{{ $firstDetail?->podtl_id }}">

        {{-- PENGATURAN KEMITRAAN CEPAT (OPSIONAL) --}}
        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">
                    Mitra Supplier (Pengirim):
                </label>
                <select name="supplier_id" class="form-control" style="font-size: 0.825rem; padding: 0.35rem 0.6rem; height: auto;">
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->supplier_id }}" {{ old('supplier_id', $qc->supplier_id) == $s->supplier_id ? 'selected' : '' }}>
                            {{ $s->supplier_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 220px;">
                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">
                    Gudang Bongkar:
                </label>
                <select name="gudang_id" class="form-control" style="font-size: 0.825rem; padding: 0.35rem 0.6rem; height: auto;">
                    @foreach ($gudangs as $g)
                        <option value="{{ $g->gudang_id }}" {{ old('gudang_id', $qc->gudang_id) == $g->gudang_id ? 'selected' : '' }}>
                            {{ $g->gudang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- LEMBAR DOKUMEN EXCEL INTERAKTIF SESUAI KOMODITAS --}}
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 1rem;">
            @if ($kat === 'SINGKONG')
                @include('gudang.qc.partials.doc-singkong', ['isEdit' => true])
            @elseif ($kat === 'MINYAK')
                @include('gudang.qc.partials.doc-minyak', ['isEdit' => true])
            @elseif ($kat === 'PLASTIK')
                @include('gudang.qc.partials.doc-plastik', ['isEdit' => true])
            @elseif ($kat === 'KARTON')
                @include('gudang.qc.partials.doc-karton', ['isEdit' => true])
            @else
                @include('gudang.qc.partials.doc-bahan-penolong', ['isEdit' => true])
            @endif
        </div>

        {{-- FLOATING BOTTOM BAR SIMPAN PERUBAHAN --}}
        <div style="position: sticky; bottom: 15px; z-index: 100; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); padding: 0.85rem 1.5rem; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); display: flex; justify-content: space-between; align-items: center; gap: 1rem; color: #ffffff;">
            <div style="font-size: 0.825rem;">
                <span style="color: #38bdf8; font-weight: 800;">📝 Mode Koreksi Dokumen Aktif</span>
                <div style="opacity: 0.8; font-size: 0.75rem;">Pastikan seluruh sel parameter dan kesimpulan sudah sesuai standar</div>
            </div>
            <div style="display: flex; gap: 0.65rem; align-items: center;">
                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.15); color: #ffffff; border: none; font-weight: 700; border-radius: 8px;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="background: #0284c7; border: none; font-weight: 800; padding: 0.6rem 1.4rem; border-radius: 8px; font-size: 0.9rem; cursor: pointer; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);">
                    💾 Simpan Perubahan Dokumen QC
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
