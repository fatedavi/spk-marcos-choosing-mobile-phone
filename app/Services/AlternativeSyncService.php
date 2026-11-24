<?php

namespace App\Services;

use App\Models\Alternative;
use App\Models\AlternativeValue;
use App\Models\Criterion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AlternativeSyncService
{
    public function __construct(private ScoreConverterService $scoreConverter) {}

    public function sync(array $alternatives, ?Collection $criteria = null): void
    {
        $criteria = ($criteria ?? Criterion::orderBy('sort_order')->get())->values();

        DB::transaction(function () use ($alternatives, $criteria) {
            foreach ($alternatives as $data) {
                $alternative = Alternative::updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'name' => $data['name'],
                        'brand' => $data['brand'] ?? null,
                        'kelas' => $data['kelas'] ?? null,
                    ]
                );

                foreach ($data['values'] as $index => $raw) {
                    $criterion = $criteria->get($index);

                    if (! $criterion) {
                        throw new InvalidArgumentException('Jumlah kriteria tidak cocok untuk '.$data['code']);
                    }

                    AlternativeValue::updateOrCreate(
                        ['alternative_id' => $alternative->id, 'criteria_id' => $criterion->id],
                        [
                            'raw_value' => $raw,
                            'score' => $this->scoreConverter->toScore($criterion, (float) $raw),
                        ]
                    );
                }
            }
        });
    }
}
