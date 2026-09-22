# Rencana Pengembangan Fitur (Project Roadmap)

Dokumen ini berisi daftar pencapaian (*milestones*) dan rencana pengembangan fitur ke depan[cite: 6].

## Milestones

### Fase 1: Modul Gudang & Pondasi (Inbound & Inventory)
* [x] Inisialisasi proyek Laravel 12 dan struktur folder[cite: 6].
* [ ] Pembuatan `AuditableTrait` dan Skema Tabel Master (Kategori, Barang, Supplier, Gudang).
* [ ] Implementasi alur Transaksi Inbound (PO & Terima Barang) dengan sistem FIFO dan Batch Tracking.
* [ ] Pembangunan `StokService` sebagai mesin utama pencatat Ledger dan Mutasi antar Gudang.

### Fase 2: Modul Produksi (Operasional Pabrik)
* [ ] Integrasi Gudang ↔ Produksi (Form Pemakaian & Hasil Produksi).
* [ ] Implementasi fitur Bill of Materials (BOM) / Resep Standar[cite: 6].
* [ ] Laporan susut produksi (Yield & Waste) dari proses singkong mentah menjadi produk jadi/sampingan.

### Fase 3: Modul Keuangan & Outbound Sales
* [ ] Implementasi Delivery Order pengiriman B2B ke Indofood.
* [ ] Kalkulasi otomatis HPP (Harga Pokok Penjualan) berdasarkan data produksi.
* [ ] Integrasi Invoice dan Pembayaran PO ke *supplier*[cite: 6].