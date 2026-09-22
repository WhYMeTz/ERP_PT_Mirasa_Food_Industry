# Standar Pembuatan Tes (Test Generation Standards)

Dokumen ini mendefinisikan standar penulisan tes otomatis menggunakan PHPUnit/Pest untuk memastikan keandalan sistem jangka panjang ERP PT Mirasa[cite: 21].

## Standar Penulisan Tes

### 1. Struktur Pengujian (AAA Pattern)
Setiap test case harus disusun menggunakan pola[cite: 21]:
* **Arrange (Persiapan)**: Siapkan data input, mock, dan konfigurasi yang diperlukan[cite: 21].
* **Act (Aksi)**: Panggil fungsi atau metode yang sedang diuji (seperti fungsi potong stok FIFO)[cite: 21].
* **Assert (Verifikasi)**: Periksa apakah output dan efek samping sesuai dengan ekspektasi[cite: 21].

### 2. Cakupan Kasus (Test Cases Coverage)
* **Happy Path**: Skenario saat sistem menerima input yang benar dan berjalan lancar[cite: 21].
* **Edge Cases**: Kasus batas seperti input bernilai 0, null, string kosong, array kosong, atau angka sangat besar[cite: 21].
* **Sad Path (Error Handling)**: Pastikan sistem melempar error yang tepat saat menerima input yang salah atau saat layanan eksternal mati[cite: 21].

### 3. Isolasi Pengujian (Isolation)
* Unit test tidak boleh bergantung pada jaringan internet, database fisik, atau API pihak ketiga secara langsung[cite: 21]. Gunakan mocks/stubs[cite: 21].
* Setiap tes harus independen dan tidak bergantung pada urutan eksekusi tes lainnya[cite: 21].