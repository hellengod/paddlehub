<?php

namespace App\Http\Controllers;

use App\Services\AnaHydrographyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class HydrographyController extends Controller
{
    public function ana(Request $request, AnaHydrographyService $anaHydrography): JsonResponse
    {
        $validatedBounds = $request->validate([
            'west' => ['required', 'numeric', 'between:-180,180'],
            'south' => ['required', 'numeric', 'between:-90,90'],
            'east' => ['required', 'numeric', 'between:-180,180'],
            'north' => ['required', 'numeric', 'between:-90,90'],
        ]);
        $bounds = [
            'west' => (float) $validatedBounds['west'],
            'south' => (float) $validatedBounds['south'],
            'east' => (float) $validatedBounds['east'],
            'north' => (float) $validatedBounds['north'],
        ];

        try {
            return response()->json([
                'message' => 'Hidrografia da ANA recuperada com sucesso.',
                'data' => $anaHydrography->fetch($bounds),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Nao foi possivel consultar a hidrografia da ANA agora.',
            ], 502);
        }
    }
}
