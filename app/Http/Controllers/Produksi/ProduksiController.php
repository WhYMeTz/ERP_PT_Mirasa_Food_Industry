<?php

namespace App\Http\Controllers\Produksi;

use App\Exports\Produksi\HasilProduksiExport;
use App\Exports\Produksi\HasilProduksiTemplate;
use App\Exports\Produksi\RekapHppExport;
use App\Exports\Produksi\RekapHppTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Produksi\HasilProduksiImport;
use App\Imports\Produksi\RekapHppImport;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\Produksi\DatKursHarian;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstLiniProduksi;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use App\Services\Produksi\ProduksiService;
use App\Services\Produksi\TarifProduksiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProduksiController extends Controller
{
    public function __construct(
        protected ProduksiService $produksiService,
        protected CodeGeneratorService $codeGenerator,
        protected StokService $stokService,
        protected TarifProduksiService $tarifService
    ) {}

    /**
     * Halaman Hasil Barang Produksi (WIP & Finish Good) & Sisa Stok Fisik Gudang (Point 9.B).
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Backward compatibility: jika user akses tab=rekap, arahkan ke route resmi produksi.rekap
        if ($request->input('tab') === 'rekap') {
            return redirect()->route('produksi.rekap', $request->except('tab'));
        }

        // Master Gudang untuk filter dropdown
        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        $filters = [
            'gudang_id'  => $request->input('gudang_id'),
            'search'     => $request->input('search'),
            'barang_cd'  => $request->input('barang_cd'),
            'barang_nm'  => $request->input('barang_nm'),
            'batch_no'   => $request->input('batch_no'),
            'jenis_cd'   => $request->input('jenis_cd'),
            'grade_cd'   => $request->input('grade_cd'),
            'kategori'   => $request->input('kategori'),
            'tgl_dari'   => $request->input('tgl_dari'),
            'tgl_sampai' => $request->input('tgl_sampai'),
        ];
        $produksiList = $this->produksiService->getDokumenProduksiList($filters, 20);

        return view('produksi.index', compact(
            'gudangList',
            'filters',
            'produksiList'
        ));
    }

    /**
     * Halaman Khusus Buku Rekapitulasi Harga Pokok Produksi (HPP):
     * - Mode Harian: Analisis per hari dalam 1 bulan kalender (Kalender Produksi & Audit Akuntansi)
     * - Mode Bulanan: Analisis tren per bulan dalam 1 tahun fiskal (Januari s/d Desember)
     */
    public function rekap(Request $request): View
    {
        $mode = $request->input('mode') === 'bulanan' ? 'bulanan' : 'harian';
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $yearsList = [2024, 2025, 2026, 2027];
        $monthName = $monthsList[$month] ?? 'Januari';

        $report = null;
        $yearlyReport = null;

        if ($mode === 'bulanan') {
            $yearlyReport = $this->produksiService->getYearlyReport($year);
        } else {
            $report = $this->produksiService->getMonthlyReport($year, $month);
        }

        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        return view('produksi.rekap', compact(
            'mode',
            'year',
            'month',
            'monthName',
            'monthsList',
            'yearsList',
            'report',
            'yearlyReport',
            'gudangList'
        ));
    }

    /**
     * Formulir Input Hasil Produksi Harian Cepat & Cerdas.
     */
    public function create(Request $request): View
    {
        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        // Ambil daftar dokumen pemakaian bahan yang belum pernah dipakai oleh produksi
        // Khusus untuk lini produksi penggorengan / olahan curah (WIP), bukan dokumen pengemasan (Packaging)
        $pakaiList = DatPakaiHdr::with('details.barang')
            ->where('deleted_st', false)
            ->whereDoesntHave('produksi')
            ->where(function ($q) {
                $q->where('tujuan_pemakaian', 'ILIKE', '%PRODUKSI%')
                  ->orWhere('tujuan_pemakaian', 'ILIKE', '%WIP%')
                  ->orWhere('tujuan_pemakaian', 'ILIKE', '%IFM%')
                  ->orWhere('tujuan_pemakaian', 'ILIKE', '%MANUAL%');
            })
            ->where('tujuan_pemakaian', 'NOT ILIKE', '%PENGEMASAN%')
            ->where('tujuan_pemakaian', 'NOT ILIKE', '%PACKAGING%')
            ->where('tujuan_pemakaian', 'NOT ILIKE', '%PACKING%')
            ->orderBy('pakai_tgl', 'desc')
            ->limit(50)
            ->get();

        // Ambil daftar master lini produksi aktif untuk dropdown
        $liniList = MstLiniProduksi::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('lini_id', 'asc')
            ->get();

        // Ambil daftar barang hasil produksi aktif (Khusus Olahan Setengah Jadi / WIP, Ekspor & Produk Jadi)
        $barangHasilList = MstBarang::active()
            ->where(function ($q) {
                $q->whereHas('jenisBarang', fn($j) => $j->whereIn('jenis_barang_cd', ['WIP', 'FG']))
                  ->orWhere('barang_cd', 'LIKE', 'WIP%')
                  ->orWhere('barang_cd', 'LIKE', 'FG%');
            })
            ->with(['satuanDasar', 'satuanBesar', 'jenisBarang'])
            ->orderBy('barang_nm')
            ->get();

        // Standar Pengali FOH & Tarif Dinamis dari Master Data
        $fohRates = $this->tarifService->getFohRates();
        $energiTkRates = $this->tarifService->getEnergiLaborRates();
        $allTarif = $this->tarifService->getAllTarif();
        $defaultCngTarif = $energiTkRates['cng_tarif'] ?? round(14.50 * 18138.00, 2);
        $defaultCngTarifUsd = $energiTkRates['cng_tarif_usd'] ?? 14.50;
        $defaultCngKursJisdor = $energiTkRates['cng_kurs_jisdor'] ?? 18138.00;

        // Tarif Master Kemasan Karton Sirkular (Single Source of Truth dari mst_barang)
        $kartonRates = [
            'baru'    => (float) (MstBarang::where('barang_cd', 'KIF00G-BP1')->value('harga_beli_standar') ?? 8741.00),
            'puteran' => (float) (MstBarang::where('barang_cd', 'KIF00G-PUT')->value('harga_beli_standar') ?? 1748.20),
            'reject'  => (float) (MstBarang::where('barang_cd', 'KMS-KTN-REJ')->value('harga_beli_standar') ?? 0.00),
        ];

        // Nomor Dokumen Hasil Produksi Resmi Otomatis (Format Korporat: {seq}/PRD/MFI/{mm}/{yy})
        $autoProduksiNo = $this->codeGenerator->generateProduksiNo(old('produksi_tgl', date('Y-m-d')));

        return view('produksi.create', compact(
            'autoProduksiNo',
            'gudangList',
            'pakaiList',
            'liniList',
            'barangHasilList',
            'defaultCngTarif',
            'defaultCngTarifUsd',
            'defaultCngKursJisdor',
            'fohRates',
            'energiTkRates',
            'allTarif',
            'kartonRates'
        ));
    }

    /**
     * Endpoint AJAX: Ekstrak data bahan dari Dokumen Pemakaian Bahan Gudang.
     */
    public function getPakaiData(int $pakaiId): JsonResponse
    {
        try {
            $summary = $this->produksiService->extractPakaiSummary($pakaiId);
            return response()->json([
                'status' => 'success',
                'data'   => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memuat rincian pemakaian bahan: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Endpoint AJAX: Cek nomor urut karton awal yang disarankan berdasarkan tanggal & shift.
     */
    public function getNextKarton(Request $request): JsonResponse
    {
        try {
            $tgl = $request->input('tgl', date('Y-m-d'));
            $shift = $request->input('shift', 'A');
            $data = $this->produksiService->getNextKartonAwal($tgl, $shift);

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memeriksa nomor karton: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Endpoint AJAX: Tarik data jumlah tenaga kerja hadir dan total upah dari Modul Karyawan berdasarkan tanggal.
     */
    public function getKaryawanDataByDate(Request $request): JsonResponse
    {
        try {
            $tgl = $request->input('tgl', date('Y-m-d'));
            $lini = $request->input('lini');
            $data = $this->produksiService->getLaborDataFromKaryawan($tgl, $lini);

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memeriksa data karyawan: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Endpoint AJAX: Dapatkan kurs JISDOR otomatis berdasarkan tanggal produksi.
     */
    public function getKursJisdor(Request $request): JsonResponse
    {
        try {
            $isLatest = $request->boolean('latest') || $request->input('mode') === 'latest' || !$request->filled('date');

            if ($isLatest) {
                $record = DatKursHarian::active()
                    ->orderByDesc('tanggal')
                    ->first();

                if (!$record) {
                    $rate = $this->tarifService->getJisdorRate(date('Y-m-d'), true);
                    $record = DatKursHarian::active()->orderByDesc('tanggal')->first();
                } else {
                    $rate = (float) $record->kurs_tengah;
                }

                $effectiveDateCarbon = $record ? Carbon::parse($record->tanggal) : Carbon::today();
                $effectiveDate = $effectiveDateCarbon->format('d/m/Y');
                $keteranganAcuan = 'Kurs terkini Bank Indonesia';
                $tgl = $effectiveDateCarbon->format('Y-m-d');
            } else {
                $tgl = $request->input('date') ?: $request->input('tgl', date('Y-m-d'));
                $rate = $this->tarifService->getJisdorRate($tgl, true);

                $record = DatKursHarian::active()
                    ->where('tanggal', '<=', $tgl)
                    ->orderByDesc('tanggal')
                    ->first();

                $prodDateCarbon = Carbon::parse($tgl);
                $effectiveDateCarbon = $record ? Carbon::parse($record->tanggal) : $prodDateCarbon;
                $effectiveDate = $effectiveDateCarbon->format('d/m/Y');

                $keteranganAcuan = '';
                if ($prodDateCarbon->isWeekend()) {
                    $dayName = $prodDateCarbon->dayOfWeek === 6 ? 'Sabtu' : ($prodDateCarbon->dayOfWeek === 0 ? 'Minggu' : 'Hari libur');
                    $keteranganAcuan = "Kurs hari kerja terakhir BI ({$dayName} bursa BI libur)";
                } elseif ($record && !$effectiveDateCarbon->isSameDay($prodDateCarbon)) {
                    $keteranganAcuan = "Kurs hari bursa terakhir";
                }
            }

            return response()->json([
                'status'           => 'success',
                'success'          => true,
                'date'             => $tgl,
                'tanggal'          => $tgl,
                'tanggal_acuan'    => $effectiveDate,
                'keterangan_acuan' => $keteranganAcuan,
                'kurs'             => $rate,
                'rate'             => $rate,
                'kurs_formatted'   => number_format($rate, 2, ',', '.'),
                'data'             => [
                    'rate'             => $rate,
                    'kurs'             => $rate,
                    'date'             => $effectiveDate,
                    'tanggal_acuan'    => $effectiveDate,
                    'keterangan_acuan' => $keteranganAcuan,
                    'kurs_formatted'   => number_format($rate, 2, ',', '.'),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'         => 'error',
                'success'        => false,
                'kurs'           => 18103.00,
                'rate'           => 18103.00,
                'kurs_formatted' => '18.103,00',
                'message'        => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Tampilkan formulir khusus penetapan / kalkulasi HPP Produksi (Tahap 3 - Bagian Keuangan / Costing).
     */
    public function createHpp(Request $request): View
    {
        $selectedProduksiId = $request->input('produksi_id');

        // Dokumen produksi fisik yang siap dikalkulasi HPP:
        // 1. Memiliki kuantitas WIP (total_wip_qty > 0 atau tercatat di outputs)
        // 2. Belum difinalisasi HPP-nya (total_biaya_produksi <= 0 ATAU komponen energi CNG & TK belum dihitung)
        // 3. Atau dokumen yang secara eksplisit dipilih via query parameter ($selectedProduksiId)
        $produksiList = DatProduksiHdr::with(['gudang', 'outputs'])
            ->where('deleted_st', false)
            ->where(function ($wipQ) {
                $wipQ->where('total_wip_qty', '>', 0)
                    ->orWhereHas('outputs', function ($outQ) {
                        $outQ->where('qty_kg', '>', 0)->orWhere('qty_hasil', '>', 0);
                    });
            })
            ->where(function ($q) use ($selectedProduksiId) {
                $q->where(function ($sub) {
                    $sub->whereNull('total_biaya_produksi')
                        ->orWhere('total_biaya_produksi', '<=', 0)
                        ->orWhere(function ($sub2) {
                            $sub2->where(function ($s) {
                                $s->whereNull('cng_nilai')->orWhere('cng_nilai', '<=', 0);
                            })->where(function ($s) {
                                $s->whereNull('tk_total_nilai')->orWhere('tk_total_nilai', '<=', 0);
                            });
                        });
                });
                if ($selectedProduksiId) {
                    $q->orWhere('produksi_id', $selectedProduksiId);
                }
            })
            ->orderBy('produksi_tgl', 'desc')
            ->orderBy('produksi_id', 'desc')
            ->take(60)
            ->get();

        $energiRates = $this->tarifService->getEnergiLaborRates();
        $fohRates = $this->tarifService->getFohRates();
        $defaultCngTarifUsd = 14.50;

        // Ambil kurs JISDOR terkini Bank Indonesia untuk penetapan HPP Finance
        $latestKursRecord = DatKursHarian::active()->orderByDesc('tanggal')->first();
        $defaultCngKursJisdor = $latestKursRecord ? (float) $latestKursRecord->kurs_tengah : ($this->tarifService->getJisdorRate(date('Y-m-d'), true) ?: 18103.00);
        $latestKursDate = $latestKursRecord ? Carbon::parse($latestKursRecord->tanggal)->format('d/m/Y') : date('d/m/Y');
        $defaultCngTarif = round($defaultCngTarifUsd * $defaultCngKursJisdor, 2);

        // Cari seluruh dokumen produksi pada tanggal yang sama untuk notifikasi shift/lini lain
        $sameDaySpks = [];
        if ($selectedProduksiId) {
            $selectedProduksi = DatProduksiHdr::find($selectedProduksiId);
            if ($selectedProduksi) {
                $sameDaySpks = DatProduksiHdr::whereDate('produksi_tgl', $selectedProduksi->produksi_tgl)
                    ->where('deleted_st', false)
                    ->orderBy('shift_cd', 'asc')
                    ->orderBy('produksi_id', 'asc')
                    ->get()
                    ->map(function ($other) use ($selectedProduksiId) {
                        $isFinal = ((float)$other->cng_nilai > 0 || (float)$other->tk_total_nilai > 0 || str_contains($other->catatan_txt ?? '', '[HPP FINAL') || str_contains($other->catatan_txt ?? '', 'HPP Finance'));
                        return [
                            'produksi_id'   => $other->produksi_id,
                            'produksi_no'   => $other->produksi_no,
                            'shift_cd'      => $other->shift_cd ?? 'A',
                            'lini_produksi' => $other->lini_produksi ?? 'Lini Manual',
                            'total_wip_qty' => (float) ($other->total_wip_qty ?: ($other->outputs ? $other->outputs->sum('qty_kg') : 0)),
                            'hpp_per_kg'    => (float) $other->hpp_per_kg,
                            'is_current'    => ((int)$other->produksi_id === (int)$selectedProduksiId),
                            'is_hpp_final'  => $isFinal,
                        ];
                    })->values()->toArray();
            }
        }

        return view('produksi.hpp.create', compact(
            'produksiList',
            'selectedProduksiId',
            'sameDaySpks',
            'energiRates',
            'fohRates',
            'defaultCngTarifUsd',
            'defaultCngKursJisdor',
            'defaultCngTarif',
            'latestKursDate'
        ));
    }

    /**
     * AJAX Endpoint: Tarik rincian fisik produksi untuk ditarik ke lembar kerja HPP.
     */
    public function getProduksiDataForHpp(int $id): JsonResponse
    {
        try {
            $data = $this->produksiService->getProduksiDataForHpp($id);
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memuat rincian produksi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Simpan hasil kalkulasi lembar HPP produksi oleh Bagian Keuangan / Costing.
     */
    public function storeHpp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produksi_id'                 => 'required|exists:dat_produksi_hdr,produksi_id',
            'singkong_nilai'              => 'nullable|numeric|min:0',
            'minyak_nilai'                => 'nullable|numeric|min:0',
            'bumbu_nilai'                 => 'nullable|numeric|min:0',
            'karton_baru_nilai'           => 'nullable|numeric|min:0',
            'karton_bekas_nilai'          => 'nullable|numeric|min:0',
            'plastik_hd_nilai'            => 'nullable|numeric|min:0',
            'lakban_besar_nilai'          => 'nullable|numeric|min:0',
            'lakban_kecil_nilai'          => 'nullable|numeric|min:0',
            'tali_rafia_nilai'            => 'nullable|numeric|min:0',

            // Utilitas Gas CNG
            'cng_mmbtu'                   => 'required|numeric|min:0',
            'cng_tarif_usd'               => 'nullable|numeric|min:0',
            'cng_kurs_jisdor'             => 'nullable|numeric|min:0',
            'cng_tarif'                   => 'nullable|numeric|min:0',
            'cng_nilai'                   => 'required|numeric|min:0',

            // Tenaga Kerja (Langsung & Tidak Langsung)
            'tk_langsung_org'             => 'nullable|integer|min:0',
            'tk_langsung_nilai'           => 'nullable|numeric|min:0',
            'tk_tidak_langsung_org'       => 'nullable|integer|min:0',
            'tk_tidak_langsung_nilai'     => 'nullable|numeric|min:0',
            'tk_jumlah_org'               => 'required|integer|min:0',
            'tk_tarif_per_org'            => 'nullable|numeric|min:0',
            'tk_total_nilai'              => 'required|numeric|min:0',

            // Output Alokasi HPP
            'output_hpp'                  => 'nullable|array',
            'output_hpp.*'                => 'nullable|numeric|min:0',

            'catatan_hpp'                 => 'nullable|string',
        ]);

        try {
            $userId = Auth::user()?->username ?? 'FINANCE';
            $produksi = $this->produksiService->saveHppCalculation((int) $validated['produksi_id'], $validated, $userId);

            // Periksa apakah masih ada dokumen fisik SPK lain pada hari yang sama yang belum difinalisasi HPP
            $otherPending = DatProduksiHdr::whereDate('produksi_tgl', $produksi->produksi_tgl)
                ->where('deleted_st', false)
                ->where('produksi_id', '!=', $produksi->produksi_id)
                ->where(function ($sub) {
                    $sub->whereNull('total_biaya_produksi')
                        ->orWhere('total_biaya_produksi', '<=', 0)
                        ->orWhere(function ($sub2) {
                            $sub2->where(function ($s) {
                                $s->whereNull('cng_nilai')->orWhere('cng_nilai', '<=', 0);
                            })->where(function ($s) {
                                $s->whereNull('tk_total_nilai')->orWhere('tk_total_nilai', '<=', 0);
                            });
                        });
                })
                ->orderBy('shift_cd', 'asc')
                ->first();

            $msg = "✅ Lembar HPP Produksi {$produksi->produksi_no} (Shift {$produksi->shift_cd} • {$produksi->lini_produksi}) berhasil difinalisasi!";
            $msg .= " Total Biaya: Rp " . number_format($produksi->total_biaya_produksi, 0, ',', '.') . " | HPP Riil: Rp " . number_format($produksi->hpp_per_kg, 2, ',', '.') . "/kg.";

            if ($otherPending) {
                $warnMsg = $msg . " ⚠️ PENGINGAT PENTING: Pada tanggal yang sama masih terdapat dokumen SPK #{$otherPending->produksi_no} (Shift {$otherPending->shift_cd} • {$otherPending->lini_produksi}) yang BELUM ditarik & difinalisasi HPP. Silakan lanjutkan pengisian biaya untuk dokumen ini.";
                return redirect()->route('produksi.hpp.create', ['produksi_id' => $otherPending->produksi_id])
                    ->with('warning', $warnMsg);
            }

            return redirect()->route('produksi.hpp.show', $produksi->produksi_id)->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan kalkulasi HPP: ' . $e->getMessage());
        }
    }

    /**
     * Simpan data hasil produksi harian & suntik stok fisik WIP jika POSTED.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produksi_tgl'                => 'required|date',
            'gudang_id'                   => 'required|exists:mst_gudang,gudang_id',
            'lini_produksi'               => 'required|string|max:100',
            'shift_cd'                    => 'nullable|string|in:A,B,a,b',
            'jam_produksi'                => 'nullable|string|max:10',
            'varietas_singkong'           => 'nullable|string|max:100',
            'qty_karton'                  => 'nullable|integer|min:0',
            'no_karton_awal'              => 'nullable|integer|min:1',
            'no_karton_akhir'             => 'nullable|integer|min:1',
            'batch_wip_no'                => 'nullable|string|max:100',
            'exp_date'                    => 'nullable|date',
            'pakai_id'                    => 'nullable|exists:dat_pakai_hdr,pakai_id',
            'status_cd'                   => 'required|in:DRAFT,POSTED',

            // Bahan
            'singkong_qty'                => 'nullable|numeric|min:0',
            'singkong_nilai'              => 'nullable|numeric|min:0',
            'minyak_sawit_qty'            => 'nullable|numeric|min:0',
            'minyak_kelapa_qty'           => 'nullable|numeric|min:0',
            'minyak_nilai'                => 'nullable|numeric|min:0',
            'bumbu_qty'                   => 'nullable|numeric|min:0',
            'bumbu_nilai'                 => 'nullable|numeric|min:0',
            'karton_baru_qty'             => 'nullable|numeric|min:0',
            'karton_baru_nilai'           => 'nullable|numeric|min:0',
            'karton_bekas_qty'            => 'nullable|numeric|min:0',
            'karton_bekas_nilai'          => 'nullable|numeric|min:0',
            'plastik_hd_qty'              => 'nullable|numeric|min:0',
            'plastik_hd_nilai'            => 'nullable|numeric|min:0',
            'lakban_besar_qty'            => 'nullable|numeric|min:0',
            'lakban_besar_nilai'          => 'nullable|numeric|min:0',
            'lakban_kecil_qty'            => 'nullable|numeric|min:0',
            'lakban_kecil_nilai'          => 'nullable|numeric|min:0',
            'tali_rafia_qty'              => 'nullable|numeric|min:0',
            'tali_rafia_nilai'            => 'nullable|numeric|min:0',

            // Energi
            'cng_mmbtu'                   => 'nullable|numeric|min:0',
            'cng_tarif_usd'               => 'nullable|numeric|min:0',
            'cng_kurs_jisdor'             => 'nullable|numeric|min:0',
            'cng_tarif'                   => 'nullable|numeric|min:0',
            'cng_nilai'                   => 'nullable|numeric|min:0',

            // Tenaga Kerja
            'tk_jumlah_org'               => 'nullable|integer|min:0',
            'tk_langsung_org'             => 'nullable|integer|min:0',
            'tk_tidak_langsung_org'       => 'nullable|integer|min:0',
            'tk_training_org'             => 'nullable|integer|min:0',
            'tk_tarif_per_org'            => 'nullable|numeric|min:0',
            'tk_total_nilai'              => 'nullable|numeric|min:0',

            // Overhead
            'fotocopy_nilai'              => 'nullable|numeric|min:0',
            'sarung_tangan_plastik_qty'   => 'nullable|numeric|min:0',
            'sarung_tangan_plastik_nilai' => 'nullable|numeric|min:0',
            'sarung_tangan_kain_qty'      => 'nullable|numeric|min:0',
            'sarung_tangan_kain_nilai'    => 'nullable|numeric|min:0',
            'qc_pengawasan_nilai'         => 'nullable|numeric|min:0',
            'listrik_air_telp_nilai'      => 'nullable|numeric|min:0',
            'pemeliharaan_mesin_nilai'    => 'nullable|numeric|min:0',
            'penyusutan_mesin_nilai'      => 'nullable|numeric|min:0',
            'limbah_padat_nilai'          => 'nullable|numeric|min:0',
            'limbah_kimia_nilai'          => 'nullable|numeric|min:0',

            // Hasil Output Standar WIP Olahan
            'asin_barco_qty'              => 'nullable|numeric|min:0',
            'asin_sawit_qty'              => 'nullable|numeric|min:0',
            'no_salt_qty'                 => 'nullable|numeric|min:0',
            'balo_gelombang_qty'          => 'nullable|numeric|min:0',
            'berko_qty'                   => 'nullable|numeric|min:0',
            'berko_me_qty'                => 'nullable|numeric|min:0',

            // Hasil Output Dinamis (WIP & Finish Good / FG - Blueprint 9.B)
            'output_items'                 => 'nullable|array',
            'output_items.*.barang_id'     => 'nullable|exists:mst_barang,barang_id',
            'output_items.*.jenis_cd'      => 'nullable|string|in:WIP,FG',
            'output_items.*.qty_hasil'     => 'nullable|numeric|min:0',
            'output_items.*.satuan_cd'     => 'nullable|string|max:50',
            'output_items.*.qty_kg'        => 'nullable|numeric|min:0',
            'output_items.*.batch_no'      => 'nullable|string|max:100',
            'output_items.*.hpp_satuan'    => 'nullable|numeric|min:0',
            'output_items.*.keterangan_txt' => 'nullable|string|max:255',

            'catatan_txt'                 => 'nullable|string',

            // Limbah HACCP & PO Buyer IFL (Otomatis Tersinkronisasi dari Tabel Output & Form)
            'po_ifl_no'                   => 'nullable|string|max:50',
            'po_ifl_realisasi_qty'        => 'nullable|numeric|min:0',
            'po_ifl_terpenuhi_st'         => 'nullable|in:0,1',
            'waste_lengket_qty'           => 'nullable|numeric|min:0',
            'waste_lengket_persen'        => 'nullable|numeric|min:0',
            'berko_ifl_qty'               => 'nullable|numeric|min:0',
            'berko_non_ifl_qty'           => 'nullable|numeric|min:0',
            'waste_total_qty'             => 'nullable|numeric|min:0',
        ]);

        try {
            $produksi = $this->produksiService->store($validated);

            $msg = "✅ Hasil Produksi {$produksi->produksi_no} tanggal " . Carbon::parse($produksi->produksi_tgl)->format('d/m/Y') . " berhasil disimpan! ";
            $msg .= "Rendemen: " . number_format($produksi->rendemen_persen, 2, ',', '.') . "% | HPP: Rp " . number_format($produksi->hpp_per_kg, 2, ',', '.') . " / kg.";

            $tglObj = Carbon::parse($produksi->produksi_tgl);
            return redirect()->route('produksi.index', ['tahun' => $tglObj->year, 'bulan' => $tglObj->month])
                ->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan hasil produksi: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan formulir lengkap pengerjaan Sortir Ulang / Rework KW 2.
     */
    public function createRework(Request $request): View
    {
        $gudangList = MstGudang::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('gudang_nm')
            ->get();

        return view('produksi.rework.create', compact('gudangList'));
    }

    /**
     * AJAX Endpoint: Mengambil daftar batch WIP & FG yang memiliki sisa stok untuk disortir ulang / rework.
     */
    public function ajaxBatchesRework(Request $request): JsonResponse
    {
        $gudangId = $request->input('gudang_id');

        $query = DatStokBatch::with(['barang.satuanDasar', 'barang.jenisBarang', 'gudang'])
            ->where('sisa_qty', '>', 0)
            ->where('deleted_st', false)
            ->whereHas('barang', function ($b) {
                $b->whereIn('jenis_barang_id', [2, 4])
                  ->orWhere('barang_cd', 'LIKE', 'WIP%')
                  ->orWhere('barang_cd', 'LIKE', 'FG%');
            });

        if (!empty($gudangId)) {
            $query->where('gudang_id', $gudangId);
        }

        $batches = $query->orderBy('updated_at', 'desc')->get()->map(function ($stok) {
            $isFg = ($stok->barang?->jenis_barang_id == 4 || str_starts_with($stok->barang?->barang_cd ?? '', 'FG-'));
            return [
                'stok_id'      => $stok->stok_id,
                'gudang_id'    => $stok->gudang_id,
                'gudang_nm'    => $stok->gudang?->gudang_nm ?? '-',
                'barang_id'    => $stok->barang_id,
                'barang_cd'    => $stok->barang?->barang_cd ?? '-',
                'barang_nm'    => $stok->barang?->barang_nm ?? '-',
                'batch_no'     => $stok->batch_no,
                'sisa_qty'     => (float) $stok->sisa_qty,
                'harga_satuan' => (float) $stok->harga_satuan,
                'satuan_nm'    => $stok->barang?->satuanDasar?->satuan_nm ?? ($isFg ? 'DUS' : 'KG'),
                'is_fg'        => $isFg,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $batches,
        ]);
    }

    /**
     * Simpan Formulir Sortir Ulang / Rework KW 2 (Poin 23 & Poin 24).
     */
    public function storeRework(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produksi_tgl'   => 'required|date',
            'gudang_id'      => 'required|exists:mst_gudang,gudang_id',
            'barang_id'      => 'required|exists:mst_barang,barang_id',
            'batch_no'       => 'required|string|max:100',
            'qty_rework'     => 'required|numeric|min:0.01',
            'qty_kw2'        => 'required|numeric|min:0.01',
            'qty_lengket'    => 'nullable|numeric|min:0',
            'qty_remahan'    => 'nullable|numeric|min:0',
            'dus_dibongkar'  => 'nullable|integer|min:0',
            'dus_puteran'    => 'nullable|integer|min:0',
            'dus_rusak'      => 'nullable|integer|min:0',
            'alasan_qc'      => 'required|string|max:255',
            'catatan_txt'    => 'nullable|string|max:1000',
        ]);

        try {
            $produksi = $this->produksiService->storeRework($validated);

            return redirect()->route('produksi.index', ['grade_cd' => 'KW2'])
                ->with('success', "Sortir Ulang / Rework KW 2 ({$produksi->produksi_no}) berhasil dicatat. Stok KS CURAH SORTIR (KW 2) telah ditambahkan ke gudang.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal memproses Sortir Ulang / Rework: " . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail lembar produksi harian, HPP riil, dan rincian karton.
     */
    public function show(int $id): View
    {
        $produksi = $this->produksiService->getById($id);

        // Ambil seluruh dokumen produksi pada tanggal yang sama untuk Tab Switcher Split View per Lini
        $sameDateProductions = DatProduksiHdr::whereDate('produksi_tgl', $produksi->produksi_tgl)
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('shift_cd', 'asc')
            ->orderBy('produksi_id', 'asc')
            ->get();

        return view('produksi.show', compact('produksi', 'sameDateProductions'));
    }

    /**
     * Tampilkan detail lembar HPP Produksi, biaya manufaktur, dan riwayat audit (Khusus Finance / Costing).
     */
    public function showHpp(int $id): View
    {
        $produksi = $this->produksiService->getById($id);

        // Ambil seluruh dokumen produksi pada tanggal yang sama untuk Tab Switcher Split View per Lini
        $sameDateProductions = DatProduksiHdr::whereDate('produksi_tgl', $produksi->produksi_tgl)
            ->where('deleted_st', false)
            ->where('status_cd', '!=', 'VOID')
            ->orderBy('shift_cd', 'asc')
            ->orderBy('produksi_id', 'asc')
            ->get();

        return view('produksi.hpp.show', compact('produksi', 'sameDateProductions'));
    }

    /**
     * Beri nomor batch resmi untuk produksi buffer stock saat jadwal keberangkatan truk tiba.
     */
    public function assignBatch(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'tgl_keberangkatan' => ['required', 'date'],
            'shift_cd'          => ['required', 'string', 'in:A,B'],
            'no_karton_awal'    => ['required', 'integer', 'min:1'],
            'qty_karton'        => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $produksi = $this->produksiService->assignBatch($id, $validated);

            return redirect()->route('produksi.show', $produksi->produksi_id)
                ->with('success', "Nomor Batch resmi ({$produksi->batch_wip_no}) berhasil diterbitkan untuk keberangkatan tgl " . Carbon::parse($validated['tgl_keberangkatan'])->format('d/m/Y') . ".");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menerbitkan nomor batch: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Khusus Penyesuaian / Koreksi HPP Lembar Produksi Harian (Worksheet Akuntansi Biaya Manufaktur).
     */
    public function adjustPage(int $id): View
    {
        $produksi = $this->produksiService->getById($id);

        // Ambil semua batch aktif gudang yang memiliki sisa stok untuk bahan baku & kemasan
        $allBatches = DatStokBatch::with(['barang.satuan', 'gudang'])
            ->where('deleted_st', false)
            ->where('sisa_qty', '>', 0)
            ->orderBy('stok_id', 'desc')
            ->get();

        $availableBatchesMap = [
            'singkong'     => [],
            'minyak'       => [],
            'bumbu'        => [],
            'karton_baru'  => [],
            'karton_bekas' => [],
            'plastik_hd'   => [],
            'lakban_besar' => [],
            'lakban_kecil' => [],
            'tali_rafia'   => [],
        ];

        foreach ($allBatches as $sb) {
            $barang = $sb->barang;
            if (!$barang) continue;
            $nm = strtoupper($barang->barang_nm ?? '');
            $cd = strtoupper($barang->barang_cd ?? '');

            $item = [
                'stok_id'      => (int) $sb->stok_id,
                'batch_no'     => (string) $sb->batch_no,
                'sisa_qty'     => (float) $sb->sisa_qty,
                'harga_satuan' => (float) $sb->harga_satuan,
                'barang_nm'    => (string) ($barang->barang_nm ?? ''),
                'satuan_nm'    => (string) ($barang->satuan?->satuan_nm ?? 'Unit'),
                'gudang_nm'    => (string) ($sb->gudang?->gudang_nm ?? 'Gudang Utama'),
            ];

            if (str_contains($nm, 'SINGKONG') || str_contains($cd, 'SNG')) {
                $availableBatchesMap['singkong'][] = $item;
            } elseif (str_contains($nm, 'MINYAK') || str_contains($cd, 'MSW') || str_contains($cd, 'MKL')) {
                $availableBatchesMap['minyak'][] = $item;
            } elseif (str_contains($nm, 'BUMBU') || str_contains($nm, 'GARAM') || str_contains($nm, 'MSG') || str_contains($cd, 'BMB')) {
                $availableBatchesMap['bumbu'][] = $item;
            } elseif (str_contains($nm, 'KARTON') && str_contains($nm, 'BARU')) {
                $availableBatchesMap['karton_baru'][] = $item;
            } elseif (str_contains($nm, 'KARTON') && str_contains($nm, 'BEKAS')) {
                $availableBatchesMap['karton_bekas'][] = $item;
            } elseif (str_contains($nm, 'PLASTIK') || str_contains($nm, 'HD')) {
                $availableBatchesMap['plastik_hd'][] = $item;
            } elseif (str_contains($nm, 'LAKBAN') && str_contains($nm, 'BESAR')) {
                $availableBatchesMap['lakban_besar'][] = $item;
            } elseif (str_contains($nm, 'LAKBAN') && str_contains($nm, 'KECIL')) {
                $availableBatchesMap['lakban_kecil'][] = $item;
            } elseif (str_contains($nm, 'RAFIA') || str_contains($nm, 'TALI')) {
                $availableBatchesMap['tali_rafia'][] = $item;
            }
        }

        $availableSingkongBatches = $availableBatchesMap['singkong'];

        return view('produksi.adjust', compact('produksi', 'availableBatchesMap', 'availableSingkongBatches'));
    }

    /**
     * Tampilan cetak label stiker karton fisik (format label sticker box).
     */
    public function cetakStiker(Request $request, int $id): View
    {
        $produksi = $this->produksiService->getById($id);
        $noKarton = $request->input('karton_no'); // Opsional: cetak nomor karton tertentu
        return view('produksi.cetak-stiker', compact('produksi', 'noKarton'));
    }

    /**
     * Hapus lembar produksi harian.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->produksiService->delete($id);
            return back()->with('success', 'Catatan produksi harian berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Penyesuaian / Koreksi HPP Harian Dokumen Tertentu dengan Audit Trail Akuntansi.
     */
    public function adjustHpp(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan_txt'                  => 'required|string|min:5',

            // Output WIP Kuantitas (Hasil Timbangan Fisik)
            'total_wip_qty'               => 'nullable|numeric|min:0',
            'output_items'                => 'nullable|array',
            'output_items.*.qty_kg'       => 'nullable|numeric|min:0',

            // Bahan Baku Langsung (BBL) - Kuantitas & Nilai
            'singkong_qty'                => 'nullable|numeric|min:0',
            'singkong_nilai'              => 'nullable|numeric|min:0',
            'singkong_stok_action'        => 'nullable|string|in:none,deduct_batch',
            'additional_batch_no'         => 'nullable|string|max:100',
            'additional_batch_qty'        => 'nullable|numeric|min:0',
            'minyak_qty'                  => 'nullable|numeric|min:0',
            'minyak_nilai'                => 'nullable|numeric|min:0',
            'bumbu_qty'                   => 'nullable|numeric|min:0',
            'bumbu_nilai'                 => 'nullable|numeric|min:0',

            // Kemasan & Packing - Kuantitas & Nilai
            'karton_baru_qty'             => 'nullable|numeric|min:0',
            'karton_baru_nilai'           => 'nullable|numeric|min:0',
            'karton_bekas_qty'            => 'nullable|numeric|min:0',
            'karton_bekas_nilai'          => 'nullable|numeric|min:0',
            'plastik_hd_qty'              => 'nullable|numeric|min:0',
            'plastik_hd_nilai'            => 'nullable|numeric|min:0',
            'lakban_besar_qty'            => 'nullable|numeric|min:0',
            'lakban_besar_nilai'          => 'nullable|numeric|min:0',
            'lakban_kecil_qty'            => 'nullable|numeric|min:0',
            'lakban_kecil_nilai'          => 'nullable|numeric|min:0',
            'tali_rafia_qty'              => 'nullable|numeric|min:0',
            'tali_rafia_nilai'            => 'nullable|numeric|min:0',

            // Tenaga Kerja & Utilitas - Kuantitas & Nilai
            'tk_jumlah_org'               => 'nullable|integer|min:0',
            'tk_total_nilai'              => 'nullable|numeric|min:0',
            'cng_mmbtu'                   => 'nullable|numeric|min:0',
            'cng_tarif_usd'               => 'nullable|numeric|min:0',
            'cng_kurs_jisdor'             => 'nullable|numeric|min:0',
            'cng_tarif'                   => 'nullable|numeric|min:0',
            'cng_nilai'                   => 'nullable|numeric|min:0',
            'listrik_air_telp_nilai'      => 'nullable|numeric|min:0',

            // Overhead Pabrik (FOH)
            'pemeliharaan_mesin_nilai'    => 'nullable|numeric|min:0',
            'qc_pengawasan_nilai'         => 'nullable|numeric|min:0',
            'penyusutan_mesin_nilai'      => 'nullable|numeric|min:0',
            'sarung_tangan_plastik_qty'   => 'nullable|numeric|min:0',
            'sarung_tangan_plastik_nilai' => 'nullable|numeric|min:0',
            'sarung_tangan_kain_qty'      => 'nullable|numeric|min:0',
            'sarung_tangan_kain_nilai'    => 'nullable|numeric|min:0',
            'fotocopy_nilai'              => 'nullable|numeric|min:0',
            'limbah_padat_nilai'          => 'nullable|numeric|min:0',
            'limbah_kimia_nilai'          => 'nullable|numeric|min:0',
        ]);

        try {
            $user = Auth::user()?->name ?? (Auth::user()?->username ?? 'SYSTEM');
            $log = $this->produksiService->adjustSingleProductionHpp($id, $request->all(), $user);

            $msg = "✅ Berhasil melakukan adjustment HPP (No. BA: {$log->adj_no})! ";
            $msg .= "HPP baru: Rp " . number_format($log->hpp_sesudah, 2, ',', '.') . " / kg. Saldo batch stok di gudang telah disinkronkan.";

            return redirect()->route('produksi.hpp.show', $id)->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal melakukan adjustment HPP: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel Daftar Hasil Barang Produksi (.xlsx) (Point 9.B.4)
     */
    public function exportHasilProduksi(Request $request): StreamedResponse
    {
        $filters = [
            'gudang_id'  => $request->input('gudang_id'),
            'search'     => $request->input('search'),
            'barang_cd'  => $request->input('barang_cd'),
            'barang_nm'  => $request->input('barang_nm'),
            'batch_no'   => $request->input('batch_no'),
            'jenis_cd'   => $request->input('jenis_cd'),
            'grade_cd'   => $request->input('grade_cd'),
            'kategori'   => $request->input('kategori'),
            'tgl_dari'   => $request->input('tgl_dari'),
            'tgl_sampai' => $request->input('tgl_sampai'),
        ];

        $items = $this->produksiService->getAllHasilProduksi($filters);

        $gudangNm = null;
        if (!empty($filters['gudang_id'])) {
            $gudang = MstGudang::find($filters['gudang_id']);
            $gudangNm = $gudang?->gudang_nm;
        }

        $printedBy = Auth::user()->username ?? 'Admin';
        $printedAt = Carbon::now()->format('d/m/Y H:i');

        $exporter = new HasilProduksiExport(
            $items,
            $gudangNm,
            $filters['search'],
            $filters['batch_no'],
            $printedBy,
            $printedAt
        );

        return $exporter->download('Laporan_Hasil_Produksi_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Download Template Resmi Import Excel Hasil Produksi (.xlsx) (Point 9.B.3)
     */
    public function downloadHasilTemplate(): StreamedResponse
    {
        $template = new HasilProduksiTemplate();
        return $template->download();
    }

    /**
     * Proses Upload File Excel Hasil Produksi (.xlsx) (Point 9.B.3)
     */
    public function importHasilProduksi(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'import_file.required' => 'Pilih file Excel yang akan diimpor.',
            'import_file.mimes'    => 'Format file harus berupa .xlsx atau .xls.',
            'import_file.max'      => 'Ukuran file tidak boleh melebihi 5 MB.',
        ]);

        try {
            $importer = new HasilProduksiImport($this->codeGenerator, $this->stokService);
            $importer->import($request->file('import_file'));

            $msg = "✅ Berhasil mengimpor {$importer->successCount} item hasil produksi!";
            if ($importer->errorCount > 0) {
                $msg .= " Catatan: terdapat {$importer->errorCount} grup data yang gagal atau dilewati.";
            }

            return redirect()->route('produksi.index', ['tab' => 'hasil'])
                ->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->route('produksi.index', ['tab' => 'hasil'])
                ->with('error', 'Gagal memproses import Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Excel Buku Rekap HPP Bulanan (.xlsx) (Point 13.c)
     */
    public function exportRekapExcel(Request $request): StreamedResponse
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));

        $report = $this->produksiService->getMonthlyReport($year, $month);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $printedBy = Auth::user()->username ?? 'Admin';
        $printedAt = Carbon::now()->format('d/m/Y H:i');

        $exporter = new RekapHppExport($report, $year, $month, $monthName, $printedBy, $printedAt);
        return $exporter->download("Rekap_HPP_{$monthName}_{$year}.xlsx");
    }

    /**
     * Export PDF Buku Rekap HPP Bulanan (.pdf) (Point 13.d)
     */
    public function exportRekapPdf(Request $request)
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));

        $report = $this->produksiService->getMonthlyReport($year, $month);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthsList[$month] ?? 'Januari';

        $pdf = Pdf::loadView('produksi.pdf.rekap-hpp', compact('report', 'year', 'month', 'monthName'))
            ->setPaper('legal', 'landscape');

        return $pdf->stream("Rekap_HPP_{$monthName}_{$year}.pdf");
    }

    /**
     * Download Template Excel Buku Rekap HPP Harian (.xlsx)
     */
    public function downloadRekapTemplate(): StreamedResponse
    {
        $template = new RekapHppTemplate();
        return $template->download();
    }

    /**
     * Proses Upload File Excel Buku Rekap HPP Bulanan (.xlsx)
     */
    public function importRekapExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:5120',
            'gudang_id'   => 'nullable|exists:mst_gudang,gudang_id',
        ], [
            'import_file.required' => 'Pilih berkas Excel Rekap HPP yang akan diimpor.',
            'import_file.mimes'    => 'Format berkas harus berupa .xlsx atau .xls.',
            'import_file.max'      => 'Ukuran berkas tidak boleh melebihi 5 MB.',
        ]);

        try {
            $importer = new RekapHppImport($this->codeGenerator);
            $importer->import($request->file('import_file'), (int) $request->input('gudang_id', 1));

            $msg = "✅ Berhasil mengimpor data Rekap HPP ({$importer->successCount} hari produksi diperbarui)!";
            if ($importer->skipCount > 0) {
                $msg .= " Catatan: {$importer->skipCount} baris tanggal kosong/libur dilewati.";
            }

            return redirect()->route('produksi.rekap')
                ->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->route('produksi.rekap')
                ->with('error', 'Gagal memproses import Rekap HPP: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Khusus Penyesuaian / Rekonsiliasi Biaya Utilitas Bulanan (Listrik & Gas).
     */
    public function adjustUtilitasPage(Request $request): View
    {
        $year = (int) $request->input('tahun', date('Y'));
        $month = (int) $request->input('bulan', date('n'));

        $report = $this->produksiService->getMonthlyReport($year, $month);
        $fohRates = $this->tarifService->getFohRates();
        $energiRates = $this->tarifService->getEnergiLaborRates();
        $allTarif = $this->tarifService->getAllTarif();
        $tarifCngDefault = $energiRates['cng_tarif'] ?? round(14.50 * 18138, 2);

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('produksi.adjust-utilitas', compact('year', 'month', 'report', 'fohRates', 'energiRates', 'tarifCngDefault', 'allTarif', 'monthsList'));
    }

    /**
     * Penyesuaian Biaya Utilitas Bulanan (Listrik, Air & Gas CNG).
     */
    public function adjustUtilitas(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun'                 => 'required|integer|min:2020|max:2099',
            'bulan'                 => 'required|integer|min:1|max:12',
            'redirect_to'           => 'nullable|string|in:rekap,adjust',
            'adjust_listrik'        => 'nullable',
            'mode_alokasi_listrik'  => 'nullable|in:tarif_per_kg,total_tagihan,bagi_rata,proporsional_wip',
            'listrik_tarif_per_kg'  => 'nullable|numeric|min:0',
            'total_listrik_air'     => 'nullable|numeric|min:0',
            'adjust_cng'            => 'nullable',
            'mode_cng'              => 'nullable|in:update_tarif,total_tagihan',
            'cng_tarif_baru'        => 'nullable|numeric|min:0',
            'total_cng_tagihan'     => 'nullable|numeric|min:0',
        ]);

        try {
            $result = $this->produksiService->adjustMonthlyUtilities(
                (int) $request->input('tahun'),
                (int) $request->input('bulan'),
                $request->all(),
                Auth::user()?->username ?? 'SYSTEM'
            );

            $targetRoute = $request->input('redirect_to') === 'adjust' ? 'produksi.adjust-utilitas' : 'produksi.rekap';
            return redirect()->route($targetRoute, [
                'mode'  => 'harian',
                'tahun' => $request->input('tahun'),
                'bulan' => $request->input('bulan'),
            ])->with('success', "Berhasil menyesuaikan biaya utilitas untuk {$result['count']} catatan produksi pada periode tersebut!");
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyesuaikan utilitas: ' . $e->getMessage());
        }
    }
}

