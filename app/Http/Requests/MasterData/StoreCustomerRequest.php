<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_customer,customer_cd',
            ],
            'customer_nm' => [
                'required',
                'string',
                'max:150',
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
            'customer_cd.required' => 'Kode customer wajib diisi.',
            'customer_cd.unique'   => 'Kode customer ini sudah terdaftar. Gunakan kode lain.',
            'customer_nm.required' => 'Nama customer wajib diisi.',
        ];
    }
}
