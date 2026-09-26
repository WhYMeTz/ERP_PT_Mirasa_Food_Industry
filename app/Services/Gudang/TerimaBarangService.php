<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatTerimaDtl;
use App\Models\Gudang\DatTerimaHdr;
use App\Models\MasterData\MstBarang;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TerimaBarangService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil riwayat penerimaan barang dengan pagination.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, int|array|null $gudangId = null): LengthAwarePaginator
    {
        $query = DatTerimaHdr::with(['supplier', 'gudang', 'po', 'details.barang'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('terima_no', 'ILIKE', "%{$search}%")
                  ->orWhere('suratjalan_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('terima_tgl', 'desc')
            ->orderBy('terima_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil daftar per-item barang masuk sesuai Sheet "Barang Masuk" di Excel operasional:
     * Kolom: Tanggal | Kode Batch | Kode Barang | Nama Barang | Jenis | Keterangan | Qty Masuk | Satuan | Harga Satuan | Total Harga
     */
    public function getBarangMasukListPaginated(int $perPage = 25, ?string $search = null, int|array|null $gudangId = null): LengthAwarePaginator
    {
        $query = DatTerimaDtl::with(['header.supplier', 'header.gudang', 'barang.jenisBarang', 'barang.satuanDasar'])
            ->whereHas('header', function ($q) use ($gudangId) {
                $q->where('deleted_st', false);
                if (is_array($gudangId)) {
                    $q->whereIn('gudang_id', $gudangId);
                } elseif ($gudangId !== null) {
                    $q->where('gudang_id', $gudangId);
                }
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('header', function ($hq) use ($search) {
                      $hq->where('terima_no', 'ILIKE', "%{$search}%")
                         ->orWhere('suratjalan_no', 'ILIKE', "%{$search}%")
                         ->orWhereHas('supplier', function ($sq) use ($search) {
                             $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                         });
                  });
            });
        }

        return $query->orderBy('terimadtl_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil detail satu penerimaan barang beserta barang, batch, dan PO terkait.
     */
    public function getById(int $id): DatTerimaHdr
    {
        return DatTerimaHdr::with([
            'supplier',
            'gudang',
            'po',
            'details.barang.satuanDasar'
        ])->where('terima_id', $id)->firstOrFail();
    }

    /**
     * Memproses penerimaan fisik barang ke gudang via DB Transaction:
     * 1. Simpan Header & Detail Penerimaan
     * 2. Suntik stok fisik & kartu stok via StokService::addStock()
     * 3. Update progres kuantitas diterima & status PO (jika ada PO).
     */
    public function store(array $data): DatTerimaHdr
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang yang diterima.");
            }

            $terimaNo = !empty($data['terima_no']) ? trim($data['terima_no']) : $this->codeGenerator->generateTerimaNo();
            $gudangId = (int) $data['gudang_id'];
            $supplierId = (int) $data['supplier_id'];
            $poId = !empty($data['po_id']) ? (int) $data['po_id'] : null;

            // 1. Simpan Header Penerimaan
            $header = DatTerimaHdr::create([
                'terima_no'     => $terimaNo,
                'terima_tgl'    => $data['terima_tgl'] ?? date('Y-m-d'),
                'po_id'         => $poId,
                'supplier_id'   => $supplierId,
                'gudang_id'     => $gudangId,
                'suratjalan_no' => $data['suratjalan_no'] ?? null,
                'status_cd'     => 'COMPLETED',
                'catatan_txt'   => $data['catatan_txt'] ?? null,
            ]);

            // 2. Loop detail item & suntik stok
            foreach ($items as $item) {
                $barangId = (int) $item['barang_id'];
                $terimaQty = (float) $item['terima_qty'];
                $hargaNominal = (float) ($item['harga_nominal'] ?? 0);
                $expiredTgl = !empty($item['expired_tgl']) ? $item['expired_tgl'] : null;

                if ($terimaQty <= 0) {
                    continue; // Skip jika qty 0
                }

                // Ambil atau generate nomor batch sesuai format PT Mirasa ([INISIAL]-[DDMMYYYY]-[01])
                $batchNo = !empty($item['batch_no']) ? trim($item['batch_no']) : null;
                if (empty($batchNo)) {
                    $barang = MstBarang::find($barangId);
                    $batchNo = $this->codeGenerator->generateBatchNo(
                        $barang?->barang_cd ?? 'ITEM',
                        $data['terima_tgl'] ?? date('Y-m-d'),
                        $barang?->barang_nm
                    );
                }

                // Ambil data grading dan reject jika ada
                $gradeCd = !empty($item['grade_cd']) ? trim($item['grade_cd']) : null;
                $rejectQty = (float) ($item['reject_qty'] ?? 0);

                // Simpan detail penerimaan
                $dtl = DatTerimaDtl::create([
                    'terima_id'     => $header->terima_id,
                    'podtl_id'      => !empty($item['podtl_id']) ? (int) $item['podtl_id'] : null,
                    'barang_id'     => $barangId,
                    'batch_no'      => $batchNo,
                    'expired_tgl'   => $expiredTgl,
                    'terima_qty'    => $terimaQty,
                    'reject_qty'    => $rejectQty,
                    'grade_cd'      => $gradeCd,
                    'harga_nominal' => $hargaNominal,
                    'catatan_txt'   => $item['catatan_txt'] ?? null,
                ]);

                // Suntik stok fisik & kartu stok mutasi
                $this->stokService->addStock(
                    $gudangId,
                    $barangId,
                    $batchNo,
                    $terimaQty,
                    $expiredTgl,
                    $terimaNo,
                    "Penerimaan Barang Fisik No {$terimaNo} (SJ: " . ($header->suratjalan_no ?? '-') . ")",
                    $hargaNominal
                );

                // Update terima_qty di PO Detail jika barang ini terkait PO
                if (!empty($dtl->podtl_id)) {
                    $poDtl = DatPoDtl::find($dtl->podtl_id);
                    if ($poDtl) {
                        $poDtl->terima_qty = (float) $poDtl->terima_qty + $terimaQty;
                        $poDtl->save();
                    }
                }

                // Update harga beli acuan di Master Barang dengan harga terbaru dari invoice / surat jalan masuk
                if ($hargaNominal > 0) {
                    $barang = MstBarang::find($barangId);
                    if ($barang && (float) $barang->harga_beli_standar != $hargaNominal) {
                        $barang->harga_beli_standar = $hargaNominal;
                        $barang->save();
                    }
                }
            }

            // 3. Update status PO jika terkait
            if ($poId) {
                $poHdr = DatPoHdr::with('details')->find($poId);
                if ($poHdr) {
                    $semuaTuntas = true;
                    $adaYangDiterima = false;

                    foreach ($poHdr->details as $pdtl) {
                        if ((float) $pdtl->terima_qty < (float) $pdtl->pesan_qty) {
                            $semuaTuntas = false;
                        }
                        if ((float) $pdtl->terima_qty > 0) {
                            $adaYangDiterima = true;
                        }
                    }

                    if ($semuaTuntas) {
                        $poHdr->status_cd = 'COMPLETED';
                    } elseif ($adaYangDiterima) {
                        $poHdr->status_cd = 'PARTIAL';
                    }
                    $poHdr->save();
                }
            }

            return $header->fresh(['details.barang']);
        });
    }
}
