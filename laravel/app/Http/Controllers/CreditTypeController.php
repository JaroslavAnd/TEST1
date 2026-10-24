<?php

namespace App\Http\Controllers;

use App\Models\CreditType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CreditTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = CreditType::all();

        return response()->json(['data' => $types], Response::HTTP_OK);
    }

    public function show(string|int $id): JsonResponse
    {
        $type = CreditType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Credit type not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $type], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gte:min_amount',
            'term_months' => 'required|integer|min:1',
        ]);

        $type = CreditType::create($validated);

        return response()->json([
            'data' => $type,
            'message' => 'Credit type created successfully',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $type = CreditType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Credit type not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'interest_rate' => 'sometimes|numeric|min:0|max:100',
            'min_amount' => 'sometimes|numeric|min:0',
            'max_amount' => 'sometimes|numeric',
            'term_months' => 'sometimes|integer|min:1',
        ]);

        $type->update($validated);

        return response()->json([
            'data' => $type,
            'message' => 'Credit type updated successfully',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $type = CreditType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Credit type not found'], Response::HTTP_NOT_FOUND);
        }

        $type->delete();

        return response()->json(['message' => 'Credit type deleted successfully'], Response::HTTP_OK);
    }
}
