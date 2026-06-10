<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TourController extends Controller
{
    public function complete(Request $request)
    {
        $request->user()->update(['has_seen_tour' => true]);
        return response()->json(['ok' => true]);
    }
}
