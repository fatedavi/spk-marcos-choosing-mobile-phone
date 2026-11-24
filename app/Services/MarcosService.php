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
        if (count($matrix) < 2 || count($weights) !== $n) {
            throw new \InvalidArgumentException('Minimal 2 alternatif dan data bobot/jenis kriteria harus konsisten.');
        }
        $sumW = array_sum($weights);
        if ($sumW <= 0) {
            throw new \InvalidArgumentException('Jumlah bobot harus > 0.');
        }
        $w = array_map(fn ($x) => $x / $sumW, array_values($weights));

        // Langkah 1-2: AI dan AAI per kriteria. Selalu diambil per indeks kolom (kode kriteria),
        // bukan per urutan baris, untuk menghindari bug tertukar C4/C6/C7 di proposal.
        $ai = $aai = [];
        for ($j = 0; $j < $n; $j++) {
            $col = array_map(fn ($row) => (float) $row[$j], $matrix);
            if (min($col) <= 0) {
                throw new \DomainException('Nilai kriteria ke-' . ($j + 1) . ' harus > 0 (pembagian dengan nol).');
            }
            if ($types[$j] === 'benefit') {
                $ai[$j] = max($col);
                $aai[$j] = min($col);
            } else {
                $ai[$j] = min($col);
                $aai[$j] = max($col);
            }
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
        $sAi = $detail['AI']['S'];

        // Langkah 6-8: derajat utilitas, fungsi utilitas, nilai akhir.
        // f(K-) dan f(K+) identik untuk semua alternatif — sifat matematis MARCOS, bukan bug.
        $results = [];
        foreach ($matrix as $key => $_) {
            $s = $detail[$key]['S'];
            $km = $s / $sAai;
            $kp = $s / $sAi;
            $fKp = $km / ($kp + $km);
            $fKm = $kp / ($kp + $km);
            $f = ($kp + $km) / (1 + (1 - $fKp) / $fKp + (1 - $fKm) / $fKm);
            $results[$key] = [
                'norm' => $detail[$key]['norm'], 'weighted' => $detail[$key]['weighted'],
                'S' => $s, 'K_minus' => $km, 'K_plus' => $kp,
                'f_K_minus' => $fKm, 'f_K_plus' => $fKp, 'f' => $f,
            ];
        }

        // Langkah 9: ranking kompetisi (1,2,2,4) pada nilai dibulatkan 6 desimal
        $keys = array_keys($results);
        usort($keys, fn ($a, $b) => round($results[$b]['f'], 6) <=> round($results[$a]['f'], 6));
        $rank = 0;
        $prev = null;
        foreach ($keys as $i => $key) {
            $val = round($results[$key]['f'], 6);
            if ($val !== $prev) {
                $rank = $i + 1;
                $prev = $val;
            }
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
