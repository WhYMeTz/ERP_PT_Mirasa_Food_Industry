<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
            ],
            'karyawan_id' => [
                'nullable',
                'integer',
                'exists:mst_karyawan,karyawan_id',
            ],
            'role_cd' => [
                'required',
                'string',
                'max:50',
            ],
            'gudang_id' => [
                'nullable',
                'integer',
                'exists:mst_gudang,gudang_id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah terdaftar sebagai pengguna sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 6 karakter.',
            'role_cd.required'  => 'Peran hak akses (Role) wajib dipilih.',
            'gudang_id.exists'  => 'Gudang tugas yang dipilih tidak valid.',
        ];
    }
}
