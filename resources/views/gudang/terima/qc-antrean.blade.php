@extends('layouts.app')

@section('title', 'Antrean Tiket QC Inbound - Gudang PT Mirasa')

@section('content')
<div style="max-width: 1320px; margin: 0 auto; padding-bottom: 2.5rem;">
    {{-- PAGE HEADER --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span style="background: #e0f2fe; color: #0284c7; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 6px; letter-spacing: 0.05em; text-transform: uppercase;">
                    Logistik Inbound Gudang
                </span>
                <span style="font-size: 0.8rem; color: #64748b;">&bull; Serah Terima Hasil Sampling QC</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0;">
                📦 Antrean Tiket QC Inbound (Bahan Siap Terima)
            </h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">
                Daftar kedatangan bahan baku yang telah lolos inspeksi sampling tim QC lapangan dan siap ditarik menjadi Bukti Penerimaan Barang (GRN).
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <a href="{{ route('gudang.terima.index') }}" class="btn btn-secondary" style="border-radius: 8px;">
                &larr; Riwayat Penerimaan (GRN)
            </a>
            <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary" style="border-radius: 8px;">
                <span>+ Buat GRN Manual</span>
            </a>
        </div>
    </div>

    {{-- KARTU METRIK OPERASIONAL GUDANG --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        {{-- METRIK 1: SIAP TERIMA GUDANG --}}
        <div class="card" style="margin: 0; padding: 1.15rem 1.35rem; border-radius: 12px; border-left: 5px solid #f59e0b; background: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                        Siap Ditarik ke GRN
                    </span>
                    <div style="font-size: 1.75rem; font-weight: 900; color: #b45309; margin-top: 0.25rem; line-height: 1;">
                        {{ number_format($countSiap, 0, ',', '.') }}
                    </div>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    ⏳
                </div>
            </div>
            <span style="font-size: 0.75rem; color: #92400e; margin-top: 0.5rem; display: block;">
                Truk sudah lolos QC &amp; siap bongkar muat
            </span>
        </div>

        {{-- METRIK 2: SELESAI DITERIMA (GRN) --}}
        <div class="card" style="margin: 0; padding: 1.15rem 1.35rem; border-radius: 12px; border-left: 5px solid #10b981; background: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                        Sudah Diterima Gudang
                    </span>
                    <div style="font-size: 1.75rem; font-weight: 900; color: #047857; margin-top: 0.25rem; line-height: 1;">
                        {{ number_format($countSelesai, 0, ',', '.') }}
                    </div>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    ✅
                </div>
            </div>
            <span style="font-size: 0.75rem; color: #065f46; margin-top: 0.5rem; display: block;">
                Telah terbit nomor GRN &amp; masuk stok persediaan
            </span>
        </div>

        {{-- METRIK 3: REJECT / DITOLAK TOTAL --}}
        <div class="card" style="margin: 0; padding: 1.15rem 1.35rem; border-radius: 12px; border-left: 5px solid #ef4444; background: #ffffff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                        Ditolak Total (Reject)
                    </span>
                    <div style="font-size: 1.75rem; font-weight: 900; color: #b91c1c; margin-top: 0.25rem; line-height: 1;">
                        {{ number_format($countReject, 0, ',', '.') }}
                    </div>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    ❌
                </div>
            </div>
            <span style="font-size: 0.75rem; color: #991b1b; margin-top: 0.5rem; display: block;">
                Barang tidak memenuhi syarat mutu pabrik
            </span>
        </div>
    </div>

    {{-- FILTER FORM CARD --}}
    <div class="card" style="margin-bottom: 1.5rem; border-radius: 12px; padding: 1.25rem; background: #ffffff;">
        <form action="{{ route('gudang.qc.antrean') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Pencarian (No QC, SJ, Truk, Sopir)</label>
                <input type="text" name="search" class="form-control" placeholder="Cari tiket atau plat truk..." value="{{ $filters['search'] ?? '' }}">
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Status Antrean Gudang</label>
                <select name="status_qc" class="form-control">
                    <option value="SIAP_GUDANG" {{ ($filters['status_qc'] ?? '') === 'SIAP_GUDANG' ? 'selected' : '' }}>⏳ Siap Terima di Gudang (Pending GRN)</option>
                    <option value="DITERIMA_GUDANG" {{ ($filters['status_qc'] ?? '') === 'DITERIMA_GUDANG' ? 'selected' : '' }}>✅ Selesai Diterima (Sudah Ada GRN)</option>
                    <option value="DITOLAK_TOTAL" {{ ($filters['status_qc'] ?? '') === 'DITOLAK_TOTAL' ? 'selected' : '' }}>❌ Ditolak QC (Reject Total)</option>
                    <option value="ALL" {{ empty($filters['status_qc']) ? 'selected' : '' }}>-- Semua Status --</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Komoditas Masuk</label>
                <select name="kategori_barang" class="form-control">
                    <option value="">-- Semua Komoditas --</option>
                    <option value="SINGKONG" {{ ($filters['kategori_barang'] ?? '') === 'SINGKONG' ? 'selected' : '' }}>🥔 Singkong</option>
                    <option value="MINYAK" {{ ($filters['kategori_barang'] ?? '') === 'MINYAK' ? 'selected' : '' }}>🛢️ Minyak Goreng</option>
                    <option value="PLASTIK" {{ ($filters['kategori_barang'] ?? '') === 'PLASTIK' ? 'selected' : '' }}>🛍️ Plastik Kemasan</option>
                    <option value="KARTON" {{ ($filters['kategori_barang'] ?? '') === 'KARTON' ? 'selected' : '' }}>📦 Karton Box</option>
                    <option value="MSG" {{ ($filters['kategori_barang'] ?? '') === 'MSG' ? 'selected' : '' }}>🧂 MSG</option>
                    <option value="GARAM" {{ ($filters['kategori_barang'] ?? '') === 'GARAM' ? 'selected' : '' }}>🧂 Garam</option>
                    <option value="PERENYAH" {{ ($filters['kategori_barang'] ?? '') === 'PERENYAH' ? 'selected' : '' }}>✨ Perenyah</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700;">Mitra Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">-- Semua Supplier --</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $s->supplier_id ? 'selected' : '' }}>
                            {{ $s->supplier_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; border-radius: 8px;">
                    Filter
                </button>
                <a href="{{ route('gudang.qc.antrean') }}" class="btn btn-secondary" style="border-radius: 8px;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABEL OPERASIONAL GUDANG DESKTOP --}}
    <div class="card" style="border-radius: 12px; overflow: hidden; background: #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 1080px; font-size: 0.85rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.8rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em;">
                        <th style="padding: 0.9rem 1rem; text-align: left;">No. Tiket QC &amp; Waktu</th>
                        <th style="padding: 0.9rem 1rem; text-align: left;">Komoditas</th>
                        <th style="padding: 0.9rem 1rem; text-align: left;">Supplier &amp; PO</th>
                        <th style="padding: 0.9rem 1rem; text-align: left;">Armada / Sopir</th>
                        <th style="padding: 0.9rem 1rem; text-align: right;">Gross (kg)</th>
                        <th style="padding: 0.9rem 1rem; text-align: right;">Refraksi</th>
                        <th style="padding: 0.9rem 1rem; text-align: right;">Reject</th>
                        <th style="padding: 0.9rem 1rem; text-align: right; color: #047857;">Netto Lolos (kg)</th>
                        <th style="padding: 0.9rem 1rem; text-align: center;">Status Mutu</th>
                        <th style="padding: 0.9rem 1rem; text-align: center;">Tindakan Penerimaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspeksiList as $qc)
                        @php
                            $totalGross = $qc->details->sum('qty_timbang_gross');
                            $totalRefraksi = $qc->details->sum('qty_refraksi');
                            $totalReject = $qc->details->sum('qty_reject');
                            $totalNetto = $qc->details->sum('qty_netto_lolos');
                            $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 800; color: #0284c7; font-size: 0.925rem;">
                                    {{ $qc->qc_no }}
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                    📅 {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} &bull; QC: {{ $qc->petugas_qc_nama }}
                                </div>
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <span style="background: #e0f2fe; color: #0284c7; font-size: 0.725rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 12px; white-space: nowrap;">
                                    @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                                    {{ $kat }}
                                </span>
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $qc->supplier?->supplier_nm ?? 'Supplier Langsung' }}
                                </div>
                                @if ($qc->po)
                                    <div style="font-size: 0.725rem; color: #0284c7; font-weight: 600;">
                                        PO: {{ $qc->po->po_no }}
                                    </div>
                                @else
                                    <div style="font-size: 0.7rem; color: #94a3b8;">(Tanpa PO)</div>
                                @endif
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 700; color: #334155;">{{ $qc->plat_nomor_truk ?: '-' }}</div>
                                <div style="font-size: 0.725rem; color: #64748b;">Sopir: {{ $qc->sopir_nama ?: '-' }}</div>
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 700;">
                                {{ number_format($totalGross, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #d97706; font-weight: 600;">
                                {{ number_format($totalRefraksi, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: #dc2626; font-weight: 600;">
                                {{ number_format($totalReject, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; font-weight: 900; color: #059669; font-size: 0.95rem;">
                                {{ number_format($totalNetto, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center;">
                                @if ($qc->status_qc === 'SIAP_GUDANG')
                                    <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.725rem; font-weight: 800; padding: 0.25rem 0.55rem; border-radius: 12px; white-space: nowrap;">
                                        ⏳ Siap Gudang
                                    </span>
                                @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                                    <span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.725rem; font-weight: 800; padding: 0.25rem 0.55rem; border-radius: 12px; white-space: nowrap;">
                                        ✅ Masuk Gudang
                                    </span>
                                @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                                    <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.725rem; font-weight: 800; padding: 0.25rem 0.55rem; border-radius: 12px; white-space: nowrap;">
                                        ❌ Reject Total
                                    </span>
                                @else
                                    <span class="badge">{{ $qc->status_qc }}</span>
                                @endif

                                @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
                                    <div style="font-size: 0.675rem; color: #854d0e; background: #fef9c3; padding: 0.1rem 0.35rem; border-radius: 4px; margin-top: 3px; display: inline-block;">
                                        ⏳ Uji Fryer Lab
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center;">
                                <div style="display: flex; gap: 0.4rem; align-items: center; justify-content: center; flex-wrap: wrap;">
                                    {{-- TOMBOL UTAMA: TARIK KE PENERIMAAN GUDANG (GRN) --}}
                                    @if ($qc->status_qc === 'SIAP_GUDANG')
                                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="btn btn-sm" style="background: #10b981; color: #ffffff; font-weight: 800; border-radius: 6px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);" title="Tarik ke Penerimaan Barang &amp; Input No Batch">
                                            <span>📦 Tarik ke GRN</span>
                                        </a>
                                    @elseif ($qc->terima)
                                        <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" class="btn btn-sm" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 800; border-radius: 6px;" title="Lihat Dokumen Penerimaan Barang">
                                            <span>🔗 GRN #{{ $qc->terima->terima_no }}</span>
                                        </a>
                                    @endif

                                    {{-- TOMBOL CETAK FORMULIR HACCP A4 RESMI UNTUK ARSIP GUDANG --}}
                                    <a href="{{ route('gudang.qc.haccp_cetak', $qc->qc_id) }}" target="_blank" class="btn btn-sm btn-secondary" style="border-radius: 6px; font-weight: 700;" title="Cetak Lembar Checklist Mutu HACCP Resmi (A4)">
                                        <span>🖨️ Cetak HACCP</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding: 3.5rem 1.5rem; text-align: center; color: #64748b;">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📦</div>
                                <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Tidak Ada Antrean Tiket QC</div>
                                <div style="font-size: 0.85rem; margin-top: 0.25rem;">
                                    Semua tiket QC yang lolos telah diproses ke Penerimaan Barang (GRN) atau belum ada kiriman baru dari tim QC lapangan.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inspeksiList->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
                {{ $inspeksiList->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
