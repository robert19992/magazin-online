<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editează produs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cod produs -->
                        <div>
                                <x-input-label for="part_number" :value="__('Cod produs')" />
                                <x-text-input id="part_number" name="part_number" type="text" class="mt-1 block w-full" :value="old('part_number', $product->part_number)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('part_number')" />
                        </div>

                        <!-- Descriere -->
                        <div>
                                <x-input-label for="description" :value="__('Descriere')" />
                                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $product->description)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                            <!-- Producător -->
                        <div>
                                <x-input-label for="manufacturer" :value="__('Producător')" />
                                <x-text-input id="manufacturer" name="manufacturer" type="text" class="mt-1 block w-full" :value="old('manufacturer', $product->manufacturer)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('manufacturer')" />
                        </div>

                        <!-- Greutate -->
                        <div>
                                <x-input-label for="weight" :value="__('Greutate (kg)')" />
                                <x-text-input id="weight" name="weight" type="number" step="0.01" class="mt-1 block w-full" :value="old('weight', $product->weight)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('weight')" />
                        </div>

                        <!-- Preț -->
                        <div>
                                <x-input-label for="price" :value="__('Preț (RON)')" />
                                <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" :value="old('price', $product->price)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>

                        <!-- Stoc -->
                        <div>
                                <x-input-label for="stock" :value="__('Stoc')" />
                                <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full" :value="old('stock', $product->stock)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('stock')" />
                            </div>

                            <!-- Categorie -->
                            <div>
                                <x-input-label for="category" :value="__('Categorie')" />
                                <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category', $product->category)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('category')" />
                            </div>

                            <!-- Specificații -->
                            <div>
                                <x-input-label for="specifications" :value="__('Specificații')" />
                                <textarea id="specifications" name="specifications" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('specifications', $product->specifications) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('specifications')" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="is_active" :value="__('Status')" />
                                <select id="is_active" name="is_active" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="1" {{ old('is_active', $product->is_active) ? 'selected' : '' }}>Activ</option>
                                    <option value="0" {{ old('is_active', $product->is_active) ? '' : 'selected' }}>Inactiv</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Salvează modificările') }}</x-primary-button>
                            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Anulează') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 