# PROMPT: Bangun SPK Pemilihan Smartphone Gaming dengan Metode MARCOS (Laravel + MySQL)

> Salin seluruh isi file ini sebagai prompt ke AI coding assistant (Claude Code, Cursor, dsb.).
> Seluruh angka pada bagian "Test Case" sudah dihitung ulang dan diverifikasi, jadi bisa dipakai sebagai patokan benar/salah.

---

## 1. Peran dan tujuan

Kamu adalah senior Laravel developer. Bangun aplikasi web **Sistem Pendukung Keputusan (SPK) Pemilihan Smartphone Gaming Terbaik** memakai metode **MARCOS** (Measurement of Alternatives and Ranking according to COmpromise Solution, Stević et al., 2020).

Aplikasi dipakai untuk skripsi, jadi **setiap langkah perhitungan harus bisa ditampilkan** (matriks keputusan, AI/AAI, normalisasi, matriks berbobot, S_i, K-, K+, f(K-), f(K+), f(K_i), ranking) supaya bisa dicocokkan dengan perhitungan Excel.

## 2. Stack

- PHP 8.2+, Laravel 11, MySQL 8, Blade + Tailwind (atau Bootstrap 5), tanpa SPA.
- Arsitektur MVC. Logika MARCOS WAJIB di class terpisah `app/Services/MarcosService.php` (bukan di controller/Blade) dan dibuat murni (tidak menyentuh database) agar mudah diuji.
- Auth dengan Laravel Breeze. Dua role: `admin` dan `user`.

## 3. Fitur

**Admin**
1. Login, dashboard ringkas.
2. CRUD **Kriteria** (kode, nama, satuan, jenis `cost`/`benefit`, bobot awal).
3. CRUD **Subkriteria** per kriteria (rentang nilai -> skor 1-5), lihat bagian 5.
4. CRUD **Alternatif** (kode A1.., nama smartphone, nilai mentah tiap kriteria). Skor 1-5 dihitung otomatis dari nilai mentah.
5. **Input hasil kuesioner**: jumlah responden per pilihan jawaban (SS/S/C/TS/STS) per kriteria -> sistem menghitung total skor, rata-rata, dan bobot awal.
6. Lihat riwayat hasil perhitungan.

**User**
1. Lihat daftar smartphone.
2. **Pembobotan dinamis**: slider/input bobot per kriteria, dimulai dari bobot kuesioner. Setiap perubahan dinormalisasi otomatis supaya jumlah = 1 (100%).
3. Pilih alternatif yang ingin dibandingkan (default semua), jalankan perhitungan.
4. Lihat hasil: tabel ranking, rekomendasi #1, dan detail langkah MARCOS (collapsible).
5. Ekspor hasil ke Excel/PDF.

## 4. Keputusan desain yang WAJIB diikuti (temuan dari audit proposal)

Proposal punya beberapa ketidakkonsistenan. Jangan ditiru. Ikuti aturan di bawah dan beri komentar di kode saat menerapkannya.

1. **Bobot tertukar di proposal.** Bobot dihitung `w_j = totalSkor_j / jumlahSemuaTotalSkor` (jumlah = 1883, bukan 1.171). Hasil benar: lihat Test 1. Di proposal, bobot C4, C6, C7 tertukar pada Tabel 3.18 dan contoh perhitungan matriks berbobot. Di kode, bobot selalu diambil per kode kriteria (C1..C8), jangan per urutan baris.
2. **Harga: jangan diperlakukan sebagai `cost` jika dinilai dengan skala 1-5 "5 = murah".** Pada Tabel 3.5, skor 5 berarti paling murah, jadi skor itu sudah berarah "makin besar makin baik". Memakai rumus cost (`AI/x`) pada skor tersebut membalik arah dua kali dan justru menguntungkan HP mahal. Terapkan salah satu (dipilih lewat konfigurasi `PRICE_MODE`):
   - `score_benefit` (**default yang direkomendasikan**): Harga dikonversi ke skor 1-5 lalu diperlakukan `benefit`.
   - `raw_cost`: Harga dipakai nilai rupiah aslinya dan diperlakukan `cost` (n = x_min / x), sementara kriteria lain tetap skor 1-5.
   - `proposal_compat`: perilaku proposal apa adanya (Harga skor 1-5 diperlakukan cost). Hanya untuk mereproduksi angka proposal; tampilkan peringatan di UI.
