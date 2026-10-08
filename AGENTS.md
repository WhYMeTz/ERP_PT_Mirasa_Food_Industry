# Pedoman Pengembang & AI Agent - ERP PT Mirasa Food Industry

Dokumen ini adalah aturan wajib (*mandatory guidelines*) untuk setiap AI Coding Assistant dan pengembang dalam memodifikasi atau membuat modul baru di repositori ERP Mirasa.

---

## 🎨 1. ATURAN WAJIB STRUKTUR HALAMAN & PEMISAHAN BERKAS (SEPARATION OF CONCERNS)

Setiap pembuatan atau modifikasi modul tampilan (*view*) **WAJIB MEMISAHKAN HTML, CSS, DAN JAVASCRIPT** ke berkasnya masing-masing:

### A. File Blade View (`resources/views/...`)
* File `.blade.php` **HANYA BERISI ELEMEN HTML/STRUKTUR BLADE**.
* **DILARANG KERAS** menyisipkan blok tag `<style>` besar atau blok `<script>` logika kompleks langsung di dalam berkas Blade.
* Panggil file CSS menggunakan `@push('styles')` di bagian atas:
  ```blade
  @push('styles')
      <link rel="stylesheet" href="{{ asset('css/<modul>/<halaman>.css') }}">
  @endpush
  ```
* Panggil file JS menggunakan `@push('scripts')` di bagian bawah:
  ```blade
  @push('scripts')
      <script>
          window.appConfig = { ... }; // Variabel PHP/URL dinamis yang dibutuhkan JS
      </script>
      <script src="{{ asset('js/<modul>/<halaman>.js') }}"></script>
  @endpush
  ```
* Dialog modal, template baris tabel (*item-row-template*), atau kartu filter picker yang besar **WAJIB DIPISAH** ke berkas partials:
  `resources/views/<modul>/partials/modal-<nama>.blade.php` lalu di-`@include()`.

### B. File Stylesheet (`public/css/<modul>/...`)
* Lokasi: `public/css/<kategori>/<modul>/<nama-halaman>.css`
  * Contoh: `public/css/master/barang/barang-index.css`
  * Contoh: `public/css/gudang/terima/terima-form.css`
* Berisi seluruh styling spesifik halaman, animasi, responsive breakpoint, dan dropdown menu.

### C. File JavaScript (`public/js/<modul>/...`)
* Lokasi: `public/js/<kategori>/<modul>/<nama-halaman>.js`
  * Contoh: `public/js/master/barang/barang-index.js`
  * Contoh: `public/js/gudang/terima/terima-create.js`
* Berisi kalkulasi matematis, event listener DOM, AJAX request, validasi submit form, dan interaksi UI.

---

## 🔘 2. STANDAR TOMBOL AKSI TABEL (SMART ACTION DROPDOWN)

Untuk menjaga konsistensi UI di seluruh sistem ERP:
1. **Dropdown Aksi Terpadu (Format Wajib PO):**
   * Setiap tabel indeks data **WAJIB** menggunakan tombol trigger `.btn-action-trigger` dengan elemen teks `<span>Aksi</span>` dan ikon SVG Chevron Down (DILARANG menggunakan teks mentah atau karakter simbol seperti `▼` / `&#9660;`):
     ```blade
     <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
         <span>Aksi</span>
         <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
     </button>
     <div id="{{ $menuId }}" class="action-dropdown-menu">
         <a href="..." class="action-dropdown-item">
             <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
             <span>Lihat Detail</span>
         </a>
         ...
     </div>
     ```
   * Styling `.btn-action-trigger`: border `1.5px solid #cbd5e1`, `border-radius: 6px`, `font-size: 0.8rem`, `font-weight: 700`, hover aksen hijau emerald `#059669` / `#f0fdf4`.
   * Setiap item dropdown wajib memiliki ikon SVG enterprise (14x14px), font `0.8125rem`, dan hover hijau emerald `#f0fdf4` / teks `#059669` (atau merah `#fef2f2` / `#dc2626` untuk `.danger-item`).
   * Wajib menggunakan fungsi `toggleSmartActionDropdown(this, event, menuId)` dengan `position: fixed` agar menu tidak terpotong oleh overflow scroll tabel.
2. **Modal Konfirmasi Aksi Kritis (Hapus / Batal / Void):**
   * **DILARANG MENGGUNAKAN** pop-up bawaan browser seperti `confirm('Yakin?')` atau `alert()`.
   * Wajib menggunakan Modal Dialog khusus bertema bahaya (`#modalDelete...` dengan header merah `#fef2f2` dan pesan peringatan dampak operasional).

---

## ⚙️ 3. STANDAR BACKEND, SERVICE PATTERN & DATABASE

1. **Thin Controller, Rich Service:**
   * Controller hanya bertugas memvalidasi request HTTP dan mengembalikan view / JSON response.
   * Seluruh kalkulasi stok, mutasi kartu stok, perhitungan HPP, dan transaksi database diletakkan di `app/Services/`.
2. **Kewajiban Database Transaction:**
   * Setiap operasi mutasi barang, pembuatan/perubahan penerimaan, dan pengeluaran bahan baku **WAJIB** dibungkus dalam `DB::transaction(function() { ... })`.
3. **Tipe Data & Keuangan:**
   * Kolom kuantitas (qty), timbangan, rendemen, harga satuan, diskon, PPN, dan HPP wajib menggunakan format numerik presisi tinggi (`decimal(16,4)` atau `float` di view).
   * Nilai rupiah selalu diformat rapi: `Rp number_format($nominal, 0, ',', '.')`.
4. **8 Kolom Audit Trail:**
   * Setiap tabel transaksi (`dat_`) dan master (`mst_`) wajib memiliki kolom audit: `created_by`, `created_dt`, `updated_by`, `updated_dt`, `active_st`, `deleted_st`, `version_no`.
5. **Hak Akses & Role Middleware:**
   * Setiap fitur dan tombol aksi dicek menggunakan `Auth::user()->canDo(...)` atau helper method di `App\Models\User.php`.
   * Route dilindungi middleware `role:<nama_permission>`.
