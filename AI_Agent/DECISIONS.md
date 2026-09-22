### 2. `DECISIONS.md`
Catatan ini diperbarui untuk merekam keputusan arsitektur database yang sangat spesifik di perusahaanmu[cite: 4].

```markdown
# Catatan Keputusan Arsitektur (Architecture Decision Records - ADR)

Dokumen ini mencatat keputusan teknologi, pustaka, dan arsitektur penting yang diambil selama pengembangan proyek ERP[cite: 4].

## ADR-001: Standar Penamaan Database
* **Tanggal**: 2026-09-22
* **Status**: Accepted[cite: 4]
* **Konteks**: Mencegah kebingungan antara data referensi dan data transaksi operasional pabrik[cite: 4].
* **Keputusan**: Menggunakan prefix `mst_` untuk tabel master dan `dat_` untuk tabel transaksi. Penamaan kolom menggunakan format `{umum}_{khusus}`. Tipe data finansial dan kuantitas wajib menggunakan `decimal(16,4)`[cite: 4].
* **Konsekuensi**: Mengubah standar bawaan Laravel. Model Eloquent wajib mendefinisikan `$table` secara manual[cite: 4].

## ADR-002: Implementasi Audit Trail Global
* **Tanggal**: 2026-09-22
* **Status**: Accepted[cite: 4]
* **Konteks**: Kebutuhan pelacakan (traceability) untuk audit keuangan dan operasional ERP[cite: 4].
* **Keputusan**: Menambahkan 8 kolom audit (`created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`, `deleted_st`, `active_st`) di semua tabel menggunakan *Global Trait* (`AuditableTrait`)[cite: 4].
* **Konsekuensi**: Mengurangi repetisi kode di Controller, namun mempe