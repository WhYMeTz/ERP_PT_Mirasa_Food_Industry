<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreCustomerRequest;
use App\Http\Requests\MasterData\UpdateCustomerRequest;
use App\Services\Common\CodeGeneratorService;
use App\Services\MasterData\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected CodeGeneratorService $codeGeneratorService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');

        $customers = $this->customerService->getAllPaginated($perPage, $search);
        $nextCustomerCode = $this->codeGeneratorService->generateCustomerCode();

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data master customer berhasil diambil.',
                'data'    => $customers,
                'next_code' => $nextCustomerCode,
            ]);
        }

        return view('master_data.customer.index', compact('customers', 'search', 'nextCustomerCode'));
    }

    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $customer = $this->customerService->store($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data customer berhasil disimpan.',
                'data'    => $customer,
            ], 201);
        }

        return redirect()
            ->route('master.customer.index')
            ->with('success', 'Data customer berhasil disimpan.');
    }

    public function update(UpdateCustomerRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $customer = $this->customerService->update($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data customer berhasil diperbarui.',
                'data'    => $customer,
            ]);
        }

        return redirect()
            ->route('master.customer.index')
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->customerService->delete($id);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data customer berhasil dinonaktifkan.',
            ]);
        }

        return redirect()
            ->route('master.customer.index')
            ->with('success', 'Data customer berhasil dinonaktifkan.');
    }
}
