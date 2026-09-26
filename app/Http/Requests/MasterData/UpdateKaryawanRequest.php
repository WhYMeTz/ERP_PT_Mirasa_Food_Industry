<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('master_karyawan') ?? $this->route('karyawan') ?? $this->route('id') ?? $this->input('karyawan_id') ?? $this->input('id');

        return [
            'nik' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_karyawan', 'nik')->ignore($id, 'karyawan_id'),
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
            'active_st' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required'           => 'NIK karyawan wajib diisi.',
            'nik.unique'             => 'NIK ini sudah digunakan oleh karyawan lain.',
            'karyawan_nm.required'   => 'Nama lengkap karyawan wajib diisi.',
            'departemen_cd.required' => 'Departemen wajib dipilih.',
            'jabatan_nm.required'    => 'Jabatan kerja wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
        ];
    }
}
