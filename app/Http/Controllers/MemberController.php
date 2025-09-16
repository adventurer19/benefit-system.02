<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Membership;

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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'card_uid' => 'required|string|unique:members,card_uid',
            'membership_start' => 'required|date',
            'membership_end' => 'required|date|after_or_equal:membership_start',
        ]);

        // Create the member.
        $member = Member::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'card_uid' => $request->card_uid,
        ]);

        // Create his membership.
        $member->memberships()->create([
            'start_date' => Carbon::parse($request->membership_start),
            'end_date' => Carbon::parse($request->membership_end),
            'status' => 'active',
        ]);

        return redirect()->route('members.create')
            ->with('success', 'Member created and card assigned successfully!');
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'card_uid' => 'required|string|unique:members,card_uid,' . $member->id,
            'membership_start' => 'required|date',
            'membership_end' => 'required|date|after_or_equal:membership_start',
        ]);

        $member->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'card_uid' => $request->card_uid,
        ]);

        $member->memberships()->update([
            'start_date' => Carbon::parse($request->membership_start),
            'end_date' => Carbon::parse($request->membership_end),
        ]);

        return redirect()->route('memberships.index')->with('success', 'Member updated successfully!');
    }

    public function renew($id, Request $request)
    {
        $membership = Membership::findOrFail($id);

        $request->validate([
            'period' => 'required|in:1_month,6_months,12_months',
        ]);

        $extension = match (request('period')) {
            '1_month' => Carbon::parse($membership->end_date)->addMonth(),
            '6_months' => Carbon::parse($membership->end_date)->addMonths(6),
            '12_months' => Carbon::parse($membership->end_date)->addYear(),
        };

        $membership->update([
            'end_date' => $extension,
            'status' => 'active',
        ]);

        return redirect()->route('memberships.index')->with('success', 'Member renewed successfully!');
    }

    /**
     * Render the memberships ordered by it`s last.
     */
    public function index(Request $request)
    {
        // Start building the memberships query.
        $query = Membership::with('member')->orderby('end_date', 'desc');

        // Apply search filter if "search" is provided.
        // This checks both the member`s name and their card UID.
        if (request()->filled('search')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('card_uid', 'like', '%' . $request->search . '%');
            });
        }

        // Apply status filter if provided (active/expired).
        if ($request->filled('status') && $request->status != 'all') {
            if ($request->status === 'expired') {
                $query->where('end_date', '<', Carbon::now());
            } elseif ($request->status === 'active') {
                $query->where('end_date', '>', Carbon::now());
            }
        }

        // Execute the query and fetch the results.
        $memberships = $query->paginate();

        return view('members.index', compact('memberships'));
    }

    // Delete the member and his membership
    public function destroy($id)
    {
        Member::findOrFail($id)->delete();

        return redirect()->route('memberships.index')->with('success', 'Member deleted successfully!');
    }
}
