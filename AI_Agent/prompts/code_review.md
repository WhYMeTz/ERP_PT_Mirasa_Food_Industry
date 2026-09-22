# Panduan Peninjauan Kode (Code Review Guidelines)

Gunakan daftar periksa (*checklist*) di bawah ini untuk meninjau setiap perubahan kode sebelum digabungkan ke cabang utama (*main branch*) proyek ERP[cite: 17].

---

## 📋 Checklist Peninjauan (Review Checklist)

### 1. Kebenaran & Logika (Correctness)
* **Kesesuaian Spesifikasi**: Apakah kode melakukan fungsionalitas tepat sesuai dengan deskripsi kebutuhan tugas[cite: 17]?
* **Kasus Batas (Edge Cases)**: Apakah kasus input ekstrem (null, undefined, nilai kosong, angka negatif) telah ditangani dengan aman[cite: 17]?
* **Aliran Loop & Memori**: Apakah tidak ada risiko *infinite loops* atau kebocoran memori (*memory leak*)[cite: 17]?

### 2. Kepatuhan Gaya Kode (Style & Readability)
* **Konsistensi Penamaan**: Apakah variabel, fungsi, dan kelas sudah mematuhi konvensi penamaan di file `CODING_STYLE.md`[cite: 17]?
* **Kompleksitas Fungsi**: Apakah fungsi berukuran kecil dan fokus (*Single Responsibility Principle*), serta tidak ada fungsi raksasa di dalam Controller[cite: 17]?
* **Kebersihan File**: Apakah tidak ada kode usang yang di-comment (*dead code*) atau variabel yang tidak digunakan (*unused variables*)[cite: 17]?

### 3. Performa & Efisiensi (Performance)
* **Optimalisasi Database**: Apakah query database Eloquent sudah efisien dan tidak memicu masalah *N+1 query* dengan menggunakan Eager Loading[cite: 17]?
* **Operasi Asinkron**: Apakah operasi I/O atau loop berat didelegasikan ke pemrosesan asinkron (*non-blocking*)[cite: 17]?
* **Efisiensi Memori**: Apakah penggunaan memori optimal dan menghindari komputasi duplikat[cite: 17]?

### 4. Keamanan (Security)
* **Sanitasi Input**: Apakah semua input eksternal divalidasi (menggunakan FormRequest) dan disanitasi untuk mencegah SQL Injection & XSS[cite: 17]?
* **Kerahasiaan Kunci**: Apakah tidak ada API key, token, atau password yang tertulis secara mentah (*hardcoded*) di dalam kode[cite: 17]?

### 5. Pengujian (Testing)
* **Cakupan Tes (Coverage)**: Apakah perubahan penting wajib disertai unit test atau integration test[cite: 17]?
* **Skenario Tes**: Apakah skenario mencakup jalur sukses (*happy path*) dan jalur penanganan error (*sad path*)[cite: 17]?