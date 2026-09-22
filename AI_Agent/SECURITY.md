---

### 2. `SECURITY.md`
Diperbarui dengan menambahkan konteks keamanan spesifik untuk transaksi uang/stok (HPP) dan fitur *Mass Assignment* Eloquent di Laravel[cite: 14].

```markdown
# Kebijakan Keamanan Sistem (System Security Policy)

Dokumen ini mendefinisikan standar keamanan krusial untuk proyek ERP PT Mirasa. Semua *developer* dan AI Agent wajib mematuhi panduan ini[cite: 14].

## 🔒 1. Keamanan Sisi Backend (Server-Side)

### A. Validasi & Sanitasi Input (Wajib Form Request)
* **Aturan Emas**: Validasi semua input menggunakan Laravel `FormRequest` class[cite: 14]. Jangan pernah validasi di dalam Controller.
* **Tindakan**:
  - Kolom harga nominal, stok *qty*, dan nilai konversi rawan terhadap injeksi. Wajib divalidasi sebagai tipe *numeric* dan dikonversi ke *decimal* sebelum masuk ke Service Layer.

### B. Otentikasi & Otorisasi Dinamis
* **Otentikasi**: Gunakan Laravel Auth bawaan. Password di-hash menggunakan algoritma *Bcrypt* otomatis oleh Laravel[cite: 14].
* **Otorisasi RBAC**: Gunakan relasi tabel matriks `dat_role_menu` yang telah didefinisikan di database untuk membatasi aksi CRUD setiap peran (*Role*)[cite: 14].

### C. Perlindungan Mass Assignment
* Jaga integritas tabel ERP menggunakan properti `$fillable` di model Eloquent.
* Jangan pernah menggunakan `$guarded = []`. Ini membuka celah keamanan bagi *user* nakal yang ingin mengubah *value* tersembunyi (seperti mengubah *ID Gudang* atau menyuntikkan *active_st*).

## 🎨 2. Keamanan Sisi Frontend (Client-Side)

### A. Pencegahan CSRF
* Karena ini adalah aplikasi monolith Laravel berbasis web, sertakan `@csrf` di setiap form `<form>` HTML/Blade[cite: 14].

### B. Pencegahan XSS
* Gunakan kurung kurawal ganda `{{ $data }}` milik Laravel Blade untuk merender output ke layar (fitur *auto-escaping* aktif)[cite: 14]. Jangan gunakan `{!! !!}` kecuali benar-benar dibutuhkan dan data telah disanitasi.

## 🛠️ 3. Pengelolaan Rahasia (Secret Management)
* **Aturan Emas**: **JANGAN PERNAH** melakukan commit terhadap file `.env` (berisi password PostgreSQL dan kunci SMTP) ke repositori Git[cite: 14].