<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstJenisBarang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJenisBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jenisParam = $this->route('master_jeni') ?? $this->route('jenis') ?? $this->route('id') ?? $this->input('jenis_barang_id');
        $jenisId = $jenisParam instanceof MstJenisBarang ? $jenisParam->jenis_barang_id : $jenisParam;

        return [
            'jenis_barang_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_jenis_barang', 'jenis_barang_cd')->ignore($jenisId, 'jenis_barang_id'),
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
