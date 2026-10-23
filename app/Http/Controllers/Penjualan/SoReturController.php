<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MstCustomer;
use App\Models\MasterData\MstGudang;
use App\Models\Penjualan\DatSoReturHdr;
use App\Services\Penjualan\SoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SoReturController extends Controller
{
    public function __construct(
        protected SoService $soService
    ) {}

    /**
     * Halaman indeks daftar seluruh dokumen Retur Penjualan (Customer Returns).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $filters = [
            'customer_id' => $request->query('customer_id'),
            'gudang_id'   => $request->query('gudang_id'),
            'tindakan_cd' => $request->query('tindakan_cd'),
            'status_cd'   => $request->query('status_cd'),
            'date_from'   => $request->query('date_from'),
            'date_to'     => $request->query('date_to'),
        ];

        $returns = $this->soService->getReturnsPaginated(15, $search, $filters);
        $kpi = $this->soService->getReturnKpi();

        $customers = MstCustomer::active()->orderBy('customer_nm')->get(['customer_id', 'customer_cd', 'customer_nm']);
        $gudangs = MstGudang::active()->orderBy('gudang_nm')->get(['gudang_id', 'gudang_cd', 'gudang_nm']);

        return view('penjualan.retur.index', compact('returns', 'kpi', 'customers', 'gudangs', 'search', 'filters'));
    }

    /**
     * Tampilan detail dokumen retur penjualan (diarahkan ke detail SO bersangkutan).
     */
    public function show(int $id): RedirectResponse
    {
        $retur = DatSoReturHdr::findOrFail($id);
        return redirect()->to(route('penjualan.so.show', $retur->so_id) . '#retur-penjualan');
    }

    /**
     * Membatalkan dokumen retur penjualan (Void) dan menyesuaikan stok kembali.
     */
    public function void(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Alasan pembatalan retur wajib diisi.',
        ]);

        try {
            $retur = $this->soService->voidSalesReturn($id, $request->input('reason'), auth()->user()?->nama_lengkap);

            return back()->with('toast_success', "Dokumen Retur {$retur->retur_no} berhasil dibatalkan (VOID) dan stok fisik telah disesuaikan kembali.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }

    /**
     * Export Berita Acara Retur Penjualan resmi ke format PDF.
     */
    public function exportPdf(int $id): Response
    {
         $retur = DatSoReturHdr::with(['order', 'customer', 'gudang', 'details.barang'])->findOrFail($id);

         $logoPath = public_path('images/logo.png');
         $logoBase64 = null;
         if (file_exists($logoPath)) {
             $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
         }

         $pdf = Pdf::loadView('penjualan.retur.pdf_berita_acara', [
             'retur'      => $retur,
             'logoBase64' => $logoBase64,
             'printedAt'  => now()->translatedFormat('d F Y H:i'),
             'printedBy'  => auth()->user()?->karyawan?->karyawan_nm ?? (auth()->user()?->nama_lengkap ?? 'Administrator'),
         ]);

         $pdf->setPaper('a4', 'portrait');

         $fileName = 'BERITA_ACARA_RETUR_' . preg_replace('/[^A-Za-z0-9\-]/', '', $retur->retur_no) . '.pdf';
         return $pdf->stream($fileName);
     }
}
