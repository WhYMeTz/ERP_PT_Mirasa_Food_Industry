<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tolak Purchase Order #{{ $po->po_no }} - PT Mirasa Food Industry</title>
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
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        }
        .header {
            padding: 28px 24px 20px;
            text-align: center;
            background-color: #fef2f2;
            border-bottom: 1px solid #fee2e2;
        }
        .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            margin: 0 auto 12px;
            background-color: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1 {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 6px;
            color: #991b1b;
        }
        p {
            font-size: 13.5px;
            color: #64748b;
            margin: 0;
            line-height: 1.4;
        }
        .content {
            padding: 24px;
        }
        .po-summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13.5px;
            color: #1e293b;
            resize: vertical;
            min-height: 100px;
            outline: none;
            font-family: inherit;
        }
        textarea:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
        }
        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-cancel {
            background: #e2e8f0;
            color: #475569;
        }
        .btn-reject {
            background: #dc2626;
            color: #ffffff;
        }
        .btn-reject:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon-circle">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h1>Konfirmasi Penolakan PO</h1>
            <p>Berikan catatan atau alasan penolakan agar tim purchasing dapat melakukan revisi dokumen.</p>
        </div>

        <div class="content">
            <div class="po-summary">
                <div><strong>Nomor PO:</strong> {{ $po->po_no }}</div>
                <div><strong>Supplier:</strong> {{ $po->supplier?->supplier_nm ?? '-' }}</div>
                <div><strong>Total Tagihan:</strong> Rp {{ number_format((float) $po->total_tagihan, 0, ',', '.') }}</div>
            </div>

            <form action="{{ route('po.approval.reject.process', $token) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="reject_reason">Alasan Penolakan <span style="color: #dc2626;">*</span></label>
                    <textarea id="reject_reason" name="reject_reason" placeholder="Contoh: Harga per unit terlalu tinggi dibanding negosiasi, silakan tawar ulang atau kuantitas dikurangi menjadi..." required>{{ old('reject_reason') }}</textarea>
                    @error('reject_reason')
                        <div style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="actions">
                    <a href="{{ route('po.approval.direct', ['token' => $token, 'action' => 'approve']) }}" class="btn btn-cancel">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-reject">
                        Konfirmasi Tolak PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
