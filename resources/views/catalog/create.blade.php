<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adaugă Produs Nou') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('catalog.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="code" :value="__('Cod Produs')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Descriere')" />
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="manufacturer" :value="__('Producător')" />
                            <x-text-input id="manufacturer" name="manufacturer" type="text" class="mt-1 block w-full" :value="old('manufacturer')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('manufacturer')" />
                        </div>

                        <div>
                            <x-input-label for="weight" :value="__('Greutate (kg)')" />
                            <x-text-input id="weight" name="weight" type="number" step="0.01" class="mt-1 block w-full" :value="old('weight')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('weight')" />
                        </div>

                        <div>
                            <x-input-label for="price" :value="__('Preț (RON)')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" :value="old('price')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>

                        <div>
                            <x-input-label for="stock" :value="__('Stoc')" />
                            <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full" :value="old('stock')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('stock')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Salvează') }}</x-primary-button>
                            <a href="{{ route('catalog.index') }}" class="text-gray-600 hover:text-gray-900">Anulează</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 