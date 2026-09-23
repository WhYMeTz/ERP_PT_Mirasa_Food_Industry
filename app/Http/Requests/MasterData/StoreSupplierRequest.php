<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_supplier,supplier_cd',
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
            'supplier_cd.unique'         => 'Kode supplier ini sudah terdaftar. Gunakan kode lain.',
            'supplier_nm.required'       => 'Nama supplier wajib diisi.',
            'jenis_supplier_id.exists'   => 'Jenis supplier yang dipilih tidak valid.',
        ];
    }
}
