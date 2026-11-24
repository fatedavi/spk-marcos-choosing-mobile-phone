<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MatrixBuilderService
{
    public function __construct(private ScoreConverterService $scoreConverter) {}

    /**
     * Bangun matriks keputusan sesuai PRICE_MODE. MarcosService tetap murni.
     *
     * @return array{matrix:array<string,array<int,float>>,types:array<int,string>,codes:array<int,string>,raw:array<string,array<int,float>>,scores:array<string,array<int,int>>,labels:array<string,string>}
     */
    public function build(Collection $criteria, Collection $alternatives, string $priceMode): array
    {
        if (! in_array($priceMode, array_keys(config('spk.modes', [])), true)) {
            throw new \InvalidArgumentException('PRICE_MODE tidak dikenal: ' . $priceMode);
        }

        $criteria = $criteria->sortBy('sort_order')->values();
        $codes = $criteria->pluck('code')->map(fn ($c) => (string) $c)->all();
        $labels = $alternatives->mapWithKeys(fn ($a) => [$a->code => $a->name])->all();

        $types = [];
        foreach ($criteria as $criterion) {
            if ($criterion->code === 'C1') {
                // Mode proposal memperlakukan skor harga (yang dipaksa 1) sebagai cost.
                $types[] = $priceMode === 'score_benefit' ? 'benefit' : 'cost';
            } else {
                $types[] = $criterion->type;
            }
        }

        $matrix = $raw = $scores = [];
        foreach ($alternatives as $alternative) {
            $row = $rawRow = $scoreRow = [];
            foreach ($criteria as $j => $criterion) {
                $value = $alternative->values->firstWhere('criteria_id', $criterion->id);
                $rawValue = $value ? (float) $value->raw_value : 0.0;
                $score = $value && $value->score !== null
                    ? (int) $value->score
                    : $this->scoreConverter->toScore($criterion, $rawValue);

                $rawRow[] = $rawValue;
                $scoreRow[] = $score;

                if ($criterion->code === 'C1' && $priceMode === 'raw_cost') {
                    $row[] = $rawValue;
                } elseif ($criterion->code === 'C1' && $priceMode === 'proposal_compat') {
                    // Reproduksi Tabel 3.18 proposal: Harga = 1 untuk semua alternatif.
                    $row[] = 1.0;
                } else {
                    $row[] = (float) $score;
                }
            }
            $matrix[$alternative->code] = $row;
            $raw[$alternative->code] = $rawRow;
            $scores[$alternative->code] = $scoreRow;
        }

        return [
            'matrix' => $matrix,
            'types' => $types,
            'codes' => $codes,
            'raw' => $raw,
            'scores' => $scores,
            'labels' => $labels,
        ];
    }
}
