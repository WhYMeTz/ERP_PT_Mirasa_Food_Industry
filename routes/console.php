<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('produksi:sync-material-qty', function (\App\Services\Produksi\ProduksiService $service) {
    $this->info('Memulai sinkronisasi kuantitas bahan kemasan & bumbu dari BPPB Gudang...');
    $allProd = \App\Models\Produksi\DatProduksiHdr::where('deleted_st', false)->orderBy('produksi_id')->get();
    $updatedCount = 0;

    \Illuminate\Support\Facades\DB::transaction(function () use ($allProd, $service, &$updatedCount) {
        foreach ($allProd as $prod) {
            $updates = [];

            // 1. Dari BPPB Utama (pakai_id)
            if ($prod->pakai_id) {
                $summary = $service->extractPakaiSummary($prod->pakai_id);
                $fields = [
                    'bumbu_qty', 'karton_baru_qty', 'karton_bekas_qty',
                    'plastik_hd_qty', 'lakban_besar_qty', 'lakban_kecil_qty',
                    'tali_rafia_qty', 'sarung_tangan_plastik_qty', 'sarung_tangan_kain_qty'
                ];
                foreach ($fields as $f) {
                    if ((float)($prod->$f ?? 0) == 0 && (float)($summary[$f] ?? 0) > 0) {
                        $updates[$f] = (float)$summary[$f];
                    }
                }
            }

            // 2. Dari Dokumen Pengeluaran Kemasan (pengeluaranKemasan)
            $kemasans = $prod->pengeluaranKemasan()->with('details.barang')->get();
            foreach ($kemasans as $km) {
                foreach ($km->details as $dtl) {
                    $b = $dtl->barang;
                    if (!$b) continue;
                    $nm = strtoupper($b->barang_nm);
                    $qty = (float)$dtl->qty_keluar;

                    if (str_contains($nm, 'KARTON') || str_contains($nm, 'BOX') || str_contains($nm, 'DUS')) {
                        if (str_contains($nm, 'BEKAS')) {
                            if ((float)($prod->karton_bekas_qty ?? 0) == 0 && !isset($updates['karton_bekas_qty'])) {
                                $updates['karton_bekas_qty'] = $qty;
                            }
                        } else {
                            if ((float)($prod->karton_baru_qty ?? 0) == 0 && !isset($updates['karton_baru_qty'])) {
                                $updates['karton_baru_qty'] = $qty;
                            }
                        }
                    } elseif (str_contains($nm, 'PLASTIK') || str_contains($nm, 'HD') || str_contains($nm, 'INNER')) {
                        if ((float)($prod->plastik_hd_qty ?? 0) == 0 && !isset($updates['plastik_hd_qty'])) {
                            $updates['plastik_hd_qty'] = $qty;
                        }
                    } elseif (str_contains($nm, 'LAKBAN')) {
                        if (str_contains($nm, 'KECIL')) {
                            if ((float)($prod->lakban_kecil_qty ?? 0) == 0 && !isset($updates['lakban_kecil_qty'])) {
                                $updates['lakban_kecil_qty'] = $qty;
                            }
                        } else {
                            if ((float)($prod->lakban_besar_qty ?? 0) == 0 && !isset($updates['lakban_besar_qty'])) {
                                $updates['lakban_besar_qty'] = $qty;
                            }
                        }
                    } elseif (str_contains($nm, 'TALI') || str_contains($nm, 'RAFIA')) {
                        if ((float)($prod->tali_rafia_qty ?? 0) == 0 && !isset($updates['tali_rafia_qty'])) {
                            $updates['tali_rafia_qty'] = $qty;
                        }
                    }
                }
            }

            if (!empty($updates)) {
                $prod->update($updates);
                $this->line("✔ Berhasil memperbarui Produksi [{$prod->produksi_no}] ID {$prod->produksi_id}: " . json_encode($updates));
                $updatedCount++;
            }
        }
    });

    $this->info("Selesai! Sebanyak {$updatedCount} catatan produksi berhasil disinkronkan.");
})->purpose('Sinkronisasi kuantitas bahan kemasan dan bumbu dari BPPB gudang ke dokumen produksi lama');
