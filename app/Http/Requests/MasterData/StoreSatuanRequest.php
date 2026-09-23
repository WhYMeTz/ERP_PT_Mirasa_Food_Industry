<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreSatuanRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses penambahan satuan baru.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'satuan_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_satuan,satuan_cd',
            ],
            'satuan_nm' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * Pesan kustom dalam Bahasa Indonesia untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'satuan_cd.required' => 'Kode satuan wajib diisi.',
            'satuan_cd.string'   => 'Kode satuan harus berupa teks.',
            'satuan_cd.max'      => 'Kode satuan maksimal :max karakter.',
            'satuan_cd.unique'   => 'Kode satuan ini sudah terdaftar. Gunakan kode lain.',

            'satuan_nm.required' => 'Nama satuan wajib diisi.',
            'satuan_nm.string'   => 'Nama satuan harus berupa teks.',
            'satuan_nm.max'      => 'Nama satuan maksimal :max karakter.',
        ];
    }
}
