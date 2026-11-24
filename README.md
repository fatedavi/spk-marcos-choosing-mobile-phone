# SPK MARCOS — Pemilihan Smartphone Gaming

Aplikasi Laravel 11 untuk Sistem Pendukung Keputusan pemilihan smartphone gaming dengan metode **MARCOS** (Measurement of Alternatives and Ranking according to COmpromise Solution). Seluruh langkah perhitungan ditampilkan di halaman hasil dan diekspor ke Excel untuk keperluan pembuktian skripsi.

## Fitur

- Autentikasi Laravel Breeze (register, login, verifikasi email, reset password) dengan role `admin` dan `user`.
- Admin: kelola kriteria, sub-kriteria, 32 alternatif dari `data.xlsx`, nilai mentah, dan rekap kuesioner.
- User & admin: bandingkan 2-4 HP secara side-by-side (nilai mentah, skor, bobot, penanda unggul, dan ringkasan skor terbobot).
- User: lihat daftar smartphone, tentukan rentang kriteria, pilih alternatif, lalu jalankan perhitungan.
- MARCOS lengkap: AI/AAI, normalisasi, matriks berbobot, `S_i`, `K-`, `K+`, `f(K-)`, `f(K+)`, `f(K_i)`, dan ranking kompetisi.
- Ekspor Excel 8 sheet: Ringkasan, Kriteria, Matriks, AI_AAI, Normalisasi, Berbobot, S_K_F, dan Perbandingan (kolom rumus untuk membandingkan dengan Excel manual).
- Riwayat perhitungan tersimpan di database.

## Kebutuhan

- PHP 8.2+ (terverifikasi dengan PHP 8.3.29)
- Composer 2.x
- Node.js + npm
- MySQL (Laragon: `root`, password kosong)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
```

Sesuaikan koneksi di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_marcos
DB_USERNAME=root
DB_PASSWORD=
```

Buat database lalu jalankan migrasi dan seeder:

```bash
php artisan migrate:fresh --seed
php artisan serve
```

File `data.xlsx` harus tersedia di root project saat seeder dijalankan. Kriteria, batas sub-kriteria, bobot, dan 32 alternatif dibaca dari file tersebut; data kuesioner tersimpan sebagai referensi dan tidak mengubah bobot.

Akun demo:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@spk.test` | `password` |
| User | `user@spk.test` | `password` |

## Mode Perhitungan Harga

Atur `PRICE_MODE` di `.env`:

| Nilai | Penjelasan |
|---|---|
| `score_benefit` | Default. Harga dikonversi ke skor 1–5 dan diperlakukan sebagai kriteria benefit. |
| `raw_cost` | Harga mentah (Rupiah) digunakan langsung sebagai kriteria cost. |
| `proposal_compat` | Reproduksi Tabel 3.18 proposal: skor Harga dipaksa `1` untuk semua alternatif dan diperlakukan sebagai cost. |

Mode lain tidak mengubah kriteria selain C1.

## Alur Perhitungan

1. **Bobot dari Excel** — bobot C1..C8 dibaca dari sheet `MARCOS` (`30, 20, 15, 10, 10, 5, 10, 5`) lalu dinormalisasi sehingga jumlahnya 1. Karena total angka di file adalah 105, bobot C1 menjadi `30/105 ≈ 0.285714`.
2. **Konversi skor** — nilai mentah tiap sub-kriteria diubah ke skor 1–5 dengan batas bawah inklusif (`min_value`).
3. **Matriks keputusan** — `x_ij` diambil dari skor, kecuali sesuai `PRICE_MODE`.
4. **AI / AAI** — ideal terbaik dan terburuk per kriteria berdasarkan jenis `benefit`/`cost`.
5. **Normalisasi dan pembobotan** — menghasilkan `S_AAI`, `S_i`, dan `S_AI`.
6. **Utilitas dan ranking** — `K-`, `K+`, `f(K-)`, `f(K+)`, `f(K_i)`, lalu ranking kompetisi dengan pembulatan 6 desimal.

`f(K-)` dan `f(K+)` identik untuk semua alternatif; ini sifat matematis MARCOS, bukan kesalahan.

## Verifikasi

```bash
php artisan test
npm run build
```

Tes meliputi 6 kasus skripsi (bobot kuesioner, konversi skor, replikasi tabel proposal, bobot tertukar, `score_benefit`, dan `raw_cost`), integrasi importer `data.xlsx`, serta alur pencarian dan ekspor. Database pengujian memakai SQLite in-memory agar data MySQL utama tidak tersentuh.

## Catatan

`composer.json` berisi `audit.block-insecure false` karena Laravel 11.57.1 terkena advisory Composer 2.9 pada saat instalasi. Ini hanya untuk mengizinkan instalasi versi yang dipakai skripsi.
