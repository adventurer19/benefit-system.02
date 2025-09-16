<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Memberships') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="text-green-600 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

                <div class="mb-4 flex items-center">
                    <form method="GET" action="{{ route('memberships.index') }}" class="flex items-center space-x-1">
                        <!-- Search -->
                        <input type="text" name="search"
                               placeholder="Search by name | card number"
                               value="{{ request('search') }}"
                               class="flex-1 border rounded px-3 py-2">

                        <!-- Filter -->
                        <select name="status" class="border rounded py-2">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>

                        <div class="flex items-center">
                        <!-- Submit -->
                        <button type="submit" class="bg-indigo-600 text-gray-600 px-3 py-2 rounded hover:bg-indigo-700">
                            Apply
                        </button>

                        <!-- Clear -->
                        <a href="{{ route('memberships.index') }}"
                           class="bg-indigo-600 text-gray-600 hover:bg-indigo-700">
                            Reset
                        </a>
                        </div>
                    </form>
                </div>

            <div class="bg-white shadow sm:rounded-lg p-6 overflow-x-auto">

                <table class="min-w-full w-full table-auto">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Member</th>
                        <th class="px-4 py-2 text-left">RFID Card UID</th>
                        <th class="px-4 py-2 text-left">Subscription Start Date</th>
                        <th class="px-4 py-2 text-left">Subscription End Date</th>
                        <th class="px-4 py-2 text-left">Renew</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($memberships as $membership)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $membership->member->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $membership->member->card_uid ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($membership->start_date)->toFormattedDateString() }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($membership->end_date)->toFormattedDateString() }}</td>
                            <td class="px-4 py-2">
                                <!-- Renew Form -->
                                <form action="{{ route('memberships.renew', $membership->id) }}" method="POST">
                                    @csrf
                                    <select name="period">
                                        <option value="1_month">+1 Month</option>
                                        <option value="6_months">+6 Months</option>
                                        <option value="12_months">+1 Year</option>
                                    </select>
                                    <button type="submit" class="ml-2 text-indigo-600 font-medium">
                                        Renew
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-2 font-medium">
                                @if($membership->status === 'active' && \Carbon\Carbon::today()->gt($membership->end_date))
                                    <span class="text-red-600">Expired</span>
                                @else
                                    <span class="text-green-600">Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 font-medium">
                                <div class="flex space-x-1">
                                    <a href="{{ route('members.edit', $membership->id) }}" class="text-indigo-600">
                                        Edit |
                                    </a>

                                    <form action="{{ route('memberships.destroy', $membership->id) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this membership?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($memberships->isEmpty())
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                                No memberships found.
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