3. **Matriks Harga di proposal salah.** Tabel 3.18 mengisi Harga = 1 untuk semua alternatif, padahal menurut Tabel 3.5 A9 (Rp7.499.000) = 2 dan A10 (Rp5.799.000) = 3. Akibatnya kriteria Harga tidak membedakan alternatif sama sekali. Konversi harus dilakukan otomatis dari nilai mentah, bukan diketik manual.
4. **Batas subkriteria dibuat inklusif di batas bawah (`>=`).** Teks tabel proposal ambigu (mis. "> 6000 - 7000 mAh" tapi A5 = 7000 mAh diberi skor 5). Aturan `nilai >= batas_bawah` cocok dengan seluruh matriks proposal selain Harga. Simpan sebagai kolom `min_value` di tabel `sub_criteria`, jangan di-hardcode.
5. **Tabel AI/AAI di proposal salah ketik.** Untuk C7 (ROM): AI = 5 dan AAI = 4 (di Tabel 3.19 tertulis AI=2). Hitung selalu dari data, jangan manual.
6. **Ranking seri.** Alternatif dengan nilai akhir sama (dibulatkan 6 desimal) mendapat peringkat sama dan peringkat berikutnya melompat (1,2,2,4). Proposal tidak konsisten soal ini.
7. Nilai f(K-) dan f(K+) **sama untuk semua alternatif** (0.4424 dan 0.5576 pada data contoh). Itu sifat matematis MARCOS, bukan bug. Jangan dianggap error.
8. Validasi: kriteria cost dengan nilai <= 0 menyebabkan pembagian nol, tolak dengan pesan jelas. Minimal 2 alternatif dan jumlah bobot > 0.

## 5. Skema database (migrasi)

```
users              id, name, email, password, role ENUM('admin','user')
criteria           id, code (C1..), name, unit, type ENUM('cost','benefit'), default_weight DECIMAL(10,8), sort_order
sub_criteria       id, criteria_id FK, score TINYINT(1-5), min_value DECIMAL(20,2) NULL, label VARCHAR
                   -- aturan: skor = skor tertinggi yang min_value-nya <= nilai (urutkan min_value DESC)
alternatives       id, code (A1..), name
alternative_values id, alternative_id FK, criteria_id FK, raw_value DECIMAL(20,2), score TINYINT NULL
                   UNIQUE(alternative_id, criteria_id)
questionnaire      id, criteria_id FK UNIQUE, n_ss, n_s, n_c, n_ts, n_sts INT
calculations       id, user_id FK, weights JSON, price_mode VARCHAR, result JSON, created_at
```

## 6. Rumus MARCOS (urutan implementasi)

Notasi: m alternatif, n kriteria, x_ij nilai alternatif i pada kriteria j (skor 1-5 atau nilai mentah sesuai mode), w_j bobot.

**Bobot dari kuesioner (Skala Likert SS=5, S=4, C=3, TS=2, STS=1)**
```
T_j = 5*n_ss + 4*n_s + 3*n_c + 2*n_ts + 1*n_sts       (total skor kriteria j)
R_j = T_j / jumlah_responden                           (rata-rata)
w_j = R_j / sum(R)  =  T_j / sum(T)                    (bobot; sum(w) = 1)
```
Pembobotan dinamis: `w_j' = input_j / sum(input)`.

**MARCOS**
1. Matriks keputusan `X` (baris = alternatif, kolom = kriteria).
2. AI dan AAI per kriteria:
   - benefit: `AI = max(x_ij)`, `AAI = min(x_ij)`
   - cost: `AI = min(x_ij)`, `AAI = max(x_ij)`
   Matriks diperluas: baris AAI di atas, A1..Am, baris AI di bawah.
3. Normalisasi (termasuk baris AAI dan AI):
   - benefit: `n_ij = x_ij / x_AI_j`
   - cost: `n_ij = x_AI_j / x_ij`
