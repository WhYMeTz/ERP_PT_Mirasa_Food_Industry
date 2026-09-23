<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\Common\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodeGeneratorController extends Controller
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $code = '';

        switch ($type) {
            case 'supplier':
                $code = $this->codeGeneratorService->generateSupplierCode();
                break;
            case 'customer':
                $code = $this->codeGeneratorService->generateCustomerCode();
                break;
            case 'gudang':
                $tipe = $request->query('tipe');
                $code = $this->codeGeneratorService->generateGudangCode($tipe);
                break;
            case 'barang':
                $jenis = $request->query('jenis');
                $code = $this->codeGeneratorService->generateBarangCode($jenis);
                break;
            default:
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tipe entitas kode tidak dikenal.',
                ], 400);
        }

        return response()->json([
            'status' => 'success',
            'code'   => $code,
        ]);
    }
}
