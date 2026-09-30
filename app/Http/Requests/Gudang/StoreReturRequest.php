<?php

namespace App\Http\Requests\Gudang;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'retur_no'               => 'nullable|string|max:50|unique:dat_retur_hdr,retur_no',
            'retur_tgl'              => 'required|date',
            'supplier_id'            => 'required|integer|exists:mst_supplier,supplier_id',
            'gudang_id'              => 'required|integer|exists:mst_gudang,gudang_id',
            'po_id'                  => 'nullable|integer|exists:dat_po_hdr,po_id',
            'terima_id'              => 'nullable|integer|exists:dat_terima_hdr,terima_id',
            'tindakan_cd'            => 'required|string|in:REPLACE,CREDIT_NOTE',
            'suratjalan_supplier_no' => 'nullable|string|max:100',
            'alasan_txt'             => 'nullable|string',

            'items'                  => 'required|array|min:1',
            'items.*.barang_id'      => 'required|integer|exists:mst_barang,barang_id',
            'items.*.batch_no'       => 'required|string|max:100',
            'items.*.podtl_id'       => 'nullable|integer|exists:dat_po_dtl,podtl_id',
            'items.*.retur_qty'      => 'required|numeric|gt:0',
            'items.*.harga_satuan'   => 'nullable|numeric|min:0',
            'items.*.alasan_reject'  => 'nullable|string|max:255',
            'items.*.catatan_txt'    => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'retur_tgl.required'    => 'Tanggal pengembalian retur wajib diisi.',
            'supplier_id.required'  => 'Supplier tujuan retur wajib dipilih.',
            'supplier_id.exists'    => 'Supplier yang dipilih tidak terdaftar di sistem.',
            'gudang_id.required'    => 'Gudang asal barang wajib dipilih.',
            'gudang_id.exists'      => 'Gudang yang dipilih tidak valid.',
            'tindakan_cd.required'  => 'Tindakan penanganan retur (Ganti Barang / Potong Tagihan) wajib ditentukan.',
            'items.required'        => 'Minimal harus ada 1 item barang yang diretur.',
            'items.*.batch_no.required' => 'Nomor batch wajib dipilih untuk setiap item yang diretur.',
            'items.*.retur_qty.required' => 'Kuantitas retur wajib diisi.',
            'items.*.retur_qty.gt'       => 'Kuantitas retur harus lebih besar dari 0.',
        ];
    }
}
