<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Checkin;
use App\Enums\CheckinStatus;
use App\Enums\MembershipStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CardValidationController extends Controller
{
    /**
     * Validate card and process gym entry
     */
    public function validateCard(Request $request): JsonResponse
    {
        $request->validate([
            'card_uid' => 'required|string|max:64',
            'device_id' => 'nullable|string|max:50',
        ]);

        $cardUid = $request->card_uid;
        $deviceId = $request->device_id ?? 'unknown';

        Log::info("Card validation attempt", [
            'card_uid' => $cardUid,
            'device_id' => $deviceId,
            'timestamp' => now(),
            'ip' => $request->ip()
        ]);

        try {
            // Find member by card UID
            $member = Member::where('card_uid', $cardUid)
                ->with(['activeMembership', 'lastCheckin'])
                ->first();

            if (!$member) {
                return $this->createResponse(
                    status: 'denied',
                    reason: 'card_not_found',
                    message: 'Card not registered in the system',
                    cardUid: $cardUid
                );
            }

            // Check membership status
            $validationResult = $this->validateMembership($member);

            if (!$validationResult['valid']) {
                // Log failed attempt
                $this->logCheckin($member->id, CheckinStatus::Denied);

                return $this->createResponse(
                    status: 'denied',
                    reason: $validationResult['reason'],
                    message: $validationResult['message'],
                    member: $member,
                    membership: $member->activeMembership
                );
            }

            // Check for rapid successive scans
            if ($this->isRapidScan($member)) {
                return $this->createResponse(
                    status: 'warning',
                    reason: 'rapid_scan',
                    message: 'Please wait before scanning again',
                    member: $member
                );
            }

            // All checks passed - grant access
            $this->logCheckin($member->id, CheckinStatus::Success);

            return $this->createResponse(
                status: 'success',
                reason: 'access_granted',
                message: "Welcome to the gym, {$member->name}!",
                member: $member,
                membership: $member->activeMembership
            );

        } catch (\Exception $e) {
            Log::error("Card validation error", [
                'card_uid' => $cardUid,
                'error' => $e->getMessage(),
            ]);

            return $this->createResponse(
                status: 'error',
                reason: 'system_error',
                message: 'System error. Please contact staff.',
                cardUid: $cardUid
            );
        }
    }

    private function validateMembership(Member $member): array
    {
        $activeMembership = $member->activeMembership;

        if (!$activeMembership) {
            return [
                'valid' => false,
                'reason' => 'no_active_membership',
                'message' => 'No active membership found'
            ];
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($activeMembership->end_date);

        if ($endDate->isPast()) {
            return [
                'valid' => false,
                'reason' => 'membership_expired',
                'message' => "Membership expired on {$endDate->format('M d, Y')}"
            ];
        }

        if ($activeMembership->status === MembershipStatus::Frozen) {
            return [
                'valid' => false,
                'reason' => 'membership_frozen',
                'message' => 'Membership is currently frozen'
            ];
        }

        return [
            'valid' => true,
            'reason' => 'active',
            'message' => 'Active membership'
        ];
    }

    private function isRapidScan(Member $member): bool
    {
        $lastCheckin = $member->lastCheckin;

        if (!$lastCheckin) {
            return false;
        }

        $timeDifference = Carbon::parse($lastCheckin->checked_in_at)->diffInSeconds(now());
        return $timeDifference < 30;
    }

    private function logCheckin(int $memberId, CheckinStatus $status): void
    {
        Checkin::create([
            'member_id' => $memberId,
            'checked_in_at' => now(),
            'status' => $status,
        ]);
    }

    private function createResponse(
        string $status,
        string $reason,
        string $message,
        Member $member = null,
               $membership = null,
        string $cardUid = null
    ): JsonResponse {
        $response = [
            'status' => $status,
            'reason' => $reason,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($member) {
            $response['member'] = [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
            ];
        }

        if ($membership) {
            $response['membership'] = [
                'id' => $membership->id,
                'type' => $membership->type,
                'start_date' => $membership->start_date->format('Y-m-d'),
                'end_date' => $membership->end_date->format('Y-m-d'),
                'status' => $membership->status->value,
                'days_remaining' => Carbon::today()->diffInDays($membership->end_date, false),
            ];
        }

        if ($cardUid) {
            $response['card_uid'] = $cardUid;
        }

        $httpStatus = match ($status) {
            'success' => 200,
            'denied' => 403,
            'warning' => 200,
            'error' => 500,
            default => 200,
        };

        return response()->json($response, $httpStatus);
    }
}
