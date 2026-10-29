<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Persetujuan Purchase Order</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 24px 12px;
        }
        .container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .badge-pending {
            display: inline-block;
            background-color: #fef3c7;
            color: #b45309;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .content {
            padding: 24px;
        }
        .greeting {
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 20px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13px;
        }
        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 13px;
        }
        .table-items th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-items td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .action-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-top: 24px;
        }
        .action-box p {
            margin: 0 0 16px;
            font-size: 13.5px;
            color: #475569;
            font-weight: 500;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 6px;
            margin: 6px 8px;
            transition: all 0.2s ease;
        }
        .btn-approve {
            background-color: #059669;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }
        .btn-reject {
            background-color: #dc2626;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }
        .footer {
            background-color: #f8fafc;
            padding: 16px 24px;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT MIRASA FOOD INDUSTRY</h1>
            <p>Sistem ERP Terpadu - Pengadaan Bahan Baku &amp; Logistik</p>
            <div class="badge-pending">⏳ Menunggu Persetujuan Anda</div>
        </div>

        <div class="content">
            <div class="greeting">
                Yth. <strong>{{ $po->approver?->name ?? 'Bapak/Ibu Atasan' }}</strong>,<br>
                Terdapat pengajuan dokumen <strong>Purchase Order (PO)</strong> baru yang membutuhkan persetujuan Anda:
            </div>

            <div class="info-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Nomor PO</td>
                        <td class="info-value" style="padding: 4px 0; color: #0284c7;">{{ $po->po_no }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Tanggal Terbit</td>
                        <td class="info-value" style="padding: 4px 0;">{{ \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Mitra Supplier</td>
                        <td class="info-value" style="padding: 4px 0;">{{ $po->supplier?->supplier_nm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Gudang Tujuan</td>
                        <td class="info-value" style="padding: 4px 0;">{{ $po->gudang?->gudang_nm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Estimasi Kedatangan</td>
                        <td class="info-value" style="padding: 4px 0;">{{ $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang)->translatedFormat('d F Y') : 'Sesuai Jadwal' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 6px 0 0; border-top: 1px solid #cbd5e1; font-weight: 700;">TOTAL TAGIHAN (IDR)</td>
                        <td class="info-value" style="padding: 6px 0 0; border-top: 1px solid #cbd5e1; color: #059669; font-size: 15px;">Rp {{ number_format((float) $po->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <h3 style="font-size: 14px; color: #0f172a; margin: 0 0 10px;">Rincian Barang yang Dipesan:</h3>
            <table class="table-items">
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th>Nama Barang</th>
                        <th style="text-align: right;">Jumlah</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($po->details as $idx => $d)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $d->barang?->barang_nm ?? '-' }}</strong>
                                @if ($d->catatan_txt)
                                    <br><span style="font-size: 11px; color: #64748b;">Ket: {{ $d->catatan_txt }}</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                {{ number_format((float) $d->pesan_qty, 0, ',', '.') }} {{ $d->barang?->satuanDasar?->satuan_cd ?? 'KG' }}
                            </td>
                            <td style="text-align: right;">
                                Rp {{ number_format((float) $d->harga_nominal, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700;">
                                Rp {{ number_format((float) $d->subtotal_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($po->catatan_txt)
                <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; padding: 12px; margin-bottom: 20px; font-size: 12.5px; color: #92400e;">
                    <strong>Catatan Pembuat PO:</strong><br>
                    {{ $po->catatan_txt }}
                </div>
            @endif

            <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                📎 <em>Dokumen draft Purchase Order lengkap telah dilampirkan dalam bentuk file PDF pada email ini.</em>
            </p>

            <div class="action-box">
                <p>Silakan klik salah satu tombol di bawah untuk memberikan keputusan Anda secara instan:</p>
                <div>
                    <a href="{{ $approveUrl }}" class="btn btn-approve">
                        ✅ Setujui Purchase Order (Approve)
                    </a>
                    <a href="{{ $rejectUrl }}" class="btn btn-reject">
                        ❌ Tolak Pengajuan (Reject)
                    </a>
                </div>
                <div style="margin-top: 12px; font-size: 11px; color: #94a3b8;">
                    Tautan di atas aman dan berlaku khusus untuk persetujuan dokumen ini.
                </div>
            </div>
        </div>

        <div class="footer">
            Email otomatis dikirim oleh Sistem ERP PT Mirasa Food Industry.<br>
            Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512
        </div>
    </div>
</body>
</html>
