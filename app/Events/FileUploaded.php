<?php

namespace App\Events;

use App\Models\TicketFile;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FileUploaded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    
    public $file;

    /**
     * Create a new event instance.
     */
    public function __construct(TicketFile $file)
    {
        $this->file = $file->toArray();
        Log::info("file". json_encode($this->file));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
           
            return new Channel('ticket.' . $this->file['ticket_id']);
    }

    public function broadcastAs(): string{
        return 'FileUploaded';
    }
}
