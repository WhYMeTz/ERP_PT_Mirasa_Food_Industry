# Peta Modul Aplikasi (Module Map)

Gunakan dokumen ini untuk memahami struktur direktori utama dan tanggung jawab masing-masing modul dalam proyek ERP PT Mirasa Food Industry[cite: 3].

## Struktur Direktori Utama Laravel (Service-Pattern)

```text
.
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── MasterData/   # Modul CRUD untuk Data Master (Kategori, Barang, Supplier)
│   │   │   ├── Gudang/       # Modul Transaksi Gudang (PO, Terima, Pakai, Mutasi)
│   │   │   ├── Produksi/     # Modul Operasional Pabrik (BOM, SPK, Hasil Produksi)
│   │   │   └── Keuangan/     # Modul Finansial (HPP, Invoice, Pembayaran)
│   │   └── Requests/         # Validasi form input khusus
│   ├── Models/               # Skema database dan relasi Eloquent (Master, Gudang, System)
│   ├── Services/             # Logika bisnis utama (StokService, HppService) untuk menghindari Fat Controller
│   ├── Traits/               # Fungsi pembantu global (contoh: AuditableTrait untuk 8 kolom audit)
│   └── Observers/            # Pemicu otomatis (Trigger) berbasis event model
├── database/
│   └── migrations/           # Skema tabel PostgreSQL dengan standar prefix perusahaan
├── resources/
│   └── views/                # UI Komponen berbasis Blade HTML
└── routes/                   # Definisi routing (web.php, web_gudang.php, web_produksi.php)