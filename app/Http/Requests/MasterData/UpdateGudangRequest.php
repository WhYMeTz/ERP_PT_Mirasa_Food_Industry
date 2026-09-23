<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstGudang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGudangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gudangParam = $this->route('master_gudang') ?? $this->route('gudang') ?? $this->route('id') ?? $this->input('gudang_id');
        $gudangId = $gudangParam instanceof MstGudang ? $gudangParam->gudang_id : $gudangParam;

        return [
            'gudang_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_gudang', 'gudang_cd')->ignore($gudangId, 'gudang_id'),
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
