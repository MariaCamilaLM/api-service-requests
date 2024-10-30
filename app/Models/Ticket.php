<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'priority',
        'issue_description',
        'solution_description',
        'client_id',
        'equipment_number',
        'serial_number',
        'brand',
        'is_under_warranty',
        'accept_conditions',
        'title',
        'comments'
    ];

    public function files()
    {
        return $this->hasMany(TicketFile::class);
    }

    public function client() {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function engineer() {
        return $this->hasOne(Engineer::class, 'id', 'engineer_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
