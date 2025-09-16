<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'timestamp',
        'status',
    ];

    protected $casts = [
        // Convert timestamp to Carbon instance for easy date manipulation.
        'timestamp' => 'datetime'
    ];

    /**
     * Check-in belongs to a member.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
