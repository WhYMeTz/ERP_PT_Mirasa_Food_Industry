<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatQcInboundDtl;
use App\Models\Gudang\DatQcInboundHdr;
use App\Models\User;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QcInboundService
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar tiket inspeksi QC dengan paginasi dan filter
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatQcInboundHdr::with(['supplier', 'gudang', 'po', 'details.barang'])
            ->where('deleted_st', false)
            ->orderBy('tgl_periksa', 'desc')
            ->orderBy('qc_id', 'desc');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('qc_no', 'ilike', "%{$search}%")
                  ->orWhere('surat_jalan_supplier', 'ilike', "%{$search}%")
                  ->orWhere('plat_nomor_truk', 'ilike', "%{$search}%")
                  ->orWhere('sopir_nama', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('supplier_nm', 'ilike', "%{$search}%"));
            });
        }

        if (!empty($filters['status_qc'])) {
            $query->where('status_qc', $filters['status_qc']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['tgl_mulai']) && !empty($filters['tgl_selesai'])) {
            $query->whereBetween('tgl_periksa', [
                $filters['tgl_mulai'] . ' 00:00:00',
                $filters['tgl_selesai'] . ' 23:59:59'
            ]);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Mengambil seluruh tiket QC yang sudah selesai dan siap ditarik oleh Admin Gudang
     */
    public function getSiapGudangTickets(): array
    {
        return DatQcInboundHdr::with(['supplier', 'gudang', 'po', 'details.barang.satuanDasar'])
            ->where('deleted_st', false)
            ->where('status_qc', 'SIAP_GUDANG')
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($h) {
                $totalGross = $h->details->sum('qty_timbang_gross');
                $totalNetto = $h->details->sum('qty_netto_lolos');
                $totalReject = $h->details->sum('qty_reject');
                $itemNames = $h->details->map(fn($d) => $d->barang?->barang_nm)->filter()->unique()->implode(', ');

                return [
                    'qc_id'                 => $h->qc_id,
                    'qc_no'                 => $h->qc_no,
                    'po_id'                 => $h->po_id,
                    'po_no'                 => $h->po?->po_no ?? 'Non-PO',
                    'supplier_id'           => $h->supplier_id,
                    'supplier_nm'           => $h->supplier?->supplier_nm ?? '-',
                    'gudang_id'             => $h->gudang_id,
                    'gudang_nm'             => $h->gudang?->gudang_nm ?? '-',
                    'surat_jalan_supplier'  => $h->surat_jalan_supplier,
                    'tgl_periksa'           => $h->tgl_periksa->format('d/m/Y H:i'),
                    'total_gross'           => (float) $totalGross,
                    'total_netto'           => (float) $totalNetto,
                    'total_reject'          => (float) $totalReject,
                    'item_count'            => $h->details->count(),
                    'item_summary'          => $itemNames,
                ];
            })
            ->toArray();
    }

    /**
     * Mengambil detail lengkap tiket QC untuk auto-fill di form Penerimaan Barang
     */
    public function getTicketData(int $qcId): array
    {
        $qc = DatQcInboundHdr::with(['supplier', 'gudang', 'po.details', 'details.barang.satuanDasar'])
            ->where('deleted_st', false)
            ->findOrFail($qcId);

        $items = $qc->details->map(function ($d) {
            $barang = $d->barang;
            $acronym = app(CodeGeneratorService::class)->extractBarangAcronym(
                $barang?->barang_nm,
                $barang?->barang_cd
            );
            $batchPrefix = ($acronym ?: 'BRG') . '-';

            return [
                'qcdtl_id'         => $d->qcdtl_id,
                'podtl_id'         => $d->podtl_id,
                'barang_id'        => $d->barang_id,
                'barang_cd'        => $barang?->barang_cd,
                'barang_nm'        => $barang?->barang_nm,
                'satuan_nm'        => $barang?->satuanDasar?->satuan_nm ?? 'KG',
                'batch_prefix'     => $batchPrefix,
                'gross_qty'        => (float) $d->qty_timbang_gross,
                'kadar_air'        => (float) $d->kadar_air_persen,
                'refraksi_persen'  => (float) $d->refraksi_persen,
                'refraksi_qty'     => (float) $d->qty_refraksi,
                'reject_qty'       => (float) $d->qty_reject,
                'netto_qty'        => (float) $d->qty_netto_lolos,
                'grade_cd'         => $d->grade_cd,
                'kondisi_fisik'              => $d->kondisi_fisik,
                'keputusan_qc'               => $d->keputusan_qc,
                'status_raw_material'        => $d->status_raw_material ?? 'OK',
                'diameter_kurang_4cm_persen' => (float) $d->diameter_kurang_4cm_persen,
                'diameter_lebih_4cm_persen'  => (float) $d->diameter_lebih_4cm_persen,
                'fryer_rasa'                 => $d->fryer_rasa ?? 'TIDAK_PAHIT',
                'fryer_tekstur'              => $d->fryer_tekstur ?? 'RENYAH',
                'fryer_penampakan'           => $d->fryer_penampakan ?? 'TIDAK_OILSOAKED',
                'catatan'                    => $d->catatan_dtl,
                'std_harga'                  => (float) ($barang?->harga_beli_standar ?? 0),
            ];
        })->toArray();

        return [
            'qc_id'                  => $qc->qc_id,
            'qc_no'                  => $qc->qc_no,
            'status_qc'              => $qc->status_qc,
            'po_id'                  => $qc->po_id,
            'po_no'                  => $qc->po?->po_no,
            'supplier_id'            => $qc->supplier_id,
            'supplier_nm'            => $qc->supplier?->supplier_nm,
            'gudang_id'              => $qc->gudang_id,
            'gudang_nm'              => $qc->gudang?->gudang_nm,
            'negara_produsen'        => $qc->negara_produsen,
            'lokasi_panen'           => $qc->lokasi_panen,
            'umur_singkong_bln'      => (float) $qc->umur_singkong_bln,
            'tgl_panen'              => $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : null,
            'jumlah_sample_kg'       => (float) $qc->jumlah_sample_kg,
            'surat_jalan_supplier'   => $qc->surat_jalan_supplier,
            'plat_nomor_truk'        => $qc->plat_nomor_truk,
            'sopir_nama'             => $qc->sopir_nama,
            'bebas_cemaran_st'       => (bool) $qc->bebas_cemaran_st,
            'angkut_barang_haram_st' => (bool) $qc->angkut_barang_haram_st,
            'komentar_transportasi'  => $qc->komentar_transportasi,
            'tgl_periksa'            => $qc->tgl_periksa->format('Y-m-d H:i'),
            'petugas_qc_nama'        => $qc->petugas_qc_nama,
            'qc_supervisor_nama'     => $qc->qc_supervisor_nama,
            'catatan_umum'           => $qc->catatan_umum,
            'items'                  => $items,
        ];
    }

    /**
     * Menyimpan hasil uji inspeksi QC masuk (dari form mobile / desktop QC)
     */
    public function store(array $data, User $user): DatQcInboundHdr
    {
        return DB::transaction(function () use ($data, $user) {
            $tglPeriksa = !empty($data['tgl_periksa']) ? $data['tgl_periksa'] : now();
            $qcNo = $this->codeGenerator->generateQcNo(date('Y-m-d', strtotime($tglPeriksa)));

            $petugasQc = !empty($data['petugas_qc_nama']) 
                ? trim($data['petugas_qc_nama']) 
                : ($user->name ?? 'Petugas QC');

            $header = DatQcInboundHdr::create([
                'qc_no'                  => $qcNo,
                'po_id'                  => !empty($data['po_id']) ? (int)$data['po_id'] : null,
                'supplier_id'            => (int) $data['supplier_id'],
                'gudang_id'              => (int) $data['gudang_id'],
                'negara_produsen'        => !empty($data['negara_produsen']) ? trim($data['negara_produsen']) : 'Indonesia',
                'lokasi_panen'           => !empty($data['lokasi_panen']) ? trim($data['lokasi_panen']) : null,
                'umur_singkong_bln'      => !empty($data['umur_singkong_bln']) ? (float)$data['umur_singkong_bln'] : null,
                'tgl_panen'              => !empty($data['tgl_panen']) ? $data['tgl_panen'] : null,
                'jumlah_sample_kg'       => !empty($data['jumlah_sample_kg']) ? (float)$data['jumlah_sample_kg'] : 0,
                'surat_jalan_supplier'   => !empty($data['surat_jalan_supplier']) ? trim($data['surat_jalan_supplier']) : null,
                'plat_nomor_truk'        => !empty($data['plat_nomor_truk']) ? strtoupper(trim($data['plat_nomor_truk'])) : null,
                'sopir_nama'             => !empty($data['sopir_nama']) ? trim($data['sopir_nama']) : null,
                'bebas_cemaran_st'       => isset($data['bebas_cemaran_st']) ? (bool)$data['bebas_cemaran_st'] : true,
                'angkut_barang_haram_st' => isset($data['angkut_barang_haram_st']) ? (bool)$data['angkut_barang_haram_st'] : false,
                'komentar_transportasi'  => !empty($data['komentar_transportasi']) ? trim($data['komentar_transportasi']) : null,
                'tgl_periksa'            => $tglPeriksa,
                'petugas_qc_nama'        => $petugasQc,
                'qc_supervisor_nama'     => !empty($data['qc_supervisor_nama']) ? trim($data['qc_supervisor_nama']) : null,
                'status_qc'              => 'SIAP_GUDANG',
                'catatan_umum'           => !empty($data['catatan_umum']) ? trim($data['catatan_umum']) : null,
            ]);

            $allRejected = true;
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item komoditas yang diinspeksi oleh QC.");
            }

            foreach ($items as $row) {
                $gross = (float) ($row['qty_timbang_gross'] ?? 0);
                $kadarAir = (float) ($row['kadar_air_persen'] ?? 0);
                $refraksiPersen = (float) ($row['refraksi_persen'] ?? 0);
                $rejectQty = (float) ($row['qty_reject'] ?? 0);

                // Hitung potongan kotoran
                $qtyRefraksi = round($gross * ($refraksiPersen / 100), 4);
                // Hitung netto lolos
                $nettoLolos = max(0, round($gross - $qtyRefraksi - $rejectQty, 4));

                $grade = !empty($row['grade_cd']) ? $row['grade_cd'] : 'A';

                // Tentukan keputusan QC otomatis
                if ($nettoLolos <= 0) {
                    $keputusan = 'REJECT_TOTAL';
                    $grade = 'REJECT';
                } elseif ($rejectQty > 0) {
                    $keputusan = 'REJECT_PARTIAL';
                    $allRejected = false;
                } elseif ($refraksiPersen > 0) {
                    $keputusan = 'PASSED_REFRAKSI';
                    $allRejected = false;
                } else {
                    $keputusan = 'PASSED';
                    $allRejected = false;
                }

                DatQcInboundDtl::create([
                    'qc_id'                      => $header->qc_id,
                    'podtl_id'                   => !empty($row['podtl_id']) ? (int)$row['podtl_id'] : null,
                    'barang_id'                  => (int) $row['barang_id'],
                    'status_raw_material'        => $row['status_raw_material'] ?? 'OK',
                    'diameter_kurang_4cm_persen' => (float)($row['diameter_kurang_4cm_persen'] ?? 0),
                    'diameter_lebih_4cm_persen'  => (float)($row['diameter_lebih_4cm_persen'] ?? 100),
                    'kondisi_segar'              => !empty($row['kondisi_segar']),
                    'kondisi_layu'               => !empty($row['kondisi_layu']),
                    'kondisi_basah'              => !empty($row['kondisi_basah']),
                    'kondisi_terkelupas'         => !empty($row['kondisi_terkelupas']),
                    'kondisi_busuk'              => !empty($row['kondisi_busuk']),
                    'kondisi_berjamur'           => !empty($row['kondisi_berjamur']),
                    'kondisi_lembek'             => !empty($row['kondisi_lembek']),
                    'fryer_rasa'                 => $row['fryer_rasa'] ?? 'TIDAK_PAHIT',
                    'fryer_tekstur'              => $row['fryer_tekstur'] ?? 'RENYAH',
                    'fryer_penampakan'           => $row['fryer_penampakan'] ?? 'TIDAK_OILSOAKED',
                    'defect_breakage_persen'     => (float)($row['defect_breakage_persen'] ?? 0),
                    'defect_cluster_persen'      => (float)($row['defect_cluster_persen'] ?? 0),
                    'defect_foldover_persen'     => (float)($row['defect_foldover_persen'] ?? 0),
                    'defect_oilsoaked_persen'    => (float)($row['defect_oilsoaked_persen'] ?? 0),
                    'defect_gambos_persen'       => (float)($row['defect_gambos_persen'] ?? 0),
                    'qty_timbang_gross'          => $gross,
                    'kadar_air_persen'           => $kadarAir,
                    'refraksi_persen'            => $refraksiPersen,
                    'qty_refraksi'               => $qtyRefraksi,
                    'qty_reject'                 => $rejectQty,
                    'qty_netto_lolos'            => $nettoLolos,
                    'grade_cd'                   => $grade,
                    'kondisi_fisik'              => $row['kondisi_fisik'] ?? 'NORMAL',
                    'keputusan_qc'               => $keputusan,
                    'catatan_dtl'                => $row['catatan_dtl'] ?? null,
                ]);
            }

            // Jika semua item ditolak total
            if ($allRejected) {
                $header->status_qc = 'DITOLAK_TOTAL';
                $header->save();
            }

            return $header;
        });
    }

    /**
     * Menandai tiket QC telah diproses dan diterima oleh Admin Gudang
     */
    public function markAsProcessed(int $qcId): void
    {
        $qc = DatQcInboundHdr::find($qcId);
        if ($qc) {
            $qc->status_qc = 'DITERIMA_GUDANG';
            $qc->save();
        }
    }
}
