<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class GlobalController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
