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
use Illuminate\Support\Str;

class PoApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $approveUrl;
    public string $rejectUrl;

    public function __construct(public DatPoHdr $po)
    {
        if (empty($this->po->approval_token)) {
            $this->po->approval_token = Str::random(40);
            $this->po->saveQuietly();
        }

        $this->approveUrl = route('po.approval.direct', [
            'token'  => $this->po->approval_token,
            'action' => 'approve',
        ]);

        $this->rejectUrl = route('po.approval.direct', [
            'token'  => $this->po->approval_token,
            'action' => 'reject',
        ]);
    }

    public function envelope(): Envelope
    {
        $supplierName = $this->po->supplier?->supplier_nm ?? 'Supplier';
        return new Envelope(
            subject: "Permohonan Persetujuan PO #{$this->po->po_no} - {$supplierName} - PT Mirasa Food Industry",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.po.approval_request',
            with: [
                'po'         => $this->po,
                'approveUrl' => $this->approveUrl,
                'rejectUrl'  => $this->rejectUrl,
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
                Attachment::fromData(fn() => $pdf->output(), "PO_{$cleanPoNo}_DRAFT.pdf")
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
