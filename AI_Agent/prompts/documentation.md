# Standar Penulisan Dokumentasi (Documentation Standards)

Dokumentasi yang baik sangat penting agar kode Laravel ini mudah dipelihara oleh manusia maupun AI Agent di masa mendatang[cite: 18].

## Aturan Penulisan Dokumentasi

### 1. Dokumentasi Kode (Inline & Block Comments)
* **Mengapa, bukan Bagaimana**: Tulis komentar untuk menjelaskan *mengapa* suatu keputusan kode diambil, bukan menjelaskan *bagaimana* kode itu bekerja (karena kode itu sendiri harus sudah cukup jelas/self-documenting)[cite: 18].
* Gunakan standar komentar dokumentasi sesuai bahasa pemrograman:
  - PHP: DocBlocks (`/** ... */`) untuk Model dan Service[cite: 18].
  - Vue/JavaScript: JSDoc (`/** ... */`)[cite: 18].

### 2. Dokumentasi API (API Documentation)
* Setiap endpoint API baru wajib didokumentasikan dengan jelas[cite: 18]:
  - URL & HTTP Method (e.g., `POST /api/v1/mutasi`)[cite: 18].
  - Header yang dibutuhkan (e.g., `Authorization`)[cite: 18].
  - Format request body (JSON schema)[cite: 18].
  - Format response sukses (status 200/201) dan response error (status 400/401/500)[cite: 18].

### 3. Pembaruan README
* Jika ada perubahan cara instalasi, dependensi baru, atau variabel lingkungan baru di `.env`, pastikan untuk langsung memperbarui berkas `README.md` dan `PROJECT_RULES.md`[cite: 18].