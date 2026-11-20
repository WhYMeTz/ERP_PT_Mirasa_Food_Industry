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
            'klasifikasi' => [
                'required',
                'string',
                'in:IFM,MANUAL,IDM_ALFA,IDM,MIDI_ALFA',
            ],
            'wilayah' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kode_pelanggan' => [
                'nullable',
                'string',
                'max:50',
            ],
            'top_txt' => [
                'nullable',
                'string',
                'max:50',
            ],
            'default_ppn' => [
                'nullable',
                'string',
                'in:PPN_11,NON_PPN',
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
