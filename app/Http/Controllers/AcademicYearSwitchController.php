<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearSwitchController extends Controller
{
    /**
     * Store the selected academic year in the session.
     * Posting academic_year_id = 'all' (or empty) clears the filter.
     */
    public function store(Request $request)
    {
        $id = $request->input('academic_year_id');

        if (!$id || $id === 'all') {
            session()->forget('selected_academic_year_id');
        } else {
            // Ensure it's a valid AcademicYear
            AcademicYear::findOrFail((int) $id);
            session(['selected_academic_year_id' => (int) $id]);
        }

        return back();
    }
}
