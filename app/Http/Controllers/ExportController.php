<?php

namespace App\Http\Controllers;

use App\Exports\MarcosResultExport;
use App\Models\Calculation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request, Calculation $calculation)
    {
        abort_unless(
            $request->user()->isAdmin() || $calculation->user_id === $request->user()->id,
            403,
            'Anda tidak memiliki akses ke hasil ini.'
        );

        $filename = 'hasil-marcos-'.$calculation->id.'-'.$calculation->created_at->format('Ymd-His').'.xlsx';

        return Excel::download(new MarcosResultExport($calculation), $filename);
    }
}
