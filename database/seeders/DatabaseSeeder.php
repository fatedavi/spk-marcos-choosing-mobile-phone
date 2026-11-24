<?php

namespace Database\Seeders;

use App\Imports\SpkWorkbookImport;
use App\Models\Alternative;
use App\Models\AlternativeValue;
use App\Models\Criterion;
use App\Models\Questionnaire;
use App\Models\SubCriterion;
use App\Models\User;
use App\Services\ScoreConverterService;
use App\Services\WeightService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    private array $questionnaire = [
        'C1' => [33, 15, 1, 4, 2],
        'C2' => [31, 18, 3, 3, 0],
        'C3' => [29, 24, 2, 0, 0],
        'C4' => [32, 17, 3, 2, 1],
        'C5' => [31, 19, 3, 2, 0],
        'C6' => [30, 20, 2, 2, 1],
        'C7' => [33, 18, 1, 2, 1],
        'C8' => [17, 12, 6, 13, 7],
    ];

    public function run(): void
    {
        $path = base_path('data.xlsx');

        if (! file_exists($path)) {
            throw new RuntimeException('File data.xlsx tidak ditemukan di root project.');
        }

        $importer = new SpkWorkbookImport;
        Excel::import($importer, $path);

        $weights = (new WeightService)->normalize($importer->weights());
        $scoreConverter = new ScoreConverterService;

        User::updateOrCreate(['email' => 'admin@spk.test'], [
            'name' => 'Administrator', 'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        User::updateOrCreate(['email' => 'user@spk.test'], [
            'name' => 'Pengguna Demo', 'password' => Hash::make('password'), 'role' => 'user',
        ]);

        $criteriaByCode = [];
        $criteriaData = $importer->criteria();
        $subCriteriaData = $importer->subCriteria();

        foreach ($criteriaData as $data) {
            $criterion = Criterion::updateOrCreate(['code' => $data['code']], [
                'name' => $data['name'],
                'unit' => $data['unit'],
                'type' => $data['type'],
                'direction' => $data['direction'],
                'default_weight' => $weights[$data['code']],
                'sort_order' => $data['sort_order'],
            ]);
            $criteriaByCode[$data['code']] = $criterion;

            foreach ($subCriteriaData[$data['code']] ?? [] as $row) {
                SubCriterion::updateOrCreate(
                    ['criteria_id' => $criterion->id, 'score' => $row['score']],
                    ['min_value' => $row['min_value'], 'label' => $row['label']]
                );
            }

            [$ss, $s, $c, $ts, $sts] = $this->questionnaire[$data['code']];
            Questionnaire::updateOrCreate(['criteria_id' => $criterion->id], [
                'n_ss' => $ss,
                'n_s' => $s,
                'n_c' => $c,
                'n_ts' => $ts,
                'n_sts' => $sts,
            ]);
        }

        foreach ($importer->alternatives() as $data) {
            $alternative = Alternative::updateOrCreate(['code' => $data['code']], ['name' => trim($data['name'])]);

            foreach (array_values($criteriaData) as $i => $criterionData) {
                $criterion = $criteriaByCode[$criterionData['code']];
                $rawValue = (float) $data['values'][$i];

                AlternativeValue::updateOrCreate(
                    ['alternative_id' => $alternative->id, 'criteria_id' => $criterion->id],
                    [
                        'raw_value' => $rawValue,
                        'score' => $scoreConverter->toScore($criterion, $rawValue),
                    ]
                );
            }
        }
    }
}
