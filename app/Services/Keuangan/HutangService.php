<?php

namespace App\Services\Keuangan;

use App\Models\Gudang\DatTerimaHdr;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\Keuangan\DatHutangDtl;
use App\Models\Keuangan\DatJurnalHdr;
use App\Models\MasterData\MstSupplier;
use App\Models\MasterData\MstBarang;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\MasterData\MstTarifProduksi;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class HutangService
{
    protected JurnalService $jurnalService;

    public function __construct(JurnalService $jurnalService)
    {
        $this->jurnalService = $jurnalService;
    }

    /**
     * Dapatkan daftar tagihan hutang terpaginasi dengan filter cerdas
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatHutangHdr::with(['supplier.jenisSupplier', 'gudang', 'details.barang', 'jurnal'])
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
            
            // Hitung akumulasi potongan level item agar tidak terpotong dua kali di level header
            $totalItemPotongan = 0;
            if (!empty($grnArr['details'])) {
                foreach ($grnArr['details'] as $dtl) {
                    $totalItemPotongan += (float)($dtl['potongan_nominal'] ?? 0);
                }
            }
            $grnArr['potongan_global'] = max(0, (float)($grn->potongan_nominal ?? 0) - $totalItemPotongan);

            if (!empty($grnArr['details'])) {
                foreach ($grnArr['details'] as $idx => $dtl) {
                    $terimaQty = (float)($dtl['terima_qty'] ?? 0);
                    $rejectQty = (float)($dtl['reject_qty'] ?? 0);
                    $nettoQty = max(0, $terimaQty - $rejectQty);

                    $harga = (float)($dtl['harga_nominal'] ?? 0);
                    if ($harga <= 0 && !empty($dtl['po_detail']['harga_nominal'])) {
                        $harga = (float)$dtl['po_detail']['harga_nominal'];
                    }
                    if ($harga <= 0 && !empty($dtl['barang']['harga_beli_standar'])) {
                        $harga = (float)$dtl['barang']['harga_beli_standar'];
                    }

                    $subtotal = round($nettoQty * $harga, 2);

                    // Hitung diskon murni per-baris dari GRN (persen / nominal)
                    $diskonPersen = (float)($dtl['diskon_persen'] ?? 0);
                    $diskonNominalUnit = (float)($dtl['diskon_nominal'] ?? 0);
                    $potonganItem = (float)($dtl['potongan_nominal'] ?? 0);

                    $diskonTotal = 0;
                    if ($diskonPersen > 0) {
                        $diskonTotal = round($subtotal * ($diskonPersen / 100), 2);
                    } elseif ($diskonNominalUnit > 0) {
                        $diskonTotal = round($nettoQty * $diskonNominalUnit, 2);
                        if ($subtotal > 0 && $diskonPersen <= 0) {
                            $diskonPersen = round(($diskonTotal / $subtotal) * 100, 2);
                        }
                    }

                    // Hitung PPN per-baris dari GRN (setelah dikurangi diskon & potongan mutu fisik)
                    $ppnNominal = (float)($dtl['ppn_nominal'] ?? 0);
                    $ppnTipe = $dtl['ppn_tipe'] ?? ($ppnNominal > 0 ? 'PPN_11' : 'NON_PPN');
                    if ($ppnNominal <= 0 && $ppnTipe === 'PPN_11') {
                        $dpp = max(0, $subtotal - $diskonTotal - $potonganItem);
                        $ppnNominal = round($dpp * 0.11, 2);
                    }

                    $grnArr['details'][$idx]['netto_qty'] = $nettoQty;
                    $grnArr['details'][$idx]['harga_nominal'] = $harga;
                    $grnArr['details'][$idx]['harga_satuan'] = $harga;
                    $grnArr['details'][$idx]['diskon_persen'] = $diskonPersen;
                    $grnArr['details'][$idx]['diskon_nominal'] = $diskonTotal;
                    $grnArr['details'][$idx]['diskon_total'] = $diskonTotal;
                    $grnArr['details'][$idx]['potongan_nominal'] = $potonganItem;
                    $grnArr['details'][$idx]['ppn_tipe'] = $ppnTipe;
                    $grnArr['details'][$idx]['ppn_total'] = $ppnNominal;
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
            $totalPotonganItem = 0;
            $totalPpn = 0;
            $totalNetto = 0;

            // Normalisasi & kalkulasi seluruh baris item tagihan
            $processedItems = [];
            foreach ($items as $item) {
                $qty = (float)($item['qty'] ?? 0);
                $harga = (float)($item['harga_satuan'] ?? 0);
                $subtotal = $qty * $harga;
                $diskonPersen = (float)($item['diskon_persen'] ?? 0);
                $diskon = (float)($item['diskon_nominal'] ?? 0);
                $potongan = (float)($item['potongan_nominal'] ?? 0);
                $ppnTipe = $item['ppn_tipe'] ?? 'NON_PPN';
                
                $nilaiBersih = max(0, $subtotal - $diskon - $potongan);
                
                if ($ppnTipe === 'PPN_INCLUDE') {
                    // Harga satuan sudah include PPN 11% (harga kotor)
                    $netto = $nilaiBersih;
                    if (isset($item['ppn_nominal']) && is_numeric($item['ppn_nominal']) && (float)$item['ppn_nominal'] > 0) {
                        $ppn = (float)$item['ppn_nominal'];
                        $dpp = max(0, $nilaiBersih - $ppn);
                    } else {
                        $dpp = round($nilaiBersih / 1.11, 2);
                        $ppn = round($nilaiBersih - $dpp, 2);
                    }
                } elseif ($ppnTipe === 'PPN_11') {
                    // PPN 11% Exclude (ditambahkan di atas DPP)
                    $dpp = $nilaiBersih;
                    $ppn = isset($item['ppn_nominal']) && is_numeric($item['ppn_nominal'])
                        ? (float)$item['ppn_nominal']
                        : round($dpp * 0.11, 2);
                    $netto = $dpp + $ppn;
                } else {
                    // NON_PPN
                    $ppnTipe = 'NON_PPN';
                    $dpp = $nilaiBersih;
                    $ppn = 0;
                    $netto = $nilaiBersih;
                }

                $processedItems[] = [
                    'terima_id'        => $item['terima_id'] ?? null,
                    'terima_dtl_id'    => $item['terima_dtl_id'] ?? null,
                    'barang_id'        => $item['barang_id'] ?? null,
                    'deskripsi'        => $item['deskripsi'] ?? 'Penerimaan Bahan Baku',
                    'qty'              => $qty,
                    'satuan'           => $item['satuan'] ?? 'KG',
                    'harga_satuan'     => $harga,
                    'subtotal'         => $subtotal,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskon,
                    'potongan_nominal' => $potongan,
                    'ppn_tipe'         => $ppnTipe,
                    'dpp_nominal'      => $dpp,
                    'ppn_nominal'      => $ppn,
                    'total_netto'      => $netto,
                    'catatan'          => $item['catatan'] ?? null,
                ];

                $totalBruto += $subtotal;
                $totalDiskon += $diskon;
                $totalPotonganItem += $potongan;
                $totalPpn += $ppn;
                $totalNetto += $netto;
            }

            $potonganRetur = (float)($payload['total_potongan_retur'] ?? 0);
            $biayaOngkir = (float)($payload['biaya_ongkir'] ?? 0);
            
            // Formula Grand Total Tagihan: Akumulasi Seluruh Netto Item - Potongan Retur Global + Biaya Ongkir
            $totalNettoAkhir = max(0, ($totalNetto - $potonganRetur) + $biayaOngkir);

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
                'total_potongan_item'  => $totalPotonganItem,
                'total_potongan_retur' => $potonganRetur,
                'total_ppn'            => $totalPpn,
                'biaya_ongkir'         => $biayaOngkir,
                'total_tagihan'        => $totalNettoAkhir,
                'total_dibayar'        => 0,
                'sisa_hutang'          => $totalNettoAkhir,
                'status_pembayaran'    => 'BELUM_LUNAS',
                'catatan'              => $catatan,
                'created_by'           => $user->name ?? 'SYSTEM',
                'updated_by'           => $user->name ?? 'SYSTEM',
            ]);

            foreach ($processedItems as $pItem) {
                DatHutangDtl::create([
                    'hutang_id'        => $hdr->hutang_id,
                    'terima_id'        => $pItem['terima_id'],
                    'terima_dtl_id'    => $pItem['terima_dtl_id'],
                    'barang_id'        => $pItem['barang_id'],
                    'deskripsi'        => $pItem['deskripsi'],
                    'qty'              => $pItem['qty'],
                    'satuan'           => $pItem['satuan'],
                    'harga_satuan'     => $pItem['harga_satuan'],
                    'subtotal'         => $pItem['subtotal'],
                    'diskon_persen'    => $pItem['diskon_persen'],
                    'diskon_nominal'   => $pItem['diskon_nominal'],
                    'potongan_nominal' => $pItem['potongan_nominal'],
                    'ppn_tipe'         => $pItem['ppn_tipe'],
                    'dpp_nominal'      => $pItem['dpp_nominal'],
                    'ppn_nominal'      => $pItem['ppn_nominal'],
                    'total_netto'      => $pItem['total_netto'],
                    'catatan'          => $pItem['catatan'],
                    'created_by'       => $user->name ?? 'SYSTEM',
                    'updated_by'       => $user->name ?? 'SYSTEM',
                ]);
            }

            // ============================================================
            // OTOMASI PENCATATAN JURNAL DOUBLE-ENTRY KE COA (BAGAN AKUN)
            // FORMAT STANDAR BUKU BESAR MIRASA:
            // [Supplier] - [Qty] [Satuan] [Nama Barang] @[Harga Satuan]
            // ============================================================
            $supplierModel = MstSupplier::find($supplierId);
            $suppName = $supplierModel?->supplier_nm ?? 'Supplier';

            $formatNum = function ($num, $maxDecimals = 2) {
                $num = (float)$num;
                if (floor($num) == $num) {
                    return number_format($num, 0, ',', '.');
                }
                return rtrim(rtrim(number_format($num, $maxDecimals, ',', '.'), '0'), ',');
            };

            $formatSatuan = function ($satuan) {
                $s = trim(strtolower($satuan ?? ''));
                if ($s === 'kilogram' || $s === 'kg') return 'kg';
                if ($s === 'gram' || $s === 'gr') return 'gr';
                if ($s === 'pieces' || $s === 'pcs') return 'pcs';
                if ($s === 'roll' || $s === 'rol') return 'roll';
                return !empty($satuan) ? $satuan : 'unit';
            };

            $jurnalEntries = [];

            foreach ($processedItems as $item) {
                $qty = $item['qty'];
                $harga = $item['harga_satuan'];
                $dppItem = $item['dpp_nominal'];
                $ppn = $item['ppn_nominal'];
                $totalHutangItem = $item['total_netto'];
                $ppnTipe = $item['ppn_tipe'];

                // Format Keterangan Persis Excel Mirasa:
                // Nusantara Top - 600 kg Palet Taro @7.657
                $qtyStr = $formatNum($qty, 2);
                $satuanStr = $formatSatuan($item['satuan'] ?? '');
                $hargaStr = $formatNum($harga, 2);

                $namaBarang = trim($item['deskripsi'] ?? '');
                if (empty($namaBarang) && !empty($item['barang_id'])) {
                    $namaBarang = MstBarang::find($item['barang_id'])?->barang_nm ?? 'Bahan Baku';
                }
                if (empty($namaBarang)) {
                    $namaBarang = 'Bahan Baku';
                }

                $ppnSuffix = ($ppnTipe === 'PPN_INCLUDE') ? ' (Inc PPN)' : '';
                $keteranganItem = "{$suppName} - {$qtyStr} {$satuanStr} {$namaBarang} @{$hargaStr}{$ppnSuffix}";

                // 1. (Debet) Persediaan Bahan Baku & Penolong (Akun 1150)
                if ($dppItem > 0) {
                    $jurnalEntries[] = [
                        'kode_akun'  => '1150',
                        'debet'      => $dppItem,
                        'kredit'     => 0,
                        'keterangan' => $keteranganItem,
                    ];
                }

                // 2. (Debet) PPN Masukan (Akun 1130) jika ada PPN
                if ($ppn > 0) {
                    $jurnalEntries[] = [
                        'kode_akun'  => '1130',
                        'debet'      => $ppn,
                        'kredit'     => 0,
                        'keterangan' => $keteranganItem,
                    ];
                }

                // 3. (Kredit) Hutang Usaha (Akun 2100) per item
                if ($totalHutangItem > 0) {
                    $jurnalEntries[] = [
                        'kode_akun'  => '2100',
                        'debet'      => 0,
                        'kredit'     => $totalHutangItem,
                        'keterangan' => $keteranganItem,
                    ];
                }
            }

            // 4. Penyesuaian Potongan Retur Global (jika ada)
            if ($potonganRetur > 0) {
                $jurnalEntries[] = [
                    'kode_akun'  => '2100',
                    'debet'      => $potonganRetur,
                    'kredit'     => 0,
                    'keterangan' => "Potongan Retur Pembelian - {$suppName} - {$hdr->hutang_no}",
                ];
                $jurnalEntries[] = [
                    'kode_akun'  => '1150',
                    'debet'      => 0,
                    'kredit'     => $potonganRetur,
                    'keterangan' => "Potongan Retur Pembelian - {$suppName} - {$hdr->hutang_no}",
                ];
            }

            // 5. Penyesuaian Ongkos Angkut / Ekspedisi Pembelian (jika ada)
            if ($biayaOngkir > 0) {
                $jurnalEntries[] = [
                    'kode_akun'  => '6112',
                    'debet'      => $biayaOngkir,
                    'kredit'     => 0,
                    'keterangan' => "Ongkos Angkut Pembelian - {$suppName} - {$hdr->hutang_no}",
                ];
                $jurnalEntries[] = [
                    'kode_akun'  => '2100',
                    'debet'      => 0,
                    'kredit'     => $biayaOngkir,
                    'keterangan' => "Ongkos Angkut Pembelian - {$suppName} - {$hdr->hutang_no}",
                ];
            }

            if (!empty($jurnalEntries)) {
                $this->jurnalService->catatJurnal(
                    tipeTransaksi: 'HUTANG_PEMBELIAN',
                    tglJurnal: $hdr->tgl_tagihan,
                    refNo: $hdr->hutang_no,
                    refId: $hdr->hutang_id,
                    keterangan: "Pencatatan Tagihan Hutang Pembelian {$hdr->hutang_no} - {$suppName}",
                    entries: $jurnalEntries,
                    user: $user
                );
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

            // Otomasi Jurnal Pengakuan Beban Utilitas Gas Pabrik
            $jurnalEntries = [];
            if ($totalBruto > 0) {
                $jurnalEntries[] = [
                    'kode_akun'  => '6102', // Beban air & Listrik (dan Gas)
                    'debet'      => $totalBruto,
                    'kredit'     => 0,
                    'keterangan' => "Beban Gas Pabrik Periode {$periodeBulan} ({$hdr->hutang_no})",
                ];
            }
            if ($totalPpn > 0) {
                $jurnalEntries[] = [
                    'kode_akun'  => '1130', // PPN Masukan
                    'debet'      => $totalPpn,
                    'kredit'     => 0,
                    'keterangan' => "PPN Masukan Gas Periode {$periodeBulan} ({$hdr->hutang_no})",
                ];
            }
            if ($totalNetto > 0) {
                $jurnalEntries[] = [
                    'kode_akun'  => '2100', // Hutang usaha
                    'debet'      => 0,
                    'kredit'     => $totalNetto,
                    'keterangan' => "Hutang Tagihan Gas Periode {$periodeBulan} ({$hdr->hutang_no})",
                ];
            }

            if (!empty($jurnalEntries)) {
                $this->jurnalService->catatJurnal(
                    tipeTransaksi: 'HUTANG_UTILITAS',
                    tglJurnal: $hdr->tgl_tagihan,
                    refNo: $hdr->hutang_no,
                    refId: $hdr->hutang_id,
                    keterangan: "Pencatatan Tagihan Gas Utilitas {$hdr->hutang_no} Periode {$periodeBulan}",
                    entries: $jurnalEntries,
                    user: $user
                );
            }

            return $hdr;
        });
    }

    /**
     * Otomatis membuat Faktur Tagihan Hutang (AP) saat Penerimaan Barang (GRN) tuntas.
     * Menggunakan tanggal jatuh tempo dari Surat Jalan jika ada, atau default TOP supplier, atau fallback 14 hari.
     */
    public function createAutoFromGrn(DatTerimaHdr $header, ?string $manualTempo = null): ?DatHutangHdr
    {
        return DB::transaction(function () use ($header, $manualTempo) {
            // 1. Cek apakah GRN ini sudah pernah ditagihkan di dat_hutang_dtl
            $alreadyInvoiced = DatHutangDtl::where('deleted_st', false)
                ->where('terima_id', $header->terima_id)
                ->exists();

            if ($alreadyInvoiced) {
                return null;
            }

            // 2. Muat relasi yang dibutuhkan
            $header->loadMissing([
                'supplier',
                'details.barang.satuanDasar',
                'details.poDetail'
            ]);

            // 3. Filter item yang memiliki kuantitas netto diterima > 0 (tidak termasuk afkir/reject)
            $itemsToInvoice = [];
            $totalBruto = 0;
            $totalDiskon = 0;
            $totalPpn = 0;
            $totalNetto = 0;

            foreach ($header->details as $dtl) {
                $nettoQty = max(0, (float)$dtl->terima_qty - (float)($dtl->reject_qty ?? 0));
                if ($nettoQty <= 0) {
                    continue;
                }

                // Ambil harga satuan acuan
                $harga = (float)($dtl->harga_nominal ?? 0);
                if ($harga <= 0 && $dtl->poDetail && (float)$dtl->poDetail->harga_nominal > 0) {
                    $harga = (float)$dtl->poDetail->harga_nominal;
                }
                if ($harga <= 0 && $dtl->barang && (float)$dtl->barang->harga_beli_standar > 0) {
                    $harga = (float)$dtl->barang->harga_beli_standar;
                }
                if ($harga <= 0 && (float)($dtl->harga_netto ?? 0) > 0) {
                    $harga = (float)$dtl->harga_netto;
                }

                $subtotal = round($nettoQty * $harga, 2);
                $diskon = 0;
                if ((float)($dtl->diskon_persen ?? 0) > 0) {
                    $diskon = round($subtotal * ((float)$dtl->diskon_persen / 100), 2);
                } elseif ((float)($dtl->diskon_nominal ?? 0) > 0) {
                    $diskon = round((float)$dtl->diskon_nominal * $nettoQty, 2);
                }

                $potongan = (float)($dtl->potongan_nominal ?? 0);
                $diskonDanPotongan = $diskon + $potongan;

                $dpp = max(0, $subtotal - $diskonDanPotongan);
                $ppn = ($dtl->ppn_tipe === 'PPN_11') ? round($dpp * 0.11, 2) : 0;
                $netto = round($dpp + $ppn, 2);

                $totalBruto += $subtotal;
                $totalDiskon += $diskonDanPotongan;
                $totalPpn += $ppn;
                $totalNetto += $netto;

                $itemsToInvoice[] = [
                    'dtl'            => $dtl,
                    'netto_qty'      => $nettoQty,
                    'satuan'         => $dtl->barang?->satuanDasar?->satuan_nm ?? 'KG',
                    'harga_satuan'   => $harga,
                    'subtotal'       => $subtotal,
                    'diskon_nominal' => $diskonDanPotongan,
                    'ppn_nominal'    => $ppn,
                    'total_netto'    => $netto,
                ];
            }

            // Jika tidak ada barang yang lolos fisik (misal seluruhnya ditolak/afkir)
            if (empty($itemsToInvoice)) {
                return null;
            }

            // Potongan tambahan GRN jika ada
            $potonganGlobal = max(0, (float)($header->potongan_nominal ?? 0) - $totalDiskon);
            $totalNettoAkhir = max(0, $totalNetto - $potonganGlobal);

            // 4. Hitung Tanggal Jatuh Tempo
            // Prioritas 1: Manual param jika dioper
            // Prioritas 2: Dari Surat Jalan fisik (terima->tgl_jatuh_tempo)
            // Prioritas 3: Tanggal GRN + TOP Master Supplier (top_hari)
            // Prioritas 4: Tanggal GRN + 14 hari
            $terimaTgl = $header->terima_tgl ? \Carbon\Carbon::parse($header->terima_tgl) : now();

            if (!empty($manualTempo)) {
                $tglTempo = \Carbon\Carbon::parse($manualTempo)->toDateString();
            } elseif (!empty($header->tgl_jatuh_tempo)) {
                $tglTempo = \Carbon\Carbon::parse($header->tgl_jatuh_tempo)->toDateString();
            } elseif (!empty($header->supplier?->top_hari) && (int)$header->supplier->top_hari > 0) {
                $tglTempo = (clone $terimaTgl)->addDays((int)$header->supplier->top_hari)->toDateString();
            } else {
                $tglTempo = (clone $terimaTgl)->addDays(14)->toDateString();
            }

            // 5. Buat Header Hutang AP
            $userNm = auth()->user()?->name ?? 'SYSTEM_AUTO_GRN';
            $hutangNo = $this->generateHutangNo();
            $noFakturSupp = $header->suratjalan_no ? 'SJ-' . $header->suratjalan_no : null;

            $hdr = DatHutangHdr::create([
                'hutang_no'            => $hutangNo,
                'no_faktur_supplier'   => $noFakturSupp,
                'supplier_id'          => $header->supplier_id,
                'gudang_id'            => $header->gudang_id,
                'tgl_tagihan'          => $terimaTgl->toDateString(),
                'tgl_jatuh_tempo'      => $tglTempo,
                'tipe_hutang'          => 'PEMBELIAN_GRN',
                'total_bruto'          => $totalBruto,
                'total_diskon'         => $totalDiskon,
                'total_potongan_retur' => $potonganGlobal,
                'total_ppn'            => $totalPpn,
                'total_tagihan'        => $totalNettoAkhir,
                'total_dibayar'        => 0,
                'sisa_hutang'          => $totalNettoAkhir,
                'status_pembayaran'    => 'BELUM_LUNAS',
                'catatan'              => "Auto-generated dari GRN {$header->terima_no}" . ($header->suratjalan_no ? " (SJ: {$header->suratjalan_no})" : ''),
                'created_by'           => $userNm,
                'updated_by'           => $userNm,
            ]);

            // 6. Buat Detail Hutang AP
            foreach ($itemsToInvoice as $row) {
                $dtl = $row['dtl'];
                DatHutangDtl::create([
                    'hutang_id'      => $hdr->hutang_id,
                    'terima_id'      => $header->terima_id,
                    'terima_dtl_id'  => $dtl->terimadtl_id,
                    'barang_id'      => $dtl->barang_id,
                    'deskripsi'      => ($dtl->barang?->barang_nm ?? 'Barang') . " (GRN {$header->terima_no})",
                    'qty'            => $row['netto_qty'],
                    'satuan'         => $row['satuan'],
                    'harga_satuan'   => $row['harga_satuan'],
                    'subtotal'       => $row['subtotal'],
                    'diskon_nominal' => $row['diskon_nominal'],
                    'ppn_nominal'    => $row['ppn_nominal'],
                    'total_netto'    => $row['total_netto'],
                    'catatan'        => $dtl->catatan_txt ?? null,
                    'created_by'     => $userNm,
                    'updated_by'     => $userNm,
                ]);
            }

            // 7. Otomasi Pencatatan Jurnal Double-Entry ke Bagan Akun (COA)
            $supplierName = $header->supplier?->supplier_nm ?? 'Supplier';
            $jurnalEntries = [];

            $formatNum = function ($num, $maxDecimals = 2) {
                $num = (float)$num;
                if (floor($num) == $num) {
                    return number_format($num, 0, ',', '.');
                }
                return rtrim(rtrim(number_format($num, $maxDecimals, ',', '.'), '0'), ',');
            };

            $formatSatuan = function ($satuan) {
                $s = trim(strtolower($satuan ?? ''));
                if ($s === 'kilogram' || $s === 'kg') return 'kg';
                if ($s === 'gram' || $s === 'gr') return 'gr';
                if ($s === 'pieces' || $s === 'pcs') return 'pcs';
                if ($s === 'roll' || $s === 'rol') return 'roll';
                return !empty($satuan) ? $satuan : 'unit';
            };

            foreach ($itemsToInvoice as $row) {
                $qty = (float)$row['netto_qty'];
                $harga = (float)$row['harga_satuan'];
                $subtotal = (float)$row['subtotal'];
                $diskon = (float)$row['diskon_nominal'];
                $ppn = (float)$row['ppn_nominal'];
                $dpp = max(0, $subtotal - $diskon);
                $totalHutang = $dpp + $ppn;

                $qtyStr = $formatNum($qty, 2);
                $satuanStr = $formatSatuan($row['satuan']);
                $hargaStr = $formatNum($harga, 2);
                $namaBarang = trim($row['dtl']->barang?->barang_nm ?? 'Bahan Baku');

                $keteranganItem = "{$supplierName} - {$qtyStr} {$satuanStr} {$namaBarang} @{$hargaStr}";

                if ($dpp > 0) {
                    $jurnalEntries[] = [
                        'kode_akun'  => '1150',
                        'debet'      => $dpp,
                        'kredit'     => 0,
                        'keterangan' => $keteranganItem,
                    ];
                }
                if ($ppn > 0) {
                    $jurnalEntries[] = [
                        'kode_akun'  => '1130',
                        'debet'      => $ppn,
                        'kredit'     => 0,
                        'keterangan' => $keteranganItem,
                    ];
                }
                if ($totalHutang > 0) {
                    $jurnalEntries[] = [
                        'kode_akun'  => '2100',
                        'debet'      => 0,
                        'kredit'     => $totalHutang,
                        'keterangan' => $keteranganItem,
                    ];
                }
            }

            if (!empty($jurnalEntries)) {
                $userObj = auth()->user() ?? User::where('name', $userNm)->first() ?? new User(['name' => $userNm]);
                $this->jurnalService->catatJurnal(
                    tipeTransaksi: 'HUTANG_PEMBELIAN',
                    tglJurnal: $terimaTgl->toDateString(),
                    refNo: $hdr->hutang_no,
                    refId: $hdr->hutang_id,
                    keterangan: "Pencatatan Tagihan Hutang Pembelian {$hdr->hutang_no} - {$supplierName}",
                    entries: $jurnalEntries,
                    user: $userObj
                );
            }

            return $hdr;
        });
    }

    /**
     * Perbarui No Faktur Supplier dan Tanggal Jatuh Tempo oleh tim Keuangan
     */
    public function updateTempoDanFaktur(int $hutangId, array $data, ?User $user = null): DatHutangHdr
    {
        $hutang = DatHutangHdr::where('deleted_st', false)->findOrFail($hutangId);

        if (!empty($data['tgl_jatuh_tempo'])) {
            $hutang->tgl_jatuh_tempo = $data['tgl_jatuh_tempo'];
        }
        if (array_key_exists('no_faktur_supplier', $data)) {
            $hutang->no_faktur_supplier = $data['no_faktur_supplier'];
        }
        if (!empty($data['catatan'])) {
            $hutang->catatan = trim(($hutang->catatan ? $hutang->catatan . ' | ' : '') . $data['catatan']);
        }

        $hutang->updated_by = $user->name ?? (auth()->user()?->name ?? 'SYSTEM');
        $hutang->save();

        return $hutang;
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

            // Batalkan / Nonaktifkan Jurnal Terkait jika ada
            DatJurnalHdr::where('ref_no', $hutang->hutang_no)
                ->where('deleted_st', false)
                ->update([
                    'deleted_st' => true,
                    'active_st'  => false,
                    'deleted_by' => $user->name ?? 'SYSTEM',
                    'updated_by' => $user->name ?? 'SYSTEM',
                ]);
        });
    }
}
