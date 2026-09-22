# Aturan Operasional AI Agent (AI Agent Rules)

Dokumen ini berisi instruksi khusus untuk AI Agent yang bekerja pada repositori ERP PT Mirasa Food Industry ini[cite: 8].

---

## 🤖 1. Aturan Tindakan AI Agent

### A. Sebelum Melakukan Perubahan
* **Wajib Membaca Aturan Database**: Sebelum menulis model atau migration, AI wajib membaca `DECISIONS.md` untuk memahami standar Prefix Tabel (`mst_` dan `dat_`) serta kewajiban menyisipkan 8 kolom Audit Trail[cite: 8].
* **Pahami Arsitektur**: Baca `MODULE_MAP.md` untuk memastikan di mana file Controller dan Service harus diletakkan (Service-Pattern)[cite: 8].

### B. Selama Menulis Kode
* Patuhi prinsip pemrograman Laravel yang ada di `CODING_STYLE.md` (Jangan gunakan Fat Controller, pindahkan logika ke Service)[cite: 8].
* Jangan menginstal *package* Composer atau NPM baru tanpa konfirmasi eksplisit dari pengguna[cite: 8].
* **⚠️ ATURAN EMAS (Tipe Data)**: Jika menangani kolom jumlah (qty), harga, diskon, atau HPP, wajib gunakan tipe data `decimal(16,4)`[cite: 8].

### C. Setelah Melakukan Perubahan
* **Catat Perubahan (Changelog)**: Wajib catat detail perubahan ke dalam berkas `CHANGELOG.md` sesuai format tabel yang ditentukan[cite: 8].

---

## ✍️ 2. Panduan Pembuatan Prompt (Prompt Engineering Guide)

Untuk kolaborasi manusia dan AI di project ini, gunakan struktur berikut[cite: 8]:

1. **Role**: "Bertindaklah sebagai Senior Laravel 12 Developer"[cite: 8].
2. **Context**: "Sistem ERP pabrik F&B. Memiliki fitur Multi-Gudang dan Batch Tracking FIFO."
3. **Task**: "Buatkan file Service untuk memotong stok."
4. **Constraints**: "**🚨 JANGAN LAKUKAN** eksekusi logic database di Controller. Jangan lupakan DB Transaction."
5. **Output**: "Kode PHP lengkap dan nama path file yang sesuai."