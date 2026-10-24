<?php

namespace App\Http\Controllers;

use App\Models\CreditType;
use Illuminate\Database\QueryException;
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
            return response()->json(['error' => 'Тип кредиту не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $type], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:credit_types,name',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gte:min_amount',
            'term_months' => 'required|integer|min:1',
        ]);

        $type = CreditType::create($validated);

        return response()->json([
            'data' => $type,
            'message' => 'Тип кредиту успішно створено',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $type = CreditType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Тип кредиту не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:credit_types,name,' . $type->id,
            'interest_rate' => 'sometimes|numeric|min:0|max:100',
            'min_amount' => 'sometimes|numeric|min:0',
            'max_amount' => 'sometimes|numeric',
            'term_months' => 'sometimes|integer|min:1',
        ]);

        $type->update($validated);

        return response()->json([
            'data' => $type,
            'message' => 'Тип кредиту успішно оновлено',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $type = CreditType::find($id);

        if (!$type) {
            return response()->json(['error' => 'Тип кредиту не знайдено'], Response::HTTP_NOT_FOUND);
        }

        // Захист від видалення типу кредиту, який використовується в кредитах (HTTP 409 Conflict замість SQL 500)
        if ($type->credits()->exists()) {
            return response()->json([
                'error' => 'Неможливо видалити тип кредиту, оскільки існують оформлені кредити цього типу'
            ], Response::HTTP_CONFLICT);
        }

        try {
            $type->delete();
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Неможливо видалити тип кредиту через обмеження зовнішнього ключа'
            ], Response::HTTP_CONFLICT);
        }

        return response()->json(['message' => 'Тип кредиту успішно видалено'], Response::HTTP_OK);
    }
}
