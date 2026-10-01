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

        if (!empty($filters['kategori_barang'])) {
            $query->where('kategori_barang', $filters['kategori_barang']);
        }

        if (!empty($filters['status_qc'])) {
            $query->where('status_qc', $filters['status_qc']);
        }

        if (!empty($filters['status_uji_goreng'])) {
            $query->where('status_uji_goreng', $filters['status_uji_goreng']);
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
                    'kategori_barang'       => $h->kategori_barang ?? 'SINGKONG',
                    'nama_jenis'            => $h->nama_jenis,
                    'po_id'                 => $h->po_id,
                    'po_no'                 => $h->po?->po_no ?? 'Non-PO',
                    'supplier_id'           => $h->supplier_id,
                    'supplier_nm'           => $h->supplier?->supplier_nm ?? '-',
                    'gudang_id'             => $h->gudang_id,
                    'gudang_nm'             => $h->gudang?->gudang_nm ?? '-',
                    'surat_jalan_supplier'  => $h->surat_jalan_supplier,
                    'plat_nomor_truk'       => $h->plat_nomor_truk,
                    'sopir_nama'            => $h->sopir_nama,
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
                'qcdtl_id'                   => $d->qcdtl_id,
                'podtl_id'                   => $d->podtl_id,
                'barang_id'                  => $d->barang_id,
                'barang_cd'                  => $barang?->barang_cd,
                'barang_nm'                  => $barang?->barang_nm,
                'satuan_nm'                  => $barang?->satuanDasar?->satuan_nm ?? 'KG',
                'batch_prefix'               => $batchPrefix,
                'gross_qty'                  => (float) $d->qty_timbang_gross,
                'kadar_air'                  => (float) $d->kadar_air_persen,
                'refraksi_persen'            => (float) $d->refraksi_persen,
                'refraksi_qty'               => (float) $d->qty_refraksi,
                'reject_qty'                 => (float) $d->qty_reject,
                'netto_qty'                  => (float) $d->qty_netto_lolos,
                'grade_cd'                   => $d->grade_cd,
                'kondisi_fisik'              => $d->kondisi_fisik,
                'keputusan_qc'               => $d->keputusan_qc,
                'status_raw_material'        => $d->status_raw_material ?? 'OK',
                'isi_kering'                 => (bool) $d->isi_kering,
                'isi_basah'                  => (bool) $d->isi_basah,
                'isi_gumpal'                 => (bool) $d->isi_gumpal,
                'isi_berminyak'              => (bool) $d->isi_berminyak,
                'kemasan_kondisi'            => $d->kemasan_kondisi ?? 'OK',
                'kemasan_kotor'              => (bool) $d->kemasan_kotor,
                'kemasan_apek'               => (bool) $d->kemasan_apek,
                'kemasan_basah'              => (bool) $d->kemasan_basah,
                'kemasan_sobek'              => (bool) $d->kemasan_sobek,
                'kemasan_jamur'              => (bool) $d->kemasan_jamur,
                'kemasan_berminyak'          => (bool) $d->kemasan_berminyak,
                'kemasan_berdebu'            => (bool) $d->kemasan_berdebu,
                'tipe_wadah_minyak'          => $d->tipe_wadah_minyak ?? 'TANGKI',
                'kondisi_tangki_jerigen'     => $d->kondisi_tangki_jerigen ?? 'OK',
                'ffa_coa'                    => $d->ffa_coa !== null ? (float)$d->ffa_coa : null,
                'ffa_qc'                     => $d->ffa_qc !== null ? (float)$d->ffa_qc : null,
                'minyak_jernih_st'           => (bool) $d->minyak_jernih_st,
                'tangki_bersih_st'           => (bool) $d->tangki_bersih_st,
                'ketebalan_analisa'          => $d->ketebalan_analisa,
                'ketebalan_standar'          => $d->ketebalan_standar,
                'keutuhan_analisa'           => $d->keutuhan_analisa,
                'keutuhan_standar'           => $d->keutuhan_standar,
                'dimensi_panjang_analisa'    => $d->dimensi_panjang_analisa,
                'dimensi_panjang_standar'    => $d->dimensi_panjang_standar,
                'dimensi_lebar_analisa'      => $d->dimensi_lebar_analisa,
                'dimensi_lebar_standar'      => $d->dimensi_lebar_standar,
                'dimensi_tinggi_analisa'     => $d->dimensi_tinggi_analisa,
                'dimensi_tinggi_standar'     => $d->dimensi_tinggi_standar,
                'spesifikasi_analisa'        => $d->spesifikasi_analisa,
                'spesifikasi_standar'        => $d->spesifikasi_standar,
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
            'qc_id'                       => $qc->qc_id,
            'qc_no'                       => $qc->qc_no,
            'status_qc'                   => $qc->status_qc,
            'kategori_barang'             => $qc->kategori_barang ?? 'SINGKONG',
            'nama_jenis'                  => $qc->nama_jenis,
            'po_id'                       => $qc->po_id,
            'po_no'                       => $qc->po?->po_no,
            'supplier_id'                 => $qc->supplier_id,
            'supplier_nm'                 => $qc->supplier?->supplier_nm,
            'gudang_id'                   => $qc->gudang_id,
            'gudang_nm'                   => $qc->gudang?->gudang_nm,
            'negara_produsen'             => $qc->negara_produsen,
            'nama_produsen'               => $qc->nama_produsen,
            'lokasi_panen'                => $qc->lokasi_panen,
            'umur_singkong_bln'           => (float) $qc->umur_singkong_bln,
            'tgl_panen'                   => $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : null,
            'jumlah_sample_kg'            => (float) $qc->jumlah_sample_kg,
            'jumlah_sample_pcs'           => $qc->jumlah_sample_pcs,
            'jumlah_sample_gr'            => (float) $qc->jumlah_sample_gr,
            'surat_jalan_supplier'        => $qc->surat_jalan_supplier,
            'nomor_do'                    => $qc->nomor_do,
            'jumlah_surat_jalan'          => (float) $qc->jumlah_surat_jalan,
            'jumlah_di_pabrik'            => (float) $qc->jumlah_di_pabrik,
            'plat_nomor_truk'             => $qc->plat_nomor_truk,
            'sopir_nama'                  => $qc->sopir_nama,
            'bebas_cemaran_st'            => (bool) $qc->bebas_cemaran_st,
            'angkut_barang_haram_st'      => (bool) $qc->angkut_barang_haram_st,
            'komentar_transportasi'       => $qc->komentar_transportasi,
            'terdaftar_lppom_st'          => (bool) $qc->terdaftar_lppom_st,
            'komentar_lppom'              => $qc->komentar_lppom,
            'ada_sertifikat_halal_st'     => (bool) $qc->ada_sertifikat_halal_st,
            'komentar_sertifikat'         => $qc->komentar_sertifikat,
            'sertifikat_halal_berlaku_st' => (bool) $qc->sertifikat_halal_berlaku_st,
            'komentar_berlaku'            => $qc->komentar_berlaku,
            'tgl_periksa'                 => $qc->tgl_periksa->format('Y-m-d H:i'),
            'petugas_qc_nama'             => $qc->petugas_qc_nama,
            'qc_supervisor_nama'          => $qc->qc_supervisor_nama,
            'catatan_umum'                => $qc->catatan_umum,
            'items'                       => $items,
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

            $kategoriBarang = !empty($data['kategori_barang']) ? strtoupper(trim($data['kategori_barang'])) : 'SINGKONG';

            $header = DatQcInboundHdr::create([
                'qc_no'                       => $qcNo,
                'po_id'                       => !empty($data['po_id']) ? (int)$data['po_id'] : null,
                'supplier_id'                 => (int) $data['supplier_id'],
                'gudang_id'                   => (int) $data['gudang_id'],
                'kategori_barang'             => $kategoriBarang,
                'nama_jenis'                  => !empty($data['nama_jenis']) ? trim($data['nama_jenis']) : null,
                'negara_produsen'             => !empty($data['negara_produsen']) ? trim($data['negara_produsen']) : 'Indonesia',
                'nama_produsen'               => !empty($data['nama_produsen']) ? trim($data['nama_produsen']) : null,
                'lokasi_panen'                => !empty($data['lokasi_panen']) ? trim($data['lokasi_panen']) : null,
                'umur_singkong_bln'           => !empty($data['umur_singkong_bln']) ? (float)$data['umur_singkong_bln'] : null,
                'tgl_panen'                   => !empty($data['tgl_panen']) ? $data['tgl_panen'] : null,
                'jumlah_sample_kg'            => !empty($data['jumlah_sample_kg']) ? (float)$data['jumlah_sample_kg'] : 0,
                'jumlah_sample_pcs'           => !empty($data['jumlah_sample_pcs']) ? (int)$data['jumlah_sample_pcs'] : null,
                'jumlah_sample_gr'            => !empty($data['jumlah_sample_gr']) ? (float)$data['jumlah_sample_gr'] : null,
                'surat_jalan_supplier'        => !empty($data['surat_jalan_supplier']) ? trim($data['surat_jalan_supplier']) : null,
                'nomor_do'                    => !empty($data['nomor_do']) ? trim($data['nomor_do']) : null,
                'jumlah_surat_jalan'          => !empty($data['jumlah_surat_jalan']) ? (float)$data['jumlah_surat_jalan'] : null,
                'jumlah_di_pabrik'            => !empty($data['jumlah_di_pabrik']) ? (float)$data['jumlah_di_pabrik'] : null,
                'plat_nomor_truk'             => !empty($data['plat_nomor_truk']) ? strtoupper(trim($data['plat_nomor_truk'])) : null,
                'sopir_nama'                  => !empty($data['sopir_nama']) ? trim($data['sopir_nama']) : null,
                'bebas_cemaran_st'            => isset($data['bebas_cemaran_st']) ? (bool)$data['bebas_cemaran_st'] : true,
                'angkut_barang_haram_st'      => isset($data['angkut_barang_haram_st']) ? (bool)$data['angkut_barang_haram_st'] : false,
                'komentar_transportasi'       => !empty($data['komentar_transportasi']) ? trim($data['komentar_transportasi']) : null,
                'terdaftar_lppom_st'          => isset($data['terdaftar_lppom_st']) ? (bool)$data['terdaftar_lppom_st'] : false,
                'komentar_lppom'              => !empty($data['komentar_lppom']) ? trim($data['komentar_lppom']) : null,
                'ada_sertifikat_halal_st'     => isset($data['ada_sertifikat_halal_st']) ? (bool)$data['ada_sertifikat_halal_st'] : false,
                'komentar_sertifikat'         => !empty($data['komentar_sertifikat']) ? trim($data['komentar_sertifikat']) : null,
                'sertifikat_halal_berlaku_st' => isset($data['sertifikat_halal_berlaku_st']) ? (bool)$data['sertifikat_halal_berlaku_st'] : false,
                'komentar_berlaku'            => !empty($data['komentar_berlaku']) ? trim($data['komentar_berlaku']) : null,
                'tgl_periksa'                 => $tglPeriksa,
                'petugas_qc_nama'             => $petugasQc,
                'qc_supervisor_nama'          => !empty($data['qc_supervisor_nama']) ? trim($data['qc_supervisor_nama']) : null,
                'status_qc'                   => 'SIAP_GUDANG',
                'status_uji_goreng'           => !empty($data['status_uji_goreng']) ? $data['status_uji_goreng'] : 'SELESAI',
                'tgl_uji_goreng'              => ($data['status_uji_goreng'] ?? 'SELESAI') === 'SELESAI' ? now() : null,
                'petugas_uji_goreng'          => ($data['status_uji_goreng'] ?? 'SELESAI') === 'SELESAI' ? $petugasQc : null,
                'catatan_umum'                => !empty($data['catatan_umum']) ? trim($data['catatan_umum']) : null,
            ]);

            $allRejected = true;
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item komoditas yang diinspeksi oleh QC.");
            }

            foreach ($items as $row) {
                $gross = (float) ($row['qty_timbang_gross'] ?? ($data['jumlah_di_pabrik'] ?? ($data['jumlah_surat_jalan'] ?? 0)));
                $kadarAir = (float) ($row['kadar_air_persen'] ?? 0);
                $refraksiPersen = (float) ($row['refraksi_persen'] ?? 0);
                $rejectQty = (float) ($row['qty_reject'] ?? 0);

                // Cek apakah user eksplisit memilih KESIMPULAN: TOLAK
                $explicitKeputusan = $row['keputusan_qc'] ?? ($data['kesimpulan_qc'] ?? null);

                if ($explicitKeputusan === 'TOLAK' || $explicitKeputusan === 'REJECT_TOTAL') {
                    $keputusan = 'REJECT_TOTAL';
                    $grade = 'REJECT';
                    $qtyRefraksi = 0;
                    $rejectQty = $gross > 0 ? $gross : 1;
                    $nettoLolos = 0;
                } else {
                    // Hitung potongan kotoran/refraksi
                    $qtyRefraksi = round($gross * ($refraksiPersen / 100), 4);
                    // Hitung netto lolos
                    $nettoLolos = max(0, round($gross - $qtyRefraksi - $rejectQty, 4));

                    $grade = !empty($row['grade_cd']) ? $row['grade_cd'] : 'A';

                    // Tentukan keputusan QC otomatis
                    if ($nettoLolos <= 0 && $gross > 0) {
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
                }

                DatQcInboundDtl::create([
                    'qc_id'                      => $header->qc_id,
                    'podtl_id'                   => !empty($row['podtl_id']) ? (int)$row['podtl_id'] : null,
                    'barang_id'                  => (int) $row['barang_id'],
                    'status_raw_material'        => $row['status_raw_material'] ?? 'OK',
                    'isi_kering'                 => isset($row['isi_kering']) ? (bool)$row['isi_kering'] : true,
                    'isi_basah'                  => !empty($row['isi_basah']),
                    'isi_gumpal'                 => !empty($row['isi_gumpal']),
                    'isi_berminyak'              => !empty($row['isi_berminyak']),
                    'kemasan_kondisi'            => $row['kemasan_kondisi'] ?? 'OK',
                    'kemasan_kotor'              => !empty($row['kemasan_kotor']),
                    'kemasan_apek'               => !empty($row['kemasan_apek']),
                    'kemasan_basah'              => !empty($row['kemasan_basah']),
                    'kemasan_sobek'              => !empty($row['kemasan_sobek']),
                    'kemasan_jamur'              => !empty($row['kemasan_jamur']),
                    'kemasan_berminyak'          => !empty($row['kemasan_berminyak']),
                    'kemasan_berdebu'            => !empty($row['kemasan_berdebu']),
                    'tipe_wadah_minyak'          => $row['tipe_wadah_minyak'] ?? 'TANGKI',
                    'kondisi_tangki_jerigen'     => $row['kondisi_tangki_jerigen'] ?? 'OK',
                    'ffa_coa'                    => isset($row['ffa_coa']) && $row['ffa_coa'] !== '' ? (float)$row['ffa_coa'] : null,
                    'ffa_qc'                     => isset($row['ffa_qc']) && $row['ffa_qc'] !== '' ? (float)$row['ffa_qc'] : null,
                    'minyak_jernih_st'           => isset($row['minyak_jernih_st']) ? (bool)$row['minyak_jernih_st'] : true,
                    'tangki_bersih_st'           => isset($row['tangki_bersih_st']) ? (bool)$row['tangki_bersih_st'] : true,
                    'ketebalan_analisa'          => $row['ketebalan_analisa'] ?? null,
                    'ketebalan_standar'          => $row['ketebalan_standar'] ?? null,
                    'keutuhan_analisa'           => $row['keutuhan_analisa'] ?? 'Tidak Sobek',
                    'keutuhan_standar'           => $row['keutuhan_standar'] ?? 'Tidak Sobek',
                    'dimensi_panjang_analisa'    => $row['dimensi_panjang_analisa'] ?? null,
                    'dimensi_panjang_standar'    => $row['dimensi_panjang_standar'] ?? null,
                    'dimensi_lebar_analisa'      => $row['dimensi_lebar_analisa'] ?? null,
                    'dimensi_lebar_standar'      => $row['dimensi_lebar_standar'] ?? null,
                    'dimensi_tinggi_analisa'     => $row['dimensi_tinggi_analisa'] ?? null,
                    'dimensi_tinggi_standar'     => $row['dimensi_tinggi_standar'] ?? null,
                    'spesifikasi_analisa'        => $row['spesifikasi_analisa'] ?? null,
                    'spesifikasi_standar'        => $row['spesifikasi_standar'] ?? null,
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

    /**
     * Memperbarui hasil uji goreng (Pengujian II / Fryer test) susulan
     */
    public function updateUjiGoreng(int $qcId, array $data, User $user): DatQcInboundHdr
    {
        return DB::transaction(function () use ($qcId, $data, $user) {
            $qc = DatQcInboundHdr::with('details')->findOrFail($qcId);

            $items = $data['items'] ?? [];
            foreach ($items as $qcdtlId => $dtlData) {
                $detail = $qc->details->firstWhere('qcdtl_id', (int)$qcdtlId);
                if ($detail) {
                    $detail->update([
                        'fryer_rasa'             => $dtlData['fryer_rasa'] ?? 'TIDAK_PAHIT',
                        'fryer_tekstur'          => $dtlData['fryer_tekstur'] ?? 'RENYAH',
                        'fryer_penampakan'       => $dtlData['fryer_penampakan'] ?? 'TIDAK_OILSOAKED',
                        'defect_breakage_persen' => (float)($dtlData['defect_breakage_persen'] ?? 0),
                        'defect_cluster_persen'  => (float)($dtlData['defect_cluster_persen'] ?? 0),
                        'defect_foldover_persen' => (float)($dtlData['defect_foldover_persen'] ?? 0),
                        'defect_oilsoaked_persen'=> (float)($dtlData['defect_oilsoaked_persen'] ?? 0),
                        'defect_gambos_persen'   => (float)($dtlData['defect_gambos_persen'] ?? 0),
                    ]);
                }
            }

            $qc->update([
                'status_uji_goreng'  => 'SELESAI',
                'tgl_uji_goreng'     => now(),
                'petugas_uji_goreng' => $user->name,
                'catatan_umum'       => !empty($data['catatan_umum']) ? $data['catatan_umum'] : $qc->catatan_umum,
            ]);

            return $qc->fresh(['details.barang']);
        });
    }

    /**
     * Memperbarui data tiket inspeksi QC masuk (Edit).
     * Jika tiket sudah ditarik ke Penerimaan Barang (GRN):
     * - Staf biasa: Ditolak (Locked).
     * - Super Admin: Diizinkan (Bypass), dan sistem otomatis meng-cascade update kuantitas ke GRN & Stok Batch terkait!
     */
    public function update(DatQcInboundHdr $qc, array $data, User $user): DatQcInboundHdr
    {
        return DB::transaction(function () use ($qc, $data, $user) {
            $isSuperAdmin = $user->isSuperAdmin();
            $terimaLinked = $qc->terima;

            // Validasi: Staf biasa tidak boleh mengedit jika sudah ditarik ke Penerimaan Gudang
            if ($terimaLinked && !$isSuperAdmin) {
                throw new Exception("Tiket QC #{$qc->qc_no} sudah diproses ke Penerimaan Barang (GRN #{$terimaLinked->terima_no}). Anda tidak memiliki wewenang mengedit data yang sudah masuk gudang. Silakan hubungi Super Administrator.");
            }

            $kategoriBarang = !empty($data['kategori_barang']) ? strtoupper(trim($data['kategori_barang'])) : ($qc->kategori_barang ?? 'SINGKONG');

            // 1. Update Header
            $qc->fill([
                'po_id'                       => !empty($data['po_id']) ? (int)$data['po_id'] : $qc->po_id,
                'supplier_id'                 => !empty($data['supplier_id']) ? (int)$data['supplier_id'] : $qc->supplier_id,
                'gudang_id'                   => !empty($data['gudang_id']) ? (int)$data['gudang_id'] : $qc->gudang_id,
                'kategori_barang'             => $kategoriBarang,
                'nama_jenis'                  => !empty($data['nama_jenis']) ? trim($data['nama_jenis']) : $qc->nama_jenis,
                'negara_produsen'             => !empty($data['negara_produsen']) ? trim($data['negara_produsen']) : $qc->negara_produsen,
                'nama_produsen'               => !empty($data['nama_produsen']) ? trim($data['nama_produsen']) : $qc->nama_produsen,
                'lokasi_panen'                => !empty($data['lokasi_panen']) ? trim($data['lokasi_panen']) : $qc->lokasi_panen,
                'umur_singkong_bln'           => isset($data['umur_singkong_bln']) ? (float)$data['umur_singkong_bln'] : $qc->umur_singkong_bln,
                'tgl_panen'                   => !empty($data['tgl_panen']) ? $data['tgl_panen'] : $qc->tgl_panen,
                'jumlah_sample_kg'            => isset($data['jumlah_sample_kg']) ? (float)$data['jumlah_sample_kg'] : $qc->jumlah_sample_kg,
                'jumlah_sample_pcs'           => isset($data['jumlah_sample_pcs']) ? (int)$data['jumlah_sample_pcs'] : $qc->jumlah_sample_pcs,
                'jumlah_sample_gr'            => isset($data['jumlah_sample_gr']) ? (float)$data['jumlah_sample_gr'] : $qc->jumlah_sample_gr,
                'surat_jalan_supplier'        => !empty($data['surat_jalan_supplier']) ? trim($data['surat_jalan_supplier']) : $qc->surat_jalan_supplier,
                'nomor_do'                    => !empty($data['nomor_do']) ? trim($data['nomor_do']) : $qc->nomor_do,
                'jumlah_surat_jalan'          => isset($data['jumlah_surat_jalan']) ? (float)$data['jumlah_surat_jalan'] : $qc->jumlah_surat_jalan,
                'jumlah_di_pabrik'            => isset($data['jumlah_di_pabrik']) ? (float)$data['jumlah_di_pabrik'] : $qc->jumlah_di_pabrik,
                'plat_nomor_truk'             => !empty($data['plat_nomor_truk']) ? strtoupper(trim($data['plat_nomor_truk'])) : $qc->plat_nomor_truk,
                'sopir_nama'                  => !empty($data['sopir_nama']) ? trim($data['sopir_nama']) : $qc->sopir_nama,
                'bebas_cemaran_st'            => isset($data['bebas_cemaran_st']) ? (bool)$data['bebas_cemaran_st'] : $qc->bebas_cemaran_st,
                'angkut_barang_haram_st'      => isset($data['angkut_barang_haram_st']) ? (bool)$data['angkut_barang_haram_st'] : $qc->angkut_barang_haram_st,
                'komentar_transportasi'       => !empty($data['komentar_transportasi']) ? trim($data['komentar_transportasi']) : $qc->komentar_transportasi,
                'terdaftar_lppom_st'          => isset($data['terdaftar_lppom_st']) ? (bool)$data['terdaftar_lppom_st'] : $qc->terdaftar_lppom_st,
                'komentar_lppom'              => !empty($data['komentar_lppom']) ? trim($data['komentar_lppom']) : $qc->komentar_lppom,
                'ada_sertifikat_halal_st'     => isset($data['ada_sertifikat_halal_st']) ? (bool)$data['ada_sertifikat_halal_st'] : $qc->ada_sertifikat_halal_st,
                'komentar_sertifikat'         => !empty($data['komentar_sertifikat']) ? trim($data['komentar_sertifikat']) : $qc->komentar_sertifikat,
                'sertifikat_halal_berlaku_st' => isset($data['sertifikat_halal_berlaku_st']) ? (bool)$data['sertifikat_halal_berlaku_st'] : $qc->sertifikat_halal_berlaku_st,
                'komentar_berlaku'            => !empty($data['komentar_berlaku']) ? trim($data['komentar_berlaku']) : $qc->komentar_berlaku,
                'petugas_qc_nama'             => !empty($data['petugas_qc_nama']) ? trim($data['petugas_qc_nama']) : $qc->petugas_qc_nama,
                'qc_supervisor_nama'          => !empty($data['qc_supervisor_nama']) ? trim($data['qc_supervisor_nama']) : $qc->qc_supervisor_nama,
                'catatan_umum'                => !empty($data['catatan_umum']) ? trim($data['catatan_umum']) : $qc->catatan_umum,
            ]);

            if ($isSuperAdmin && $terimaLinked) {
                $qc->catatan_umum .= " [REVISI SUPER ADMIN oleh {$user->name} pada " . now()->format('d/m/Y H:i') . "]";
            }
            $qc->save();

            // 2. Update Details
            $items = $data['items'] ?? [];
            if (!empty($items)) {
                $allRejected = true;
                $deltaQtyPerBarang = [];

                foreach ($items as $idx => $row) {
                    $barangId = (int)$row['barang_id'];
                    $gross = (float) ($row['qty_timbang_gross'] ?? ($data['jumlah_di_pabrik'] ?? ($data['jumlah_surat_jalan'] ?? 0)));
                    $kadarAir = (float) ($row['kadar_air_persen'] ?? 0);
                    $refraksiPersen = (float) ($row['refraksi_persen'] ?? 0);
                    $rejectQty = (float) ($row['qty_reject'] ?? 0);

                    $explicitKeputusan = $row['keputusan_qc'] ?? ($data['kesimpulan_qc'] ?? null);
                    if ($explicitKeputusan === 'TOLAK' || $explicitKeputusan === 'REJECT_TOTAL') {
                        $keputusan = 'REJECT_TOTAL';
                        $grade = 'REJECT';
                        $qtyRefraksi = 0;
                        $rejectQty = $gross > 0 ? $gross : 1;
                        $nettoLolos = 0;
                    } else {
                        $qtyRefraksi = round($gross * ($refraksiPersen / 100), 4);
                        $nettoLolos = max(0, round($gross - $qtyRefraksi - $rejectQty, 4));
                        $grade = !empty($row['grade_cd']) ? $row['grade_cd'] : 'A';

                        if ($nettoLolos <= 0 && $gross > 0) {
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
                    }

                    // Cari detail existing
                    $existingDtl = null;
                    if (!empty($row['qcdtl_id'])) {
                        $existingDtl = $qc->details->firstWhere('qcdtl_id', (int)$row['qcdtl_id']);
                    }
                    if (!$existingDtl) {
                        $existingDtl = $qc->details->values()->get($idx);
                    }

                    $oldNetto = $existingDtl ? (float)$existingDtl->qty_netto_lolos : 0;
                    $diffNetto = round($nettoLolos - $oldNetto, 4);
                    $deltaQtyPerBarang[$barangId] = ($deltaQtyPerBarang[$barangId] ?? 0) + $diffNetto;

                    $payloadDtl = [
                        'podtl_id'                   => !empty($row['podtl_id']) ? (int)$row['podtl_id'] : ($existingDtl?->podtl_id),
                        'barang_id'                  => $barangId,
                        'status_raw_material'        => $row['status_raw_material'] ?? 'OK',
                        'isi_kering'                 => isset($row['isi_kering']) ? (bool)$row['isi_kering'] : true,
                        'isi_basah'                  => !empty($row['isi_basah']),
                        'isi_gumpal'                 => !empty($row['isi_gumpal']),
                        'isi_berminyak'              => !empty($row['isi_berminyak']),
                        'kemasan_kondisi'            => $row['kemasan_kondisi'] ?? 'OK',
                        'kemasan_kotor'              => !empty($row['kemasan_kotor']),
                        'kemasan_apek'               => !empty($row['kemasan_apek']),
                        'kemasan_basah'              => !empty($row['kemasan_basah']),
                        'kemasan_sobek'              => !empty($row['kemasan_sobek']),
                        'kemasan_jamur'              => !empty($row['kemasan_jamur']),
                        'kemasan_berminyak'          => !empty($row['kemasan_berminyak']),
                        'kemasan_berdebu'            => !empty($row['kemasan_berdebu']),
                        'tipe_wadah_minyak'          => $row['tipe_wadah_minyak'] ?? 'TANGKI',
                        'kondisi_tangki_jerigen'     => $row['kondisi_tangki_jerigen'] ?? 'OK',
                        'ffa_coa'                    => isset($row['ffa_coa']) && $row['ffa_coa'] !== '' ? (float)$row['ffa_coa'] : null,
                        'ffa_qc'                     => isset($row['ffa_qc']) && $row['ffa_qc'] !== '' ? (float)$row['ffa_qc'] : null,
                        'minyak_jernih_st'           => isset($row['minyak_jernih_st']) ? (bool)$row['minyak_jernih_st'] : true,
                        'tangki_bersih_st'           => isset($row['tangki_bersih_st']) ? (bool)$row['tangki_bersih_st'] : true,
                        'ketebalan_analisa'          => $row['ketebalan_analisa'] ?? null,
                        'ketebalan_standar'          => $row['ketebalan_standar'] ?? null,
                        'keutuhan_analisa'           => $row['keutuhan_analisa'] ?? 'Tidak Sobek',
                        'keutuhan_standar'           => $row['keutuhan_standar'] ?? 'Tidak Sobek',
                        'dimensi_panjang_analisa'    => $row['dimensi_panjang_analisa'] ?? null,
                        'dimensi_panjang_standar'    => $row['dimensi_panjang_standar'] ?? null,
                        'dimensi_lebar_analisa'      => $row['dimensi_lebar_analisa'] ?? null,
                        'dimensi_lebar_standar'      => $row['dimensi_lebar_standar'] ?? null,
                        'dimensi_tinggi_analisa'     => $row['dimensi_tinggi_analisa'] ?? null,
                        'dimensi_tinggi_standar'     => $row['dimensi_tinggi_standar'] ?? null,
                        'spesifikasi_analisa'        => $row['spesifikasi_analisa'] ?? null,
                        'spesifikasi_standar'        => $row['spesifikasi_standar'] ?? null,
                        'diameter_kurang_4cm_persen' => (float)($row['diameter_kurang_4cm_persen'] ?? 0),
                        'diameter_lebih_4cm_persen'  => (float)($row['diameter_lebih_4cm_persen'] ?? 100),
                        'kondisi_segar'              => !empty($row['kondisi_segar']),
                        'kondisi_layu'               => !empty($row['kondisi_layu']),
                        'kondisi_basah'              => !empty($row['kondisi_basah']),
                        'kondisi_terkelupas'         => !empty($row['kondisi_terkelupas']),
                        'kondisi_busuk'              => !empty($row['kondisi_busuk']),
                        'kondisi_berjamur'           => !empty($row['kondisi_berjamur']),
                        'kondisi_lembek'             => !empty($row['kondisi_lembek']),
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
                    ];

                    if ($existingDtl) {
                        $existingDtl->update($payloadDtl);
                    } else {
                        $payloadDtl['qc_id'] = $qc->qc_id;
                        DatQcInboundDtl::create($payloadDtl);
                    }
                }

                if ($allRejected) {
                    $qc->status_qc = 'DITOLAK_TOTAL';
                    $qc->save();
                } elseif ($qc->status_qc === 'DITOLAK_TOTAL') {
                    $qc->status_qc = $terimaLinked ? 'DITERIMA_GUDANG' : 'SIAP_GUDANG';
                    $qc->save();
                }

                // 3. AUTO-CASCADE SINKRONISASI KE PENERIMAAN GUDANG (GRN) & STOK BATCH (KHUSUS SUPER ADMIN)
                if ($isSuperAdmin && $terimaLinked) {
                    $terimaLinked->load('details');
                    $subtotalBaru = 0;
                    $ppnNominalBaru = 0;

                    foreach ($terimaLinked->details as $tDtl) {
                        $diff = $deltaQtyPerBarang[$tDtl->barang_id] ?? 0;
                        if ($diff != 0) {
                            $newQtyTerima = max(0, (float)$tDtl->terima_qty + $diff);
                            $hargaUnit = (float)$tDtl->harga_netto;
                            $newSubtotal = round($newQtyTerima * $hargaUnit, 4);

                            $tDtl->update([
                                'terima_qty'     => $newQtyTerima,
                                'subtotal_netto' => $newSubtotal,
                                'subtotal_tagihan' => $newSubtotal + (float)$tDtl->ppn_nominal,
                            ]);

                            // Auto adjust Batch Stok terkait jika ada batch_no
                            if (!empty($tDtl->batch_no)) {
                                $batch = \App\Models\Gudang\DatStokBatch::where('batch_no', $tDtl->batch_no)
                                    ->where('barang_id', $tDtl->barang_id)
                                    ->first();
                                if ($batch) {
                                    $batch->masuk_qty = max(0, (float)$batch->masuk_qty + $diff);
                                    $batch->sisa_qty = max(0, (float)$batch->sisa_qty + $diff);
                                    if ($batch->sisa_qty <= 0) {
                                        $batch->status_batch = 'HABIS';
                                    } elseif ($batch->sisa_qty < $batch->masuk_qty) {
                                        $batch->status_batch = 'DIGUNAKAN';
                                    } else {
                                        $batch->status_batch = 'TERSEDIA';
                                    }
                                    $batch->save();
                                }
                            }
                        }
                        $subtotalBaru += (float)$tDtl->subtotal_netto;
                        $ppnNominalBaru += (float)$tDtl->ppn_nominal;
                    }

                    // Update total di Header GRN
                    $potonganGlobal = (float)$terimaLinked->potongan_nominal;
                    $terimaLinked->update([
                        'subtotal_nominal' => $subtotalBaru,
                        'dpp_nominal'      => max(0, $subtotalBaru - $potonganGlobal),
                        'ppn_nominal'      => $ppnNominalBaru,
                        'total_tagihan'    => max(0, $subtotalBaru + $ppnNominalBaru - $potonganGlobal),
                    ]);
                }
            }

            return $qc->fresh(['details.barang', 'supplier', 'gudang', 'po', 'terima']);
        });
    }

    /**
     * Menghapus tiket QC (Soft delete).
     */
    public function destroy(DatQcInboundHdr $qc, User $user): bool
    {
        return DB::transaction(function () use ($qc, $user) {
            $terimaLinked = $qc->terima;
            if ($terimaLinked && !$user->isSuperAdmin()) {
                throw new Exception("Tiket QC #{$qc->qc_no} sudah diproses ke Penerimaan Barang (GRN #{$terimaLinked->terima_no}) dan tidak dapat dihapus. Silakan hubungi Super Administrator.");
            }

            if ($terimaLinked && $user->isSuperAdmin()) {
                // Unlink qc_id dari GRN
                $terimaLinked->update(['qc_id' => null]);
            }

            $qc->details()->update([
                'deleted_st' => true,
                'deleted_by' => $user->id,
            ]);

            $qc->update([
                'deleted_st' => true,
                'deleted_by' => $user->id,
            ]);

            return true;
        });
    }
}
