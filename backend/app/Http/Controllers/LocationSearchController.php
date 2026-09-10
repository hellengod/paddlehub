<?php

namespace App\Http\Controllers;

use App\Services\LocationSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class LocationSearchController extends Controller
{
    public function __invoke(Request $request, LocationSearchService $locationSearch): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        try {
            return response()->json([
                'data' => [
                    'places' => $locationSearch->search($validated['q']),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Nao foi possivel pesquisar locais agora.',
            ], 502);
        }
    }
}
