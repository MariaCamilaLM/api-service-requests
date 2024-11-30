<?php

namespace App\Http\Controllers;

use App\Events\FileUploaded;
use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Models\Client;
use App\Models\Engineer;
use App\Models\TicketFile;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Comment;
use App\Events\CommentPosted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        Log::info('store started');
        $request->validate([
            'priority' => 'required|string|max:255',
            'issue_description' => 'required|string',
            'serial_number' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'is_under_warranty' => 'required',
            'accept_conditions' => 'required',
            'solution_description' => 'nullable|string',
            'files.*' => 'file|mimes:jpg,png,pdf,doc,docx|max:2048',
        ]);
        Log::info('validated');
        $user = Auth::user();
        $client = $user->clients()->first();

        $ticketData = $request->all();
        $ticketData['client_id'] = $client->id;

        $ticket = Ticket::create($ticketData);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = $file->store('ticket_files');
                $ticket->files()->create(['file_path' => $filePath]);
            }
        }
        event(new TicketCreated($ticket));
        
        return response()->json($ticket, 201);
    }

    public function index()
    {
        return Ticket::with(['client', 'engineer'])->get();
    }

    public function show($id)
    {
        $user = Auth::user();
        $ticket = Ticket::with(['client', 'engineer', 'files', 'comments.user'])->findOrFail($id);

        if (
            ($user->clients && $ticket->client_id === $user->clients->id)
            || ($user->profile === 'engineer')
        ) {
            return $ticket;
        }

        return response()->json('Unauthorized', 401);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $request->validate([
            'client_id' => 'sometimes|required|exists:clients,id',
            'engineer_id' => 'nullable|exists:engineers,id',
            'status' => 'sometimes|required|string|max:255',
            'priority' => 'sometimes|required|string|max:255',
            'issue_description' => 'sometimes|required|string',
            'serial_number' => 'sometimes|required|string|max:255',
            'brand' => 'sometimes|required|string|max:255',
            'is_under_warranty' => 'sometimes|required|boolean',
            'accept_conditions' => 'sometimes|required|boolean',
            'solution_description' => 'sometimes|nullable|string',
        ]);

        $ticket->update($request->all());
        event(new TicketUpdated($ticket));

        return response()->json($ticket, 200);
    }

    public function destroy($id)
    {
        Ticket::destroy($id);
        return response()->json(null, 204);
    }

    public function getTicketsByClient()
    {
        $user = Auth::user();
        Log::info('user ' . json_encode($user));

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $clientId = Client::where('user_id', $user->id)->value('id');

        $tickets = Ticket::withCount('files')->where('client_id', $clientId)->get();

        return response()->json($tickets);
    }

    public function getTicketsByEngineer()
    {
        $user = Auth::user();
        Log::info('user ' . json_encode($user));

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $clientId = Engineer::where('user_id', $user->id)->value('id');

        $tickets = Ticket::withCount('files')->where('engineer_id', $clientId)->get();

        return response()->json($tickets);
    }

    public function downloadFile(Request $request, $id)
    {
        $ticketFile = TicketFile::find($id);
        $user = Auth::user();
        if (
            !$ticketFile->ticket || // Ensure the ticket exists
            (!$user->clients || $ticketFile->ticket->client_id !== $user->clients->id) || // Check client ID if clients exist
            ($user->engineers && $ticketFile->ticket->engineer_id !== $user->engineers->id) // Check engineer ID if engineers exist
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $filePath = $ticketFile->file_path;
        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::download($filePath);
    }

    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ]);

        $ticket = Ticket::findOrFail($id);

        foreach ($request->file('files') as $file) {
            $filePath = $file->store('ticket_files');
            $file = $ticket->files()->create(['file_path' => $filePath]);
            event(new FileUploaded($file));
        }

        return response()->json(['message' => 'File uploaded successfully.'], 201);
    }

    public function createComment(Request $request, $ticketId)
    {
        $request->validate([
            'comment_text' => 'required|string|max:500',
        ]);

        $ticket = Ticket::findOrFail($ticketId);

        $comment = new Comment([
            'comment_text' => $request->input('comment_text'),
            'ticket_id' => $ticket->id,
            'author_id' => auth()->id(),
        ]);

        $comment->save();
        $comment->user = auth()->user();

        event(new CommentPosted($comment));

        return response()->json(['message' => 'Comment added successfully.', 'comment' => $comment], 201);
    }

    public function getCommentsByTicket($ticketId)
    {

        $ticket = Ticket::findOrFail($ticketId);

        $comments = $ticket->comments;

        return response()->json($comments);
    }

    public function getUnassignedTickets(Request $request)
    {
        $tickets = Ticket::withCount('files')->whereNull('engineer_id', )->get();
        return response()->json($tickets);
    }

    public function assignToMe( Request $request, $ticketId) {
        $user = Auth::user();

        if ($user->profile !== 'engineer') {
            return response()->json(['Unauthorized'=> ''],403);
        }

        $ticket = Ticket::findOrFail($ticketId);
        Log::info($user);
        $engineer = Engineer::where('user_id', $user->id)->first();
        $ticket->engineer_id = $engineer->id;
        $ticket->save();

        return response()->json(['message'=> '',''=> $ticket->id], 200);
    }

}
