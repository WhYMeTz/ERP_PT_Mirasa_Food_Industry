<?php

namespace App\Services\Produksi;

use App\Models\Produksi\MstBomDtl;
use App\Models\Produksi\MstBomHdr;
use App\Services\Common\CodeGeneratorService;
use App\Models\Gudang\DatPakaiDtl;
use App\Models\Gudang\DatStokBatch;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BomService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar resep BOM terpaginasi
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('bom_no', 'ILIKE', "%{$search}%")
                  ->orWhere('bom_nm', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barangJadi', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('bom_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil seluruh resep aktif
     */
    public function getAllActive(): Collection
    {
        return MstBomHdr::with('barangJadi')->active()->orderBy('bom_nm')->get();
    }

    /**
     * Mengambil data resep berdasarkan ID
     */
    public function getById(int $id): MstBomHdr
    {
        return MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])->where('bom_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan Formula Resep (BOM) baru beserta rincian bahannya
     */
    public function store(array $data): MstBomHdr
    {
        return DB::transaction(function () use ($data) {
            $bomNo = !empty($data['bom_no']) ? trim($data['bom_no']) : $this->codeGenerator->generate('mst_bom_hdr', 'bom_no', 'BOM-');

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 bahan baku / penolong dalam resep BOM.");
            }

            $header = MstBomHdr::create([
                'bom_no'           => $bomNo,
                'bom_nm'           => $data['bom_nm'],
                'barang_jadi_id'   => $data['barang_jadi_id'],
                'batch_ukuran_qty' => (float) ($data['batch_ukuran_qty'] ?? 1),
                'catatan_txt'      => $data['catatan_txt'] ?? null,
            ]);

            foreach ($items as $item) {
                MstBomDtl::create([
                    'bom_id'           => $header->bom_id,
                    'barang_mentah_id' => $item['barang_mentah_id'],
                    'kebutuhan_qty'    => (float) $item['kebutuhan_qty'],
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);
            }

            return $header->fresh(['barangJadi', 'details.barangMentah']);
        });
    }

    /**
     * Memperbarui formula resep BOM
     */
    public function update(int $id, array $data): MstBomHdr
    {
        return DB::transaction(function () use ($id, $data) {
            $header = MstBomHdr::findOrFail($id);

            $header->update([
                'bom_nm'           => $data['bom_nm'],
                'barang_jadi_id'   => $data['barang_jadi_id'],
                'batch_ukuran_qty' => (float) ($data['batch_ukuran_qty'] ?? 1),
                'catatan_txt'      => $data['catatan_txt'] ?? null,
            ]);

            if (isset($data['items'])) {
                $header->details()->delete();
                foreach ($data['items'] as $item) {
                    MstBomDtl::create([
                        'bom_id'           => $header->bom_id,
                        'barang_mentah_id' => $item['barang_mentah_id'],
                        'kebutuhan_qty'    => (float) $item['kebutuhan_qty'],
                        'catatan_txt'      => $item['catatan_txt'] ?? null,
                    ]);
                }
            }

            return $header->fresh(['barangJadi', 'details.barangMentah']);
        });
    }

    /**
     * Menghapus formula resep (Soft Delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $bom = MstBomHdr::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $bom->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }

    /**
     * Kalkulasi kebutuhan bahan baku secara proporsional berdasarkan target kuantitas produksi.
     * Formula: (Target Qty / Batch Ukuran Qty) * Kebutuhan Qty per Batch
     */
    public function kalkulasiKebutuhanBahan(int $barangJadiId, float $targetQty, ?int $bomId = null): array
    {
        $query = MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])->active();

        if ($bomId) {
            $bom = $query->where('bom_id', $bomId)->first();
        } else {
            $bom = $query->where('barang_jadi_id', $barangJadiId)->first();
        }

        if (!$bom) {
            throw new Exception("Formula Resep (BOM) untuk produk target tidak ditemukan. Harap buat resep terlebih dahulu.");
        }

        if ((float) $bom->batch_ukuran_qty <= 0) {
            throw new Exception("Ukuran batch standar pada resep {$bom->bom_no} tidak valid (harus > 0).");
        }

        $rasio = $targetQty / (float) $bom->batch_ukuran_qty;
        $bahanList = [];

        foreach ($bom->details as $dtl) {
            $kebutuhanTotal = (float) $dtl->kebutuhan_qty * $rasio;

            $bahanList[] = [
                'barang_id'            => $dtl->barang_mentah_id,
                'barang_cd'            => $dtl->barangMentah?->barang_cd,
                'barang_nm'            => $dtl->barangMentah?->barang_nm,
                'satuan_nm'            => $dtl->barangMentah?->satuanDasar?->satuan_nm,
                'kebutuhan_per_batch'  => (float) $dtl->kebutuhan_qty,
                'kebutuhan_total'      => $kebutuhanTotal,
            ];
        }

        return [
            'bom_id'           => $bom->bom_id,
            'bom_no'           => $bom->bom_no,
            'bom_nm'           => $bom->bom_nm,
            'batch_ukuran_qty' => (float) $bom->batch_ukuran_qty,
            'target_qty'       => $targetQty,
            'rasio_faktor'     => $rasio,
            'materials'        => $bahanList,
        ];
    }

    /**
     * Mengalokasikan kebutuhan bahan baku resep produksi ke Batch Stok Fisik Gudang
     * berdasarkan prinsip FEFO / FIFO (First In / First Expired):
     * - Mengutamakan batch yang dibeli/masuk paling lama (created_at paling awal / expired paling dekat).
     * - Jika batch terlama tidak mencukupi, sistem otomatis menghabiskan batch tersebut sampai 0
     *   dan memecah (split) sisa kebutuhan ke batch terlama berikutnya (multi-batch split).
     * - Mengembalikan daftar baris draf pick-list yang siap dimasukkan ke form pengeluaran barang.
     */
    public function alokasiBahanResepFifo(int $gudangId, int $bomId, float $targetQty, ?string $gradeFilter = null, int $excludePakaiId = 0): array
    {
        $bom = MstBomHdr::with(['barangJadi.satuanDasar', 'details.barangMentah.satuanDasar'])
            ->where('bom_id', $bomId)
            ->where('deleted_st', false)
            ->firstOrFail();

        if ((float) $bom->batch_ukuran_qty <= 0) {
            throw new Exception("Ukuran batch standar pada resep {$bom->bom_no} tidak valid.");
        }

        if ($targetQty <= 0) {
            throw new Exception("Jumlah target rencana produksi harus lebih besar dari 0.");
        }

        $gradeFilter = strtoupper(trim((string) $gradeFilter));
        if (!in_array($gradeFilter, ['A', 'B'])) {
            $gradeFilter = null; // Default: Campur / Semua Grade
        }

        $rasio = $targetQty / (float) $bom->batch_ukuran_qty;
        $allocatedItems = [];
        $peringatanList = [];

        foreach ($bom->details as $dtl) {
            $barang = $dtl->barangMentah;
            if (!$barang) {
                continue;
            }

            $barangId = (int) $dtl->barang_mentah_id;
            $kebutuhanTotal = (float) $dtl->kebutuhan_qty * $rasio;
            $satuanNm = $barang->satuanDasar?->satuan_nm ?? 'Unit';

            // Ambil seluruh batch aktif yang tersedia di gudang tujuan, diurutkan FIFO (paling lama masuk)
            $availableBatches = DatStokBatch::where('gudang_id', $gudangId)
                ->where('barang_id', $barangId)
                ->where('sisa_qty', '>', 0)
                ->where('deleted_st', false)
                ->orderBy('created_at', 'asc')
                ->orderBy('stok_id', 'asc')
                ->get();

            // Ambil alokasi draft dokumen pemakaian lain yang belum diposting (anti-tabrakan draft)
            $draftAllocations = DatPakaiDtl::where('barang_id', $barangId)
                ->whereHas('header', function ($q) use ($gudangId, $excludePakaiId) {
                    $q->where('gudang_id', $gudangId)
                      ->where('status_st', 'DRAFT')
                      ->where('deleted_st', false);
                    if ($excludePakaiId > 0) {
                        $q->where('pakai_id', '!=', $excludePakaiId);
                    }
                })
                ->with('header:pakai_id,pakai_no')
                ->get()
                ->groupBy(function ($d) {
                    return trim($d->batch_no) . '_' . strtoupper(trim($d->grade_cd ?: 'A'));
                });

            // Format semua opsi batch untuk dimasukkan ke dropdown baris (agar user tetap bisa ganti manual jika mau)
            $allBatchOptions = $availableBatches->values()->map(function ($b, $index) use ($draftAllocations) {
                $gradeCd = strtoupper(trim($b->grade_cd ?: 'A'));
                $lookupKey = trim($b->batch_no) . '_' . $gradeCd;
                $group = $draftAllocations->get($lookupKey);

                $sisaFisik = (float) $b->sisa_qty;
                $draftQty = $group ? (float) $group->sum('qty_keluar') : 0.0;
                $draftDocNos = $group ? $group->pluck('header.pakai_no')->unique()->filter()->values()->toArray() : [];
                $sisaBebas = max(0.0, round($sisaFisik - $draftQty, 4));

                $b->sisa_bebas = $sisaBebas;
                $b->draft_qty = $draftQty;
                $b->draft_doc_nos = $draftDocNos;

                return [
                    'stok_id'        => $b->stok_id,
                    'batch_no'       => $b->batch_no,
                    'grade_cd'       => $gradeCd,
                    'sisa_qty'       => $sisaFisik,
                    'draft_qty'      => $draftQty,
                    'draft_doc_nos'  => $draftDocNos,
                    'sisa_bebas'     => $sisaBebas,
                    'is_committed'   => $draftQty > 0,
                    'is_fully_booked'=> $sisaBebas <= 0.0001,
                    'harga_satuan'   => (float) $b->harga_satuan,
                    'expired_tgl'    => $b->expired_tgl ? Carbon::parse($b->expired_tgl)->format('d/m/Y') : null,
                    'tgl_terima'     => $b->created_at ? $b->created_at->format('d/m/Y') : '-',
                    'is_fifo_top'    => $index === 0,
                ];
            })->toArray();

            // Cek apakah barang ini ber-grade (bahan baku atau memiliki batch dengan variasi grade A/B)
            $jenisCd = strtoupper($barang->jenisBarang->jenis_barang_cd ?? '');
            $nm = strtoupper($barang->barang_nm ?? '');
            $isBahanBaku = ($jenisCd === 'BB' || $jenisCd === 'RAW' || str_contains($nm, 'SINGKONG') || str_contains($nm, 'UBI') || str_contains($nm, 'OPAK') || str_contains($nm, 'PUYUR') || str_contains($nm, 'BERKO'));
            $hasMultipleGrades = $availableBatches->pluck('grade_cd')->filter()->unique()->count() > 1;
            $isBahanBerGrade = ($isBahanBaku || $hasMultipleGrades);

            // Filter batch yang akan dipakai untuk alokasi otomatis berdasarkan preferensi Grade user
            $batchesForAllocation = $availableBatches;
            $filterLabel = '';
            if ($isBahanBerGrade && !empty($gradeFilter)) {
                $filterLabel = " (Khusus Grade {$gradeFilter})";
                $batchesForAllocation = $availableBatches->filter(function ($b) use ($gradeFilter) {
                    return strtoupper($b->grade_cd ?? 'A') === $gradeFilter;
                })->values();
            }

            // Saring kandidat yang memiliki sisa bebas > 0 agar tidak mengambil batch yang sudah dibooking draft lain
            $freeBatchesForAllocation = $batchesForAllocation->filter(function ($b) {
                return ($b->sisa_bebas ?? $b->sisa_qty) > 0.0001;
            })->values();

            // Jika semua batch ter-booking draft lain, fallback ke batchesForAllocation untuk tetap menampilkan info defisit
            $activeAllocationBatches = $freeBatchesForAllocation->isNotEmpty() ? $freeBatchesForAllocation : $batchesForAllocation;

            if ($activeAllocationBatches->isEmpty() || ($freeBatchesForAllocation->isEmpty() && $batchesForAllocation->isNotEmpty())) {
                $pesanDefisit = $freeBatchesForAllocation->isEmpty() && $batchesForAllocation->isNotEmpty()
                    ? "Bahan [{$barang->barang_cd}] {$barang->barang_nm}{$filterLabel} seluruh stok fisiknya telah terikat di dokumen DRAFT lain (kebutuhan: {$kebutuhanTotal} {$satuanNm})."
                    : (!empty($filterLabel)
                        ? "Bahan [{$barang->barang_cd}] {$barang->barang_nm}{$filterLabel} tidak memiliki stok fisik aktif di gudang ini (kebutuhan: {$kebutuhanTotal} {$satuanNm})."
                        : "Bahan [{$barang->barang_cd}] {$barang->barang_nm} tidak memiliki stok fisik di gudang ini (kebutuhan: {$kebutuhanTotal} {$satuanNm}).");

                $peringatanList[] = $pesanDefisit;
                $allocatedItems[] = [
                    'barang_id'       => $barangId,
                    'barang_cd'       => $barang->barang_cd,
                    'barang_nm'       => $barang->barang_nm,
                    'satuan_nm'       => $satuanNm,
                    'kebutuhan_total' => $kebutuhanTotal,
                    'batch_no'        => '',
                    'grade_cd'        => ($isBahanBerGrade && $gradeFilter) ? $gradeFilter : 'A',
                    'qty_keluar'      => $kebutuhanTotal,
                    'harga_satuan'    => (float) ($barang->harga_beli_standar ?? 0),
                    'sisa_batch'      => 0,
                    'tgl_terima'      => '-',
                    'expired_tgl'     => '-',
                    'is_allocated'    => false,
                    'is_oldest'       => false,
                    'is_split'        => false,
                    'catatan_fifo'    => '⚠️ ' . $pesanDefisit,
                    'all_batches'     => $allBatchOptions,
                ];
                continue;
            }

            // Alokasikan kebutuhan bertahap dari batch terlama (FIFO) berdasarkan saldo BEBAS (unallocated)
            $sisaKebutuhan = $kebutuhanTotal;
            $batchIndex = 0;

            while ($sisaKebutuhan > 0 && $batchIndex < $activeAllocationBatches->count()) {
                $batch = $activeAllocationBatches[$batchIndex];
                $sisaBatch = (float) ($batch->sisa_bebas ?? $batch->sisa_qty);
                if ($sisaBatch <= 0.0001) {
                    $batchIndex++;
                    continue;
                }

                $ambilQty = min($sisaKebutuhan, $sisaBatch);
                $isExhausted = ($ambilQty >= $sisaBatch);
                $tglTerima = $batch->created_at ? $batch->created_at->format('d/m/Y') : '-';
                $expTgl = $batch->expired_tgl ? Carbon::parse($batch->expired_tgl)->format('d/m/Y') : '-';
                $hargaSatuan = (float) $batch->harga_satuan > 0 ? (float) $batch->harga_satuan : (float) ($barang->harga_beli_standar ?? 0);

                $keteranganFifo = ($batchIndex === 0)
                    ? "⭐ FIFO Prioritas{$filterLabel}: Batch masuk ({$tglTerima})"
                    : "Lanjutan Split FIFO{$filterLabel}: Batch ({$tglTerima})";

                if ($isExhausted) {
                    $keteranganFifo .= " [Habiskan Sisa Bebas]";
                }

                if (!empty($batch->draft_qty) && $batch->draft_qty > 0) {
                    $keteranganFifo .= " (⚠️ Draft Lain: " . round($batch->draft_qty, 2) . " {$satuanNm})";
                }

                $allocatedItems[] = [
                    'barang_id'       => $barangId,
                    'barang_cd'       => $barang->barang_cd,
                    'barang_nm'       => $barang->barang_nm,
                    'satuan_nm'       => $satuanNm,
                    'kebutuhan_total' => $kebutuhanTotal,
                    'stok_id'         => $batch->stok_id,
                    'batch_no'        => $batch->batch_no,
                    'grade_cd'        => $batch->grade_cd ?? 'A',
                    'qty_keluar'      => round($ambilQty, 4),
                    'harga_satuan'    => $hargaSatuan,
                    'sisa_batch'      => $sisaBatch,
                    'tgl_terima'      => $tglTerima,
                    'expired_tgl'     => $expTgl,
                    'is_allocated'    => true,
                    'is_oldest'       => ($batchIndex === 0),
                    'is_split'        => ($kebutuhanTotal > $sisaBatch),
                    'catatan_fifo'    => $keteranganFifo,
                    'all_batches'     => $allBatchOptions,
                ];

                $sisaKebutuhan -= $ambilQty;
                $batchIndex++;
            }

            // Jika semua batch pilihan di gudang ini sudah habis tapi kebutuhan belum tercukupi
            if ($sisaKebutuhan > 0.0001) {
                $pesanKurang = !empty($filterLabel)
                    ? "Bahan [{$barang->barang_cd}] {$barang->barang_nm}{$filterLabel} masih kurang " . round($sisaKebutuhan, 4) . " {$satuanNm} karena semua batch Grade {$gradeFilter} di gudang telah dialokasikan maksimal."
                    : "Bahan [{$barang->barang_cd}] {$barang->barang_nm} masih kurang " . round($sisaKebutuhan, 4) . " {$satuanNm} karena semua batch di gudang telah dialokasikan maksimal.";

                $peringatanList[] = $pesanKurang;
                $allocatedItems[] = [
                    'barang_id'       => $barangId,
                    'barang_cd'       => $barang->barang_cd,
                    'barang_nm'       => $barang->barang_nm,
                    'satuan_nm'       => $satuanNm,
                    'kebutuhan_total' => $kebutuhanTotal,
                    'batch_no'        => '',
                    'grade_cd'        => ($isBahanBerGrade && $gradeFilter) ? $gradeFilter : 'A',
                    'qty_keluar'      => round($sisaKebutuhan, 4),
                    'harga_satuan'    => (float) ($barang->harga_beli_standar ?? 0),
                    'sisa_batch'      => 0,
                    'tgl_terima'      => '-',
                    'expired_tgl'     => '-',
                    'is_allocated'    => false,
                    'is_oldest'       => false,
                    'is_split'        => true,
                    'catatan_fifo'    => '⚠️ Defisit stok fisik' . $filterLabel . ': Kurang ' . round($sisaKebutuhan, 4) . " {$satuanNm}",
                    'all_batches'     => $allBatchOptions,
                ];
            }
        }

        $canSwitchToCampur = false;
        $defisitInfo = null;

        if (!empty($gradeFilter) && !empty($peringatanList)) {
            // Cek apakah ada barang yang defisit tapi sebenarnya memiliki stok fisik aktif pada grade lain
            foreach ($bom->details as $dtl) {
                $bId = (int) $dtl->barang_mentah_id;
                $otherStock = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $bId)
                    ->where('sisa_qty', '>', 0)
                    ->where('deleted_st', false)
                    ->where(function($q) use ($gradeFilter) {
                        $q->whereNull('grade_cd')
                          ->orWhere('grade_cd', '!=', $gradeFilter);
                    })
                    ->sum('sisa_qty');

                if ((float) $otherStock > 0) {
                    $canSwitchToCampur = true;
                    $otherGradeLabel = ($gradeFilter === 'B') ? 'Grade A' : 'Grade B';
                    $defisitInfo = [
                        'barang_nm'         => $dtl->barangMentah?->barang_nm ?? 'Bahan Baku',
                        'barang_cd'         => $dtl->barangMentah?->barang_cd ?? '',
                        'chosen_grade'      => $gradeFilter,
                        'other_grade_label' => $otherGradeLabel,
                        'other_stock'       => (float) $otherStock,
                        'satuan_nm'         => $dtl->barangMentah?->satuanDasar?->satuan_nm ?? 'Kg',
                    ];
                    break;
                }
            }
        }

        return [
            'bom_id'                => $bom->bom_id,
            'bom_no'                => $bom->bom_no,
            'bom_nm'                => $bom->bom_nm,
            'target_qty'            => $targetQty,
            'batch_ukuran_qty'      => (float) $bom->batch_ukuran_qty,
            'satuan_target'         => $bom->barangJadi?->satuanDasar?->satuan_nm ?? 'Unit',
            'grade_filter'          => $gradeFilter ?: 'CAMPUR',
            'items'                 => $allocatedItems,
            'peringatan'            => $peringatanList,
            'is_lengkap'            => empty($peringatanList),
            'can_switch_to_campur'  => $canSwitchToCampur,
            'defisit_info'          => $defisitInfo,
        ];
    }
}
