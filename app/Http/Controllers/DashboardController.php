<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Calculation;
use App\Models\Criterion;
use App\Models\Questionnaire;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $counts = [
            'criteria' => Criterion::count(),
            'alternatives' => Alternative::count(),
            'questionnaire' => Questionnaire::count(),
            'calculations' => Calculation::count(),
        ];

        return $request->user()->isAdmin()
            ? view('admin.dashboard', compact('counts'))
            : view('user.dashboard', compact('counts'));
    }
}
