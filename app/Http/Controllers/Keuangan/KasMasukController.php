<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Models\MasterData\MstCustomer;
use App\Services\Keuangan\KasMasukService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KasMasukController extends Controller
{
    protected KasMasukService $kasMasukService;

    public function __construct(KasMasukService $kasMasukService)
    {
        $this->kasMasukService = $kasMasukService;
    }

    /**
     * Halaman Indeks Daftar Bukti Kas Masuk (BKM)
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 15;
        }

        $filters = [
            'status'        => $request->input('status', 'ALL'),
            'kas_bank_id'   => $request->input('kas_bank_id'),
            'kategori'      => $request->input('kategori'),
            'metode_terima' => $request->input('metode_terima'),
            'search'        => $request->input('search'),
            'tgl_mulai'     => $request->input('tgl_mulai'),
            'tgl_selesai'   => $request->input('tgl_selesai'),
            'per_page'      => $perPage,
        ];

        $bkmList = $this->kasMasukService->getKasMasukListPaginated($filters, $perPage);
        $kpi = $this->kasMasukService->getKpiMetrics();
        $statusCounts = $this->kasMasukService->getStatusCounts();
        $kasBanks = MstKasBank::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('nama_akun', 'asc')
            ->get();

        return view('keuangan.kas_masuk.index', compact('bkmList', 'kpi', 'statusCounts', 'kasBanks', 'filters'));
    }

    /**
     * Form Input Penerimaan Kas / Bank Baru (BKM)
     */
    public function create(Request $request): View
    {
        $kasBanks = MstKasBank::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('nama_akun', 'asc')
            ->get();
        $customers = MstCustomer::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('customer_nm', 'asc')
            ->get();

        $defaultKategori = $request->input('kategori', 'PENJUALAN_LIMBAH');
        $suggestedBkmNo = $this->kasMasukService->generateBkmNo();

        return view('keuangan.kas_masuk.create', compact('kasBanks', 'customers', 'defaultKategori', 'suggestedBkmNo'));
    }

    /**
     * Simpan Transaksi Bukti Kas Masuk (Tahap 1: Kasir)
     * Status otomatis DRAFT, saldo kas/bank fisik bertambah dan tercatat di mutasi kas.
     */
    public function store(Request $request): RedirectResponse
    {
        $rawItems = $request->input('items', []);
        $filteredItems = [];

        foreach ($rawItems as $item) {
            $deskripsi = trim($item['deskripsi'] ?? $item['uraian'] ?? '');
            $qty = (float) ($item['qty'] ?? 1);
            $satuan = trim($item['satuan'] ?? 'kg');
            $harga = (float) ($item['harga_satuan'] ?? 0);
            $subtotal = (float) ($item['subtotal'] ?? $item['nominal'] ?? ($qty * $harga));

            if (!empty($deskripsi) && $subtotal > 0) {
                $filteredItems[] = [
                    'uraian'       => $deskripsi,
                    'qty'          => $qty,
                    'satuan'       => $satuan,
                    'harga_satuan' => $harga,
                    'nominal'      => $subtotal,
                ];
            }
        }

        $request->merge(['items' => $filteredItems]);

        $request->validate([
            'kas_bank_id'         => 'required|exists:mst_kas_bank,kas_bank_id',
            'tgl_terima'          => 'required|date',
            'kategori_penerimaan' => 'required|string|in:PENJUALAN_LIMBAH,PENJUALAN_TUNAI,SETORAN_MODAL,PENDAPATAN_LAIN',
            'metode_terima'       => 'required|string|in:CASH,TRANSFER,GIRO_CEK,EDC_QRIS',
            'penyetor_nama'       => 'required|string|max:150',
            'customer_id'         => 'nullable|exists:mst_customer,customer_id',
            'no_referensi_bank'   => 'nullable|string|max:100',
            'catatan'             => 'nullable|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.uraian'      => 'required|string|max:255',
            'items.*.nominal'     => 'required|numeric|min:1',
        ], [
            'kas_bank_id.required'         => 'Silakan pilih rekening/kas tujuan penerimaan uang.',
            'tgl_terima.required'          => 'Tanggal penerimaan kas wajib diisi.',
            'kategori_penerimaan.required' => 'Pilih kategori penerimaan kas.',
            'kategori_penerimaan.in'       => 'Kategori penerimaan tidak valid. Untuk pembayaran piutang customer, gunakan menu Kas Masuk Piutang (AR).',
            'metode_terima.required'       => 'Pilih metode pembayaran (Cash / Transfer / dll).',
            'penyetor_nama.required'       => 'Nama penyetor atau pembayar wajib diisi.',
            'items.required'               => 'Tambahkan minimal 1 baris rincian item penerimaan.',
            'items.min'                    => 'Tambahkan minimal 1 baris rincian item penerimaan dengan nominal > 0.',
            'items.*.uraian.required'      => 'Deskripsi rincian item wajib diisi.',
            'items.*.nominal.min'          => 'Nominal subtotal item minimal Rp 1.',
        ]);

        try {
            $payload = [
                'kas_bank_id'         => (int) $request->input('kas_bank_id'),
                'tgl_terima'          => $request->input('tgl_terima'),
                'kategori_penerimaan' => $request->input('kategori_penerimaan'),
                'metode_terima'       => $request->input('metode_terima'),
                'penyetor_nama'       => $request->input('penyetor_nama'),
                'customer_id'         => $request->input('customer_id') ?: null,
                'no_referensi_bank'   => $request->input('no_referensi_bank') ?: null,
                'catatan'             => $request->input('catatan') ?: null,
                'items'               => $filteredItems,
            ];

            $bkm = $this->kasMasukService->storeKasMasuk($payload, Auth::user());

            $submitAction = $request->input('submit_action', 'save');
            $successMsg = "Bukti Kas Masuk {$bkm->bkm_no} berhasil dicatat (Rp " . number_format($bkm->total_terima, 0, ',', '.') . "). Saldo kas/bank fisik telah diperbarui.";

            if ($submitAction === 'save_and_print') {
                return redirect()->route('keuangan.kas_masuk.show', [$bkm->bkm_id, 'print' => 'true'])
                    ->with('success', $successMsg);
            }

            return redirect()->route('keuangan.kas_masuk.show', $bkm->bkm_id)
                ->with('success', $successMsg);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat Kas Masuk: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi BKM dan Terbitkan Jurnal Umum GL (Tahap 2: Bagian Akuntansi)
     */
    public function verifikasiJurnal(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'coa_id'            => 'required|exists:mst_coa,coa_id',
            'tgl_jurnal'        => 'nullable|date',
            'keterangan_jurnal' => 'nullable|string|max:500',
        ], [
            'coa_id.required' => 'Silakan pilih Akun Kredit (Pos Pendapatan / Modal) untuk pembukuan Jurnal Umum.',
            'coa_id.exists'   => 'Akun COA yang dipilih tidak valid di sistem.',
            'tgl_jurnal.date' => 'Format tanggal jurnal tidak valid.',
        ]);

        try {
            $coaId = (int) $request->input('coa_id');
            $tglJurnal = $request->input('tgl_jurnal');
            $keteranganJurnal = $request->input('keterangan_jurnal');

            $bkm = $this->kasMasukService->verifikasiDanTerbitkanJurnal(
                $id,
                $coaId,
                $tglJurnal,
                Auth::user(),
                $keteranganJurnal
            );

            $message = "Bukti Kas Masuk {$bkm->bkm_no} berhasil diverifikasi dan Jurnal Umum GL telah diterbitkan (Status POSTED)!";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'bkm_no'  => $bkm->bkm_no,
                    'bkm_id'  => $bkm->bkm_id,
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memverifikasi BKM: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal memverifikasi BKM: ' . $e->getMessage());
        }
    }

    /**
     * Lembar Voucher Bukti Kas Masuk (BKM) Siap Cetak
     */
    public function show(int $id): View
    {
        $bkm = $this->kasMasukService->getById($id);

        $revenueCoas = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) {
                $q->whereIn('kategori_akun', ['PENDAPATAN', 'MODAL', 'EKUITAS'])
                  ->orWhere('kode_akun', 'like', '3%')
                  ->orWhere('kode_akun', 'like', '4%')
                  ->orWhere('kode_akun', 'like', '6114%');
            })
            ->orderBy('kode_akun', 'asc')
            ->get();

        return view('keuangan.kas_masuk.show', compact('bkm', 'revenueCoas'));
    }

    /**
     * Batalkan / Void Transaksi BKM (Kurangi kembali saldo kas/bank terkait & batalkan jurnal jika POSTED)
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'alasan' => 'required|string|min:5|max:255',
        ], [
            'alasan.required' => 'Alasan pembatalan (void) wajib diisi.',
            'alasan.min'      => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        try {
            $bkm = $this->kasMasukService->voidKasMasuk($id, $request->input('alasan'), Auth::user());

            return redirect()->route('keuangan.kas_masuk.index')
                ->with('success', "Bukti Kas Masuk {$bkm->bkm_no} telah dibatalkan (void). Saldo kas/bank dan Jurnal GL terkait telah dibatalkan secara otomatis.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan BKM: ' . $e->getMessage());
        }
    }
}
