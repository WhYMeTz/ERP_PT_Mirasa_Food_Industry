<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('pengguna_sistem') ?? $this->route('user') ?? $this->route('id') ?? $this->input('id');

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
                Rule::unique('users', 'email')->ignore($id),
            ],
            'password' => [
                'nullable',
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
            'gudang_ids' => [
                'nullable',
                'array',
            ],
            'gudang_ids.*' => [
                'integer',
                'exists:mst_gudang,gudang_id',
            ],
            'active_st' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah digunakan oleh akun lain.',
            'password.min'      => 'Password minimal harus 6 karakter.',
            'role_cd.required'  => 'Peran hak akses (Role) wajib dipilih.',
            'gudang_id.exists'  => 'Gudang tugas yang dipilih tidak valid.',
        ];
    }
}
