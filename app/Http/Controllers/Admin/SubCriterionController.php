<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use App\Models\SubCriterion;
use App\Services\ScoreRefreshService;
use Illuminate\Http\Request;

class SubCriterionController extends Controller
{
    public function __construct(private ScoreRefreshService $scoreRefresh) {}

    public function index(Criterion $criterion)
    {
        $criterion->load('subCriteria');
        $criteria = Criterion::orderBy('sort_order')->get();

        return view('admin.sub-criteria.index', compact('criterion', 'criteria'));
    }

    public function create(Criterion $criterion)
    {
        return view('admin.sub-criteria.create', compact('criterion'));
    }

    public function store(Request $request, Criterion $criterion)
    {
        $data = $this->validateRequest($request, $criterion->id);

        SubCriterion::create($data + ['criteria_id' => $criterion->id]);
        $this->scoreRefresh->refreshAll();

        return redirect()->route('admin.sub-criteria.index', $criterion)
            ->with('success', 'Subkriteria berhasil ditambahkan.');
    }

    public function edit(SubCriterion $subCriterion)
    {
        $criterion = $subCriterion->criterion;

        return view('admin.sub-criteria.edit', compact('subCriterion', 'criterion'));
    }

    public function update(Request $request, SubCriterion $subCriterion)
    {
        $data = $this->validateRequest($request, $subCriterion->criteria_id, $subCriterion->id);

        $subCriterion->update($data);
        $this->scoreRefresh->refreshAll();

        return redirect()->route('admin.sub-criteria.index', $subCriterion->criterion)
            ->with('success', 'Subkriteria berhasil diperbarui.');
    }

    public function destroy(SubCriterion $subCriterion)
    {
        $criterion = $subCriterion->criterion;
        $subCriterion->delete();
        $this->scoreRefresh->refreshAll();

        return redirect()->route('admin.sub-criteria.index', $criterion)
            ->with('success', 'Subkriteria berhasil dihapus.');
    }

    private function validateRequest(Request $request, int $criteriaId, ?int $id = null): array
    {
        return $request->validate([
            'score' => 'required|integer|min:1|max:5|unique:sub_criteria,score,'.($id ?? 'NULL').',id,criteria_id,'.$criteriaId,
            'min_value' => 'nullable|numeric',
            'label' => 'nullable|string|max:150',
        ], [
            'score.unique' => 'Skor ini sudah ada pada kriteria tersebut.',
        ]);
    }
}