4. Matriks berbobot: `v_ij = n_ij * w_j`
5. Total: `S_i = sum_j v_ij` (juga untuk S_AAI dan S_AI)
6. Derajat utilitas: `K-_i = S_i / S_AAI`, `K+_i = S_i / S_AI`
7. Fungsi utilitas:
   - `f(K+_i) = K-_i / (K+_i + K-_i)`
   - `f(K-_i) = K+_i / (K+_i + K-_i)`
8. Nilai akhir:
   `f(K_i) = (K+_i + K-_i) / ( 1 + (1 - f(K+_i)) / f(K+_i) + (1 - f(K-_i)) / f(K-_i) )`
9. Ranking: urutkan `f(K_i)` menurun. Nilai terbesar = rekomendasi terbaik.

## 7. Implementasi referensi `MarcosService` (sudah diuji, hasilnya cocok dengan Test Case)

Pakai kode ini apa adanya sebagai inti, lalu bungkus dengan controller dan view.

```php
<?php

namespace App\Services;

class MarcosService
{
    /**
     * @param array<string,array<int,float>> $matrix  [kode_alternatif => [x_1..x_n]]
     * @param array<int,float>               $weights [w_1..w_n] (dinormalisasi agar jumlah = 1)
     * @param array<int,string>              $types   ['cost'|'benefit', ...] sepanjang n
     */
    public function calculate(array $matrix, array $weights, array $types): array
    {
        $n = count($types);
        if ($matrix === [] || count($weights) !== $n) {
            throw new \InvalidArgumentException('Data alternatif/bobot/jenis kriteria tidak konsisten.');
        }
        $sumW = array_sum($weights);
        if ($sumW <= 0) {
            throw new \InvalidArgumentException('Jumlah bobot harus > 0.');
        }
        $w = array_map(fn ($x) => $x / $sumW, array_values($weights));

        // Langkah 1-2: AI dan AAI per kriteria
        $ai = $aai = [];
        for ($j = 0; $j < $n; $j++) {
            $col = array_map(fn ($row) => (float) $row[$j], $matrix);
            if (min($col) <= 0) {
                throw new \DomainException('Nilai kriteria ke-' . ($j + 1) . ' harus > 0 (pembagian dengan nol).');
            }
            if ($types[$j] === 'benefit') { $ai[$j] = max($col); $aai[$j] = min($col); }
            else                          { $ai[$j] = min($col); $aai[$j] = max($col); }
        }

        // Matriks diperluas: AAI, A1..Am, AI
        $ext = ['AAI' => $aai] + $matrix + ['AI' => $ai];

        // Langkah 3-5: normalisasi, pembobotan, S_i
        $detail = [];
        foreach ($ext as $key => $row) {
            $norm = $weighted = [];
            for ($j = 0; $j < $n; $j++) {
                $norm[$j] = $types[$j] === 'benefit' ? $row[$j] / $ai[$j] : $ai[$j] / $row[$j];
                $weighted[$j] = $norm[$j] * $w[$j];
            }
            $detail[$key] = ['norm' => $norm, 'weighted' => $weighted, 'S' => array_sum($weighted)];
        }
        $sAai = $detail['AAI']['S'];
        $sAi  = $detail['AI']['S'];

        // Langkah 6-8: derajat utilitas, fungsi utilitas, nilai akhir
        $results = [];
        foreach ($matrix as $key => $_) {
            $s   = $detail[$key]['S'];
            $km  = $s / $sAai;                 // K-
            $kp  = $s / $sAi;                  // K+
            $fKp = $km / ($kp + $km);          // f(K+)
            $fKm = $kp / ($kp + $km);          // f(K-)
            $f   = ($kp + $km) / (1 + (1 - $fKp) / $fKp + (1 - $fKm) / $fKm);
            $results[$key] = [
                'norm' => $detail[$key]['norm'], 'weighted' => $detail[$key]['weighted'],
                'S' => $s, 'K_minus' => $km, 'K_plus' => $kp,
                'f_K_minus' => $fKm, 'f_K_plus' => $fKp, 'f' => $f,
            ];
        }

        // Langkah 9: ranking kompetisi (1,2,2,4) pada nilai dibulatkan 6 desimal
        $keys = array_keys($results);
        usort($keys, fn ($a, $b) => round($results[$b]['f'], 6) <=> round($results[$a]['f'], 6));
        $rank = 0; $prev = null;
        foreach ($keys as $i => $key) {
            $val = round($results[$key]['f'], 6);
            if ($val !== $prev) { $rank = $i + 1; $prev = $val; }
            $results[$key]['rank'] = $rank;
        }

        return [
            'weights' => $w, 'ai' => $ai, 'aai' => $aai,
            'S_ai' => $sAi, 'S_aai' => $sAai,
            'alternatives' => $results, 'ranking' => $keys,
            'ext' => ['AAI' => $detail['AAI'], 'AI' => $detail['AI']],
        ];
    }
}
```

