<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calculation;

class CalculationController extends Controller
{
    public function index()
    {
        $calculations = Calculation::with('user')->latest()->get();

        return view('admin.calculations.index', compact('calculations'));
    }
}
