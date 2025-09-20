<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Enums\MembershipStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipActionController extends Controller
{
    /**
     * Quick renewal for expired members
     */
    public function quickRenewal(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'period' => 'required|in:1_month,3_months,6_months,12_months',
            'payment_confirmed' => 'required|boolean',
        ]);

        if (!$request->payment_confirmed) {
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation required'
            ], 400);
        }

        try {
            DB::transaction(function () use ($request) {
                $member = Member::findOrFail($request->member_id);

                $months = match ($request->period) {
                    '1_month' => 1,
                    '3_months' => 3,
                    '6_months' => 6,
                    '12_months' => 12,
                };

                // Get the latest membership
                $lastMembership = $member->memberships()
                    ->orderBy('end_date', 'desc')
                    ->first();

                $startDate = $lastMembership && $lastMembership->end_date >= Carbon::today()
                    ? $lastMembership->end_date->addDay()
                    : Carbon::today();

                $endDate = $startDate->copy()->addMonthsNoOverflow($months);

                // Create new membership
                $member->memberships()->create([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'type' => $request->period,
                    'status' => MembershipStatus::Active,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Membership renewed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error renewing membership: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send renewal notification to member
     */
    public function sendRenewalNotification(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'method' => 'required|in:email,sms,both',
        ]);

        try {
            $member = Member::findOrFail($request->member_id);

            // Here you would implement actual email/SMS sending
            // For now, we'll just log the notification
            \Log::info("Renewal notification sent to member", [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'method' => $request->getMethod(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Renewal notification sent to {$member->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Freeze a membership temporarily
     */
    public function freezeMembership(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $member = Member::findOrFail($request->member_id);
            $activeMembership = $member->activeMembership;

            if (!$activeMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active membership found'
                ], 400);
            }

            $activeMembership->update([
                'status' => MembershipStatus::Frozen
            ]);

            return response()->json([
                'success' => true,
                'message' => "Membership frozen for {$member->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error freezing membership: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unfreeze a membership
     */
    public function unfreezeMembership(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        try {
            $member = Member::findOrFail($request->member_id);

            $frozenMembership = $member->memberships()
                ->where('status', MembershipStatus::Frozen)
                ->orderBy('end_date', 'desc')
                ->first();

            if (!$frozenMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'No frozen membership found'
                ], 400);
            }

            $frozenMembership->update([
                'status' => MembershipStatus::Active
            ]);

            return response()->json([
                'success' => true,
                'message' => "Membership unfrozen for {$member->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unfreezing membership: ' . $e->getMessage()
            ], 500);
        }
    }
}
