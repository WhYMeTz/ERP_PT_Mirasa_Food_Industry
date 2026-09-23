<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_barang_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_jenis_barang,jenis_barang_cd',
            ],
            'jenis_barang_nm' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_barang_cd.required' => 'Kode jenis barang wajib diisi.',
            'jenis_barang_cd.unique'   => 'Kode jenis barang ini sudah digunakan. Gunakan kode lain.',
            'jenis_barang_nm.required' => 'Nama jenis barang wajib diisi.',
        ];
    }
}
