# Catatan Perubahan Proyek (Project Changelog)

Dokumen ini digunakan untuk mencatat setiap perubahan kode, fitur, perbaikan bug, atau konfigurasi sistem ERP PT Mirasa Food Industry[cite: 9].

---

## 📋 Aturan Pencatatan Changelog
1. **Siapa yang Mencatat**: Developer maupun AI Agent wajib memperbarui file ini setelah melakukan modifikasi file atau fitur[cite: 9].
2. **Kapan Dicatat**: Sebelum melakukan commit ke Git[cite: 9].
3. **Format Tanggal**: Menggunakan standar ISO `YYYY-MM-DD`[cite: 9].

---

## 📜 Riwayat Perubahan Proyek

### 2026-09-22 - Inisialisasi Struktur Proyek ERP & Panduan AI Agent
* **Penulis (Author)**: AI Agent Antigravity
* **Tipe Perubahan**: `Added`[cite: 9]
* **Rincian Perubahan**:
  - `MODULE_MAP.md` (Baru): Penyesuaian struktur direktori Laravel Service-Pattern.
  - `DECISIONS.md` (Baru): Keputusan implementasi Prefix Database dan Audit Trail 8 Kolom.
  - `CODING_STYLE.md` (Baru): Integrasi prinsip Clean Code PHP.
  - `AI_RULES.md` (Baru): Instruksi khusus AI agar fokus pada arsitektur F&B Pabrik.
  - `CHANGELOG.md` (Baru): Pembuatan sistem pencatatan riwayat perubahan[cite: 9].

### 2026-09-22 - Pembuatan AuditableTrait, Migrasi PostgreSQL, & 6 Model MasterData (mst data.md)
* **Penulis (Author)**: AI Agent Antigravity
* **Tipe Perubahan**: `Added` & `Updated`
* **Rincian Perubahan**:
  - `app/Traits/AuditableTrait.php`: Otomasi 8 kolom audit trail (`created_by`, `updated_by`, `deleted_by`, `deleted_st`, `active_st`, timestamps) pada Eloquent.
  - Migrasi PostgreSQL: 6 tabel master (`mst_jenis_barang`, `mst_satuan`, `mst_gudang`, `mst_supplier`, `mst_customer`, `mst_barang`) dengan penamaan kolom berstandar `_cd`, `_nm`, `_txt`, `_no`, dan `decimal(16,4)`.
  - `app/Models/MasterData/MstJenisBarang.php` (Baru): Model master jenis barang.
  - `app/Models/MasterData/MstSatuan.php` (Baru): Model master satuan unit.
  - `app/Models/MasterData/MstGudang.php` (Baru): Model master gudang.
  - `app/Models/MasterData/MstSupplier.php` (Baru): Model master data supplier.
  - `app/Models/MasterData/MstCustomer.php` (Baru): Model master data customer / mitra B2B.
  - `app/Models/MasterData/MstBarang.php` (Baru): Model master barang dengan relasi jenisBarang, satuanDasar, satuanBesar, dan konversi_qty.
  - `app/Http/Requests/MasterData/StoreBarangRequest.php` (Baru): FormRequest validasi tambah barang dengan pengecekan unique dan pesan kustom bahasa Indonesia.
  - `app/Http/Requests/MasterData/UpdateBarangRequest.php` (Baru): FormRequest validasi update barang dengan ignore unique ID dan pesan kustom bahasa Indonesia.