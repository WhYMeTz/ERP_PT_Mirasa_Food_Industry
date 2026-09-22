# Panduan Refactoring (Refactoring Guidelines)

Refactoring adalah proses restrukturisasi kode internal tanpa mengubah perilaku eksternal sistem[cite: 20]. Tujuannya adalah meningkatkan keterbacaan, mengurangi kompleksitas, dan mempermudah pemeliharaan Service Layer.

## Prinsip Utama Refactoring

### 1. Pastikan Tes Berjalan Terlebih Dahulu
* Jangan melakukan refactoring pada kode yang tidak memiliki unit test yang memadai[cite: 20].
* Jalankan tes sebelum mulai mengubah kode untuk memastikan status awal stabil[cite: 20].

### 2. Lakukan Secara Bertahap (Small Steps)
* Jangan melakukan refactoring skala besar sekaligus[cite: 20]. Ubah satu bagian kecil, jalankan tes, lalu lanjutkan[cite: 20].
* Pisahkan commit refactoring dari commit penambahan fitur atau perbaikan bug[cite: 20].

### 3. Aturan Kebersihan Kode
* **DRY (Don't Repeat Yourself)**: Satukan logika duplikat ke dalam fungsi atau modul utilitas bersama (misalnya Trait)[cite: 20].
* **KSRP (Keep It Simple, Stupid)**: Hindari over-engineering[cite: 20]. Pilih solusi yang paling mudah dipahami[cite: 20].
* **Separation of Concerns**: Pisahkan logika bisnis dari logika presentasi UI atau akses database langsung[cite: 20].