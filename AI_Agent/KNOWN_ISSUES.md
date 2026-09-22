# Masalah yang Diketahui (Known Issues)

Daftar *bug* operasional pabrik atau batasan fungsional sistem ERP PT Mirasa saat ini[cite: 15].

---

## ⚠️ Daftar Masalah Aktif

### KI-01: Perhitungan Stok Konversi
* **Deskripsi**: Ketika barang ditarik untuk produksi, sistem belum optimal membulatkan sisa barang jika *user* menarik 1 PCS dari kardus yang berisi 50 PCS.
* **Dampak**: `Sedang`[cite: 15]
* **Solusi Sementara**: Operator Gudang wajib menarik stok dalam Satuan Dasar/Terkecil (misal: 50 PCS, bukan 1 BOX) pada form Pemakaian Barang[cite: 15].

### KI-02: Kunci Batch (Lock Batch) Saat Transit
* **Deskripsi**: Saat Mutasi Antar Gudang sedang berstatus `IN_TRANSIT` (di jalan dari Magelang ke Jakarta), barang secara fisik hilang dari tampilan, tapi nilai HPP-nya belum terselesaikan di sistem Keuangan.
* **Dampak**: `Rendah`[cite: 15]
* **Solusi Sementara**: Tim Keuangan diminta menunggu status surat mutasi berubah menjadi `RECEIVED` di Jakarta sebelum menghitung aset total[cite: 15].