<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePajakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('master_pajak') ?? $this->route('id') ?? $this->input('pajak_id');

        return [
            'pajak_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'pajak_cd' => [
                'required',
                'string',
                'max:30',
                Rule::unique('mst_pajak', 'pajak_cd')->ignore($id, 'pajak_id'),
            ],
            'tarif_persen' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'sifat_pajak' => [
                'nullable',
                'string',
                'in:TAMBAH,POTONG',
            ],
            'is_pembelian' => [
                'nullable',
                'boolean',
            ],
            'coa_pembelian_id' => [
                'nullable',
                'integer',
                'exists:mst_coa,coa_id',
            ],
            'is_penjualan' => [
                'nullable',
                'boolean',
            ],
            'coa_penjualan_id' => [
                'nullable',
                'integer',
                'exists:mst_coa,coa_id',
            ],
            'coa_id' => [
                'nullable',
                'integer',
                'exists:mst_coa,coa_id',
            ],
            'keterangan' => [
                'nullable',
                'string',
            ],
            'active_st' => [
                'nullable',
                'boolean',
            ],
            'is_default' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'pajak_cd.required'       => 'Kode pajak wajib diisi.',
            'pajak_cd.unique'         => 'Kode pajak ini sudah digunakan oleh data lain.',
            'pajak_nm.required'       => 'Nama pajak wajib diisi.',
            'kategori_pajak.required' => 'Kategori pajak wajib dipilih.',
            'sifat_pajak.required'    => 'Sifat perlakuan pajak wajib dipilih.',
            'tarif_persen.required'   => 'Tarif persentase pajak wajib diisi.',
            'tarif_persen.numeric'    => 'Tarif pajak harus berupa angka numerik.',
            'tarif_persen.min'        => 'Tarif pajak minimal 0%.',
            'tarif_persen.max'        => 'Tarif pajak maksimal 100%.',
            'coa_id.exists'           => 'Akun COA yang dipilih tidak valid di sistem.',
        ];
    }
}
