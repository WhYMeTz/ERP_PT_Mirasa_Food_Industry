<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

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
        $query = DatPoHdr::with(['supplier', 'gudang', 'details.barang.satuanDasar'])
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
            'details.barang.satuanDasar',
            'details.barang.satuanBesar',
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

            foreach ($itemsBySupplier as $supplierId => $supplierItems) {
                // Jika single supplier dan user mengetikkan po_no manual, gunakan itu.
                // Jika multi-supplier, generate nomor PO unik otomatis per supplier.
                if (!$isMultiSupplier && !empty($data['po_no'])) {
                    $poNo = trim($data['po_no']);
                } else {
                    $poNo = $this->codeGenerator->generatePoNo();
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
                    $subtotalNetto = max(0, ($qty * $hargaNetto) - $potonganNominal);
                    $ppnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
                    $ppnNominal = $ppnTipe === 'PPN_11' ? round($subtotalNetto * 0.11, 4) : 0;
                    $subtotalTagihan = $subtotalNetto + $ppnNominal;

                    $subtotalBruto += ($qty * $harga);
                    $diskonTotal += ($qty * $diskonUnit);
                    $totalPotongan += $potonganNominal;
                    $totalDpp += $subtotalNetto;
                    $totalPpn += $ppnNominal;
                    $grandTotal += $subtotalTagihan;
                }

                $header = DatPoHdr::create([
                    'po_no'               => $poNo,
                    'po_tgl'              => $data['po_tgl'] ?? date('Y-m-d'),
                    'tgl_estimasi_datang' => $data['tgl_estimasi_datang'] ?? null,
                    'supplier_id'         => $supplierId,
                    'gudang_id'           => $data['gudang_id'],
                    'status_cd'           => $data['status_cd'] ?? 'APPROVED', // Langsung siap diterima
                    'subtotal_bruto'      => $subtotalBruto,
                    'diskon_total'        => $diskonTotal,
                    'potongan_nominal'    => $totalPotongan,
                    'dpp_nominal'         => $totalDpp,
                    'ppn_nominal'         => $totalPpn,
                    'total_nominal'       => $grandTotal,
                    'total_tagihan'       => $grandTotal,
                    'catatan_txt'         => $data['catatan_txt'] ?? null,
                ]);

                foreach ($supplierItems as $item) {
                    $qty = (float) ($item['pesan_qty'] ?? 0);
                    $harga = (float) ($item['harga_nominal'] ?? 0);
                    $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                    $diskonUnit = $harga * ($diskonPersen / 100);
                    $hargaNetto = max(0, $harga - $diskonUnit);
                    $potonganNominal = (float) ($item['potongan_nominal'] ?? 0);
                    $subtotalNetto = max(0, ($qty * $hargaNetto) - $potonganNominal);
                    $ppnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
                    $ppnPersen = $ppnTipe === 'PPN_11' ? 11.00 : 0;
                    $ppnNominal = $ppnTipe === 'PPN_11' ? round($subtotalNetto * 0.11, 4) : 0;
                    $subtotalTagihan = $subtotalNetto + $ppnNominal;

                    DatPoDtl::create([
                        'po_id'            => $header->po_id,
                        'barang_id'        => $item['barang_id'],
                        'pesan_qty'        => $qty,
                        'harga_nominal'    => $harga,
                        'diskon_persen'    => $diskonPersen,
                        'diskon_nominal'   => $diskonUnit,
                        'potongan_nominal' => $potonganNominal,
                        'harga_netto'      => $hargaNetto,
                        'subtotal_netto'   => $subtotalNetto,
                        'ppn_tipe'         => $ppnTipe,
                        'ppn_persen'       => $ppnPersen,
                        'ppn_nominal'      => $ppnNominal,
                        'subtotal_nominal' => $subtotalTagihan,
                        'subtotal_tagihan' => $subtotalTagihan,
                        'terima_qty'       => 0,
                        'catatan_txt'      => $item['catatan_txt'] ?? null,
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

            if ($createdPOs->count() === 1) {
                return $createdPOs->first();
            }

            return $createdPOs;
        });
    }

    /**
     * Memperbarui dokumen PO jika status masih DRAFT atau APPROVED (belum ada penerimaan fisik).
     */
    public function update(int $id, array $data): DatPoHdr
    {
        return DB::transaction(function () use ($id, $data) {
            $po = DatPoHdr::with('details')->findOrFail($id);

            // Cek apakah sudah ada barang yang diterima
            $sudahAdaPenerimaan = $po->details->contains(fn($dtl) => (float) $dtl->terima_qty > 0);
            if ($sudahAdaPenerimaan) {
                throw new Exception("PO tidak dapat diedit karena sebagian atau seluruh barang sudah diterima di gudang.");
            }

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang dalam Purchase Order.");
            }

            $subtotalBruto = 0;
            $diskonTotal = 0;
            $totalPotongan = 0;
            $totalDpp = 0;
            $totalPpn = 0;
            $grandTotal = 0;

            foreach ($items as $item) {
                $qty = (float) ($item['pesan_qty'] ?? 0);
                $harga = (float) ($item['harga_nominal'] ?? 0);
                $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                $diskonUnit = $harga * ($diskonPersen / 100);
                $hargaNetto = max(0, $harga - $diskonUnit);
                $potonganNominal = (float) ($item['potongan_nominal'] ?? 0);
                $subtotalNetto = max(0, ($qty * $hargaNetto) - $potonganNominal);
                $ppnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
                $ppnNominal = $ppnTipe === 'PPN_11' ? round($subtotalNetto * 0.11, 4) : 0;
                $subtotalTagihan = $subtotalNetto + $ppnNominal;

                $subtotalBruto += ($qty * $harga);
                $diskonTotal += ($qty * $diskonUnit);
                $totalPotongan += $potonganNominal;
                $totalDpp += $subtotalNetto;
                $totalPpn += $ppnNominal;
                $grandTotal += $subtotalTagihan;
            }

            $po->update([
                'po_tgl'              => $data['po_tgl'] ?? $po->po_tgl,
                'tgl_estimasi_datang' => $data['tgl_estimasi_datang'] ?? $po->tgl_estimasi_datang,
                'supplier_id'         => $data['supplier_id'] ?? $po->supplier_id,
                'gudang_id'           => $data['gudang_id'] ?? $po->gudang_id,
                'subtotal_bruto'      => $subtotalBruto,
                'diskon_total'        => $diskonTotal,
                'potongan_nominal'    => $totalPotongan,
                'dpp_nominal'         => $totalDpp,
                'ppn_nominal'         => $totalPpn,
                'total_nominal'       => $grandTotal,
                'total_tagihan'       => $grandTotal,
                'catatan_txt'         => $data['catatan_txt'] ?? $po->catatan_txt,
            ]);

            // Hapus detail lama dan ganti dengan yang baru
            DatPoDtl::where('po_id', $po->po_id)->delete();

            foreach ($items as $item) {
                $qty = (float) ($item['pesan_qty'] ?? 0);
                $harga = (float) ($item['harga_nominal'] ?? 0);
                $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                $diskonUnit = $harga * ($diskonPersen / 100);
                $hargaNetto = max(0, $harga - $diskonUnit);
                $potonganNominal = (float) ($item['potongan_nominal'] ?? 0);
                $subtotalNetto = max(0, ($qty * $hargaNetto) - $potonganNominal);
                $ppnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
                $ppnPersen = $ppnTipe === 'PPN_11' ? 11.00 : 0;
                $ppnNominal = $ppnTipe === 'PPN_11' ? round($subtotalNetto * 0.11, 4) : 0;
                $subtotalTagihan = $subtotalNetto + $ppnNominal;

                DatPoDtl::create([
                    'po_id'            => $po->po_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $qty,
                    'harga_nominal'    => $harga,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskonUnit,
                    'potongan_nominal' => $potonganNominal,
                    'harga_netto'      => $hargaNetto,
                    'subtotal_netto'   => $subtotalNetto,
                    'ppn_tipe'         => $ppnTipe,
                    'ppn_persen'       => $ppnPersen,
                    'ppn_nominal'      => $ppnNominal,
                    'subtotal_nominal' => $subtotalTagihan,
                    'subtotal_tagihan' => $subtotalTagihan,
                    'terima_qty'       => 0,
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);
            }

            return $po->fresh(['details.barang']);
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
                throw new Exception("Hanya Purchase Order dengan status 'PARTIAL' (sebagian diterima) yang dapat ditutup paksa.");
            }

            if ($po->total_sisa_qty <= 0) {
                throw new Exception("Semua kuantitas barang pada PO ini sudah diterima penuh.");
            }

            $dateStr = date('d/m/Y H:i');
            $userStr = $userName ?: 'Petugas Gudang / Purchasing';
            $catatanTambahan = "[FORCE CLOSE - {$dateStr}] Ditutup oleh {$userStr}. Alasan: {$reason}. Sisa kuota pesanan resmi dibatalkan.";

            $po->update([
                'status_cd'     => 'CLOSED',
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
}
