<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isSuccess ? ($action === 'APPROVED' ? 'Persetujuan Berhasil' : 'Penolakan Berhasil') : 'Gagal Memproses' }} - PT Mirasa Food Industry</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 40px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .card {
            max-width: 540px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .header {
            padding: 36px 24px 24px;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .icon-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .icon-error {
            background-color: #fef3c7;
            color: #b45309;
        }
        h1 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 8px;
            color: #0f172a;
        }
        p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }
        .body-details {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: left;
            font-size: 13.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }
        .detail-label {
            color: #64748b;
        }
        .detail-value {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }
        .footer {
            padding: 24px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            background: #0284c7;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn:hover {
            background: #0369a1;
        }
        .brand {
            margin-top: 16px;
            font-size: 11px;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            @if ($isSuccess && in_array($action, ['APPROVED', 'ALREADY_APPROVED']))
                <div class="icon-circle icon-success">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1>{{ $action === 'ALREADY_APPROVED' ? 'Dokumen PO Sudah Disetujui' : 'Dokumen PO Berhasil Disetujui!' }}</h1>
                <p>{{ $message }}</p>
            @elseif ($isSuccess && in_array($action, ['REJECTED', 'ALREADY_REJECTED']))
                <div class="icon-circle icon-rejected">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1>{{ $action === 'ALREADY_REJECTED' ? 'Dokumen PO Sudah Ditolak' : 'Pengajuan PO Telah Ditolak' }}</h1>
                <p>{{ $message }}</p>
            @else
                <div class="icon-circle icon-error">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h1>Terjadi Kesalahan</h1>
                <p>{{ $message }}</p>
            @endif
        </div>

        @if ($po)
            <div class="body-details">
                <div class="detail-row">
                    <span class="detail-label">Nomor Dokumen PO</span>
                    <span class="detail-value" style="color: #0284c7;">{{ $po->po_no }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mitra Supplier</span>
                    <span class="detail-value">{{ $po->supplier?->supplier_nm ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Gudang Tujuan</span>
                    <span class="detail-value">{{ $po->gudang?->gudang_nm ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Nilai Tagihan</span>
                    <span class="detail-value" style="color: #059669;">Rp {{ number_format((float) $po->total_tagihan, 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status Terkini</span>
                    <span class="detail-value">
                        @if ($po->isApproved())
                            <span style="color: #16a34a;">✓ APPROVED</span>
                        @elseif ($po->isRejected())
                            <span style="color: #dc2626;">✕ REJECTED</span>
                        @else
                            {{ $po->status_cd }}
                        @endif
                    </span>
                </div>
                @if ($po->approved_at)
                    <div class="detail-row">
                        <span class="detail-label">Waktu Approval</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($po->approved_at)->translatedFormat('d F Y H:i') }} WIB</span>
                    </div>
                @endif
            </div>
        @endif

        <div class="footer">
            @if ($po)
                <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" class="btn">
                    Lihat Dokumen PDF Resmi
                </a>
            @endif
            <div class="brand">
                PT MIRASA FOOD INDUSTRY &bull; SISTEM ERP TERPADU
            </div>
        </div>
    </div>
</body>
</html>
