<?php

namespace App\Http\Requests\Keuangan;

use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_jurnal'           => ['required', 'date'],
            'tipe_transaksi'       => ['required', 'string', 'in:JURNAL_UMUM,JURNAL_PENYESUAIAN,SALDO_AWAL'],
            'ref_no'               => ['nullable', 'string', 'max:100'],
            'keterangan'           => ['required', 'string', 'max:500'],
            'entries'              => ['required', 'array', 'min:2'],
            'entries.*.coa_id'     => ['required', 'integer', 'exists:mst_coa,coa_id'],
            'entries.*.debet'      => ['nullable', 'numeric', 'min:0'],
            'entries.*.kredit'     => ['nullable', 'numeric', 'min:0'],
            'entries.*.keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_jurnal.required'       => 'Tanggal jurnal wajib diisi.',
            'tgl_jurnal.date'           => 'Format tanggal jurnal tidak valid.',
            'tipe_transaksi.required'   => 'Kategori / tipe jurnal wajib dipilih.',
            'tipe_transaksi.in'         => 'Tipe transaksi tidak valid.',
            'keterangan.required'       => 'Keterangan umum jurnal wajib diisi.',
            'entries.required'          => 'Minimal harus terdapat 2 baris akun jurnal (Debet dan Kredit).',
            'entries.min'               => 'Jurnal berpasangan membutuhkan minimal 2 baris transaksi.',
            'entries.*.coa_id.required' => 'Pilih akun COA untuk setiap baris transaksi.',
            'entries.*.coa_id.exists'   => 'Akun COA yang dipilih tidak terdaftar di sistem.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $entries = $this->input('entries', []);
            $totalDebet = 0;
            $totalKredit = 0;
            $validRowCount = 0;

            foreach ($entries as $index => $row) {
                $debet = (float) ($row['debet'] ?? 0);
                $kredit = (float) ($row['kredit'] ?? 0);

                if ($debet > 0 && $kredit > 0) {
                    $v->errors()->add("entries.{$index}.debet", "Baris #" . ($index + 1) . " tidak boleh mengisi Debet dan Kredit sekaligus.");
                }

                if ($debet <= 0 && $kredit <= 0) {
                    $v->errors()->add("entries.{$index}.debet", "Baris #" . ($index + 1) . " harus memiliki nilai nominal pada salah satu kolom (Debet atau Kredit).");
                } else {
                    $validRowCount++;
                }

                $totalDebet += $debet;
                $totalKredit += $kredit;
            }

            if ($validRowCount < 2) {
                $v->errors()->add('entries', 'Minimal harus terdapat 2 baris transaksi yang bernilai nominal.');
            }

            $selisih = abs($totalDebet - $totalKredit);
            if ($selisih > 0.01) {
                $formattedDebet = 'Rp ' . number_format($totalDebet, 0, ',', '.');
                $formattedKredit = 'Rp ' . number_format($totalKredit, 0, ',', '.');
                $formattedSelisih = 'Rp ' . number_format($selisih, 0, ',', '.');
                $v->errors()->add('keseimbangan', "Jurnal tidak seimbang (Unbalanced)! Total Debet: {$formattedDebet}, Total Kredit: {$formattedKredit}. Selisih: {$formattedSelisih}. Jurnal wajib 100% seimbang (Balance).");
            }
        });
    }
}

