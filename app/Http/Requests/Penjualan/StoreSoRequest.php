<?php

namespace App\Http\Requests\Penjualan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $soId = $this->route('so')?->so_id ?? $this->route('so');

        return [
            'so_no'              => [
                'nullable',
                'string',
                'max:50',
                $soId ? Rule::unique('dat_so_hdr', 'so_no')->ignore($soId, 'so_id') : Rule::unique('dat_so_hdr', 'so_no'),
            ],
            'faktur_no'          => 'nullable|string|max:50',
            'surat_jalan_no'     => 'nullable|string|max:50',
            'so_tgl'             => 'required|date',
            'customer_id'        => 'required|integer|exists:mst_customer,customer_id',
            'customer_po_no'     => 'nullable|string|max:100',
            'top_txt'            => 'nullable|string|max:50',
            'ppn_tipe_header'    => 'nullable|string|in:NON_PPN,PPN_11',
            'tgl_kirim_estimasi' => 'nullable|date',
            'supir_nm'           => 'nullable|string|max:100',
            'nopol_kendaraan'    => 'nullable|string|max:30',
            'catatan_txt'        => 'nullable|string',
            'potongan_nominal'   => 'nullable|numeric|min:0',

            'items'                     => 'required|array|min:1',
            'items.*.barang_id'         => 'required|integer|exists:mst_barang,barang_id',
            'items.*.pesan_qty'         => 'required|numeric|min:0.0001',
            'items.*.harga_satuan'      => 'required|numeric|min:0',
            'items.*.diskon_persen'     => 'nullable|numeric|min:0|max:100',
            'items.*.potongan_nominal'  => 'nullable|numeric|min:0',
            'items.*.ppn_tipe'          => 'nullable|string|in:NON_PPN,PPN_11',
            'items.*.catatan_txt'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'so_tgl.required'            => 'Tanggal pesanan wajib diisi.',
            'customer_id.required'       => 'Customer pemesan wajib dipilih.',
            'customer_id.exists'         => 'Customer yang dipilih tidak valid.',
            'items.required'             => 'Minimal harus ada 1 barang dalam pesanan penjualan.',
            'items.min'                  => 'Minimal harus ada 1 barang dalam pesanan penjualan.',
            'items.*.barang_id.required' => 'Barang pesanan wajib dipilih pada setiap baris.',
            'items.*.barang_id.exists'   => 'Barang pesanan tidak valid.',
            'items.*.pesan_qty.required' => 'Kuantitas pesanan wajib diisi.',
            'items.*.pesan_qty.min'      => 'Kuantitas pesanan harus lebih dari 0.',
            'items.*.harga_satuan.required' => 'Harga satuan wajib diisi.',
            'items.*.harga_satuan.min'   => 'Harga satuan tidak boleh negatif.',
        ];
    }

    /**
     * Validasi bisnis lanjutan: produk pasokan khusus IFM hanya untuk pelanggan berklasifikasi IFM.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customer = \App\Models\MasterData\MstCustomer::find($this->customer_id);
            if (!$customer) {
                return;
            }

            $isIfm = strtoupper(trim($customer->klasifikasi ?? '')) === 'IFM'
                || str_contains(strtoupper($customer->customer_nm), 'INDOFOOD')
                || str_contains(strtoupper($customer->customer_nm), 'FORTUNA')
                || str_contains(strtoupper($customer->customer_cd ?? ''), 'IFM');

            $items = $this->input('items', []);
            if (!is_array($items)) {
                return;
            }

            foreach ($items as $idx => $item) {
                $barangId = $item['barang_id'] ?? null;
                if (!$barangId) continue;

                $barang = \App\Models\MasterData\MstBarang::find($barangId);
                if (!$barang) continue;

                $cd = strtoupper($barang->barang_cd ?? '');
                $nm = strtoupper($barang->barang_nm ?? '');
                $isIfmProduct = str_contains($cd, 'FCC') || str_contains($cd, 'IFL') || str_contains($nm, 'IFL') || str_contains($nm, 'INDOFOOD');

                if (!$isIfm && $isIfmProduct) {
                    $validator->errors()->add("items.{$idx}.barang_id", "Produk {$barang->barang_nm} merupakan pasokan khusus PT. Indofood (IFM). Untuk customer umum/manual, silakan pilih produk Barang Jadi (FG) seperti KS Jumbo, Maksi, atau Ping-Ping.");
                }
            }
        });
    }
}
