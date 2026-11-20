<?php

namespace App\Mail;

use App\Models\Gudang\DatPoHdr;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PoSupplierDispatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DatPoHdr $po,
        public ?string $customNotes = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Purchase Order Resmi #{$this->po->po_no} - PT Mirasa Food Industry",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.po.supplier_dispatch',
            with: [
                'po'          => $this->po,
                'customNotes' => $this->customNotes,
            ],
        );
    }

    public function attachments(): array
    {
        try {
            $this->po->loadMissing(['details.barang.satuanDasar', 'supplier', 'gudang']);

            $logoPath = public_path('images/logo.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdf = Pdf::loadView('gudang.po.pdf', [
                'po'         => $this->po,
                'logoBase64' => $logoBase64,
            ]);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            $cleanPoNo = preg_replace('/[^A-Za-z0-9\-]/', '_', $this->po->po_no);
            return [
                Attachment::fromData(fn() => $pdf->output(), "PO_{$cleanPoNo}_RESMI.pdf")
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
