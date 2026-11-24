<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Services\ComparisonService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComparisonController extends Controller
{
    public function __construct(private ComparisonService $comparison) {}

    public function index(Request $request)
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $alternatives = Alternative::with('values')->orderBy('code')->get();

        $selectedCodes = $request->validate([
            'alternatives' => 'nullable|array|min:2|max:4',
            'alternatives.*' => Rule::in($alternatives->pluck('code')->all()),
        ], [
            'alternatives.min' => 'Pilih minimal 2 HP untuk dibandingkan.',
            'alternatives.max' => 'Maksimal 4 HP yang bisa dibandingkan.',
            'alternatives.*.in' => 'HP yang dipilih tidak valid.',
        ])['alternatives'] ?? [];

        $selected = $alternatives
            ->filter(fn (Alternative $alternative) => in_array($alternative->code, $selectedCodes, true))
            ->values();

        $result = $selected->count() >= 2 ? $this->comparison->build($criteria, $selected) : null;

        return view('comparison.index', compact('criteria', 'alternatives', 'selectedCodes', 'result'));
    }
}
