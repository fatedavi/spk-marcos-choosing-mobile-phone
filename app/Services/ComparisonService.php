<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ComparisonService
{
    public function __construct(private ScoreConverterService $scoreConverter) {}

    public function build(Collection $criteria, Collection $alternatives): array
    {
        $codes = $alternatives->pluck('code')->all();
        $labels = $alternatives->mapWithKeys(fn ($alternative) => [$alternative->code => $alternative->name])->all();
        $rows = [];
        $wins = array_fill_keys($codes, 0);
        $scoreSums = array_fill_keys($codes, 0);
        $weightedSums = array_fill_keys($codes, 0.0);

        foreach ($criteria as $criterion) {
            $raws = [];
            $scores = [];

            foreach ($alternatives as $alternative) {
                $value = $alternative->values->firstWhere('criteria_id', $criterion->id);
                $raw = $value ? (float) $value->raw_value : 0.0;
                $score = $value && $value->score !== null
                    ? (int) $value->score
                    : $this->scoreConverter->toScore($criterion, $raw);

                $raws[$alternative->code] = $raw;
                $scores[$alternative->code] = $score;
                $scoreSums[$alternative->code] += $score;
                $weightedSums[$alternative->code] += $score * (float) $criterion->default_weight;
            }

            $bestRaw = $criterion->type === 'cost' ? min($raws) : max($raws);
            $bestCount = collect($raws)->filter(fn ($raw) => abs($raw - $bestRaw) < 0.000001)->count();
            $cells = [];

            foreach ($codes as $code) {
                $isBest = abs($raws[$code] - $bestRaw) < 0.000001;

                if ($isBest) {
                    $wins[$code]++;
                }

                $cells[$code] = [
                    'raw' => $raws[$code],
                    'score' => $scores[$code],
                    'state' => $isBest ? ($bestCount > 1 ? 'sama' : 'unggul') : '',
                ];
            }

            $rows[] = [
                'code' => $criterion->code,
                'name' => $criterion->name,
                'unit' => $criterion->unit,
                'type' => $criterion->type,
                'weight' => (float) $criterion->default_weight,
                'cells' => $cells,
            ];
        }

        $count = max($criteria->count(), 1);
        $summary = [];

        foreach ($codes as $code) {
            $summary[$code] = [
                'average' => $scoreSums[$code] / $count,
                'wins' => $wins[$code],
                'weighted' => $weightedSums[$code],
            ];
        }

        return [
            'codes' => $codes,
            'labels' => $labels,
            'rows' => $rows,
            'summary' => $summary,
        ];
    }
}
