<?php

namespace Tests\Feature\Import;

use App\Models\Alternative;
use App\Models\AlternativeValue;
use App\Models\Criterion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportAlternativesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_perintah_mengisi_alternatif_dari_sheet_data_tanpa_mengubah_bobot(): void
    {
        $this->seed(DatabaseSeeder::class);

        Criterion::where('code', 'C1')->update(['default_weight' => 0.42]);

        $keep = collect(range(1, 10))->map(fn (int $i) => 'A'.$i)->all();
        $removedIds = Alternative::whereNotIn('code', $keep)->pluck('id');
        AlternativeValue::whereIn('alternative_id', $removedIds)->delete();
        Alternative::whereIn('id', $removedIds)->delete();

        $this->assertSame(10, Alternative::count());

        $this->artisan('spk:import-alternatives')->assertSuccessful();

        $this->assertSame(32, Alternative::count());
        $this->assertSame(256, AlternativeValue::count());
        $this->assertSame('Asus', Alternative::where('code', 'A1')->value('brand'));
        $this->assertSame('OPPO Find X9', Alternative::where('code', 'A32')->value('name'));
        $this->assertEqualsWithDelta(0.42, (float) Criterion::where('code', 'C1')->value('default_weight'), 0.000001);
    }
}
