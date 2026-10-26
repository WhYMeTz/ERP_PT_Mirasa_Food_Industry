<?php

return [
    // Data tetap perusahaan (dari file Multi Payroll BCA yang sudah berjalan)
    'corporate_id'   => 'IBSMIRASAF',
    'company_code'   => '01040276',
    'rekening_sumber'=> '1049173332',
    'kode_bank'      => 'BCA',

    // Batas panjang nama penerima di file (legenda bank: 70).
    'maks_nama'      => 70,

    // Nomor header pertama yang dipakai bila belum ada riwayat unduhan.
    'header_awal'    => 73,

    // Bagian (isian Jabatan di data karyawan) => kode di ID transaksi & Remarks 1 bawaan.
    // Kunci ditulis HURUF BESAR; pencocokan tidak membedakan huruf besar/kecil.
    'bagian' => [
        'SORTIR AC'   => ['kode' => 'AC', 'remarks1' => 'AC'],
        'BATCH FRYER' => ['kode' => 'GO', 'remarks1' => 'BATCH FRYER'],
        'POT.RENDEM'  => ['kode' => 'PT', 'remarks1' => 'POT RENDEM'],
        'UMUM'        => ['kode' => 'UM', 'remarks1' => 'UMUM', 'alias' => ['DRIVER', 'SECURITY', 'KEUANGAN', 'QUALITYCONTROL']],
        'PACKING'     => ['kode' => 'BU', 'remarks1' => 'PACKING'],
        'MAKARONI'    => ['kode' => 'MKR', 'remarks1' => 'MAKARONI'],
    ],
];
