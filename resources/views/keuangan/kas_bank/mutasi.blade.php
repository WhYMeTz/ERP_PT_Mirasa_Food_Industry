@extends('layouts.app')

@section('title', 'Buku Mutasi Kas & Rekening Bank - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_bank/kas-bank-mutasi.css') }}">
@endpush

@section('content')
{{-- Page Header --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>Buku Mutasi Kas &amp; Rekening Bank</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Histori arus kas masuk, kas keluar, pelunasan hutang, transfer internal dan opname saldo berjalan.
        </p>
    </div>
    <div>
        <a href="{{ route('keuangan.kas_bank.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Master Rekening</span>
        </a>
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL (INFORMATIF & SERAGAM DENGAN PO & TERIMA BARANG) --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    {{-- TOTAL ARUS MASUK --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #059669;">
                    Total Arus Masuk (Kredit)
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #059669; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    + Rp {{ number_format($totalMasuk, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total penerimaan pada filter aktif
        </div>
    </div>

    {{-- TOTAL ARUS KELUAR --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #dc2626;">
                    Total Arus Keluar (Debit)
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: #dc2626; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    - Rp {{ number_format($totalKeluar, 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Total pengeluaran / pelunasan hutang
        </div>
    </div>

    {{-- NET ARUS KAS (NETFLOW) --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $netFlow >= 0 ? '#059669' : '#dc2626' }};">
                    Net Arus Kas (Netflow)
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: {{ $netFlow >= 0 ? '#059669' : '#dc2626' }}; margin-top: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                    {{ $netFlow >= 0 ? '+' : '-' }} Rp {{ number_format(abs($netFlow), 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $netFlow >= 0 ? '#d1fae5' : '#fee2e2' }}; display: flex; align-items: center; justify-content: center; color: {{ $netFlow >= 0 ? '#059669' : '#dc2626' }};">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Selisih neto periode yang dipilih
        </div>
    </div>

    {{-- TOTAL DOKUMEN MUTASI --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                    Total Baris Mutasi
                </span>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($mutasiList->total()) }} Baris
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #475569;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Histori mutasi terfilter di buku kas
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card" style="padding: 1rem 1.25rem; background: #ffffff; margin-bottom: 1.25rem;">
    <form action="{{ route('keuangan.kas_bank.mutasi') }}" method="GET" id="filterMutasiForm">
        <div class="kb-filter-grid">
            <div class="kb-filter-group">
                <label class="kb-filter-label">Rekening Kas / Bank</label>
                <select name="kas_bank_id" class="kb-filter-select auto-submit-filter">
                    <option value="">-- Semua Rekening &amp; Kas --</option>
                    @foreach ($kasBanks as $item)
                        <option value="{{ $item->kas_bank_id }}" {{ ($filters['kas_bank_id'] ?? '') == $item->kas_bank_id ? 'selected' : '' }}>
                            [{{ $item->kode_akun }}] {{ $item->nama_akun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="kb-filter-group">
                <label class="kb-filter-label">Arah Mutasi</label>
                <select name="tipe_mutasi" class="kb-filter-select auto-submit-filter">
                    <option value="">Semua Arah</option>
                    <option value="MASUK" {{ ($filters['tipe_mutasi'] ?? '') === 'MASUK' ? 'selected' : '' }}>Uang Masuk (Kredit)</option>
                    <option value="KELUAR" {{ ($filters['tipe_mutasi'] ?? '') === 'KELUAR' ? 'selected' : '' }}>Uang Keluar (Debit)</option>
                </select>
            </div>

            <div class="kb-filter-group">
                <label class="kb-filter-label">Jenis Transaksi</label>
                <select name="jenis_transaksi" class="kb-filter-select auto-submit-filter">
                    <option value="">Semua Jenis</option>
                    <option value="BAYAR_HUTANG" {{ ($filters['jenis_transaksi'] ?? '') === 'BAYAR_HUTANG' ? 'selected' : '' }}>Pelunasan Hutang (BKK)</option>
                    <option value="MUTASI_KAS" {{ ($filters['jenis_transaksi'] ?? '') === 'MUTASI_KAS' ? 'selected' : '' }}>Transfer Antar Kas/Bank</option>
                    <option value="PENYESUAIAN" {{ ($filters['jenis_transaksi'] ?? '') === 'PENYESUAIAN' ? 'selected' : '' }}>Penyesuaian / Opname</option>
                    <option value="SALDO_AWAL" {{ ($filters['jenis_transaksi'] ?? '') === 'SALDO_AWAL' ? 'selected' : '' }}>Saldo Awal</option>
                </select>
            </div>

            <div class="kb-filter-group">
                <label class="kb-filter-label">Mulai Tanggal</label>
                <input type="date" name="tgl_mulai" class="kb-filter-input" value="{{ $filters['tgl_mulai'] ?? '' }}">
            </div>

            <div class="kb-filter-group">
                <label class="kb-filter-label">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" class="kb-filter-input" value="{{ $filters['tgl_selesai'] ?? '' }}">
            </div>

            <div class="kb-filter-actions">
                <button type="submit" class="kb-btn kb-btn-filter">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>
                @if (!empty($filters['kas_bank_id']) || !empty($filters['tipe_mutasi']) || !empty($filters['jenis_transaksi']) || !empty($filters['tgl_mulai']) || !empty($filters['tgl_selesai']))
                    <a href="{{ route('keuangan.kas_bank.mutasi') }}" class="kb-btn" title="Reset Filter">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Ledger Data Table --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>Buku Riwayat Mutasi Kas &amp; Bank</span>
        </h3>
        <span style="color: #64748b; font-size: 0.85rem;">
            Total Mutasi: <strong style="color: #0f172a;">{{ $mutasiList->total() }}</strong> transaksi
        </span>
    </div>

    <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em;">
                    <th style="width: 45px; text-align: center; padding: 0.85rem 0.5rem;">No</th>
                    <th style="min-width: 100px; padding: 0.85rem 0.75rem;">Tanggal</th>
                    <th style="min-width: 160px; padding: 0.85rem 0.75rem;">Rekening / Kas</th>
                    <th style="min-width: 100px; padding: 0.85rem 0.75rem;">Arah</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem;">Kategori</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem;">No. Referensi</th>
                    <th style="min-width: 180px; padding: 0.85rem 0.75rem;">Keterangan</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem; text-align: right;">Saldo Sebelum</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem; text-align: right;">Mutasi (+/-)</th>
                    <th style="min-width: 120px; padding: 0.85rem 0.75rem; text-align: right;">Saldo Sesudah</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.8125rem; color: #1e293b;">
                @forelse ($mutasiList as $index => $item)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 0.75rem 0.5rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                            {{ $mutasiList->firstItem() + $index }}
                        </td>
                        <td style="padding: 0.75rem 0.75rem; white-space: nowrap; font-weight: 600;">
                            {{ date('d/m/Y', strtotime($item->tgl_mutasi)) }}
                        </td>
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0f172a;">{{ $item->kasBank->nama_akun ?? '-' }}</div>
                            <div style="font-size: 0.72rem; color: #64748b; font-family: monospace;">{{ $item->kasBank->kode_akun ?? '' }}</div>
                        </td>
                        <td style="padding: 0.75rem 0.75rem;">
                            @if ($item->tipe_mutasi === 'MASUK')
                                <span class="kb-badge-mutasi kb-mutasi-masuk">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                    <span>MASUK</span>
                                </span>
                            @else
                                <span class="kb-badge-mutasi kb-mutasi-keluar">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                    <span>KELUAR</span>
                                </span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.75rem;">
                            <span class="kb-badge-transaksi">{{ $item->jenis_transaksi }}</span>
                        </td>
                        <td style="padding: 0.75rem 0.75rem; font-family: monospace; font-size: 0.78rem; font-weight: 700; color: #0284c7;">
                            {{ $item->no_referensi ?: '-' }}
                        </td>
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="max-width: 280px; line-height: 1.4; color: #334155;">
                                {{ $item->keterangan ?: '-' }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.15rem;">
                                User: {{ $item->created_by ?: 'SYSTEM' }}
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.75rem; text-align: right;" class="kb-amount-balance">
                            Rp {{ number_format($item->saldo_sebelum, 0, ',', '.') }}
                        </td>
                        <td style="padding: 0.75rem 0.75rem; text-align: right;">
                            @if ($item->tipe_mutasi === 'MASUK')
                                <span class="kb-amount-masuk">+Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                            @else
                                <span class="kb-amount-keluar">-Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 0.75rem; text-align: right;" class="kb-amount-balance">
                            Rp {{ number_format($item->saldo_sesudah, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: #64748b; padding: 3rem;">
                            Tidak ada data mutasi kas &amp; bank yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($mutasiList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <span style="font-size: 0.8rem; color: #64748b;">
                Menampilkan {{ $mutasiList->firstItem() }} - {{ $mutasiList->lastItem() }} dari {{ $mutasiList->total() }} catatan mutasi
            </span>
            <div>
                {{ $mutasiList->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_bank/kas-bank-mutasi.js') }}"></script>
@endpush
