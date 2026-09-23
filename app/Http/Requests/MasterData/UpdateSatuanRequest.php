<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstSatuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSatuanRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses edit satuan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $satuanParam = $this->route('master_satuan') ?? $this->route('satuan') ?? $this->route('id') ?? $this->input('satuan_id');
        $satuanId = $satuanParam instanceof MstSatuan ? $satuanParam->satuan_id : $satuanParam;

        return [
            'satuan_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_satuan', 'satuan_cd')->ignore($satuanId, 'satuan_id'),
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
            'satuan_cd.unique'   => 'Kode satuan ini sudah digunakan oleh satuan lain. Gunakan kode lain.',

            'satuan_nm.required' => 'Nama satuan wajib diisi.',
            'satuan_nm.string'   => 'Nama satuan harus berupa teks.',
            'satuan_nm.max'      => 'Nama satuan maksimal :max karakter.',
        ];
    }
}
