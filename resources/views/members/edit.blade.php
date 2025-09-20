<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Member') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('members.update', $member->id) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $member->name) }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email (optional)')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{ old('email', $member->email) }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <x-input-label for="phone" :value="__('Phone (optional)')" />
                        <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" value="{{ old('phone', $member->phone) }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
                    </div>

                    <!-- Card UID -->
                    <div class="mb-4">
                        <x-input-label for="card_uid" :value="__('RFID Card UID')" />
                        <x-text-input id="card_uid" class="block mt-1 w-full" type="text" name="card_uid" value="{{ old('card_uid', $member->card_uid) }}" required />
                        <x-input-error :messages="$errors->get('card_uid')" class="mt-2"/>
                    </div>

                    <!-- Membership Start -->
                    <div class="mb-4">
                        <x-input-label for="membership_start" :value="__('Membership Start Date')" />
                        <x-text-input id="membership_start" class="block mt-1 w-full" type="date" name="membership_start" value="{{ old('membership_start', $member->memberships()->latest('start_date')->first()->start_date->toDateString()) }}" required />
                        <x-input-error :messages="$errors->get('membership_start')" class="mt-2"/>
                    </div>

                    <!-- Membership End -->
                    <div class="mb-4">
                        <x-input-label for="membership_end" :value="__('Membership End Date')" />
                        <x-text-input id="membership_end" class="block mt-1 w-full" type="date" name="membership_end" value="{{ old('membership_end', $member->memberships()->latest('start_date')->first()->end_date->toDateString()) }}" required />
                        <x-input-error :messages="$errors->get('membership_end')" class="mt-2"/>
                    </div>

                    <x-primary-button>
                        {{ __('Update Member') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
