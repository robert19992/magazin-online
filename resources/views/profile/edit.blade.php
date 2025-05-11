<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600">
                                {{ __('Ups! Au apărut următoarele erori:') }}
                            </div>

                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="company_name" :value="__('Nume companie')" />
                            <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $user->company_name)" required autofocus autocomplete="company_name" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                        </div>

                        <div>
                            <x-input-label for="street" :value="__('Stradă')" />
                            <x-text-input id="street" name="street" type="text" class="mt-1 block w-full" :value="old('street', $user->street)" required autocomplete="street" />
                            <x-input-error class="mt-2" :messages="$errors->get('street')" />
                        </div>

                        <div>
                            <x-input-label for="street_number" :value="__('Număr stradă')" />
                            <x-text-input id="street_number" name="street_number" type="text" class="mt-1 block w-full" :value="old('street_number', $user->street_number)" required autocomplete="street_number" />
                            <x-input-error class="mt-2" :messages="$errors->get('street_number')" />
                        </div>

                        <div>
                            <x-input-label for="cui" :value="__('CUI')" />
                            <x-text-input id="cui" name="cui" type="text" class="mt-1 block w-full" :value="old('cui', $user->cui)" required autocomplete="cui" />
                            <x-input-error class="mt-2" :messages="$errors->get('cui')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="connect_id" :value="__('Connect ID')" />
                            <x-text-input id="connect_id" type="text" class="mt-1 block w-full bg-gray-100" :value="$user->connect_id" disabled />
                            <p class="mt-2 text-sm text-gray-500">
                                {{ __('Acesta este ID-ul unic de conectare. Nu poate fi modificat.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Salvează') }}</x-primary-button>

                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600"
                                >{{ __('Salvat.') }}</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
