<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'file_path',
    ];

    protected $hidden = ['file_path'];
    protected $appends = ['file_type'];
    public function getFileTypeAttribute()
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
