<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Factură') }} #{{ $invoice->numar_factura }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    {{ __('Descarcă PDF') }}
                </a>
                @if($invoice->status === 'emisa')
                    <form method="POST" action="{{ route('invoices.mark-as-paid', $invoice) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <x-primary-button>
                            {{ __('Marchează ca plătită') }}
                        </x-primary-button>
                    </form>
                    <form method="POST" action="{{ route('invoices.mark-as-cancelled', $invoice) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <x-danger-button onclick="return confirm('{{ __('Sigur doriți să anulați această factură?') }}')">
                            {{ __('Anulează') }}
                        </x-danger-button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informații Furnizor -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Furnizor') }}</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600"><strong>Nume:</strong> {{ $invoice->supplier->name }}</p>
                                <p class="text-sm text-gray-600"><strong>Email:</strong> {{ $invoice->supplier->email }}</p>
                                <!-- Adăugați aici alte detalii despre furnizor -->
                            </div>
                        </div>

                        <!-- Informații Client -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Client') }}</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600"><strong>Nume:</strong> {{ $invoice->customer->name }}</p>
                                <p class="text-sm text-gray-600"><strong>Email:</strong> {{ $invoice->customer->email }}</p>
                                <p class="text-sm text-gray-600"><strong>Adresă:</strong> {{ $invoice->order->adresa_livrare }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informații Factură -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Detalii Factură') }}</h3>
                        <div class="bg-gray-50 rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600"><strong>Număr Factură:</strong> {{ $invoice->numar_factura }}</p>
                                <p class="text-sm text-gray-600"><strong>Data Emitere:</strong> {{ $invoice->data_emitere->format('d.m.Y') }}</p>
                                <p class="text-sm text-gray-600"><strong>Data Scadentă:</strong> {{ $invoice->data_scadenta->format('d.m.Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    <strong>Status:</strong>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $invoice->status === 'emisa' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $invoice->status === 'platita' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $invoice->status === 'anulata' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ __(ucfirst($invoice->status)) }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-600"><strong>Număr Comandă:</strong> {{ $invoice->order->numar_comanda }}</p>
                            </div>
                            @if($invoice->mentiuni)
                                <div>
                                    <p class="text-sm text-gray-600"><strong>Mențiuni:</strong> {{ $invoice->mentiuni }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produse Facturate -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Produse Facturate') }}</h3>
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
                                @foreach($invoice->order->items as $item)
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
                                        {{ number_format($invoice->subtotal, 2) }} RON
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        TVA (19%):
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($invoice->tva, 2) }} RON
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        Total:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($invoice->total, 2) }} RON
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 