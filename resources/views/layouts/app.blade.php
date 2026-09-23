<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP PT Mirasa Food Industry')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #0f172a;
            font-weight: 700;
            font-size: 1.15rem;
        }
        .brand-badge {
            background: #0284c7;
            color: #ffffff;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
        }
        nav a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.2s;
        }
        nav a:hover, nav a.active {
            color: #0284c7;
            background: #f0f9ff;
            font-weight: 600;
        }
        main {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.925rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.125rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        .btn-danger:hover {
            background: #fecaca;
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-align: left;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        tr:hover td {
            background: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-info {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-success {
            background: #ecfdf5;
            color: #065f46;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.375rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: #334155;
        }
        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.925rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2,132,199,0.15);
        }
        .form-error {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* Modal Popup Styling */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .modal-backdrop.show {
            display: flex;
        }
        .modal-dialog {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 620px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            animation: modalSlideIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-16px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #94a3b8;
            line-height: 1;
            transition: color 0.15s;
        }
        .modal-close:hover {
            color: #334155;
        }
        .modal-body {
            padding: 1.5rem;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background: #f8fafc;
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            color: #94a3b8;
            font-size: 0.85rem;
            border-top: 1px solid #e2e8f0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header>
        <a href="{{ route('master.barang.index') }}" class="brand">
            <span class="brand-badge">ERP</span>
            PT Mirasa Food Industry
        </a>
        <nav style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
            <a href="{{ route('master.barang.index') }}" class="{{ request()->routeIs('master.barang.*') ? 'active' : '' }}">Barang</a>
            <a href="{{ route('master.satuan.index') }}" class="{{ request()->routeIs('master.satuan.*') ? 'active' : '' }}">Satuan</a>
            <a href="{{ route('master.jenis.index') }}" class="{{ request()->routeIs('master.jenis.*') ? 'active' : '' }}">Jenis Barang</a>
            <a href="{{ route('master.gudang.index') }}" class="{{ request()->routeIs('master.gudang.*') ? 'active' : '' }}">Gudang</a>
            <a href="{{ route('master.jenis_supplier.index') }}" class="{{ request()->routeIs('master.jenis_supplier.*') ? 'active' : '' }}">Jenis Supplier</a>
            <a href="{{ route('master.supplier.index') }}" class="{{ request()->routeIs('master.supplier.*') ? 'active' : '' }}">Supplier</a>
            <a href="{{ route('master.customer.index') }}" class="{{ request()->routeIs('master.customer.*') ? 'active' : '' }}">Customer</a>
        </nav>
    </header>

    <main>
        @if (session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#065f46;font-size:1.1rem;">&times;</button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert alert-error">
                <span>Silakan periksa kembali input formulir Anda.</span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#991b1b;font-size:1.1rem;">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} ERP PT Mirasa Food Industry &bull; Pabrik Pengolahan F&B Singkong
    </footer>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
            }
        }

        // Close when clicking outside modal dialog
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-backdrop')) {
                e.target.classList.remove('show');
            }
        });

        // Close on ESC key
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
            }
        });

        // AJAX Helper untuk auto-generate kode master
        async function fetchNextCode(type, targetInputId, extraParams = {}) {
            const input = document.getElementById(targetInputId);
            if (!input) return;

            let url = '{{ route("ajax.generate_code") }}?type=' + encodeURIComponent(type);
            for (const [key, value] of Object.entries(extraParams)) {
                if (value) url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(value);
            }

            try {
                const btn = document.querySelector(`[data-target="${targetInputId}"]`);
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }

                const res = await fetch(url);
                const data = await res.json();
                if (data.status === 'success' && data.code) {
                    input.value = data.code;
                }

                if (btn) {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            } catch (err) {
                console.error('Gagal mengambil kode otomatis:', err);
            }
        }
    </script>
</body>
</html>
