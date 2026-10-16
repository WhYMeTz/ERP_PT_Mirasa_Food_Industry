<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MstKasBank;
use App\Models\MasterData\MstCustomer;
use App\Services\Keuangan\KasMasukService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Exception;

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
        $filters = [
            'kas_bank_id'   => $request->input('kas_bank_id'),
            'kategori'      => $request->input('kategori'),
            'metode_terima' => $request->input('metode_terima'),
            'search'        => $request->input('search'),
            'tgl_mulai'     => $request->input('tgl_mulai'),
            'tgl_selesai'   => $request->input('tgl_selesai'),
        ];

        $bkmList = $this->kasMasukService->getKasMasukListPaginated($filters, 15);
        $kpi = $this->kasMasukService->getKpiMetrics();
        $kasBanks = MstKasBank::active()->orderBy('nama_akun')->get();

        return view('keuangan.kas_masuk.index', compact('bkmList', 'kpi', 'kasBanks', 'filters'));
    }

    /**
     * Form Input Penerimaan Kas / Bank Baru (BKM)
     */
    public function create(Request $request): View
    {
        $kasBanks = MstKasBank::active()->orderBy('nama_akun')->get();
        $customers = MstCustomer::active()->orderBy('customer_nm')->get();

        $defaultKategori = $request->input('kategori', 'PENJUALAN_LIMBAH');

        return view('keuangan.kas_masuk.create', compact('kasBanks', 'customers', 'defaultKategori'));
    }

    /**
     * Simpan Transaksi Bukti Kas Masuk & Otomatis Tambah Saldo Kas/Bank
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
            'kategori_penerimaan' => 'required|string|in:PENJUALAN_LIMBAH,PENJUALAN_TUNAI,SETORAN_MODAL,PELUNASAN_PIUTANG,PENDAPATAN_LAIN',
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

            return redirect()->route('keuangan.kas_masuk.show', $bkm->bkm_id)
                ->with('success', "Bukti Kas Masuk {$bkm->bkm_no} berhasil dicatat sebesar Rp " . number_format($bkm->total_terima, 0, ',', '.') . " dan saldo rekening tujuan telah bertambah.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mencatat Kas Masuk: ' . $e->getMessage());
        }
    }

    /**
     * Lembar Voucher Bukti Kas Masuk (BKM) Siap Cetak
     */
    public function show(int $id): View
    {
        $bkm = $this->kasMasukService->getById($id);

        return view('keuangan.kas_masuk.show', compact('bkm'));
    }

    /**
     * Batalkan / Void Transaksi BKM (Kurangi kembali saldo kas/bank terkait)
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
                ->with('success', "Bukti Kas Masuk {$bkm->bkm_no} telah dibatalkan (void). Saldo kas/bank terkait telah disesuaikan kembali.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan BKM: ' . $e->getMessage());
        }
    }
}
