<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\AlternativeValue;
use App\Models\Criterion;
use App\Services\ScoreConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlternativeController extends Controller
{
    public function __construct(private ScoreConverterService $scoreConverter) {}

    public function index()
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $alternatives = Alternative::with('values')->orderBy('code')->get();

        return view('admin.alternatives.index', compact('criteria', 'alternatives'));
    }

    public function create()
    {
        $criteria = Criterion::orderBy('sort_order')->get();

        return view('admin.alternatives.create', compact('criteria'));
    }

    public function store(Request $request)
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $data = $this->validateRequest($request, $criteria);

        DB::transaction(function () use ($data, $criteria) {
            $alternative = Alternative::create([
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
            ]);
            $this->syncValues($alternative, $criteria, $data['values']);
        });

        return redirect()->route('admin.alternatives.index')->with('success', 'Alternatif berhasil ditambahkan.');
    }

    public function edit(Alternative $alternative)
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $alternative->load('values');

        return view('admin.alternatives.edit', compact('criteria', 'alternative'));
    }

    public function update(Request $request, Alternative $alternative)
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $data = $this->validateRequest($request, $criteria, $alternative->id);

        DB::transaction(function () use ($data, $criteria, $alternative) {
            $alternative->update([
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
            ]);
            $this->syncValues($alternative, $criteria, $data['values']);
        });

        return redirect()->route('admin.alternatives.index')->with('success', 'Alternatif berhasil diperbarui.');
    }

    public function destroy(Alternative $alternative)
    {
        $alternative->delete();

        return redirect()->route('admin.alternatives.index')->with('success', 'Alternatif berhasil dihapus.');
    }

    private function validateRequest(Request $request, $criteria, ?int $id = null): array
    {
        $rules = [
            'code' => 'required|string|max:10|unique:alternatives,code'.($id ? ','.$id : ''),
            'name' => 'required|string|max:150',
            'values' => 'required|array|size:'.$criteria->count(),
        ];
        foreach ($criteria as $criterion) {
            $rules['values.'.$criterion->id] = 'required|numeric|min:0.01';
        }

        return $request->validate($rules, [
            'values.*.min' => 'Nilai harus lebih besar dari 0 (menghindari pembagian nol).',
            'values.*.numeric' => 'Nilai harus berupa angka.',
        ]);
    }

    private function syncValues(Alternative $alternative, $criteria, array $values): void
    {
        foreach ($criteria as $criterion) {
            $raw = (float) $values[$criterion->id];
            AlternativeValue::updateOrCreate(
                ['alternative_id' => $alternative->id, 'criteria_id' => $criterion->id],
                [
                    'raw_value' => $raw,
                    'score' => $this->scoreConverter->toScore($criterion, $raw),
                ]
            );
        }
    }
}
