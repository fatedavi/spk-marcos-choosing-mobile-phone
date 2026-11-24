<?php

namespace Tests\Unit;

use App\Services\MarcosService;
use PHPUnit\Framework\TestCase;
use Tests\Support\MarcosData;

class MarcosServiceTest extends TestCase
{
    private const DELTA = 0.0005;

    public function test_test_3_proposal_compat(): void
    {
        $result = (new MarcosService())->calculate(
            MarcosData::proposalMatrix(),
            MarcosData::weights(),
            MarcosData::proposalTypes()
        );

        foreach ([1, 5, 5, 5, 5, 5, 5, 2] as $j => $expected) {
            $this->assertEqualsWithDelta($expected, $result['ai'][$j], self::DELTA);
        }
        foreach ([1, 5, 4, 3, 3, 3, 4, 2] as $j => $expected) {
            $this->assertEqualsWithDelta($expected, $result['aai'][$j], self::DELTA);
        }
        $this->assertEqualsWithDelta(0.793309, $result['S_aai'], self::DELTA);
        $this->assertEqualsWithDelta(1.0, $result['S_ai'], self::DELTA);

        $expected = [
            'A5' => [0.974296, 1.2281, 0.7212, 1],
            'A3' => [0.948699, 1.1959, 0.7023, 2],
            'A6' => [0.948380, 1.1955, 0.7020, 3],
            'A1' => [0.896973, 1.1307, 0.6640, 4],
            'A2' => [0.896973, 1.1307, 0.6640, 4],
            'A8' => [0.896654, 1.1303, 0.6637, 6],
            'A7' => [0.896442, 1.1300, 0.6636, 7],
            'A9' => [0.896336, 1.1299, 0.6635, 8],
            'A4' => [0.871057, 1.0980, 0.6448, 9],
            'A10' => [0.870420, 1.0972, 0.6443, 10],
        ];

        foreach ($expected as $code => [$s, $km, $f, $rank]) {
            $row = $result['alternatives'][$code];
            $this->assertEqualsWithDelta($s, $row['S'], self::DELTA, "{$code} S");
            $this->assertEqualsWithDelta($km, $row['K_minus'], self::DELTA, "{$code} K-");
            $this->assertEqualsWithDelta($s, $row['K_plus'], self::DELTA, "{$code} K+");
            $this->assertEqualsWithDelta($f, $row['f'], self::DELTA, "{$code} f");
            $this->assertSame($rank, $row['rank'], "{$code} rank");
        }

        foreach ($result['alternatives'] as $row) {
            $this->assertEqualsWithDelta(0.4424, $row['f_K_minus'], self::DELTA);
            $this->assertEqualsWithDelta(0.5576, $row['f_K_plus'], self::DELTA);
        }
    }

    public function test_test_4_reproduces_swapped_weight_tables(): void
    {
        $result = (new MarcosService())->calculate(
            MarcosData::proposalMatrix(),
            MarcosData::SWAPPED_WEIGHTS,
            MarcosData::proposalTypes()
        );

        $this->assertEqualsWithDelta(0.792989, $result['S_aai'], self::DELTA);
        $this->assertEqualsWithDelta(0.721396, $result['alternatives']['A5']['f'], self::DELTA);
        $this->assertEqualsWithDelta(0.702131, $result['alternatives']['A3']['f'], self::DELTA);
        $this->assertEqualsWithDelta(0.702209, $result['alternatives']['A6']['f'], self::DELTA);
        $this->assertEqualsWithDelta(0.644806, $result['alternatives']['A10']['f'], self::DELTA);
        $this->assertSame(2, $result['alternatives']['A6']['rank']);
        $this->assertSame(3, $result['alternatives']['A3']['rank']);
    }

    public function test_test_5_score_benefit_mode(): void
    {
        $result = (new MarcosService())->calculate(
            MarcosData::SCORE_MATRIX,
            MarcosData::weights(),
            MarcosData::allBenefitTypes()
        );

        $expected = [
            'A5' => [0.6877, 1], 'A10' => [0.6726, 2], 'A3' => [0.6679, 3],
            'A6' => [0.6677, 4], 'A9' => [0.6600, 5], 'A1' => [0.6280, 6],
            'A2' => [0.6280, 6], 'A8' => [0.6277, 8], 'A7' => [0.6276, 9],
            'A4' => [0.6080, 10],
        ];
        $this->assertRanking($expected, $result);
    }

    public function test_test_6_raw_cost_mode(): void
    {
        $result = (new MarcosService())->calculate(
            MarcosData::rawCostMatrix(),
            MarcosData::weights(),
            MarcosData::proposalTypes()
        );

        $this->assertEqualsWithDelta(5799000, $result['ai'][0], self::DELTA);
        $this->assertEqualsWithDelta(21999000, $result['aai'][0], self::DELTA);

        $expected = [
            'A5' => [0.6937, 1], 'A6' => [0.6879, 2], 'A10' => [0.6756, 3],
            'A9' => [0.6735, 4], 'A3' => [0.6667, 5], 'A7' => [0.6494, 6],
            'A8' => [0.6453, 7], 'A2' => [0.6265, 8], 'A1' => [0.6240, 9],
            'A4' => [0.6105, 10],
        ];
        $this->assertRanking($expected, $result);
    }

    public function test_rejects_single_alternative_zero_weight_and_non_positive_cost(): void
    {
        $service = new MarcosService();

        $this->expectException(\InvalidArgumentException::class);
        $service->calculate(['A1' => [1, 2]], [0.5, 0.5], ['benefit', 'benefit']);
    }

    private function assertRanking(array $expected, array $result): void
    {
        foreach ($expected as $code => [$f, $rank]) {
            $this->assertEqualsWithDelta($f, $result['alternatives'][$code]['f'], self::DELTA, "{$code} f");
            $this->assertSame($rank, $result['alternatives'][$code]['rank'], "{$code} rank");
        }
    }
}
