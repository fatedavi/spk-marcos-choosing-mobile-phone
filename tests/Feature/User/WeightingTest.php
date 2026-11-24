<?php

namespace Tests\Feature\User;

use App\Exports\MarcosResultExport;
use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Tests\TestCase;

class WeightingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_halaman_pencarian_menampilkan_input_rentang_kriteria(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('user.weighting.index'));

        $response->assertOk();
        $response->assertSee('name="filters[C1][min]"', false);
        $response->assertSee('name="filters[C1][max]"', false);
        $response->assertSee('name="filters[C3][min]"', false);
        $response->assertSee('name="filters[C3][max]"', false);
        $response->assertDontSee('name="weights[', false);
        $response->assertSee('Cari HP & Hitung Peringkat', false);
    }

    public function test_rentang_harga_dan_ram_menghasilkan_hp_cocok_yang_diurutkan(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('user.weighting.calculate'), [
            'alternatives' => Alternative::orderBy('code')->pluck('code')->all(),
            'filters' => [
                'C1' => ['min' => 5000000, 'max' => 12000000],
                'C3' => ['min' => 16, 'max' => 16],
            ],
        ]);

        $calculation = Calculation::firstOrFail();
        $response->assertRedirect(route('results.show', $calculation));
        $response->assertSessionHas('success');

        $result = $calculation->result;
        $this->assertSame(['A19', 'A6', 'A7', 'A8'], $result['matched_codes']);
        $this->assertSame(['C1', 'C3'], array_keys($result['filters']));
        $this->assertEquals(5000000, $result['filters']['C1']['min']);
        $this->assertEquals(12000000, $result['filters']['C1']['max']);
        $this->assertEquals(16, $result['filters']['C3']['min']);
        $this->assertEquals(16, $result['filters']['C3']['max']);
        $this->assertSame('raw_cost', $calculation->price_mode);
        $this->assertSame('excel_fixed', $result['weight_source']);
        $this->assertEqualsWithDelta(1.0, array_sum($result['weights']), 0.000001);

        $ranking = $result['output']['ranking'];
        $this->assertSame([], array_diff($ranking, ['A19', 'A6', 'A7', 'A8']));
        $this->assertSame(1, min(array_column($result['output']['alternatives'], 'rank')));

        foreach ($ranking as $index => $code) {
            if ($index > 0) {
                $previous = $result['output']['alternatives'][$ranking[$index - 1]]['f'];
                $current = $result['output']['alternatives'][$code]['f'];
                $this->assertGreaterThanOrEqual($current, $previous);
            }
        }

        $response = $this->actingAs($user)->get(route('results.show', $calculation));
        $response->assertOk();
        $response->assertSee('Kriteria yang Diminta');
        $response->assertSee('min Rp 5.000.000');
        $response->assertSee('maks Rp 12.000.000');

        $export = ExcelFacade::raw(new MarcosResultExport($calculation), Excel::XLSX);
        $this->assertNotEmpty($export);
    }

    public function test_rentang_terlalu_ketat_ditolak(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->from(route('user.weighting.index'))->post(route('user.weighting.calculate'), [
            'alternatives' => Alternative::orderBy('code')->pluck('code')->all(),
            'filters' => ['C1' => ['max' => 1000000]],
        ]);

        $response->assertRedirect(route('user.weighting.index'));
        $response->assertSessionHasErrors('filters');
        $this->assertDatabaseCount('calculations', 0);
    }

    public function test_rentang_terbalik_ditolak(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->from(route('user.weighting.index'))->post(route('user.weighting.calculate'), [
            'alternatives' => Alternative::orderBy('code')->pluck('code')->all(),
            'filters' => ['C3' => ['min' => 16, 'max' => 8]],
        ]);

        $response->assertRedirect(route('user.weighting.index'));
        $response->assertSessionHasErrors('filters');
        $this->assertDatabaseCount('calculations', 0);
    }

    public function test_tamu_tidak_bisa_mengakses_halaman_pencarian(): void
    {
        $this->get(route('user.weighting.index'))->assertRedirect(route('login'));
    }
}
