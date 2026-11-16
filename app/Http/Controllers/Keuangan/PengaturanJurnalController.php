<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan\MstCoa;
use App\Services\Keuangan\PengaturanJurnalService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PengaturanJurnalController extends Controller
{
    public function __construct(
        protected PengaturanJurnalService $pengaturanService
    ) {}

    /**
     * Tampilkan halaman pengaturan pemetaan akun jurnal otomatis.
     */
    public function index(): View
    {
        $settingsGrouped = $this->pengaturanService->getAllGrouped();
        $coaList = MstCoa::active()
            ->orderBy('kode_akun', 'asc')
            ->get();

        return view('keuangan.pengaturan_jurnal.index', compact('settingsGrouped', 'coaList'));
    }

    /**
     * Simpan pembaruan pengaturan pemetaan akun dan template jurnal.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        try {
            $payload = $request->input('settings', []);
            $this->pengaturanService->updateBatch($payload, Auth::user());

            return redirect()->route('keuangan.pengaturan_jurnal.index')
                ->with('success', 'Pengaturan pemetaan akun jurnal otomatis berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pengaturan jurnal: ' . $e->getMessage());
        }
    }
}
