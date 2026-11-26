<?php

namespace App\Services\Penjualan;

use App\Models\Gudang\DatKartuStok;
use App\Models\Gudang\DatPengeluaranDtl;
use App\Models\Gudang\DatPengeluaranHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatFakturDtl;
use App\Models\Penjualan\DatFakturHdr;
use App\Models\Penjualan\DatSoDtl;
use App\Models\Penjualan\DatSoHdr;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PenjualanBahanPenolongService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Dapatkan daftar transaksi penjualan bahan penolong & bumbu berpaginasi
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): LengthAwarePaginator {
        $query = DatSoHdr::with(['customer', 'details.barang.satuanDasar', 'details.barang.jenisBarang', 'pengeluaran', 'faktur'])
            ->where('deleted_st', false)
            ->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN BAHAN PENOLONG%')
                  ->orWhere('so_no', 'like', '%/JBP/%')
                  ->orWhere('so_no', 'like', 'JBP-%')
                  ->orWhere('so_no', 'like', 'BP-%');
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('so_no', 'ilike', "%{$search}%")
                  ->orWhere('faktur_no', 'ilike', "%{$search}%")
                  ->orWhere('surat_jalan_no', 'ilike', "%{$search}%")
                  ->orWhere('supir_nm', 'ilike', "%{$search}%")
                  ->orWhere('catatan_txt', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('customer_nm', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('details.barang', function ($b) use ($search) {
                      $b->where('barang_nm', 'ilike', "%{$search}%")
                        ->orWhere('barang_cd', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($startDate)) {
            $query->whereDate('so_tgl', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('so_tgl', '<=', $endDate);
        }

        return $query->orderBy('so_tgl', 'desc')
            ->orderBy('so_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Dapatkan daftar transaksi penjualan bahan penolong & bumbu untuk diexport ke Excel
     */
    public function getAllForExport(
        ?string $search = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = DatSoHdr::with(['customer', 'details.barang.satuanDasar', 'details.barang.jenisBarang', 'pengeluaran', 'faktur'])
            ->where('deleted_st', false)
            ->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN BAHAN PENOLONG%')
                  ->orWhere('so_no', 'like', '%/JBP/%')
                  ->orWhere('so_no', 'like', 'JBP-%')
                  ->orWhere('so_no', 'like', 'BP-%');
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('so_no', 'ilike', "%{$search}%")
                  ->orWhere('faktur_no', 'ilike', "%{$search}%")
                  ->orWhere('surat_jalan_no', 'ilike', "%{$search}%")
                  ->orWhere('supir_nm', 'ilike', "%{$search}%")
                  ->orWhere('catatan_txt', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('customer_nm', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('details.barang', function ($b) use ($search) {
                      $b->where('barang_nm', 'ilike', "%{$search}%")
                        ->orWhere('barang_cd', 'ilike', "%{$search}%");
                  });
            });
        }

        if (!empty($startDate)) {
            $query->whereDate('so_tgl', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('so_tgl', '<=', $endDate);
        }

        return $query->orderBy('so_tgl', 'desc')
            ->orderBy('so_id', 'desc')
            ->get();
    }

    /**
     * KPI Ringkasan Metrik Penjualan Bahan Penolong & Bumbu
     */
    public function getMetrics(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $base = DatSoHdr::where('deleted_st', false)
            ->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN BAHAN PENOLONG%')
                  ->orWhere('so_no', 'like', '%/JBP/%')
                  ->orWhere('so_no', 'like', 'JBP-%')
                  ->orWhere('so_no', 'like', 'BP-%');
            });

        $todayQuery = (clone $base)->whereDate('so_tgl', $today);
        $monthQuery = (clone $base)->whereRaw("TO_CHAR(so_tgl, 'YYYY-MM') = ?", [$thisMonth]);

        $todayOmzet = (float) $todayQuery->sum('total_tagihan');
        $todayCount = (int) $todayQuery->count();

        $monthOmzet = (float) $monthQuery->sum('total_tagihan');
        $monthCount = (int) $monthQuery->count();

        $totalOmzet = (float) (clone $base)->sum('total_tagihan');
        $totalCount = (int) (clone $base)->count();

        return [
            'today_omzet' => $todayOmzet,
            'today_count' => $todayCount,
            'month_omzet' => $monthOmzet,
            'month_count' => $monthCount,
            'total_omzet' => $totalOmzet,
            'total_count' => $totalCount,
        ];
    }

    /**
     * Dapatkan Customer Khusus Penjualan Umum
     */
    public function getOrCreateCashCustomer(): MstCustomer
    {
        $cust = MstCustomer::where('customer_cd', 'CUST-UMUM-BP')
            ->orWhere('customer_nm', 'ilike', '%PELANGGAN UMUM BAHAN PENOLONG%')
            ->first();

        if (!$cust) {
            $cust = MstCustomer::where('customer_cd', 'CUST-UMUM')->first();
        }

        if (!$cust) {
            $cust = MstCustomer::create([
                'customer_cd' => 'CUST-UMUM-BP',
                'customer_nm' => 'PELANGGAN UMUM / ECERAN',
                'kontak_no'   => '-',
                'alamat_txt'  => 'Penjualan Bahan Penolong & Bumbu Pabrik',
                'klasifikasi' => 'MANUAL',
                'top_txt'     => 'LANGSUNG',
                'default_ppn' => 'NON_PPN',
                'active_st'   => true,
                'deleted_st'  => false,
                'created_by'  => 'SYSTEM_INIT',
            ]);
        }

        return $cust;
    }

    /**
     * Dapatkan daftar barang Bahan Penolong, Kemasan & Bumbu siap jual beserta stok batch aktif
     */
    public function getReadyToSellItems(?int $defaultGudangId = null): Collection
    {
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->whereHas('jenisBarang', function ($q) {
                $q->whereIn('jenis_barang_cd', ['BP', 'PACK', 'SUPP', 'RAW', 'BB']);
            })
            ->where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('barang_nm')
            ->get();

        // Ambil pemetaan sisa stok batch aktif per gudang dan per barang
        $stocks = DatStokBatch::where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->select('gudang_id', 'barang_id', DB::raw('SUM(sisa_qty) as total_sisa'))
            ->groupBy('gudang_id', 'barang_id')
            ->get();

        $stockMatrix = [];
        foreach ($stocks as $s) {
            $stockMatrix[$s->barang_id][$s->gudang_id] = (float) $s->total_sisa;
        }

        foreach ($barangs as $b) {
            $b->stocks_by_gudang = $stockMatrix[$b->barang_id] ?? [];
            if ($defaultGudangId) {
                $b->ready_stock = (float) ($b->stocks_by_gudang[$defaultGudangId] ?? 0);
            } else {
                // Default cek gudang MFI-CBG (3), MFI (1), atau gudang pertama
                $b->ready_stock = (float) ($b->stocks_by_gudang[3] ?? ($b->stocks_by_gudang[1] ?? 0));
            }
        }

        return $barangs;
    }

    /**
     * Eksekusi Transaksi Penjualan Bahan Penolong & Bumbu 1-Klik Selesai
     * Alur: Buat SO Completed -> Pengeluaran Terkirim (Potong Stok FIFO) -> Faktur Lunas
     */
    public function createPenjualanBahanPenolong(array $data, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $soTgl = $data['tgl_transaksi'] ?? now()->toDateString();
            $soNo = $this->codeGenerator->generatePenjualanBahanPenolongNo($soTgl);
            $suratJalanNo = $this->codeGenerator->generateSuratJalanNo($soTgl);
            $fakturNo = $this->codeGenerator->generateFakturNo($soTgl);

            // Default gudang asal (MFI-CBG / MFI-PST / Gudang Bahan)
            $gudangId = !empty($data['gudang_id']) ? (int) $data['gudang_id'] : 3;
            $gudang = MstGudang::findOrFail($gudangId);

            // 1. Validasi Customer
            $customerId = !empty($data['customer_id']) ? (int) $data['customer_id'] : null;
            if (!$customerId) {
                $defaultCust = $this->getOrCreateCashCustomer();
                $customerId = $defaultCust->customer_id;
            }
            $customer = MstCustomer::findOrFail($customerId);

            // 2. Hitung Rincian Item Penjualan
            $itemsInput = $data['items'] ?? [];
            if (empty($itemsInput) || !is_array($itemsInput)) {
                throw new Exception("Minimal harus memilih 1 jenis barang yang dijual.");
            }

            $subtotalBruto = 0;
            $diskonTotal = 0;
            $dppNominal = 0;
            $ppnNominal = 0;
            $totalTagihan = 0;
            $calculatedItems = [];

            $isPpn = !empty($data['is_ppn']);
            $ppnTipe = $isPpn ? 'PPN_11' : 'NON_PPN';
            $ppnPersen = $isPpn ? 11.00 : 0.00;

            foreach ($itemsInput as $row) {
                $barangId = (int) ($row['barang_id'] ?? 0);
                $qty = (float) ($row['qty'] ?? 0);
                $hargaSatuan = (float) ($row['harga_satuan'] ?? 0);
                $diskonPersen = (float) ($row['diskon_persen'] ?? 0);
                $potonganItem = (float) ($row['potongan_nominal'] ?? 0);

                if ($barangId <= 0 || $qty <= 0) {
                    continue;
                }

                $barang = MstBarang::with(['satuanDasar', 'jenisBarang'])->findOrFail($barangId);

                // Cek ketersediaan stok fisik di Gudang pengeluaran
                $availableStock = (float) DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->where('deleted_st', false)
                    ->sum('sisa_qty');

                if ($qty > $availableStock) {
                    throw new Exception("Stok untuk '{$barang->barang_nm}' di {$gudang->gudang_nm} tidak mencukupi (Tersedia: " . number_format($availableStock, 2) . ", Diminta: " . number_format($qty, 2) . ").");
                }

                $itemBruto = round($qty * $hargaSatuan, 2);
                $diskonNominal = round(($itemBruto * $diskonPersen / 100) + $potonganItem, 2);
                $itemNetto = max(0, $itemBruto - $diskonNominal);

                $itemPpn = 0;
                if ($isPpn) {
                    $itemPpn = round($itemNetto * 0.11, 2);
                }
                $itemTagihan = $itemNetto + $itemPpn;

                $subtotalBruto += $itemBruto;
                $diskonTotal += $diskonNominal;
                $dppNominal += $itemNetto;
                $ppnNominal += $itemPpn;
                $totalTagihan += $itemTagihan;

                $calculatedItems[] = [
                    'barang_id'        => $barangId,
                    'barang'           => $barang,
                    'qty'              => $qty,
                    'harga_satuan'     => $hargaSatuan,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskonNominal,
                    'potongan_nominal' => $potonganItem,
                    'subtotal_netto'   => $itemNetto,
                    'ppn_tipe'         => $ppnTipe,
                    'ppn_persen'       => $ppnPersen,
                    'ppn_nominal'      => $itemPpn,
                    'subtotal_tagihan' => $itemTagihan,
                    'catatan_txt'      => $row['catatan_txt'] ?? null,
                ];
            }

            if (empty($calculatedItems)) {
                throw new Exception("Tidak ada item barang valid dengan kuantitas lebih dari 0.");
            }

            $potonganHeader = (float) ($data['potongan_nominal'] ?? 0);
            if ($potonganHeader > 0) {
                $totalTagihan = max(0, $totalTagihan - $potonganHeader);
            }

            $metodeBayar = strtoupper(trim($data['metode_bayar'] ?? 'TUNAI'));
            $namaPembeli = trim($data['nama_pembeli'] ?? '') ?: ($customer->customer_nm ?? 'Pelanggan Umum');
            $nopolKendaraan = trim($data['nopol_kendaraan'] ?? 'AMBIL SENDIRI');
            $customerPoNo = 'PENJUALAN BAHAN PENOLONG & BUMBU';
            $catatanTransaksi = "[BAHAN PENOLONG / BUMBU] Pembeli: {$namaPembeli} | Asal: {$gudang->gudang_nm}";
            if (!empty($data['catatan_txt'])) {
                $catatanTransaksi .= " | " . $data['catatan_txt'];
            }

            // 3. TAHAP 1: Simpan Sales Order (Status Langsung COMPLETED)
            $order = DatSoHdr::create([
                'so_no'              => $soNo,
                'faktur_no'          => $fakturNo,
                'surat_jalan_no'     => $suratJalanNo,
                'so_tgl'             => $soTgl,
                'customer_id'        => $customerId,
                'customer_po_no'     => $customerPoNo,
                'top_txt'            => $metodeBayar,
                'tgl_kirim_estimasi' => $soTgl,
                'tgl_kirim_aktual'   => $soTgl,
                'supir_nm'           => $namaPembeli,
                'nopol_kendaraan'    => $nopolKendaraan,
                'status_cd'          => 'COMPLETED',
                'catatan_txt'        => $catatanTransaksi,
                'subtotal_bruto'     => $subtotalBruto,
                'diskon_total'       => $diskonTotal,
                'potongan_nominal'   => $potonganHeader,
                'dpp_nominal'        => $dppNominal,
                'ppn_nominal'        => $ppnNominal,
                'ppn_tipe'           => $ppnTipe,
                'ppn_persen'         => $ppnPersen,
                'total_tagihan'      => $totalTagihan,
                'net_tagihan'        => $totalTagihan,
                'created_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'Kasir',
                'active_st'          => true,
                'deleted_st'         => false,
                'version_no'         => 1,
            ]);

            $soDetails = [];
            foreach ($calculatedItems as $item) {
                $dtl = DatSoDtl::create([
                    'so_id'            => $order->so_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $item['qty'],
                    'kirim_qty'        => $item['qty'],
                    'satuan'           => $item['barang']->satuanDasar?->satuan_nm ?? 'Pcs',
                    'harga_satuan'     => $item['harga_satuan'],
                    'diskon_persen'    => $item['diskon_persen'],
                    'diskon_nominal'   => $item['diskon_nominal'],
                    'potongan_nominal' => $item['potongan_nominal'],
                    'harga_netto'      => $item['subtotal_netto'] / ($item['qty'] ?: 1),
                    'subtotal_netto'   => $item['subtotal_netto'],
                    'ppn_tipe'         => $item['ppn_tipe'],
                    'ppn_persen'       => $item['ppn_persen'],
                    'ppn_nominal'      => $item['ppn_nominal'],
                    'subtotal_tagihan' => $item['subtotal_tagihan'],
                    'catatan_txt'      => $item['catatan_txt'],
                ]);
                $item['sodtl_id'] = $dtl->sodtl_id;
                $soDetails[] = $item;
            }

            // 4. TAHAP 2: Simpan Pengeluaran Barang (Surat Jalan) & Potong Stok FIFO
            $pengeluaran = DatPengeluaranHdr::create([
                'pengeluaran_no'  => $suratJalanNo,
                'pengeluaran_tgl' => $soTgl,
                'so_id'           => $order->so_id,
                'customer_id'     => $customerId,
                'gudang_id'       => $gudangId,
                'supir_nm'        => $namaPembeli,
                'nopol_kendaraan' => $nopolKendaraan,
                'ekspedisi_nm'    => 'Penyerahan Langsung Gudang Bahan',
                'total_qty'       => array_sum(array_column($calculatedItems, 'qty')),
                'status_cd'       => 'TERKIRIM',
                'catatan_txt'     => "Penyerahan bahan penolong/bumbu {$soNo} dari {$gudang->gudang_nm}",
                'created_by'      => $userName ?? auth()->user()?->nama_lengkap ?? 'Kasir',
                'active_st'       => true,
                'deleted_st'      => false,
                'version_no'      => 1,
            ]);

            $pengeluaranDetails = [];
            foreach ($soDetails as $item) {
                $ketStok = "Penjualan Bahan Penolong {$soNo} (SJ: {$suratJalanNo}) Pembeli: {$namaPembeli} Asal: {$gudang->gudang_nm}";
                $deductions = $this->stokService->deductStockFifo(
                    gudangId: $gudangId,
                    barangId: $item['barang_id'],
                    qty: $item['qty'],
                    refNo: $suratJalanNo,
                    keterangan: $ketStok,
                    userName: $userName ?? 'Kasir'
                );

                if (empty($deductions)) {
                    $pDtl = DatPengeluaranDtl::create([
                        'pengeluaran_id' => $pengeluaran->pengeluaran_id,
                        'sodtl_id'       => $item['sodtl_id'],
                        'barang_id'      => $item['barang_id'],
                        'batch_no'       => '-',
                        'kirim_qty'      => $item['qty'],
                        'catatan_txt'    => 'Penyerahan langsung',
                    ]);
                    $item['pengeluaran_dtl_id'] = $pDtl->pengeluaran_dtl_id;
                    $pengeluaranDetails[] = $item;
                } else {
                    foreach ($deductions as $deduct) {
                        $pDtl = DatPengeluaranDtl::create([
                            'pengeluaran_id' => $pengeluaran->pengeluaran_id,
                            'sodtl_id'       => $item['sodtl_id'],
                            'barang_id'      => $item['barang_id'],
                            'batch_no'       => $deduct['batch_no'] ?? '-',
                            'kirim_qty'      => $deduct['qty'],
                            'catatan_txt'    => "Potong batch {$deduct['batch_no']}",
                        ]);
                        $itemCopy = $item;
                        $itemCopy['pengeluaran_dtl_id'] = $pDtl->pengeluaran_dtl_id;
                        $itemCopy['batch_no'] = $deduct['batch_no'] ?? '-';
                        $pengeluaranDetails[] = $itemCopy;
                    }
                }
            }

            // 5. TAHAP 3: Terbitkan Faktur Penjualan Lunas
            $faktur = DatFakturHdr::create([
                'faktur_no'         => $fakturNo,
                'faktur_tgl'        => $soTgl,
                'so_id'             => $order->so_id,
                'pengeluaran_id'    => $pengeluaran->pengeluaran_id,
                'customer_id'       => $customerId,
                'subtotal_bruto'    => $subtotalBruto,
                'total_diskon'      => $diskonTotal,
                'total_potongan'    => $potonganHeader,
                'dpp_nominal'       => $dppNominal,
                'ppn_persen'        => $ppnPersen,
                'ppn_nominal'       => $ppnNominal,
                'total_tagihan'     => $totalTagihan,
                'total_bayar'       => $totalTagihan,
                'sisa_piutang'      => 0,
                'status_pembayaran' => 'LUNAS',
                'tgl_jatuh_tempo'   => $soTgl,
                'catatan_txt'       => "Faktur Lunas Penjualan Bahan Penolong / Bumbu ({$metodeBayar})",
                'created_by'        => $userName ?? auth()->user()?->nama_lengkap ?? 'Kasir',
                'active_st'         => true,
                'deleted_st'        => false,
                'version_no'        => 1,
            ]);

            foreach ($pengeluaranDetails as $pItem) {
                DatFakturDtl::create([
                    'faktur_id'          => $faktur->faktur_id,
                    'pengeluaran_dtl_id' => $pItem['pengeluaran_dtl_id'] ?? null,
                    'barang_id'          => $pItem['barang_id'],
                    'qty'                => $pItem['qty'],
                    'harga_satuan'       => $pItem['harga_satuan'],
                    'diskon_persen'      => $pItem['diskon_persen'],
                    'diskon_nominal'     => $pItem['diskon_nominal'],
                    'potongan_nominal'   => $pItem['potongan_nominal'],
                    'harga_netto'        => $pItem['subtotal_netto'] / ($pItem['qty'] ?: 1),
                    'subtotal_netto'     => $pItem['subtotal_netto'],
                    'ppn_persen'         => $pItem['ppn_persen'],
                    'ppn_nominal'        => $pItem['ppn_nominal'],
                    'subtotal_tagihan'   => $pItem['subtotal_tagihan'],
                    'batch_no'           => $pItem['batch_no'] ?? '-',
                ]);
            }

            return $order->load(['customer', 'details.barang.satuanDasar', 'details.barang.jenisBarang', 'pengeluaran', 'faktur']);
        });
    }
}
