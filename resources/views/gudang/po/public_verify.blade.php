<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Resmi - PT Mirasa Food Industry</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 32px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .cert-card {
            max-width: 580px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        }
        .cert-header {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .cert-header h1 {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .cert-header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #d1fae5;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #ffffff;
            color: #065f46;
            font-size: 12.5px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 9999px;
            margin-top: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .content {
            padding: 24px;
        }
        .notice-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #166534;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            margin-bottom: 20px;
        }
        .doc-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .doc-table tr:last-child {
            border-bottom: none;
        }
        .doc-table td {
            padding: 10px 0;
        }
        .doc-label {
            color: #64748b;
            font-weight: 500;
            width: 42%;
        }
        .doc-val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .cert-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="cert-card">
        @if ($po && ($po->isApproved() || $po->status_cd === 'PARTIAL' || $po->status_cd === 'COMPLETED'))
            <div class="cert-header">
                <h1>PT. MIRASA FOOD INDUSTRY</h1>
                <p>Sistem Sertifikasi &amp; Verifikasi Dokumen Digital Resmi</p>
                <div class="status-badge">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    DOKUMEN ASLI &bull; RESMI TERVERIFIKASI
                </div>
            </div>

            <div class="content">
                <div class="notice-box">
                    ✓ Dokumen Purchase Order ini dinyatakan <strong>ASLI dan SAH</strong>, diterbitkan secara resmi oleh Divisi Pengadaan PT Mirasa Food Industry dan telah disetujui pimpinan perusahaan.
                </div>

                <table class="doc-table">
                    <tr>
                        <td class="doc-label">Nomor PO Resmi</td>
                        <td class="doc-val" style="color: #0284c7; font-size: 14.5px;">{{ $po->po_no }}</td>
                    </tr>
                    <tr>
                        <td class="doc-label">Tanggal Pemesanan</td>
                        <td class="doc-val">{{ \Carbon\Carbon::parse($po->po_tgl)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="doc-label">Mitra Supplier</td>
                        <td class="doc-val">{{ $po->supplier?->supplier_nm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="doc-label">Gudang Tujuan</td>
                        <td class="doc-val">{{ $po->gudang?->gudang_nm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="doc-label">Total Tagihan</td>
                        <td class="doc-val" style="color: #059669;">Rp {{ number_format((float) $po->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="doc-label">Status Otorisasi</td>
                        <td class="doc-val" style="color: #059669;">✓ DISAHKAN (APPROVED)</td>
                    </tr>
                    <tr>
                        <td class="doc-label">Disetujui Oleh</td>
                        <td class="doc-val">{{ $po->approved_by ?? $po->approver?->name ?? 'Pimpinan Perusahaan' }}</td>
                    </tr>
                    @if ($po->approved_at)
                        <tr>
                            <td class="doc-label">Waktu Persetujuan</td>
                            <td class="doc-val">{{ \Carbon\Carbon::parse($po->approved_at)->translatedFormat('d F Y, H:i') }} WIB</td>
                        </tr>
                    @endif
                </table>
            </div>
        @else
            <div class="cert-header" style="background: linear-gradient(135deg, #991b1b 0%, #b91c1c 100%);">
                <h1>PT. MIRASA FOOD INDUSTRY</h1>
                <p>Sistem Sertifikasi &amp; Verifikasi Dokumen Digital</p>
                <div class="status-badge" style="color: #991b1b;">
                    ⚠ DOKUMEN BELUM SAH / TIDAK VALID
                </div>
            </div>

            <div class="content" style="text-align: center; padding: 32px 24px;">
                <p style="color: #64748b; font-size: 14px; line-height: 1.6;">
                    Dokumen Purchase Order dengan token ini <strong>belum disetujui resmi oleh pimpinan</strong> atau tidak ditemukan dalam arsip database perusahaan kami.<br>
                    Harap hubungi Divisi Pengadaan PT Mirasa Food Industry untuk konfirmasi lebih lanjut.
                </p>
            </div>
        @endif

        <div class="cert-footer">
            Sistem Verifikasi Digital Terpadu ERP Mirasa<br>
            Jl. Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah 56512
        </div>
    </div>
</body>
</html>
