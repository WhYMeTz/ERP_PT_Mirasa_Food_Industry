<?php

namespace App\Services\Penjualan;

use App\Models\Penjualan\DatFakturDtl;
use App\Models\Penjualan\DatFakturHdr;
use App\Models\Penjualan\DatPengeluaranHdr;
use App\Models\Penjualan\DatSoDtl;
use App\Models\Penjualan\DatSoHdr;
use App\Services\Common\CodeGeneratorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FakturPenjualanService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar data Faktur Penjualan dengan pagination dan filter.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): LengthAwarePaginator {
        $query = DatFakturHdr::with(['customer', 'order', 'pengeluaran', 'details.barang'])
            ->where('deleted_st', false)
            ->orderBy('faktur_tgl', 'desc')
            ->orderBy('faktur_id', 'desc');

        if (!empty($search)) {
            $searchTrim = trim($search);
            $query->where(function ($q) use ($searchTrim) {
                $q->where('faktur_no', 'ilike', "%{$searchTrim}%")
                  ->orWhere('customer_po_no', 'ilike', "%{$searchTrim}%")
                  ->orWhereHas('order', function ($sub) use ($searchTrim) {
                      $sub->where('so_no', 'ilike', "%{$searchTrim}%");
                  })
                  ->orWhereHas('pengeluaran', function ($sub) use ($searchTrim) {
                      $sub->where('pengeluaran_no', 'ilike', "%{$searchTrim}%");
                  })
                  ->orWhereHas('customer', function ($sub) use ($searchTrim) {
                      $sub->where('customer_nm', 'ilike', "%{$searchTrim}%")
                          ->orWhere('customer_cd', 'ilike', "%{$searchTrim}%");
                  });
            });
        }

        if (!empty($status)) {
            $query->where('status_pembayaran', strtoupper(trim($status)));
        }

        if (!empty($dateFrom)) {
            $query->whereDate('faktur_tgl', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('faktur_tgl', '<=', $dateTo);
        }

        return $query->paginate($perPage);
    }

    /**
     * Hitung 4 metrik operasional Faktur & Piutang Penjualan.
     */
    public function getMetrics(): array
    {
        $currentMonth = now()->format('Y-m');
        $base = DatFakturHdr::where('deleted_st', false)->where('status_pembayaran', '!=', 'BATAL');

        $totalPenjualanBulanIni = (float) (clone $base)
            ->whereRaw("TO_CHAR(faktur_tgl, 'YYYY-MM') = ?", [$currentMonth])
            ->sum('total_tagihan');

        $fakturBulanIni = (clone $base)
            ->whereRaw("TO_CHAR(faktur_tgl, 'YYYY-MM') = ?", [$currentMonth])
            ->count();

        $totalSisaPiutang = (float) (clone $base)
            ->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
            ->sum('sisa_piutang');

        $fakturLunasCount = (clone $base)
            ->where('status_pembayaran', 'LUNAS')
            ->count();

        // Jumlah Surat Jalan yang belum difakturkan
        $uninvoicedCount = DatPengeluaranHdr::where('deleted_st', false)
            ->where('status_cd', '!=', 'BATAL')
            ->whereDoesntHave('faktur', function ($q) {
                $q->where('deleted_st', false)->where('status_pembayaran', '!=', 'BATAL');
            })
            ->count();

        return [
            'total_dokumen'             => DatFakturHdr::where('deleted_st', false)->count(),
            'total_omzet'               => (float) (clone $base)->sum('total_tagihan'),
            'total_piutang'             => $totalSisaPiutang,
            'count_belum_lunas'         => (clone $base)->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])->count(),
            'count_lunas'               => $fakturLunasCount,
            'total_penjualan_bulan_ini' => $totalPenjualanBulanIni,
            'faktur_bulan_ini'          => $fakturBulanIni,
            'total_sisa_piutang'        => $totalSisaPiutang,
            'faktur_lunas_count'        => $fakturLunasCount,
            'uninvoiced_count'          => $uninvoicedCount,
        ];
    }

    /**
     * Mengambil daftar Surat Jalan yang sudah terbit tapi belum dibuatkan Faktur Penjualannya.
     */
    public function getUninvoicedSuratJalan(): Collection
    {
        return DatPengeluaranHdr::with(['order', 'customer', 'details.barang'])
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'BATAL')
            ->whereDoesntHave('faktur', function ($q) {
                $q->where('deleted_st', false)->where('status_pembayaran', '!=', 'BATAL');
            })
            ->orderBy('pengeluaran_tgl', 'desc')
            ->orderBy('pengeluaran_id', 'desc')
            ->get();
    }

    /**
     * Mengambil rincian Surat Jalan untuk dimuat ke form pembuatan Faktur Penjualan.
     */
    public function getSuratJalanDetailForInvoice(int $pengeluaranId): array
    {
        $pengeluaran = DatPengeluaranHdr::with([
            'customer',
            'order.details.barang.satuanDasar',
            'details.barang.satuanDasar',
            'details.orderDetail'
        ])
        ->where('deleted_st', false)
        ->findOrFail($pengeluaranId);

        $customer = $pengeluaran->customer;
        $order = $pengeluaran->order;
        $fakturTgl = now()->toDateString();

        // Hitung perkiraan jatuh tempo berdasarkan TOP
        $topTxt = $order?->top_txt ?: ($customer?->top_txt ?? '7 Hari');
        $jatuhTempo = $this->calculateDueDate($fakturTgl, $topTxt);

        // Tentukan status PPN
        $isPpn = ($order && ($order->ppn_nominal > 0 || $order->ppn_tipe === 'PPN_11'));
        if (!$isPpn && $customer) {
            $isPpn = ($customer->default_ppn === 'PPN_11');
        }

        // Generate No Faktur resmi standar mentor
        $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
        $wilayah = $customer?->wilayah ?: ($customer?->alamat_txt ?: $customer?->customer_nm);
        $generatedFakturNo = !empty($order?->faktur_no)
            ? $order->faktur_no
            : $this->codeGenerator->generateFakturByKlasifikasi($klasifikasi, $wilayah, $isPpn, $fakturTgl);

        // Agregasi item dari baris detail Surat Jalan
        $items = [];
        $subtotalBruto = 0;
        $totalDiskon = 0;
        $totalDpp = 0;
        $totalPpn = 0;
        $totalTagihan = 0;

        foreach ($pengeluaran->details as $dtl) {
            $soDtl = $dtl->orderDetail;
            $qty = (float) $dtl->kirim_qty;
            $hargaSatuan = $soDtl ? (float) $soDtl->harga_satuan : 0;
            $diskonPersen = $soDtl ? (float) $soDtl->diskon_persen : 0;

            $bruto = $qty * $hargaSatuan;
            $diskonNominal = ($diskonPersen > 0) ? ($bruto * ($diskonPersen / 100)) : ($soDtl ? (float) $soDtl->potongan_nominal : 0);
            $netto = max(0, $bruto - $diskonNominal);
            $ppnNominal = $isPpn ? ($netto * 0.11) : 0;
            $itemTotal = $netto + $ppnNominal;

            $subtotalBruto += $bruto;
            $totalDiskon += $diskonNominal;
            $totalDpp += $netto;
            $totalPpn += $ppnNominal;
            $totalTagihan += $itemTotal;

            $items[] = [
                'pengeluaran_dtl_id' => $dtl->pengeluaran_dtl_id,
                'sodtl_id'           => $dtl->sodtl_id,
                'barang_id'          => $dtl->barang_id,
                'barang_cd'          => $dtl->barang?->barang_cd ?? '-',
                'barang_nm'          => $dtl->barang?->barang_nm ?? "Barang #{$dtl->barang_id}",
                'batch_no'           => $dtl->batch_no,
                'qty'                => $qty,
                'satuan'             => $dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton',
                'harga_satuan'       => $hargaSatuan,
                'diskon_nominal'     => $diskonNominal,
                'subtotal_netto'     => $netto,
                'ppn_nominal'        => $ppnNominal,
                'total_nominal'      => $itemTotal,
            ];
        }

        return [
            'pengeluaran'         => $pengeluaran,
            'order'               => $order,
            'customer'            => $customer,
            'generated_faktur_no' => $generatedFakturNo,
            'faktur_tgl'          => $fakturTgl,
            'tgl_jatuh_tempo'     => $jatuhTempo,
            'top_txt'             => $topTxt,
            'is_ppn'              => $isPpn,
            'ppn_tipe'            => $isPpn ? 'PPN_11' : 'NON_PPN',
            'ppn_persen'          => $isPpn ? 11.00 : 0.00,
            'subtotal_bruto'      => $subtotalBruto,
            'total_diskon'        => $totalDiskon,
            'dpp_nominal'         => $totalDpp,
            'ppn_nominal'         => $totalPpn,
            'total_tagihan'       => $totalTagihan,
            'items'               => $items,
        ];
    }

    /**
     * Hitung tanggal jatuh tempo berdasarkan string TOP.
     */
    public function calculateDueDate(string $date, ?string $topTxt): string
    {
        $baseDate = Carbon::parse($date);
        if (empty($topTxt)) {
            return $baseDate->copy()->addDays(7)->toDateString();
        }

        $upper = strtoupper(trim($topTxt));
        if (str_contains($upper, '14')) {
            return $baseDate->copy()->addDays(14)->toDateString();
        } elseif (str_contains($upper, '30')) {
            return $baseDate->copy()->addDays(30)->toDateString();
        } elseif (str_contains($upper, '60')) {
            return $baseDate->copy()->addDays(60)->toDateString();
        } elseif (str_contains($upper, 'CASH') || str_contains($upper, 'TUNAI')) {
            return $baseDate->toDateString();
        } elseif (str_contains($upper, 'NOTA')) {
            return $baseDate->copy()->addDays(14)->toDateString();
        }

        // Default fallback 7 hari (standar IFM)
        return $baseDate->copy()->addDays(7)->toDateString();
    }

    /**
     * Membuat dokumen Faktur Penjualan resmi & mencatat pengakuan piutang.
     */
    public function createFaktur(array $data, ?string $userName = null): DatFakturHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $pengeluaran = DatPengeluaranHdr::with(['order', 'customer'])->lockForUpdate()->findOrFail($data['pengeluaran_id']);
            $order = $pengeluaran->order;
            $customer = $pengeluaran->customer;

            $fakturTgl = $data['faktur_tgl'] ?? now()->toDateString();
            $tglJatuhTempo = $data['tgl_jatuh_tempo'] ?? $this->calculateDueDate($fakturTgl, $data['top_txt'] ?? null);

            $fakturNo = !empty($data['faktur_no']) ? trim($data['faktur_no']) : $order?->faktur_no;
            if (empty($fakturNo)) {
                $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
                $wilayah = $customer?->wilayah ?: ($customer?->alamat_txt ?: $customer?->customer_nm);
                $isPpn = ($data['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11';
                $fakturNo = $this->codeGenerator->generateFakturByKlasifikasi($klasifikasi, $wilayah, $isPpn, $fakturTgl);
            }

            // Hitung akumulasi dari item yang dikirimkan
            $subtotalBruto = 0;
            $totalDiskon = 0;
            $totalDpp = 0;
            $totalPpn = 0;
            $totalTagihan = 0;
            $itemsData = $data['items'] ?? [];

            foreach ($itemsData as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                $harga = (float) ($item['harga_satuan'] ?? 0);
                $diskon = (float) ($item['diskon_nominal'] ?? 0);
                $bruto = $qty * $harga;
                $netto = max(0, $bruto - $diskon);
                $ppn = (float) ($item['ppn_nominal'] ?? 0);
                $itemTotal = $netto + $ppn;

                $subtotalBruto += $bruto;
                $totalDiskon += $diskon;
                $totalDpp += $netto;
                $totalPpn += $ppn;
                $totalTagihan += $itemTotal;
            }

            $potongan = (float) ($data['total_potongan'] ?? 0);
            $totalTagihan = max(0, $totalTagihan - $potongan);

            // Buat Header Faktur Penjualan
            $header = DatFakturHdr::create([
                'faktur_no'         => $fakturNo,
                'faktur_tgl'        => $fakturTgl,
                'tgl_jatuh_tempo'   => $tglJatuhTempo,
                'pengeluaran_id'    => $pengeluaran->pengeluaran_id,
                'so_id'             => $order?->so_id ?? $pengeluaran->so_id,
                'customer_id'       => $customer->customer_id,
                'customer_po_no'    => $data['customer_po_no'] ?? $order?->customer_po_no,
                'top_txt'           => $data['top_txt'] ?? $order?->top_txt,
                'ppn_tipe'          => $data['ppn_tipe'] ?? 'NON_PPN',
                'ppn_persen'        => ($data['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 11.00 : 0.00,
                'subtotal_bruto'    => $subtotalBruto,
                'total_diskon'      => $totalDiskon,
                'total_potongan'    => $potongan,
                'dpp_nominal'       => $totalDpp,
                'ppn_nominal'       => $totalPpn,
                'total_tagihan'     => $totalTagihan,
                'total_dibayar'     => 0,
                'sisa_piutang'      => $totalTagihan,
                'status_pembayaran' => 'BELUM_LUNAS',
                'catatan_txt'       => $data['catatan_txt'] ?? null,
                'created_by'        => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'active_st'         => true,
                'deleted_st'        => false,
                'version_no'        => 1,
            ]);

            // Buat baris detail faktur
            foreach ($itemsData as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                $harga = (float) ($item['harga_satuan'] ?? 0);
                $diskon = (float) ($item['diskon_nominal'] ?? 0);
                $bruto = $qty * $harga;
                $netto = max(0, $bruto - $diskon);
                $ppn = (float) ($item['ppn_nominal'] ?? 0);
                $itemTotal = $netto + $ppn;

                DatFakturDtl::create([
                    'faktur_id'          => $header->faktur_id,
                    'pengeluaran_dtl_id' => $item['pengeluaran_dtl_id'] ?? null,
                    'sodtl_id'           => $item['sodtl_id'] ?? null,
                    'barang_id'          => $item['barang_id'],
                    'barang_nm'          => $item['barang_nm'] ?? "Barang #{$item['barang_id']}",
                    'qty'                => $qty,
                    'satuan'             => $item['satuan'] ?? 'Karton',
                    'harga_satuan'       => $harga,
                    'diskon_nominal'     => $diskon,
                    'subtotal_netto'     => $netto,
                    'ppn_nominal'        => $ppn,
                    'total_nominal'      => $itemTotal,
                    'catatan_txt'        => $item['catatan_txt'] ?? null,
                ]);
            }

            // Perbarui status Surat Jalan menjadi SAMPAI_TUJUAN
            $pengeluaran->update([
                'status_cd'  => 'SAMPAI_TUJUAN',
                'updated_by' => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
            ]);

            // Sinkronkan nomor faktur resmi pada PO Penjualan
            if ($order) {
                $order->update([
                    'faktur_no'  => $fakturNo,
                    'updated_by' => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                ]);
            }

            Log::info("Faktur Penjualan {$fakturNo} (ID: {$header->faktur_id}) berhasil diterbitkan untuk Surat Jalan {$pengeluaran->pengeluaran_no} oleh " . ($userName ?? 'System'));

            return $header;
        });
    }

    /**
     * Membatalkan Faktur Penjualan (Void Invoice).
     */
    public function voidFaktur(int $fakturId, string $reason, ?string $userName = null): DatFakturHdr
    {
        return DB::transaction(function () use ($fakturId, $reason, $userName) {
            $header = DatFakturHdr::with(['pengeluaran', 'order'])->lockForUpdate()->findOrFail($fakturId);

            if ($header->status_pembayaran === 'BATAL') {
                throw new Exception("Faktur {$header->faktur_no} sudah dalam status Dibatalkan (BATAL).");
            }

            if ($header->total_dibayar > 0) {
                throw new Exception("Faktur {$header->faktur_no} tidak dapat dibatalkan karena sudah ada pembayaran masuk (Rp " . number_format($header->total_dibayar, 0, ',', '.') . ").");
            }

            $note = $header->catatan_txt
                ? $header->catatan_txt . "\n[DIBATALKAN]: " . $reason
                : "[DIBATALKAN]: " . $reason;

            $header->update([
                'status_pembayaran' => 'BATAL',
                'sisa_piutang'      => 0,
                'catatan_txt'       => $note,
                'updated_by'        => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                'version_no'        => $header->version_no + 1,
            ]);

            // Kembalikan status Surat Jalan ke TERKIRIM
            if ($header->pengeluaran) {
                $header->pengeluaran->update([
                    'status_cd'  => 'TERKIRIM',
                    'updated_by' => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                ]);
            }

            return $header;
        });
    }

    /**
     * Dapatkan detail Faktur Penjualan berdasarkan ID.
     */
    public function getById(int $fakturId): DatFakturHdr
    {
        return DatFakturHdr::with([
            'customer',
            'order.details.barang.satuanDasar',
            'pengeluaran.details.barang.satuanDasar',
            'details.barang.satuanDasar'
        ])->findOrFail($fakturId);
    }
}
