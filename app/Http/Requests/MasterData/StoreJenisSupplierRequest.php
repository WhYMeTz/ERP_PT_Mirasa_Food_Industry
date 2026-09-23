<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_supplier_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_jenis_supplier,jenis_supplier_cd',
            ],
            'jenis_supplier_nm' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_supplier_cd.required' => 'Kode jenis supplier wajib diisi.',
            'jenis_supplier_cd.unique'   => 'Kode jenis supplier ini sudah digunakan. Gunakan kode lain.',
            'jenis_supplier_nm.required' => 'Nama jenis supplier wajib diisi.',
        ];
    }
}
