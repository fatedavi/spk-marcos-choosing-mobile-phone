<?php

namespace App\Services;

use App\Models\Criterion;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class ScoreConverterService
{
    /**
     * Skor = skor dengan min_value <= raw terbesar (min_value = batas bawah inklusif).
     * Berlaku untuk kriteria asc maupun desc: Harga menyimpan min_value 0/3jt/5jt/7jt/10jt.
     */
    public function toScore(Criterion $criterion, float $raw, ?Collection $subCriteria = null): int
    {
        $rows = ($subCriteria ?? $criterion->subCriteria)
            ->sortByDesc(fn ($row) => Arr::get($row, 'min_value') === null ? -INF : (float) Arr::get($row, 'min_value'));

        foreach ($rows as $row) {
            $min = Arr::get($row, 'min_value');
            if ($min === null || $raw >= (float) $min) {
                return (int) Arr::get($row, 'score', 1);
            }
        }

        return 1;
    }
}
