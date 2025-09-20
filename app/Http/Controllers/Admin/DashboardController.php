<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Checkin;
use App\Models\Membership;
use App\Enums\CheckinStatus;
use App\Enums\MembershipStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function getStats(): JsonResponse
    {
        $today = Carbon::today();

        $todayCheckins = Checkin::where('checked_in_at', '>=', $today)
            ->where('status', CheckinStatus::Success)
            ->count();

        $todayDenied = Checkin::where('checked_in_at', '>=', $today)
            ->where('status', CheckinStatus::Denied)
            ->count();

        $currentlyInside = Checkin::where('checked_in_at', '>=', now()->subHours(4))
            ->where('status', CheckinStatus::Success)
            ->distinct('member_id')
            ->count();

        $expiringSoon = Membership::where('end_date', '<=', $today->copy()->addDays(7))
            ->where('end_date', '>=', $today)
            ->where('status', MembershipStatus::Active)
            ->count();

        return response()->json([
            'todayCheckins' => $todayCheckins,
            'todayDenied' => $todayDenied,
            'currentlyInside' => $currentlyInside,
            'expiringSoon' => $expiringSoon,
        ]);
    }

    public function getSuccessfulCheckins(): JsonResponse
    {
        $today = Carbon::today();

        $checkins = Checkin::with(['member.activeMembership'])
            ->where('checked_in_at', '>=', $today)
            ->where('status', CheckinStatus::Success)
            ->orderBy('checked_in_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($checkin) {
                $membership = $checkin->member->activeMembership;
                $daysRemaining = $membership ?
                    Carbon::today()->diffInDays($membership->end_date, false) : 0;

                return [
                    'id' => $checkin->id,
                    'time' => $checkin->checked_in_at->format('H:i'),
                    'member' => $checkin->member->name,
                    'membership_type' => $membership->type ?? 'N/A',
                    'days_remaining' => max(0, $daysRemaining),
                    'card_uid' => $checkin->member->card_uid,
                ];
            });

        return response()->json($checkins);
    }

    public function getDeniedAccess(): JsonResponse
    {
        $today = Carbon::today();

        $denied = Checkin::with(['member'])
            ->where('checked_in_at', '>=', $today)
            ->where('status', CheckinStatus::Denied)
            ->orderBy('checked_in_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($checkin) {
                $member = $checkin->member;
                $reason = $this->getDenialReason($member);

                return [
                    'id' => $checkin->id,
                    'time' => $checkin->checked_in_at->format('H:i'),
                    'member' => $member->name,
                    'card_uid' => $member->card_uid,
                    'reason' => $reason['reason'],
                    'details' => $reason['details'],
                    'member_id' => $member->id,
                ];
            });

        return response()->json($denied);
    }

    public function getActivityLog(): JsonResponse
    {
        $today = Carbon::today();

        $activities = Checkin::with(['member'])
            ->where('checked_in_at', '>=', $today)
            ->orderBy('checked_in_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($checkin) {
                return [
                    'id' => $checkin->id,
                    'time' => $checkin->checked_in_at->format('H:i'),
                    'member' => $checkin->member->name,
                    'card_uid' => $checkin->member->card_uid,
                    'status' => $checkin->status->value,
                    'reason' => $checkin->status === CheckinStatus::Success ?
                        'Access Granted' :
                        $this->getDenialReason($checkin->member)['reason'],
                ];
            });

        return response()->json($activities);
    }

    public function getUpdates(Request $request): JsonResponse
    {
        $lastUpdate = $request->query('since');
        $since = $lastUpdate ? Carbon::parse($lastUpdate) : Carbon::now()->subMinutes(5);

        $newCheckins = Checkin::with(['member'])
            ->where('checked_in_at', '>', $since)
            ->orderBy('checked_in_at', 'desc')
            ->get()
            ->map(function ($checkin) {
                $isSuccess = $checkin->status === CheckinStatus::Success;
                $member = $checkin->member;

                $data = [
                    'id' => $checkin->id,
                    'time' => $checkin->checked_in_at->format('H:i'),
                    'member' => $member->name,
                    'card_uid' => $member->card_uid,
                    'status' => $checkin->status->value,
                    'timestamp' => $checkin->checked_in_at->toISOString(),
                ];

                if ($isSuccess) {
                    $membership = $member->activeMembership;
                    $data['membership_type'] = $membership->type ?? 'N/A';
                    $data['days_remaining'] = $membership ?
                        Carbon::today()->diffInDays($membership->end_date, false) : 0;
                } else {
                    $reason = $this->getDenialReason($member);
                    $data['reason'] = $reason['reason'];
                    $data['details'] = $reason['details'];
                    $data['member_id'] = $member->id;
                }

                return $data;
            });

        return response()->json([
            'updates' => $newCheckins,
            'last_update' => now()->toISOString(),
        ]);
    }

    private function getDenialReason(Member $member): array
    {
        $membership = $member->activeMembership;

        if (!$membership) {
            return [
                'reason' => 'No Active Membership',
                'details' => 'Member has no active membership'
            ];
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($membership->end_date);

        if ($endDate->isPast()) {
            return [
                'reason' => 'Membership Expired',
                'details' => "Expired on {$endDate->format('M d, Y')}"
            ];
        }

        if ($membership->status === MembershipStatus::Frozen) {
            return [
                'reason' => 'Membership Frozen',
                'details' => 'Membership is currently frozen'
            ];
        }

        return [
            'reason' => 'Unknown',
            'details' => 'Please check membership status'
        ];
    }
}
