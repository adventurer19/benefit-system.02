<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Http\Requests\RenewMembershipRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{

    /**
     * Render the form to create a new member.
     */
    public function create()
    {
        return view('members.create');
    }

    /**
     * Handle form submission and assign card
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $member = Member::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'card_uid' => $data['card_uid'],
            ]);

        $member->memberships()->create([
            'start_date' => Carbon::parse($data['membership_start'])->toDateString(),
            'end_date' => Carbon::parse($data['membership_end'])->toDateString(),
            'type' => $data['membership_type'] ?? null,
            'status' => MembershipStatus::Active, // enum cast
            ]);
        });

        return redirect()->route('members.create')
            ->with('success', 'Member created and card assigned successfully!');
    }

    public function edit(Member $member)
    {
        $member->load('activeMembership');

        return view('members.edit', compact('member'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $member) {
            $member->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'card_uid' => $data['card_uid'],
            ]);

            $start = Carbon::parse($data['membership_start'])->toDateString();
            $end = Carbon::parse($data['membership_end'])->toDateString();

            $target = $member->activeMembership ?: $member->memberships()->orderByDesc('end_date')->first();

            if ($target) {
                $overlap = $member->memberships()
                    ->where('id', '!=', $target->id)
                    ->where(function ($query) use ($start, $end) {
                        $query->whereBetween('start_date', [$start, $end])
                            ->orwhereBetween('end_date', [$start, $end])
                            ->orwhere(function ($query2) use ($start, $end) {
                                $query2->where('start_date', '<=', $start)
                                    ->where('end_date', '>=', $end);
                            });
                    })
                    ->whereIn('status', [MembershipStatus::Active->value, MembershipStatus::Frozen->value])
                    ->exists();

                if ($overlap) {
                    abort(422, 'This update creates an overlapping active/frozen membership period.');
                }

                $target->update([
                    'start_date' => $start,
                    'end_date' => $end,
                    'type' => $data['membership_type'] ?? $target->type,
                    'status' => isset($data['membership_status'])
                    ? MembershipStatus::from($data['membership_status']) : $target->status,
                ]);
            } else {
                $member->memberships()->create([
                    'start_date' => $start,
                    'end_date' => $end,
                    'type' => $data['membership_type'] ?? null,
                    'status' => MembershipStatus::Active
                ]);
            }
        });
        return redirect()->route('memberships.index')
            ->with('success', 'Member updated successfully!');
    }

    public function renew(RenewMembershipRequest $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $months = match ($request->validated()['period']) {
            '1_month' => 1,
            '6_months' => 6,
            '12_months' => 12,
        };

        $baseEnd = Carbon::parse($membership->end_date);
        $startFrom = $baseEnd->isFuture() || $baseEnd->isSameDay(Carbon::today()) ? $baseEnd : Carbon::today();

        $newEnd = $startFrom->clone()->addMonthsNoOverflow($months)->toDateString();
        $membership->update([
            'end_date' => $newEnd,
            'status' => MembershipStatus::Active,
        ]);

        return redirect()->route('memberships.index')
            ->with('success', 'Member renewed successfully!');
    }

    /**
     * List memberships with optional search and status filters.
     * [On Progress]
     */
    public function index(Request $request)
    {
      $perPage = $request->integer('per_page', 15);
      $today = now()->toDateString();

      $query = Membership::with('member')->orderBy('end_date', 'desc');

      if ($request->filled('search')) {
          $term = trim((string)$request->input('search'));
          $query->whereHas('member', function ($query) use ($term) {
              $query->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('card_uid', 'LIKE', "%{$term}%");
          });
      }

      if ($request->filled('status') && request()->status !== 'all') {
          if ($request->status === 'expired') {
              $query->where('end_date', '<', $today);
          } elseif ($request->status === 'active') {
              $query->where('start_date', '<=', $today)
                  ->where('end_date', '>=', $today)
                  ->where('status', MembershipStatus::Active);
          }
      }

      $memberships = $query->paginate($perPage)->appends($request->query());

      return view('members.index', compact('memberships'));
    }

    /**
     * Delete a member record. Cascading deletes for memberships depend on FK settings.
     * Redirects back to the memberships index with a success message.
     */
    public function destroy($id)
    {
        Member::findOrFail($id)->delete();

        return redirect()
            ->route('memberships.index')
            ->with('success', 'Member deleted successfully!');
    }
}
