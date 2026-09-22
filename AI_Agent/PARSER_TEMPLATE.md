# Templat Parser & Validasi Data (Parser & Validation Template)

Dokumen ini mendefinisikan standar cara memproses dan memvalidasi data input dari user (Formulir UI, API Barcode Scanner, dll)[cite: 11].

## Pola Validasi di Laravel

### 1. Wajib Menggunakan "Form Request"
* **🚨 JANGAN PERNAH** melakukan validasi input langsung di dalam Controller menggunakan `$request->validate()`[cite: 11].
* Buat kelas validasi terpisah menggunakan perintah `php artisan make:request NamaModulRequest`.
* Validasi wajib mencakup skema seperti `required`, `numeric`, `date`, `exists` (untuk foreign key), atau aturan kustom jika input berupa nomor *Batch* atau *Barcode*.

### 2. Penanganan Tipe Data
* Laravel secara otomatis mengkonversi string kosong menjadi `null` (*TrimStrings*). Jangan tulis logika pengecekan string kosong secara berulang.
* Semua input kolom nominal dan *qty* harus dikonversi menjadi format `decimal` sebelum dikirim ke Service (hilangkan tanda koma separator ribuan jika ada input dari frontend)[cite: 11].

### 3. Penanganan Data Kosong / Null
* Gunakan fitur `$request->validated()` untuk mendapatkan data yang murni sudah lulus validasi agar terhindar dari injeksi data ekstra (*mass assignment vulnerability*)[cite: 11].