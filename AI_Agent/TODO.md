# Daftar Tugas Proyek (Project Todo List)

Gunakan berkas ini untuk melacak progres pekerjaan harian[cite: 7].

## Tugas Aktif

- `[ ]` Tugas yang belum dikerjakan[cite: 7].
- `[/]` Tugas yang sedang dalam proses pengerjaan[cite: 7].
- `[x]` Tugas yang telah selesai diverifikasi[cite: 7].

### Backlog Fitur (Fase 1 - Gudang & Master Data)
* [x] Setup Database PostgreSQL dan penyesuaian `.env`.
* [x] Buat file `app/Traits/AuditableTrait.php` untuk otomasi 8 kolom standar.
* [x] Buat Migrations 6 Tabel Master (`mst_jenis_barang`, `mst_satuan`, `mst_gudang`, `mst_supplier`, `mst_customer`, `mst_barang`) sesuai `mst data.md`.
* [x] Buat Eloquent Models di `app/Models/MasterData/` lengkap dengan relasi dan `AuditableTrait`.
* [x] Buat Form Requests (`StoreBarangRequest`, `UpdateBarangRequest`) di `app/Http/Requests/MasterData/`.
* [ ] Buat Service Layer (`BarangService.php`) di `app/Services/MasterData/`.
* [ ] Buat Controller Thin (`BarangController.php`) di `app/Http/Controllers/MasterData/`.
* [ ] Buat Migrations untuk Tabel Transaksi Header-Detail (`dat_po`, `dat_terima`, `dat_pakai`).
* [ ] Tulis logika backend di `app/Services/Gudang/StokService.php` untuk fungsi `addStock()` dan `deductStock()`.