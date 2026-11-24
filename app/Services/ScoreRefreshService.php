<?php

namespace App\Services;

use App\Models\AlternativeValue;
use App\Models\Criterion;

class ScoreRefreshService
{
    public function __construct(private ScoreConverterService $scoreConverter) {}

    public function refreshAll(): void
    {
        $criteria = Criterion::with('subCriteria')->get()->keyBy('id');

        foreach (AlternativeValue::all() as $value) {
            $criterion = $criteria->get($value->criteria_id);
            if ($criterion) {
                $value->update(['score' => $this->scoreConverter->toScore($criterion, (float) $value->raw_value)]);
            }
        }
    }
}
