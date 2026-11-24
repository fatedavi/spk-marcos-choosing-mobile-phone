<?php

namespace Tests\Feature\Import;

use App\Models\Alternative;
use App\Models\AlternativeValue;
use App\Models\Criterion;
use App\Models\SubCriterion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpkWorkbookImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_mengimpor_data_dari_xlsx(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(8, Criterion::count());
        $this->assertSame(40, SubCriterion::count());
        $this->assertSame(32, Alternative::count());
        $this->assertSame(256, AlternativeValue::count());
        $this->assertEqualsWithDelta(1.0, (float) Criterion::sum('default_weight'), 0.000001);

        $c1 = Criterion::where('code', 'C1')->firstOrFail();
        $c2 = Criterion::where('code', 'C2')->firstOrFail();
        $c6 = Criterion::where('code', 'C6')->firstOrFail();
        $c7 = Criterion::where('code', 'C7')->firstOrFail();

        $this->assertEqualsWithDelta(30 / 105, (float) $c1->default_weight, 0.000001);
        $this->assertEqualsWithDelta(5 / 105, (float) Criterion::where('code', 'C8')->value('default_weight'), 0.000001);
        $this->assertSame('cost', $c1->type);
        $this->assertEqualsWithDelta(120.0, (float) $c6->subCriteria()->where('score', 5)->value('min_value'), 0.000001);

        $a1 = Alternative::where('code', 'A1')->firstOrFail();
        $a3 = Alternative::where('code', 'A3')->firstOrFail();
        $a15 = Alternative::where('code', 'A15')->firstOrFail();
        $a22 = Alternative::where('code', 'A22')->firstOrFail();

        $this->assertSame('Asus', $a1->brand);
        $this->assertSame('Flagship', $a1->kelas);
        $this->assertSame('Infinix', $a22->brand);
        $this->assertSame('Midrange', $a22->kelas);
        $this->assertEqualsWithDelta(1024.0, $this->rawValue($a3, $c7), 0.01);
        $this->assertEqualsWithDelta(9999000.0, $this->rawValue($a15, $c1), 0.01);
        $this->assertEqualsWithDelta(1265000.0, $this->rawValue($a22, $c2), 0.01);
    }

    private function rawValue(Alternative $alternative, Criterion $criterion): float
    {
        return (float) AlternativeValue::where('alternative_id', $alternative->id)
            ->where('criteria_id', $criterion->id)
            ->value('raw_value');
    }
}
