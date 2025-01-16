<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $sauna_id = $request->query('sauna_id');

        $schedules = Schedule::with('sauna')
            ->where('sauna_id', $sauna_id)
            ->where('date', '>=', now()->startOfDay())
            ->where('date', '<=', now()->addDays(30))
            ->get();

        return response()->json($schedules);
    }
}
