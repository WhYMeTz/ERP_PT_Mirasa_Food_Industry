# Panduan Manajemen Dependensi (Dependency Management)

Dokumen ini mengatur cara penambahan dan pemeliharaan pustaka (*third-party dependencies*) di ekosistem Laravel 12[cite: 10].

## Kebijakan Dependensi ERP PT Mirasa

### 1. Minimalisasi Dependensi
* Sebelum melakukan `composer require` atau `npm install`, evaluasi apakah fungsionalitas tersebut dapat dibuat secara mandiri menggunakan fitur bawaan Laravel[cite: 10].
* Hindari memasang *package* yang berukuran besar jika hanya menggunakan sebagian kecil fiturnya[cite: 10].

### 2. Keamanan & Stabilitas
* Selalu gunakan versi pustaka yang stabil. Hindari versi beta/RC[cite: 10].
* Versi dependensi akan dikunci secara otomatis oleh `composer.lock` (untuk backend) dan `package-lock.json` (untuk frontend assets)[cite: 10]. File *lock* wajib di-commit ke Git.

### 3. Audit Berkala
* Lakukan pemeriksaan celah keamanan menggunakan `composer audit` dan `npm audit` sebelum mendeploy ke server produksi[cite: 10].