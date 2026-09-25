<?php

namespace App\Http\Requests\Gudang;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerimaBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terima_no'     => 'nullable|string|max:50|unique:dat_terima_hdr,terima_no',
            'terima_tgl'    => 'required|date',
            'po_id'         => 'nullable|integer|exists:dat_po_hdr,po_id',
            'supplier_id'   => 'required|integer|exists:mst_supplier,supplier_id',
            'gudang_id'     => 'required|integer|exists:mst_gudang,gudang_id',
            'suratjalan_no' => 'nullable|string|max:100',
            'catatan_txt'   => 'nullable|string',

            'items'                 => 'required|array|min:1',
            'items.*.barang_id'     => 'required|integer|exists:mst_barang,barang_id',
            'items.*.podtl_id'      => 'nullable|integer|exists:dat_po_dtl,podtl_id',
            'items.*.batch_no'      => 'nullable|string|max:100',
            'items.*.expired_tgl'   => 'nullable|date',
            'items.*.grade_cd'      => 'nullable|string|max:20',
            'items.*.reject_qty'    => 'nullable|numeric|min:0',
            'items.*.terima_qty'    => 'required|numeric|min:0.0001',
            'items.*.harga_nominal' => 'nullable|numeric|min:0',
            'items.*.catatan_txt'   => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'terima_tgl.required'        => 'Tanggal penerimaan fisik wajib diisi.',
            'supplier_id.required'       => 'Supplier pengirim wajib dipilih.',
            'supplier_id.exists'         => 'Supplier yang dipilih tidak valid.',
            'gudang_id.required'         => 'Gudang penyimpanan wajib dipilih.',
            'gudang_id.exists'           => 'Gudang yang dipilih tidak valid.',
            'items.required'             => 'Minimal harus ada 1 item barang yang diterima.',
            'items.min'                  => 'Minimal harus ada 1 item barang yang diterima.',
            'items.*.barang_id.required' => 'Barang wajib dipilih pada setiap baris item.',
            'items.*.barang_id.exists'   => 'Barang yang dipilih tidak valid.',
            'items.*.terima_qty.required'=> 'Kuantitas penerimaan wajib diisi.',
            'items.*.terima_qty.min'     => 'Kuantitas penerimaan minimal 0.0001.',
        ];
    }
}
