<?php

namespace App\Services\Penjualan;

use App\Models\Penjualan\DatSoDtl;
use App\Models\Penjualan\DatSoHdr;
use App\Models\Penjualan\DatSoReturDtl;
use App\Models\Penjualan\DatSoReturHdr;
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
            'returns.gudang',
            'returns.details.barang.satuanDasar',
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

            // Deteksi otomatis jika pelanggan adalah IFM / Indofood Fortuna
            $custId = $data['customer_id'] ?? null;
            $isIfm = false;
            $calculated = $this->calculateOrderTotals($data['items'] ?? [], (float) ($data['potongan_nominal'] ?? 0));

            $klasifikasi = null;
            $wilayah = null;
            if ($custId) {
                $cust = \App\Models\MasterData\MstCustomer::find($custId);
                if ($cust) {
                    $klasifikasi = $cust->klasifikasi;
                    $wilayah = $cust->wilayah ?: ($cust->alamat_txt ?: $cust->customer_nm);

                    if (empty($klasifikasi)) {
                        $nmUpper = strtoupper($cust->customer_nm);
                        $cdUpper = strtoupper($cust->customer_cd);
                        if (str_contains($nmUpper, 'INDOFOOD') || str_contains($nmUpper, 'IFM') || str_contains($nmUpper, 'FORTUNA') || str_contains($cdUpper, 'IFM') || str_contains($cdUpper, 'INDOFOOD')) {
                            $klasifikasi = 'IFM';
                        } elseif (str_contains($nmUpper, 'ALFA') || str_contains($nmUpper, 'INDOMARET') || str_contains($nmUpper, 'MIDI') || str_contains($cdUpper, 'IDM') || str_contains($cdUpper, 'ALFA')) {
                            $klasifikasi = 'IDM_ALFA';
                        } else {
                            $klasifikasi = 'MANUAL';
                        }
                    }
                }
            }

            // Tentukan status PPN order (dari request form atau hasil kalkulasi rincian barang)
            $isPpn = false;
            if (isset($data['ppn_tipe_header']) && !empty($data['ppn_tipe_header'])) {
                $isPpn = ($data['ppn_tipe_header'] === 'PPN_11');
            } else {
                $isPpn = ($calculated['ppn_nominal'] > 0 || $calculated['ppn_tipe'] === 'PPN_11');
            }

            // Generate Faktur & Surat Jalan awal
            $fakturNo = !empty($data['faktur_no']) 
                ? $data['faktur_no'] 
                : $this->codeGenerator->generateFakturByKlasifikasi($klasifikasi, $wilayah, $isPpn, $soTgl);
            $suratJalanNo = !empty($data['surat_jalan_no']) 
                ? $data['surat_jalan_no'] 
                : $this->codeGenerator->generateSuratJalanNo($soTgl);

            $topTxt = !empty($data['top_txt']) ? $data['top_txt'] : ($cust?->top_txt ?? null);

            $header = DatSoHdr::create([
                'so_no'              => $soNo,
                'faktur_no'          => $fakturNo,
                'surat_jalan_no'     => $suratJalanNo,
                'so_tgl'             => $soTgl,
                'customer_id'        => $data['customer_id'],
                'customer_po_no'     => $data['customer_po_no'] ?? null,
                'top_txt'            => $topTxt,
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
                'faktur_no'          => $data['faktur_no'] ?? $header->faktur_no,
                'surat_jalan_no'     => $data['surat_jalan_no'] ?? $header->surat_jalan_no,
                'customer_id'        => $data['customer_id'] ?? $header->customer_id,
                'customer_po_no'     => $data['customer_po_no'] ?? $header->customer_po_no,
                'top_txt'            => $data['top_txt'] ?? $header->top_txt,
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
     * Catat Retur Penjualan (Customer Return) untuk SO yang telah terkirim.
     * Mengembalikan kuantitas fisik ke Gudang (default Gudang FG) dengan Batch RET-[SO_NO]
     * serta mencatat penyesuaian komersial (potong tagihan / tukar guling).
     */
    public function recordSalesReturn(int $id, array $data, $user): DatSoReturHdr
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $header = DatSoHdr::with(['details.barang.satuanDasar', 'customer'])->lockForUpdate()->findOrFail($id);

            if (!in_array($header->status_cd, ['COMPLETED', 'PARTIAL'])) {
                throw new Exception("Retur penjualan hanya dapat diproses untuk pesanan yang sudah berstatus terkirim (Status: {$header->status_cd}).");
            }

            // Gudang masuk retur: default ke GDG-FG (Gudang Barang Jadi / pabrik utama)
            $defaultGudangId = \App\Models\MasterData\MstGudang::where('gudang_cd', 'GDG-FG')->value('gudang_id') ?? 6;
            $gudangId = !empty($data['gudang_id']) ? (int) $data['gudang_id'] : $defaultGudangId;

            $tglRetur = $data['retur_tgl'] ?? now()->toDateString();
            $returNo = $this->codeGenerator->generateSoReturNo($tglRetur);
            $tindakanCd = in_array($data['tindakan_cd'] ?? '', ['POTONG_TAGIHAN', 'TUKAR_GULING'])
                ? $data['tindakan_cd']
                : 'POTONG_TAGIHAN';

            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new Exception("Pilih setidaknya 1 item barang yang akan diretur.");
            }

            $detailsToSave = [];
            $totalQtyRetur = 0;
            $totalNilaiRetur = 0;
            $batchNo = 'RET-' . $header->so_no;

            foreach ($rawItems as $item) {
                $sodtlId = (int) ($item['sodtl_id'] ?? 0);
                $qtyRetur = (float) ($item['qty_retur'] ?? 0);

                if ($qtyRetur <= 0) {
                    continue;
                }

                $dtl = $header->details->firstWhere('sodtl_id', $sodtlId);
                if (!$dtl) {
                    throw new Exception("Item pesanan dengan ID {$sodtlId} tidak ditemukan pada dokumen SO ini.");
                }

                // Cek akumulasi retur terdahulu yang masih aktif
                $alreadyReturQty = (float) DatSoReturDtl::where('sodtl_id', $sodtlId)
                    ->whereHas('header', fn($q) => $q->where('status_cd', 'CONFIRMED')->where('deleted_st', false))
                    ->sum('qty_retur');

                $maxReturnable = max(0, (float) $dtl->kirim_qty - $alreadyReturQty);
                if ($qtyRetur > $maxReturnable) {
                    $barangNm = $dtl->barang?->barang_nm ?? "Item #{$sodtlId}";
                    throw new Exception("Kuantitas retur untuk '{$barangNm}' ({$qtyRetur}) melebihi sisa barang yang terkirim ({$maxReturnable}).");
                }

                // Hitung harga efektif per satuan (termasuk diskon & proporsi tagihan netto)
                $effectiveUnitPrice = (float) $dtl->pesan_qty > 0
                    ? ((float) $dtl->subtotal_tagihan / (float) $dtl->pesan_qty)
                    : (float) $dtl->harga_satuan;

                $subtotalRow = round($qtyRetur * $effectiveUnitPrice, 2);

                // Kembalikan fisik stok ke gudang yang dipilih dengan Batch khusus retur
                $ket = "Retur Penjualan SO {$header->so_no} (" . ($data['alasan_txt'] ?? 'Retur Customer') . ")";
                $this->stokService->addStock(
                    gudangId: $gudangId,
                    barangId: $dtl->barang_id,
                    batchNo: $batchNo,
                    qty: $qtyRetur,
                    expiredTgl: null,
                    dokumenNo: $returNo,
                    keterangan: $ket,
                    hargaSatuan: (float) $dtl->harga_satuan
                );

                $detailsToSave[] = [
                    'sodtl_id'       => $sodtlId,
                    'barang_id'      => $dtl->barang_id,
                    'batch_no'       => $batchNo,
                    'qty_retur'      => $qtyRetur,
                    'harga_satuan'   => $effectiveUnitPrice,
                    'subtotal_nilai' => $subtotalRow,
                    'kondisi_cd'     => $item['kondisi_cd'] ?? 'RUSAK_KEMASAN',
                    'catatan_txt'    => $item['catatan_txt'] ?? null,
                ];

                $totalQtyRetur += $qtyRetur;
                $totalNilaiRetur += $subtotalRow;
            }

            if ($totalQtyRetur <= 0) {
                throw new Exception("Total kuantitas barang yang diretur harus lebih besar dari 0.");
            }

            $userName = $user instanceof \App\Models\User ? $user->nama_lengkap : ($user ?? auth()->user()?->nama_lengkap ?? 'System');

            $returHdr = DatSoReturHdr::create([
                'retur_no'    => $returNo,
                'retur_tgl'   => $tglRetur,
                'so_id'       => $header->so_id,
                'customer_id' => $header->customer_id,
                'gudang_id'   => $gudangId,
                'tindakan_cd' => $tindakanCd,
                'total_qty'   => $totalQtyRetur,
                'total_nilai' => $totalNilaiRetur,
                'alasan_txt'  => $data['alasan_txt'] ?? null,
                'status_cd'   => 'CONFIRMED',
                'created_by'  => $userName,
                'active_st'   => true,
                'deleted_st'  => false,
                'version_no'  => 1,
            ]);

            foreach ($detailsToSave as $dtlData) {
                $dtlData['retur_id'] = $returHdr->retur_id;
                DatSoReturDtl::create($dtlData);
            }

            // Catat ke log catatan SO
            $actionLabel = $tindakanCd === 'POTONG_TAGIHAN' ? 'Potong Tagihan' : 'Tukar Guling';
            $note = ($header->catatan_txt ? $header->catatan_txt . "\n" : "") .
                "[RETUR PENJUALAN {$returNo}]: {$totalQtyRetur} unit diretur ({$actionLabel}).";
            $header->update([
                'catatan_txt' => $note,
                'version_no'  => $header->version_no + 1,
            ]);

            Log::info("Retur Penjualan {$returNo} untuk SO {$header->so_no} berhasil dicatat: {$totalQtyRetur} unit, Rp {$totalNilaiRetur} oleh {$userName}");

            return $returHdr;
        });
    }

    /**
     * Batalkan dokumen retur penjualan (Void) dan keluarkan kembali stok yang telah dimasukkan.
     */
    public function voidSalesReturn(int $returId, string $reason, ?string $userName = null): DatSoReturHdr
    {
        return DB::transaction(function () use ($returId, $reason, $userName) {
            $retur = DatSoReturHdr::with(['details.barang', 'order'])->lockForUpdate()->findOrFail($returId);

            if ($retur->status_cd !== 'CONFIRMED') {
                throw new Exception("Hanya dokumen retur dengan status Terkonfirmasi yang dapat dibatalkan.");
            }

            // Keluarkan kembali stok fisik dari gudang retur
            foreach ($retur->details as $dtl) {
                $this->stokService->deductStock(
                    gudangId: $retur->gudang_id,
                    barangId: $dtl->barang_id,
                    batchNo: $dtl->batch_no,
                    qty: (float) $dtl->qty_retur,
                    dokumenNo: $retur->retur_no,
                    keterangan: "Pembatalan Retur Penjualan {$retur->retur_no}: {$reason}"
                );
            }

            $note = ($retur->alasan_txt ? $retur->alasan_txt . "\n" : "") . "[DIBATALKAN/VOID]: " . $reason;
            $retur->update([
                'status_cd'   => 'VOID',
                'alasan_txt'  => $note,
                'updated_by'  => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'version_no'  => $retur->version_no + 1,
            ]);

            Log::info("Dokumen Retur Penjualan {$retur->retur_no} telah dibatalkan (VOID) oleh " . ($userName ?? 'System'));

            return $retur;
        });
    }

    /**
     * Mengambil daftar Retur Penjualan dengan pagination, pencarian, dan filter bertingkat.
     */
    public function getReturnsPaginated(
        int $perPage = 15,
        ?string $search = null,
        array $filters = []
    ): LengthAwarePaginator {
        $query = DatSoReturHdr::with([
            'order',
            'customer',
            'gudang',
            'details.barang.satuanDasar'
        ])->active();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('retur_no', 'LIKE', "%{$search}%")
                  ->orWhere('alasan_txt', 'LIKE', "%{$search}%")
                  ->orWhereHas('order', fn($oq) => $oq->where('so_no', 'LIKE', "%{$search}%"))
                  ->orWhereHas('customer', fn($cq) => $cq->where('customer_nm', 'LIKE', "%{$search}%")->orWhere('customer_cd', 'LIKE', "%{$search}%"))
                  ->orWhereHas('details.barang', fn($bq) => $bq->where('barang_nm', 'LIKE', "%{$search}%")->orWhere('barang_cd', 'LIKE', "%{$search}%"));
            });
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['gudang_id'])) {
            $query->where('gudang_id', $filters['gudang_id']);
        }

        if (!empty($filters['tindakan_cd'])) {
            $query->where('tindakan_cd', $filters['tindakan_cd']);
        }

        if (!empty($filters['status_cd'])) {
            $query->where('status_cd', $filters['status_cd']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('retur_tgl', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('retur_tgl', '<=', $filters['date_to']);
        }

        return $query->orderBy('retur_tgl', 'desc')
            ->orderBy('retur_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * KPI Ringkasan Metrik Retur Penjualan.
     */
    public function getReturnKpi(): array
    {
        $base = DatSoReturHdr::active();

        $allConfirmed = (clone $base)->where('status_cd', 'CONFIRMED');

        $totalTrx = (clone $base)->count();
        $totalQty = (float) (clone $allConfirmed)->sum('total_qty');
        $totalNilai = (float) (clone $allConfirmed)->sum('total_nilai');
        $qtyBulanIni = (float) (clone $allConfirmed)
            ->whereMonth('retur_tgl', date('m'))
            ->whereYear('retur_tgl', date('Y'))
            ->sum('total_qty');

        return [
            'total_trx'     => $totalTrx,
            'total_qty'     => $totalQty,
            'total_nilai'   => $totalNilai,
            'qty_bulan_ini' => $qtyBulanIni,
        ];
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
