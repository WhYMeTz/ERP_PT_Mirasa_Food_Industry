# Blueprint & AI Workflow: Modul Master Data (ERP PT Mirasa)

**Tujuan:** Membangun fondasi CRUD (Create, Read, Update, Delete) untuk data referensi utama pabrik. Data master ini akan menjadi *Foreign Key* bagi seluruh tabel transaksi operasional gudang dan produksi.

---

## 📌 ATURAN GLOBAL (WAJIB DIBACA OLEH AI)
1. **Service Pattern:** Controller dilarang melakukan `Model::create()` atau `Model::update()` secara langsung. Semua operasi insert/update ke database harus dilakukan di dalam class Service (contoh: `BarangService.php`).
2. **Validasi Request:** Controller wajib menggunakan `FormRequest` (contoh: `StoreBarangRequest`) untuk memvalidasi input. Jangan gunakan `$request->validate()`.
3. **Audit Trail:** Model TIDAK PERLU mengisi kolom `created_by`, `updated_by`, dll secara manual di controller/service. Gunakan trait `AuditableTrait` di dalam setiap Model.
4. **Soft Delete & Active Status:** Saat menghapus data master, ubah `deleted_st` menjadi '1' dan isi `deleted_at`. Jangan gunakan fungsi SQL `DELETE` permanen. Untuk menonaktifkan data tanpa menghapusnya, ubah `active_st` menjadi '0'.

---

## 🗄️ SKEMA TABEL MASTER

Berikut adalah 6 tabel utama yang harus diimplementasikan oleh AI:

1. **`mst_jenis_barang`** (Pengelompokan jenis barang)
   - Kolom: `jenis_barang_id` (PK), `jenis_barang_cd` (Varchar), `jenis_barang_nm` (Varchar).
2. **`mst_satuan`** (Unit hitung)
   - Kolom: `satuan_id` (PK), `satuan_cd` (Varchar), `satuan_nm` (Varchar).
3. **`mst_gudang`** (Lokasi fisik & operasional pabrik)
   - Kolom: `gudang_id` (PK), `gudang_cd` (Varchar), `gudang_nm` (Varchar), `tipe_gudang_cd` (Varchar), `alamat_txt` (Text).
4. **`mst_supplier`** (Pemasok bahan baku)
   - Kolom: `supplier_id` (PK), `supplier_cd` (Varchar), `supplier_nm` (Varchar), `kontak_no` (Varchar), `alamat_txt` (Text).
5. **`mst_customer`** (Klien B2B / Indofood)
   - Kolom: `customer_id` (PK), `customer_cd` (Varchar), `customer_nm` (Varchar), `kontak_no` (Varchar), `alamat_txt` (Text).
6. **`mst_barang`** (Katalog fisik barang)
   - Kolom: `barang_id` (PK), `barang_cd` (Varchar), `barang_nm` (Varchar), `jenis_barang_id` (FK -> mst_jenis_barang), `satuan_dasar_id` (FK -> mst_satuan), `satuan_besar_id` (FK -> mst_satuan), `konversi_qty` (decimal 16,4).

*(Catatan: 8 kolom audit standar wajib ditambahkan di setiap tabel migration).*

---

## 🚀 INSTRUKSI EKSEKUSI UNTUK AI (PROMPTS)

Gunakan prompt di bawah ini secara bertahap (satu per satu) kepada AI Agent untuk menghasilkan kode yang bersih dan sesuai arsitektur.

### 🤖 Tahap 1: Migrations & Models
> "Sebagai Senior Laravel 12 Developer, buatkan file Migrations dan Eloquent Models untuk 6 tabel master: `mst_jenis_barang`, `mst_satuan`, `mst_gudang`, `mst_supplier`, `mst_customer`, dan `mst_barang`.
> 
> Ketentuan:
> 1. Gunakan nama tabel secara eksplisit di dalam Model (`protected $table = 'nama_tabel';`).
> 2. Sertakan 8 kolom audit standar (`created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`, `deleted_st` default '0', `active_st` default '1') di semua tabel migrasi.
> 3. Terapkan `use AuditableTrait;` pada semua Model.
> 4. Buat relasi antar model (contoh: MstBarang `belongsTo` MstJenisBarang dan MstSatuan).
> 5. Kolom `konversi_qty` di `mst_barang` wajib menggunakan tipe `decimal(16,4)`."

### 🤖 Tahap 2: Form Requests (Validasi)
> "Tolong buatkan class FormRequest Laravel untuk tabel `mst_barang` (buatkan `StoreBarangRequest` dan `UpdateBarangRequest`). 
> 
> Ketentuan Validasi:
> 1. `barang_cd` wajib unik di tabel `mst_barang` (perhatikan pengecualian ID saat update).
> 2. `jenis_barang_id`, `satuan_dasar_id`, dan `satuan_besar_id` wajib ada di tabel referensinya masing-masing.
> 3. `konversi_qty` wajib numeric dengan minimal nilai 1.
> 4. Sediakan custom messages dalam bahasa Indonesia jika validasi gagal."

### 🤖 Tahap 3: Service Layer (Logika Bisnis)
> "Buatkan file `app/Services/MasterData/BarangService.php`. Service ini harus menangani logika CRUD untuk `mst_barang`.
> 
> Ketentuan:
> 1. Buat method `getAllPaginated()` dengan Eager Loading (`with(['jenisBarang', 'satuanDasar', 'satuanBesar'])`) untuk mencegah N+1 Query.
> 2. Buat method `store(array $data)`, `update(int $id, array $data)`, dan `delete(int $id)`.
> 3. Pada method `delete()`, pastikan hanya melakukan update nilai `deleted_st` menjadi '1' (Soft Delete), bukan melakukan Hard Delete.
> 4. Gunakan `DB::transaction()` pada proses store dan update untuk menjaga integritas."

### 🤖 Tahap 4: Thin Controllers
> "Buatkan `BarangController.php` di dalam namespace `App\Http\Controllers\MasterData`. 
> 
> Ketentuan:
> 1. Terapkan prinsip Thin Controller. Controller hanya boleh memanggil `BarangService` dan mengembalikan response (return view atau JSON).
> 2. Gunakan Constructor Injection untuk memasukkan `BarangService`.
> 3. Gunakan `StoreBarangRequest` dan `UpdateBarangRequest` pada parameter fungsi `store` dan `update`.
> 4. Jangan ada logika database (`Model::create`, `DB::table`, dll) di dalam controller ini."