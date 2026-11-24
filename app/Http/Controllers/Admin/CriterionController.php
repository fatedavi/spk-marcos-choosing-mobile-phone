<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Criterion;
use Illuminate\Http\Request;

class CriterionController extends Controller
{
    public function index()
    {
        $criteria = Criterion::orderBy('sort_order')->get();

        return view('admin.criteria.index', compact('criteria'));
    }

    public function create()
    {
        return view('admin.criteria.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data['code'] = strtoupper($data['code']);

        Criterion::create($data);

        return redirect()->route('admin.criteria.index')->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function edit(Criterion $criterion)
    {
        return view('admin.criteria.edit', compact('criterion'));
    }

    public function update(Request $request, Criterion $criterion)
    {
        $data = $this->validateRequest($request, $criterion->id);
        $data['code'] = strtoupper($data['code']);

        $criterion->update($data);

        return redirect()->route('admin.criteria.index')->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function destroy(Criterion $criterion)
    {
        $criterion->delete();

        return redirect()->route('admin.criteria.index')->with('success', 'Kriteria berhasil dihapus.');
    }

    private function validateRequest(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:10|unique:criteria,code'.($id ? ','.$id : ''),
            'name' => 'required|string|max:150',
            'unit' => 'nullable|string|max:30',
            'type' => 'required|in:cost,benefit',
            'direction' => 'required|in:asc,desc',
            'default_weight' => 'required|numeric|min:0|max:1',
            'sort_order' => 'required|integer|min:1',
        ], [
            'code.unique' => 'Kode kriteria sudah digunakan.',
            'default_weight.max' => 'Bobot tidak boleh lebih dari 1.',
        ]);
    }
}
