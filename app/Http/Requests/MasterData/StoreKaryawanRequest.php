<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MasterData\MstKaryawan;


class StoreKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => [
                'required',
                'string',
                'max:50',
                'unique:mst_karyawan,nik',
            ],
            'karyawan_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'departemen_cd' => [
                'required',
                'string',
                'max:50',
            ],
            'jabatan_nm' => [
                'required',
                'string',
                'max:100',
            ],
            'telepon_no' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
            ],
            'alamat_txt' => [
                'nullable',
                'string',
            ],
            'id_absensi' => [
                'nullable', 
                'string', '
                max:50'
            ],
            'kelompok' => [
                'nullable', 
                Rule::in(array_keys(MstKaryawan::KELOMPOK))
            ],
            'shift'      => [
                'nullable', 
                Rule::in(['SHIFT A', 'SHIFT B', 'NON SHIFT'])
            ],
            'status_kerja' => [
                'nullable', 
                'string', 
                'max:50'
            ],
            'tgl_masuk'  => [
                'nullable', 
                'date'
            ],
            'bank_name'  => [
                'nullable',
                'string', 
                'max:50'],
            'no_rekening' => [
                'nullable', 
                'string', 
                'max:50'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required'           => 'NIK / ID Karyawan wajib diisi.',
            'karyawan_nm.required'   => 'Nama lengkap karyawan wajib diisi.',
            'departemen_cd.required' => 'Departemen wajib dipilih.',
            'jabatan_nm.required'    => 'Jabatan kerja wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'kelompok.in'            => 'Kelompok yang dipilih tidak valid.',
            'shift.in'               => 'Shift harus Shift A, Shift B, atau Non Shift.',
            'tgl_masuk.date'         => 'Format tanggal masuk kerja tidak valid.',
            'id_absensi.max'         => 'ID Absensi maksimal 50 karakter.',
            'bank_name.max'          => 'Nama bank maksimal 50 karakter.',
            'no_rekening.max'        => 'Nomor rekening maksimal 50 karakter.',
        ];
    }
}
