<div>
    <div class="space-y-6">
        <!-- Tip Comandă -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" name="order_type" value="order" wire:model="orderType">
                    <span class="ml-2">{{ __('Comandă') }}</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" name="order_type" value="quote" wire:model="orderType">
                    <span class="ml-2">{{ __('Cerere de Ofertă') }}</span>
                </label>
            </div>
        </div>

        <!-- Selectare Furnizor -->
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Selectează Furnizorul') }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($suppliers as $supplier)
                    <div class="border rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors duration-200 
                        {{ $selectedSupplier == $supplier->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}"
                        wire:click="selectSupplier({{ $supplier->id }})">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $supplier->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $supplier->city }}, {{ $supplier->country }}</p>
                            </div>
                            @if($selectedSupplier == $supplier->id)
                                <svg class="h-5 w-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($selectedSupplier)
            <!-- Detalii Livrare -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Detalii Livrare') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="shippingAddress" :value="__('Adresă Livrare')" />
                        <x-text-input id="shippingAddress" class="block mt-1 w-full" type="text" wire:model="shippingAddress" required />
                        <x-input-error :messages="$errors->get('shippingAddress')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="shippingCity" :value="__('Oraș')" />
                        <x-text-input id="shippingCity" class="block mt-1 w-full" type="text" wire:model="shippingCity" required />
                        <x-input-error :messages="$errors->get('shippingCity')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="shippingPostalCode" :value="__('Cod Poștal')" />
                        <x-text-input id="shippingPostalCode" class="block mt-1 w-full" type="text" wire:model="shippingPostalCode" required />
                        <x-input-error :messages="$errors->get('shippingPostalCode')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="shippingCountry" :value="__('Țară')" />
                        <select id="shippingCountry" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="shippingCountry" required>
                            <option value="">{{ __('Selectează țara') }}</option>
                            @foreach($countries as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('shippingCountry')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="requestedDeliveryDate" :value="__('Data Livrare Dorită')" />
                        <x-text-input id="requestedDeliveryDate" class="block mt-1 w-full" type="date" wire:model="requestedDeliveryDate" />
                        <x-input-error :messages="$errors->get('requestedDeliveryDate')" class="mt-2" />
                    </div>

                    <div>
                        <label class="inline-flex items-center mt-6">
                            <input type="checkbox" class="form-checkbox" wire:model="allowPartialDelivery">
                            <span class="ml-2">{{ __('Permite livrare parțială') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Listă Produse -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="mb-4">
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <x-input-label for="search" :value="__('Caută Produse')" />
                            <x-text-input 
                                id="search" 
                                type="text"
                                class="mt-1 block w-full" 
                                wire:model.debounce.300ms="search"
                                placeholder="{{ __('Cod produs, descriere, producător...') }}" />
                        </div>
                        <div class="w-48">
                            <x-input-label for="manufacturer" :value="__('Producător')" />
                            <select 
                                id="manufacturer" 
                                wire:model="selectedManufacturer"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Toți producătorii') }}</option>
                                @foreach($manufacturers as $manufacturer)
                                    <option value="{{ $manufacturer }}">{{ $manufacturer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-48">
                            <x-input-label for="sort" :value="__('Sortare')" />
                            <select 
                                id="sort" 
                                wire:model="sortBy"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="code">{{ __('Cod') }}</option>
                                <option value="price">{{ __('Preț') }}</option>
                                <option value="manufacturer">{{ __('Producător') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Cod') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Descriere') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Producător') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Preț') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Cantitate') }}
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">{{ __('Acțiuni') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $product->code }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $product->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->manufacturer }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($product->price, 2) }} {{ $currency }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <x-text-input 
                                            type="number"
                                            min="1"
                                            class="w-20"
                                            wire:model="quantities.{{ $product->id }}" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button 
                                            type="button"
                                            wire:click="addToOrder({{ $product->id }})"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            {{ __('Adaugă') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        {{ __('Nu au fost găsite produse care să corespundă criteriilor de căutare.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>

            <!-- Sumar Comandă -->
            @if($orderItems->isNotEmpty())
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Sumar Comandă') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Produs') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Cantitate') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Preț Unitar') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Total') }}
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">{{ __('Acțiuni') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orderItems as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->code }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->product->description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <x-text-input 
                                                type="number"
                                                min="1"
                                                class="w-20"
                                                wire:model="quantities.{{ $item->product_id }}"
                                                wire:change="updateQuantity({{ $item->product_id }}, $event.target.value)" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($item->unit_price, 2) }} {{ $currency }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($item->total_price, 2) }} {{ $currency }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button 
                                                type="button"
                                                wire:click="removeItem({{ $item->product_id }})"
                                                class="text-red-600 hover:text-red-900">
                                                {{ __('Șterge') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        {{ __('Total Comandă:') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($total, 2) }} {{ $currency }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <x-secondary-button wire:click="saveAsDraft">
                            {{ __('Salvează ca Ciornă') }}
                        </x-secondary-button>
                        <x-primary-button wire:click="submit">
                            {{ __('Trimite Comanda') }}
                        </x-primary-button>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div> 