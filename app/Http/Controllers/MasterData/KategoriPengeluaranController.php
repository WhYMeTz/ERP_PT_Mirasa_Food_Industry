<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MstKategoriPengeluaran;
use App\Services\MasterData\KategoriPengeluaranService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriPengeluaranController extends Controller
{
    /**
     * Injeksi KategoriPengeluaranService.
     */
    public function __construct(
        protected KategoriPengeluaranService $kategoriService
    ) {}

    /**
     * Menampilkan daftar seluruh master kategori pengeluaran.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canAccessKategoriPengeluaran(), 403, 'Anda tidak memiliki hak akses untuk melihat master kategori pengeluaran.');

        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status', 'SEMUA');

        $kategoriList = $this->kategoriService->getAllPaginated($perPage, $search, $status);
        $summary = $this->kategoriService->getSummaryCounts();
        $coaList = $this->kategoriService->getCoaBebanList();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master kategori pengeluaran berhasil dimuat.',
                'summary' => $summary,
                'data'    => $kategoriList,
            ]);
        }

        return view('master_data.kategori_pengeluaran.index', compact(
            'kategoriList',
            'summary',
            'coaList',
            'search',
            'status'
        ));
    }

    /**
     * Menyimpan data master kategori pengeluaran baru.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canCreateMasterKategoriPengeluaran(), 403, 'Anda tidak memiliki hak akses untuk menambah kategori pengeluaran.');

        $validated = $request->validate([
            'kode_kategori'  => [
                'required',
                'string',
                'max:30',
                Rule::unique('mst_kategori_pengeluaran', 'kode_kategori')->where(fn ($q) => $q->where('deleted_st', false)),
            ],
            'nama_kategori'  => ['required', 'string', 'max:100'],
            'coa_id_default' => ['nullable', 'exists:mst_coa,coa_id'],
            'keterangan'     => ['nullable', 'string', 'max:255'],
            'active_st'      => ['nullable', 'boolean'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'   => 'Kode kategori sudah terdaftar dalam sistem.',
            'nama_kategori.required' => 'Nama kategori pengeluaran wajib diisi.',
            'coa_id_default.exists'  => 'Akun COA beban yang dipilih tidak valid.',
        ]);

        try {
            $kategori = $this->kategoriService->store($validated);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Kategori '{$kategori->nama_kategori}' ({$kategori->kode_kategori}) berhasil ditambahkan.",
                    'data'    => $kategori,
                ], 201);
            }

            return redirect()
                ->route('master.kategori_pengeluaran.index')
                ->with('success', "Kategori '{$kategori->nama_kategori}' ({$kategori->kode_kategori}) berhasil ditambahkan.");
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Mengambil detail satu data kategori (JSON untuk Modal Edit atau API).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canAccessKategoriPengeluaran(), 403, 'Akses ditolak.');

        $kategori = $this->kategoriService->getById($id);

        return response()->json([
            'status' => 'success',
            'data'   => $kategori,
        ]);
    }

    /**
     * Memperbarui data kategori pengeluaran.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canEditMasterKategoriPengeluaran(), 403, 'Anda tidak memiliki hak akses untuk mengubah kategori pengeluaran.');

        $validated = $request->validate([
            'kode_kategori'  => [
                'required',
                'string',
                'max:30',
                Rule::unique('mst_kategori_pengeluaran', 'kode_kategori')
                    ->ignore($id, 'kategori_id')
                    ->where(fn ($q) => $q->where('deleted_st', false)),
            ],
            'nama_kategori'  => ['required', 'string', 'max:100'],
            'coa_id_default' => ['nullable', 'exists:mst_coa,coa_id'],
            'keterangan'     => ['nullable', 'string', 'max:255'],
            'active_st'      => ['nullable', 'boolean'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique'   => 'Kode kategori sudah digunakan oleh kategori lain.',
            'nama_kategori.required' => 'Nama kategori pengeluaran wajib diisi.',
            'coa_id_default.exists'  => 'Akun COA beban yang dipilih tidak valid.',
        ]);

        try {
            $kategori = $this->kategoriService->update($id, $validated);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Kategori '{$kategori->nama_kategori}' berhasil diperbarui.",
                    'data'    => $kategori,
                ]);
            }

            return redirect()
                ->route('master.kategori_pengeluaran.index')
                ->with('success', "Kategori '{$kategori->nama_kategori}' ({$kategori->kode_kategori}) berhasil diperbarui.");
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Menghapus (Soft Delete) data kategori pengeluaran.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canDeleteMasterKategoriPengeluaran(), 403, 'Anda tidak memiliki hak akses untuk menghapus kategori pengeluaran.');

        try {
            $kategori = $this->kategoriService->getById($id);
            $nama = $kategori->nama_kategori;
            $kode = $kategori->kode_kategori;

            $this->kategoriService->delete($id);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Kategori '{$nama}' ({$kode}) berhasil dihapus.",
                    'data'    => null,
                ]);
            }

            return redirect()
                ->route('master.kategori_pengeluaran.index')
                ->with('success', "Kategori '{$nama}' ({$kode}) berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mengubah status aktif / nonaktif (toggle) kategori pengeluaran.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        abort_unless($user && $user->canEditMasterKategoriPengeluaran(), 403, 'Anda tidak memiliki hak akses untuk mengubah status kategori pengeluaran.');

        try {
            $kategori = $this->kategoriService->toggleStatus($id);
            $statusText = $kategori->active_st ? 'diaktifkan' : 'dinonaktifkan';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'   => true,
                    'message'   => "Kategori '{$kategori->nama_kategori}' berhasil {$statusText}.",
                    'active_st' => (bool) $kategori->active_st,
                ]);
            }

            return redirect()
                ->back()
                ->with('success', "Status kategori '{$kategori->nama_kategori}' berhasil {$statusText}.");
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