Tambahkan juga `ScoreConverterService`: `toScore(Criteria $c, float $raw): int` = skor dari `sub_criteria` dengan `min_value <= raw` terbesar; jika `raw` di bawah semua batas, kembalikan skor 1.

## 8. Data seed

**Kriteria** (kode, nama, satuan, jenis menurut proposal)

| Kode | Kriteria | Satuan | Jenis proposal | Jenis yang dipakai (`score_benefit`) |
|---|---|---|---|---|
| C1 | Harga | Rp | cost | benefit pada skor (lihat bagian 4.2) |
| C2 | Skor AnTuTu | skor | benefit | benefit |
| C3 | RAM | GB | benefit | benefit |
| C4 | Refresh Rate | Hz | benefit | benefit |
| C5 | Kapasitas Baterai | mAh | benefit | benefit |
| C6 | Fast Charging | W | benefit | benefit |
| C7 | ROM | GB | benefit | benefit |
| C8 | Kualitas Kamera | MP | benefit | benefit |

**Subkriteria** (`min_value` -> skor; aturan `nilai >= min_value`)

| Kriteria | Skor 5 | Skor 4 | Skor 3 | Skor 2 | Skor 1 |
|---|---|---|---|---|---|
| Harga (Rp), makin murah makin tinggi | < 3.000.000 | 3.000.000 s/d < 5.000.000 | 5.000.000 s/d < 7.000.000 | 7.000.000 s/d < 10.000.000 | >= 10.000.000 |
| AnTuTu | >= 1.500.000 | >= 1.000.000 | >= 700.000 | >= 400.000 | < 400.000 |
| RAM (GB) | >= 16 | >= 12 | >= 8 | >= 6 | < 6 |
| Refresh Rate (Hz) | >= 165 | >= 144 | >= 120 | >= 90 | < 90 |
| Baterai (mAh) | >= 7000 | >= 6000 | >= 5000 | >= 4000 | < 4000 |
| Fast Charging (W) | >= 100 | >= 67 | >= 45 | >= 25 | < 25 |
| ROM (GB) | >= 1024 | >= 512 | >= 256 | >= 128 | < 128 |
| Kamera (MP) | >= 200 | >= 108 | >= 64 | >= 50 | < 50 |

Catatan: untuk Harga, karena arahnya terbalik, simpan sebagai `max_value` (batas atas eksklusif) atau tambahkan kolom `direction` pada `criteria` (`asc`/`desc`) supaya `ScoreConverterService` bisa menangani keduanya.

**Kuesioner** (55 responden): jumlah per pilihan SS, S, C, TS, STS

| Kriteria | SS | S | C | TS | STS |
|---|---|---|---|---|---|
| C1 | 33 | 15 | 1 | 4 | 2 |
| C2 | 31 | 18 | 3 | 3 | 0 |
| C3 | 29 | 24 | 2 | 0 | 0 |
| C4 | 32 | 17 | 3 | 2 | 1 |
| C5 | 31 | 19 | 3 | 2 | 0 |
| C6 | 30 | 20 | 2 | 2 | 1 |
| C7 | 33 | 18 | 1 | 2 | 1 |
| C8 | 17 | 12 | 6 | 13 | 7 |

**Alternatif** (nilai mentah: Harga Rp, AnTuTu, RAM GB, Hz, mAh, Watt, ROM GB, MP)

