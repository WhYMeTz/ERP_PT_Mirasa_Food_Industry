@extends('layouts.app')

@section('title', 'Koreksi & Verifikasi Pemakaian Bahan - ' . $pakai->pakai_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('keuangan.verifikasi_pemakaian.show', $pakai->pakai_id) }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Detail Dokumen
    </a>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <span>Koreksi &amp; Verifikasi Pemakaian Bahan</span>
                <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 0.8rem;">
                    {{ $pakai->pakai_no }}
                </span>
            </h1>
            <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                Koreksi langsung nomor batch, kuantitas timbangan, atau bahan produksi tanpa perlu menyuruh gudang revisi ulang.
            </p>
        </div>
        <div>
            <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.8rem; border: 1px solid #fde68a; padding: 0.4rem 0.75rem;">
                ⏳ Status Dokumen: DRAFT
            </span>
        </div>
    </div>
</div>

{{-- FORM UTAMA VERIFIKASI & UPDATE --}}
<form action="{{ route('keuangan.verifikasi_pemakaian.update', $pakai->pakai_id) }}" method="POST" id="verifikasiEditForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="action_type" id="action_type" value="save_draft">

    <div class="order-station-grid">
        {{-- KOLOM KIRI: FORMULIR UTAMA & TABEL (2.3fr) --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            {{-- KARTU 1: INFORMASI DOKUMEN & PENGELUARAN --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Tujuan Pengeluaran</strong>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.725rem;">Stasiun Verifikasi</span>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    {{-- BARIS 1: NO DOKUMEN, TANGGAL, GUDANG ASAL, TUJUAN / SPK --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="pakai_no" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Nomor Bukti Pengeluaran (BPPB)
                            </label>
                            <input type="text" id="pakai_no" value="{{ $pakai->pakai_no }}" class="form-control" style="font-family: monospace; font-weight: 700; height: 38px; border-radius: 6px; font-size: 0.85rem; background: #f8fafc;" disabled>
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor urut resmi dokumen tidak dapat diubah.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="pakai_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Tanggal Pengeluaran Fisik <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="date" id="pakai_tgl" name="pakai_tgl" value="{{ old('pakai_tgl', $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('Y-m-d') : date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Tanggal barang dikeluarkan dari gudang.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Lokasi Gudang Asal <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="gudang_id" id="gudang_id" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="onGudangChanged()">
                                <option value="">-- Pilih Lokasi Asal --</option>
                                @foreach ($gudangList as $gdg)
                                    <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $pakai->gudang_id) == $gdg->gudang_id ? 'selected' : '' }}>
                                        {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem;">Gudang fisik asal stok barang dipotong.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="tujuan_pemakaian" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Tujuan Pemakaian / Lini Kerja <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="tujuan_pemakaian" id="tujuan_pemakaian" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="updateSidebarInfo()">
                                <option value="">-- Pilih Lini Produksi / Tujuan Pengeluaran --</option>
                                @if(isset($liniList) && $liniList->isNotEmpty())
                                    @foreach($liniList->groupBy(fn($item) => $item->kategori_lini ?: 'UMUM') as $kategori => $items)
                                        <optgroup label="{{ $kategori }}">
                                            @foreach($items as $lini)
                                                <option value="{{ $lini->lini_nm }}" {{ old('tujuan_pemakaian', $pakai->tujuan_pemakaian) === $lini->lini_nm ? 'selected' : '' }}>
                                                    {{ $lini->lini_nm }} {{ $lini->keterangan ? '('.$lini->keterangan.')' : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem;">Lini kerja atau peruntukan operasional pabrik.</small>
                        </div>
                    </div>

                    {{-- BARIS 2: CATATAN TAMBAHAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                            Catatan Tambahan Verifikasi / Koreksi (Opsional)
                        </label>
                        <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt', $pakai->catatan_txt) }}" placeholder="Keterangan koreksi timbangan, instruksi supervisi, memo verifikator, dll." class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: RINCIAN BAHAN YANG DIKELUARKAN --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">2. Rincian Fisik Bahan Dikeluarkan &amp; Pilihan Batch (FIFO)</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                            💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah antar-baris layaknya Excel
                        </span>
                    </div>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                        <span style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-right: 0.25rem;">+ Tambah Cepat:</span>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('BAHAN_BAKU')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 0.3rem 0.65rem;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #d97706; margin-right: 4px;"></span>Bahan Baku
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('BAHAN_PENOLONG')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 0.3rem 0.65rem;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #0284c7; margin-right: 4px;"></span>Bumbu / Penolong
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('KEMASAN')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 0.3rem 0.65rem;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>Kemasan
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addRow('')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; background: #0284c7; border: none; padding: 0.3rem 0.75rem;">
                            + Baris Umum
                        </button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="excel-grid-table" id="itemsTable" style="width: 100%; min-width: 980px;">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="min-width: 240px;">Kategori &amp; Nama Bahan Produksi <span style="color:#ef4444;">*</span></th>
                                <th style="min-width: 260px;">Pilihan Grade &amp; Batch (FIFO) <span style="color:#ef4444;">*</span></th>
                                <th style="width: 115px; text-align: right;">Qty Keluar <span style="color:#ef4444;">*</span></th>
                                <th style="width: 65px; text-align: center;">Satuan</th>
                                <th style="width: 125px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 135px; text-align: right;">Total Biaya (HPP)</th>
                                <th style="width: 40px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Di-render via JavaScript (addRowWithItemData) --}}
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                                <td colspan="3" style="padding: 0.65rem 0.75rem; text-align: right; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Total Akumulasi Fisik &amp; HPP:
                                    <span style="margin-left: 0.65rem; text-transform: none; font-size: 0.75rem; display: inline-flex; gap: 0.35rem; vertical-align: middle;">
                                        <span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 2px 7px; border-radius: 4px; font-weight: 700;">
                                            🟢 Grade A: <strong id="footGradeAQty" style="font-family: monospace;">0,00</strong>
                                        </span>
                                        <span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; font-weight: 700;">
                                            🟡 Grade B: <strong id="footGradeBQty" style="font-family: monospace;">0,00</strong>
                                        </span>
                                    </span>
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right; color: #0284c7; font-family: monospace; font-size: 0.95rem; font-weight: 700;" id="grandTotalQty">
                                    0,00
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 0.725rem;">Subtotal:</td>
                                <td></td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right; color: #0f172a; font-family: monospace; font-size: 1.05rem; font-weight: 800;" id="grandTotalNilai">
                                    Rp 0
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div> {{-- End Kolom Kiri (.order-station-grid left) --}}

        {{-- KOLOM KANAN: STICKY ACTION SIDEBAR (1fr) --}}
        <div class="sticky-action-sidebar" style="position: sticky; top: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- KARTU SUMMARY & ACTION UTAMA --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); overflow: hidden;">
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.875rem 1.25rem;">
                    <div style="font-size: 0.725rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #94a3b8;">
                        Panel Verifikasi
                    </div>
                    <strong style="color: #ffffff; font-size: 1.05rem;">Ringkasan Biaya HPP</strong>
                </div>

                <div style="padding: 1.25rem;">
                    {{-- TOTAL NOMINAL DISPLAY BESAR --}}
                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.725rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Total Biaya Bahan (HPP):</span>
                        <div id="sideGrandTotal" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem; font-family: monospace; letter-spacing: -0.02em;">
                            Rp 0
                        </div>
                    </div>

                    {{-- STATISTIK DAMPAK KARTU STOK --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Jumlah Bahan:</span>
                            <strong style="color: #0f172a;"><span id="sideTotalItems">0</span> Baris Item</strong>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.4rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Gudang Asal:</span>
                            <strong id="sideGudangName" style="color: #0f172a; font-size: 0.8rem; max-width: 150px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</strong>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.4rem; border-top: 1px dashed #e2e8f0;">
                            <span style="color: #64748b;">Tujuan / SPK:</span>
                            <strong id="sideTujuanName" style="color: #0f172a; font-size: 0.8rem; max-width: 150px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</strong>
                        </div>

                        {{-- BREAKDOWN REKAPITULASI GRADE A & GRADE B --}}
                        <div style="padding: 0.55rem 0.65rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 5px; margin-top: 0.2rem;">
                            <div style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem; display: flex; justify-content: space-between;">
                                <span>Pemakaian per Grade:</span>
                                <span style="color: #94a3b8; font-weight: normal;">(Bahan Baku)</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding: 0.2rem 0; border-bottom: 1px dashed #f1f5f9;">
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #065f46; font-weight: 600;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                    Grade A (Super)
                                </span>
                                <strong id="sideGradeAQty" style="color: #065f46; font-family: monospace;">0,00 Kg</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding-top: 0.2rem;">
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #b45309; font-weight: 600;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                    Grade B (Standar)
                                </span>
                                <strong id="sideGradeBQty" style="color: #b45309; font-family: monospace;">0,00 Kg</strong>
                            </div>
                        </div>

                        {{-- HIGHLIGHT TOTAL QTY KELUAR --}}
                        <div style="padding: 0.55rem 0.65rem; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 5px; margin-top: 0.2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #0369a1; font-size: 0.775rem; font-weight: 700; text-transform: uppercase;">Total Qty Keluar:</span>
                                <strong style="color: #0284c7; font-size: 1.05rem; font-family: monospace;"><span id="sideTotalQty">0,00</span></strong>
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL 1: SIMPAN PERUBAHAN & LANGSUNG SETUJUI (POSTING) --}}
                    <button type="button" onclick="submitVerifikasiForm('save_and_approve')" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.925rem; font-weight: 700; background: #059669; border: 1px solid #059669; justify-content: center; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.65rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Simpan &amp; Langsung Setujui (Posting)</span>
                    </button>

                    {{-- TOMBOL 2: SIMPAN PERUBAHAN SAJA (TETAP DRAFT) --}}
                    <button type="button" onclick="submitVerifikasiForm('save_draft')" class="btn btn-secondary" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.85rem; font-weight: 600; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; justify-content: center; display: flex; align-items: center; gap: 0.4rem; cursor: pointer; margin-bottom: 0.65rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span>Simpan Perubahan Saja (Tetap DRAFT)</span>
                    </button>

                    <a href="{{ route('keuangan.verifikasi_pemakaian.show', $pakai->pakai_id) }}" class="btn btn-secondary" style="width: 100%; justify-content: center; font-size: 0.85rem; padding: 0.5rem; color: #64748b;">
                        Batal &amp; Kembali ke Rincian
                    </a>
                </div>
            </div>

            {{-- KARTU PANDUAN VERIFIKATOR --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.85rem;">Panduan Verifikator / Supervisor</strong>
                </div>
                <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span><strong>Koreksi Langsung di Tempat:</strong> Anda dapat mengganti nomor batch atau angka timbangan riil yang dimasukkan orang gudang tanpa perlu menolak dokumen.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span><strong>Simpan &amp; Langsung Setujui:</strong> Otomatis memotong saldo fisik batch dan menerbitkan Jurnal Umum (Debet 1151 WIP &amp; FG, Kredit 1150 Persediaan Bahan).</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="color: #059669; font-weight: 700;">&bull;</span>
                        <span><strong>Simpan Perubahan Saja:</strong> Menyimpan rincian baru sebagai DRAFT bila Anda masih menunggu konfirmasi lanjutan dari lantai pabrik.</span>
                    </div>
                </div>
            </div>

        </div> {{-- End Kolom Kanan (.sticky-action-sidebar) --}}
    </div> {{-- End .order-station-grid --}}
</form>

@include('keuangan.verifikasi_pemakaian.partials.modal-warning-fifo')
@endsection

@push('scripts')
<script>
    window.appConfig = {
        barangList: @json($barangList),
        initialItems: @json($initialItems),
        urlGetBatches: "{{ route('gudang.pemakaian.batches') }}",
        urlAlokasiResep: "{{ route('gudang.pemakaian.alokasi-resep') }}"
    };
</script>
<script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.js') }}"></script>
@endpush
