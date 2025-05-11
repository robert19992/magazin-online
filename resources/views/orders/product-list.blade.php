@props(['products'])

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