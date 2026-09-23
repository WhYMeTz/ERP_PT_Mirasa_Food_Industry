<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstJenisSupplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJenisSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jenisParam = $this->route('master_jenis_supplier') ?? $this->route('jenis_supplier') ?? $this->route('id') ?? $this->input('jenis_supplier_id');
        $jenisId = $jenisParam instanceof MstJenisSupplier ? $jenisParam->jenis_supplier_id : $jenisParam;

        return [
            'jenis_supplier_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_jenis_supplier', 'jenis_supplier_cd')->ignore($jenisId, 'jenis_supplier_id'),
            ],
            'jenis_supplier_nm' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_supplier_cd.required' => 'Kode jenis supplier wajib diisi.',
            'jenis_supplier_cd.unique'   => 'Kode jenis supplier ini sudah digunakan. Gunakan kode lain.',
            'jenis_supplier_nm.required' => 'Nama jenis supplier wajib diisi.',
        ];
    }
}
