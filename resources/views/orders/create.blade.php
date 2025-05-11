<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Comandă nouă') }}
        </h2>
    </x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
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

                    <form method="POST" action="{{ route('orders.store') }}" class="space-y-6">
                    @csrf
                    
                        <!-- Selectare furnizor -->
                    <div>
                            <x-input-label for="supplier_id" :value="__('Furnizor')" />
                            <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Selectează furnizor</option>
                            @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->company_name }}
                                    </option>
                            @endforeach
                        </select>
                            <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
                    </div>

                    <!-- Produse -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Produse') }}</h3>
                            
                    <div id="products-container">
                                <div class="product-row grid grid-cols-12 gap-4 items-end mb-4">
                                    <div class="col-span-5">
                                        <x-input-label for="product_id_0" :value="__('Produs')" />
                                        <select name="items[0][product_id]" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option value="">Selectează produs</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                    {{ $product->part_number }} - {{ $product->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-3">
                                        <x-input-label for="quantity_0" :value="__('Cantitate')" />
                                        <x-text-input id="quantity_0" name="items[0][quantity]" type="number" min="1" class="mt-1 block w-full" required />
                                    </div>
                                    <div class="col-span-3">
                                        <x-input-label :value="__('Preț unitar')" />
                                        <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-500">
                                            <span class="price-display">0.00</span> RON
                                </div>
                                </div>
                                    <div class="col-span-1">
                                        <button type="button" class="remove-product text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                    </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="add-product" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Adaugă produs') }}
                            </button>
                    </div>

                        <!-- Note -->
                        <div>
                            <x-input-label for="notes" :value="__('Note')" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('notes') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Plasează comanda') }}</x-primary-button>
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Anulează') }}
                            </a>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('products-container');
    const addButton = document.getElementById('add-product');
    let productCount = 1;

            // Adaugă produs nou
    addButton.addEventListener('click', function() {
                const template = container.querySelector('.product-row').cloneNode(true);
                template.querySelectorAll('select, input').forEach(input => {
            input.value = '';
                    input.name = input.name.replace('[0]', `[${productCount}]`);
                    input.id = input.id.replace('_0', `_${productCount}`);
        });
                template.querySelector('.price-display').textContent = '0.00';
        container.appendChild(template);
        productCount++;
    });

            // Șterge produs
    container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-product')) {
                    if (container.querySelectorAll('.product-row').length > 1) {
                        e.target.closest('.product-row').remove();
            }
        }
    });

            // Actualizează prețul când se selectează un produs
            container.addEventListener('change', function(e) {
                if (e.target.tagName === 'SELECT' && e.target.name.includes('[product_id]')) {
                    const option = e.target.options[e.target.selectedIndex];
                    const price = option.dataset.price || '0.00';
                    const priceDisplay = e.target.closest('.product-row').querySelector('.price-display');
                    priceDisplay.textContent = parseFloat(price).toFixed(2);
                }
            });
});
</script>
@endpush
</x-app-layout> 