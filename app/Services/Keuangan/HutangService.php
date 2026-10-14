<?php

namespace App\Services\Keuangan;

use App\Models\Gudang\DatTerimaHdr;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\Keuangan\DatHutangDtl;
use App\Models\MasterData\MstSupplier;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\MasterData\MstTarifProduksi;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class HutangService
{
    /**
     * Dapatkan daftar tagihan hutang terpaginasi dengan filter cerdas
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatHutangHdr::with(['supplier.jenisSupplier', 'gudang', 'details.barang'])
            ->where('deleted_st', false);

        if (!empty($filters['status_pembayaran'])) {
            if ($filters['status_pembayaran'] === 'JATUH_TEMPO') {
                $query->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
                      ->where('tgl_jatuh_tempo', '<', now()->startOfDay());
            } else {
                $query->where('status_pembayaran', $filters['status_pembayaran']);
            }
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['tipe_hutang'])) {
            $query->where('tipe_hutang', $filters['tipe_hutang']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('hutang_no', 'like', "%{$search}%")
                  ->orWhere('no_faktur_supplier', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('supplier_nm', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->where('tgl_tagihan', '>=', $filters['tgl_mulai']);
        }
        if (!empty($filters['tgl_selesai'])) {
            $query->where('tgl_tagihan', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('tgl_jatuh_tempo', 'asc')
            ->orderBy('hutang_id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ringkasan Metrik Operasional Keuangan (AP Summary)
     */
    public function getSummaryMetrics(): array
    {
        $base = DatHutangHdr::where('deleted_st', false);

        $totalHutangAktif = (clone $base)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->sum('sisa_hutang');

        $totalJatuhTempo = (clone $base)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->where('tgl_jatuh_tempo', '<', now()->startOfDay())
            ->sum('sisa_hutang');

        $countJatuhTempo = (clone $base)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->where('tgl_jatuh_tempo', '<', now()->startOfDay())
            ->count();

        $countBelumLunas = (clone $base)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->count();

        $totalBayarBulanIni = DB::table('dat_bayar_hutang_hdr')
            ->where('deleted_st', false)
            ->where('status_cd', 'POSTED')
            ->whereBetween('tgl_bayar', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_bayar');

        return [
            'total_hutang_aktif'     => (float) $totalHutangAktif,
            'total_jatuh_tempo'      => (float) $totalJatuhTempo,
            'count_jatuh_tempo'      => $countJatuhTempo,
            'count_belum_lunas'      => $countBelumLunas,
            'total_bayar_bulan_ini'  => (float) $totalBayarBulanIni,
        ];
    }

    /**
     * Generate Nomor Hutang Baru (AP-YYYYMM-XXXX)
     */
    public function generateHutangNo(): string
    {
        $prefix = 'AP-' . date('Ym') . '-';
        $last = DatHutangHdr::where('hutang_no', 'like', $prefix . '%')
            ->orderBy('hutang_no', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->hutang_no, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    /**
     * Hitung rekap jumlah Penerimaan Barang (GRN) yang belum ditagihkan per supplier
     */
    public function getUnbilledSupplierSummary(): array
    {
        $billedTerimaIds = DatHutangDtl::where('deleted_st', false)
            ->whereNotNull('terima_id')
            ->pluck('terima_id')
            ->unique()
            ->toArray();

        return DatTerimaHdr::where('deleted_st', false)
            ->where('status_cd', 'COMPLETED')
            ->whereNotIn('terima_id', $billedTerimaIds)
            ->selectRaw('supplier_id, COUNT(*) as total_grn')
            ->groupBy('supplier_id')
            ->pluck('total_grn', 'supplier_id')
            ->toArray();
    }

    /**
     * Ambil daftar Penerimaan Barang (GRN) yang belum pernah ditagihkan ke Hutang
     */
    public function getUnbilledGrnList(?int $supplierId = null): array
    {
        // Ambil terima_id yang sudah ada di dat_hutang_dtl aktif
        $billedTerimaIds = DatHutangDtl::where('deleted_st', false)
            ->whereNotNull('terima_id')
            ->pluck('terima_id')
            ->unique()
            ->toArray();

        $query = DatTerimaHdr::with([
            'supplier',
            'details.barang.satuanDasar',
            'details.poDetail'
        ])
            ->where('deleted_st', false)
            ->where('status_cd', 'COMPLETED')
            ->whereNotIn('terima_id', $billedTerimaIds);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $results = $query->orderBy('terima_tgl', 'desc')
            ->orderBy('terima_id', 'desc')
            ->take(50)
            ->get();

        return $results->map(function ($grn) {
            $grnArr = $grn->toArray();
            if (!empty($grnArr['details'])) {
                foreach ($grnArr['details'] as $idx => $dtl) {
                    $harga = (float)($dtl['harga_nominal'] ?? 0);
                    if ($harga <= 0 && !empty($dtl['po_detail']['harga_nominal'])) {
                        $harga = (float)$dtl['po_detail']['harga_nominal'];
                    }
                    if ($harga <= 0 && !empty($dtl['barang']['harga_beli_standar'])) {
                        $harga = (float)$dtl['barang']['harga_beli_standar'];
                    }
                    $grnArr['details'][$idx]['harga_nominal'] = $harga;
                    $grnArr['details'][$idx]['harga_satuan'] = $harga;
                }
            }
            return $grnArr;
        })->toArray();
    }

    /**
     * Buat Tagihan Hutang Pembelian dari satu atau lebih Penerimaan Barang (GRN)
     */
    public function createFromGrn(array $payload, User $user): DatHutangHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $hutangNo = $this->generateHutangNo();
            $supplierId = $payload['supplier_id'];
            $tglTagihan = $payload['tgl_tagihan'] ?? now()->toDateString();
            $tglTempo = $payload['tgl_jatuh_tempo'] ?? now()->addDays(14)->toDateString();
            $noFakturSupp = $payload['no_faktur_supplier'] ?? null;
            $catatan = $payload['catatan'] ?? null;
            $gudangId = $payload['gudang_id'] ?? null;

            $items = $payload['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang yang ditagihkan.");
            }

            $totalBruto = 0;
            $totalDiskon = 0;
            $totalPpn = 0;
            $totalNetto = 0;

            foreach ($items as $item) {
                $subtotal = (float)($item['qty'] ?? 0) * (float)($item['harga_satuan'] ?? 0);
                $diskon = (float)($item['diskon_nominal'] ?? 0);
                $ppn = (float)($item['ppn_nominal'] ?? 0);
                $netto = $subtotal - $diskon + $ppn;

                $totalBruto += $subtotal;
                $totalDiskon += $diskon;
                $totalPpn += $ppn;
                $totalNetto += $netto;
            }

            $potonganRetur = (float)($payload['total_potongan_retur'] ?? 0);
            $totalNettoAkhir = max(0, $totalNetto - $potonganRetur);

            $hdr = DatHutangHdr::create([
                'hutang_no'            => $hutangNo,
                'no_faktur_supplier'   => $noFakturSupp,
                'supplier_id'          => $supplierId,
                'gudang_id'            => $gudangId,
                'tgl_tagihan'          => $tglTagihan,
                'tgl_jatuh_tempo'      => $tglTempo,
                'tipe_hutang'          => 'PEMBELIAN_GRN',
                'total_bruto'          => $totalBruto,
                'total_diskon'         => $totalDiskon,
                'total_potongan_retur' => $potonganRetur,
                'total_ppn'            => $totalPpn,
                'total_tagihan'        => $totalNettoAkhir,
                'total_dibayar'        => 0,
                'sisa_hutang'          => $totalNettoAkhir,
                'status_pembayaran'    => 'BELUM_LUNAS',
                'catatan'              => $catatan,
                'created_by'           => $user->name ?? 'SYSTEM',
                'updated_by'           => $user->name ?? 'SYSTEM',
            ]);

            foreach ($items as $item) {
                $qty = (float)($item['qty'] ?? 0);
                $harga = (float)($item['harga_satuan'] ?? 0);
                $subtotal = $qty * $harga;
                $diskon = (float)($item['diskon_nominal'] ?? 0);
                $ppn = (float)($item['ppn_nominal'] ?? 0);
                $netto = $subtotal - $diskon + $ppn;

                DatHutangDtl::create([
                    'hutang_id'      => $hdr->hutang_id,
                    'terima_id'      => $item['terima_id'] ?? null,
                    'terima_dtl_id'  => $item['terima_dtl_id'] ?? null,
                    'barang_id'      => $item['barang_id'] ?? null,
                    'deskripsi'      => $item['deskripsi'] ?? 'Penerimaan Bahan Baku',
                    'qty'            => $qty,
                    'satuan'         => $item['satuan'] ?? 'KG',
                    'harga_satuan'   => $harga,
                    'subtotal'       => $subtotal,
                    'diskon_nominal' => $diskon,
                    'ppn_nominal'    => $ppn,
                    'total_netto'    => $netto,
                    'catatan'        => $item['catatan'] ?? null,
                    'created_by'     => $user->name ?? 'SYSTEM',
                    'updated_by'     => $user->name ?? 'SYSTEM',
                ]);
            }

            return $hdr;
        });
    }

    /**
     * Hitung Rekapitulasi Pemakaian Gas Bulanan untuk Dasar Tagihan Utilitas
     */
    public function getGasUsageSummary(string $periodeBulan): array
    {
        // Periode: YYYY-MM
        $start = \Carbon\Carbon::parse($periodeBulan . '-01')->startOfMonth()->toDateString();
        $end = \Carbon\Carbon::parse($periodeBulan . '-01')->endOfMonth()->toDateString();

        $produksiHeaders = DatProduksiHdr::where('deleted_st', false)
            ->whereBetween('produksi_tgl', [$start, $end])
            ->get();

        $totalCngMmbtu = (float) $produksiHeaders->sum('cng_mmbtu');
        $totalGasKg = 0.0; // LPG cadangan jika ada

        // Ambil standar tarif aktif
        $tarifGas = MstTarifProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->whereIn('kode_tarif', ['TARIF_CNG', 'GAS_CNG_M3', 'GAS_CNG', 'GAS_LPG'])
            ->get();

        $tarifMmbtu = (float)($tarifGas->firstWhere('kode_tarif', 'TARIF_CNG')?->nilai_tarif 
            ?? $tarifGas->firstWhere('kode_tarif', 'GAS_CNG')?->nilai_tarif 
            ?? 226800);

        $tarifKg = (float)($tarifGas->firstWhere('kode_tarif', 'GAS_LPG')?->nilai_tarif 
            ?? 18000);

        $estimasiBiaya = ($totalCngMmbtu * $tarifMmbtu) + ($totalGasKg * $tarifKg);

        return [
            'periode_bulan'   => $periodeBulan,
            'total_cng_mmbtu' => (float) $totalCngMmbtu,
            'total_gas_m3'    => (float) $totalCngMmbtu, // backwards compatibility
            'total_gas_kg'    => (float) $totalGasKg,
            'tarif_mmbtu'     => $tarifMmbtu,
            'tarif_m3'        => $tarifMmbtu,
            'tarif_kg'        => $tarifKg,
            'estimasi_biaya'  => (float) $estimasiBiaya,
            'jumlah_batch'    => $produksiHeaders->count(),
        ];
    }

    /**
     * Buat Tagihan Khusus Gas / Utilitas Konsinyasi Bulanan
     */
    public function createTagihanGas(array $payload, User $user): DatHutangHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $hutangNo = $this->generateHutangNo();
            $supplierId = $payload['supplier_id'] ?? null;
            $periodeBulan = $payload['periode_bulan'] ?? date('Y-m');
            $tglTagihan = $payload['tgl_tagihan'] ?? now()->toDateString();
            $tglTempo = $payload['tgl_jatuh_tempo'] ?? now()->addDays(14)->toDateString();
            $noFakturSupp = $payload['no_faktur_supplier'] ?? null;
            $catatan = $payload['catatan'] ?? "Tagihan Konsinyasi Gas Utilitas Periode {$periodeBulan}";

            $qtyM3 = (float)($payload['qty_m3'] ?? 0);
            $hargaM3 = (float)($payload['harga_m3'] ?? 0);
            $subtotalM3 = $qtyM3 * $hargaM3;

            $qtyKg = (float)($payload['qty_kg'] ?? 0);
            $hargaKg = (float)($payload['harga_kg'] ?? 0);
            $subtotalKg = $qtyKg * $hargaKg;

            $totalBruto = $subtotalM3 + $subtotalKg;
            $totalPpn = (float)($payload['total_ppn'] ?? 0);
            $totalNetto = $totalBruto + $totalPpn;

            $hdr = DatHutangHdr::create([
                'hutang_no'            => $hutangNo,
                'no_faktur_supplier'   => $noFakturSupp,
                'supplier_id'          => $supplierId,
                'gudang_id'            => null,
                'tgl_tagihan'          => $tglTagihan,
                'tgl_jatuh_tempo'      => $tglTempo,
                'tipe_hutang'          => 'GAS_UTILITAS',
                'periode_bulan'        => $periodeBulan,
                'total_bruto'          => $totalBruto,
                'total_diskon'         => 0,
                'total_potongan_retur' => 0,
                'total_ppn'            => $totalPpn,
                'total_tagihan'        => $totalNetto,
                'total_dibayar'        => 0,
                'sisa_hutang'          => $totalNetto,
                'status_pembayaran'    => 'BELUM_LUNAS',
                'catatan'              => $catatan,
                'created_by'           => $user->name ?? 'SYSTEM',
                'updated_by'           => $user->name ?? 'SYSTEM',
            ]);

            if ($qtyM3 > 0) {
                DatHutangDtl::create([
                    'hutang_id'    => $hdr->hutang_id,
                    'deskripsi'    => "Pemakaian Gas CNG Pipa/Skid Tank ({$periodeBulan})",
                    'qty'          => $qtyM3,
                    'satuan'       => 'M3',
                    'harga_satuan' => $hargaM3,
                    'subtotal'     => $subtotalM3,
                    'total_netto'  => $subtotalM3,
                    'catatan'      => "Rekap meteran pemakaian dapur fryer pabrik",
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);
            }

            if ($qtyKg > 0) {
                DatHutangDtl::create([
                    'hutang_id'    => $hdr->hutang_id,
                    'deskripsi'    => "Pemakaian Gas LPG Industri ({$periodeBulan})",
                    'qty'          => $qtyKg,
                    'satuan'       => 'KG',
                    'harga_satuan' => $hargaKg,
                    'subtotal'     => $subtotalKg,
                    'total_netto'  => $subtotalKg,
                    'catatan'      => "Tabung gas operasional pabrik",
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);
            }

            return $hdr;
        });
    }

    /**
     * Hapus / Batalkan Tagihan Hutang
     */
    public function destroy(DatHutangHdr $hutang, User $user): void
    {
        if ($hutang->total_dibayar > 0) {
            throw new Exception("Tagihan {$hutang->hutang_no} tidak dapat dihapus karena sudah memiliki riwayat pembayaran (Total terbayar: Rp " . number_format($hutang->total_dibayar, 0, ',', '.') . "). Silakan batalkan transaksi pembayaran terlebih dahulu.");
        }

        DB::transaction(function () use ($hutang, $user) {
            $hutang->details()->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_by' => $user->name ?? 'SYSTEM',
            ]);

            $hutang->update([
                'status_pembayaran' => 'DIBATALKAN',
                'deleted_st'        => true,
                'active_st'         => false,
                'deleted_by'        => $user->name ?? 'SYSTEM',
            ]);
        });
    }
}
