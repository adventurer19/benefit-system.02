<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MembershipStatus;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'start_date',
        'end_date',
        'type',
        'status', // 'active' | 'expired' | 'frozen'
    ];

    protected $casts = [
        // Convert start_date and end_date from strings to Carbon instances.
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => MembershipStatus::class,
    ];

    /**
     * Membership belongs to a member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
