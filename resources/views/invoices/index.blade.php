<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Facturi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtre -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Toate</option>
                                <option value="emisa" {{ request('status') === 'emisa' ? 'selected' : '' }}>Emisă</option>
                                <option value="platita" {{ request('status') === 'platita' ? 'selected' : '' }}>Plătită</option>
                                <option value="anulata" {{ request('status') === 'anulata' ? 'selected' : '' }}>Anulată</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="date_from" :value="__('Data început')" />
                            <x-text-input id="date_from" type="date" name="date_from" :value="request('date_from')" class="mt-1 block w-full" />
                        </div>

                        <div>
                            <x-input-label for="date_to" :value="__('Data sfârșit')" />
                            <x-text-input id="date_to" type="date" name="date_to" :value="request('date_to')" class="mt-1 block w-full" />
                        </div>

                        <div class="flex items-end">
                            <x-primary-button class="w-full justify-center">
                                {{ __('Filtrează') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista facturi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Număr Factură</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Emitere</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Scadentă</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $invoice->numar_factura }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->customer->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->data_emitere->format('d.m.Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->data_scadenta->format('d.m.Y') }}
                                            @if($invoice->isOverdue())
                                                <span class="text-red-600 text-xs">(Întârziată)</span>
                                            @elseif($invoice->status === 'emisa')
                                                <span class="text-gray-500 text-xs">({{ $invoice->getRemainingDays() }} zile rămase)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($invoice->total, 2) }} RON
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $invoice->status === 'emisa' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $invoice->status === 'platita' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $invoice->status === 'anulata' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ __(ucfirst($invoice->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ __('Vezi') }}
                                            </a>
                                            <a href="{{ route('invoices.download', $invoice) }}" class="text-green-600 hover:text-green-900">
                                                {{ __('Descarcă') }}
                                            </a>
                                            @if($invoice->status === 'emisa')
                                                <form method="POST" action="{{ route('invoices.mark-as-paid', $invoice) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                        {{ __('Marchează plătită') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('invoices.mark-as-cancelled', $invoice) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                                            onclick="return confirm('{{ __('Sigur doriți să anulați această factură?') }}')">
                                                        {{ __('Anulează') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ __('Nu există facturi care să corespundă criteriilor selectate.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 