| Kode | Smartphone | Harga | AnTuTu | RAM | Hz | mAh | W | ROM | MP |
|---|---|---|---|---|---|---|---|---|---|
| A1 | Asus ROG Phone 9 Pro | 21.999.000 | 3.290.813 | 24 | 185 | 5800 | 65 | 1024 | 50 |
| A2 | Asus ROG Phone 8 Pro | 19.999.000 | 2.464.181 | 24 | 165 | 5500 | 65 | 1024 | 50 |
| A3 | RedMagic 11 Pro | 19.999.000 | 4.106.597 | 24 | 144 | 7500 | 80 | 1024 | 50 |
| A4 | RedMagic 9 Pro | 17.500.000 | 2.500.131 | 16 | 120 | 6500 | 80 | 512 | 50 |
| A5 | IQOO 15 | 15.999.000 | 3.719.658 | 16 | 144 | 7000 | 100 | 1024 | 50 |
| A6 | IQOO 13 | 11.400.000 | 3.116.670 | 16 | 144 | 6150 | 120 | 1024 | 50 |
| A7 | IQOO 12 | 10.999.000 | 2.333.285 | 16 | 144 | 5000 | 120 | 512 | 50 |
| A8 | POCO F8 Ultra | 11.999.000 | 3.797.111 | 16 | 120 | 6500 | 100 | 512 | 50 |
| A9 | POCO X8 Pro Max | 7.499.000 | 3.006.210 | 12 | 120 | 8500 | 100 | 512 | 50 |
| A10 | POCO X8 Pro | 5.799.000 | 2.101.496 | 12 | 120 | 6500 | 100 | 512 | 50 |

## 9. Test case (wajib lulus; toleransi 0,0005)

Buat `tests/Unit/MarcosServiceTest.php` (PHPUnit) dan `tests/Unit/WeightTest.php`.

**Test 1: bobot kuesioner** (jumlah total skor = 1883)

| C1 | C2 | C3 | C4 | C5 | C6 | C7 | C8 |
|---|---|---|---|---|---|---|---|
| 0.126394 | 0.128518 | 0.131174 | 0.128518 | 0.129580 | 0.127987 | 0.130112 | 0.097716 |

Total skor: 238, 242, 247, 242, 244, 241, 245, 184. Rata-rata: 4.3273, 4.4000, 4.4909, 4.4000, 4.4364, 4.3818, 4.4545, 3.3455 (jumlah 34.2364).

**Test 2: konversi skor** dari nilai mentah (tabel bagian 8) harus menghasilkan matriks di bawah (C1 = skor harga yang benar):

```
      C1 C2 C3 C4 C5 C6 C7 C8
A1     1  5  5  5  3  3  5  2
A2     1  5  5  5  3  3  5  2
A3     1  5  5  4  5  4  5  2
A4     1  5  5  3  4  4  4  2
A5     1  5  5  4  5  5  5  2
A6     1  5  5  4  4  5  5  2
A7     1  5  5  4  3  5  4  2
A8     1  5  5  3  4  5  4  2
A9     2  5  4  3  5  5  4  2     <- proposal salah menulis 1
A10    3  5  4  3  4  5  4  2     <- proposal salah menulis 1
```

**Test 3: MARCOS mode `proposal_compat`** (matriks proposal dengan C1 = 1 untuk semua, C1 cost, bobot Test 1)

AI = [1,5,5,5,5,5,5,2], AAI = [1,5,4,3,3,3,4,2]. S_AAI = 0.793309, S_AI = 1.

| Peringkat | Alt | S_i | K-_i | K+_i | f(K_i) |
|---|---|---|---|---|---|
| 1 | A5 | 0.974296 | 1.2281 | 0.9743 | 0.7212 |
| 2 | A3 | 0.948699 | 1.1959 | 0.9487 | 0.7023 |
| 3 | A6 | 0.948380 | 1.1955 | 0.9484 | 0.7020 |
| 4 | A1 | 0.896973 | 1.1307 | 0.8970 | 0.6640 |
| 4 | A2 | 0.896973 | 1.1307 | 0.8970 | 0.6640 |
| 6 | A8 | 0.896654 | 1.1303 | 0.8967 | 0.6637 |
| 7 | A7 | 0.896442 | 1.1300 | 0.8964 | 0.6636 |
| 8 | A9 | 0.896336 | 1.1299 | 0.8963 | 0.6635 |
| 9 | A4 | 0.871057 | 1.0980 | 0.8711 | 0.6448 |
| 10 | A10 | 0.870420 | 1.0972 | 0.8704 | 0.6443 |

