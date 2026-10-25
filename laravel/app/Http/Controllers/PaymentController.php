<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Payment;
use App\Services\PaginationService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index(Request $request, PaginationService $paginationService): JsonResponse
    {
        $query = Payment::filter($request->query())->with('credit');
        $result = $paginationService->paginate($query, $request);

        return response()->json($result, Response::HTTP_OK);
    }

    public function show(string|int $id): JsonResponse
    {
        $payment = Payment::with('credit')->find($id);

        if (!$payment) {
            return response()->json(['error' => 'Платіж не знайдено'], Response::HTTP_NOT_FOUND);
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

        // Зменшення залишку заборгованості за кредитом
        $newRemaining = max(0, (float)$credit->remaining_amount - (float)$validated['amount']);
        $credit->remaining_amount = $newRemaining;
        if ($newRemaining == 0) {
            $credit->status = 'paid';
        }
        $credit->save();

        return response()->json([
            'data' => $payment->load('credit'),
            'message' => 'Платіж успішно проведено',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Платіж не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|string|max:50',
            'status' => 'sometimes|string|in:completed,pending,failed',
        ]);

        $payment->update($validated);

        return response()->json([
            'data' => $payment->load('credit'),
            'message' => 'Дані платежу успішно оновлено',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Платіж не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $payment->delete();
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Неможливо видалити платіж через обмеження зовнішнього ключа'
            ], Response::HTTP_CONFLICT);
        }

        return response()->json(['message' => 'Платіж успішно видалено'], Response::HTTP_OK);
    }
}
