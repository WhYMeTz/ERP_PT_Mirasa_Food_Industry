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

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $role = $request->input('role');

        $userList = $this->userService->getAllPaginated($perPage, $search, $role);
        $karyawanList = $this->karyawanService->getAllActive();
        $gudangList = MstGudang::active()->orderBy('gudang_nm')->get();
        $roleList = $this->userService->getAvailableRoles();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data pengguna sistem berhasil diambil.',
                'data'    => $userList,
            ]);
        }

        return view('master_data.user.index', compact('userList', 'karyawanList', 'gudangList', 'roleList', 'search', 'role'));
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

    public function update(UpdateUserRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

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
}
