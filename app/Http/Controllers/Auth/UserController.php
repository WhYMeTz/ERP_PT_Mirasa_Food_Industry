<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\MasterData\MstGudang;
use App\Services\Auth\UserService;
use App\Services\MasterData\KaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected KaryawanService $karyawanService
    ) {}

    public function index(Request $request, \App\Services\Auth\PermissionService $permissionService): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $role = $request->input('role');

        $userList = $this->userService->getAllPaginated($perPage, $search, $role);
        $karyawanList = $this->karyawanService->getAllActive();
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();
        $roleList = $this->userService->getAvailableRoles();
        $matrix = $permissionService->getPermissionMatrix();
        $modules = \App\Services\Auth\PermissionService::MODULES;

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data pengguna sistem berhasil diambil.',
                'data'    => $userList,
            ]);
        }

        return view('master_data.user.index', compact(
            'userList', 'karyawanList', 'gudangList', 'roleList', 'search', 'role',
            'matrix', 'modules'
        ));
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
    {
        $user = $this->userService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Akun pengguna {$user->name} berhasil dibuat.",
                'data'    => $user,
            ], 201);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun pengguna {$user->name} berhasil dibuat dengan role {$user->role_cd}.");
    }

    public function update(UpdateUserRequest $request, $id = null): RedirectResponse|JsonResponse
    {
        $userId = (int) ($request->route('pengguna_sistem') ?? $id ?? $request->input('id'));
        $user = $this->userService->update($userId, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Data akun {$user->name} berhasil diperbarui.",
                'data'    => $user,
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Data akun pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->userService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Akun pengguna berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dinonaktifkan.');
    }

    /**
     * Tampilkan antarmuka pengaturan matriks hak akses per role
     */
    public function permissions(\App\Services\Auth\PermissionService $permissionService): View
    {
        $matrix = $permissionService->getPermissionMatrix();
        $modules = \App\Services\Auth\PermissionService::MODULES;

        return view('master_data.user.permissions', compact('matrix', 'modules'));
    }

    /**
     * Simpan perubahan hak akses per role yang diatur Superadmin
     */
    public function updatePermissions(Request $request, \App\Services\Auth\PermissionService $permissionService): RedirectResponse
    {
        $singleRole = $request->input('single_role');
        $updatedBy = auth()->user()?->name ?? 'SUPERADMIN';

        if (!empty($singleRole)) {
            $allowedForRole = $request->input('permissions', []);
            $permissionService->updateRolePermissions($singleRole, $allowedForRole, $updatedBy);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Hak akses untuk peran '{$singleRole}' berhasil diperbarui dan langsung aktif!");
        }

        $permissionsData = $request->input('permissions', []);

        foreach (array_keys($permissionService->getPermissionMatrix()) as $roleCd) {
            $allowedForRole = $permissionsData[$roleCd] ?? [];
            $permissionService->updateRolePermissions($roleCd, $allowedForRole, $updatedBy);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengaturan hak akses peran berhasil diperbarui dan langsung aktif!');
    }
}
