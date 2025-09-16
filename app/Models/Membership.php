<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'start_date',
        'end_date',
        'type',
        'status',
    ];

    protected $casts = [
        // Convert start_date and end_date from strings to Carbon instances.
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    /**
     * Membership belongs to a member.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
