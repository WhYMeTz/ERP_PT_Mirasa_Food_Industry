<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstBarang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BarangPdfService
{
    /**
     * Menghasilkan file PDF seluruh data Master Barang PT Mirasa Food Industry.
     */
    public function exportPdf(): Response
    {
        $barangs = MstBarang::with(['jenisBarang', 'satuanDasar', 'satuanBesar'])
            ->where('deleted_st', false)
            ->orderBy('barang_cd', 'asc')
            ->get();

        // Encode logo ke Base64 agar dapat di-render 100% stabil oleh Dompdf
        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $printedAt = date('d/m/Y H:i');
        $printedBy = Auth::user()?->karyawan?->karyawan_nm ?? (Auth::user()?->username ?? 'Administrator');

        $pdf = Pdf::loadView('master_data.barang.pdf', [
            'barangs'    => $barangs,
            'logoBase64' => $logoBase64,
            'printedAt'  => $printedAt,
            'printedBy'  => $printedBy,
        ]);

        // Atur ukuran kertas A4 Landscape agar tabel luas dan nyaman dibaca
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        $filename = 'Master_Barang_PT_Mirasa_' . date('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }
}
