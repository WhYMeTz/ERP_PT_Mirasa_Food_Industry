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
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar data Pengeluaran Barang / Surat Jalan dengan pagination dan filter.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
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
            $query->where('status_cd', strtoupper(trim($status)));
        }

        if (!empty($dateFrom)) {
            $query->whereDate('pengeluaran_tgl', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('pengeluaran_tgl', '<=', $dateTo);
        }

        return $query->paginate($perPage);
    }

    /**
     * Hitung metrik operasional Surat Jalan harian.
     */
    public function getMetrics(): array
    {
        $today = now()->toDateString();
        $base = DatPengeluaranHdr::where('deleted_st', false);

        $totalDokumen = (clone $base)->count();
        $kirimHariIni = (clone $base)->where('pengeluaran_tgl', $today)->count();
        $totalQtyKirim = (float) (clone $base)->where('status_cd', '!=', 'BATAL')->sum('total_qty');
        $dalamPerjalanan = (clone $base)->where('status_cd', 'TERKIRIM')->count();
        $selesaiTiba = (clone $base)->where('status_cd', 'SAMPAI_TUJUAN')->count();

        // PO yang siap dikirim (Approved tapi belum selesai kirim)
        $readyOrdersCount = DatSoHdr::where('deleted_st', false)
            ->whereIn('status_cd', ['APPROVED', 'PROCESSING', 'PARTIAL'])
            ->count();

        return [
            'total_dokumen'      => $totalDokumen,
            'kirim_hari_ini'     => $kirimHariIni,
            'total_qty_kirim'    => $totalQtyKirim,
            'dalam_perjalanan'   => $dalamPerjalanan,
            'selesai_tiba'       => $selesaiTiba,
            'ready_orders_count' => $readyOrdersCount,
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

        $gudangFgId = MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;

        $items = [];
        foreach ($order->details as $dtl) {
            $pesanQty = (float) $dtl->pesan_qty;
            $kirimQty = (float) $dtl->kirim_qty;
            $sisaQty = max(0, $pesanQty - $kirimQty);

            // Cek stok fisik aktual di Gudang FG
            $stokTersedia = (float) \App\Models\Gudang\DatStokBatch::where('gudang_id', $gudangFgId)
                ->where('barang_id', $dtl->barang_id)
                ->sum('sisa_qty');

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
            $suratJalanNo = !empty($data['pengeluaran_no'])
                ? $data['pengeluaran_no']
                : $this->codeGenerator->generateSuratJalanNo($tglKirim);

            // Buat header pengeluaran
            $header = DatPengeluaranHdr::create([
                'pengeluaran_no'  => $suratJalanNo,
                'pengeluaran_tgl' => $tglKirim,
                'so_id'           => $order->so_id,
                'customer_id'     => $order->customer_id,
                'gudang_id'       => $gudangFgId,
                'supir_nm'        => $data['supir_nm'] ?? null,
                'nopol_kendaraan' => $data['nopol_kendaraan'] ?? null,
                'ekspedisi_nm'    => $data['ekspedisi_nm'] ?? null,
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

                // Eksekusi pemotongan stok FEFO dari Gudang FG
                try {
                    $deductions = $this->stokService->deductStockFifo(
                        gudangId: $gudangFgId,
                        barangId: $soDtl->barang_id,
                        totalQty: $qtyKirim,
                        dokumenNo: $suratJalanNo,
                        keterangan: $ketStok
                    );
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

            Log::info("Surat Jalan {$suratJalanNo} (ID: {$header->pengeluaran_id}) berhasil diterbitkan untuk SO {$order->so_no} oleh " . ($userName ?? 'System'));

            return $header;
        });
    }

    /**
     * Membatalkan dokumen Pengeluaran Barang (Void Surat Jalan) & mengembalikan stok ke Gudang FG.
     */
    public function voidPengeluaran(int $pengeluaranId, string $reason, ?string $userName = null): DatPengeluaranHdr
    {
        return DB::transaction(function () use ($pengeluaranId, $reason, $userName) {
            $header = DatPengeluaranHdr::with(['details', 'order.details'])->lockForUpdate()->findOrFail($pengeluaranId);

            if ($header->status_cd === 'BATAL') {
                throw new Exception("Surat Jalan {$header->pengeluaran_no} sudah dalam status Dibatalkan (BATAL).");
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
