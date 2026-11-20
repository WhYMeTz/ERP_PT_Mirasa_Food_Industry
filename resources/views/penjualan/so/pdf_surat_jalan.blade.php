@php
    $custNmUpper = strtoupper($order->customer?->customer_nm ?? '');
    $custCdUpper = strtoupper($order->customer?->customer_cd ?? '');
    $isIfmOrder = ($isIfm ?? false) 
        || str_contains($custNmUpper, 'INDOFOOD') 
        || str_contains($custNmUpper, 'IFM') 
        || str_contains($custNmUpper, 'FORTUNA') 
        || str_contains($custCdUpper, 'IFM') 
        || str_contains($custCdUpper, 'INDOFOOD');
    $custKlasifikasi = strtoupper($order->customer?->klasifikasi ?? '');
    $isIdmOrder = ($isIdm ?? false)
        || $custKlasifikasi === 'IDM'
        || str_contains($custNmUpper, 'INDOMARET')
        || str_contains($custNmUpper, 'DC YOGYA')
        || str_contains($custCdUpper, 'IDM');
    $isAlfaOrder = ($isAlfaMidi ?? false)
        || in_array($custKlasifikasi, ['MIDI_ALFA', 'IDM_ALFA'])
        || str_contains($custNmUpper, 'ALFAMART')
        || str_contains($custNmUpper, 'ALFAMIDI')
        || str_contains($custNmUpper, 'SUMBER ALFARIA')
        || str_contains($custNmUpper, 'MIDI UTAMA')
        || str_contains($custCdUpper, 'ALFA')
        || str_contains($custCdUpper, 'MIDI');
@endphp
@if($isIfmOrder)
    @include('penjualan.so.pdf_surat_jalan_ifm')
@elseif($isIdmOrder)
    @include('penjualan.so.pdf_surat_jalan_idm')
@elseif($isAlfaOrder)
    @include('penjualan.so.pdf_surat_jalan_alfa')
@elseif(!empty($isPpn) && $isPpn)
    @include('penjualan.so.pdf_surat_jalan_ppn')
@else
    @include('penjualan.so.pdf_surat_jalan_non_ppn')
@endif

