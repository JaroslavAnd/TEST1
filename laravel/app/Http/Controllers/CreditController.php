<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\CreditType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CreditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Credit::with(['client', 'bankAccount', 'creditType']);

        if ($request->has('client_id')) {
            $query->where('client_id', $request->query('client_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json(['data' => $query->get()], Response::HTTP_OK);
    }

    public function show(string|int $id): JsonResponse
    {
        $credit = Credit::with(['client', 'bankAccount', 'creditType', 'payments'])->find($id);

        if (!$credit) {
            return response()->json(['error' => 'Credit not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $credit], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'credit_type_id' => 'required|exists:credit_types,id',
            'amount' => 'required|numeric|min:1',
            'remaining_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'term_months' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:active,paid,overdue',
        ]);

        $creditType = CreditType::find($validated['credit_type_id']);

        $startDate = isset($validated['start_date']) ? Carbon::parse($validated['start_date']) : Carbon::today();
        $termMonths = $validated['term_months'] ?? $creditType->term_months ?? 12;
        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : (clone $startDate)->addMonths($termMonths);

        $credit = Credit::create([
            'client_id' => $validated['client_id'],
            'bank_account_id' => $validated['bank_account_id'],
            'credit_type_id' => $validated['credit_type_id'],
            'amount' => $validated['amount'],
            'remaining_amount' => $validated['remaining_amount'] ?? $validated['amount'],
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'data' => $credit->load(['client', 'bankAccount', 'creditType']),
            'message' => 'Credit created successfully',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $credit = Credit::find($id);

        if (!$credit) {
            return response()->json(['error' => 'Credit not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:1',
            'remaining_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|in:active,paid,overdue',
            'end_date' => 'sometimes|date',
        ]);

        $credit->update($validated);

        return response()->json([
            'data' => $credit->load(['client', 'bankAccount', 'creditType']),
            'message' => 'Credit updated successfully',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $credit = Credit::find($id);

        if (!$credit) {
            return response()->json(['error' => 'Credit not found'], Response::HTTP_NOT_FOUND);
        }

        $credit->delete();

        return response()->json(['message' => 'Credit deleted successfully'], Response::HTTP_OK);
    }
}
