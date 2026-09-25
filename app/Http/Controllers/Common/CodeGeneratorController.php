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
        $name = $request->query('name');
        $code = '';

        switch ($type) {
            case 'supplier':
                $code = $this->codeGeneratorService->generateSupplierCode($name);
                break;
            case 'customer':
                $code = $this->codeGeneratorService->generateCustomerCode($name);
                break;
            case 'gudang':
                $tipe = $request->query('tipe');
                $code = $this->codeGeneratorService->generateGudangCode($tipe, $name);
                break;
            case 'barang':
                $jenis = $request->query('jenis');
                $code = $this->codeGeneratorService->generateBarangCode($jenis);
                break;
            case 'batch':
                $barangId = $request->query('barang_id');
                $date = $request->query('date', date('Y-m-d'));
                $exclude = $request->query('exclude', '');
                $excludeArray = array_filter(array_map('trim', explode(',', (string) $exclude)));

                $barang = $barangId ? \App\Models\MasterData\MstBarang::find($barangId) : null;
                $code = $this->codeGeneratorService->generateBatchNo(
                    $barang?->barang_cd ?? $request->query('code'),
                    $date,
                    $barang?->barang_nm ?? $name,
                    $excludeArray
                );
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
