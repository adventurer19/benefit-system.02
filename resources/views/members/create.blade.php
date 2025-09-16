<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Member Registration') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="text-green-600 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('members.store') }}" novalidate>
                    @csrf

                    <!-- Name Field-->
                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                    </div>

                    <!-- Email Field-->
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email (optional)')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                    </div>

                    <!-- Phone Field-->
                    <div class="mb-4">
                        <x-input-label for="phone" :value="__('Phone (optional)')" />
                        <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
                    </div>

                    <!-- Card UID Field-->
                    <div class="mb-4">
                        <x-input-label for="card_uid" :value="__('RFID Card UID')" />
                        <x-text-input id="card_uid" class="block mt-1 w-full" type="text" name="card_uid" required />
                        <x-input-error :messages="$errors->get('card_uid')" class="mt-2"/>
                        <p class="text-sm text-gray-500 mt-1">Scan the card with the reader to fill this field.</p>
                    </div>

                    <!-- Membership Start Field-->
                    <div class="mb-4">
                        <x-input-label for="membership_start" :value="__('Membership Start Date')" />
                        <x-text-input id="membership_start"
                                      class="block mt-1 w-full"
                                      type="date"
                                      name="membership_start"
                                      value="{{ old('membership_start', \Carbon\Carbon::today()->toDateString()) }}"
                                      required />
                        <x-input-error :messages="$errors->get('membership_start')" class="mt-2"/>
                    </div>

                    <!-- Membership End Field-->
                    <div class="mb-4">
                        <x-input-label for="membership_end" :value="__('Membership End Date')" />
                        <x-text-input id="membership_end"
                                      class="block mt-1 w-full"
                                      type="date"
                                      name="membership_end"
                                      value="{{ old('membership_end', \Carbon\Carbon::today()->addMonth()->toDateString()) }}"
                                      required />
                        <x-input-error :messages="$errors->get('membership_end')" class="mt-2"/>
                    </div>

                    <x-primary-button>
                        {{ __('Register Member & Assign Card') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const cardInput = document.getElementById('card_uid');

        cardInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
            }
        });
    </script>

</x-app-layout>
