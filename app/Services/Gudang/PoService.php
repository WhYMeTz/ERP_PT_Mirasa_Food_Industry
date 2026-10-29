<?php

namespace App\Services\Gudang;

use App\Mail\PoApprovalRequestMail;
use App\Mail\PoSupplierDispatchMail;
use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
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
        $query = DatPoHdr::with(['supplier', 'gudang', 'approver', 'details.barang.satuanDasar'])
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
            'penerimaan.details'
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

            $pajakIds = collect($items)->pluck('pajak_id')->filter()->unique()->toArray();
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

                $subtotalBruto = 0;
                $diskonTotal = 0;
                $totalPotongan = 0;
                $totalDpp = 0;
                $totalPpn = 0;
                $grandTotal = 0;

                foreach ($supplierItems as $item) {
                    $qty = (float) ($item['pesan_qty'] ?? 0);
                    $harga = (float) ($item['harga_nominal'] ?? 0);
                    $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                    $diskonUnit = $harga * ($diskonPersen / 100);
                    $hargaNetto = max(0, $harga - $diskonUnit);
                    $potonganNominal = (float) ($item['potongan_nominal'] ?? 0);
                    $grossRowTotal = max(0, ($qty * $hargaNetto) - $potonganNominal);

                    $pajakId = !empty($item['pajak_id']) ? (int) $item['pajak_id'] : null;
                    $pajak = $pajakId ? ($taxesById[$pajakId] ?? null) : null;
                    $tarifPersen = $pajak ? (float) $pajak->tarif_persen : (($item['ppn_tipe'] ?? '') === 'PPN_11' ? 11.00 : 0.0);
                    $ppnCode = $pajak ? $pajak->pajak_cd : ($tarifPersen > 0 ? 'PPN_11' : 'NON_PPN');
                    $sifatPajak = $pajak ? $pajak->sifat_pajak : 'TAMBAH';

                    $rowTaxInclusive = isset($item['is_harga_termasuk_pajak'])
                        ? filter_var($item['is_harga_termasuk_pajak'], FILTER_VALIDATE_BOOLEAN)
                        : $headerTaxInclusive;

                    if ($rowTaxInclusive && $tarifPersen > 0) {
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
                    $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                    $diskonUnit = $harga * ($diskonPersen / 100);
                    $hargaNetto = max(0, $harga - $diskonUnit);
                    $potonganNominal = (float) ($item['potongan_nominal'] ?? 0);
                    $grossRowTotal = max(0, ($qty * $hargaNetto) - $potonganNominal);

                    $pajakId = !empty($item['pajak_id']) ? (int) $item['pajak_id'] : null;
                    $pajak = $pajakId ? ($taxesById[$pajakId] ?? null) : null;
                    $tarifPersen = $pajak ? (float) $pajak->tarif_persen : (($item['ppn_tipe'] ?? '') === 'PPN_11' ? 11.00 : 0.0);
                    $ppnCode = $pajak ? $pajak->pajak_cd : ($tarifPersen > 0 ? 'PPN_11' : 'NON_PPN');
                    $sifatPajak = $pajak ? $pajak->sifat_pajak : 'TAMBAH';

                    $rowTaxInclusive = isset($item['is_harga_termasuk_pajak'])
                        ? filter_var($item['is_harga_termasuk_pajak'], FILTER_VALIDATE_BOOLEAN)
                        : $headerTaxInclusive;

                    if ($rowTaxInclusive && $tarifPersen > 0) {
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
            $po = DatPoHdr::with('details.barang')->findOrFail($id);

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

            $pajakIds = collect($items)->pluck('pajak_id')->filter()->unique()->toArray();
            $taxesById = !empty($pajakIds) ? MstPajak::whereIn('pajak_id', $pajakIds)->get()->keyBy('pajak_id') : collect();

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
                $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                $diskonUnit = $harga * ($diskonPersen / 100);
                $hargaNetto = max(0, $harga - $diskonUnit);
                $potonganNominal = (float) ($item['potongan_nominal'] ?? 0);
                $grossRowTotal = max(0, ($qty * $hargaNetto) - $potonganNominal);

                $pajakId = !empty($item['pajak_id']) ? (int) $item['pajak_id'] : null;
                $pajak = $pajakId ? ($taxesById[$pajakId] ?? null) : null;
                $tarifPersen = $pajak ? (float) $pajak->tarif_persen : (($item['ppn_tipe'] ?? '') === 'PPN_11' ? 11.00 : 0.0);
                $ppnCode = $pajak ? $pajak->pajak_cd : ($tarifPersen > 0 ? 'PPN_11' : 'NON_PPN');
                $sifatPajak = $pajak ? $pajak->sifat_pajak : 'TAMBAH';

                $rowTaxInclusive = isset($item['is_harga_termasuk_pajak'])
                    ? filter_var($item['is_harga_termasuk_pajak'], FILTER_VALIDATE_BOOLEAN)
                    : $headerTaxInclusive;

                if ($rowTaxInclusive && $tarifPersen > 0) {
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
            } elseif ($po->status_cd === 'REJECTED') {
                $newStatusCd = 'PENDING_APPROVAL'; // Pengajuan ulang
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

            return $po->fresh(['supplier', 'details.barang', 'details.pajak']);
        });
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

            $approver = $approverName ?: ($po->approver?->name ?: (Auth::user()?->name ?: 'Pimpinan / Direksi'));

            $po->update([
                'status_cd'      => 'APPROVED',
                'approved_by'    => $approver,
                'approved_at'    => now(),
                'approval_notes' => $notes ?: 'Disetujui secara resmi oleh atasan.',
                'rejected_by'    => null,
                'rejected_at'    => null,
                'reject_reason'  => null,
            ]);

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
