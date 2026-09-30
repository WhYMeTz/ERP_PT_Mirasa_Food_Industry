<?php

namespace App\Http\Requests\Penjualan;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $soId = $this->route('so')?->so_id ?? $this->route('so');

        return [
            'so_no'              => 'nullable|string|max:50|unique:dat_so_hdr,so_no,' . $soId . ',so_id',
            'so_tgl'             => 'required|date',
            'customer_id'        => 'required|integer|exists:mst_customer,customer_id',
            'customer_po_no'     => 'nullable|string|max:100',
            'tgl_kirim_estimasi' => 'nullable|date',
            'catatan_txt'        => 'nullable|string',
            'potongan_nominal'   => 'nullable|numeric|min:0',

            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => 'required|integer|exists:mst_barang,barang_id',
            'items.*.pesan_qty'         => 'required|numeric|min:0.0001',
            'items.*.harga_satuan'      => 'required|numeric|min:0',
            'items.*.diskon_persen'     => 'nullable|numeric|min:0|max:100',
            'items.*.potongan_nominal'  => 'nullable|numeric|min:0',
            'items.*.ppn_tipe'          => 'nullable|string|in:NON_PPN,PPN_11',
            'items.*.catatan_txt'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'so_tgl.required'            => 'Tanggal pesanan wajib diisi.',
            'customer_id.required'       => 'Customer pemesan wajib dipilih.',
            'customer_id.exists'         => 'Customer yang dipilih tidak valid.',
            'items.required'             => 'Minimal harus ada 1 barang dalam pesanan penjualan.',
            'items.min'                  => 'Minimal harus ada 1 barang dalam pesanan penjualan.',
            'items.*.barang_id.required' => 'Barang pesanan wajib dipilih pada setiap baris.',
            'items.*.barang_id.exists'   => 'Barang pesanan tidak valid.',
            'items.*.pesan_qty.required' => 'Kuantitas pesanan wajib diisi.',
            'items.*.pesan_qty.min'      => 'Kuantitas pesanan harus lebih dari 0.',
            'items.*.harga_satuan.required' => 'Harga satuan wajib diisi.',
            'items.*.harga_satuan.min'   => 'Harga satuan tidak boleh negatif.',
        ];
    }
}
