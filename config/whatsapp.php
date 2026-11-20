<?php
// config/whatsapp.php — WhatsApp Business Cloud API (Meta). Nilai rahasia HANYA di .env, jangan di-commit.
return [
    'api_version'       => env('WHATSAPP_API_VERSION', 'v25.0'),
    'graph_url'         => env('WHATSAPP_GRAPH_URL', 'https://graph.facebook.com'),
    'access_token'      => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id'   => env('WHATSAPP_PHONE_NUMBER_ID'),
    'template_name'     => env('WHATSAPP_TEMPLATE_NAME', 'slip_pembayaran'),
    'template_language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'id'),
    'timeout'           => (int) env('WHATSAPP_TIMEOUT', 60),

    // dipakai sebagai isi {{no_hrd_info}} di template dan di balasan otomatis
    'no_hrd_info'       => env('NO_HRD_INFO', ''),

    // webhook (tahap berikutnya)
    'verify_token'      => env('WHATSAPP_VERIFY_TOKEN'),
    'app_secret'        => env('META_APP_SECRET'),
];
