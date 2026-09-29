<?php

namespace App\Http\Controllers;

use App\Http\Resources\RiverResource;
use App\Models\River;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RiverPaddlingListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rivers = $request->user()
            ->riversToPaddle()
            ->with('creator:id,name')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Rios onde quero remar recuperados com sucesso',
            'data' => [
                'rivers' => RiverResource::collection($rivers),
            ],
        ]);
    }

    public function store(Request $request, River $river): JsonResponse
    {
        $changes = $request->user()->riversToPaddle()->syncWithoutDetaching([$river->id]);
        $wasAdded = count($changes['attached']) > 0;

        return response()->json([
            'message' => $wasAdded
                ? 'Rio adicionado aos rios onde quero remar'
                : 'Rio ja estava nos rios onde quero remar',
            'data' => [
                'riverId' => $river->id,
            ],
        ], $wasAdded ? 201 : 200);
    }

    public function destroy(Request $request, River $river): Response
    {
        $request->user()->riversToPaddle()->detach($river->id);

        return response()->noContent();
    }
}
