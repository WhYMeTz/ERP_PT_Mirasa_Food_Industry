<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreGudangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gudang_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_gudang,gudang_cd',
            ],
            'gudang_nm' => [    
                'required',
                'string',
                'max:100',
            ],
            'tipe_gudang_cd' => [
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
            'gudang_cd.required' => 'Kode gudang wajib diisi.',
            'gudang_cd.unique'   => 'Kode gudang ini sudah digunakan. Gunakan kode lain.',
            'gudang_nm.required' => 'Nama gudang wajib diisi.',
        ];
    }
}
