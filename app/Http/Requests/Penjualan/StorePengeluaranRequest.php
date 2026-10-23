<?php

namespace App\Http\Requests\Penjualan;

use Illuminate\Foundation\Http\FormRequest;

class StorePengeluaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'so_id'              => ['required', 'integer', 'exists:dat_so_hdr,so_id'],
            'pengeluaran_tgl'    => ['required', 'date'],
            'pengeluaran_no'     => ['nullable', 'string', 'max:50'],
            'supir_nm'           => ['nullable', 'string', 'max:100'],
            'nopol_kendaraan'    => ['nullable', 'string', 'max:30'],
            'ekspedisi_nm'       => ['nullable', 'string', 'max:100'],
            'catatan_txt'        => ['nullable', 'string', 'max:500'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.sodtl_id'   => ['required', 'integer', 'exists:dat_so_dtl,sodtl_id'],
            'items.*.kirim_qty'  => ['required', 'numeric', 'min:0'],
            'items.*.catatan_txt'=> ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'so_id.required'             => 'Pilih dokumen PO Penjualan yang akan dikirim.',
            'so_id.exists'               => 'Dokumen PO Penjualan tidak valid atau tidak ditemukan.',
            'pengeluaran_tgl.required'   => 'Tanggal pengeluaran barang wajib diisi.',
            'items.required'             => 'Rincian barang yang dikirim tidak boleh kosong.',
            'items.min'                  => 'Minimal harus ada 1 barang yang diproses pengeluarannya.',
            'items.*.kirim_qty.required' => 'Kuantitas kirim wajib diisi.',
            'items.*.kirim_qty.numeric'  => 'Kuantitas kirim harus berupa angka.',
            'items.*.kirim_qty.min'      => 'Kuantitas kirim tidak boleh bernilai negatif.',
        ];
    }
}
