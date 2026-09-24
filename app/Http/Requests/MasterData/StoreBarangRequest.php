<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses penambahan data barang baru.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'barang_cd' => [
                'required',
                'string',
                'max:50',
                'unique:mst_barang,barang_cd',
            ],
            'barang_nm' => [
                'required',
                'string',
                'max:150',
            ],
            'jenis_barang_id' => [
                'required',
                'integer',
                'exists:mst_jenis_barang,jenis_barang_id',
            ],
            'satuan_dasar_id' => [
                'required',
                'integer',
                'exists:mst_satuan,satuan_id',
            ],
            'satuan_besar_id' => [
                'nullable',
                'integer',
                'exists:mst_satuan,satuan_id',
            ],
            'konversi_qty' => [
                'required',
                'numeric',
                'min:1',
            ],
            'batas_minimum_qty' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'harga_beli_standar' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    /**
     * Pesan kustom dalam Bahasa Indonesia untuk kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'barang_cd.required' => 'Kode barang wajib diisi.',
            'barang_cd.string'   => 'Kode barang harus berupa teks.',
            'barang_cd.max'      => 'Kode barang maksimal :max karakter.',
            'barang_cd.unique'   => 'Kode barang ini sudah digunakan. Silakan gunakan kode lain.',

            'barang_nm.required' => 'Nama barang wajib diisi.',
            'barang_nm.string'   => 'Nama barang harus berupa teks.',
            'barang_nm.max'      => 'Nama barang maksimal :max karakter.',

            'jenis_barang_id.required' => 'Jenis barang wajib dipilih.',
            'jenis_barang_id.integer'  => 'Format jenis barang tidak valid.',
            'jenis_barang_id.exists'   => 'Jenis barang yang dipilih tidak terdaftar di sistem.',

            'satuan_dasar_id.required' => 'Satuan dasar wajib dipilih.',
            'satuan_dasar_id.integer'  => 'Format satuan dasar tidak valid.',
            'satuan_dasar_id.exists'   => 'Satuan dasar yang dipilih tidak terdaftar di sistem.',

            'satuan_besar_id.integer'  => 'Format satuan besar tidak valid.',
            'satuan_besar_id.exists'   => 'Satuan besar yang dipilih tidak terdaftar di sistem.',

            'konversi_qty.required' => 'Nilai konversi kuantitas wajib diisi.',
            'konversi_qty.numeric'  => 'Nilai konversi kuantitas harus berupa angka.',
            'konversi_qty.min'      => 'Nilai konversi kuantitas minimal bernilai 1.',
        ];
    }
}
