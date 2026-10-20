<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstMesin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMesinRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses edit mesin.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $mesinParam = $this->route('master_mesin') ?? $this->route('mesin') ?? $this->route('id') ?? $this->input('mesin_id');
        $mesinId = $mesinParam instanceof MstMesin ? $mesinParam->mesin_id : $mesinParam;

        return [
            'mesin_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_mesin', 'mesin_cd')->ignore($mesinId, 'mesin_id'),
            ],
            'mesin_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'kategori_mesin' => [
                'required',
                'string',
                'in:PRODUKSI,PACKAGING',
            ],
            'jenis_proses' => [
                'nullable',
                'string',
                'max:50',
            ],
            'kapasitas_per_jam' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'satuan_kapasitas' => [
                'nullable',
                'string',
                'max:30',
            ],
            'tipe_energi' => [
                'nullable',
                'string',
                'max:30',
            ],
            'status_kondisi' => [
                'required',
                'string',
                'in:SIAP,MAINTENANCE,RUSAK',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:255',
            ],
            'active_st' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Pesan kustom dalam Bahasa Indonesia untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mesin_cd.required' => 'Kode mesin wajib diisi.',
            'mesin_cd.string'   => 'Kode mesin harus berupa teks.',
            'mesin_cd.max'      => 'Kode mesin maksimal :max karakter.',
            'mesin_cd.unique'   => 'Kode mesin ini sudah digunakan oleh mesin lain.',

            'mesin_nm.required' => 'Nama mesin operasional wajib diisi.',
            'mesin_nm.string'   => 'Nama mesin harus berupa teks.',
            'mesin_nm.max'      => 'Nama mesin maksimal :max karakter.',

            'kategori_mesin.required' => 'Kategori mesin wajib dipilih.',
            'kategori_mesin.in'       => 'Kategori mesin harus berupa PRODUKSI atau PACKAGING.',

            'kapasitas_per_jam.numeric' => 'Kapasitas per jam harus berupa angka valid.',
            'kapasitas_per_jam.min'     => 'Kapasitas per jam minimal :min.',

            'status_kondisi.required' => 'Status kondisi mesin wajib dipilih.',
            'status_kondisi.in'       => 'Status kondisi mesin harus berupa SIAP, MAINTENANCE, atau RUSAK.',

            'keterangan.max' => 'Keterangan maksimal :max karakter.',
        ];
    }
}
