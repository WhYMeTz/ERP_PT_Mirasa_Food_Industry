<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstLiniProduksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLiniProduksiRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses edit lini produksi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $liniParam = $this->route('master_lini_produksi') ?? $this->route('lini_produksi') ?? $this->route('id') ?? $this->input('lini_id');
        $liniId = $liniParam instanceof MstLiniProduksi ? $liniParam->lini_id : $liniParam;

        return [
            'lini_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_lini_produksi', 'lini_cd')->ignore($liniId, 'lini_id'),
            ],
            'lini_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'kategori_lini' => [
                'nullable',
                'string',
                'max:50',
            ],
            'tipe_batch' => [
                'required',
                'string',
                'in:IFM,REGULER',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'active_st' => [
                'nullable',
                'boolean',
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
            'lini_cd.required' => 'Kode lini wajib diisi.',
            'lini_cd.string'   => 'Kode lini harus berupa teks.',
            'lini_cd.max'      => 'Kode lini maksimal :max karakter.',
            'lini_cd.unique'   => 'Kode lini ini sudah digunakan oleh lini lain. Gunakan kode lain.',

            'lini_nm.required' => 'Nama lini / tujuan wajib diisi.',
            'lini_nm.string'   => 'Nama lini / tujuan harus berupa teks.',
            'lini_nm.max'      => 'Nama lini / tujuan maksimal :max karakter.',

            'tipe_batch.required' => 'Format penomoran batch wajib dipilih.',
            'tipe_batch.in'       => 'Format penomoran batch harus berupa IFM atau REGULER.',

            'keterangan.max' => 'Keterangan maksimal :max karakter.',
        ];
    }
}
