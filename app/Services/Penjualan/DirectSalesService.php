<?php

namespace App\Services\Penjualan;

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
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service Penjualan Langsung / Tunai (Direct Sales / Cash Sales).
 * Mengelola transaksi penjualan cepat tanpa dokumen PO customer:
 * 1-Klik selesai: SO Completed -> Pengeluaran Terkirim (Potong Stok FIFO) -> Faktur Lunas.
 */
class DirectSalesService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Dapatkan daftar penjualan langsung berpaginasi
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): LengthAwarePaginator {
        $query = DatSoHdr::with(['customer', 'details.barang.satuanDasar', 'pengeluaran', 'faktur'])
            ->where('deleted_st', false)
            ->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN LANGSUNG%')
                  ->orWhere('so_no', 'like', 'DIR-%');
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
     * KPI Ringkasan Metrik Penjualan Langsung
     */
    public function getMetrics(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $base = DatSoHdr::where('deleted_st', false)
            ->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN LANGSUNG%')
                  ->orWhere('so_no', 'like', 'DIR-%');
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
     * Dapatkan Customer Khusus Penjualan Umum (Otomatis dibuat jika belum ada)
     */
    public function getOrCreateCashCustomer(): MstCustomer
    {
        $cust = MstCustomer::where('customer_cd', 'CUST-UMUM')
            ->orWhere('customer_nm', 'ilike', '%PELANGGAN UMUM%')
            ->first();

        if (!$cust) {
            $cust = MstCustomer::create([
                'customer_cd' => 'CUST-UMUM',
                'customer_nm' => 'PELANGGAN UMUM',
                'kontak_no'   => '-',
                'alamat_txt'  => 'Transaksi Penjualan Langsung Pabrik',
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
     * Dapatkan daftar barang produk jadi siap jual beserta matriks stok aktif per gudang
     */
    public function getReadyToSellProducts(?int $defaultGudangId = null): Collection
    {
        $barangs = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->finishedGoods()
            ->active()
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
                // Default ke stok gudang GDG-FG (6) atau gudang pertama yang ada stok
                $b->ready_stock = (float) ($b->stocks_by_gudang[6] ?? ($b->stocks_by_gudang[1] ?? 0));
            }
        }

        return $barangs;
    }

    /**
     * Eksekusi Transaksi Penjualan Langsung / Tunai 1-Klik Selesai
     * Alur: Buat SO Completed -> Buat Pengeluaran Terkirim (Potong Stok FIFO) -> Buat Faktur Lunas
     */
    public function createDirectSale(array $data, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $soTgl = $data['tgl_transaksi'] ?? now()->toDateString();
            $soNo = $this->codeGenerator->generateDirectSaleNo($soTgl);
            $suratJalanNo = $this->codeGenerator->generateSuratJalanNo($soTgl);
            $fakturNo = $this->codeGenerator->generateFakturNo($soTgl);

            $gudangId = !empty($data['gudang_id']) ? (int) $data['gudang_id'] : (MstGudang::where('gudang_cd', 'MFI-PST')->value('gudang_id') ?? 1);
            $gudang = MstGudang::findOrFail($gudangId);

            $kategori = strtoupper(trim($data['kategori_penjualan'] ?? 'UMUM'));

            // 1. Validasi Customer & Parameter Berdasarkan Kategori
            if ($kategori === 'SALES') {
                $customerId = !empty($data['customer_id_sales']) ? (int) $data['customer_id_sales'] : (!empty($data['customer_id']) ? (int) $data['customer_id'] : null);
            } else {
                $customerId = !empty($data['customer_id']) ? (int) $data['customer_id'] : null;
            }

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

                if (($barang->jenisBarang->jenis_barang_cd ?? '') !== 'FG') {
                    throw new Exception("Hanya Produk Jadi (FG) yang dapat dijual pada Penjualan Langsung. '{$barang->barang_nm}' bukan produk FG.");
                }

                // Cek ketersediaan stok fisik di Gudang pengeluaran
                $availableStock = (float) DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->where('deleted_st', false)
                    ->sum('sisa_qty');

                if ($qty > $availableStock) {
                    throw new Exception("Stok untuk '{$barang->barang_nm}' di gudang {$gudang->gudang_nm} tidak mencukupi (Tersedia: {$availableStock}, Diminta: {$qty}).");
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
            $uangDiterima = (float) ($data['uang_diterima'] ?? $totalTagihan);
            $uangKembalian = max(0, $uangDiterima - $totalTagihan);

            if ($kategori === 'SALES') {
                $salesNm = trim($data['sales_nm'] ?? 'Sales Kanvaser');
                $rute = trim($data['rute_wilayah'] ?? '-');
                $armada = trim($data['armada_sales'] ?? 'ARMADA SALES');
                $tokoKanvas = trim($data['toko_kanvas_nm'] ?? '');
                $notaManual = trim($data['nota_manual_no'] ?? '');

                $namaPembeli = $tokoKanvas ? "{$tokoKanvas} (Sales: {$salesNm})" : "Sales: {$salesNm}";
                $nopolKendaraan = $armada ?: 'ARMADA SALES';
                $customerPoNo = 'PENJUALAN LANGSUNG - SALES';
                $catatanTransaksi = "[SALES KELILING] Sales: {$salesNm} | Rute: {$rute}";
                if (!empty($notaManual) && $notaManual !== '-') {
                    $catatanTransaksi .= " | Nota: {$notaManual}";
                }
                if (!empty($data['catatan_txt'])) {
                    $catatanTransaksi .= " | " . $data['catatan_txt'];
                }
            } else {
                $namaPembeli = trim($data['nama_pembeli'] ?? '') ?: ($customer->customer_nm ?? 'Pelanggan Umum');
                $nopolKendaraan = 'AMBIL SENDIRI';
                $customerPoNo = 'PENJUALAN LANGSUNG - UMUM';
                $catatanTransaksi = "[UMUM PABRIK] Pembeli: {$namaPembeli} | Gudang: {$gudang->gudang_nm}";
                if (!empty($data['catatan_txt'])) {
                    $catatanTransaksi .= " | " . $data['catatan_txt'];
                }
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
                'ppn_tipe'           => $ppnTipe,
                'ppn_persen'         => $ppnPersen,
                'ppn_nominal'        => $ppnNominal,
                'total_tagihan'      => $totalTagihan,
                'total_terkirim_qty' => array_sum(array_column($calculatedItems, 'qty')),
                'created_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'Admin Penjualan',
                'active_st'          => true,
                'deleted_st'         => false,
            ]);

            $soDetails = [];
            foreach ($calculatedItems as $item) {
                $dtl = DatSoDtl::create([
                    'so_id'            => $order->so_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $item['qty'],
                    'kirim_qty'        => $item['qty'],
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
                'nopol_kendaraan' => $data['nopol_kendaraan'] ?? ($nopolKendaraan ?: 'AMBIL SENDIRI'),
                'ekspedisi_nm'    => $kategori === 'SALES' ? 'Armada Sales Pabrik' : 'Ambil Langsung di Pabrik',
                'total_qty'       => array_sum(array_column($calculatedItems, 'qty')),
                'status_cd'       => 'TERKIRIM',
                'catatan_txt'     => "Penyerahan barang langsung transaksi {$soNo} dari {$gudang->gudang_nm}",
                'created_by'      => $userName ?? auth()->user()?->nama_lengkap ?? 'Kasir',
                'active_st'       => true,
                'deleted_st'      => false,
                'version_no'      => 1,
            ]);

            $pengeluaranDetails = [];
            foreach ($soDetails as $item) {
                // Eksekusi potong stok FEFO/FIFO dari Gudang pengeluaran
                $ketStok = "Penjualan Langsung {$soNo} (SJ: {$suratJalanNo}) Pembeli: {$namaPembeli} Gudang: {$gudang->gudang_nm}";
                $deductions = $this->stokService->deductStockFifo(
                    gudangId: $gudangId,
                    barangId: $item['barang_id'],
                    qty: $item['qty'],
                    refNo: $suratJalanNo,
                    keterangan: $ketStok,
                    userName: $userName ?? 'Kasir'
                );

                if (empty($deductions)) {
                    // Fallback create detail jika deduct batch tunggal
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

            // 5. TAHAP 3: Terbitkan Faktur Penjualan Lunas (Status LUNAS, Piutang = 0)
            $faktur = DatFakturHdr::create([
                'faktur_no'         => $fakturNo,
                'faktur_tgl'        => $soTgl,
                'tgl_jatuh_tempo'   => $soTgl,
                'pengeluaran_id'    => $pengeluaran->pengeluaran_id,
                'so_id'             => $order->so_id,
                'customer_id'       => $customerId,
                'customer_po_no'    => 'PENJUALAN LANGSUNG',
                'top_txt'           => $metodeBayar,
                'ppn_tipe'          => $ppnTipe,
                'ppn_persen'        => $ppnPersen,
                'subtotal_bruto'    => $subtotalBruto,
                'total_diskon'      => $diskonTotal,
                'total_potongan'    => $potonganHeader,
                'dpp_nominal'       => $dppNominal,
                'ppn_nominal'       => $ppnNominal,
                'total_tagihan'     => $totalTagihan,
                'total_dibayar'     => $totalTagihan,
                'sisa_piutang'      => 0,
                'status_pembayaran' => 'LUNAS',
                'catatan_txt'       => "Pembayaran {$metodeBayar} lunas saat transaksi. Uang Diterima: Rp " . number_format($uangDiterima, 0, ',', '.') . ", Kembalian: Rp " . number_format($uangKembalian, 0, ',', '.'),
                'created_by'        => $userName ?? auth()->user()?->nama_lengkap ?? 'Kasir',
                'active_st'         => true,
                'deleted_st'        => false,
                'version_no'        => 1,
            ]);

            foreach ($pengeluaranDetails as $pItem) {
                DatFakturDtl::create([
                    'faktur_id'          => $faktur->faktur_id,
                    'pengeluaran_dtl_id' => $pItem['pengeluaran_dtl_id'] ?? null,
                    'sodtl_id'           => $pItem['sodtl_id'] ?? null,
                    'barang_id'          => $pItem['barang_id'],
                    'barang_nm'          => $pItem['barang']->barang_nm ?? "Barang #{$pItem['barang_id']}",
                    'qty'                => $pItem['qty'],
                    'satuan'             => $pItem['barang']->satuanDasar?->satuan_nm ?? 'Karton',
                    'harga_satuan'       => $pItem['harga_satuan'],
                    'diskon_nominal'     => $pItem['diskon_nominal'],
                    'subtotal_netto'     => $pItem['subtotal_netto'],
                    'ppn_nominal'        => $pItem['ppn_nominal'],
                    'total_nominal'      => $pItem['subtotal_tagihan'],
                    'catatan_txt'        => $pItem['catatan_txt'],
                ]);
            }

            return $order->load(['customer', 'details.barang.satuanDasar', 'pengeluaran', 'faktur']);
        });
    }

    /**
     * Dapatkan detail lengkap satu transaksi penjualan langsung
     */
    public function getById(int $id): DatSoHdr
    {
        return DatSoHdr::with([
            'customer',
            'details.barang.satuanDasar',
            'pengeluaran.details',
            'faktur.details',
        ])
        ->where('deleted_st', false)
        ->findOrFail($id);
    }
}
