<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Emitere Factură pentru Comanda') }} #{{ $order->numar_comanda }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('invoices.store', $order) }}" class="space-y-6">
                        @csrf

                        <!-- Detalii Comandă -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Detalii Comandă') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600"><strong>Client:</strong> {{ $order->customer->name }}</p>
                                    <p class="text-sm text-gray-600"><strong>Adresă Livrare:</strong> {{ $order->adresa_livrare }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600"><strong>Data Comandă:</strong> {{ $order->created_at->format('d.m.Y') }}</p>
                                    <p class="text-sm text-gray-600"><strong>Total Comandă:</strong> {{ number_format($order->total, 2) }} RON</p>
                                </div>
                            </div>
                        </div>

                        <!-- Data Scadentă -->
                        <div>
                            <x-input-label for="data_scadenta" :value="__('Data Scadentă')" />
                            <x-text-input id="data_scadenta" 
                                         type="date" 
                                         name="data_scadenta" 
                                         :value="old('data_scadenta', now()->addDays(30)->format('Y-m-d'))"
                                         class="mt-1 block w-full" 
                                         required />
                            <x-input-error :messages="$errors->get('data_scadenta')" class="mt-2" />
                        </div>

                        <!-- Mențiuni -->
                        <div>
                            <x-input-label for="mentiuni" :value="__('Mențiuni')" />
                            <textarea id="mentiuni" 
                                      name="mentiuni"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      rows="3">{{ old('mentiuni') }}</textarea>
                            <x-input-error :messages="$errors->get('mentiuni')" class="mt-2" />
                        </div>

                        <!-- Produse -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Produse') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cod Produs</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descriere</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantitate</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preț Unitar</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $item->product->cod_produs }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $item->product->descriere }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $item->cantitate }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ number_format($item->pret_unitar, 2) }} RON
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ number_format($item->subtotal, 2) }} RON
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                Subtotal:
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($order->total, 2) }} RON
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                TVA (19%):
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($order->total * 0.19, 2) }} RON
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                Total:
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($order->total * 1.19, 2) }} RON
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button onclick="window.history.back()" type="button" class="mr-4">
                                {{ __('Anulează') }}
                            </x-secondary-button>
                            
                            <x-primary-button>
                                {{ __('Emite Factură') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 