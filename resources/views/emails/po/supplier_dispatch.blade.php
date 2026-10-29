<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Resmi - PT Mirasa Food Industry</title>
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
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
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
            color: #d1fae5;
        }
        .content {
            padding: 24px;
        }
        .letter-header {
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 14px;
            color: #334155;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-card table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
            padding: 4px 0;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            padding: 4px 0;
        }
        .custom-notes {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px 16px;
            margin-bottom: 24px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #1e40af;
        }
        .address-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 13px;
            margin-bottom: 24px;
            line-height: 1.5;
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
            <h1>PT. MIRASA FOOD INDUSTRY</h1>
            <p>Divisi Pengadaan Bahan Baku &amp; Logistik Pabrik</p>
        </div>

        <div class="content">
            <div class="letter-header">
                Kepada Yth.<br>
                <strong>Pimpinan / Bagian Penjualan &amp; Pengiriman</strong><br>
                <strong>{{ $po->supplier?->supplier_nm ?? 'Mitra Supplier' }}</strong><br>
                Di Tempat
            </div>

            <p style="font-size: 13.5px; line-height: 1.6; color: #334155;">
                Dengan hormat,<br>
                Sehubungan dengan kebutuhan operasional produksi di pabrik kami, bersama email ini kami menerbitkan dan mengirimkan <strong>Purchase Order (PO) Resmi</strong> sebagai berikut:
            </p>

            <div class="info-card">
                <table>
                    <tr>
                        <td class="info-label">Nomor PO Resmi</td>
                        <td class="info-value" style="color: #059669; font-size: 14px;">{{ $po->po_no }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Pemesanan</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Estimasi Pengiriman / Kedatangan</td>
                        <td class="info-value">{{ $po->tgl_estimasi_datang ? \Carbon\Carbon::parse($po->tgl_estimasi_datang)->translatedFormat('d F Y') : 'Sesuai Konfirmasi' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status Keabsahan Dokumen</td>
                        <td class="info-value" style="color: #059669;">
                            ✓ Disetujui Resmi (Tervalidasi QR Code)
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Disetujui Oleh</td>
                        <td class="info-value">{{ $po->approved_by ?? 'Manajemen PT Mirasa' }}</td>
                    </tr>
                </table>
            </div>

            @if (!empty($customNotes))
                <div class="custom-notes">
                    <strong>Pesan Khusus dari Tim Pengadaan Mirasa:</strong><br>
                    {{ $customNotes }}
                </div>
            @endif

            <div class="address-box">
                <strong style="color: #0f172a;">Alamat Pengiriman Barang:</strong><br>
                <strong>PT. MIRASA FOOD INDUSTRY (Pabrik Magelang)</strong><br>
                Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512<br>
                <em>(Gudang Tujuan: {{ $po->gudang?->gudang_nm ?? 'Gudang Bahan Baku Magelang' }})</em>
            </div>

            <p style="font-size: 13px; color: #475569; line-height: 1.6;">
                📎 <strong>Lampiran:</strong><br>
                Dokumen Purchase Order resmi yang telah dibubuhi <em>Cap Digital dan QR Code Keabsahan</em> telah dilampirkan dalam format PDF pada email ini.<br>
                Mohon konfirmasi penerimaan pesanan ini dan jadwal pengiriman ke pihak kami. Terima kasih atas kerja samanya.
            </p>
        </div>

        <div class="footer">
            Hormat kami,<br>
            <strong>Departemen Purchasing &amp; Supply Chain</strong><br>
            PT Mirasa Food Industry | Email: purchasing@mirasafood.com | Magelang, Jawa Tengah
        </div>
    </div>
</body>
</html>
