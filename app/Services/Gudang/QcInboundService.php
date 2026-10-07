<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatQcInboundDtl;
use App\Models\Gudang\DatQcInboundHdr;
use App\Models\Gudang\DatTerimaHdr;
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
        $query = DatQcInboundHdr::with([
            'supplier', 
            'gudang', 
            'po', 
            'details.barang', 
            'terima', 
            'pengujian2List.details.barang',
            'pengujian2List.terima'
        ])
            ->where('deleted_st', false)
            ->orderBy('tgl_periksa', 'desc')
            ->orderBy('qc_id', 'desc');

        // Untuk tampilan 1 baris per truk, ambil kedatangan induk (Pengujian 1 atau tiket tunggal)
        if (empty($filters['tahap_uji'])) {
            $query->where(function ($q) {
                $q->whereNull('parent_qc_id')
                  ->orWhereDoesntHave('parentQc');
            });
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('qc_no', 'ilike', "%{$search}%")
                  ->orWhere('surat_jalan_supplier', 'ilike', "%{$search}%")
                  ->orWhere('plat_nomor_truk', 'ilike', "%{$search}%")
                  ->orWhere('sopir_nama', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('supplier_nm', 'ilike', "%{$search}%"))
                  ->orWhereHas('pengujian2List', fn($pq) => $pq->where('qc_no', 'ilike', "%{$search}%"));
            });
        }

        if (!empty($filters['kategori_barang'])) {
            $query->where('kategori_barang', $filters['kategori_barang']);
        }

        if (!empty($filters['status_qc'])) {
            $statusVal = strtoupper((string) $filters['status_qc']);
            if ($statusVal === 'MENUNGGU_UJI_2') {
                $query->where('kategori_barang', 'SINGKONG')
                      ->where('tahap_uji', 'PENGUJIAN_1')
                      ->where('status_qc', 'SIAP_GUDANG')
                      ->doesntHave('pengujian2List');
            } elseif (in_array($statusVal, ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'PARSIAL'])) {
                $query->where(function ($q) {
                    $q->whereIn('status_qc', ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL'])
                      ->orWhere(function ($sq) {
                          $sq->where('status_qc', '!=', 'DITOLAK_TOTAL')
                             ->whereHas('details', fn($dq) => $dq->where('qty_reject', '>', 0));
                      })
                      ->orWhereHas('pengujian2List', function ($pq) {
                          $pq->whereIn('status_qc', ['DITOLAK_TOTAL', 'DITERIMA_PARSIAL', 'DITOLAK_PARSIAL'])
                             ->orWhereHas('details', fn($dq) => $dq->where('qty_reject', '>', 0));
                      })
                      ->orWhere(function ($sq) {
                          $sq->where('status_qc', 'DITOLAK_TOTAL')
                             ->whereHas('pengujian2List', fn($pq) => $pq->whereIn('status_qc', ['DITERIMA_GUDANG', 'SIAP_GUDANG']));
                      });
                });
            } elseif ($statusVal === 'DITERIMA_GUDANG') {
                $query->where(function ($q) {
                    $q->whereIn('status_qc', ['DITERIMA_GUDANG', 'DITERIMA_PARSIAL'])
                      ->orWhereHas('pengujian2List', fn($pq) => $pq->whereIn('status_qc', ['DITERIMA_GUDANG', 'DITERIMA_PARSIAL']))
                      ->orWhereHas('terima', fn($tq) => $tq->where('deleted_st', false));
                });
            } elseif ($statusVal === 'DITOLAK_TOTAL') {
                $query->where(function ($q) {
                    $q->where('status_qc', 'DITOLAK_TOTAL')
                      ->where(function ($sq) {
                          $sq->doesntHave('pengujian2List')
                             ->orWhereDoesntHave('pengujian2List', fn($pq) => $pq->whereIn('status_qc', ['DITERIMA_GUDANG', 'SIAP_GUDANG']));
                      });
                });
            } else {
                $query->where(function ($q) use ($statusVal) {
                    $q->where('status_qc', $statusVal)
                      ->orWhereHas('pengujian2List', fn($pq) => $pq->where('status_qc', $statusVal));
                });
            }
        }

        if (!empty($filters['status_uji_goreng'])) {
            $query->where('status_uji_goreng', $filters['status_uji_goreng']);
        }

        if (!empty($filters['tahap_uji'])) {
            $query->where('tahap_uji', $filters['tahap_uji']);
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

        if (!empty($filters['allowed_gudang_ids'])) {
            $query->whereIn('gudang_id', $filters['allowed_gudang_ids']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Mengambil seluruh tiket QC yang sudah selesai dan siap ditarik oleh Admin Gudang
     */
    public function getSiapGudangTickets(): array
    {
        return DatQcInboundHdr::with([
            'supplier', 
            'gudang', 
            'po', 
            'details.barang.satuanDasar',
            'pengujian2List.details.barang.satuanDasar'
        ])
            ->where('deleted_st', false)
            ->whereNull('parent_qc_id')
            ->where('status_qc', 'SIAP_GUDANG')
            ->whereDoesntHave('terima', function ($q) {
                $q->where('deleted_st', false);
            })
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($h) {
                $totalGross = (float) $h->details->sum('qty_timbang_gross');
                $totalNetto = (float) $h->details->sum('qty_netto_lolos');
                $totalReject = (float) $h->details->sum('qty_reject');
                $totalRefraksi = (float) $h->details->sum('qty_refraksi');
                $itemNames = $h->details->map(fn($d) => $d->barang?->barang_nm)->filter();
                $hasP2 = $h->pengujian2List && $h->pengujian2List->isNotEmpty();
                $p2Count = $h->pengujian2List ? $h->pengujian2List->count() : 0;

                if ($hasP2) {
                    foreach ($h->pengujian2List as $p2) {
                        $totalGross += (float) $p2->details->sum('qty_timbang_gross');
                        $totalNetto += (float) $p2->details->sum('qty_netto_lolos');
                        $totalReject += (float) $p2->details->sum('qty_reject');
                        $totalRefraksi += (float) $p2->details->sum('qty_refraksi');
                        $itemNames = $itemNames->merge($p2->details->map(fn($d) => $d->barang?->barang_nm)->filter());
                    }
                }

                $grades = $h->details->pluck('grade_cd')
                    ->merge($h->pengujian2List ? $h->pengujian2List->flatMap(fn($p) => $p->details->pluck('grade_cd')) : [])
                    ->filter()->unique()->values()->all();

                $itemSummary = $itemNames->unique()->implode(', ');
                $itemCount = $h->details->count() + ($h->pengujian2List ? $h->pengujian2List->sum(fn($p2) => $p2->details->count()) : 0);

                return [
                    'qc_id'                 => $h->qc_id,
                    'qc_no'                 => $h->qc_no,
                    'kategori_barang'       => $h->kategori_barang ?? 'SINGKONG',
                    'nama_jenis'            => $h->nama_jenis,
                    'tahap_uji'             => $h->tahap_uji,
                    'has_p2'                => $hasP2,
                    'p2_count'              => $p2Count,
                    'grades'                => $grades,
                    'po_id'                 => $h->po_id,
                    'po_no'                 => $h->po?->po_no ?? 'Non-PO',
                    'supplier_id'           => $h->supplier_id,
                    'supplier_nm'           => $h->supplier?->supplier_nm ?? '-',
                    'gudang_id'             => $h->gudang_id,
                    'gudang_nm'             => $h->gudang?->gudang_nm ?? '-',
                    'surat_jalan_supplier'  => $h->surat_jalan_supplier,
                    'plat_nomor_truk'       => $h->plat_nomor_truk ?: ($h->pengujian2List->first()?->plat_nomor_truk ?? '-'),
                    'sopir_nama'            => $h->sopir_nama ?: ($h->pengujian2List->first()?->sopir_nama ?? '-'),
                    'tgl_periksa'           => $h->tgl_periksa->format('d/m/Y H:i'),
                    'tgl_periksa_raw'       => $h->tgl_periksa->format('Y-m-d'),
                    'total_gross'           => (float) $totalGross,
                    'total_netto'           => (float) $totalNetto,
                    'total_reject'          => (float) $totalReject,
                    'total_refraksi'        => (float) $totalRefraksi,
                    'item_count'            => $itemCount,
                    'item_summary'          => $itemSummary,
                ];
            })
            ->toArray();
    }

    /**
     * Mengambil detail lengkap tiket QC untuk auto-fill di form Penerimaan Barang
     */
    public function getTicketData(int $qcId): array
    {
        $qc = DatQcInboundHdr::with([
            'supplier', 
            'gudang', 
            'po.details', 
            'details.barang.satuanDasar',
            'details.poDetail',
            'pengujian2List.details.barang.satuanDasar',
            'pengujian2List.details.poDetail',
        ])
            ->where('deleted_st', false)
            ->findOrFail($qcId);

        // Jika tiket ini adalah anak (Pengujian 2), arahkan ke tiket induk agar data armada & PO lengkap
        if ($qc->parent_qc_id) {
            $parent = DatQcInboundHdr::with([
                'supplier', 
                'gudang', 
                'po.details', 
                'details.barang.satuanDasar',
                'details.poDetail',
                'pengujian2List.details.barang.satuanDasar',
                'pengujian2List.details.poDetail',
            ])->find($qc->parent_qc_id);
            if ($parent) {
                $qc = $parent;
            }
        }

        $items = [];
        $hasP2 = $qc->pengujian2List->isNotEmpty();

        // 1. Muat Item dari Pengujian 1 (Setengah Bak Awal)
        foreach ($qc->details as $d) {
            $barang = $d->barang;
            $acronym = app(CodeGeneratorService::class)->extractBarangAcronym(
                $barang?->barang_nm,
                $barang?->barang_cd
            );
            $batchPrefix = ($acronym ?: 'BRG') . '-';
            $labelSuffix = $hasP2 ? ' (Uji 1 - 1/2 Bak)' : '';
            $poDtl = $d->poDetail;
            $hargaPo = $poDtl ? (float) $poDtl->harga_nominal : (float) ($barang?->harga_beli_standar ?? 0);

            $items[] = [
                'qcdtl_id'                   => $d->qcdtl_id,
                'podtl_id'                   => $d->podtl_id,
                'barang_id'                  => $d->barang_id,
                'barang_cd'                  => $barang?->barang_cd,
                'barang_nm'                  => ($barang?->barang_nm ?? 'SINGKONG') . $labelSuffix,
                'satuan_nm'                  => $barang?->satuanDasar?->satuan_nm ?? 'KG',
                'batch_prefix'               => $batchPrefix,
                'gross_qty'                  => (float) $d->qty_timbang_gross,
                'kadar_air'                  => (float) $d->kadar_air_persen,
                'refraksi_persen'            => (float) $d->refraksi_persen,
                'refraksi_qty'               => (float) $d->qty_refraksi,
                'reject_qty'                 => (float) $d->qty_reject,
                'netto_qty'                  => (float) $d->qty_netto_lolos,
                'grade_cd'                   => $d->grade_cd ?: 'A',
                'kondisi_fisik'              => $d->kondisi_fisik,
                'keputusan_qc'               => $d->keputusan_qc,
                'status_raw_material'        => $d->status_raw_material ?? 'OK',
                'isi_kering'                 => (bool) $d->isi_kering,
                'isi_basah'                  => (bool) $d->isi_basah,
                'isi_gumpal'                 => (bool) $d->isi_gumpal,
                'isi_berminyak'              => (bool) $d->isi_berminyak,
                'kemasan_kondisi'            => $d->kemasan_kondisi ?? 'OK',
                'catatan'                    => $d->catatan_dtl ?: ($qc->catatan_umum ?: 'Uji 1 (Setengah Bak)'),
                'std_harga'                  => $hargaPo,
                'diskon_persen'              => (float) ($poDtl?->diskon_persen ?? 0),
                'ppn_tipe'                   => $poDtl?->ppn_tipe ?? 'NON_PPN',
            ];
        }

        // 2. Muat Item dari Pengujian 2 (Sisa Bak Lantai Produksi) jika ada
        foreach ($qc->pengujian2List as $p2) {
            foreach ($p2->details as $d2) {
                $barang2 = $d2->barang ?: $qc->details->first()?->barang;
                $acronym2 = app(CodeGeneratorService::class)->extractBarangAcronym(
                    $barang2?->barang_nm,
                    $barang2?->barang_cd
                );
                $batchPrefix2 = ($acronym2 ?: 'BRG') . '-';
                $poDtl2 = $d2->poDetail ?: $qc->details->first()?->poDetail;
                $hargaPo2 = $poDtl2 ? (float) $poDtl2->harga_nominal : (float) ($barang2?->harga_beli_standar ?? 0);

                $items[] = [
                    'qcdtl_id'                   => $d2->qcdtl_id,
                    'podtl_id'                   => $d2->podtl_id ?: $qc->details->first()?->podtl_id,
                    'barang_id'                  => $d2->barang_id ?: $qc->details->first()?->barang_id,
                    'barang_cd'                  => $barang2?->barang_cd,
                    'barang_nm'                  => ($barang2?->barang_nm ?? 'SINGKONG') . ' (Uji 2 - Sisa Bak)',
                    'satuan_nm'                  => $barang2?->satuanDasar?->satuan_nm ?? 'KG',
                    'batch_prefix'               => $batchPrefix2,
                    'gross_qty'                  => (float) $d2->qty_timbang_gross,
                    'kadar_air'                  => (float) $d2->kadar_air_persen,
                    'refraksi_persen'            => (float) $d2->refraksi_persen,
                    'refraksi_qty'               => (float) $d2->qty_refraksi,
                    'reject_qty'                 => (float) $d2->qty_reject,
                    'netto_qty'                  => (float) $d2->qty_netto_lolos,
                    'grade_cd'                   => $d2->grade_cd ?: 'A',
                    'kondisi_fisik'              => $d2->kondisi_fisik,
                    'keputusan_qc'               => $d2->keputusan_qc,
                    'status_raw_material'        => $d2->status_raw_material ?? 'OK',
                    'isi_kering'                 => (bool) $d2->isi_kering,
                    'isi_basah'                  => (bool) $d2->isi_basah,
                    'isi_gumpal'                 => (bool) $d2->isi_gumpal,
                    'isi_berminyak'              => (bool) $d2->isi_berminyak,
                    'kemasan_kondisi'            => $d2->kemasan_kondisi ?? 'OK',
                    'catatan'                    => $d2->catatan_dtl ?: ($p2->catatan_umum ?: 'Uji 2 (Sisa Bak)'),
                    'std_harga'                  => $hargaPo2,
                    'diskon_persen'              => (float) ($poDtl2?->diskon_persen ?? 0),
                    'ppn_tipe'                   => $poDtl2?->ppn_tipe ?? 'NON_PPN',
                ];
            }
        }

        $totalPabrik = (float) $qc->jumlah_di_pabrik + (float) $qc->pengujian2List->sum('jumlah_di_pabrik');

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
            'jumlah_di_pabrik'            => $totalPabrik,
            'plat_nomor_truk'             => $qc->plat_nomor_truk ?: ($qc->pengujian2List->first()?->plat_nomor_truk ?? '-'),
            'sopir_nama'                  => $qc->sopir_nama ?: ($qc->pengujian2List->first()?->sopir_nama ?? '-'),
            'bebas_cemaran_st'            => (bool) $qc->bebas_cemaran_st,
            'angkut_barang_haram_st'      => (bool) $qc->angkut_barang_haram_st,
            'komentar_transportasi'       => $qc->komentar_transportasi,
            'terdaftar_lppom_st'          => (bool) $qc->terdaftar_lppom_st,
            'komentar_lppom'              => $qc->komentar_lppom,
            'ada_sertifikat_halal_st'     => (bool) $qc->ada_sertifikat_halal_st,
            'komentar_sertifikat'         => $qc->komentar_sertifikat,
            'sertifikat_halal_berlaku_st' => (bool) $qc->sertifikat_halal_berlaku_st,
            'komentar_berlaku'            => $qc->komentar_berlaku,
            'tgl_periksa'                 => $qc->tgl_periksa ? $qc->tgl_periksa->format('Y-m-d H:i') : null,
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

            $supplier = !empty($data['supplier_id']) ? \App\Models\MasterData\MstSupplier::find($data['supplier_id']) : null;
            $namaProdusen = !empty($data['nama_produsen']) ? trim($data['nama_produsen']) : ($supplier?->supplier_nm ?? null);

            $firstItem = !empty($data['items']) && is_array($data['items']) ? reset($data['items']) : null;
            $firstBarangId = $firstItem['barang_id'] ?? ($data['minyak_barang_id'] ?? ($data['plastik_barang_id'] ?? ($data['karton_barang_id'] ?? ($data['bp_barang_id'] ?? null))));
            $firstBarang = $firstBarangId ? \App\Models\MasterData\MstBarang::find($firstBarangId) : null;
            $namaRm = !empty($data['nama_jenis']) ? trim($data['nama_jenis']) : ($firstBarang?->barang_nm ?? ($kategoriBarang === 'SINGKONG' ? 'Singkong Basah Curah' : $kategoriBarang));

            // Proteksi Anti-Double Submit (Mencegah input ganda akibat tombol ditekan berkali-kali saat jaringan lambat/lag)
            $formToken = !empty($data['form_token']) ? trim($data['form_token']) : null;
            if ($formToken) {
                $cachedQcId = \Illuminate\Support\Facades\Cache::get("qc_form_token_{$formToken}");
                if ($cachedQcId) {
                    $existingQc = DatQcInboundHdr::find($cachedQcId);
                    if ($existingQc) {
                        return $existingQc;
                    }
                }
            }

            // Fallback duplikasi: jika data yang sama persis baru saja di-submit oleh user dalam 10 detik terakhir
            $tahapUji = !empty($data['tahap_uji']) ? strtoupper(trim($data['tahap_uji'])) : 'PENGUJIAN_1';
            $recentDuplicate = DatQcInboundHdr::where('deleted_st', false)
                ->where('supplier_id', (int) $data['supplier_id'])
                ->where('gudang_id', (int) $data['gudang_id'])
                ->where('kategori_barang', $kategoriBarang)
                ->where('tahap_uji', $tahapUji)
                ->where('po_id', !empty($data['po_id']) ? (int)$data['po_id'] : null)
                ->where('created_by', $user->id ?? null)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->latest('qc_id')
                ->first();

            if ($recentDuplicate) {
                return $recentDuplicate;
            }

            $header = DatQcInboundHdr::create([
                'qc_no'                       => $qcNo,
                'po_id'                       => !empty($data['po_id']) ? (int)$data['po_id'] : null,
                'supplier_id'                 => (int) $data['supplier_id'],
                'gudang_id'                   => (int) $data['gudang_id'],
                'kategori_barang'             => $kategoriBarang,
                'tahap_uji'                   => !empty($data['tahap_uji']) ? strtoupper(trim($data['tahap_uji'])) : 'PENGUJIAN_1',
                'posisi_bak'                  => !empty($data['posisi_bak']) ? strtoupper(trim($data['posisi_bak'])) : null,
                'parent_qc_id'                => !empty($data['parent_qc_id']) ? (int)$data['parent_qc_id'] : null,
                'batch_no'                    => !empty($data['batch_no']) ? trim($data['batch_no']) : null,
                'nama_jenis'                  => $namaRm,
                'negara_produsen'             => !empty($data['negara_produsen']) ? trim($data['negara_produsen']) : 'Indonesia',
                'nama_produsen'               => $namaProdusen,
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

            if ($formToken) {
                \Illuminate\Support\Facades\Cache::put("qc_form_token_{$formToken}", $header->qc_id, 120);
            }

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

                // Cek apakah user eksplisit memilih KESIMPULAN: TOLAK atau hasil tes rasa di depan PAHIT
                $explicitKeputusan = $row['keputusan_qc'] ?? ($data['kesimpulan_qc'] ?? null);
                $isPahit = ($row['fryer_rasa'] ?? '') === 'PAHIT';

                if ($explicitKeputusan === 'TOLAK' || $explicitKeputusan === 'REJECT_TOTAL' || $isPahit) {
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

                    $grade = !empty($row['grade_cd']) ? $row['grade_cd'] : (!empty($data['grade_cd']) ? $data['grade_cd'] : 'A');

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
     * Menyimpan dokumen Pengujian II (Uji Masuk Lantai Produksi / Batch Penggorengan)
     */
    public function storePengujian2(array $data, User $user): DatQcInboundHdr
    {
        return DB::transaction(function () use ($data, $user) {
            $tglPeriksa = !empty($data['tgl_periksa']) ? $data['tgl_periksa'] : now();
            $qcNo = $this->codeGenerator->generateQcNo(date('Y-m-d', strtotime($tglPeriksa)));

            $petugasQc = !empty($data['petugas_qc_nama']) 
                ? trim($data['petugas_qc_nama']) 
                : ($user->name ?? 'Petugas QC');

            $parentQcId = !empty($data['parent_qc_id']) ? (int) $data['parent_qc_id'] : null;
            $parentQc   = $parentQcId ? DatQcInboundHdr::with(['supplier', 'gudang', 'po', 'details'])->find($parentQcId) : null;

            $supplierId = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : ($parentQc?->supplier_id);
            $gudangId   = !empty($data['gudang_id']) ? (int) $data['gudang_id'] : ($parentQc?->gudang_id);
            $poId       = !empty($data['po_id']) ? (int) $data['po_id'] : ($parentQc?->po_id);
            $batchNo    = !empty($data['batch_no']) ? trim($data['batch_no']) : ($parentQc?->batch_no ?? 'BATCH-' . date('Ymd'));

            // Proteksi Anti-Double Submit Pengujian II (Network lag / Multi-tap)
            $formToken = !empty($data['form_token']) ? trim($data['form_token']) : null;
            if ($formToken) {
                $cachedQcId = \Illuminate\Support\Facades\Cache::get("qc_form_token_{$formToken}");
                if ($cachedQcId) {
                    $existingQc = DatQcInboundHdr::find($cachedQcId);
                    if ($existingQc) {
                        return $existingQc;
                    }
                }
            }

            // Fallback recent duplicate: batch yang sama di-submit user dalam 10 detik terakhir
            $recentDuplicate = DatQcInboundHdr::where('deleted_st', false)
                ->where('tahap_uji', 'PENGUJIAN_2')
                ->where('batch_no', $batchNo)
                ->where('created_by', $user->id ?? null)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->latest()
                ->first();

            if ($recentDuplicate) {
                return $recentDuplicate;
            }

            $fryerRasa       = $data['fryer_rasa'] ?? 'TIDAK_PAHIT';
            $fryerTekstur    = $data['fryer_tekstur'] ?? 'RENYAH';
            $fryerPenampakan = $data['fryer_penampakan'] ?? 'KUNING_CERAH';
            $isPahit         = ($fryerRasa === 'PAHIT');

            $inputGrade      = !empty($data['grade_cd']) ? strtoupper(trim($data['grade_cd'])) : 'A';
            $grade           = $isPahit ? 'REJECT' : $inputGrade;

            // Tonase Uji (Default 7.000 kg / 7 Ton atau sesuai input timbangan)
            $grossTonase = !empty($data['qty_timbang_gross']) 
                ? (float) $data['qty_timbang_gross'] 
                : (!empty($data['jumlah_sample_kg']) && (float)$data['jumlah_sample_kg'] > 50 ? (float)$data['jumlah_sample_kg'] : 7000.0);

            $sampleKg = !empty($data['jumlah_sample_kg']) ? (float) $data['jumlah_sample_kg'] : 5.0;
            $liniProduksi = !empty($data['lini_produksi']) ? trim($data['lini_produksi']) : 'Lini Penggorengan';

            $refraksi = isset($data['refraksi_persen']) ? (float)$data['refraksi_persen'] : 0;
            $rejectKg = isset($data['qty_reject']) ? (float)$data['qty_reject'] : ($isPahit ? $grossTonase : 0);
            $qtyRefraksi = round(($grossTonase * $refraksi) / 100, 4);
            $nettoLolos = $isPahit ? 0 : max(0, round($grossTonase - $rejectKg - $qtyRefraksi, 4));

            $explicitKeputusan = $data['keputusan_qc'] ?? null;
            if ($isPahit || $explicitKeputusan === 'TOLAK' || $explicitKeputusan === 'REJECT_TOTAL') {
                $keputusan = 'REJECT_TOTAL';
                $grade = 'REJECT';
                $statusQc = 'DITOLAK_TOTAL';
            } elseif ($rejectKg > 0) {
                $keputusan = 'REJECT_PARTIAL';
                $statusQc = 'DITERIMA_GUDANG';
            } elseif ($refraksi > 0) {
                $keputusan = 'PASSED_REFRAKSI';
                $statusQc = 'DITERIMA_GUDANG';
            } else {
                $keputusan = 'PASSED';
                $statusQc = 'DITERIMA_GUDANG';
            }

            // Catatan bahan penolong (perenyah & minyak)
            $statusPerenyah = $data['status_perenyah'] ?? 'BELUM_DILARUTKAN';
            $perenyahTerbuangKg = !empty($data['perenyah_terbuang_kg']) ? (float) $data['perenyah_terbuang_kg'] : 0;
            $kondisiMinyak = $data['kondisi_minyak'] ?? 'NORMAL';
            
            $catatanTambahan = [];
            $catatanTambahan[] = "🍟 [PENGUJIAN II] Lini: {$liniProduksi}";
            $catatanTambahan[] = "Tonase Uji: {$grossTonase} kg | Lolos: {$nettoLolos} kg | Reject: {$rejectKg} kg | Grade: {$grade}";
            $catatanTambahan[] = "Uji Rasa: " . ($isPahit ? 'PAHIT (BAHAYA)' : 'GURIH/TIDAK PAHIT') . " | Tekstur: {$fryerTekstur} | Warna: {$fryerPenampakan}";
            
            if ($statusPerenyah === 'SUDAH_DILARUTKAN') {
                $catatanTambahan[] = "Status Perenyah: SUDAH DILARUTKAN DI BAK (Air terkontaminasi pahit, est. terbuang {$perenyahTerbuangKg} kg)";
            } elseif ($statusPerenyah === 'BELUM_DILARUTKAN') {
                $catatanTambahan[] = "Status Perenyah: BELUM DILARUTKAN (100% Utuh / Masih di Sak)";
            } else {
                $catatanTambahan[] = "Status Perenyah: TANPA PERENYAH (Tes Sampel Cepat)";
            }

            if ($kondisiMinyak === 'TERKONTAMINASI') {
                $catatanTambahan[] = "Kondisi Minyak: TERKONTAMINASI BAU/GETAH (Perlu Kuras/Ganti Minyak Wajan)";
            }

            if (!empty($data['catatan_umum'])) {
                $catatanTambahan[] = "Catatan QC: " . trim($data['catatan_umum']);
            }

            $catatanFinal = implode("\n", $catatanTambahan);

            $header = DatQcInboundHdr::create([
                'qc_no'                       => $qcNo,
                'po_id'                       => $poId,
                'supplier_id'                 => $supplierId ?: 1,
                'gudang_id'                   => $gudangId ?: 1,
                'kategori_barang'             => 'SINGKONG',
                'tahap_uji'                   => 'PENGUJIAN_2',
                'posisi_bak'                  => !empty($data['posisi_bak']) ? strtoupper(trim($data['posisi_bak'])) : null,
                'parent_qc_id'                => $parentQcId,
                'batch_no'                    => $batchNo,
                'nama_jenis'                  => "Singkong Uji Produksi",
                'negara_produsen'             => 'Indonesia',
                'nama_produsen'               => $parentQc?->nama_produsen ?? $parentQc?->supplier?->supplier_nm,
                'lokasi_panen'                => $parentQc?->lokasi_panen,
                'umur_singkong_bln'           => $parentQc?->umur_singkong_bln,
                'tgl_panen'                   => $parentQc?->tgl_panen,
                'jumlah_sample_kg'            => $sampleKg,
                'jumlah_sample_pcs'           => null,
                'jumlah_sample_gr'            => $sampleKg * 1000,
                'surat_jalan_supplier'        => $parentQc?->surat_jalan_supplier,
                'nomor_do'                    => $parentQc?->nomor_do,
                'jumlah_surat_jalan'          => $parentQc?->jumlah_surat_jalan,
                'jumlah_di_pabrik'            => $grossTonase,
                'plat_nomor_truk'             => $parentQc?->plat_nomor_truk,
                'sopir_nama'                  => $parentQc?->sopir_nama,
                'bebas_cemaran_st'            => true,
                'angkut_barang_haram_st'      => false,
                'komentar_transportasi'       => "Pemeriksaan Lanjutan Bak {$posisiBak} (Uji Wajan)",
                'terdaftar_lppom_st'          => true,
                'ada_sertifikat_halal_st'     => true,
                'sertifikat_halal_berlaku_st' => true,
                'tgl_periksa'                 => $tglPeriksa,
                'petugas_qc_nama'             => $petugasQc,
                'qc_supervisor_nama'          => !empty($data['qc_supervisor_nama']) ? trim($data['qc_supervisor_nama']) : ($parentQc?->qc_supervisor_nama ?? 'Kepala Produksi / Direktur'),
                'status_qc'                   => $statusQc,
                'status_uji_goreng'           => 'SELESAI',
                'tgl_uji_goreng'              => now(),
                'petugas_uji_goreng'          => $petugasQc,
                'catatan_umum'                => $catatanFinal,
            ]);

            if ($formToken) {
                \Illuminate\Support\Facades\Cache::put("qc_form_token_{$formToken}", $header->qc_id, 120);
            }

            // Cari ID Barang Singkong
            $barangId = !empty($data['barang_id']) ? (int) $data['barang_id'] : ($parentQc?->details?->first()?->barang_id);
            if (!$barangId) {
                $barangSingkong = \App\Models\MasterData\MstBarang::where('barang_nm', 'ilike', '%singkong%')
                    ->orWhere('barang_cd', 'ilike', '%SK%')
                    ->first();
                $barangId = $barangSingkong?->barang_id ?? 1;
            }

            $kondisiSegar      = isset($data['kondisi_segar']) ? (bool)$data['kondisi_segar'] : true;
            $kondisiBusuk      = !empty($data['kondisi_busuk']);
            $kondisiLayu       = !empty($data['kondisi_layu']);
            $kondisiBerjamur   = !empty($data['kondisi_berjamur']);
            $kondisiBasah      = !empty($data['kondisi_basah']);
            $kondisiLembek     = !empty($data['kondisi_lembek']);
            $kondisiTerkelupas = !empty($data['kondisi_terkelupas']);

            $dKurang4 = isset($data['diameter_kurang_4cm_persen']) ? (float)$data['diameter_kurang_4cm_persen'] : ($parentQc?->details?->first()?->diameter_kurang_4cm_persen ?? 0);
            $dLebih4  = isset($data['diameter_lebih_4cm_persen']) ? (float)$data['diameter_lebih_4cm_persen'] : ($parentQc?->details?->first()?->diameter_lebih_4cm_persen ?? 100);

            $defectBreakage  = isset($data['defect_breakage_persen']) ? (float)$data['defect_breakage_persen'] : 0;
            $defectCluster   = isset($data['defect_cluster_persen']) ? (float)$data['defect_cluster_persen'] : 0;
            $defectFoldover  = isset($data['defect_foldover_persen']) ? (float)$data['defect_foldover_persen'] : 0;
            $defectOilsoaked = isset($data['defect_oilsoaked_persen']) ? (float)$data['defect_oilsoaked_persen'] : 0;
            $defectGambos    = isset($data['defect_gambos_persen']) ? (float)$data['defect_gambos_persen'] : 0;

            DatQcInboundDtl::create([
                'qc_id'                      => $header->qc_id,
                'podtl_id'                   => $parentQc?->details?->first()?->podtl_id,
                'barang_id'                  => $barangId,
                'status_raw_material'        => $isPahit ? 'REJECT' : 'OK',
                'isi_kering'                 => true,
                'diameter_kurang_4cm_persen' => $dKurang4,
                'diameter_lebih_4cm_persen'  => $dLebih4,
                'kondisi_segar'              => $kondisiSegar,
                'kondisi_busuk'              => $kondisiBusuk,
                'kondisi_layu'               => $kondisiLayu,
                'kondisi_berjamur'           => $kondisiBerjamur,
                'kondisi_basah'              => $kondisiBasah,
                'kondisi_lembek'             => $kondisiLembek,
                'kondisi_terkelupas'         => $kondisiTerkelupas,
                'defect_breakage_persen'     => $defectBreakage,
                'defect_cluster_persen'      => $defectCluster,
                'defect_foldover_persen'     => $defectFoldover,
                'defect_oilsoaked_persen'    => $defectOilsoaked,
                'defect_gambos_persen'       => $defectGambos,
                'fryer_rasa'                 => $fryerRasa,
                'fryer_tekstur'              => $fryerTekstur,
                'fryer_penampakan'           => $fryerPenampakan,
                'qty_timbang_gross'          => $grossTonase,
                'kadar_air_persen'           => 0,
                'refraksi_persen'            => $refraksi,
                'qty_refraksi'               => $qtyRefraksi,
                'qty_reject'                 => $rejectKg,
                'qty_netto_lolos'            => $nettoLolos,
                'grade_cd'                   => $grade,
                'kondisi_fisik'              => $isPahit ? 'PAHIT' : ($kondisiBusuk ? 'BUSUK' : 'NORMAL'),
                'keputusan_qc'               => $keputusan,
                'catatan_dtl'                => "Pengujian II ({$posisiBak}): {$liniProduksi} | Rasa: {$fryerRasa} | Grade: {$grade}",
            ]);

            return $header;
        });
    }

    /**
     * Sinkronisasi & Rekonsiliasi Otomatis Tiket QC dengan Penerimaan Barang (GRN):
     * - Memperbarui kuantitas diterima (qty_netto_lolos) & ditolak (qty_reject) per baris QC detail.
     * - Jika Grade B / suatu baris diambil sebagian: mencatat sisa reject & mengubah keputusan_qc ke 'REJECT_PARTIAL'.
     * - Jika Grade B / suatu baris tidak diambil (dihapus dari form GRN atau terima_qty = 0):
     *   mencatat seluruh sisa sebagai reject & mengubah keputusan_qc ke 'TOLAK_TOTAL'.
     * - Menentukan status akhir masing-masing tiket (Uji 1 & Uji 2):
     *   Jika kuantitas lolos tiket = 0 -> status_qc: 'DITOLAK_TOTAL'
     *   Jika ada kuantitas lolos -> status_qc: 'DITERIMA_GUDANG'
     */
    public function syncFromTerimaBarang(int $qcId, DatTerimaHdr $terima, array $submittedItems): void
    {
        $qc = DatQcInboundHdr::with(['details', 'pengujian2List.details'])->find($qcId);
        if (!$qc) {
            return;
        }

        // Jika $qc adalah tiket anak (Pengujian 2), arahkan ke induk agar seluruh siklus armada (Uji 1 & Uji 2) terkoreksi sinkron
        if ($qc->parent_qc_id) {
            $parent = DatQcInboundHdr::with(['details', 'pengujian2List.details'])->find($qc->parent_qc_id);
            if ($parent) {
                $qc = $parent;
            }
        }

        // Petakan item yang dikirim dari form penerimaan berdasarkan qcdtl_id
        $submittedByQcDtl = [];
        foreach ($submittedItems as $item) {
            $qcdtlId = !empty($item['qcdtl_id']) ? (int) $item['qcdtl_id'] : null;
            if ($qcdtlId) {
                $submittedByQcDtl[$qcdtlId] = $item;
            }
        }

        // Kumpulkan semua tiket yang terlibat (Uji 1 induk + semua Uji 2 anak)
        $allTickets = collect([$qc])->merge($qc->pengujian2List);

        foreach ($allTickets as $ticket) {
            $ticketNettoTotal = 0;
            $ticketRejectTotal = 0;

            foreach ($ticket->details as $dtl) {
                if (isset($submittedByQcDtl[$dtl->qcdtl_id])) {
                    // Item ada dalam input penerimaan barang
                    $sub = $submittedByQcDtl[$dtl->qcdtl_id];
                    $tQty = max(0, (float) ($sub['terima_qty'] ?? 0));
                    $rQty = max(0, (float) ($sub['reject_qty'] ?? 0));

                    // Jika rQty tidak diinput manual tetapi tQty < (gross - refraksi), hitung selisihnya otomatis sebagai reject
                    $grossEst = (float) $dtl->qty_timbang_gross;
                    $refraksiEst = (float) $dtl->qty_refraksi;
                    $maxPossibleNetto = max(0, $grossEst - $refraksiEst);

                    if ($rQty <= 0 && $maxPossibleNetto > 0 && $tQty < $maxPossibleNetto) {
                        $rQty = round($maxPossibleNetto - $tQty, 4);
                    }

                    $dtl->qty_netto_lolos = $tQty;
                    $dtl->qty_reject = $rQty;

                    if ($tQty <= 0) {
                        $dtl->keputusan_qc = 'TOLAK_TOTAL';
                        $catatanGdg = "Ditolak total oleh gudang ({$rQty} KG reject). Tidak masuk stok pabrik.";
                    } elseif ($rQty > 0) {
                        $dtl->keputusan_qc = 'REJECT_PARTIAL';
                        $catatanGdg = "Diterima sebagian: {$tQty} KG, ditolak: {$rQty} KG (Grade {$dtl->grade_cd}).";
                    } else {
                        $dtl->keputusan_qc = 'PASSED';
                        $catatanGdg = "Diterima penuh ke gudang: {$tQty} KG.";
                    }

                    // Bersihkan catatan lama dari rekonsiliasi sebelumnya agar tidak menumpuk
                    $baseCatatan = preg_replace('/\s*\|\s*(Diterima|Ditolak).*$/', '', $dtl->catatan_dtl ?? '');
                    $dtl->catatan_dtl = trim(($baseCatatan ? $baseCatatan . ' | ' : '') . $catatanGdg);
                    $dtl->save();

                    $ticketNettoTotal += $tQty;
                    $ticketRejectTotal += $rQty;
                } else {
                    // Item QC ini TIDAK disertakan dalam penerimaan barang (dihapus dari form GRN oleh petugas)
                    // Maka seluruh muatan baris ini otomatis DITOLAK TOTAL
                    $rejectedQty = (float) $dtl->qty_timbang_gross > 0 
                        ? (float) $dtl->qty_timbang_gross 
                        : (float) $dtl->qty_netto_lolos;

                    $dtl->qty_netto_lolos = 0;
                    $dtl->qty_reject = $rejectedQty;
                    $dtl->keputusan_qc = 'TOLAK_TOTAL';

                    $baseCatatan = preg_replace('/\s*\|\s*(Diterima|Ditolak).*$/', '', $dtl->catatan_dtl ?? '');
                    $catatanGdg = "Ditolak total oleh gudang (baris dihapus dari penerimaan GRN).";
                    $dtl->catatan_dtl = trim(($baseCatatan ? $baseCatatan . ' | ' : '') . $catatanGdg);
                    $dtl->save();

                    $ticketRejectTotal += $rejectedQty;
                }
            }

            // Tentukan status akhir tiket header ini
            if ($ticketNettoTotal <= 0) {
                $ticket->status_qc = 'DITOLAK_TOTAL';
                $ticket->jumlah_di_pabrik = 0;
            } elseif ($ticketRejectTotal > 0) {
                $ticket->status_qc = 'DITERIMA_PARSIAL';
                $ticket->jumlah_di_pabrik = $ticketNettoTotal;
            } else {
                $ticket->status_qc = 'DITERIMA_GUDANG';
                $ticket->jumlah_di_pabrik = $ticketNettoTotal;
            }
            $ticket->save();
        }

        // Evaluasi ulang tiket induk jika anak mengalami penolakan (parsial atau total)
        if ($qc->pengujian2List->isNotEmpty()) {
            $hasChildReject = $qc->pengujian2List->contains(fn($c) => in_array($c->status_qc, ['DITOLAK_TOTAL', 'DITERIMA_PARSIAL']));
            if ($hasChildReject && $qc->status_qc === 'DITERIMA_GUDANG') {
                $qc->status_qc = 'DITERIMA_PARSIAL';
                $qc->save();
            }
        }
    }

    /**
     * Menandai tiket QC telah diproses dan diterima oleh Admin Gudang (Fallback / Direct)
     */
    public function markAsProcessed(int $qcId): void
    {
        $qc = DatQcInboundHdr::find($qcId);
        if ($qc) {
            $qc->status_qc = 'DITERIMA_GUDANG';
            $qc->save();

            // Tandai juga tiket pengujian 2 (anak) jika ada
            DatQcInboundHdr::where('parent_qc_id', $qc->qc_id)->update(['status_qc' => 'DITERIMA_GUDANG']);

            // Jika tiket ini adalah anak, tandai induknya
            if ($qc->parent_qc_id) {
                DatQcInboundHdr::where('qc_id', $qc->parent_qc_id)->update(['status_qc' => 'DITERIMA_GUDANG']);
            }
        }
    }

    /**
     * Mengembalikan status tiket QC ke SIAP_GUDANG saat dokumen penerimaan dibatalkan/dihapus
     */
    public function revertProcessed(int $qcId): void
    {
        $qc = DatQcInboundHdr::with(['details', 'pengujian2List.details'])->find($qcId);
        if (!$qc) {
            return;
        }

        if ($qc->parent_qc_id) {
            $parent = DatQcInboundHdr::with(['details', 'pengujian2List.details'])->find($qc->parent_qc_id);
            if ($parent) {
                $qc = $parent;
            }
        }

        $allTickets = collect([$qc])->merge($qc->pengujian2List);

        foreach ($allTickets as $ticket) {
            $ticket->status_qc = 'SIAP_GUDANG';
            foreach ($ticket->details as $dtl) {
                // Kembalikan netto awal (gross - refraksi)
                $origNetto = max(0, (float) $dtl->qty_timbang_gross - (float) $dtl->qty_refraksi);
                $dtl->qty_netto_lolos = $origNetto;
                $dtl->qty_reject = 0;
                $dtl->keputusan_qc = ($dtl->qty_refraksi > 0) ? 'PASSED_REFRAKSI' : 'PASSED';
                $dtl->catatan_dtl = preg_replace('/\s*\|\s*(Diterima|Ditolak).*$/', '', $dtl->catatan_dtl ?? '');
                $dtl->save();
            }
            $ticket->jumlah_di_pabrik = $ticket->details->sum('qty_netto_lolos');
            $ticket->save();
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
            if (!empty($items)) {
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
            } else {
                // Fallback: Jika data dikirim langsung tanpa pembungkus items[]
                $targetDtl = !empty($data['qcdtl_id'])
                    ? $qc->details->firstWhere('qcdtl_id', (int)$data['qcdtl_id'])
                    : $qc->details->first();

                if ($targetDtl) {
                    $targetDtl->update([
                        'fryer_rasa'             => $data['fryer_rasa'] ?? 'TIDAK_PAHIT',
                        'fryer_tekstur'          => $data['fryer_tekstur'] ?? 'RENYAH',
                        'fryer_penampakan'       => $data['fryer_penampakan'] ?? 'TIDAK_OILSOAKED',
                        'defect_breakage_persen' => (float)($data['defect_breakage_persen'] ?? 0),
                        'defect_cluster_persen'  => (float)($data['defect_cluster_persen'] ?? 0),
                        'defect_foldover_persen' => (float)($data['defect_foldover_persen'] ?? 0),
                        'defect_oilsoaked_persen'=> (float)($data['defect_oilsoaked_persen'] ?? 0),
                        'defect_gambos_persen'   => (float)($data['defect_gambos_persen'] ?? 0),
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
            if ($terimaLinked && !$isSuperAdmin && !$user->isGudang()) {
                throw new Exception("Tiket QC #{$qc->qc_no} sudah diproses ke Penerimaan Barang (GRN #{$terimaLinked->terima_no}). Anda tidak memiliki wewenang mengedit data yang sudah masuk gudang. Silakan hubungi Admin Gudang atau Super Administrator.");
            }

            $kategoriBarang = !empty($data['kategori_barang']) ? strtoupper(trim($data['kategori_barang'])) : ($qc->kategori_barang ?? 'SINGKONG');

            $supplier = !empty($data['supplier_id']) ? \App\Models\MasterData\MstSupplier::find($data['supplier_id']) : null;
            $namaProdusen = !empty($data['nama_produsen']) ? trim($data['nama_produsen']) : ($supplier?->supplier_nm ?? $qc->nama_produsen);

            $firstItem = !empty($data['items']) && is_array($data['items']) ? reset($data['items']) : null;
            $firstBarangId = $firstItem['barang_id'] ?? ($data['minyak_barang_id'] ?? ($data['plastik_barang_id'] ?? ($data['karton_barang_id'] ?? ($data['bp_barang_id'] ?? null))));
            $firstBarang = $firstBarangId ? \App\Models\MasterData\MstBarang::find($firstBarangId) : null;
            $namaRm = !empty($data['nama_jenis']) ? trim($data['nama_jenis']) : ($firstBarang?->barang_nm ?? $qc->nama_jenis);

            // 1. Update Header
            $qc->fill([
                'po_id'                       => !empty($data['po_id']) ? (int)$data['po_id'] : $qc->po_id,
                'supplier_id'                 => !empty($data['supplier_id']) ? (int)$data['supplier_id'] : $qc->supplier_id,
                'gudang_id'                   => !empty($data['gudang_id']) ? (int)$data['gudang_id'] : $qc->gudang_id,
                'kategori_barang'             => $kategoriBarang,
                'tahap_uji'                   => !empty($data['tahap_uji']) ? strtoupper(trim($data['tahap_uji'])) : ($qc->tahap_uji ?? 'PENGUJIAN_1'),
                'posisi_bak'                  => !empty($data['posisi_bak']) ? strtoupper(trim($data['posisi_bak'])) : $qc->posisi_bak,
                'parent_qc_id'                => array_key_exists('parent_qc_id', $data) ? (!empty($data['parent_qc_id']) ? (int)$data['parent_qc_id'] : null) : $qc->parent_qc_id,
                'batch_no'                    => array_key_exists('batch_no', $data) ? (!empty($data['batch_no']) ? trim($data['batch_no']) : null) : $qc->batch_no,
                'nama_jenis'                  => $namaRm,
                'negara_produsen'             => !empty($data['negara_produsen']) ? trim($data['negara_produsen']) : $qc->negara_produsen,
                'nama_produsen'               => $namaProdusen,
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

            if (($isSuperAdmin || $user->isGudang()) && $terimaLinked) {
                $qc->catatan_umum .= " [REVISI oleh {$user->name} pada " . now()->format('d/m/Y H:i') . "]";
            }
            $qc->save();

            // 2. Update Details
            $items = $data['items'] ?? [];
            if (!empty($items)) {
                $allRejected = true;
                $deltaQtyPerBarang = [];
                $processedDetailIds = [];

                foreach ($items as $idx => $row) {
                    $barangId = (int)$row['barang_id'];
                    $gross = (float) ($row['qty_timbang_gross'] ?? ($data['jumlah_di_pabrik'] ?? ($data['jumlah_surat_jalan'] ?? 0)));
                    $kadarAir = (float) ($row['kadar_air_persen'] ?? 0);
                    $refraksiPersen = (float) ($row['refraksi_persen'] ?? 0);
                    $rejectQty = (float) ($row['qty_reject'] ?? 0);

                    $explicitKeputusan = $row['keputusan_qc'] ?? ($data['kesimpulan_qc'] ?? null);
                    $isPahit = ($row['fryer_rasa'] ?? '') === 'PAHIT';
                    if ($explicitKeputusan === 'TOLAK' || $explicitKeputusan === 'REJECT_TOTAL' || $isPahit) {
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
                    if (!$existingDtl && !empty($barangId)) {
                        $existingDtl = $qc->details->firstWhere('barang_id', $barangId);
                    }
                    if (!$existingDtl) {
                        $existingDtl = $qc->details->values()->get($idx) ?? $qc->details->first();
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
                        'fryer_rasa'                 => $row['fryer_rasa'] ?? ($existingDtl?->fryer_rasa ?? 'TIDAK_PAHIT'),
                        'fryer_tekstur'              => $row['fryer_tekstur'] ?? ($existingDtl?->fryer_tekstur ?? 'RENYAH'),
                        'fryer_penampakan'           => $row['fryer_penampakan'] ?? ($existingDtl?->fryer_penampakan ?? 'TIDAK_OILSOAKED'),
                        'defect_breakage_persen'     => isset($row['defect_breakage_persen']) ? (float)$row['defect_breakage_persen'] : ($existingDtl?->defect_breakage_persen ?? 0),
                        'defect_cluster_persen'      => isset($row['defect_cluster_persen']) ? (float)$row['defect_cluster_persen'] : ($existingDtl?->defect_cluster_persen ?? 0),
                        'defect_foldover_persen'     => isset($row['defect_foldover_persen']) ? (float)$row['defect_foldover_persen'] : ($existingDtl?->defect_foldover_persen ?? 0),
                        'defect_oilsoaked_persen'    => isset($row['defect_oilsoaked_persen']) ? (float)$row['defect_oilsoaked_persen'] : ($existingDtl?->defect_oilsoaked_persen ?? 0),
                        'defect_gambos_persen'       => isset($row['defect_gambos_persen']) ? (float)$row['defect_gambos_persen'] : ($existingDtl?->defect_gambos_persen ?? 0),
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
                        $processedDetailIds[] = $existingDtl->qcdtl_id;
                    } else {
                        $payloadDtl['qc_id'] = $qc->qc_id;
                        $newDtl = DatQcInboundDtl::create($payloadDtl);
                        $processedDetailIds[] = $newDtl->qcdtl_id;
                    }
                }

                // Bersihkan detail lama / duplikat yang tidak lagi ada di formulir (misal duplikasi hasil edit terdahulu)
                if (!empty($processedDetailIds)) {
                    DatQcInboundDtl::where('qc_id', $qc->qc_id)
                        ->whereNotIn('qcdtl_id', $processedDetailIds)
                        ->delete();
                }

                if ($allRejected) {
                    $qc->status_qc = 'DITOLAK_TOTAL';
                    $qc->save();
                } elseif ($qc->status_qc === 'DITOLAK_TOTAL') {
                    $qc->status_qc = $terimaLinked ? 'DITERIMA_GUDANG' : 'SIAP_GUDANG';
                    $qc->save();
                }

                // 3. AUTO-CASCADE SINKRONISASI KE PENERIMAAN GUDANG (GRN) & STOK BATCH
                if (($isSuperAdmin || $user->isGudang()) && $terimaLinked) {
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
