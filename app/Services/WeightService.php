<?php

namespace App\Services;

class WeightService
{
    /**
     * Hitung T_j, R_j dan w_j dari kuesioner.
     * w_j = R_j / sum(R); untuk jumlah responden yang sama ini identik dengan T_j / sum(T).
     *
     * @param  array<int,array<string,mixed>>  $rows  baris dengan key code, n_ss, n_s, n_c, n_ts, n_sts
     * @return array<string,array{total:int,respondents:int,average:float,weight:float}>
     */
    public function fromQuestionnaire(array $rows): array
    {
        $data = [];
        $sumAverage = 0.0;

        foreach ($rows as $row) {
            $total = 5 * (int) $row['n_ss'] + 4 * (int) $row['n_s'] + 3 * (int) $row['n_c']
                + 2 * (int) $row['n_ts'] + (int) $row['n_sts'];
            $respondents = (int) $row['n_ss'] + (int) $row['n_s'] + (int) $row['n_c']
                + (int) $row['n_ts'] + (int) $row['n_sts'];
            $average = $respondents > 0 ? $total / $respondents : 0.0;
            $data[$row['code']] = [
                'total' => $total,
                'respondents' => $respondents,
                'average' => $average,
                'weight' => 0.0,
            ];
            $sumAverage += $average;
        }

        foreach ($data as $code => $item) {
            $data[$code]['weight'] = $sumAverage > 0 ? $item['average'] / $sumAverage : 0.0;
        }

        return $data;
    }

    /**
     * Normalisasi bobot dinamis: w_j' = input_j / sum(input).
     *
     * @param  array<string|int,float>  $inputs
     * @return array<string|int,float>
     */
    public function normalize(array $inputs): array
    {
        $sum = array_sum($inputs);
        if ($sum <= 0) {
            throw new \InvalidArgumentException('Jumlah bobot harus > 0.');
        }

        $result = [];
        foreach ($inputs as $key => $value) {
            $result[$key] = (float) $value / $sum;
        }

        return $result;
    }
}
