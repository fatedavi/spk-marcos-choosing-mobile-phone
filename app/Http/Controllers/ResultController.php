<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function show(Request $request, Calculation $calculation)
    {
        abort_unless(
            $request->user()->isAdmin() || $calculation->user_id === $request->user()->id,
            403,
            'Anda tidak memiliki akses ke hasil ini.'
        );

        $result = $calculation->result;
        $output = $result['output'];
        $ranking = $output['ranking'];
        $topCode = $ranking[0] ?? null;
        $recommendation = [
            'code' => $topCode,
            'name' => $result['labels'][$topCode] ?? $topCode,
            'f' => $output['alternatives'][$topCode]['f'] ?? null,
        ];

        return view('results.show', compact('calculation', 'result', 'output', 'recommendation'));
    }
}
