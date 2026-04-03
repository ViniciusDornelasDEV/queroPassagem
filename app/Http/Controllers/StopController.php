<?php

namespace App\Http\Controllers;

use App\Services\StopService;
use Illuminate\Http\JsonResponse;

class StopController extends Controller
{
    public function __construct(
        private readonly StopService $stopService,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->stopService->getStops(),
        ]);
    }
}
