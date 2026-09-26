<?php

namespace App\Http\Requests\Produksi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $bomParam = $this->route('master_resep')
            ?? $this->route('resep')
            ?? $this->input('bom_id');

        $bomId = is_object($bomParam) ? $bomParam->bom_id : (int) $bomParam;

        return [
            'bom_no'                    => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('mst_bom_hdr', 'bom_no')->ignore($bomId, 'bom_id'),
            ],
            'bom_nm'                    => 'required|string|max:150',
            'barang_jadi_id'            => 'required|integer|exists:mst_barang,barang_id',
            'batch_ukuran_qty'          => 'required|numeric|min:0.0001',
            'catatan_txt'               => 'nullable|string|max:500',
            'items'                     => 'required|array|min:1',
            'items.*.barang_mentah_id'  => 'required|integer|exists:mst_barang,barang_id',
            'items.*.kebutuhan_qty'     => 'required|numeric|min:0.0001',
            'items.*.catatan_txt'       => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'bom_no.unique'                     => 'Nomor Resep / BOM sudah terdaftar di sistem.',
            'bom_nm.required'                   => 'Nama Formula Resep wajib diisi.',
            'barang_jadi_id.required'           => 'Produk Jadi (Output) wajib dipilih.',
            'barang_jadi_id.exists'             => 'Produk Jadi tidak valid.',
            'batch_ukuran_qty.required'         => 'Ukuran Batch Standar wajib diisi.',
            'batch_ukuran_qty.min'              => 'Ukuran Batch Standar minimal 0.0001.',
            'items.required'                    => 'Minimal harus ada 1 bahan baku / penolong dalam resep.',
            'items.min'                         => 'Minimal harus ada 1 bahan baku / penolong dalam resep.',
            'items.*.barang_mentah_id.required' => 'Bahan Baku / Penolong wajib dipilih.',
            'items.*.barang_mentah_id.exists'   => 'Bahan Baku tidak terdaftar di sistem.',
            'items.*.kebutuhan_qty.required'    => 'Kuantitas takaran kebutuhan wajib diisi.',
            'items.*.kebutuhan_qty.min'         => 'Kuantitas takaran kebutuhan minimal 0.0001.',
        ];
    }
}
