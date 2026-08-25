<?php

namespace App\Http\Controllers;

use App\Http\Requests\River\StoreRiverRequest;
use App\Http\Resources\RiverResource;
use App\Models\River;
use Illuminate\Http\JsonResponse;

class RiverController extends Controller
{
    public function index(): JsonResponse
    {
        $rivers = River::query()
            ->with('creator:id,name')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Rios recuperados com sucesso',
            'data' => [
                'rivers' => RiverResource::collection($rivers),
            ],
        ]);
    }

    public function store(StoreRiverRequest $request): JsonResponse
    {
        $river = River::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $river->load('creator:id,name');

        return response()->json([
            'message' => 'Rio cadastrado com sucesso',
            'data' => [
                'river' => new RiverResource($river),
            ],
        ], 201);
    }
}
