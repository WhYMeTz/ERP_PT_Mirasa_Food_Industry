<?php

namespace App\Http\Requests\Gudang;

use Illuminate\Foundation\Http\FormRequest;

class StorePemakaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pakai_no'         => ['nullable', 'string', 'max:100', 'unique:dat_pakai_hdr,pakai_no'],
            'pakai_tgl'        => ['required', 'date'],
            'gudang_id'        => ['required', 'exists:mst_gudang,gudang_id'],
            'tujuan_pemakaian' => ['required', 'string', 'max:150'],
            'catatan_txt'      => ['nullable', 'string', 'max:1000'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.barang_id'=> ['required', 'exists:mst_barang,barang_id'],
            'items.*.batch_no' => ['required', 'string', 'max:100'],
            'items.*.qty_keluar' => ['required', 'numeric', 'min:0.0001'],
            'items.*.harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'items.*.keterangan_txt' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'gudang_id.required'        => 'Gudang asal barang wajib dipilih.',
            'tujuan_pemakaian.required' => 'Tujuan pemakaian / SPK wajib diisi (contoh: PRODUKSI IFM, PACKING EKSPOR).',
            'items.required'            => 'Minimal harus ada 1 item barang yang dikeluarkan.',
            'items.*.barang_id.required'=> 'Barang wajib dipilih.',
            'items.*.batch_no.required' => 'Nomor batch wajib dipilih.',
            'items.*.qty_keluar.min'    => 'Qty keluar harus lebih besar dari 0.',
        ];
    }
}
