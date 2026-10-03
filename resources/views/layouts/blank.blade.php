<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dokumen Mutu HACCP - ERP PT Mirasa')</title>
    <style>
        @media print {
            html, body { background: #ffffff !important; padding: 0 !important; margin: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body style="background: #f8fafc; margin: 0; padding: 1.25rem 0.75rem; color: #0f172a; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;">
    @yield('content')
    @stack('scripts')
</body>
</html>
