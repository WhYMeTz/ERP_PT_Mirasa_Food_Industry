<?php
namespace App\Services\Gudang;

use App\Mail\PoApprovalRequestMail;
use App\Mail\PoSupplierDispatchMail;
use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatPoRevisiLog;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstPajak;
use App\Models\User;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PoService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar bahan baku & bahan penolong yang stok fisiknya di bawah batas minimum (Safety Stock).
     * Digunakan untuk memicu alert atau rekomendasi pembuatan PO otomatis.
     */
    public function getBarangBelowMinimum(int|array|null $gudangId = null): Collection
    {
        $barangList = MstBarang::with(['satuanDasar', 'jenisBarang'])
            ->bahanBaku()
            ->active()
            ->where('batas_minimum_qty', '>', 0)
            ->get();

        $stokQuery = DatStokBatch::query()->where('deleted_st', false)->where('sisa_qty', '>', 0);
        if (is_array($gudangId)) {
            $stokQuery->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $stokQuery->where('gudang_id', $gudangId);
        }

        $stockPerBarang = $stokQuery->groupBy('barang_id')
            ->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')
            ->pluck('total_sisa', 'barang_id');

        return $barangList->filter(function ($item) use ($stockPerBarang) {
            $currentStock = (float) ($stockPerBarang[$item->barang_id] ?? 0);
            $item->current_stock = $currentStock;
            $item->selisih_kurang = max(0, (float) $item->batas_minimum_qty - $currentStock);
            return $currentStock < (float) $item->batas_minimum_qty;
        })->values();
    }

    /**
     * Mengambil daftar PO dengan pagination dan filter pencarian.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, ?string $status = null, int|array|null $gudangId = null): LengthAwarePaginator
    {
        $query = DatPoHdr::with(['supplier', 'gudang', 'approver', 'details.barang.satuanDasar', 'qcInbound.details'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
        }

        if (!empty($status)) {
            if ($status === 'COMPLETED_CLOSED') {
                $query->whereIn('status_cd', ['COMPLETED', 'CLOSED']);
            } else {
                $query->where('status_cd', $status);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('po_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('po_tgl', 'desc')
            ->orderBy('po_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil detail satu dokumen PO berdasarkan ID.
     */
    public function getById(int $id): DatPoHdr
    {
        return DatPoHdr::with([
            'supplier',
            'gudang',
            'approver',
            'details.barang.satuanDasar',
            'details.barang.satuanBesar',
            'details.pajak',
            'penerimaan.details',
            'qcInbound.details.barang.satuanDasar',
            'qcInbound.terima',
            'revisiLog',
        ])->where('po_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan transaksi pembuatan PO baru beserta rincian itemnya via DB Transaction.
     */
    /**
     * Menyimpan transaksi pembuatan PO baru beserta rincian itemnya via DB Transaction.
     * Mendukung Konsep 1 (Auto-Split PO): Jika dalam 1 formulir terdapat item dari supplier berbeda,
     * sistem secara otomatis memecah menjadi beberapa dokumen PO resmi terpisah per-supplier.
     *
     * @return DatPoHdr|SupportCollection
     */
    public function store(array $data): DatPoHdr|SupportCollection
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang dalam Purchase Order.");
            }

            $headerSupplierId = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;
            $itemsBySupplier = [];

            foreach ($items as $item) {
                $supId = !empty($item['supplier_id']) ? (int) $item['supplier_id'] : $headerSupplierId;
                if (!$supId) {
                    throw new Exception("Supplier mitra wajib dipilih untuk setiap item barang pesanan.");
                }
                $itemsBySupplier[$supId][] = $item;
            }

            $createdPOs = collect();
            $isMultiSupplier = count($itemsBySupplier) > 1;

            $headerDiskonPersen = !empty($data['diskon_persen']) ? (float) $data['diskon_persen'] : 0;
            $headerPotonganNominal = !empty($data['potongan_nominal']) ? (float) $data['potongan_nominal'] : 0;
            $headerPajakId = !empty($data['pajak_id']) ? (int) $data['pajak_id'] : null;

            $pajakIds = collect($items)->pluck('pajak_id')->filter()->unique()->toArray();
            if ($headerPajakId && !in_array($headerPajakId, $pajakIds)) {
                $pajakIds[] = $headerPajakId;
            }
            $taxesById = !empty($pajakIds) ? MstPajak::whereIn('pajak_id', $pajakIds)->get()->keyBy('pajak_id') : collect();
            $headerTaxInclusive = !empty($data['is_harga_termasuk_pajak']);

            foreach ($itemsBySupplier as $supplierId => $supplierItems) {
                // Jika single supplier dan user mengetikkan po_no manual, gunakan itu.
                // Jika multi-supplier, generate nomor PO unik otomatis per supplier.
                if (!$isMultiSupplier && !empty($data['po_no'])) {
                    $poNo = trim($data['po_no']);
                } else {
                    $poTgl = !empty($data['po_tgl']) ? (string) $data['po_tgl'] : null;
                    $poNo = $this->codeGenerator->generatePoNo($poTgl);
                }

                // Hitung total gross setelah diskon untuk prorata potongan nominal jika ada
                $sumGrossAfterDiskon = 0;
                foreach ($supplierItems as $it) {
                    $q = (float) ($it['pesan_qty'] ?? 0);
                    $h = (float) ($it['harga_nominal'] ?? 0);
                    $dp = isset($it['diskon_persen']) && $it['diskon_persen'] !== '' ? (float) $it['diskon_persen'] : $headerDiskonPersen;
                    $du = $h * ($dp / 100);
                    $sumGrossAfterDiskon += ($q * max(0, $h - $du));
                }

                $subtotalBruto = 0;
                $diskonTotal = 0;
                $totalPotongan = 0;
                $totalDpp = 0;
                $totalPpn = 0;
                $grandTotal = 0;

                foreach ($supplierItems as $item) {
                    $qty = (float) ($item['pesan_qty'] ?? 0);
                    $harga = (float) ($item['harga_nominal'] ?? 0);
                    $diskonPersen = isset($item['diskon_persen']) && $item['diskon_persen'] !== '' ? (float) $item['diskon_persen'] : $headerDiskonPersen;
                    $diskonUnit = $harga * ($diskonPersen / 100);
                    $hargaNetto = max(0, $harga - $diskonUnit);

                    if (isset($item['potongan_nominal']) && $item['potongan_nominal'] !== '') {
                        $potonganNominal = (float) $item['potongan_nominal'];
                    } elseif ($headerPotonganNominal > 0 && $sumGrossAfterDiskon > 0) {
                        $itemGross = $qty * $hargaNetto;
                        $potonganNominal = round(($itemGross / $sumGrossAfterDiskon) * $headerPotonganNominal, 4);
                    } else {
                        $potonganNominal = 0;
                    }

                    $grossRowTotal = max(0, ($qty * $hargaNetto) - $potonganNominal);

                    $pajakId = !empty($item['pajak_id']) ? (int) $item['pajak_id'] : $headerPajakId;
                    $pajak = $pajakId ? ($taxesById[$pajakId] ?? null) : null;
                    $tarifPersen = $pajak ? (float) $pajak->tarif_persen : (($item['ppn_tipe'] ?? '') === 'PPN_11' ? 11.00 : 0.0);
                    $ppnCode = $pajak ? $pajak->pajak_cd : ($tarifPersen > 0 ? 'PPN_11' : 'NON_PPN');
                    $sifatPajak = $pajak ? $pajak->sifat_pajak : 'TAMBAH';

                    $rowTaxInclusive = isset($item['is_harga_termasuk_pajak'])
                        ? filter_var($item['is_harga_termasuk_pajak'], FILTER_VALIDATE_BOOLEAN)
                        : $headerTaxInclusive;

                    if ($rowTaxInclusive && $tarifPersen > 0 && $sifatPajak !== 'POTONG') {
                        $subtotalNetto = round($grossRowTotal / (1 + ($tarifPersen / 100)), 4);
                        $ppnNominal = round($grossRowTotal - $subtotalNetto, 4);
                        $subtotalTagihan = $grossRowTotal;
                    } else {
                        $subtotalNetto = $grossRowTotal;
                        $ppnNominal = round($subtotalNetto * ($tarifPersen / 100), 4);
                        $subtotalTagihan = ($sifatPajak === 'POTONG')
                            ? max(0, $subtotalNetto - $ppnNominal)
                            : ($subtotalNetto + $ppnNominal);
                    }

                    $subtotalBruto += ($qty * $harga);
                    $diskonTotal += ($qty * $diskonUnit);
                    $totalPotongan += $potonganNominal;
                    $totalDpp += $subtotalNetto;
                    $totalPpn += $ppnNominal;
                    $grandTotal += $subtotalTagihan;
                }

                $approverId = !empty($data['approver_id']) ? (int) $data['approver_id'] : null;
                $initialStatus = !empty($data['status_cd']) ? $data['status_cd'] : ($approverId ? 'PENDING_APPROVAL' : 'APPROVED');

                $header = DatPoHdr::create([
                    'po_no'                   => $poNo,
                    'po_tgl'                  => $data['po_tgl'] ?? date('Y-m-d'),
                    'tgl_estimasi_datang'     => $data['tgl_estimasi_datang'] ?? null,
                    'supplier_id'             => $supplierId,
                    'gudang_id'               => $data['gudang_id'],
                    'approver_id'             => $approverId,
                    'approval_token'          => Str::random(40),
                    'status_cd'               => $initialStatus,
                    'is_harga_termasuk_pajak' => $headerTaxInclusive,
                    'subtotal_bruto'          => $subtotalBruto,
                    'diskon_total'            => $diskonTotal,
                    'potongan_nominal'        => $totalPotongan,
                    'dpp_nominal'             => $totalDpp,
                    'ppn_nominal'             => $totalPpn,
                    'total_nominal'           => $grandTotal,
                    'total_tagihan'           => $grandTotal,
                    'catatan_txt'             => $data['catatan_txt'] ?? null,
                    'remark_txt'              => $data['remark_txt'] ?? null,
                    'term_of_payment'         => $data['term_of_payment'] ?? null,
                ]);

                foreach ($supplierItems as $item) {
                    $qty = (float) ($item['pesan_qty'] ?? 0);
                    $harga = (float) ($item['harga_nominal'] ?? 0);
                    $diskonPersen = isset($item['diskon_persen']) && $item['diskon_persen'] !== '' ? (float) $item['diskon_persen'] : $headerDiskonPersen;
                    $diskonUnit = $harga * ($diskonPersen / 100);
                    $hargaNetto = max(0, $harga - $diskonUnit);

                    if (isset($item['potongan_nominal']) && $item['potongan_nominal'] !== '') {
                        $potonganNominal = (float) $item['potongan_nominal'];
                    } elseif ($headerPotonganNominal > 0 && $sumGrossAfterDiskon > 0) {
                        $itemGross = $qty * $hargaNetto;
                        $potonganNominal = round(($itemGross / $sumGrossAfterDiskon) * $headerPotonganNominal, 4);
                    } else {
                        $potonganNominal = 0;
                    }

                    $grossRowTotal = max(0, ($qty * $hargaNetto) - $potonganNominal);

                    $pajakId = !empty($item['pajak_id']) ? (int) $item['pajak_id'] : $headerPajakId;
                    $pajak = $pajakId ? ($taxesById[$pajakId] ?? null) : null;
                    $tarifPersen = $pajak ? (float) $pajak->tarif_persen : (($item['ppn_tipe'] ?? '') === 'PPN_11' ? 11.00 : 0.0);
                    $ppnCode = $pajak ? $pajak->pajak_cd : ($tarifPersen > 0 ? 'PPN_11' : 'NON_PPN');
                    $sifatPajak = $pajak ? $pajak->sifat_pajak : 'TAMBAH';

                    $rowTaxInclusive = isset($item['is_harga_termasuk_pajak'])
                        ? filter_var($item['is_harga_termasuk_pajak'], FILTER_VALIDATE_BOOLEAN)
                        : $headerTaxInclusive;

                    if ($rowTaxInclusive && $tarifPersen > 0 && $sifatPajak !== 'POTONG') {
                        $subtotalNetto = round($grossRowTotal / (1 + ($tarifPersen / 100)), 4);
                        $ppnNominal = round($grossRowTotal - $subtotalNetto, 4);
                        $subtotalTagihan = $grossRowTotal;
                    } else {
                        $subtotalNetto = $grossRowTotal;
                        $ppnNominal = round($subtotalNetto * ($tarifPersen / 100), 4);
                        $subtotalTagihan = ($sifatPajak === 'POTONG')
                            ? max(0, $subtotalNetto - $ppnNominal)
                            : ($subtotalNetto + $ppnNominal);
                    }

                    DatPoDtl::create([
                        'po_id'                   => $header->po_id,
                        'barang_id'               => $item['barang_id'],
                        'pesan_qty'               => $qty,
                        'harga_nominal'           => $harga,
                        'diskon_persen'           => $diskonPersen,
                        'diskon_nominal'          => $diskonUnit,
                        'potongan_nominal'        => $potonganNominal,
                        'harga_netto'             => $hargaNetto,
                        'subtotal_netto'          => $subtotalNetto,
                        'pajak_id'                => $pajakId,
                        'is_harga_termasuk_pajak' => $rowTaxInclusive,
                        'ppn_tipe'                => $ppnCode,
                        'ppn_persen'              => $tarifPersen,
                        'ppn_nominal'             => $ppnNominal,
                        'subtotal_nominal'        => $subtotalTagihan,
                        'subtotal_tagihan'        => $subtotalTagihan,
                        'terima_qty'              => 0,
                        'catatan_txt'             => $item['catatan_txt'] ?? null,
                    ]);

                    // Update harga beli acuan di Master Barang dengan harga terbaru
                    if ($harga > 0) {
                        $barang = MstBarang::find($item['barang_id']);
                        if ($barang && (float) $barang->harga_beli_standar != $harga) {
                            $barang->harga_beli_standar = $harga;
                            $barang->save();
                        }
                    }
                }

                $createdPOs->push($header->fresh(['supplier', 'details.barang']));
            }

            return $createdPOs;
        });

        // Kirim email notifikasi approval ke Atasan secara otomatis jika approver ditentukan
        $pos = ($result instanceof SupportCollection) ? $result : collect([$result]);
        foreach ($pos as $createdPo) {
            if ($createdPo->approver_id && $createdPo->status_cd === 'PENDING_APPROVAL') {
                $this->sendApprovalEmail($createdPo);
            }
        }

        if ($result instanceof SupportCollection && $result->count() === 1) {
            return $result->first();
        }

        return $result;
    }

    /**
     * Memperbarui dokumen PO (termasuk status PARTIAL / penerimaan bertahap).
     */
    public function update(int $id, array $data): DatPoHdr
    {
        return DB::transaction(function () use ($id, $data) {
            $po = DatPoHdr::with(['supplier', 'gudang', 'details.barang.satuanDasar'])->findOrFail($id);

            // 1. Ambil Snapshot SEBELUM pengubahan dokumen untuk Audit Trail (Riwayat Edit)
            $beforeSnapshot = $this->buildPoSnapshot($po);

            // Validasi status dokumen
            if (in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED'])) {
                $user = auth()->user();
                if (!$user || !$user->isSuperAdmin()) {
                    throw new Exception("PO {$po->po_no} dengan status {$po->status_cd} tidak dapat diedit.");
                }
            }

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang dalam Purchase Order.");
            }

            // Kumpulkan ID detail yang dikirim dari form
            $submittedDtlIds = [];
            foreach ($items as $item) {
                if (!empty($item['podtl_id'])) {
                    $submittedDtlIds[] = (int) $item['podtl_id'];
                }
            }

            // Validasi item yang sudah ada penerimaan fisik di gudang (terima_qty > 0):
            // 1. Tidak boleh dihapus dari PO
            // 2. Qty pesan tidak boleh lebih kecil dari terima_qty
            foreach ($po->details as $existingDtl) {
                $terimaQty = (float) $existingDtl->terima_qty;
                if ($terimaQty > 0) {
                    if (!in_array($existingDtl->podtl_id, $submittedDtlIds)) {
                        $barangNm = $existingDtl->barang?->barang_nm ?? 'Item #' . $existingDtl->barang_id;
                        throw new Exception("Barang '{$barangNm}' sudah diterima di gudang sebanyak {$terimaQty} dan tidak dapat dihapus dari Purchase Order.");
                    }
                }
            }

            $subtotalBruto = 0;
            $diskonTotal = 0;
            $totalPotongan = 0;
            $totalDpp = 0;
            $totalPpn = 0;
            $grandTotal = 0;
            $totalPesanQtyAll = 0;
            $totalTerimaQtyAll = 0;

            $existingDetailsById = $po->details->keyBy('podtl_id');
            $processedDtlIds = [];

            $headerTaxInclusive = isset($data['is_harga_termasuk_pajak'])
                ? (bool) $data['is_harga_termasuk_pajak']
                : (bool) $po->is_harga_termasuk_pajak;

            $headerDiskonPersen = !empty($data['diskon_persen']) ? (float) $data['diskon_persen'] : 0;
            $headerPotonganNominal = !empty($data['potongan_nominal']) ? (float) $data['potongan_nominal'] : 0;
            $headerPajakId = !empty($data['pajak_id']) ? (int) $data['pajak_id'] : null;

            $pajakIds = collect($items)->pluck('pajak_id')->filter()->unique()->toArray();
            if ($headerPajakId && !in_array($headerPajakId, $pajakIds)) {
                $pajakIds[] = $headerPajakId;
            }
            $taxesById = !empty($pajakIds) ? MstPajak::whereIn('pajak_id', $pajakIds)->get()->keyBy('pajak_id') : collect();

            // Hitung total gross setelah diskon untuk prorata potongan nominal jika ada
            $sumGrossAfterDiskon = 0;
            foreach ($items as $it) {
                $q = (float) ($it['pesan_qty'] ?? 0);
                $h = (float) ($it['harga_nominal'] ?? 0);
                $dp = isset($it['diskon_persen']) && $it['diskon_persen'] !== '' ? (float) $it['diskon_persen'] : $headerDiskonPersen;
                $du = $h * ($dp / 100);
                $sumGrossAfterDiskon += ($q * max(0, $h - $du));
            }

            foreach ($items as $item) {
                $dtlId = !empty($item['podtl_id']) ? (int) $item['podtl_id'] : null;
                $existingDtl = ($dtlId && $existingDetailsById->has($dtlId)) ? $existingDetailsById->get($dtlId) : null;

                $terimaQty = $existingDtl ? (float) $existingDtl->terima_qty : 0;
                $qty = (float) ($item['pesan_qty'] ?? 0);

                if ($existingDtl && $qty < $terimaQty) {
                    $barangNm = $existingDtl->barang?->barang_nm ?? 'Item #' . $existingDtl->barang_id;
                    throw new Exception("Kuantitas pesan untuk '{$barangNm}' ({$qty}) tidak boleh lebih kecil dari kuantitas yang sudah diterima di gudang ({$terimaQty}).");
                }

                $harga = (float) ($item['harga_nominal'] ?? 0);
                $diskonPersen = isset($item['diskon_persen']) && $item['diskon_persen'] !== '' ? (float) $item['diskon_persen'] : $headerDiskonPersen;
                $diskonUnit = $harga * ($diskonPersen / 100);
                $hargaNetto = max(0, $harga - $diskonUnit);

                if (isset($item['potongan_nominal']) && $item['potongan_nominal'] !== '') {
                    $potonganNominal = (float) $item['potongan_nominal'];
                } elseif ($headerPotonganNominal > 0 && $sumGrossAfterDiskon > 0) {
                    $itemGross = $qty * $hargaNetto;
                    $potonganNominal = round(($itemGross / $sumGrossAfterDiskon) * $headerPotonganNominal, 4);
                } else {
                    $potonganNominal = 0;
                }

                $grossRowTotal = max(0, ($qty * $hargaNetto) - $potonganNominal);

                $pajakId = !empty($item['pajak_id']) ? (int) $item['pajak_id'] : $headerPajakId;
                $pajak = $pajakId ? ($taxesById[$pajakId] ?? null) : null;
                $tarifPersen = $pajak ? (float) $pajak->tarif_persen : (($item['ppn_tipe'] ?? '') === 'PPN_11' ? 11.00 : 0.0);
                $ppnCode = $pajak ? $pajak->pajak_cd : ($tarifPersen > 0 ? 'PPN_11' : 'NON_PPN');
                $sifatPajak = $pajak ? $pajak->sifat_pajak : 'TAMBAH';

                $rowTaxInclusive = isset($item['is_harga_termasuk_pajak'])
                    ? filter_var($item['is_harga_termasuk_pajak'], FILTER_VALIDATE_BOOLEAN)
                    : $headerTaxInclusive;

                if ($rowTaxInclusive && $tarifPersen > 0 && $sifatPajak !== 'POTONG') {
                    $subtotalNetto = round($grossRowTotal / (1 + ($tarifPersen / 100)), 4);
                    $ppnNominal = round($grossRowTotal - $subtotalNetto, 4);
                    $subtotalTagihan = $grossRowTotal;
                } else {
                    $subtotalNetto = $grossRowTotal;
                    $ppnNominal = round($subtotalNetto * ($tarifPersen / 100), 4);
                    $subtotalTagihan = ($sifatPajak === 'POTONG')
                        ? max(0, $subtotalNetto - $ppnNominal)
                        : ($subtotalNetto + $ppnNominal);
                }

                $subtotalBruto += ($qty * $harga);
                $diskonTotal += ($qty * $diskonUnit);
                $totalPotongan += $potonganNominal;
                $totalDpp += $subtotalNetto;
                $totalPpn += $ppnNominal;
                $grandTotal += $subtotalTagihan;

                $totalPesanQtyAll += $qty;
                $totalTerimaQtyAll += $terimaQty;

                // Jika barang sudah diterima di gudang, barang_id tidak boleh diubah
                $barangId = ($existingDtl && $terimaQty > 0) ? $existingDtl->barang_id : (int) $item['barang_id'];

                $payload = [
                    'po_id'                   => $po->po_id,
                    'barang_id'               => $barangId,
                    'pesan_qty'               => $qty,
                    'harga_nominal'           => $harga,
                    'diskon_persen'           => $diskonPersen,
                    'diskon_nominal'          => $diskonUnit,
                    'potongan_nominal'        => $potonganNominal,
                    'harga_netto'             => $hargaNetto,
                    'subtotal_netto'          => $subtotalNetto,
                    'pajak_id'                => $pajakId,
                    'is_harga_termasuk_pajak' => $rowTaxInclusive,
                    'ppn_tipe'                => $ppnCode,
                    'ppn_persen'              => $tarifPersen,
                    'ppn_nominal'             => $ppnNominal,
                    'subtotal_nominal'        => $subtotalTagihan,
                    'subtotal_tagihan'        => $subtotalTagihan,
                    'catatan_txt'             => $item['catatan_txt'] ?? null,
                ];

                if ($existingDtl) {
                    $existingDtl->update($payload);
                    $processedDtlIds[] = $existingDtl->podtl_id;
                } else {
                    $payload['terima_qty'] = 0;
                    $newDtl = DatPoDtl::create($payload);
                    $processedDtlIds[] = $newDtl->podtl_id;
                }

                // Update harga beli acuan di Master Barang
                if ($harga > 0) {
                    $barang = MstBarang::find($barangId);
                    if ($barang && (float) $barang->harga_beli_standar != $harga) {
                        $barang->harga_beli_standar = $harga;
                        $barang->save();
                    }
                }
            }

            // Hapus detail lama yang tidak dikirim dan terima_qty == 0
            foreach ($po->details as $oldDtl) {
                if (!in_array($oldDtl->podtl_id, $processedDtlIds) && (float) $oldDtl->terima_qty == 0) {
                    $oldDtl->delete();
                }
            }

            // Evaluasi status_cd dokumen PO
            $newStatusCd = $po->status_cd;
            if ($totalTerimaQtyAll > 0) {
                if ($totalTerimaQtyAll >= $totalPesanQtyAll) {
                    $newStatusCd = 'COMPLETED';
                } else {
                    $newStatusCd = 'PARTIAL';
                }
            } elseif ($po->status_cd === 'DRAFT') {
                $newStatusCd = 'DRAFT';
            } elseif (in_array($po->status_cd, ['REJECTED', 'REVISION'])) {
                $newStatusCd = 'PENDING_APPROVAL'; // Pengajuan ulang setelah direvisi
            } elseif ($po->status_cd === 'PENDING_APPROVAL') {
                $newStatusCd = 'PENDING_APPROVAL';
            } else {
                $newStatusCd = 'APPROVED';
            }

            $hasAnyReceived = $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);

            $po->update([
                'po_no'                   => !empty($data['po_no']) ? trim($data['po_no']) : $po->po_no,
                'po_tgl'                  => $data['po_tgl'] ?? $po->po_tgl,
                'tgl_estimasi_datang'     => $data['tgl_estimasi_datang'] ?? $po->tgl_estimasi_datang,
                'supplier_id'             => $hasAnyReceived ? $po->supplier_id : ($data['supplier_id'] ?? $po->supplier_id),
                'gudang_id'               => $data['gudang_id'] ?? $po->gudang_id,
                'approver_id'             => !empty($data['approver_id']) ? (int) $data['approver_id'] : $po->approver_id,
                'status_cd'               => $newStatusCd,
                'is_harga_termasuk_pajak' => $headerTaxInclusive,
                'subtotal_bruto'          => $subtotalBruto,
                'diskon_total'            => $diskonTotal,
                'potongan_nominal'        => $totalPotongan,
                'dpp_nominal'             => $totalDpp,
                'ppn_nominal'             => $totalPpn,
                'total_nominal'           => $grandTotal,
                'total_tagihan'           => $grandTotal,
                'catatan_txt'             => array_key_exists('catatan_txt', $data) ? $data['catatan_txt'] : $po->catatan_txt,
                'remark_txt'              => array_key_exists('remark_txt', $data) ? $data['remark_txt'] : $po->remark_txt,
                'term_of_payment'         => array_key_exists('term_of_payment', $data) ? $data['term_of_payment'] : $po->term_of_payment,
            ]);

            // 2. Ambil Snapshot SESUDAH pengubahan & hitung selisih perubahannya
            $poAfter = $po->fresh(['supplier', 'gudang', 'details.barang.satuanDasar']);
            $afterSnapshot = $this->buildPoSnapshot($poAfter);
            $diffResult = $this->computePoDiff($beforeSnapshot, $afterSnapshot);

            if (!empty($diffResult['diffs'])) {
                $user = auth()->user();
                $userName = $user ? $user->name : ($data['editor_nama'] ?? 'SYSTEM');
                $userRole = $user ? ($user->role?->role_nm ?? ($user->isSuperAdmin() ? 'Super Administrator' : 'Purchasing')) : 'Staf Pengadaan';

                $nextRevNumber = (int) ($po->total_revisi_count ?? 0) + 1;
                $revisiNo = "REV-{$nextRevNumber}";

                $diffTypes = collect($diffResult['diffs'])->pluck('tipe')->unique()->toArray();
                if (count($diffTypes) === 1 && in_array('HEADER', $diffTypes)) {
                    $kategoriPerubahan = 'PERUBAHAN_HEADER';
                } elseif (count($diffTypes) === 1 && in_array('ITEM_QTY', $diffTypes)) {
                    $kategoriPerubahan = 'PERUBAHAN_QTY';
                } elseif (count($diffTypes) === 1 && in_array('ITEM_HARGA', $diffTypes)) {
                    $kategoriPerubahan = 'PERUBAHAN_HARGA';
                } elseif (in_array('ITEM_TAMBAH', $diffTypes) || in_array('ITEM_HAPUS', $diffTypes)) {
                    $kategoriPerubahan = 'PERUBAHAN_ITEM';
                } else {
                    $kategoriPerubahan = 'KOMPREHENSIF';
                }

                $alasanEdit = !empty($data['alasan_edit']) ? trim($data['alasan_edit']) : 'Pembaruan Dokumen Purchase Order';

                DatPoRevisiLog::create([
                    'po_id'               => $po->po_id,
                    'revisi_no'           => $revisiNo,
                    'revisi_tgl'          => now(),
                    'user_id'             => $user?->id,
                    'user_nama'           => $userName,
                    'user_role'           => $userRole,
                    'kategori_perubahan'  => $kategoriPerubahan,
                    'alasan_edit'         => $alasanEdit,
                    'ringkasan_perubahan' => implode("; ", $diffResult['summaryParts']),
                    'data_sebelum_json'   => $beforeSnapshot,
                    'data_sesudah_json'   => $afterSnapshot,
                    'perubahan_json'      => $diffResult['diffs'],
                    'created_by'          => $userName,
                ]);

                $po->update([
                    'is_ada_revisi'        => true,
                    'total_revisi_count'   => $nextRevNumber,
                    'terakhir_direvisi_at' => now(),
                    'terakhir_direvisi_by' => $userName,
                ]);
            }

            return $po->fresh(['supplier', 'details.barang', 'details.pajak', 'revisiLog']);
        });
    }

    /**
     * Membangun snapshot komprehensif data PO (Header & Items) untuk Audit Trail.
     */
    protected function buildPoSnapshot(DatPoHdr $po): array
    {
        return [
            'po_no'                   => $po->po_no,
            'po_tgl'                  => $po->po_tgl ? $po->po_tgl->format('Y-m-d') : null,
            'tgl_estimasi_datang'     => $po->tgl_estimasi_datang ? $po->tgl_estimasi_datang->format('Y-m-d') : null,
            'supplier_id'             => $po->supplier_id,
            'supplier_nm'             => $po->supplier?->supplier_nm,
            'gudang_id'               => $po->gudang_id,
            'gudang_nm'               => $po->gudang?->gudang_nm,
            'term_of_payment'         => $po->term_of_payment,
            'is_harga_termasuk_pajak' => (bool) $po->is_harga_termasuk_pajak,
            'subtotal_bruto'          => (float) $po->subtotal_bruto,
            'diskon_total'            => (float) $po->diskon_total,
            'potongan_nominal'        => (float) $po->potongan_nominal,
            'dpp_nominal'             => (float) $po->dpp_nominal,
            'ppn_nominal'             => (float) $po->ppn_nominal,
            'total_tagihan'           => (float) $po->total_tagihan,
            'catatan_txt'             => $po->catatan_txt,
            'items'                   => $po->details->map(function ($d) {
                return [
                    'podtl_id'         => $d->podtl_id,
                    'barang_id'        => $d->barang_id,
                    'barang_cd'        => $d->barang?->barang_cd,
                    'barang_nm'        => $d->barang?->barang_nm,
                    'satuan_nm'        => $d->barang?->satuanDasar?->satuan_nm ?? ($d->barang?->satuanDasar?->satuan_cd ?? 'Unit'),
                    'pesan_qty'        => (float) $d->pesan_qty,
                    'harga_nominal'    => (float) $d->harga_nominal,
                    'diskon_persen'    => (float) $d->diskon_persen,
                    'potongan_nominal' => (float) $d->potongan_nominal,
                    'subtotal_tagihan' => (float) $d->subtotal_tagihan,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Membandingkan snapshot sebelum vs sesudah untuk menghasilkan rincian per bagian (Diffs).
     */
    protected function computePoDiff(array $before, array $after): array
    {
        $diffs = [];
        $summaryParts = [];

        // 1. Cek Perubahan Header Fields
        $headerFields = [
            'po_no'                   => 'Nomor PO',
            'po_tgl'                  => 'Tanggal Terbit PO',
            'tgl_estimasi_datang'     => 'Estimasi Tiba (ETA)',
            'supplier_id'             => 'Supplier Mitra',
            'gudang_id'               => 'Gudang Tujuan',
            'term_of_payment'         => 'Term of Payment (TOP)',
            'is_harga_termasuk_pajak' => 'Skema Pajak',
            'catatan_txt'             => 'Catatan PO',
            'total_tagihan'           => 'Total Nilai PO',
        ];

        foreach ($headerFields as $field => $label) {
            $oldVal = $before[$field] ?? null;
            $newVal = $after[$field] ?? null;

            if ($field === 'supplier_id' && $oldVal != $newVal) {
                $oldName = $before['supplier_nm'] ?? "ID {$oldVal}";
                $newName = $after['supplier_nm'] ?? "ID {$newVal}";
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => $oldName,
                    'sesudah' => $newName,
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "Supplier diubah dari '{$oldName}' menjadi '{$newName}'";
            } elseif ($field === 'gudang_id' && $oldVal != $newVal) {
                $oldName = $before['gudang_nm'] ?? "ID {$oldVal}";
                $newName = $after['gudang_nm'] ?? "ID {$newVal}";
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => $oldName,
                    'sesudah' => $newName,
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "Gudang diubah dari '{$oldName}' menjadi '{$newName}'";
            } elseif ($field === 'total_tagihan' && abs((float)$oldVal - (float)$newVal) > 0.01) {
                $oldFmt = 'Rp ' . number_format((float)$oldVal, 0, ',', '.');
                $newFmt = 'Rp ' . number_format((float)$newVal, 0, ',', '.');
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => $oldFmt,
                    'sesudah' => $newFmt,
                    'tipe'    => 'FINANSIAL',
                ];
                $summaryParts[] = "Total Nilai PO: {$oldFmt} ➔ {$newFmt}";
            } elseif ($field === 'is_harga_termasuk_pajak' && (bool)$oldVal !== (bool)$newVal) {
                $oldTxt = $oldVal ? 'Termasuk PPN (Inclusive)' : 'Belum Termasuk PPN (Exclusive)';
                $newTxt = $newVal ? 'Termasuk PPN (Inclusive)' : 'Belum Termasuk PPN (Exclusive)';
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => $oldTxt,
                    'sesudah' => $newTxt,
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "Perlakuan Pajak diubah: {$oldTxt} ➔ {$newTxt}";
            } elseif (!in_array($field, ['supplier_id', 'gudang_id', 'total_tagihan', 'is_harga_termasuk_pajak']) && $oldVal != $newVal) {
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => (string) ($oldVal ?? '-'),
                    'sesudah' => (string) ($newVal ?? '-'),
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "{$label} diubah: '" . ($oldVal ?: '-') . "' ➔ '" . ($newVal ?: '-') . "'";
            }
        }

        // 2. Cek Perubahan Item Komoditas
        $beforeItems = collect($before['items'] ?? [])->keyBy('barang_id');
        $afterItems = collect($after['items'] ?? [])->keyBy('barang_id');

        // Item Baru Ditambahkan
        foreach ($afterItems as $bId => $aItem) {
            if (!$beforeItems->has($bId)) {
                $bNm = $aItem['barang_nm'] ?? "Barang #{$bId}";
                $qtyFmt = number_format((float)$aItem['pesan_qty'], 2, ',', '.') . ' ' . ($aItem['satuan_nm'] ?? '');
                $hrgFmt = 'Rp ' . number_format((float)$aItem['harga_nominal'], 0, ',', '.');
                $diffs[] = [
                    'field'   => 'item_tambah',
                    'label'   => "Tambah Barang: {$bNm}",
                    'sebelum' => '-',
                    'sesudah' => "Qty: {$qtyFmt} @ {$hrgFmt}",
                    'tipe'    => 'ITEM_TAMBAH',
                ];
                $summaryParts[] = "Menambahkan barang baru '{$bNm}' ({$qtyFmt})";
            }
        }

        // Item Dihapus
        foreach ($beforeItems as $bId => $bItem) {
            if (!$afterItems->has($bId)) {
                $bNm = $bItem['barang_nm'] ?? "Barang #{$bId}";
                $qtyFmt = number_format((float)$bItem['pesan_qty'], 2, ',', '.') . ' ' . ($bItem['satuan_nm'] ?? '');
                $diffs[] = [
                    'field'   => 'item_hapus',
                    'label'   => "Hapus Barang: {$bNm}",
                    'sebelum' => "Qty: {$qtyFmt}",
                    'sesudah' => 'Dihapus dari PO',
                    'tipe'    => 'ITEM_HAPUS',
                ];
                $summaryParts[] = "Menghapus item '{$bNm}'";
            }
        }

        // Item Yang Dimodifikasi (Qty, Harga, Diskon)
        foreach ($beforeItems as $bId => $bItem) {
            if ($afterItems->has($bId)) {
                $aItem = $afterItems->get($bId);
                $bNm = $aItem['barang_nm'] ?? ($bItem['barang_nm'] ?? "Barang #{$bId}");
                $satuan = $aItem['satuan_nm'] ?? ($bItem['satuan_nm'] ?? '');

                // Cek Qty Pesan
                $oldQty = (float) $bItem['pesan_qty'];
                $newQty = (float) $aItem['pesan_qty'];
                if (abs($oldQty - $newQty) > 0.0001) {
                    $diffs[] = [
                        'field'   => "item_{$bId}_qty",
                        'label'   => "Kuantitas {$bNm}",
                        'sebelum' => number_format($oldQty, 2, ',', '.') . " {$satuan}",
                        'sesudah' => number_format($newQty, 2, ',', '.') . " {$satuan}",
                        'tipe'    => 'ITEM_QTY',
                    ];
                    $summaryParts[] = "Qty '{$bNm}': " . number_format($oldQty, 2, ',', '.') . " ➔ " . number_format($newQty, 2, ',', '.') . " {$satuan}";
                }

                // Cek Harga Satuan
                $oldHrg = (float) $bItem['harga_nominal'];
                $newHrg = (float) $aItem['harga_nominal'];
                if (abs($oldHrg - $newHrg) > 0.01) {
                    $oldHrgFmt = 'Rp ' . number_format($oldHrg, 0, ',', '.');
                    $newHrgFmt = 'Rp ' . number_format($newHrg, 0, ',', '.');
                    $diffs[] = [
                        'field'   => "item_{$bId}_harga",
                        'label'   => "Harga Satuan {$bNm}",
                        'sebelum' => $oldHrgFmt,
                        'sesudah' => $newHrgFmt,
                        'tipe'    => 'ITEM_HARGA',
                    ];
                    $summaryParts[] = "Harga '{$bNm}': {$oldHrgFmt} ➔ {$newHrgFmt}";
                }

                // Cek Diskon Persen
                $oldDisc = (float) ($bItem['diskon_persen'] ?? 0);
                $newDisc = (float) ($aItem['diskon_persen'] ?? 0);
                if (abs($oldDisc - $newDisc) > 0.01) {
                    $diffs[] = [
                        'field'   => "item_{$bId}_diskon",
                        'label'   => "Diskon {$bNm}",
                        'sebelum' => "{$oldDisc}%",
                        'sesudah' => "{$newDisc}%",
                        'tipe'    => 'ITEM_DISKON',
                    ];
                    $summaryParts[] = "Diskon '{$bNm}': {$oldDisc}% ➔ {$newDisc}%";
                }
            }
        }

        return [
            'diffs'        => $diffs,
            'summaryParts' => $summaryParts,
        ];
    }

    /**
     * Membatalkan PO jika belum ada penerimaan barang.
     */
    public function cancel(int $id, ?string $reason = null): DatPoHdr
    {
        return DB::transaction(function () use ($id, $reason) {
            $po = DatPoHdr::with('details')->findOrFail($id);

            $sudahAdaPenerimaan = $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);
            if ($sudahAdaPenerimaan) {
                throw new Exception("PO tidak dapat dibatalkan karena sudah ada barang yang diterima.");
            }

            $po->update([
                'status_cd'   => 'CANCELLED',
                'catatan_txt' => trim(($po->catatan_txt ? $po->catatan_txt . "\n" : "") . "Dibatalkan: " . $reason),
            ]);

            return $po;
        });
    }

    /**
     * Menghapus dokumen PO secara aman (Soft Delete).
     * Hanya diizinkan jika belum ada penerimaan barang fisik di gudang.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $po = DatPoHdr::with(['details', 'penerimaan'])->findOrFail($id);

            $sudahAdaTerima = $po->penerimaan()->where('deleted_st', false)->exists()
                || $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);

            if ($sudahAdaTerima) {
                throw new Exception("PO {$po->po_no} tidak dapat dihapus karena sudah ada riwayat penerimaan barang fisik (GRN). Silakan hapus/batalkan data penerimaan terlebih dahulu jika ingin menghapus dokumen PO ini.");
            }

            $userName = auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->name ?? 'SUPERADMIN');

            $po->update([
                'deleted_st' => true,
                'deleted_by' => $userName,
                'status_cd'  => 'CANCELLED',
            ]);

            DatPoDtl::where('po_id', $po->po_id)->update([
                'deleted_st' => true,
                'deleted_by' => $userName,
            ]);

            return true;
        });
    }

    /**
     * Menutup paksa PO yang statusnya masih PARTIAL jika sisa kuota barang
     * tidak dapat/tidak akan dikirim lagi oleh supplier.
     */
    public function forceClose(int $id, string $reason, ?string $userName = null): DatPoHdr
    {
        return DB::transaction(function () use ($id, $reason, $userName) {
            $po = DatPoHdr::with('details')->findOrFail($id);

            if ($po->status_cd !== 'PARTIAL') {
                throw new Exception("Hanya Purchase Order dengan status 'PARTIAL' (sebagian diterima) yang dapat diselesaikan dengan pembatalan sisa.");
            }

            if ($po->total_sisa_qty <= 0) {
                throw new Exception("Semua kuantitas barang pada PO ini sudah diterima penuh.");
            }

            $dateStr = date('d/m/Y H:i');
            $userStr = $userName ?: 'Petugas Gudang / Purchasing';
            $sisaQtyFormatted = number_format($po->total_sisa_qty, 2, ',', '.');
            $catatanTambahan = "[SELESAI / BATAL SISA - {$dateStr}] Diselesaikan oleh {$userStr}. Sisa kuota pesanan {$sisaQtyFormatted} resmi dibatalkan. Alasan: {$reason}.";

            $po->update([
                'status_cd'     => 'COMPLETED',
                'closed_at'     => now(),
                'closed_by'     => $userStr,
                'closed_reason' => $reason,
                'catatan_txt'   => trim(($po->catatan_txt ? $po->catatan_txt . "\n" : "") . $catatanTambahan),
            ]);

            return $po->fresh(['details.barang']);
        });
    }

    /**
     * Mengambil daftar PO yang statusnya aktif (APPROVED atau PARTIAL) untuk dropdown Penerimaan Barang.
     */
    public function getOpenPoList(?int $supplierId = null, int|array|null $gudangId = null): Collection
    {
        $query = DatPoHdr::with(['supplier', 'gudang', 'details.barang.satuanDasar'])
            ->whereIn('status_cd', ['APPROVED', 'PARTIAL'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->orderBy('po_tgl', 'desc')->get();
    }

    /**
     * Kirim email permohonan persetujuan PO ke atasan
     */
    public function sendApprovalEmail(DatPoHdr $po): bool
    {
        try {
            $po->loadMissing(['approver', 'supplier', 'details.barang.satuanDasar']);
            if (!$po->approver || empty($po->approver->email)) {
                return false;
            }

            if (empty($po->approval_token)) {
                $po->approval_token = Str::random(40);
                $po->saveQuietly();
            }

            Mail::to($po->approver->email)->send(new PoApprovalRequestMail($po));
            return true;
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim email approval PO {$po->po_no} ke {$po->approver?->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Setujui Purchase Order oleh atasan
     */
    public function approvePo(int|string $identifier, ?string $approverName = null, ?string $notes = null): DatPoHdr
    {
        return DB::transaction(function () use ($identifier, $approverName, $notes) {
            $query = is_numeric($identifier)
                ? DatPoHdr::where('po_id', $identifier)
                : DatPoHdr::where('approval_token', $identifier);

            $po = $query->with('approver')->firstOrFail();

            if ($po->status_cd === 'APPROVED') {
                return $po; // Sudah disetujui sebelumnya
            }

            if (in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED'])) {
                throw new Exception("Dokumen PO {$po->po_no} sudah berstatus {$po->status_cd} dan tidak dapat diubah.");
            }

            $currentUser = Auth::user();
            $approver = $approverName ?: ($currentUser?->name ?: ($po->approver?->name ?: 'Pimpinan / Direksi'));

            $updatePayload = [
                'status_cd'      => 'APPROVED',
                'approved_by'    => $approver,
                'approved_at'    => now(),
                'approval_notes' => $notes ?: 'Disetujui secara resmi oleh atasan.',
                'rejected_by'    => null,
                'rejected_at'    => null,
                'reject_reason'  => null,
            ];

            // Jika yang menyetujui adalah user atasan yang sedang login di sistem, sinkronkan approver_id
            if ($currentUser && method_exists($currentUser, 'canApprovePo') && $currentUser->canApprovePo()) {
                $updatePayload['approver_id'] = $currentUser->id;
            }

            $po->update($updatePayload);

            return $po->fresh(['details.barang', 'supplier', 'gudang', 'approver']);
        });
    }

    /**
     * Tolak Purchase Order oleh atasan
     */
    public function rejectPo(int|string $identifier, ?string $rejectorName = null, string $reason = ''): DatPoHdr
    {
        return DB::transaction(function () use ($identifier, $rejectorName, $reason) {
            $query = is_numeric($identifier)
                ? DatPoHdr::where('po_id', $identifier)
                : DatPoHdr::where('approval_token', $identifier);

            $po = $query->with('approver')->firstOrFail();

            if (in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED'])) {
                throw new Exception("Dokumen PO {$po->po_no} sudah berstatus {$po->status_cd} dan tidak dapat diubah.");
            }

            $rejector = $rejectorName ?: ($po->approver?->name ?: (Auth::user()?->name ?: 'Pimpinan / Direksi'));

            $po->update([
                'status_cd'     => 'REJECTED',
                'rejected_by'   => $rejector,
                'rejected_at'   => now(),
                'reject_reason' => $reason ?: 'Pengajuan ditolak oleh atasan.',
            ]);

            return $po->fresh(['details.barang', 'supplier', 'gudang', 'approver']);
        });
    }

    /**
     * Minta tinjauan / penyesuaian kuantitas/harga ke staf pengadaan oleh atasan
     */
    public function requestRevision(int|string $identifier, ?string $reviewerName = null, string $revisionNotes = ''): DatPoHdr
    {
        return DB::transaction(function () use ($identifier, $reviewerName, $revisionNotes) {
            $query = is_numeric($identifier)
                ? DatPoHdr::where('po_id', $identifier)
                : DatPoHdr::where('approval_token', $identifier);

            $po = $query->with('approver')->firstOrFail();

            if (in_array($po->status_cd, ['COMPLETED', 'CLOSED', 'CANCELLED'])) {
                throw new Exception("Dokumen PO {$po->po_no} sudah berstatus {$po->status_cd} dan tidak dapat diubah.");
            }

            $reviewer = $reviewerName ?: ($po->approver?->name ?: (Auth::user()?->name ?: 'Pimpinan / Direksi'));

            $po->update([
                'status_cd'     => 'REVISION',
                'rejected_by'   => $reviewer,
                'rejected_at'   => now(),
                'reject_reason' => $revisionNotes ?: 'Atasan meminta peninjauan dan penyesuaian dokumen PO.',
            ]);

            return $po->fresh(['details.barang', 'supplier', 'gudang', 'approver']);
        });
    }

    /**
     * Kirim dokumen resmi Purchase Order yang sudah disetujui ke email supplier
     */
    public function sendToSupplier(int $poId, string $supplierEmail, ?string $customNotes = null, ?string $ccEmail = null): DatPoHdr
    {
        $po = DatPoHdr::findOrFail($poId);

        if (!in_array($po->status_cd, ['APPROVED', 'PARTIAL'])) {
            throw new Exception("Hanya Purchase Order yang sudah disetujui resmi (APPROVED) yang dapat dikirim ke supplier.");
        }

        if (empty($supplierEmail)) {
            throw new Exception("Alamat email supplier tujuan wajib diisi.");
        }

        try {
            $mail = new PoSupplierDispatchMail($po, $customNotes);

            $mailable = Mail::to($supplierEmail);
            if (!empty($ccEmail)) {
                $ccList = array_map('trim', explode(',', $ccEmail));
                $mailable->cc($ccList);
            }

            $mailable->send($mail);

            $user = Auth::user();
            $senderName = $user?->name ?: 'Admin Purchasing';

            $po->update([
                'sent_to_supplier_at'   => now(),
                'sent_to_supplier_by'   => $senderName,
                'supplier_email_sent'   => $supplierEmail,
                'supplier_email_notes'  => $customNotes,
            ]);

            return $po->fresh(['details.barang', 'supplier', 'gudang', 'approver']);
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email PO ke supplier: " . $e->getMessage());
            throw new Exception("Gagal mengirim email ke supplier: " . $e->getMessage());
        }
    }
}
