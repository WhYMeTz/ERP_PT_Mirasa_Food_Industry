<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Ambil daftar peran aktif (JSON)
     */
    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar peran berhasil diambil.',
            'data'    => $roles,
        ]);
    }

    /**
     * Tambah peran (role) baru
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'role_cd'          => ['required', 'string', 'max:50'],
            'role_nm'          => ['required', 'string', 'max:100'],
            'desc_txt'         => ['nullable', 'string'],
            'copy_from_role'   => ['nullable', 'string', 'max:50'],
        ], [
            'role_cd.required' => 'Kode peran wajib diisi (Contoh: SALES, OPERATOR_LAB).',
            'role_nm.required' => 'Nama peran wajib diisi (Contoh: Tim Sales & Marketing).',
        ]);

        try {
            $role = $this->roleService->storeRole($request->all(), $request->user()?->name);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Peran baru '{$role->role_nm}' ({$role->role_cd}) berhasil dibuat dan hak akses telah disiapkan.",
                    'data'    => $role,
                ], 201);
            }

            return redirect()
                ->back()
                ->with('success', "Peran baru '{$role->role_nm}' ({$role->role_cd}) berhasil ditambahkan! Anda kini dapat mengatur hak aksesnya.");
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Perbarui nama dan deskripsi peran
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'role_nm'  => ['required', 'string', 'max:100'],
            'desc_txt' => ['nullable', 'string'],
        ], [
            'role_nm.required' => 'Nama peran wajib diisi.',
        ]);

        try {
            $role = $this->roleService->updateRole($id, $request->all(), $request->user()?->name);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Data peran '{$role->role_nm}' berhasil diperbarui.",
                    'data'    => $role,
                ]);
            }

            return redirect()
                ->back()
                ->with('success', "Data peran '{$role->role_nm}' berhasil diperbarui.");
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus peran yang tidak digunakan
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        try {
            $this->roleService->deleteRole($id, $request->user()?->name);

            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Peran berhasil dinonaktifkan.',
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Peran berhasil dinonaktifkan.');
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
