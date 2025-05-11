@props(['items', 'total', 'currency'])

<div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Sumar Comandă') }}</h3>

    @if(count($items) > 0)
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
                    @foreach($items as $item)
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
    @else
        <div class="text-center text-gray-500 py-4">
            {{ __('Nu există produse în comandă.') }}
        </div>
    @endif

    <div class="mt-6 flex justify-end space-x-4">
        <x-secondary-button wire:click="saveAsDraft">
            {{ __('Salvează ca Ciornă') }}
        </x-secondary-button>
        <x-primary-button wire:click="submit">
            {{ __('Trimite Comanda') }}
        </x-primary-button>
    </div>
</div> 