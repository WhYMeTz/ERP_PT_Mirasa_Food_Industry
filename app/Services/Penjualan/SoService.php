<?php

namespace App\Services\Penjualan;

use App\Models\Penjualan\DatSoDtl;
use App\Models\Penjualan\DatSoHdr;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SoService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator,
        protected StokService $stokService
    ) {}

    /**
     * Mengambil daftar PO Penjualan dengan pagination, pencarian, dan filter bertingkat.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        array $filters = []
    ): LengthAwarePaginator {
        $query = DatSoHdr::with([
            'customer',
            'details.barang.satuanDasar',
            'details.barang.jenisBarang',
            'payments.header.kasBank',
        ])->active();

        if (!empty($status)) {
            $query->where('status_cd', $status);
        }

        // Pencarian teks umum (Kode Pesanan, Customer, Nama Barang)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('so_no', 'LIKE', "%{$search}%")
                  ->orWhere('customer_po_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('customer_nm', 'LIKE', "%{$search}%")
                         ->orWhere('customer_cd', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('details.barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'LIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter Spesifik: Kode Barang
        if (!empty($filters['barang_cd'])) {
            $query->whereHas('details.barang', function ($bq) use ($filters) {
                $bq->where('barang_cd', 'LIKE', "%{$filters['barang_cd']}%");
            });
        }

        // Filter Spesifik: Nama Barang
        if (!empty($filters['barang_nm'])) {
            $query->whereHas('details.barang', function ($bq) use ($filters) {
                $bq->where('barang_nm', 'LIKE', "%{$filters['barang_nm']}%");
            });
        }

        // Filter Spesifik: ID Barang
        if (!empty($filters['barang_id'])) {
            $query->whereHas('details', function ($dq) use ($filters) {
                $dq->where('barang_id', $filters['barang_id']);
            });
        }

        // Filter Spesifik: Kode Customer
        if (!empty($filters['customer_cd'])) {
            $query->whereHas('customer', function ($cq) use ($filters) {
                $cq->where('customer_cd', 'LIKE', "%{$filters['customer_cd']}%");
            });
        }

        // Filter Spesifik: Nama Customer
        if (!empty($filters['customer_nm'])) {
            $query->whereHas('customer', function ($cq) use ($filters) {
                $cq->where('customer_nm', 'LIKE', "%{$filters['customer_nm']}%");
            });
        }

        // Filter Spesifik: ID Customer
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Filter Spesifik: Rentang Tanggal
        if (!empty($filters['date_from'])) {
            $query->whereDate('so_tgl', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('so_tgl', '<=', $filters['date_to']);
        }

        return $query->orderBy('so_tgl', 'desc')
            ->orderBy('so_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil detail 1 dokumen SO beserta relasi lengkap.
     */
    public function getById(int $id): DatSoHdr
    {
        return DatSoHdr::with([
            'customer',
            'details.barang.satuanDasar',
            'details.barang.jenisBarang',
            'payments.header.kasBank',
        ])->where('so_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan data PO Penjualan baru (Header & Detail).
     */
    public function store(array $data, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $soTgl = $data['so_tgl'] ?? date('Y-m-d');
            $soNo = !empty($data['so_no']) ? $data['so_no'] : $this->codeGenerator->generateSoNo($soTgl);

            // Generate Faktur & Surat Jalan awal
            $fakturNo = $this->codeGenerator->generateFakturNo($soTgl);
            $suratJalanNo = $this->codeGenerator->generateSuratJalanNo($soTgl);

            $calculated = $this->calculateOrderTotals($data['items'] ?? [], (float) ($data['potongan_nominal'] ?? 0));

            $header = DatSoHdr::create([
                'so_no'              => $soNo,
                'faktur_no'          => $fakturNo,
                'surat_jalan_no'     => $suratJalanNo,
                'so_tgl'             => $soTgl,
                'customer_id'        => $data['customer_id'],
                'customer_po_no'     => $data['customer_po_no'] ?? null,
                'tgl_kirim_estimasi' => $data['tgl_kirim_estimasi'] ?? null,
                'status_cd'          => 'APPROVED',
                'catatan_txt'        => $data['catatan_txt'] ?? null,
                'subtotal_bruto'     => $calculated['subtotal_bruto'],
                'diskon_total'       => $calculated['diskon_total'],
                'potongan_nominal'   => $calculated['potongan_nominal'],
                'dpp_nominal'        => $calculated['dpp_nominal'],
                'ppn_tipe'           => $calculated['ppn_tipe'],
                'ppn_persen'         => $calculated['ppn_persen'],
                'ppn_nominal'        => $calculated['ppn_nominal'],
                'total_tagihan'      => $calculated['total_tagihan'],
                'created_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'active_st'          => true,
                'deleted_st'         => false,
            ]);

            foreach ($calculated['items'] as $item) {
                DatSoDtl::create([
                    'so_id'            => $header->so_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $item['pesan_qty'],
                    'kirim_qty'        => 0,
                    'harga_satuan'     => $item['harga_satuan'],
                    'diskon_persen'    => $item['diskon_persen'],
                    'diskon_nominal'   => $item['diskon_nominal'],
                    'potongan_nominal' => $item['potongan_nominal'],
                    'harga_netto'      => $item['harga_netto'],
                    'subtotal_netto'   => $item['subtotal_netto'],
                    'ppn_tipe'         => $item['ppn_tipe'],
                    'ppn_persen'       => $item['ppn_persen'],
                    'ppn_nominal'      => $item['ppn_nominal'],
                    'subtotal_tagihan' => $item['subtotal_tagihan'],
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);
            }

            return $header;
        });
    }

    /**
     * Memperbarui data PO Penjualan.
     */
    public function update(int $id, array $data, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($id, $data, $userName) {
            $header = DatSoHdr::where('so_id', $id)->firstOrFail();

            if (in_array($header->status_cd, ['COMPLETED', 'CANCELLED'])) {
                throw new Exception("Dokumen SO dengan status {$header->status_cd} tidak dapat diubah.");
            }

            $calculated = $this->calculateOrderTotals($data['items'] ?? [], (float) ($data['potongan_nominal'] ?? 0));

            $header->update([
                'so_tgl'             => $data['so_tgl'] ?? $header->so_tgl,
                'customer_id'        => $data['customer_id'] ?? $header->customer_id,
                'customer_po_no'     => $data['customer_po_no'] ?? $header->customer_po_no,
                'tgl_kirim_estimasi' => $data['tgl_kirim_estimasi'] ?? $header->tgl_kirim_estimasi,
                'catatan_txt'        => $data['catatan_txt'] ?? $header->catatan_txt,
                'subtotal_bruto'     => $calculated['subtotal_bruto'],
                'diskon_total'       => $calculated['diskon_total'],
                'potongan_nominal'   => $calculated['potongan_nominal'],
                'dpp_nominal'        => $calculated['dpp_nominal'],
                'ppn_tipe'           => $calculated['ppn_tipe'],
                'ppn_persen'         => $calculated['ppn_persen'],
                'ppn_nominal'        => $calculated['ppn_nominal'],
                'total_tagihan'      => $calculated['total_tagihan'],
                'updated_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
            ]);

            // Hapus detail lama dan ganti baru
            DatSoDtl::where('so_id', $header->so_id)->delete();

            foreach ($calculated['items'] as $item) {
                DatSoDtl::create([
                    'so_id'            => $header->so_id,
                    'barang_id'        => $item['barang_id'],
                    'pesan_qty'        => $item['pesan_qty'],
                    'kirim_qty'        => 0,
                    'harga_satuan'     => $item['harga_satuan'],
                    'diskon_persen'    => $item['diskon_persen'],
                    'diskon_nominal'   => $item['diskon_nominal'],
                    'potongan_nominal' => $item['potongan_nominal'],
                    'harga_netto'      => $item['harga_netto'],
                    'subtotal_netto'   => $item['subtotal_netto'],
                    'ppn_tipe'         => $item['ppn_tipe'],
                    'ppn_persen'       => $item['ppn_persen'],
                    'ppn_nominal'      => $item['ppn_nominal'],
                    'subtotal_tagihan' => $item['subtotal_tagihan'],
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ]);
            }

            return $header;
        });
    }

    /**
     * Dapatkan peta ketersediaan stok fisik barang jadi di Gudang FG.
     *
     * @param array $barangIds
     * @return array<int, float> [barang_id => sisa_qty]
     */
    public function getStockAvailabilityMap(array $barangIds): array
    {
        $gudangFgId = \App\Models\MasterData\MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;

        return \App\Models\Gudang\DatStokBatch::where('gudang_id', $gudangFgId)
            ->whereIn('barang_id', $barangIds)
            ->selectRaw('barang_id, SUM(sisa_qty) as total_stok')
            ->groupBy('barang_id')
            ->pluck('total_stok', 'barang_id')
            ->map(fn($v) => (float) $v)
            ->toArray();
    }

    /**
     * Cek ketersediaan stok detail untuk 1 barang jadi (termasuk batch & exp date).
     */
    public function checkStockItem(int $barangId): array
    {
        $gudangFgId = \App\Models\MasterData\MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;

        $batches = \App\Models\Gudang\DatStokBatch::where('gudang_id', $gudangFgId)
            ->where('barang_id', $barangId)
            ->where('sisa_qty', '>', 0)
            ->orderByRaw('expired_tgl ASC NULLS LAST')
            ->get(['batch_no', 'sisa_qty', 'expired_tgl', 'harga_satuan']);

        $totalStok = (float) $batches->sum('sisa_qty');

        return [
            'barang_id'   => $barangId,
            'total_stok'  => $totalStok,
            'batch_count' => $batches->count(),
            'batches'     => $batches,
        ];
    }

    /**
     * Konfirmasi Pengiriman Surat Jalan & Pemotongan Stok Barang Jadi (FEFO / FIFO).
     *
     * @param int $id
     * @param array $data ['tgl_kirim_aktual' => '...', 'supir_nm' => '...', 'nopol_kendaraan' => '...']
     * @param string|null $userName
     * @return DatSoHdr
     * @throws Exception
     */
    public function confirmDelivery(int $id, array $data, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($id, $data, $userName) {
            $header = DatSoHdr::with(['details.barang.satuanDasar', 'customer'])->lockForUpdate()->findOrFail($id);

            if ($header->status_cd === 'COMPLETED') {
                throw new Exception("Dokumen SO {$header->so_no} sudah dalam status Selesai / Terkirim.");
            }

            if ($header->status_cd === 'CANCELLED') {
                throw new Exception("Dokumen SO {$header->so_no} yang telah dibatalkan tidak dapat dikirim.");
            }

            $gudangFgId = \App\Models\MasterData\MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;
            $tglKirim = $data['tgl_kirim_aktual'] ?? now()->toDateString();
            $suratJalanNo = $header->surat_jalan_no ?: $this->codeGenerator->generateSuratJalanNo($tglKirim);
            $totalPesanQty = 0;

            // 1. Eksekusi pemotongan stok FEFO untuk setiap rincian barang jadi
            foreach ($header->details as $dtl) {
                $qtyPesan = (float) $dtl->pesan_qty;
                $totalPesanQty += $qtyPesan;
                $barang = $dtl->barang;
                $barangNm = $barang ? $barang->barang_nm : "Barang ID {$dtl->barang_id}";

                $ket = "Pengiriman Surat Jalan {$suratJalanNo} (SO: {$header->so_no}) ke " . ($header->customer?->customer_nm ?? 'Customer');

                try {
                    $this->stokService->deductStockFifo(
                        gudangId: $gudangFgId,
                        barangId: $dtl->barang_id,
                        totalQty: $qtyPesan,
                        dokumenNo: $suratJalanNo,
                        keterangan: $ket
                    );
                } catch (Exception $e) {
                    throw new Exception("Gagal memproses pengiriman untuk '{$barangNm}': {$e->getMessage()}");
                }

                // Update kirim_qty pada detail
                $dtl->update([
                    'kirim_qty' => $qtyPesan,
                ]);
            }

            // 2. Perbarui Header PO Penjualan menjadi COMPLETED
            $header->update([
                'status_cd'          => 'COMPLETED',
                'surat_jalan_no'     => $suratJalanNo,
                'tgl_kirim_aktual'   => $tglKirim,
                'supir_nm'           => $data['supir_nm'] ?? $header->supir_nm,
                'nopol_kendaraan'    => $data['nopol_kendaraan'] ?? $header->nopol_kendaraan,
                'total_terkirim_qty' => $totalPesanQty,
                'updated_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'version_no'         => $header->version_no + 1,
            ]);

            Log::info("SO ID {$header->so_id} ({$header->so_no}) berhasil dikonfirmasi kirim dengan SJ: {$suratJalanNo} oleh " . ($userName ?? 'System'));

            return $header;
        });
    }

    /**
     * Batalkan pengiriman barang & kembalikan stok fisik ke Gudang FG.
     */
    public function voidDelivery(int $id, string $reason, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($id, $reason, $userName) {
            $header = DatSoHdr::with(['details.barang'])->lockForUpdate()->findOrFail($id);

            if ($header->status_cd !== 'COMPLETED') {
                throw new Exception("Hanya dokumen dengan status COMPLETED yang dapat dibatalkan pengirimannya.");
            }

            $gudangFgId = \App\Models\MasterData\MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;

            // Kembalikan stok fisik ke Gudang FG
            foreach ($header->details as $dtl) {
                $qtyToRestore = (float) $dtl->kirim_qty;
                if ($qtyToRestore > 0) {
                    $this->stokService->addStock(
                        gudangId: $gudangFgId,
                        barangId: $dtl->barang_id,
                        batchNo: 'RESTORE-SO',
                        qty: $qtyToRestore,
                        expiredTgl: null,
                        dokumenNo: $header->surat_jalan_no,
                        keterangan: "Pembatalan Pengiriman SO ({$header->so_no}): {$reason}",
                        hargaSatuan: (float) $dtl->harga_satuan
                    );
                    $dtl->update(['kirim_qty' => 0]);
                }
            }

            $note = $header->catatan_txt ? $header->catatan_txt . "\n[PENGIRIMAN DIBATALKAN]: {$reason}" : "[PENGIRIMAN DIBATALKAN]: {$reason}";

            $header->update([
                'status_cd'          => 'APPROVED',
                'total_terkirim_qty' => 0,
                'catatan_txt'        => $note,
                'updated_by'         => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'version_no'         => $header->version_no + 1,
            ]);

            return $header;
        });
    }

    /**
     * Catat Penerimaan Pembayaran Kas Masuk (BKM) untuk SO ini (Bisa Parsial / Cicilan / Lunas).
     */
    public function recordPayment(int $id, array $data, $user): \App\Models\Keuangan\DatKasMasukHdr
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $header = DatSoHdr::with(['customer', 'payments.header'])->lockForUpdate()->findOrFail($id);

            if ($header->status_cd === 'CANCELLED') {
                throw new Exception("Tidak dapat mencatat pembayaran untuk pesanan yang telah dibatalkan.");
            }

            $nominal = (float) $data['nominal'];
            if ($nominal <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari Rp 0.");
            }

            $sisa = $header->sisa_tagihan;
            if ($nominal > $sisa) {
                throw new Exception("Nominal pembayaran (Rp " . number_format($nominal, 0, ',', '.') . ") melebihi sisa tagihan pesanan (Rp " . number_format($sisa, 0, ',', '.') . ").");
            }

            $kasMasukService = app(\App\Services\Keuangan\KasMasukService::class);

            $payload = [
                'kas_bank_id'         => $data['kas_bank_id'],
                'tgl_terima'          => $data['tgl_terima'] ?? now()->toDateString(),
                'kategori_penerimaan' => 'PELUNASAN_PIUTANG',
                'customer_id'         => $header->customer_id,
                'penyetor_nama'       => !empty($data['penyetor_nama']) ? $data['penyetor_nama'] : ($header->customer?->customer_nm ?? 'Customer'),
                'metode_terima'       => $data['metode_terima'] ?? 'TRANSFER',
                'no_referensi_bank'   => $data['no_referensi_bank'] ?? null,
                'catatan'             => $data['catatan'] ?? "Pembayaran Piutang PO {$header->so_no} (Faktur: {$header->faktur_no})",
                'items'               => [
                    [
                        'so_id'        => $header->so_id,
                        'uraian'       => "Pembayaran PO {$header->so_no} - {$header->customer?->customer_nm}",
                        'qty'          => 1,
                        'satuan'       => 'Trx',
                        'harga_satuan' => $nominal,
                        'nominal'      => $nominal,
                        'catatan'      => $data['catatan'] ?? null,
                    ]
                ]
            ];

            $userModel = $user instanceof \App\Models\User ? $user : \App\Models\User::first();
            $bkm = $kasMasukService->storeKasMasuk($payload, $userModel);

            Log::info("Pembayaran SO ID {$header->so_id} ({$header->so_no}) sebesar Rp {$nominal} berhasil dicatat via BKM {$bkm->bkm_no}");

            return $bkm;
        });
    }

    /**
     * Membatalkan PO Penjualan.
     */
    public function cancel(int $id, ?string $reason = null, ?string $userName = null): DatSoHdr
    {
        return DB::transaction(function () use ($id, $reason, $userName) {
            $header = DatSoHdr::where('so_id', $id)->firstOrFail();

            if ($header->total_kirim_qty > 0) {
                throw new Exception("Dokumen SO tidak dapat dibatalkan karena sudah ada pengiriman barang.");
            }

            $note = $header->catatan_txt ? $header->catatan_txt . "\n[DIBATALKAN]: " . $reason : "[DIBATALKAN]: " . $reason;

            $header->update([
                'status_cd'   => 'CANCELLED',
                'catatan_txt' => $note,
                'updated_by'  => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
            ]);

            return $header;
        });
    }

    /**
     * Soft delete dokumen SO.
     */
    public function delete(int $id, ?string $userName = null): void
    {
        $header = DatSoHdr::where('so_id', $id)->firstOrFail();
        if ($header->total_kirim_qty > 0) {
            throw new Exception("SO yang sudah dalam proses pengiriman tidak dapat dihapus.");
        }

        $header->update([
            'deleted_st' => true,
            'deleted_by' => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
            'deleted_at' => now(),
        ]);
    }

    /**
     * Menghitung kalkulasi komersial item dan total header secara presisi.
     */
    public function calculateOrderTotals(array $rawItems, float $headerPotonganNominal = 0): array
    {
        $processedItems = [];
        $totalBruto = 0;
        $totalDiskon = 0;
        $totalPotonganDetail = 0;
        $totalDpp = 0;
        $totalPpn = 0;
        $hasPpn = false;
        $hasNonPpn = false;

        foreach ($rawItems as $item) {
            $qty = (float) ($item['pesan_qty'] ?? 0);
            $harga = (float) ($item['harga_satuan'] ?? 0);
            $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
            $potonganRp = (float) ($item['potongan_nominal'] ?? 0);
            $ppnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';

            $brutoBaris = $qty * $harga;
            $diskonBaris = $brutoBaris * ($diskonPersen / 100);
            $nettoBaris = max(0, $brutoBaris - $diskonBaris - $potonganRp);
            $hargaNetto = $qty > 0 ? ($nettoBaris / $qty) : 0;

            if ($ppnTipe === 'PPN_11') {
                $hasPpn = true;
                $ppnPersen = 11.0;
                $ppnBaris = round($nettoBaris * 0.11, 2);
            } else {
                $hasNonPpn = true;
                $ppnPersen = 0.0;
                $ppnBaris = 0.0;
            }

            $tagihanBaris = $nettoBaris + $ppnBaris;

            $totalBruto += $brutoBaris;
            $totalDiskon += $diskonBaris;
            $totalPotonganDetail += $potonganRp;
            $totalDpp += $nettoBaris;
            $totalPpn += $ppnBaris;

            $processedItems[] = [
                'barang_id'        => (int) $item['barang_id'],
                'pesan_qty'        => $qty,
                'harga_satuan'     => $harga,
                'diskon_persen'    => $diskonPersen,
                'diskon_nominal'   => $diskonBaris,
                'potongan_nominal' => $potonganRp,
                'harga_netto'      => $hargaNetto,
                'subtotal_netto'   => $nettoBaris,
                'ppn_tipe'         => $ppnTipe,
                'ppn_persen'       => $ppnPersen,
                'ppn_nominal'      => $ppnBaris,
                'subtotal_tagihan' => $tagihanBaris,
                'catatan_txt'      => $item['catatan_txt'] ?? null,
            ];
        }

        $grandPotongan = $totalPotonganDetail + $headerPotonganNominal;
        $finalDpp = max(0, $totalDpp - $headerPotonganNominal);
        $grandTotal = $finalDpp + $totalPpn;

        $overallPpnTipe = 'NON_PPN';
        if ($hasPpn && $hasNonPpn) {
            $overallPpnTipe = 'MIXED';
        } elseif ($hasPpn) {
            $overallPpnTipe = 'PPN_11';
        }

        return [
            'items'            => $processedItems,
            'subtotal_bruto'   => $totalBruto,
            'diskon_total'     => $totalDiskon,
            'potongan_nominal' => $grandPotongan,
            'dpp_nominal'      => $finalDpp,
            'ppn_tipe'         => $overallPpnTipe,
            'ppn_persen'       => $overallPpnTipe === 'PPN_11' ? 11.0 : ($overallPpnTipe === 'NON_PPN' ? 0.0 : 11.0),
            'ppn_nominal'      => $totalPpn,
            'total_tagihan'    => $grandTotal,
        ];
    }
}
