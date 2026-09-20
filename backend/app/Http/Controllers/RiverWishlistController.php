<?php

namespace App\Http\Controllers;

use App\Http\Resources\RiverResource;
use App\Models\River;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RiverWishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rivers = $request->user()
            ->wishlistRivers()
            ->with('creator:id,name')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Wishlist recuperada com sucesso',
            'data' => [
                'rivers' => RiverResource::collection($rivers),
            ],
        ]);
    }

    public function store(Request $request, River $river): JsonResponse
    {
        $changes = $request->user()->wishlistRivers()->syncWithoutDetaching([$river->id]);
        $wasAdded = count($changes['attached']) > 0;

        return response()->json([
            'message' => $wasAdded
                ? 'Rio adicionado a wishlist'
                : 'Rio ja estava na wishlist',
            'data' => [
                'riverId' => $river->id,
            ],
        ], $wasAdded ? 201 : 200);
    }

    public function destroy(Request $request, River $river): Response
    {
        $request->user()->wishlistRivers()->detach($river->id);

        return response()->noContent();
    }
}
