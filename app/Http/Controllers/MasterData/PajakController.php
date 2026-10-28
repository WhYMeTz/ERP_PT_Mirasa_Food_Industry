<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StorePajakRequest;
use App\Http\Requests\MasterData\UpdatePajakRequest;
use App\Models\Keuangan\MstCoa;
use App\Services\MasterData\PajakService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PajakController extends Controller
{
    /**
     * Injeksi dependensi PajakService via Constructor.
     */
    public function __construct(
        protected PajakService $pajakService
    ) {}

    /**
     * Menampilkan daftar master pajak dan tarif.
     */
    public function index(Request $request): View|JsonResponse
    {
        abort_unless(auth()->user()->canAccessPajak(), 403, 'Anda tidak memiliki hak akses untuk melihat data master pajak.');

        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $kategori = $request->input('kategori', 'SEMUA');
        $status = $request->input('status', 'SEMUA');

        $pajakList = $this->pajakService->getAllPaginated($perPage, $search, $kategori, $status);
        $summary = $this->pajakService->getSummaryCounts();
        $coaList = MstCoa::where('active_st', true)->where('deleted_st', false)->orderBy('kode_akun')->get();

        $kategoriOptions = [
            'PPN'            => 'PPN (Pajak Pertambahan Nilai)',
            'PPH_23'         => 'PPh 23 (Jasa & Sewa Alat)',
            'PPH_4_2'        => 'PPh 4(2) (Sewa Gedung/Final)',
            'PPH_21'         => 'PPh 21 (Tenaga Ahli/Karyawan)',
            'PPH_22'         => 'PPh 22 (Komoditas/Bahan Baku)',
            'BEBAS_PAJAK'    => 'Bebas Pajak (Non-PPN)',
            'LAINNYA'        => 'Pajak Lainnya',
        ];

        $sifatOptions = [
            'TAMBAH' => 'TAMBAH (Menambah Tagihan)',
            'POTONG' => 'POTONG (Memotong Pembayaran)',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master pajak berhasil dimuat.',
                'summary' => $summary,
                'data'    => $pajakList,
            ]);
        }

        return view('master_data.pajak.index', compact('pajakList', 'summary', 'coaList', 'search', 'kategori', 'status', 'kategoriOptions', 'sifatOptions'));
    }

    /**
     * Menyimpan data master pajak baru.
     */
    public function store(StorePajakRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user() ?? $request->user();
        abort_unless($user && $user->canCreateMasterPajak(), 403, 'Anda tidak memiliki hak akses untuk menambah data pajak.');

        try {
            $pajak = $this->pajakService->store($request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Pajak '{$pajak->pajak_nm}' ({$pajak->tarif_persen}%) berhasil ditambahkan.",
                    'data'    => $pajak,
                ], 201);
            }

            return redirect()
                ->route('master.pajak.index')
                ->with('success', "Tarif Pajak '{$pajak->pajak_nm}' ({$pajak->pajak_cd}) berhasil ditambahkan ke sistem.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Mengambil detail satu data pajak (JSON untuk modal edit).
     */
    public function edit(int $id): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canEditMasterPajak(), 403, 'Anda tidak memiliki hak akses untuk mengubah data pajak.');

        $pajak = $this->pajakService->getById($id);

        return response()->json([
            'status' => 'success',
            'data'   => $pajak,
        ]);
    }

    /**
     * Memperbarui data master pajak.
     */
    public function update(UpdatePajakRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user() ?? $request->user();
        abort_unless($user && $user->canEditMasterPajak(), 403, 'Anda tidak memiliki hak akses untuk mengubah data pajak.');

        try {
            $pajak = $this->pajakService->update($id, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Pajak '{$pajak->pajak_nm}' berhasil diperbarui.",
                    'data'    => $pajak,
                ]);
            }

            return redirect()
                ->route('master.pajak.index')
                ->with('success', "Perubahan data pajak '{$pajak->pajak_nm}' berhasil disimpan.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle status aktif / nonaktif master pajak.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user() ?? $request->user();
        abort_unless($user && $user->canEditMasterPajak(), 403, 'Anda tidak memiliki hak akses untuk mengubah status pajak.');

        try {
            $pajak = $this->pajakService->toggleStatus($id);
            $statusText = $pajak->active_st ? 'diaktifkan kembali' : 'dinonaktifkan';

            if ($request->wantsJson()) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => "Pajak '{$pajak->pajak_nm}' berhasil {$statusText}.",
                    'active_st' => $pajak->active_st,
                ]);
            }

            return redirect()
                ->back()
                ->with('success', "Status pajak '{$pajak->pajak_nm}' berhasil {$statusText}.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menghapus (Soft Delete) data master pajak.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user() ?? $request->user();
        abort_unless($user && $user->canDeleteMasterPajak(), 403, 'Anda tidak memiliki hak akses untuk menghapus data pajak.');

        try {
            $this->pajakService->delete($id);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tarif pajak berhasil dinonaktifkan dari sistem.',
                ]);
            }

            return redirect()
                ->route('master.pajak.index')
                ->with('success', 'Tarif pajak berhasil dinonaktifkan dari sistem.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
