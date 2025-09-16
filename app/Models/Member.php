<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * A member can have multiple check-ins.
     */
    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Get the active membership for the member.
     */
    public function activeMembership()
    {
        return $this->memberships()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('status', 'active')
            ->first();
    }
}
