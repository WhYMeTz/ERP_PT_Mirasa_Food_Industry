<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => [
                'nullable',
                'string',
                'max:50',
                'unique:mst_karyawan,nik',
            ],
            'karyawan_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'departemen_cd' => [
                'required',
                'string',
                'max:50',
            ],
            'jabatan_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'telepon_no' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
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
            'nik.unique'             => 'NIK ini sudah terdaftar. Gunakan NIK lain.',
            'karyawan_nm.required'   => 'Nama lengkap karyawan wajib diisi.',
            'departemen_cd.required' => 'Departemen wajib dipilih.',
            'jabatan_nm.required'    => 'Jabatan kerja wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
        ];
    }
}
