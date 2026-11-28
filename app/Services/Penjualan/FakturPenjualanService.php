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
            $statusUpper = strtoupper(trim($status));
            if ($statusUpper === 'BELUM_LUNAS') {
                $query->where(function ($q) {
                    $q->whereIn('status_pembayaran', ['BELUM_LUNAS', 'SEBAGIAN'])
                      ->orWhereNull('status_pembayaran');
                });
            } elseif ($statusUpper === 'TAK_TERTAGIH' || $statusUpper === 'MACET') {
                $query->where('status_pembayaran', 'TAK_TERTAGIH');
            } else {
                $query->where('status_pembayaran', $statusUpper);
            }
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

        $countTakTertagih = (clone $base)
            ->where('status_pembayaran', 'TAK_TERTAGIH')
            ->count();

        $totalPiutangMacet = (float) (clone $base)
            ->where('status_pembayaran', 'TAK_TERTAGIH')
            ->sum('sisa_piutang');

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
            'count_tak_tertagih'        => $countTakTertagih,
            'total_piutang_macet'       => $totalPiutangMacet,
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

        // Generate No Faktur resmi standar perusahaan: bersih jika 1 kali kirim, sub-indeks (-1, -2) jika parsial
        $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
        $wilayah = $customer?->wilayah ?: ($customer?->alamat_txt ?: $customer?->customer_nm);
        $baseFakturNo = !empty($order?->faktur_no)
            ? $order->faktur_no
            : $this->codeGenerator->generateFakturByKlasifikasi($klasifikasi, $wilayah, $isPpn, $fakturTgl);

        $cleanBaseFakturNo = preg_replace('/-\d+$/', '', $baseFakturNo);

        $soId = $order?->so_id ?? $pengeluaran->so_id;
        $existingCount = DatFakturHdr::where('so_id', $soId)
            ->where('deleted_st', false)
            ->where('status_pembayaran', '!=', 'BATAL')
            ->count();

        $generatedFakturNo = ($existingCount > 0)
            ? "{$cleanBaseFakturNo}-" . ($existingCount + 1)
            : $cleanBaseFakturNo;

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
                'satuan'             => $soDtl?->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton'),
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
     * Membuat dokumen Faktur Penjualan resmi secara otomatis dari Surat Jalan (Pengeluaran Barang).
     * Mencegah double-entry dan memastikan faktur langsung terbit saat barang dikeluarkan.
     */
    public function autoCreateFromPengeluaran(DatPengeluaranHdr $pengeluaran, ?string $userName = null): DatFakturHdr
    {
        // Cek apakah faktur aktif sudah pernah dibuat untuk Surat Jalan ini
        $existing = DatFakturHdr::where('pengeluaran_id', $pengeluaran->pengeluaran_id)
            ->where('deleted_st', false)
            ->where('status_pembayaran', '!=', 'BATAL')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Pastikan relasi penting sudah dimuat
        $pengeluaran->loadMissing([
            'customer',
            'order.details.barang.satuanDasar',
            'details.barang.satuanDasar',
            'details.orderDetail'
        ]);

        $customer = $pengeluaran->customer;
        $order = $pengeluaran->order;
        $fakturTgl = $pengeluaran->pengeluaran_tgl ? Carbon::parse($pengeluaran->pengeluaran_tgl)->toDateString() : now()->toDateString();

        // Hitung perkiraan jatuh tempo berdasarkan TOP
        $topTxt = $order?->top_txt ?: ($customer?->top_txt ?? '7 Hari');
        $jatuhTempo = $this->calculateDueDate($fakturTgl, $topTxt);

        // Tentukan status PPN
        $isPpn = ($order && ($order->ppn_nominal > 0 || $order->ppn_tipe === 'PPN_11'));
        if (!$isPpn && $customer) {
            $isPpn = ($customer->default_ppn === 'PPN_11');
        }
        $ppnTipe = $isPpn ? 'PPN_11' : 'NON_PPN';
        $ppnPersen = $isPpn ? 11.00 : 0.00;

        // Generate No Faktur resmi: selalu bersih (1 PO = 1 Faktur utuh tanpa suffix -1, -2)
        $klasifikasi = $customer?->klasifikasi ?? 'MANUAL';
        $wilayah = $customer?->wilayah ?: ($customer?->alamat_txt ?: $customer?->customer_nm);
        $baseFakturNo = !empty($order?->faktur_no)
            ? $order->faktur_no
            : $this->codeGenerator->generateFakturByKlasifikasi($klasifikasi, $wilayah, $isPpn, $fakturTgl);

        $cleanBaseFakturNo = preg_replace('/-\d+$/', '', $baseFakturNo);
        $soId = $order?->so_id ?? $pengeluaran->so_id;

        // Cek apakah sudah ada faktur aktif untuk PO/SO ini (Prinsip: 1 SO = 1 Faktur Penjualan, multi-trip SJ)
        if ($soId) {
            $existingSoFaktur = DatFakturHdr::with('details')->where('so_id', $soId)
                ->where('deleted_st', false)
                ->where('status_pembayaran', '!=', 'BATAL')
                ->first();

            if ($existingSoFaktur) {
                // Pengiriman parsial ke-2 atau seterusnya:
                // Sesuai arahan operasional: Faktur Penjualan tetap 1 per PO (Surat Jalan boleh > 1).
                // Tambahkan rincian barang dari surat jalan baru ini ke faktur tunggal yang sudah ada jika belum tercatat.
                $existingDtlPengeluaranIds = $existingSoFaktur->details->pluck('pengeluaran_dtl_id')->filter()->all();

                $additionalBruto = 0;
                $additionalDiskon = 0;
                $additionalDpp = 0;
                $additionalPpn = 0;
                $additionalTotal = 0;

                foreach ($pengeluaran->details as $dtl) {
                    if (in_array($dtl->pengeluaran_dtl_id, $existingDtlPengeluaranIds)) {
                        continue;
                    }

                    $soDtl = $dtl->orderDetail;
                    $qty = (float) $dtl->kirim_qty;
                    $hargaSatuan = $soDtl ? (float) $soDtl->harga_satuan : 0;
                    $diskonPersen = $soDtl ? (float) $soDtl->diskon_persen : 0;

                    $bruto = $qty * $hargaSatuan;
                    $diskonNominal = ($diskonPersen > 0) ? ($bruto * ($diskonPersen / 100)) : ($soDtl ? (float) $soDtl->potongan_nominal : 0);
                    $netto = max(0, $bruto - $diskonNominal);
                    $ppnNominal = $isPpn ? ($netto * 0.11) : 0;
                    $itemTotal = $netto + $ppnNominal;

                    $additionalBruto += $bruto;
                    $additionalDiskon += $diskonNominal;
                    $additionalDpp += $netto;
                    $additionalPpn += $ppnNominal;
                    $additionalTotal += $itemTotal;

                    DatFakturDtl::create([
                        'faktur_id'          => $existingSoFaktur->faktur_id,
                        'pengeluaran_dtl_id' => $dtl->pengeluaran_dtl_id,
                        'sodtl_id'           => $dtl->sodtl_id,
                        'barang_id'          => $dtl->barang_id,
                        'barang_nm'          => $dtl->barang?->barang_nm ?? "Barang #{$dtl->barang_id}",
                        'qty'                => $qty,
                        'satuan'             => $soDtl?->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton'),
                        'harga_satuan'       => $hargaSatuan,
                        'diskon_nominal'     => $diskonNominal,
                        'subtotal_netto'     => $netto,
                        'ppn_nominal'        => $ppnNominal,
                        'total_nominal'      => $itemTotal,
                        'catatan_txt'        => $dtl->catatan_txt,
                    ]);
                }

                if ($additionalTotal > 0) {
                    $newSubtotalBruto = (float) $existingSoFaktur->subtotal_bruto + $additionalBruto;
                    $newTotalDiskon   = (float) $existingSoFaktur->total_diskon + $additionalDiskon;
                    $newDpp           = (float) $existingSoFaktur->dpp_nominal + $additionalDpp;
                    $newPpn           = (float) $existingSoFaktur->ppn_nominal + $additionalPpn;
                    $newTotalTagihan  = (float) $existingSoFaktur->total_tagihan + $additionalTotal;
                    $newSisaPiutang   = max(0, $newTotalTagihan - (float) $existingSoFaktur->total_dibayar);

                    $allSjNos = $order ? $order->pengeluaran()->where('status_cd', '!=', 'BATAL')->pluck('pengeluaran_no')->filter()->implode(', ') : $pengeluaran->pengeluaran_no;

                    // Bersihkan nomor faktur dari suffix -1, -2 jika sebelumnya sempat ada
                    $cleanFakturNo = preg_replace('/-\d+$/', '', $existingSoFaktur->faktur_no);

                    $existingSoFaktur->update([
                        'faktur_no'      => $cleanFakturNo,
                        'subtotal_bruto' => $newSubtotalBruto,
                        'total_diskon'   => $newTotalDiskon,
                        'dpp_nominal'    => $newDpp,
                        'ppn_nominal'    => $newPpn,
                        'total_tagihan'  => $newTotalTagihan,
                        'sisa_piutang'   => $newSisaPiutang,
                        'catatan_txt'    => "Mencakup Surat Jalan: {$allSjNos}",
                        'updated_by'     => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                    ]);
                }

                Log::info("Faktur Penjualan {$existingSoFaktur->faktur_no} disinkronkan dengan pengiriman parsial Surat Jalan {$pengeluaran->pengeluaran_no}");
                return $existingSoFaktur;
            }
        }

        // Pengiriman pertama: gunakan nomor faktur bersih tanpa akhiran (-1, -2)
        $fakturNo = $cleanBaseFakturNo;

        // Agregasi item dari baris detail Surat Jalan
        $itemsData = [];
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

            $itemsData[] = [
                'pengeluaran_dtl_id' => $dtl->pengeluaran_dtl_id,
                'sodtl_id'           => $dtl->sodtl_id,
                'barang_id'          => $dtl->barang_id,
                'barang_nm'          => $dtl->barang?->barang_nm ?? "Barang #{$dtl->barang_id}",
                'qty'                => $qty,
                'satuan'             => $soDtl?->satuan ?: ($dtl->barang?->satuanDasar?->satuan_nm ?? 'Karton'),
                'harga_satuan'       => $hargaSatuan,
                'diskon_nominal'     => $diskonNominal,
                'subtotal_netto'     => $netto,
                'ppn_nominal'        => $ppnNominal,
                'total_nominal'      => $itemTotal,
                'catatan_txt'        => $dtl->catatan_txt,
            ];
        }

        // Buat Header Faktur Penjualan
        $header = DatFakturHdr::create([
            'faktur_no'         => $fakturNo,
            'faktur_tgl'        => $fakturTgl,
            'tgl_jatuh_tempo'   => $jatuhTempo,
            'pengeluaran_id'    => $pengeluaran->pengeluaran_id,
            'so_id'             => $order?->so_id ?? $pengeluaran->so_id,
            'customer_id'       => $customer?->customer_id ?? $pengeluaran->customer_id,
            'customer_po_no'    => $order?->customer_po_no,
            'top_txt'           => $topTxt,
            'ppn_tipe'          => $ppnTipe,
            'ppn_persen'        => $ppnPersen,
            'subtotal_bruto'    => $subtotalBruto,
            'total_diskon'      => $totalDiskon,
            'total_potongan'    => 0,
            'dpp_nominal'       => $totalDpp,
            'ppn_nominal'       => $totalPpn,
            'total_tagihan'     => $totalTagihan,
            'total_dibayar'     => 0,
            'sisa_piutang'      => $totalTagihan,
            'status_pembayaran' => 'BELUM_LUNAS',
            'catatan_txt'       => "Otomatis diterbitkan dari Surat Jalan {$pengeluaran->pengeluaran_no}",
            'created_by'        => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
            'active_st'         => true,
            'deleted_st'        => false,
            'version_no'        => 1,
        ]);

        // Buat baris detail faktur
        foreach ($itemsData as $item) {
            DatFakturDtl::create([
                'faktur_id'          => $header->faktur_id,
                'pengeluaran_dtl_id' => $item['pengeluaran_dtl_id'],
                'sodtl_id'           => $item['sodtl_id'],
                'barang_id'          => $item['barang_id'],
                'barang_nm'          => $item['barang_nm'],
                'qty'                => $item['qty'],
                'satuan'             => $item['satuan'],
                'harga_satuan'       => $item['harga_satuan'],
                'diskon_nominal'     => $item['diskon_nominal'],
                'subtotal_netto'     => $item['subtotal_netto'],
                'ppn_nominal'        => $item['ppn_nominal'],
                'total_nominal'      => $item['total_nominal'],
                'catatan_txt'        => $item['catatan_txt'],
            ]);
        }

        // Sinkronkan nomor faktur pada PO Penjualan jika belum tercatat
        if ($order && empty($order->faktur_no)) {
            $order->update([
                'faktur_no'  => $fakturNo,
                'updated_by' => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
            ]);
        }

        Log::info("Faktur Penjualan {$fakturNo} (ID: {$header->faktur_id}) otomatis diterbitkan untuk Surat Jalan {$pengeluaran->pengeluaran_no}");

        return $header;
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
        if (str_contains($upper, 'CASH') || str_contains($upper, 'TUNAI') || str_contains($upper, 'COD')) {
            return $baseDate->toDateString();
        }

        if (str_contains($upper, 'NOTA')) {
            preg_match('/\d+/', $upper, $matches);
            $notaCount = isset($matches[0]) ? (int)$matches[0] : 0;
            // Tiap 1 nota diasumsikan estimasi perputaran siklus kirim 14 hari
            return $baseDate->copy()->addDays($notaCount * 14)->toDateString();
        }

        // Tangkap angka hari dinamis (misal: "0 Hari", "7 Hari", "14 Hari", "21 Hari", "45 Hari", "60 Hari")
        if (preg_match('/(\d+)\s*(HARI|DAYS|D)?/', $upper, $matches)) {
            $days = (int) $matches[1];
            return $baseDate->copy()->addDays($days)->toDateString();
        }

        if (str_contains($upper, 'NOTA')) {
            preg_match('/\d+/', $upper, $matches);
            $notaCount = isset($matches[0]) ? (int)$matches[0] : 0;
            // Tiap 1 nota diasumsikan estimasi perputaran siklus kirim 14 hari
            return $baseDate->copy()->addDays($notaCount * 14)->toDateString();
        }

        // Tangkap angka hari dinamis (misal: "0 Hari", "7 Hari", "14 Hari", "21 Hari", "45 Hari", "60 Hari")
        if (preg_match('/(\d+)\s*(HARI|DAYS|D)?/', $upper, $matches)) {
            $days = (int) $matches[1];
            return $baseDate->copy()->addDays($days)->toDateString();
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

            // Untuk PO pengiriman parsial, Faktur Penjualan tetap 1 per PO (Surat Jalan bisa multiple).
            // Periksa apakah SO sudah memiliki Faktur Penjualan aktif.
            if ($order && $order->so_id) {
                $existingSoFaktur = DatFakturHdr::with('details')->where('so_id', $order->so_id)
                    ->where('deleted_st', false)
                    ->where('status_pembayaran', '!=', 'BATAL')
                    ->first();

                if ($existingSoFaktur) {
                    $cleanBaseFakturNo = preg_replace('/-\d+$/', '', $existingSoFaktur->faktur_no);
                    $existingDtlIds = $existingSoFaktur->details->pluck('pengeluaran_dtl_id')->filter()->toArray();
                    $itemsData = $data['items'] ?? [];

                    foreach ($itemsData as $item) {
                        $dtlId = $item['pengeluaran_dtl_id'] ?? null;
                        if ($dtlId && in_array($dtlId, $existingDtlIds)) {
                            continue;
                        }
                        $qty = (float) ($item['qty'] ?? 0);
                        $harga = (float) ($item['harga_satuan'] ?? 0);
                        $diskon = (float) ($item['diskon_nominal'] ?? 0);
                        $bruto = $qty * $harga;
                        $netto = max(0, $bruto - $diskon);
                        $ppn = (float) ($item['ppn_nominal'] ?? 0);
                        $itemTotal = $netto + $ppn;

                        DatFakturDtl::create([
                            'faktur_id'          => $existingSoFaktur->faktur_id,
                            'pengeluaran_dtl_id' => $dtlId,
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

                    $existingSoFaktur->load('details');
                    $newSubtotalBruto = 0;
                    $newTotalDiskon = 0;
                    $newDpp = 0;
                    $newPpn = 0;
                    $newTotalTagihan = 0;

                    foreach ($existingSoFaktur->details as $it) {
                        $q = (float) $it->qty;
                        $h = (float) $it->harga_satuan;
                        $d = (float) $it->diskon_nominal;
                        $b = $q * $h;
                        $n = max(0, $b - $d);
                        $p = (float) $it->ppn_nominal;
                        $t = $n + $p;

                        $newSubtotalBruto += $b;
                        $newTotalDiskon += $d;
                        $newDpp += $n;
                        $newPpn += $p;
                        $newTotalTagihan += $t;
                    }

                    $newTotalTagihan = max(0, $newTotalTagihan - (float) $existingSoFaktur->total_potongan);
                    $newSisaPiutang = max(0, $newTotalTagihan - (float) $existingSoFaktur->total_dibayar);

                    $allSjNos = DatPengeluaranHdr::where('so_id', $order->so_id)
                        ->where('status_cd', '!=', 'BATAL')
                        ->pluck('pengeluaran_no')
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    $existingSoFaktur->update([
                        'faktur_no'      => $cleanBaseFakturNo,
                        'subtotal_bruto' => $newSubtotalBruto,
                        'total_diskon'   => $newTotalDiskon,
                        'dpp_nominal'    => $newDpp,
                        'ppn_nominal'    => $newPpn,
                        'total_tagihan'  => $newTotalTagihan,
                        'sisa_piutang'   => $newSisaPiutang,
                        'catatan_txt'    => "Mencakup Surat Jalan: {$allSjNos}",
                        'updated_by'     => $userName ?? auth()->user()?->nama_lengkap ?? 'System',
                    ]);

                    return $existingSoFaktur;
                }
            }

            $submittedFakturNo = !empty($data['faktur_no']) ? trim($data['faktur_no']) : null;
            $isSubmittedUsed = $submittedFakturNo && DatFakturHdr::where('faktur_no', $submittedFakturNo)
                ->where('deleted_st', false)
                ->where('status_pembayaran', '!=', 'BATAL')
                ->exists();

            if ($submittedFakturNo && !$isSubmittedUsed) {
                $fakturNo = $submittedFakturNo;
            } elseif (!empty($order?->faktur_no)) {
                $fakturNo = preg_replace('/-\d+$/', '', $order->faktur_no);
            } else {
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
     * Catat Pembayaran Piutang untuk Faktur Penjualan ini (BKM).
     */
    public function recordPayment(int $fakturId, array $data, $user): \App\Models\Keuangan\DatKasMasukHdr
    {
        return DB::transaction(function () use ($fakturId, $data, $user) {
            $faktur = DatFakturHdr::with(['customer', 'order'])->lockForUpdate()->findOrFail($fakturId);

            if ($faktur->status_pembayaran === 'BATAL') {
                throw new Exception("Tidak dapat mencatat pembayaran untuk faktur yang telah dibatalkan.");
            }

            if ($faktur->sisa_piutang <= 0) {
                throw new Exception("Faktur {$faktur->faktur_no} sudah lunas.");
            }

            $nominal = (float) $data['nominal'];
            if ($nominal <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari Rp 0.");
            }

            $sisa = (float) $faktur->sisa_piutang;
            if ($nominal > $sisa) {
                throw new Exception("Nominal pembayaran (Rp " . number_format($nominal, 0, ',', '.') . ") melebihi sisa piutang faktur (Rp " . number_format($sisa, 0, ',', '.') . ").");
            }

            $kasMasukService = app(\App\Services\Keuangan\KasMasukService::class);

            $payload = [
                'kas_bank_id'         => $data['kas_bank_id'],
                'tgl_terima'          => $data['tgl_terima'] ?? now()->toDateString(),
                'kategori_penerimaan' => 'PELUNASAN_PIUTANG',
                'customer_id'         => $faktur->customer_id,
                'penyetor_nama'       => !empty($data['penyetor_nama']) ? $data['penyetor_nama'] : ($faktur->customer?->customer_nm ?? 'Customer'),
                'metode_terima'       => $data['metode_terima'] ?? 'TRANSFER',
                'no_referensi_bank'   => $data['no_referensi_bank'] ?? null,
                'catatan'             => $data['catatan'] ?? "Pelunasan Faktur {$faktur->faktur_no} (PO: " . ($faktur->order?->so_no ?: '-') . ")",
                'items'               => [
                    [
                        'so_id'        => $faktur->so_id,
                        'uraian'       => "Pelunasan Faktur {$faktur->faktur_no} - " . ($faktur->customer?->customer_nm ?: 'Customer'),
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

            // Update Faktur sisa & status
            $totalDibayarBaru = (float) $faktur->total_dibayar + $nominal;
            $sisaBaru = max(0, (float) $faktur->total_tagihan - $totalDibayarBaru);
            $statusBayar = $sisaBaru <= 0 ? 'LUNAS' : 'SEBAGIAN';

            $faktur->update([
                'total_dibayar'     => $totalDibayarBaru,
                'sisa_piutang'      => $sisaBaru,
                'status_pembayaran' => $statusBayar,
                'updated_by'        => $userModel->name ?? 'SYSTEM',
            ]);

            Log::info("Pembayaran Faktur ID {$faktur->faktur_id} ({$faktur->faktur_no}) sebesar Rp {$nominal} berhasil dicatat via BKM {$bkm->bkm_no}");

            return $bkm;
        });
    }

    /**
     * Tandai Faktur Penjualan sebagai Piutang Tak Tertagih (Bad Debt / Macet).
     * Data faktur dan rincian transaksi tetap 100% tersimpan sebagai rekam jejak historis customer.
     */
    public function markAsMacet(int $fakturId, string $alasan, ?string $userName = null): DatFakturHdr
    {
        return DB::transaction(function () use ($fakturId, $alasan, $userName) {
            $faktur = DatFakturHdr::with(['customer'])->lockForUpdate()->findOrFail($fakturId);

            if ($faktur->status_pembayaran === 'LUNAS') {
                throw new Exception("Faktur {$faktur->faktur_no} sudah lunas, tidak dapat ditandai sebagai piutang macet.");
            }

            if ($faktur->status_pembayaran === 'BATAL') {
                throw new Exception("Faktur {$faktur->faktur_no} sudah dibatalkan (void).");
            }

            $currentNote = $faktur->catatan_txt ? trim($faktur->catatan_txt) . "\n" : '';
            $auditLog = "[PIUTANG TAK TERTAGIH]: " . trim($alasan) . " (Ditandai oleh " . ($userName ?? 'System') . " tgl " . now()->format('d/m/Y H:i') . ")";

            $faktur->update([
                'status_pembayaran' => 'TAK_TERTAGIH',
                'tgl_macet'         => now()->toDateString(),
                'alasan_macet'      => trim($alasan),
                'macet_by'          => $userName ?? 'System',
                'catatan_txt'       => $currentNote . $auditLog,
                'updated_by'        => $userName ?? 'System',
            ]);

            Log::warning("Faktur Penjualan {$faktur->faktur_no} (ID: {$faktur->faktur_id}, Customer: {$faktur->customer?->customer_nm}) ditandai sebagai PIUTANG TAK TERTAGIH senilai Rp " . number_format($faktur->sisa_piutang, 0, ',', '.') . " oleh " . ($userName ?? 'System') . ". Alasan: {$alasan}");

            return $faktur;
        });
    }

    /**
     * Pulihkan status Faktur dari Tak Tertagih kembali ke status penagihan aktif (Belum Lunas / Sebagian).
     */
    public function restoreFromMacet(int $fakturId, ?string $userName = null): DatFakturHdr
    {
        return DB::transaction(function () use ($fakturId, $userName) {
            $faktur = DatFakturHdr::lockForUpdate()->findOrFail($fakturId);

            if ($faktur->status_pembayaran !== 'TAK_TERTAGIH') {
                throw new Exception("Faktur {$faktur->faktur_no} tidak dalam status Tak Tertagih.");
            }

            $newStatus = ((float) $faktur->total_dibayar > 0) ? 'SEBAGIAN' : 'BELUM_LUNAS';
            $currentNote = $faktur->catatan_txt ? trim($faktur->catatan_txt) . "\n" : '';
            $auditLog = "[PULIHKAN DARI MACET]: Dikembalikan ke penagihan aktif ({$newStatus}) oleh " . ($userName ?? 'System') . " tgl " . now()->format('d/m/Y H:i');

            $faktur->update([
                'status_pembayaran' => $newStatus,
                'tgl_macet'         => null,
                'alasan_macet'      => null,
                'macet_by'          => null,
                'catatan_txt'       => $currentNote . $auditLog,
                'updated_by'        => $userName ?? 'System',
            ]);

            Log::info("Faktur Penjualan {$faktur->faktur_no} dipulihkan dari Tak Tertagih ke {$newStatus} oleh " . ($userName ?? 'System'));

            return $faktur;
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

    /**
     * Konsolidasi faktur split/parsial menjadi 1 faktur utuh untuk sebuah SO.
     * Menggabungkan rincian item, memperbarui total tagihan, dan menonaktifkan duplikat.
     */
    public function consolidateSplitFaktursForSo(int $soId): ?DatFakturHdr
    {
        $fakturs = DatFakturHdr::with('details')
            ->where('so_id', $soId)
            ->where('deleted_st', false)
            ->where('status_pembayaran', '!=', 'BATAL')
            ->orderBy('faktur_id')
            ->get();

        if ($fakturs->isEmpty()) {
            return null;
        }

        $primaryFaktur = $fakturs->first();
        $cleanFakturNo = preg_replace('/-\d+$/', '', $primaryFaktur->faktur_no);

        if ($fakturs->count() > 1) {
            $splitFakturs = $fakturs->slice(1);
            $existingDtlPengeluaranIds = $primaryFaktur->details->pluck('pengeluaran_dtl_id')->filter()->toArray();

            foreach ($splitFakturs as $splitFaktur) {
                foreach ($splitFaktur->details as $dtl) {
                    if (!$dtl->pengeluaran_dtl_id || !in_array($dtl->pengeluaran_dtl_id, $existingDtlPengeluaranIds)) {
                        $dtl->update(['faktur_id' => $primaryFaktur->faktur_id]);
                        if ($dtl->pengeluaran_dtl_id) {
                            $existingDtlPengeluaranIds[] = $dtl->pengeluaran_dtl_id;
                        }
                    } else {
                        // Hapus duplikat baris
                        $dtl->delete();
                    }
                }

                // Tandai faktur pecahan sebagai deleted
                $splitFaktur->update([
                    'deleted_st'  => true,
                    'active_st'   => false,
                    'catatan_txt' => trim(($splitFaktur->catatan_txt ? $splitFaktur->catatan_txt . ' | ' : '') . "Digabung ke faktur {$cleanFakturNo}"),
                ]);
            }

            // Muat ulang detail faktur utama dan hitung ulang total
            $primaryFaktur->load('details');
            $subtotalBruto = 0;
            $totalDiskon = 0;
            $totalDpp = 0;
            $totalPpn = 0;
            $totalTagihan = 0;

            foreach ($primaryFaktur->details as $item) {
                $qty = (float) $item->qty;
                $harga = (float) $item->harga_satuan;
                $diskon = (float) $item->diskon_nominal;
                $bruto = $qty * $harga;
                $netto = max(0, $bruto - $diskon);
                $ppn = (float) $item->ppn_nominal;
                $itemTotal = $netto + $ppn;

                $subtotalBruto += $bruto;
                $totalDiskon += $diskon;
                $totalDpp += $netto;
                $totalPpn += $ppn;
                $totalTagihan += $itemTotal;
            }

            $totalTagihan = max(0, $totalTagihan - (float) $primaryFaktur->total_potongan);
            $sisaPiutang = max(0, $totalTagihan - (float) $primaryFaktur->total_dibayar);

            // Kumpulkan semua nomor Surat Jalan terkait dari SO
            $allSjNos = DatPengeluaranHdr::where('so_id', $soId)
                ->where('status_cd', '!=', 'BATAL')
                ->pluck('pengeluaran_no')
                ->filter()
                ->unique()
                ->implode(', ');

            $catatanTxt = !empty($allSjNos) ? "Mencakup Surat Jalan: {$allSjNos}" : $primaryFaktur->catatan_txt;

            $primaryFaktur->update([
                'faktur_no'      => $cleanFakturNo,
                'subtotal_bruto' => $subtotalBruto,
                'total_diskon'   => $totalDiskon,
                'dpp_nominal'    => $totalDpp,
                'ppn_nominal'    => $ppnNominal,
                'total_tagihan'  => $totalTagihan,
                'sisa_piutang'   => $sisaPiutang,
                'catatan_txt'    => $catatanTxt,
            ]);
        } else {
            if ($primaryFaktur->faktur_no !== $cleanFakturNo) {
                $primaryFaktur->update(['faktur_no' => $cleanFakturNo]);
            }
        }

        // Sinkronkan nomor faktur di SO
        $so = DatSoHdr::find($soId);
        if ($so && $so->faktur_no !== $cleanFakturNo) {
            $so->update(['faktur_no' => $cleanFakturNo]);
        }

        return $primaryFaktur;
    }
}
