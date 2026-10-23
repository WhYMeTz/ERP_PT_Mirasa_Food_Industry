<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatJurnalDtl;
use App\Models\Keuangan\DatJurnalHdr;
use App\Models\Keuangan\MstCoa;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class JurnalService
{
    /**
     * Catat Transaksi Jurnal Umum Berpasangan (Double Entry)
     * 
     * @param string $tipeTransaksi Contoh: HUTANG_PEMBELIAN, BAYAR_HUTANG, KAS_MASUK, JURNAL_UMUM
     * @param string $tglJurnal Tanggal YYYY-MM-DD
     * @param string|null $refNo Nomor referensi dokumen (No faktur/No invoice/No BKK)
     * @param int|null $refId ID record referensi
     * @param string $keterangan Deskripsi jurnal
     * @param array $entries Array baris jurnal: [['kode_akun' => '1150', 'debet' => 1000, 'kredit' => 0, 'keterangan' => '...'], ...]
     * @param User|null $user Pengguna yang melakukan aksi
     */
    public function catatJurnal(
        string $tipeTransaksi,
        string $tglJurnal,
        ?string $refNo,
        ?int $refId,
        string $keterangan,
        array $entries,
        ?User $user = null
    ): DatJurnalHdr {
        return DB::transaction(function () use ($tipeTransaksi, $tglJurnal, $refNo, $refId, $keterangan, $entries, $user) {
            $userId = $user?->name ?? 'SYSTEM';

            $totalDebet = 0;
            $totalKredit = 0;
            $parsedLines = [];

            foreach ($entries as $line) {
                $debet = isset($line['debet']) ? (float) $line['debet'] : 0;
                $kredit = isset($line['kredit']) ? (float) $line['kredit'] : 0;

                // Abaikan jika baris 0 debet dan 0 kredit
                if (abs($debet) < 0.0001 && abs($kredit) < 0.0001) {
                    continue;
                }

                $coa = null;
                if (!empty($line['coa_id'])) {
                    $coa = MstCoa::find($line['coa_id']);
                } elseif (!empty($line['kode_akun'])) {
                    $coa = MstCoa::where('kode_akun', trim($line['kode_akun']))->first();
                }

                if (!$coa) {
                    throw new Exception("Akun COA dengan kode/ID '" . ($line['kode_akun'] ?? $line['coa_id']) . "' tidak ditemukan di master data.");
                }

                $totalDebet += $debet;
                $totalKredit += $kredit;

                $parsedLines[] = [
                    'coa'        => $coa,
                    'debet'      => $debet,
                    'kredit'     => $kredit,
                    'keterangan' => $line['keterangan'] ?? $keterangan,
                ];
            }

            // Validasi Keseimbangan (Debet harus sama dengan Kredit)
            $selisih = abs($totalDebet - $totalKredit);
            if ($selisih > 0.01) {
                throw new Exception("Jurnal tidak seimbang (Unbalanced)! Total Debet: Rp " . number_format($totalDebet, 0, ',', '.') . ", Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . " (Selisih: Rp " . number_format($selisih, 0, ',', '.') . ")");
            }

            $jurnalNo = $this->generateJurnalNo($tglJurnal);

            // 1. Simpan Header Jurnal
            $hdr = DatJurnalHdr::create([
                'jurnal_no'      => $jurnalNo,
                'tgl_jurnal'     => $tglJurnal,
                'tipe_transaksi' => $tipeTransaksi,
                'ref_no'         => $refNo,
                'ref_id'         => $refId,
                'keterangan'     => $keterangan,
                'total_debet'    => $totalDebet,
                'total_kredit'   => $totalKredit,
                'is_balanced'    => true,
                'created_by'     => $userId,
                'updated_by'     => $userId,
                'active_st'      => true,
                'deleted_st'     => false,
            ]);

            // 2. Simpan Detail & Mutasi Saldo Berjalan COA
            foreach ($parsedLines as $pl) {
                DatJurnalDtl::create([
                    'jurnal_id'  => $hdr->jurnal_id,
                    'coa_id'     => $pl['coa']->coa_id,
                    'debet'      => $pl['debet'],
                    'kredit'     => $pl['kredit'],
                    'keterangan' => $pl['keterangan'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'active_st'  => true,
                    'deleted_st' => false,
                ]);

                // Update saldo berjalan pada tabel mst_coa
                $coa = $pl['coa'];
                if ($coa->saldo_normal === 'DEBET') {
                    $coa->saldo_berjalan += ($pl['debet'] - $pl['kredit']);
                } else {
                    $coa->saldo_berjalan += ($pl['kredit'] - $pl['debet']);
                }
                $coa->save();
            }

            return $hdr;
        });
    }

    /**
     * Generator Nomor Jurnal: JRN-YYYYMM-XXXX
     */
    public function generateJurnalNo(string $tgl): string
    {
        $prefix = 'JRN-' . date('Ym', strtotime($tgl)) . '-';
        $last = DatJurnalHdr::where('jurnal_no', 'LIKE', "{$prefix}%")
            ->orderBy('jurnal_no', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->jurnal_no);
            $lastNum = (int) end($parts);
            $seq = $lastNum + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil daftar histori Jurnal Umum dengan Pagination & Filter
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15)
    {
        $query = DatJurnalHdr::with(['details.coa'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('jurnal_no', 'LIKE', "%{$search}%")
                  ->orWhere('ref_no', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['tipe_transaksi'])) {
            $query->where('tipe_transaksi', $filters['tipe_transaksi']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('tgl_jurnal', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tgl_jurnal', '<=', $filters['end_date']);
        }

        if (!empty($filters['coa_id'])) {
            $coaId = (int) $filters['coa_id'];
            $query->whereHas('details', function ($dq) use ($coaId) {
                $dq->where('coa_id', $coaId);
            });
        }

        return $query->orderBy('tgl_jurnal', 'desc')
            ->orderBy('jurnal_id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ringkasan Metrik Jurnal Umum (Debet, Kredit, Entri)
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $base = DatJurnalHdr::where('deleted_st', false);

        if (!empty($filters['start_date'])) {
            $base->whereDate('tgl_jurnal', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $base->whereDate('tgl_jurnal', '<=', $filters['end_date']);
        }
        if (!empty($filters['tipe_transaksi'])) {
            $base->where('tipe_transaksi', $filters['tipe_transaksi']);
        }

        $totalEntries = (clone $base)->count();
        $totalDebet = (float) (clone $base)->sum('total_debet');
        $totalKredit = (float) (clone $base)->sum('total_kredit');
        $countUnbalanced = (clone $base)->where('is_balanced', false)->count();

        return [
            'total_entries'    => $totalEntries,
            'total_debet'      => $totalDebet,
            'total_kredit'     => $totalKredit,
            'count_unbalanced' => $countUnbalanced,
            'is_all_balanced'  => ($countUnbalanced === 0 && abs($totalDebet - $totalKredit) < 0.01),
        ];
    }

    /**
     * Ambil detail Jurnal berdasarkan ID
     */
    public function getById(int $id): DatJurnalHdr
    {
        return DatJurnalHdr::with(['details.coa'])
            ->where('deleted_st', false)
            ->findOrFail($id);
    }
}
