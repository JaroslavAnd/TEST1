<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::with(['bankAccounts', 'credits'])->get();

        return response()->json(['data' => $clients], Response::HTTP_OK);
    }

    public function show(string|int $id): JsonResponse
    {
        $client = Client::with(['bankAccounts', 'credits'])->find($id);

        if (!$client) {
            return response()->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $client], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:180|unique:clients,email',
            'phone' => 'required|string|max:50',
            'tax_id' => 'required|string|max:50|unique:clients,tax_id',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'data' => $client,
            'message' => 'Client created successfully',
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:180|unique:clients,email,' . $client->id,
            'phone' => 'sometimes|string|max:50',
            'tax_id' => 'sometimes|string|max:50|unique:clients,tax_id,' . $client->id,
        ]);

        $client->update($validated);

        return response()->json([
            'data' => $client,
            'message' => 'Client updated successfully',
        ], Response::HTTP_OK);
    }

    public function destroy(string|int $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully'], Response::HTTP_OK);
    }
}
