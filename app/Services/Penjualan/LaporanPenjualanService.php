<?php

namespace App\Services\Penjualan;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstCustomer;
use App\Models\Penjualan\DatFakturHdr;
use App\Models\Penjualan\DatSoDtl;
use App\Models\Penjualan\DatSoHdr;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanService
{
    /**
     * Resolusi rentang tanggal dari request atau preset standar
     */
    public function resolveDateRange(?string $preset, ?string $startDate, ?string $endDate): array
    {
        $now = now();
        $preset = $preset ?: 'this_month';

        switch ($preset) {
            case 'today':
                $start = $now->toDateString();
                $end = $now->toDateString();
                $label = 'Hari Ini (' . $now->translatedFormat('d F Y') . ')';
                break;

            case 'yesterday':
                $yesterday = $now->copy()->subDay();
                $start = $yesterday->toDateString();
                $end = $yesterday->toDateString();
                $label = 'Kemarin (' . $yesterday->translatedFormat('d F Y') . ')';
                break;

            case 'this_week':
                $start = $now->copy()->startOfWeek()->toDateString();
                $end = $now->copy()->endOfWeek()->toDateString();
                $label = 'Minggu Ini (' . Carbon::parse($start)->translatedFormat('d M') . ' - ' . Carbon::parse($end)->translatedFormat('d M Y') . ')';
                break;

            case 'last_month':
                $last = $now->copy()->subMonth();
                $start = $last->startOfMonth()->toDateString();
                $end = $last->endOfMonth()->toDateString();
                $label = 'Bulan Lalu (' . $last->translatedFormat('F Y') . ')';
                break;

            case 'this_quarter':
                $start = $now->copy()->firstOfQuarter()->toDateString();
                $end = $now->copy()->lastOfQuarter()->toDateString();
                $label = 'Kuartal Ini (Q' . $now->quarter . ' ' . $now->year . ')';
                break;

            case 'this_year':
                $start = $now->copy()->startOfYear()->toDateString();
                $end = $now->copy()->endOfYear()->toDateString();
                $label = 'Tahun Ini (' . $now->year . ')';
                break;

            case 'all':
                $start = '2020-01-01';
                $end = '2035-12-31';
                $label = 'Semua Periode Transaksi';
                break;

            case 'custom':
                $start = $startDate ?: $now->copy()->startOfMonth()->toDateString();
                $end = $endDate ?: $now->copy()->endOfMonth()->toDateString();
                $label = Carbon::parse($start)->translatedFormat('d M Y') . ' - ' . Carbon::parse($end)->translatedFormat('d M Y');
                break;

            case 'this_month':
            default:
                $preset = 'this_month';
                $start = $now->copy()->startOfMonth()->toDateString();
                $end = $now->copy()->endOfMonth()->toDateString();
                $label = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                break;
        }

        return [
            'preset'     => $preset,
            'start_date' => $start,
            'end_date'   => $end,
            'label'      => $label,
        ];
    }

    /**
     * Ringkasan Metrik Eksekutif Penjualan (Executive Summary)
     */
    public function getExecutiveMetrics(string $startDate, string $endDate, ?string $channel = null): array
    {
        $base = DatSoHdr::where('deleted_st', false)
            ->where('status_cd', '!=', 'CANCELLED')
            ->whereDate('so_tgl', '>=', $startDate)
            ->whereDate('so_tgl', '<=', $endDate);

        if ($channel === 'PO') {
            $base->where(function ($q) {
                $q->whereNull('customer_po_no')
                  ->orWhere('customer_po_no', 'not like', 'PENJUALAN LANGSUNG%');
            })->where('so_no', 'not like', 'DIR-%');
        } elseif ($channel === 'DIRECT') {
            $base->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN LANGSUNG%')
                  ->orWhere('so_no', 'like', 'DIR-%');
            });
        }

        $allOrders = (clone $base)->get();

        $totalOmzet = (float) $allOrders->sum('total_tagihan');
        $totalTransaksi = $allOrders->count();

        // Pisahkan PO vs Direct Sales
        $poOrders = $allOrders->filter(function ($so) {
            return !str_starts_with((string)$so->customer_po_no, 'PENJUALAN LANGSUNG') && !str_starts_with((string)$so->so_no, 'DIR-');
        });
        $directOrders = $allOrders->filter(function ($so) {
            return str_starts_with((string)$so->customer_po_no, 'PENJUALAN LANGSUNG') || str_starts_with((string)$so->so_no, 'DIR-');
        });

        $poOmzet = (float) $poOrders->sum('total_tagihan');
        $poCount = $poOrders->count();

        $directOmzet = (float) $directOrders->sum('total_tagihan');
        $directCount = $directOrders->count();

        $poPct = $totalOmzet > 0 ? round(($poOmzet / $totalOmzet) * 100, 1) : 0;
        $directPct = $totalOmzet > 0 ? round(($directOmzet / $totalOmzet) * 100, 1) : 0;

        // Total kuantitas barang terjual dari detail
        $soIds = $allOrders->pluck('so_id')->toArray();
        $totalQty = 0;
        if (!empty($soIds)) {
            $totalQty = (float) DatSoDtl::whereIn('so_id', $soIds)->sum('pesan_qty');
        }

        // Sisa Piutang Berjalan dari Faktur terkait
        $totalPiutang = 0;
        if (!empty($soIds)) {
            $totalPiutang = (float) DatFakturHdr::whereIn('so_id', $soIds)
                ->where('deleted_st', false)
                ->where('status_pembayaran', '!=', 'BATAL')
                ->sum('sisa_piutang');
        }

        return [
            'total_omzet'     => $totalOmzet,
            'total_transaksi' => $totalTransaksi,
            'total_qty'       => $totalQty,
            'total_piutang'   => $totalPiutang,
            'po_omzet'        => $poOmzet,
            'po_count'        => $poCount,
            'po_pct'          => $poPct,
            'direct_omzet'    => $directOmzet,
            'direct_count'    => $directCount,
            'direct_pct'      => $directPct,
        ];
    }

    /**
     * Rekapitulasi Penjualan Menurut Produk (Sales by Product)
     */
    public function getSalesByProduct(string $startDate, string $endDate, ?string $channel = null): Collection
    {
        $query = DB::table('dat_so_dtl as dtl')
            ->join('dat_so_hdr as hdr', 'hdr.so_id', '=', 'dtl.so_id')
            ->join('mst_barang as brg', 'brg.barang_id', '=', 'dtl.barang_id')
            ->leftJoin('mst_satuan as stn', 'stn.satuan_id', '=', 'brg.satuan_dasar_id')
            ->leftJoin('mst_jenis_barang as jns', 'jns.jenis_barang_id', '=', 'brg.jenis_barang_id')
            ->where('hdr.deleted_st', false)
            ->where('hdr.status_cd', '!=', 'CANCELLED')
            ->whereDate('hdr.so_tgl', '>=', $startDate)
            ->whereDate('hdr.so_tgl', '<=', $endDate);

        if ($channel === 'PO') {
            $query->where(function ($q) {
                $q->whereNull('hdr.customer_po_no')
                  ->orWhere('hdr.customer_po_no', 'not like', 'PENJUALAN LANGSUNG%');
            })->where('hdr.so_no', 'not like', 'DIR-%');
        } elseif ($channel === 'DIRECT') {
            $query->where(function ($q) {
                $q->where('hdr.customer_po_no', 'like', 'PENJUALAN LANGSUNG%')
                  ->orWhere('hdr.so_no', 'like', 'DIR-%');
            });
        }

        $results = $query->select(
            'brg.barang_id',
            'brg.barang_cd',
            'brg.barang_nm',
            DB::raw("COALESCE(stn.satuan_nm, stn.satuan_cd, 'Unit') as satuan_nm"),
            DB::raw("COALESCE(jns.jenis_barang_nm, 'Barang Jadi') as jenis_barang_nm"),
            DB::raw("SUM(dtl.pesan_qty) as total_qty"),
            DB::raw("AVG(dtl.harga_satuan) as avg_harga"),
            DB::raw("SUM(dtl.subtotal_tagihan) as total_omzet"),
            DB::raw("COUNT(DISTINCT hdr.so_id) as frekuensi_pesanan")
        )
        ->groupBy('brg.barang_id', 'brg.barang_cd', 'brg.barang_nm', 'stn.satuan_nm', 'stn.satuan_cd', 'jns.jenis_barang_nm')
        ->orderByDesc('total_omzet')
        ->get();

        $totalAllOmzet = (float) $results->sum('total_omzet');

        return $results->map(function ($row) use ($totalAllOmzet) {
            $omzet = (float) $row->total_omzet;
            $row->kontribusi_pct = $totalAllOmzet > 0 ? round(($omzet / $totalAllOmzet) * 100, 1) : 0;
            return $row;
        });
    }

    /**
     * Rekapitulasi Penjualan Menurut Pelanggan (Sales by Customer)
     */
    public function getSalesByCustomer(string $startDate, string $endDate, ?string $channel = null): Collection
    {
        $query = DB::table('dat_so_hdr as hdr')
            ->leftJoin('mst_customer as cust', 'cust.customer_id', '=', 'hdr.customer_id')
            ->leftJoin('dat_faktur_hdr as fkt', function ($join) {
                $join->on('fkt.so_id', '=', 'hdr.so_id')
                     ->where('fkt.deleted_st', '=', false)
                     ->where('fkt.status_pembayaran', '!=', 'BATAL');
            })
            ->where('hdr.deleted_st', false)
            ->where('hdr.status_cd', '!=', 'CANCELLED')
            ->whereDate('hdr.so_tgl', '>=', $startDate)
            ->whereDate('hdr.so_tgl', '<=', $endDate);

        if ($channel === 'PO') {
            $query->where(function ($q) {
                $q->whereNull('hdr.customer_po_no')
                  ->orWhere('hdr.customer_po_no', 'not like', 'PENJUALAN LANGSUNG%');
            })->where('hdr.so_no', 'not like', 'DIR-%');
        } elseif ($channel === 'DIRECT') {
            $query->where(function ($q) {
                $q->where('hdr.customer_po_no', 'like', 'PENJUALAN LANGSUNG%')
                  ->orWhere('hdr.so_no', 'like', 'DIR-%');
            });
        }

        $results = $query->select(
            DB::raw("COALESCE(cust.customer_id, 0) as customer_id"),
            DB::raw("COALESCE(cust.customer_cd, 'CUST-CASH') as customer_cd"),
            DB::raw("COALESCE(cust.customer_nm, 'Pembeli Tunai / Kasir') as customer_nm"),
            DB::raw("COALESCE(cust.klasifikasi, 'UMUM') as klasifikasi"),
            DB::raw("COUNT(DISTINCT hdr.so_id) as total_transaksi"),
            DB::raw("SUM(hdr.total_tagihan) as total_omzet"),
            DB::raw("COALESCE(SUM(fkt.sisa_piutang), 0) as total_sisa_piutang")
        )
        ->groupBy('cust.customer_id', 'cust.customer_cd', 'cust.customer_nm', 'cust.klasifikasi')
        ->orderByDesc('total_omzet')
        ->get();

        $totalAllOmzet = (float) $results->sum('total_omzet');

        return $results->map(function ($row) use ($totalAllOmzet) {
            $omzet = (float) $row->total_omzet;
            $row->kontribusi_pct = $totalAllOmzet > 0 ? round(($omzet / $totalAllOmzet) * 100, 1) : 0;
            return $row;
        });
    }

    /**
     * Daftar Buku Transaksi Penjualan Lengkap (Sales Transaction Ledger) berpaginasi
     */
    public function getTransactionLedger(
        string $startDate,
        string $endDate,
        ?string $channel = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = DatSoHdr::with([
            'customer',
            'details.barang.satuanDasar',
            'pengeluaran',
            'faktur'
        ])
        ->where('deleted_st', false)
        ->where('status_cd', '!=', 'CANCELLED')
        ->whereDate('so_tgl', '>=', $startDate)
        ->whereDate('so_tgl', '<=', $endDate);

        if ($channel === 'PO') {
            $query->where(function ($q) {
                $q->whereNull('customer_po_no')
                  ->orWhere('customer_po_no', 'not like', 'PENJUALAN LANGSUNG%');
            })->where('so_no', 'not like', 'DIR-%');
        } elseif ($channel === 'DIRECT') {
            $query->where(function ($q) {
                $q->where('customer_po_no', 'like', 'PENJUALAN LANGSUNG%')
                  ->orWhere('so_no', 'like', 'DIR-%');
            });
        }

        if (!empty($search)) {
            $searchTrim = trim($search);
            $query->where(function ($q) use ($searchTrim) {
                $q->where('so_no', 'ilike', "%{$searchTrim}%")
                  ->orWhere('faktur_no', 'ilike', "%{$searchTrim}%")
                  ->orWhere('surat_jalan_no', 'ilike', "%{$searchTrim}%")
                  ->orWhere('customer_po_no', 'ilike', "%{$searchTrim}%")
                  ->orWhereHas('customer', function ($c) use ($searchTrim) {
                      $c->where('customer_nm', 'ilike', "%{$searchTrim}%")
                        ->orWhere('customer_cd', 'ilike', "%{$searchTrim}%");
                  });
            });
        }

        return $query->orderBy('so_tgl', 'desc')
                     ->orderBy('so_id', 'desc')
                     ->paginate($perPage);
    }
}
