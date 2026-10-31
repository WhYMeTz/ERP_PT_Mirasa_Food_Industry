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

    public function index(Request $request, \App\Services\Auth\PermissionService $permissionService, \App\Services\Auth\RoleService $roleService): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $role = $request->input('role');

        $userList = $this->userService->getAllPaginated($perPage, $search, $role);
        $karyawanList = $this->karyawanService->getAllActive();
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();
        $roleList = $this->userService->getAvailableRoles();
        $allRoles = $roleService->getAllRoles();
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
            'userList', 'karyawanList', 'gudangList', 'roleList', 'allRoles', 'search', 'role',
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
     * Tampilkan detail lengkap akun pengguna untuk modal popup
     * Kata sandi akun HANYA didekripsi jika pengakses adalah Superadmin
     */
    public function show(Request $request, $id = null, ?\App\Services\Auth\PermissionService $permissionService = null): JsonResponse
    {
        $userId = (int) ($request->route('pengguna_sistem') ?? $id ?? $request->input('id'));
        $user = $this->userService->getById($userId);
        $permissionService = $permissionService ?? app(\App\Services\Auth\PermissionService::class);
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;

        $passwordPlain = null;
        if ($isSuperAdmin) {
            if (!empty($user->password_plain)) {
                try {
                    $passwordPlain = \Illuminate\Support\Facades\Crypt::decryptString($user->password_plain);
                } catch (\Throwable $e) {
                    $passwordPlain = 'password123';
                }
            } else {
                $passwordPlain = 'password123';
            }
        }

        // Ambil izin role
        $rolePermissions = $permissionService->getPermissionsForRole($user->role_cd);

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail akun berhasil dimuat.',
            'data'    => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role_cd'           => $user->role_cd,
                'role_desc'         => \App\Services\Auth\UserService::ROLES[$user->role_cd] ?? $user->role_cd,
                'active_st'         => (bool) $user->active_st,
                'karyawan'          => $user->karyawan ? [
                    'id'         => $user->karyawan->karyawan_id,
                    'nik'        => $user->karyawan->nik,
                    'nama'       => $user->karyawan->karyawan_nm,
                    'departemen' => $user->karyawan->departemen_cd,
                ] : null,
                'gudangs'           => $user->assignedGudangs->map(fn($g) => [
                    'id'   => $g->gudang_id,
                    'kode' => $g->gudang_cd,
                    'nama' => $g->display_name ?? $g->gudang_nm,
                ]),
                'default_gudang'    => $user->gudang ? ($user->gudang->display_name ?? $user->gudang->gudang_nm) : null,
                'created_at'        => $user->created_at?->format('d M Y H:i') ?? '-',
                'updated_at'        => $user->updated_at?->format('d M Y H:i') ?? '-',
                'signature_path'    => $user->signature_path,
                'signature_url'     => $user->signature_url,
                'can_view_password' => $isSuperAdmin,
                'password'          => $passwordPlain,
                'permissions'       => $rolePermissions,
            ],
        ]);
    }

    /**
     * Tampilkan antarmuka pengaturan matriks hak akses per role
     */
    public function permissions(\App\Services\Auth\PermissionService $permissionService, \App\Services\Auth\RoleService $roleService): View
    {
        $matrix = $permissionService->getPermissionMatrix();
        $modules = \App\Services\Auth\PermissionService::MODULES;
        $allRoles = $roleService->getAllRoles();

        return view('master_data.user.permissions', compact('matrix', 'modules', 'allRoles'));
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
