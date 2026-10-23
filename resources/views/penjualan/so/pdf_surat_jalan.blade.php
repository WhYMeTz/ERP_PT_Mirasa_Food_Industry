@php
    $custNmUpper = strtoupper($order->customer?->customer_nm ?? '');
    $custCdUpper = strtoupper($order->customer?->customer_cd ?? '');
    $isIfmOrder = ($isIfm ?? false) 
        || str_contains($custNmUpper, 'INDOFOOD') 
        || str_contains($custNmUpper, 'IFM') 
        || str_contains($custNmUpper, 'FORTUNA') 
        || str_contains($custCdUpper, 'IFM') 
        || str_contains($custCdUpper, 'INDOFOOD');
@endphp
@if($isIfmOrder)
    @include('penjualan.so.pdf_surat_jalan_ifm')
@elseif(!empty($isPpn) && $isPpn)
    @include('penjualan.so.pdf_surat_jalan_ppn')
@else
    @include('penjualan.so.pdf_surat_jalan_non_ppn')
@endif
