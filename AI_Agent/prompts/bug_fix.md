# Workflow Perbaikan Bug (Bug Fix Workflow)

Dokumen ini berisi panduan bagi AI Agent dan Developer dalam mendeteksi, mereproduksi, dan memperbaiki masalah (bug) di dalam aplikasi ERP PT Mirasa[cite: 16].

## Langkah-Langkah Perbaikan Bug

### 1. Reproduksi Masalah (Reproduce)
* **Wajib**: Sebelum menulis kode perbaikan, buatlah skenario pengetesan atau script mandiri yang dapat mereproduksi error secara konsisten[cite: 16].
* Gunakan test case otomatis jika memungkinkan[cite: 16].
* Dokumentasikan:
  - Input yang memicu error[cite: 16].
  - Output yang salah[cite: 16].
  - Output yang diharapkan[cite: 16].

### 2. Analisis Penyebab (Root Cause Analysis)
* Telusuri *stack trace* atau log sistem (seperti `storage/logs/laravel.log`) untuk menemukan file dan baris kode yang bermasalah[cite: 16].
* Identifikasi mengapa kondisi error tersebut terjadi (misal: *null pointer*, *race condition*, tipe data tidak sesuai, logika percabangan salah)[cite: 16].
* Periksa apakah masalah ini memengaruhi modul operasional pabrik yang lain[cite: 16].

### 3. Implementasi Perbaikan (Implementation)
* Buat perbaikan seminimal dan seaman mungkin (*least disruptive fix*)[cite: 16].
* Hindari mengubah fungsionalitas yang tidak berhubungan dengan bug tersebut[cite: 16].
* Pastikan penanganan kesalahan (*error handling*) dan *edge cases* ditangani dengan baik (misal: input form kosong, tipe data tidak valid pada nominal desimal)[cite: 16].

### 4. Verifikasi & Pengujian (Verification)
* Jalankan kembali skenario reproduksi untuk memastikan bug telah hilang[cite: 16].
* Pastikan seluruh unit test yang ada tetap berjalan dengan sukses (*no regression*)[cite: 16].
* Tulis tes unit baru khusus untuk kasus bug ini agar tidak terjadi kembali di masa mendatang[cite: 16].