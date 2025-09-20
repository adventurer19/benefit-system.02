<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'card_uid'
    ];

    /**
     * A member can have multiple memberships.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * A member can have multiple check-ins.
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Get the active membership for the member.
     */
    public function activeMembership(): HasOne
    {
        $today = now()->toDateString();

        return $this->hasOne(Membership::class)
            ->ofMany(['end_date' =>'max', 'id' => 'max'], function ($query) use ($today) {
                $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today)
                    ->where('status', MembershipStatus::Active);
            });
    }

    /**
     * Loads the last check-ins.
     */
    public function lastCheckin(): HasOne
    {
        return $this->hasOne(Checkin::class)->ofMany('checked_in_at', 'max');
    }
}
