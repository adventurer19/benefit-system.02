<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\CheckinStatus;

class Checkin extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'checkin_date',
        'status', // 'success' | 'denied'
    ];

    protected $casts = [
        // Convert timestamp to Carbon instance for easy date manipulation.
        'checkin_date' => 'datetime',
        'status' => CheckinStatus::class,
    ];

    /**
     * Check-in belongs to a member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
