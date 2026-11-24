<?php

namespace Tests\Unit;

use App\Services\WeightService;
use PHPUnit\Framework\TestCase;
use Tests\Support\MarcosData;

class WeightTest extends TestCase
{
    public function test_questionnaire_weights_match_test_case_1(): void
    {
        $weights = (new WeightService())->fromQuestionnaire(MarcosData::QUESTIONNAIRE);

        $this->assertSame(1883, array_sum(array_column($weights, 'total')));

        foreach (MarcosData::EXPECTED_WEIGHTS as $code => $expected) {
            $this->assertEqualsWithDelta($expected, $weights[$code]['weight'], 0.0005, $code);
        }

        $this->assertEqualsWithDelta(1.0, array_sum(array_column($weights, 'weight')), 0.0005);
    }

    public function test_average_and_total_kuesioner(): void
    {
        $weights = (new WeightService())->fromQuestionnaire(MarcosData::QUESTIONNAIRE);

        $this->assertSame(238, $weights['C1']['total']);
        $this->assertSame(55, $weights['C1']['respondents']);
        $this->assertEqualsWithDelta(4.3273, $weights['C1']['average'], 0.0005);
        $this->assertEqualsWithDelta(3.3455, $weights['C8']['average'], 0.0005);
    }

    public function test_normalize_dynamic_weights(): void
    {
        $normalized = (new WeightService())->normalize(['C1' => 30, 'C2' => 45, 'C3' => 25]);

        $this->assertEqualsWithDelta(0.3, $normalized['C1'], 0.0005);
        $this->assertEqualsWithDelta(0.45, $normalized['C2'], 0.0005);
        $this->assertEqualsWithDelta(0.25, $normalized['C3'], 0.0005);
        $this->assertEqualsWithDelta(1.0, array_sum($normalized), 0.0005);
    }
}
