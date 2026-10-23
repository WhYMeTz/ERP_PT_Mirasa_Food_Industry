<?php

namespace App\Http\Requests\Penjualan;

use Illuminate\Foundation\Http\FormRequest;

class StoreFakturRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pengeluaran_id'       => ['required', 'integer', 'exists:dat_pengeluaran_hdr,pengeluaran_id'],
            'faktur_tgl'           => ['required', 'date'],
            'tgl_jatuh_tempo'      => ['nullable', 'date'],
            'faktur_no'            => ['nullable', 'string', 'max:50'],
            'customer_po_no'       => ['nullable', 'string', 'max:100'],
            'top_txt'              => ['nullable', 'string', 'max:50'],
            'ppn_tipe'             => ['required', 'string', 'in:NON_PPN,PPN_11'],
            'total_potongan'       => ['nullable', 'numeric', 'min:0'],
            'catatan_txt'          => ['nullable', 'string', 'max:500'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.barang_id'    => ['required', 'integer', 'exists:mst_barang,barang_id'],
            'items.*.barang_nm'    => ['nullable', 'string', 'max:255'],
            'items.*.qty'          => ['required', 'numeric', 'min:0.0001'],
            'items.*.satuan'       => ['nullable', 'string', 'max:50'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'items.*.diskon_nominal' => ['nullable', 'numeric', 'min:0'],
            'items.*.ppn_nominal'  => ['nullable', 'numeric', 'min:0'],
            'items.*.catatan_txt'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'pengeluaran_id.required'    => 'Pilih dokumen Surat Jalan yang akan diterbitkan fakturnya.',
            'pengeluaran_id.exists'      => 'Dokumen Surat Jalan tidak ditemukan atau tidak valid.',
            'faktur_tgl.required'        => 'Tanggal penerbitan faktur wajib diisi.',
            'ppn_tipe.required'          => 'Tipe PPN wajib dipilih.',
            'items.required'             => 'Rincian barang dalam faktur tidak boleh kosong.',
            'items.min'                  => 'Minimal harus ada 1 item barang yang ditagihkan.',
            'items.*.qty.required'       => 'Kuantitas faktur wajib diisi.',
            'items.*.qty.min'            => 'Kuantitas faktur harus lebih dari 0.',
            'items.*.harga_satuan.min'   => 'Harga satuan tidak boleh bernilai negatif.',
        ];
    }
}
