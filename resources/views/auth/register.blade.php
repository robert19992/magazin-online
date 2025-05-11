<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nume')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Tip cont')" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="client">Client</option>
                <option value="supplier">Furnizor</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Company Name -->
        <div class="mt-4">
            <x-input-label for="company_name" :value="__('Nume companie')" />
            <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" required />
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>

        <!-- Street -->
        <div class="mt-4">
            <x-input-label for="street" :value="__('Strada')" />
            <x-text-input id="street" class="block mt-1 w-full" type="text" name="street" :value="old('street')" required />
            <x-input-error :messages="$errors->get('street')" class="mt-2" />
        </div>

        <!-- Street Number -->
        <div class="mt-4">
            <x-input-label for="street_number" :value="__('Număr')" />
            <x-text-input id="street_number" class="block mt-1 w-full" type="text" name="street_number" :value="old('street_number')" required />
            <x-input-error :messages="$errors->get('street_number')" class="mt-2" />
        </div>

        <!-- CUI -->
        <div class="mt-4">
            <x-input-label for="cui" :value="__('CUI')" />
            <x-text-input id="cui" class="block mt-1 w-full" type="text" name="cui" :value="old('cui')" required />
            <x-input-error :messages="$errors->get('cui')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Parolă')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmă parola')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Ai deja cont?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Înregistrare') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
