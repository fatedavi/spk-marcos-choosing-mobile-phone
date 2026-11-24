<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\Questionnaire;
use App\Services\WeightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionnaireController extends Controller
{
    public function __construct(private WeightService $weightService) {}

    public function index()
    {
        $criteria = Criterion::with('questionnaire')->orderBy('sort_order')->get();
        $rows = $this->rows($criteria);
        $weights = $this->weightService->fromQuestionnaire($rows);

        return view('admin.questionnaire.index', compact('criteria', 'weights'));
    }

    public function edit()
    {
        $criteria = Criterion::with('questionnaire')->orderBy('sort_order')->get();

        return view('admin.questionnaire.edit', compact('criteria'));
    }

    public function update(Request $request)
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $rules = [];
        foreach ($criteria as $criterion) {
            foreach (['n_ss', 'n_s', 'n_c', 'n_ts', 'n_sts'] as $field) {
                $rules[$criterion->id.'.'.$field] = 'required|integer|min:0';
            }
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($criteria, $data) {
            foreach ($criteria as $criterion) {
                Questionnaire::updateOrCreate(
                    ['criteria_id' => $criterion->id],
                    array_map('intval', $data[$criterion->id])
                );
            }
        });

        return redirect()->route('admin.questionnaire.index')
            ->with('success', 'Data kuesioner disimpan sebagai referensi.');
    }

    private function rows($criteria): array
    {
        return $criteria->map(function (Criterion $criterion) {
            $q = $criterion->questionnaire;

            return [
                'code' => $criterion->code,
                'n_ss' => $q?->n_ss ?? 0,
                'n_s' => $q?->n_s ?? 0,
                'n_c' => $q?->n_c ?? 0,
                'n_ts' => $q?->n_ts ?? 0,
                'n_sts' => $q?->n_sts ?? 0,
            ];
        })->all();
    }
}
