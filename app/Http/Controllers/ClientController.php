<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    // Create a new client
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'company_name' => 'required|string|max:255',
        ]);

        $client = Client::create($request->all());
        return response()->json($client, 201);
    }

    // Get all clients
    public function index()
    {
        return Client::all();
    }

    // Get a specific client
    public function show($id)
    {
        return Client::findOrFail($id);
    }

    // Update a specific client
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
        ]);

        $client->update($request->all());
        return response()->json($client, 200);
    }

    // Delete a specific client
    public function destroy($id)
    {
        Client::destroy($id);
        return response()->json(null, 204);
    }
}
