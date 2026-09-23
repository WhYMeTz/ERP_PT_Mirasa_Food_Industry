<?php

namespace App\Http\Requests\MasterData;

use App\Models\MasterData\MstCustomer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerParam = $this->route('master_customer') ?? $this->route('customer') ?? $this->route('id') ?? $this->input('customer_id');
        $customerId = $customerParam instanceof MstCustomer ? $customerParam->customer_id : $customerParam;

        return [
            'customer_cd' => [
                'required',
                'string',
                'max:50',
                Rule::unique('mst_customer', 'customer_cd')->ignore($customerId, 'customer_id'),
            ],
            'customer_nm' => [
                'required',
                'string',
                'max:150',
            ],
            'kontak_no' => [
                'nullable',
                'string',
                'max:50',
            ],
            'alamat_txt' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_cd.required' => 'Kode customer wajib diisi.',
            'customer_cd.unique'   => 'Kode customer ini sudah digunakan oleh customer lain. Gunakan kode lain.',
            'customer_nm.required' => 'Nama customer wajib diisi.',
        ];
    }
}
