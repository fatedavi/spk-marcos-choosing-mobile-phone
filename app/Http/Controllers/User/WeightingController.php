<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criterion;
use App\Services\MarcosService;
use App\Services\MatrixBuilderService;
use App\Services\WeightService;
use Illuminate\Http\Request;

class WeightingController extends Controller
{
    public function __construct(
        private WeightService $weightService,
        private MatrixBuilderService $matrixBuilder,
        private MarcosService $marcos
    ) {}

    public function index()
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $alternatives = Alternative::with('values')->orderBy('code')->get();
        $priceMode = config('spk.price_mode');

        return view('user.weighting.index', compact('criteria', 'alternatives', 'priceMode'));
    }

    public function calculate(Request $request)
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $codes = $criteria->pluck('code')->all();
        $rules = [
            'alternatives' => 'required|array|min:2',
            'filters' => 'nullable|array',
        ];
        foreach ($codes as $code) {
            $rules['filters.'.$code] = 'nullable|array';
            $rules['filters.'.$code.'.min'] = 'nullable|numeric|min:0';
            $rules['filters.'.$code.'.max'] = 'nullable|numeric|min:0';
        }
        $data = $request->validate($rules, [
            'alternatives.min' => 'Minimal pilih 2 alternatif untuk dibandingkan.',
            'filters.*.min.numeric' => 'Batas minimum harus berupa angka.',
            'filters.*.min.min' => 'Batas minimum tidak boleh negatif.',
            'filters.*.max.numeric' => 'Batas maksimum harus berupa angka.',
            'filters.*.max.min' => 'Batas maksimum tidak boleh negatif.',
        ]);

        $selected = Alternative::with('values')
            ->whereIn('code', $data['alternatives'])
            ->orderBy('code')->get();

        if ($selected->count() < 2) {
            return back()->withErrors(['alternatives' => 'Minimal pilih 2 alternatif yang valid.'])->withInput();
        }

        $filters = collect($codes)
            ->mapWithKeys(fn ($code) => [$code => [
                'min' => $data['filters'][$code]['min'] ?? null,
                'max' => $data['filters'][$code]['max'] ?? null,
            ]])
            ->map(fn ($bounds) => array_filter($bounds, fn ($value) => $value !== null && $value !== ''))
            ->filter(fn ($bounds) => $bounds !== [])
            ->all();

        foreach ($filters as $code => $bounds) {
            if (isset($bounds['min'], $bounds['max']) && (float) $bounds['min'] > (float) $bounds['max']) {
                return back()->withErrors([
                    'filters' => 'Rentang '.$code.' tidak valid. Nilai minimum harus lebih kecil atau sama dengan nilai maksimum.',
                ])->withInput();
            }
        }

        $matched = $selected->filter(function (Alternative $alternative) use ($criteria, $filters) {
            foreach ($criteria as $criterion) {
                if (! array_key_exists($criterion->code, $filters)) {
                    continue;
                }

                $value = $alternative->values->firstWhere('criteria_id', $criterion->id);
                if (! $value) {
                    return false;
                }

                $raw = (float) $value->raw_value;
                $bounds = $filters[$criterion->code];

                if (array_key_exists('min', $bounds) && $raw < (float) $bounds['min']) {
                    return false;
                }

                if (array_key_exists('max', $bounds) && $raw > (float) $bounds['max']) {
                    return false;
                }
            }

            return true;
        })->values();

        if ($matched->count() < 2) {
            return back()->withErrors([
                'filters' => 'Hanya '.$matched->count().' HP yang cocok dengan kriteria. Minimal 2 HP diperlukan untuk perhitungan MARCOS. Longgarkan batas kriteria.',
            ])->withInput();
        }

        $defaultWeights = $criteria->mapWithKeys(fn (Criterion $criterion) => [
            $criterion->code => (float) $criterion->default_weight,
        ])->all();

        if (array_sum($defaultWeights) <= 0) {
            return back()->withErrors(['filters' => 'Bobot kuesioner belum tersedia. Hubungi admin untuk mengatur kuesioner.'])->withInput();
        }

        try {
            $weights = $this->weightService->normalize($defaultWeights);
            $priceMode = array_key_exists('C1', $filters) ? 'raw_cost' : config('spk.price_mode');
            $built = $this->matrixBuilder->build($criteria, $matched, $priceMode);
            $output = $this->marcos->calculate($built['matrix'], array_values($weights), $built['types']);
        } catch (\InvalidArgumentException|\DomainException $e) {
            return back()->withErrors(['filters' => $e->getMessage()])->withInput();
        }

        $calculation = Calculation::create([
            'user_id' => $request->user()->id,
            'weights' => $weights,
            'price_mode' => $priceMode,
            'result' => $built + [
                'weights' => $weights,
                'price_mode' => $priceMode,
                'output' => $output,
                'criteria_names' => $criteria->pluck('name', 'code')->all(),
                'criteria_units' => $criteria->pluck('unit', 'code')->all(),
                'filters' => $filters,
                'matched_codes' => $matched->pluck('code')->all(),
                'weight_source' => 'excel_fixed',
            ],
        ]);

        return redirect()->route('results.show', $calculation)
            ->with('success', 'Ditemukan '.$matched->count().' HP yang cocok. Perhitungan MARCOS berhasil.');
    }
}
