<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstSupplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierParam = $this->route('master_supplier') ?? $this->route('supplier') ?? $this->route('id') ?? $this->input('supplier_id');
        $supplierId = $supplierParam instanceof MstSupplier ? $supplierParam->supplier_id : $supplierParam;

        return [
            'supplier_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_supplier', 'supplier_cd')->ignore($supplierId, 'supplier_id'),
            ],
            'supplier_nm' => [
                'required',
                'string',
                'max:150',
            ],
            'jenis_supplier_id' => [
                'nullable',
                'integer',
                'exists:mst_jenis_supplier,jenis_supplier_id',
            ],
            'kontak_no' => [
                'nullable',
                'string',
                'max:50',
            ],
            'alamat_txt' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_cd.required'       => 'Kode supplier wajib diisi.',
            'supplier_cd.unique'         => 'Kode supplier ini sudah digunakan oleh supplier lain. Gunakan kode lain.',
            'supplier_nm.required'       => 'Nama supplier wajib diisi.',
            'jenis_supplier_id.exists'   => 'Jenis supplier yang dipilih tidak valid.',
        ];
    }
}
