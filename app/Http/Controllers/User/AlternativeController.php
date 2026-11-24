<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Alternative;
use App\Models\Criterion;

class AlternativeController extends Controller
{
    public function index()
    {
        $criteria = Criterion::orderBy('sort_order')->get();
        $alternatives = Alternative::with('values')->orderBy('code')->get();

        return view('user.alternatives.index', compact('criteria', 'alternatives'));
    }
}
