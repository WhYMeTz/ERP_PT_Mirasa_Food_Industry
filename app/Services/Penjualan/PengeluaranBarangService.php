<?php

namespace App\Services\Penjualan;

use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatPengeluaranDtl;
use App\Models\Penjualan\DatPengeluaranHdr;
use App\Models\Penjualan\DatSoDtl;
use App\Models\Penjualan\DatSoHdr;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengeluaranBarangService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator,
        protected FakturPenjualanService $fakturService
    ) {}

    /**
     * Mengambil daftar data Pengeluaran Barang / Surat Jalan dengan pagination dan filter.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        ?string $date = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): LengthAwarePaginator {
        $query = DatPengeluaranHdr::with(['order', 'customer', 'gudang', 'details.barang', 'faktur'])
            ->where('deleted_st', false)
            ->orderBy('pengeluaran_tgl', 'desc')
            ->orderBy('pengeluaran_id', 'desc');

        if (!empty($search)) {
            $searchTrim = trim($search);
            $query->where(function ($q) use ($searchTrim) {
                $q->where('pengeluaran_no', 'ilike', "%{$searchTrim}%")
                  ->orWhere('supir_nm', 'ilike', "%{$searchTrim}%")
                  ->orWhere('nopol_kendaraan', 'ilike', "%{$searchTrim}%")
                  ->orWhere('ekspedisi_nm', 'ilike', "%{$searchTrim}%")
                  ->orWhereHas('order', function ($sub) use ($searchTrim) {
                      $sub->where('so_no', 'ilike', "%{$searchTrim}%")
                          ->orWhere('customer_po_no', 'ilike', "%{$searchTrim}%");
                  })
                  ->orWhereHas('customer', function ($sub) use ($searchTrim) {
                      $sub->where('customer_nm', 'ilike', "%{$searchTrim}%")
                          ->orWhere('customer_cd', 'ilike', "%{$searchTrim}%");
                  });
            });
        }

        if (!empty($status)) {
            $statusUpper = strtoupper(trim($status));
            if ($statusUpper === 'DIKELUARKAN') {
                $query->where('status_cd', '!=', 'BATAL');
            } else {
                $query->where('status_cd', $statusUpper);
            }
        }

        if (!empty($date)) {
            $query->whereDate('pengeluaran_tgl', $date);
        } else {
            if (!empty($dateFrom)) {
                $query->whereDate('pengeluaran_tgl', '>=', $dateFrom);
            }
            if (!empty($dateTo)) {
                $query->whereDate('pengeluaran_tgl', '<=', $dateTo);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Hitung metrik operasional Surat Jalan harian.
     */
    public function getMetrics(): array
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $base = DatPengeluaranHdr::where('deleted_st', false);

        // 1. Antrean PO menunggu pengeluaran fisik (Gudang FG)
        $readyOrdersCount = DatSoHdr::where('deleted_st', false)
            ->whereIn('status_cd', ['APPROVED', 'PROCESSING', 'PARTIAL'])
            ->count();

        // 2. Dokumen pengiriman diberangkatkan hari ini (non-batal)
        $kirimHariIni = (clone $base)->where('pengeluaran_tgl', $today)
            ->where('status_cd', '!=', 'BATAL')
            ->count();

        // 3. Fisik karton keluar hari ini
        $qtyHariIni = (float) (clone $base)->where('pengeluaran_tgl', $today)
            ->where('status_cd', '!=', 'BATAL')
            ->sum('total_qty');

        // 4. Akumulasi pengeluaran bulan ini (Karton & Dokumen)
        $qtyBulanIni = (float) (clone $base)->whereBetween('pengeluaran_tgl', [$startOfMonth, $endOfMonth])
            ->where('status_cd', '!=', 'BATAL')
            ->sum('total_qty');
        $sjBulanIni = (clone $base)->whereBetween('pengeluaran_tgl', [$startOfMonth, $endOfMonth])
            ->where('status_cd', '!=', 'BATAL')
            ->count();

        $totalDokumen = (clone $base)->count();

        return [
            'ready_orders_count' => $readyOrdersCount,
            'kirim_hari_ini'     => $kirimHariIni,
            'qty_hari_ini'       => $qtyHariIni,
            'qty_bulan_ini'      => $qtyBulanIni,
            'sj_bulan_ini'       => $sjBulanIni,
            'bulan_nama'         => now()->translatedFormat('F Y'),
            'total_dokumen'      => $totalDokumen,
        ];
    }

    /**
     * Mengambil daftar PO Penjualan yang siap untuk dikeluarkan barangnya dari Gudang FG.
     */
    public function getReadyToShipOrders(): Collection
    {
        return DatSoHdr::with(['customer', 'details.barang'])
            ->where('deleted_st', false)
            ->whereIn('status_cd', ['APPROVED', 'PROCESSING', 'PARTIAL'])
            ->orderBy('so_tgl', 'desc')
            ->orderBy('so_id', 'desc')
            ->get();
    }

    /**
     * Mengambil rincian PO untuk form Surat Jalan (termasuk cek stok riil di Gudang FG).
     */
    public function getSoDetailForShipping(int $soId): array
    {
        $order = DatSoHdr::with(['customer', 'details.barang.satuanDasar'])
            ->where('deleted_st', false)
            ->findOrFail($soId);

        $fgGudangIds = MstGudang::where('gudang_cd', 'GDG-FG')
            ->orWhere('tipe_gudang_cd', 'FG')
            ->orWhere('gudang_cd', 'MFI-PST')
            ->pluck('gudang_id')
            ->toArray();
        if (empty($fgGudangIds)) {
            $fgGudangIds = [6, 1];
        }

        $gudangFgId = $fgGudangIds[0] ?? 6;

        $items = [];
        foreach ($order->details as $dtl) {
            $pesanQty = (float) $dtl->pesan_qty;
            $kirimQty = (float) $dtl->kirim_qty;
            $sisaQty = max(0, $pesanQty - $kirimQty);

            // Cek stok fisik aktual di Gudang FG & Pusat
            $rawBatches = \App\Models\Gudang\DatStokBatch::whereIn('gudang_id', $fgGudangIds)
                ->where('barang_id', $dtl->barang_id)
                ->where('sisa_qty', '>', 0)
                ->orderByRaw('expired_tgl ASC NULLS LAST')
                ->orderBy('batch_id', 'asc')
                ->get();

            $stokTersedia = (float) $rawBatches->sum('sisa_qty');

            $batchList = [];
            $firstBatch = true;
            foreach ($rawBatches as $rb) {
                $packTgl = \App\Models\Produksi\DatPackagingHdr::where('batch_no', $rb->batch_no)
                    ->where('barang_jadi_id', $dtl->barang_id)
                    ->value('pack_tgl');

                $batchList[] = [
                    'batch_id'    => $rb->batch_id,
                    'batch_no'    => $rb->batch_no,
                    'sisa_qty'    => (float) $rb->sisa_qty,
                    'expired_tgl' => $rb->expired_tgl ? \Carbon\Carbon::parse($rb->expired_tgl)->format('Y-m-d') : null,
                    'expired_fmt' => $rb->expired_tgl ? \Carbon\Carbon::parse($rb->expired_tgl)->format('d/m/Y') : '-',
                    'pack_tgl'    => $packTgl ? \Carbon\Carbon::parse($packTgl)->format('d/m/Y') : '-',
                    'gudang_id'   => $rb->gudang_id,
                    'is_fifo_top' => $firstBatch,
                ];
                $firstBatch = false;
            }

            $items[] = [
                'sodtl_id'      => $dtl->sodtl_id,
                'barang_id'     => $dtl->barang_id,
                'barang_cd'     => $dtl->barang?->barang_cd ?? '-',
                'barang_nm'     => $dtl->barang?->barang_nm ?? "Barang #{$dtl->barang_id}",
                'satuan_nm'     => $dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton',
                'pesan_qty'     => $pesanQty,
                'kirim_qty_old' => $kirimQty,
                'sisa_qty'      => $sisaQty,
                'stok_fg'       => $stokTersedia,
                'is_sufficient' => $stokTersedia >= $sisaQty,
                'batches'       => $batchList,
            ];
        }

        return [
            'order'        => $order,
            'customer'     => $order->customer,
            'items'        => $items,
            'gudang_fg_id' => $gudangFgId,
        ];
    }

    /**
     * Membuat dokumen Pengeluaran Barang (Surat Jalan Outbound) & memotong stok FEFO di Gudang FG.
     */
    public function createPengeluaran(array $data, ?string $userName = null): DatPengeluaranHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $order = DatSoHdr::with(['details.barang', 'customer'])->lockForUpdate()->findOrFail($data['so_id']);

            if (!in_array($order->status_cd, ['APPROVED', 'PROCESSING', 'PARTIAL'])) {
                throw new Exception("Dokumen PO {$order->so_no} tidak dapat dikirim karena statusnya {$order->status_cd}.");
            }

            $gudangFgId = MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;
            $tglKirim = $data['pengeluaran_tgl'] ?? now()->toDateString();
            $suratJalanNo = !empty($order->surat_jalan_no)
                ? $order->surat_jalan_no
                : (!empty($data['pengeluaran_no'])
                    ? $data['pengeluaran_no']
                    : $this->codeGenerator->generateSuratJalanNo($tglKirim));

            // Buat header pengeluaran
            $header = DatPengeluaranHdr::create([
                'pengeluaran_no'  => $suratJalanNo,
                'pengeluaran_tgl' => $tglKirim,
                'so_id'           => $order->so_id,
                'customer_id'     => $order->customer_id,
                'gudang_id'       => $gudangFgId,
                'supir_nm'        => !empty($data['supir_nm']) ? $data['supir_nm'] : $order->supir_nm,
                'nopol_kendaraan' => !empty($data['nopol_kendaraan']) ? $data['nopol_kendaraan'] : $order->nopol_kendaraan,
                'ekspedisi_nm'    => $data['ekspedisi_nm'] ?? 'Truk Box MFI',
                'total_qty'       => 0,
                'status_cd'       => 'TERKIRIM',
                'catatan_txt'     => $data['catatan_txt'] ?? null,
                'created_by'      => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'active_st'       => true,
                'deleted_st'      => false,
                'version_no'      => 1,
            ]);

            $totalKirimQtyHeader = 0;
            $itemsInput = $data['items'] ?? [];

            foreach ($itemsInput as $itemRow) {
                $sodtlId = (int) $itemRow['sodtl_id'];
                $qtyKirim = (float) ($itemRow['kirim_qty'] ?? 0);

                if ($qtyKirim <= 0) {
                    continue; // Lewati item jika qty kirim 0
                }

                $soDtl = DatSoDtl::with('barang')->where('sodtl_id', $sodtlId)->where('so_id', $order->so_id)->firstOrFail();
                $sisaBolehKirim = (float) $soDtl->pesan_qty - (float) $soDtl->kirim_qty;

                if ($qtyKirim > $sisaBolehKirim) {
                    $barangNm = $soDtl->barang?->barang_nm ?? "Item #{$soDtl->barang_id}";
                    throw new Exception("Kuantitas kirim untuk '{$barangNm}' ({$qtyKirim}) melebihi sisa pesanan ({$sisaBolehKirim}).");
                }

                $barangNm = $soDtl->barang?->barang_nm ?? "Barang ID {$soDtl->barang_id}";
                $ketStok = "Pengeluaran Surat Jalan {$suratJalanNo} (PO: {$order->so_no}) ke " . ($order->customer?->customer_nm ?? 'Customer');

                $selectedBatchNo = !empty($itemRow['batch_no']) && $itemRow['batch_no'] !== 'AUTO_FIFO' ? trim($itemRow['batch_no']) : null;

                try {
                    if ($selectedBatchNo) {
                        // User memilih batch tertentu secara manual (Override FIFO)
                        $targetBatch = \App\Models\Gudang\DatStokBatch::whereIn('gudang_id', $fgGudangIds)
                            ->where('barang_id', $soDtl->barang_id)
                            ->where('batch_no', $selectedBatchNo)
                            ->where('sisa_qty', '>', 0)
                            ->first();

                        $actualGudangId = $targetBatch ? $targetBatch->gudang_id : $gudangFgId;

                        $this->stokService->deductStock(
                            gudangId: $actualGudangId,
                            barangId: $soDtl->barang_id,
                            batchNo: $selectedBatchNo,
                            qty: $qtyKirim,
                            dokumenNo: $suratJalanNo,
                            keterangan: $ketStok
                        );

                        $deductions = [
                            [
                                'batch_no'     => $selectedBatchNo,
                                'qty_deducted' => $qtyKirim,
                            ]
                        ];
                    } else {
                        // Eksekusi pemotongan stok FEFO otomatis dari Gudang FG
                        $deductions = $this->stokService->deductStockFifo(
                            gudangId: $gudangFgId,
                            barangId: $soDtl->barang_id,
                            totalQty: $qtyKirim,
                            dokumenNo: $suratJalanNo,
                            keterangan: $ketStok
                        );
                    }
                } catch (Exception $e) {
                    throw new Exception("Gagal memotong stok Gudang FG untuk '{$barangNm}': {$e->getMessage()}");
                }

                // Catat rincian alokasi batch
                foreach ($deductions as $deduct) {
                    DatPengeluaranDtl::create([
                        'pengeluaran_id' => $header->pengeluaran_id,
                        'sodtl_id'       => $soDtl->sodtl_id,
                        'barang_id'      => $soDtl->barang_id,
                        'batch_no'       => $deduct['batch_no'] ?? null,
                        'kirim_qty'      => $deduct['qty_deducted'],
                        'catatan_txt'    => $itemRow['catatan_txt'] ?? null,
                    ]);
                }

                // Update akumulasi kirim_qty pada baris detail SO
                $soDtl->update([
                    'kirim_qty' => (float) $soDtl->kirim_qty + $qtyKirim,
                ]);

                $totalKirimQtyHeader += $qtyKirim;
            }

            if ($totalKirimQtyHeader <= 0) {
                throw new Exception("Kuantitas barang yang dikirim tidak boleh kosong (minimal 1 item memiliki qty > 0).");
            }

            // Update total qty pada header Surat Jalan
            $header->update(['total_qty' => $totalKirimQtyHeader]);

            // Cek apakah seluruh item di SO sudah terkirim penuh
            $order->load('details');
            $allCompleted = true;
            $totalAkumulasiTerkirim = 0;

            foreach ($order->details as $dtl) {
                $totalAkumulasiTerkirim += (float) $dtl->kirim_qty;
                if ((float) $dtl->kirim_qty < (float) $dtl->pesan_qty) {
                    $allCompleted = false;
                }
            }

            $order->update([
                'status_cd'          => $allCompleted ? 'COMPLETED' : 'PARTIAL',
                'surat_jalan_no'     => $suratJalanNo,
                'tgl_kirim_aktual'   => $tglKirim,
                'supir_nm'           => $data['supir_nm'] ?? $order->supir_nm,
                'nopol_kendaraan'    => $data['nopol_kendaraan'] ?? $order->nopol_kendaraan,
                'total_terkirim_qty' => $totalAkumulasiTerkirim,
                'updated_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'version_no'         => $order->version_no + 1,
            ]);

            // Terbitkan Faktur Penjualan komersial secara otomatis berbarengan dengan terbitnya Surat Jalan
            $header->load(['customer', 'order.details', 'details.orderDetail', 'details.barang.satuanDasar']);
            $this->fakturService->autoCreateFromPengeluaran($header, $userName);

            Log::info("Surat Jalan {$suratJalanNo} (ID: {$header->pengeluaran_id}) dan Faktur Penjualan otomatis berhasil diterbitkan untuk SO {$order->so_no} oleh " . ($userName ?? 'System'));

            return $header;
        });
    }

    /**
     * Membatalkan dokumen Pengeluaran Barang (Void Surat Jalan) & mengembalikan stok ke Gudang FG.
     */
    public function voidPengeluaran(int $pengeluaranId, string $reason, ?string $userName = null): DatPengeluaranHdr
    {
        return DB::transaction(function () use ($pengeluaranId, $reason, $userName) {
            $header = DatPengeluaranHdr::with(['details', 'order.details', 'faktur'])->lockForUpdate()->findOrFail($pengeluaranId);

            if ($header->status_cd === 'BATAL') {
                throw new Exception("Surat Jalan {$header->pengeluaran_no} sudah dalam status Dibatalkan (BATAL).");
            }

            // Validasi Keuangan: Jika Faktur Penjualan sudah terbit
            if ($header->faktur && $header->faktur->status_pembayaran !== 'BATAL' && !$header->faktur->deleted_st) {
                // Jika sudah ada pembayaran yang tercatat, cegah pembatalan untuk menjaga integritas kas
                if ((float) $header->faktur->total_dibayar > 0) {
                    throw new Exception("Surat Jalan {$header->pengeluaran_no} tidak dapat dibatalkan karena Faktur Penjualan ({$header->faktur->faktur_no}) sudah memiliki pembayaran piutang sebesar Rp " . number_format($header->faktur->total_dibayar, 0, ',', '.') . ". Harap batalkan/reversikan data pembayaran terlebih dahulu.");
                }

                // Jika belum ada pembayaran (total_dibayar == 0), batalkan faktur otomatis bersamaan dengan Surat Jalan
                $header->faktur->update([
                    'status_pembayaran' => 'BATAL',
                    'catatan_txt'       => ($header->faktur->catatan_txt ? $header->faktur->catatan_txt . "\n" : '') . "[DIBATALKAN OTOMATIS BERSAMAAN SURAT JALAN]: " . $reason,
                    'updated_by'        => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                    'version_no'        => $header->faktur->version_no + 1,
                ]);

                Log::info("Faktur Penjualan {$header->faktur->faktur_no} otomatis dibatalkan bersamaan dengan pembatalan Surat Jalan {$header->pengeluaran_no}");
            }

            $gudangFgId = $header->gudang_id;
            $order = $header->order;

            // 1. Kembalikan stok fisik ke Gudang FG untuk setiap baris detail
            foreach ($header->details as $dtl) {
                $qtyToRestore = (float) $dtl->kirim_qty;
                if ($qtyToRestore > 0) {
                    $this->stokService->addStock(
                        gudangId: $gudangFgId,
                        barangId: $dtl->barang_id,
                        batchNo: $dtl->batch_no ?: 'RESTORE-SJ',
                        qty: $qtyToRestore,
                        expiredTgl: null,
                        dokumenNo: $header->pengeluaran_no,
                        keterangan: "Pembatalan Surat Jalan ({$header->pengeluaran_no}): {$reason}",
                        hargaSatuan: 0
                    );

                    // Reversikan akumulasi kirim_qty pada baris detail SO
                    $soDtl = DatSoDtl::find($dtl->sodtl_id);
                    if ($soDtl) {
                        $newKirim = max(0, (float) $soDtl->kirim_qty - $qtyToRestore);
                        $soDtl->update(['kirim_qty' => $newKirim]);
                    }
                }
            }

            // 2. Perbarui status Surat Jalan menjadi BATAL
            $headerNote = $header->catatan_txt
                ? $header->catatan_txt . "\n[DIBATALKAN]: " . $reason
                : "[DIBATALKAN]: " . $reason;

            $header->update([
                'status_cd'   => 'BATAL',
                'catatan_txt' => $headerNote,
                'updated_by'  => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'version_no'  => $header->version_no + 1,
            ]);

            // 3. Evaluasi kembali status Dokumen SO
            if ($order) {
                $order->load('details');
                $totalKirimSisa = 0;
                foreach ($order->details as $d) {
                    $totalKirimSisa += (float) $d->kirim_qty;
                }

                $newSoStatus = 'APPROVED';
                if ($totalKirimSisa > 0) {
                    $newSoStatus = 'PARTIAL';
                }

                $order->update([
                    'status_cd'          => $newSoStatus,
                    'total_terkirim_qty' => $totalKirimSisa,
                    'updated_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                    'version_no'         => $order->version_no + 1,
                ]);
            }

            return $header;
        });
    }

    /**
     * Dapatkan dokumen Pengeluaran Barang berdasarkan ID.
     */
    public function getById(int $pengeluaranId): DatPengeluaranHdr
    {
        return DatPengeluaranHdr::with([
            'order.details.barang.satuanDasar',
            'customer',
            'gudang',
            'details.barang.satuanDasar',
            'details.orderDetail',
            'faktur'
        ])->findOrFail($pengeluaranId);
    }
}
