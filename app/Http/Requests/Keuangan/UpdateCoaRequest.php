<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('coa') ?? $this->route('id') ?? $this->input('coa_id');

        return [
            'kode_akun'        => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_coa', 'kode_akun')->ignore($id, 'coa_id'),
            ],
            'nama_akun'        => 'required|string|max:150',
            'kategori_akun'    => 'required|string|in:AKTIVA,PASIVA,MODAL,PENDAPATAN,HPP,BEBAN',
            'sub_kategori'     => 'nullable|string|max:100',
            'posisi_keuangan'  => 'required|string|in:NERACA,LABA/RUGI',
            'saldo_normal'     => 'required|string|in:DEBET,KREDIT',
            'saldo_awal'       => 'nullable|numeric',
            'is_kas_bank'      => 'nullable|boolean',
            'keterangan'       => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_akun.required'     => 'Nomor/Kode akun wajib diisi.',
            'kode_akun.unique'       => 'Kode akun ini sudah digunakan oleh akun lain.',
            'nama_akun.required'     => 'Nama akun keuangan wajib diisi.',
            'kategori_akun.required' => 'Pilih kategori akun yang sesuai.',
            'kategori_akun.in'       => 'Kategori akun harus salah satu dari: AKTIVA, PASIVA, MODAL, PENDAPATAN, HPP, BEBAN.',
            'posisi_keuangan.in'     => 'Posisi keuangan harus NERACA atau LABA/RUGI.',
            'saldo_normal.in'        => 'Saldo normal harus DEBET atau KREDIT.',
        ];
    }
}
