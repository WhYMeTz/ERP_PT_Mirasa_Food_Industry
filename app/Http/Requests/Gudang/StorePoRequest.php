<?php

namespace App\Http\Requests\Gudang;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_no'               => 'nullable|string|max:50|unique:dat_po_hdr,po_no',
            'po_tgl'              => 'required|date',
            'tgl_estimasi_datang' => 'nullable|date',
            'supplier_id'         => 'required|integer|exists:mst_supplier,supplier_id',
            'gudang_id'           => 'required|integer|exists:mst_gudang,gudang_id',
            'catatan_txt'         => 'nullable|string',

            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => [
                'required',
                'integer',
                Rule::exists('mst_barang', 'barang_id')->where(function ($query) {
                    $query->where('deleted_st', false)
                          ->whereIn('jenis_barang_id', function ($sub) {
                              $sub->select('jenis_barang_id')
                                  ->from('mst_jenis_barang')
                                  ->whereIn('jenis_barang_cd', ['RAW', 'SUPP', 'PACK', 'BUMBU']);
                          });
                }),
            ],
            'items.*.pesan_qty'         => 'required|numeric|min:0.0001',
            'items.*.harga_nominal'     => 'nullable|numeric|min:0',
            'items.*.catatan_txt'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'po_tgl.required'           => 'Tanggal PO wajib diisi.',
            'supplier_id.required'      => 'Supplier wajib dipilih.',
            'supplier_id.exists'        => 'Supplier yang dipilih tidak valid.',
            'gudang_id.required'        => 'Gudang tujuan pengiriman wajib dipilih.',
            'gudang_id.exists'          => 'Gudang tujuan yang dipilih tidak valid.',
            'items.required'            => 'Minimal harus ada 1 item barang yang dipesan.',
            'items.min'                 => 'Minimal harus ada 1 item barang yang dipesan.',
            'items.*.barang_id.required'=> 'Barang wajib dipilih pada setiap baris item.',
            'items.*.barang_id.exists'  => 'Barang yang dipilih tidak valid atau bukan kategori Bahan Baku / Penolong.',
            'items.*.pesan_qty.required'=> 'Kuantitas pesanan wajib diisi.',
            'items.*.pesan_qty.min'     => 'Kuantitas pesanan minimal 0.0001.',
        ];
    }
}
