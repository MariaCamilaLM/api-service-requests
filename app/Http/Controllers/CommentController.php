<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    // Create a new comment
    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'author_id' => 'required|exists:users,id', // The user who is commenting
            'comment' => 'required|string',
        ]);

        $comment = Comment::create($request->all());
        return response()->json($comment, 201);
    }

    // Get all comments for a ticket
    public function index($ticketId)
    {
        return Comment::where('ticket_id', $ticketId)->get();
    }

    // Get a specific comment
    public function show($id)
    {
        return Comment::findOrFail($id);
    }

    // Update a specific comment
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $request->validate([
            'comment' => 'sometimes|required|string',
        ]);

        $comment->update($request->all());
        return response()->json($comment, 200);
    }

    // Delete a specific comment
    public function destroy($id)
    {
        Comment::destroy($id);
        return response()->json(null, 204);
    }
}
