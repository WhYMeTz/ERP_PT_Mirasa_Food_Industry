# Aturan Khusus Proyek (Project Rules & Constraints)

Dokumen ini berisi batasan khusus dan pengelolaan lingkungan (*environment*) untuk proyek ERP PT Mirasa[cite: 12].

---

## 🛠️ 1. Batasan Lingkungan (Environment Constraints)

### A. Pengelolaan Kredensial (File `.env`)
* Semua konfigurasi koneksi PostgreSQL, API Keys, atau pengaturan SMTP (Email PO) **wajib** disimpan di dalam file `.env`[cite: 12].
* **🚨 JANGAN PERNAH** melakukan *commit* terhadap file `.env` ke Git[cite: 12]. 
* Gunakan file `.env.example` sebagai acuan setup bagi developer atau server *staging*[cite: 12].

### B. Deployment & Lingkungan Laravel
* Tentukan perilaku sistem berdasarkan variabel `APP_ENV` dan `APP_DEBUG`[cite: 12]:
  - `APP_ENV=local` / `APP_DEBUG=true`: Mengaktifkan pesan *error trace* mendetail (Ignition) untuk *debugging*.
  - `APP_ENV=production` / `APP_DEBUG=false`: Wajib digunakan saat rilis di pabrik. Menyembunyikan *error stack trace* ke client, dan pastikan HTTPS (`APP_URL` memakai `https://`) aktif[cite: 12].

---

## 🔒 2. Kepatuhan Keamanan (Security Compliance)
* Untuk seluruh panduan dan taktik pengamanan sistem, merujuklah pada berkas kemudi keamanan: **`SECURITY.md`**[cite: 12].
* Fitur *Mass Assignment* (Eloquent `$fillable`) wajib dilindungi dari modifikasi pihak luar, terutama untuk kolom-kolom yang mengandung nilai *qty*, nominal uang, dan HPP.