<?php

namespace Tests\Feature;

use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criterion;
use App\Models\User;
use App\Services\ComparisonService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get(route('comparison.index'))->assertRedirect(route('login'));
    }

    public function test_halaman_pemilihan_menampilkan_semua_hp(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('comparison.index'));

        $response->assertOk();
        $response->assertSee('name="alternatives[]"', false);
        $response->assertSee('value="A32"', false);
        $response->assertSee('Bandingkan HP');
    }

    public function test_perbandingan_menampilkan_nilai_skor_dan_ringkasan(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('comparison.index', [
            'alternatives' => ['A3', 'A22'],
        ]));

        $response->assertOk();
        $response->assertSee('RedMagic 11 Pro');
        $response->assertSee('Infinix GT 20 Pro');
        $response->assertSee('Rp 19.999.000');
        $response->assertSee('skor 5');
        $response->assertSee('Unggul');
        $response->assertSee('Skor terbobot');
        $response->assertSee('Hitung MARCOS untuk HP Terpilih');
    }

    public function test_unggul_ditentukan_dari_jenis_kriteria(): void
    {
        $this->seed(DatabaseSeeder::class);

        $criteria = Criterion::orderBy('sort_order')->get();
        $selected = Alternative::with('values')->whereIn('code', ['A3', 'A22'])->get();
        $result = app(ComparisonService::class)->build($criteria, $selected);

        $price = collect($result['rows'])->firstWhere('code', 'C1');
        $this->assertSame('unggul', $price['cells']['A22']['state']);
        $this->assertSame('', $price['cells']['A3']['state']);

        $antutu = collect($result['rows'])->firstWhere('code', 'C2');
        $this->assertSame('unggul', $antutu['cells']['A3']['state']);
        $this->assertSame('', $antutu['cells']['A22']['state']);
        $this->assertSame(8, collect($result['rows'])->count());
    }

    public function test_jumlah_dan_kode_hp_divalidasi(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('comparison.index', ['alternatives' => ['A1']]))
            ->assertSessionHasErrors('alternatives');

        $this->actingAs($user)->get(route('comparison.index', [
            'alternatives' => ['A1', 'A2', 'A3', 'A4', 'A5'],
        ]))->assertSessionHasErrors('alternatives');

        $this->actingAs($user)->get(route('comparison.index', [
            'alternatives' => ['A1', 'XXX'],
        ]))->assertSessionHasErrors('alternatives.1');
    }

    public function test_tombol_marcos_memakai_hp_terpilih(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('user.weighting.calculate'), [
            'alternatives' => ['A3', 'A22'],
        ]);

        $calculation = Calculation::firstOrFail();
        $response->assertRedirect(route('results.show', $calculation));
        $this->assertSame(['A22', 'A3'], $calculation->result['matched_codes']);
    }
}
