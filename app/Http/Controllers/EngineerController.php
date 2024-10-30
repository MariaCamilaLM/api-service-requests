<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engineer;

class EngineerController extends Controller
{
    // Create a new engineer
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'specialization' => 'required|string|max:255',
        ]);

        $engineer = Engineer::create($request->all());
        return response()->json($engineer, 201);
    }

    // Get all engineers
    public function index()
    {
        return Engineer::all();
    }

    // Get a specific engineer
    public function show($id)
    {
        return Engineer::findOrFail($id);
    }

    // Update a specific engineer
    public function update(Request $request, $id)
    {
        $engineer = Engineer::findOrFail($id);
        $request->validate([
            'specialization' => 'sometimes|required|string|max:255',
        ]);

        $engineer->update($request->all());
        return response()->json($engineer, 200);
    }

    // Delete a specific engineer
    public function destroy($id)
    {
        Engineer::destroy($id);
        return response()->json(null, 204);
    }
}