f(K-) = 0.4424 dan f(K+) = 0.5576 untuk semua alternatif.

**Test 4: mereproduksi angka tabel di proposal** (hanya untuk mencocokkan dokumen). Angka di Tabel 3.22-3.26 proposal baru muncul jika bobot dipertukarkan: C4 = 0.127987, C6 = 0.130112, C7 = 0.128518 (selain itu sama dengan Test 1). Dengan bobot itu: S_AAI = 0.792989, f(A5) = 0.721396, f(A3) = 0.702131, f(A6) = 0.702209 (sehingga A6 peringkat 2, A3 peringkat 3), f(A10) = 0.644806. Ini membuktikan tabel proposal memakai bobot yang tertukar. Perbaiki tabelnya, jangan diubah kodenya.

**Test 5: mode `score_benefit`** (matriks Test 2 dengan C1 diperlakukan benefit, semua kolom benefit, bobot Test 1)

| Peringkat | Alt | f(K_i) |
|---|---|---|
| 1 | A5 | 0.6877 |
| 2 | A10 | 0.6726 |
| 3 | A3 | 0.6679 |
| 4 | A6 | 0.6677 |
| 5 | A9 | 0.6600 |
| 6 | A1 | 0.6280 |
| 6 | A2 | 0.6280 |
| 8 | A8 | 0.6277 |
| 9 | A7 | 0.6276 |
| 10 | A4 | 0.6080 |

**Test 6: mode `raw_cost`** (Harga rupiah asli sebagai cost, kolom lain skor Test 2, bobot Test 1). AI harga = 5.799.000 dan AAI harga = 21.999.000.

| Peringkat | Alt | f(K_i) |
|---|---|---|
| 1 | A5 | 0.6937 |
| 2 | A6 | 0.6879 |
| 3 | A10 | 0.6756 |
| 4 | A9 | 0.6735 |
| 5 | A3 | 0.6667 |
| 6 | A7 | 0.6494 |
| 7 | A8 | 0.6453 |
| 8 | A2 | 0.6265 |
| 9 | A1 | 0.6240 |
| 10 | A4 | 0.6105 |

## 10. Tampilan dan UX

- Halaman hasil menampilkan tabel per langkah (bagian 6), dengan 6 desimal, plus ringkasan "Rekomendasi: <nama HP> (nilai <f>)".
- Pada pembobotan dinamis tampilkan jumlah bobot realtime (harus 100%).
- Tampilkan peringatan jika `PRICE_MODE = proposal_compat`: "Kriteria Harga tidak membedakan alternatif pada mode ini."
- Tampilkan bobot bersama persentase dan nilai mentah + skor 1-5 tiap sel pada detail alternatif.
- Semua teks UI berbahasa Indonesia.

## 11. Urutan kerja yang kamu lakukan

1. Buat proyek, migrasi, model, seeder (data bagian 8).
2. Tulis `MarcosService`, `WeightService`, `ScoreConverterService` + unit test bagian 9. **Jalankan test sampai lulus sebelum membuat UI.**
3. Buat CRUD admin, lalu halaman user (pembobotan dinamis + hasil).
4. Buat fitur ekspor Excel (maatwebsite/excel) berisi seluruh langkah perhitungan, supaya bisa dibandingkan dengan Excel manual (Tabel 3.27 proposal: Hasil Sistem vs Hasil Excel, kolom Sesuai/Tidak Sesuai).
5. Di akhir, tulis `README.md` berisi cara instal, `php artisan migrate --seed`, `php artisan test`, dan penjelasan tiga `PRICE_MODE`.

Jika ada keputusan yang belum jelas, pilih opsi paling sederhana, tulis asumsinya di README, dan lanjutkan.
