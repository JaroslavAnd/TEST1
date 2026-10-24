<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BankAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BankAccount::with('client');

        if ($request->has('client_id')) {
            $query->where('client_id', $request->query('client_id'));
        }

        return response()->json(['data' => $query->get()], Response::HTTP_OK);
    }

    public function show(string|int $id): JsonResponse
    {
        $account = BankAccount::with(['client', 'credits'])->find($id);

        if (!$account) {
            return response()->json(['error' => 'Bank account not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $account], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'account_number' => 'required|string|max:34|unique:bank_accounts,account_number',
            'balance' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'status' => 'nullable|string|in:active,blocked,closed',
        ]);

        $account = BankAccount::create($validated);

        return response()->json([
            'data' => $account->load('client'),
            'message' => 'Bank account created successfully',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $account = BankAccount::find($id);

        if (!$account) {
            return response()->json(['error' => 'Bank account not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'account_number' => 'sometimes|string|max:34|unique:bank_accounts,account_number,' . $account->id,
            'balance' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'status' => 'sometimes|string|in:active,blocked,closed',
        ]);

        $account->update($validated);

        return response()->json([
            'data' => $account->load('client'),
            'message' => 'Bank account updated successfully',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $account = BankAccount::find($id);

        if (!$account) {
            return response()->json(['error' => 'Bank account not found'], Response::HTTP_NOT_FOUND);
        }

        $account->delete();

        return response()->json(['message' => 'Bank account deleted successfully'], Response::HTTP_OK);
    }
}
