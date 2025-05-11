<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adaugă Organizație') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('organizations.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informații Generale -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Informații Generale') }}</h3>

                                <!-- Nume -->
                                <div>
                                    <x-input-label for="name" :value="__('Nume')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <!-- CUI -->
                                <div>
                                    <x-input-label for="tax_id" :value="__('CUI')" />
                                    <x-text-input id="tax_id" name="tax_id" type="text" class="mt-1 block w-full" :value="old('tax_id')" required />
                                    <x-input-error :messages="$errors->get('tax_id')" class="mt-2" />
                                </div>

                                <!-- Număr Registrul Comerțului -->
                                <div>
                                    <x-input-label for="registration_number" :value="__('Număr Registrul Comerțului')" />
                                    <x-text-input id="registration_number" name="registration_number" type="text" class="mt-1 block w-full" :value="old('registration_number')" required />
                                    <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                                </div>

                                <!-- Tip -->
                                <div>
                                    <x-input-label for="type" :value="__('Tip')" />
                                    <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">{{ __('Selectează tipul') }}</option>
                                        <option value="supplier" {{ old('type') === 'supplier' ? 'selected' : '' }}>{{ __('Furnizor') }}</option>
                                        <option value="customer" {{ old('type') === 'customer' ? 'selected' : '' }}>{{ __('Client') }}</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Contact') }}</h3>

                                <!-- Email -->
                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Telefon -->
                                <div>
                                    <x-input-label for="phone" :value="__('Telefon')" />
                                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" required />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>

                                <!-- Website -->
                                <div>
                                    <x-input-label for="website" :value="__('Website')" />
                                    <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website')" />
                                    <x-input-error :messages="$errors->get('website')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Adresă -->
                        <div class="mt-8 space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Adresă') }}</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Adresă -->
                                <div>
                                    <x-input-label for="address" :value="__('Adresă')" />
                                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" required />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <!-- Oraș -->
                                <div>
                                    <x-input-label for="city" :value="__('Oraș')" />
                                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city')" required />
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>

                                <!-- Județ -->
                                <div>
                                    <x-input-label for="county" :value="__('Județ')" />
                                    <x-text-input id="county" name="county" type="text" class="mt-1 block w-full" :value="old('county')" required />
                                    <x-input-error :messages="$errors->get('county')" class="mt-2" />
                                </div>

                                <!-- Cod Poștal -->
                                <div>
                                    <x-input-label for="postal_code" :value="__('Cod Poștal')" />
                                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code')" required />
                                    <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                                </div>

                                <!-- Țară -->
                                <div>
                                    <x-input-label for="country" :value="__('Țară')" />
                                    <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', 'România')" required />
                                    <x-input-error :messages="$errors->get('country')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Setări -->
                        <div class="mt-8 space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Setări') }}</h3>

                            <div class="space-y-4">
                                <!-- Status -->
                                <div>
                                    <label for="is_active" class="inline-flex items-center">
                                        <input id="is_active" type="checkbox" name="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <span class="ms-2 text-sm text-gray-600">{{ __('Activ') }}</span>
                                    </label>
                                </div>

                                <!-- Monedă Implicită -->
                                <div>
                                    <x-input-label for="default_currency" :value="__('Monedă Implicită')" />
                                    <select id="default_currency" name="default_currency" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="RON" {{ old('default_currency') === 'RON' ? 'selected' : '' }}>RON</option>
                                        <option value="EUR" {{ old('default_currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                                        <option value="USD" {{ old('default_currency') === 'USD' ? 'selected' : '' }}>USD</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('default_currency')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Salvează') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 