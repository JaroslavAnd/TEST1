<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('credit');

        if ($request->has('credit_id')) {
            $query->where('credit_id', $request->query('credit_id'));
        }

        return response()->json(['data' => $query->get()], Response::HTTP_OK);
    }

    public function show(string|int $id): JsonResponse
    {
        $payment = Payment::with('credit')->find($id);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $payment], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credit_id' => 'required|exists:credits,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:completed,pending,failed',
        ]);

        $credit = Credit::find($validated['credit_id']);

        $payment = Payment::create([
            'credit_id' => $validated['credit_id'],
            'amount' => $validated['amount'],
            'payment_date' => isset($validated['payment_date']) ? Carbon::parse($validated['payment_date']) : Carbon::now(),
            'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
            'status' => $validated['status'] ?? 'completed',
        ]);

        // Reduce remaining amount on the credit
        $newRemaining = max(0, (float)$credit->remaining_amount - (float)$validated['amount']);
        $credit->remaining_amount = $newRemaining;
        if ($newRemaining == 0) {
            $credit->status = 'paid';
        }
        $credit->save();

        return response()->json([
            'data' => $payment->load('credit'),
            'message' => 'Payment recorded successfully',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|string|max:50',
            'status' => 'sometimes|string|in:completed,pending,failed',
        ]);

        $payment->update($validated);

        return response()->json([
            'data' => $payment->load('credit'),
            'message' => 'Payment updated successfully',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully'], Response::HTTP_OK);
    }
}
