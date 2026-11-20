<?php

return [
    // NPWP pemotong (16 digit) = TIN di berkas XML BP21. Bisa diganti lewat .env: PAJAK_NPWP_PEMOTONG
    'npwp_pemotong' => env('PAJAK_NPWP_PEMOTONG', '0763570041524000'),

    // Isian tetap pada tiap bukti potong, sama dengan Excel "BP21 Excel to XML"
    'kode_objek' => '21-100-35',   // Upah pegawai tidak tetap yang dibayarkan secara bulanan
    'fasilitas'  => 'N/A',
    'dokumen'    => 'Other',
];
