<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sauna;
use Illuminate\Http\Request;

class SaunaController extends Controller
{
    public function index()
    {
        $saunas = Sauna::select('id', 'name')->get();
        return response()->json($saunas);
    }
}
