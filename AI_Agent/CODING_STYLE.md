# Panduan Gaya Penulisan Kode (Coding Style Guide)

Panduan ini mendefinisikan standar penulisan kode bersih (*Clean Code*), efisien, dan rapi yang harus dipatuhi oleh seluruh developer dan AI Agent untuk menjamin keterbacaan serta kemudahan pemeliharaan (*maintainability*) sistem[cite: 5].

## 5 Prinsip Utama Clean Code

1. **Hindari "Deep Nesting" (Arrow Code):** Terapkan teknik Guard Clauses untuk membersihkan kondisi error di awal sehingga logika utama berada di tingkat indentasi terluar[cite: 5].
2. **Berhenti Menggunakan "Magic Numbers":** Definisikan nilai statis sebagai Descriptive Constants di bagian atas file agar memiliki label yang jelas[cite: 5].
3. **Utamakan Deklaratif daripada Imperatif:** Gunakan fungsi tingkat tinggi (*higher-order functions*) yang lebih deskriptif[cite: 5].
4. **Prinsip "Satu Fungsi, Satu Tanggung Jawab" (SRP):** Pisahkan fungsi besar menjadi unit-unit kecil yang mandiri di dalam folder `app/Services/`[cite: 5].
5. **Gunakan Objek untuk Argumen yang Banyak:** Gunakan Object Destructuring agar urutan parameter tidak menjadi masalah[cite: 5].

## Standar Formatting Universal ERP

1. **Naming Conventions**:
   * **Variabel & Fungsi**: `camelCase`[cite: 5].
   * **Kelas & Model**: `PascalCase`[cite: 5].
   * **Konstanta**: `UPPER_SNAKE_CASE`[cite: 5].
   * **Database**: Kolom status wajib menggunakan akhiran `_st`, kode menggunakan `_cd`, dan nama menggunakan `_nm`.
2. **Indentasi**: Selalu gunakan 4 spasi untuk kode PHP/Laravel secara konsisten[cite: 5].
3. **Panjang Baris**: Batasi baris kode maksimal 120 karakter[cite: 5].