<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Raport Financiar') }}
            </h2>
            <a href="{{ route('reports.export-financial', request()->query()) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Exportă CSV
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtre -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.financial') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Data început</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">Data sfârșit</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Filtrează
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistici Generale -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Total Facturi</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ $generalStats->total_invoices }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Valoare Totală</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ number_format($generalStats->total_value, 2) }} RON</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Valoare Plătită</h3>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($generalStats->total_paid, 2) }} RON</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Valoare Neplătită</h3>
                        <p class="text-3xl font-bold text-red-600">{{ number_format($generalStats->total_unpaid, 2) }} RON</p>
                    </div>
                </div>
            </div>

            <!-- Grafic Facturi Lunare -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Evoluție Facturi Lunare</h3>
                    <canvas id="monthlyInvoicesChart" height="100"></canvas>
                </div>
            </div>

            <!-- Facturi pe Luni -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detalii Facturi pe Luni</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lună</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Număr Facturi</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valoare Totală</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valoare Plătită</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata Încasării</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($monthlyInvoices as $month)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('F Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $month->total_invoices }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($month->total_value, 2) }} RON</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($month->total_paid, 2) }} RON</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $rate = $month->total_value > 0 ? ($month->total_paid / $month->total_value) * 100 : 0;
                                            @endphp
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $rate }}%"></div>
                                                </div>
                                                <span class="ml-2">{{ number_format($rate, 1) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Facturi Restante -->
            @if($overdueInvoices->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Facturi Restante</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Număr Factură</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Emiterii</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Scadenței</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valoare</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zile Restante</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($overdueInvoices as $invoice)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->numar_factura }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->order->customer->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->data_emitere->format('d.m.Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->data_scadenta->format('d.m.Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($invoice->total, 2) }} RON</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                {{ $invoice->data_scadenta->diffInDays(now()) }} zile
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('monthlyInvoicesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyInvoices->pluck('month')->map(function($month) {
                    return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y');
                })) !!},
                datasets: [{
                    label: 'Valoare Totală',
                    data: {!! json_encode($monthlyInvoices->pluck('total_value')) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.5)',
                    borderColor: 'rgb(79, 70, 229)',
                    borderWidth: 1
                }, {
                    label: 'Valoare Plătită',
                    data: {!! json_encode($monthlyInvoices->pluck('total_paid')) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.5)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ro-RO', {
                                    style: 'currency',
                                    currency: 'RON'
                                });
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout> 