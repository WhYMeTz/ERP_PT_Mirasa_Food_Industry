<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keuangan\StoreJurnalManualRequest;
use App\Models\Keuangan\MstCoa;
use App\Services\Keuangan\JurnalService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JurnalController extends Controller
{
    public function __construct(
        protected JurnalService $jurnalService
    ) {}

    /**
     * Halaman Utama Jurnal Umum (General Journal Ledger)
     */
    public function index(Request $request): View
    {
        $filters = [
            'search'         => $request->input('search'),
            'tipe_transaksi' => $request->input('tipe_transaksi'),
            'start_date'     => $request->input('start_date'),
            'end_date'       => $request->input('end_date'),
            'coa_id'         => $request->input('coa_id'),
        ];

        $jurnals = $this->jurnalService->getAllPaginated($filters, 20);
        $metrics = $this->jurnalService->getSummaryMetrics($filters);
        $coaList = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('kode_akun', 'asc')
            ->get();

        $tipeTransaksiList = [
            'HUTANG_PEMBELIAN'    => 'Hutang Pembelian (GRN)',
            'BAYAR_HUTANG'        => 'Pelunasan Hutang (BKK)',
            'KAS_MASUK'           => 'Kas Masuk (BKM)',
            'PEMAKAIAN_PRODUKSI'  => 'Pemakaian Bahan (BPPB)',
            'JURNAL_UMUM'         => 'Jurnal Umum Manual',
            'JURNAL_PENYESUAIAN'  => 'Jurnal Penyesuaian (Adj)',
            'SALDO_AWAL'          => 'Saldo Awal Akuntansi',
        ];

        return view('keuangan.jurnal.index', compact(
            'jurnals',
            'metrics',
            'coaList',
            'tipeTransaksiList',
            'filters'
        ));
    }

    /**
     * Formulir Input Jurnal Umum Manual
     */
    public function create(): View
    {
        $coaList = MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('kode_akun', 'asc')
            ->get();

        $tipeList = [
            'JURNAL_UMUM'        => 'Jurnal Umum (Beban Operasional, Gaji, dll)',
            'JURNAL_PENYESUAIAN' => 'Jurnal Penyesuaian (Penyusutan, Akrual)',
            'SALDO_AWAL'         => 'Saldo Awal Akuntansi',
        ];

        return view('keuangan.jurnal.create', compact('coaList', 'tipeList'));
    }

    /**
     * Simpan Entri Jurnal Umum Manual
     */
    public function store(StoreJurnalManualRequest $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            $hdr = $this->jurnalService->catatJurnal(
                tipeTransaksi: $validated['tipe_transaksi'],
                tglJurnal: $validated['tgl_jurnal'],
                refNo: $validated['ref_no'] ?? null,
                refId: null,
                keterangan: $validated['keterangan'],
                entries: $validated['entries'],
                user: $user
            );

            return redirect()
                ->route('keuangan.jurnal.index')
                ->with('success', "Jurnal manual '{$hdr->jurnal_no}' berhasil disimpan dan dibukukan ke Buku Besar.");
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membukukan jurnal: ' . $e->getMessage());
        }
    }

    /**
     * Detail Jurnal (AJAX / JSON)
     */
    public function show(int $id, Request $request): View|JsonResponse
    {
        $jurnal = $this->jurnalService->getById($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data'   => $jurnal,
            ]);
        }

        return view('keuangan.jurnal.show', compact('jurnal'));
    }
}
