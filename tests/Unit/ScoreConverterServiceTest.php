<?php

namespace Tests\Unit;

use App\Models\Criterion;
use App\Services\ScoreConverterService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Tests\Support\MarcosData;

class ScoreConverterServiceTest extends TestCase
{
    public function test_score_conversion_matches_test_case_2(): void
    {
        $converter = new ScoreConverterService();

        foreach (MarcosData::SUB_CRITERIA as $code => $ranges) {
            $criterion = new Criterion(['code' => $code]);
            $subs = collect($ranges)->map(fn ($r) => ['score' => $r[0], 'min_value' => $r[1]]);

            foreach (MarcosData::RAW_VALUES as $alternative => $values) {
                $index = (int) substr($code, 1) - 1;
                $expected = MarcosData::SCORE_MATRIX[$alternative][$index];
                $this->assertSame(
                    $expected,
                    $converter->toScore($criterion, (float) $values[$index], $subs),
                    "{$alternative} {$code}"
                );
            }
        }
    }

    public function test_fallback_score_is_one_below_all_thresholds(): void
    {
        $criterion = new Criterion(['code' => 'C2']);
        $subs = new Collection([
            ['score' => 5, 'min_value' => 100],
            ['score' => 4, 'min_value' => 50],
        ]);

        $this->assertSame(1, (new ScoreConverterService())->toScore($criterion, 10, $subs));
    }
}
