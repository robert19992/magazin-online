@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-4">Raport Comenzi</h2>

                <!-- Statistici generale -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-blue-900">Total Comenzi</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $totalOrders }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-green-900">Comenzi Active</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $activeOrders }}</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-yellow-900">Comenzi în Așteptare</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-purple-900">Comenzi Livrate</h3>
                        <p class="text-3xl font-bold text-purple-600">{{ $deliveredOrders }}</p>
                    </div>
                </div>

                <!-- Statistici valorice -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-indigo-900">Valoare Totală Comenzi</h3>
                        <p class="text-3xl font-bold text-indigo-600">{{ number_format($totalValue, 2) }} RON</p>
                    </div>
                    <div class="bg-pink-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-pink-900">Valoare Medie per Comandă</h3>
                        <p class="text-3xl font-bold text-pink-600">{{ number_format($averageValue, 2) }} RON</p>
                    </div>
                    <div class="bg-teal-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-teal-900">Perioada Activitate</h3>
                        <p class="text-lg font-bold text-teal-600">{{ $firstOrderDate }} - {{ $lastOrderDate }}</p>
                    </div>
                </div>

                <!-- Filtre -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Filtrare Comenzi</h3>
                    <form action="{{ route('orders.report') }}" method="GET" class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Toate</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>În așteptare</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Livrate</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label for="date_from" class="block text-sm font-medium text-gray-700">De la data</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="w-full md:w-auto">
                            <label for="date_to" class="block text-sm font-medium text-gray-700">Până la data</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="w-full md:w-auto flex items-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Filtrează
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Produse comandate frecvent -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Produse Comandate Frecvent</h3>
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cod Produs</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denumire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantitate Totală</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valoare Totală</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frecvență</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($topProducts as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $product->cod_produs }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->total_quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($product->total_value, 2) }} RON
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->order_count }} comenzi
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel comenzi -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Lista Comenzi</h3>
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Comandă</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Furnizor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valoare Totală</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nr. Produse</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->supplier->name ?? $order->supplier->organization->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($order->status == 'delivered') bg-green-100 text-green-800
                                            @elseif($order->status == 'active') bg-blue-100 text-blue-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($order->total_amount, 2) }} RON
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->items_count ?? $order->items->count() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">Detalii</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grafic distribuție lunară -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Distribuție Lunară Comenzi</h3>
                    <div class="bg-white p-4 rounded-lg shadow-sm h-64">
                        <!-- Aici va fi afișat graficul -->
                        <div id="monthly-chart" class="w-full h-full"></div>
                    </div>
                </div>

                <!-- Paginare -->
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthly-chart').getContext('2d');
        
        // Date din controller
        const monthlyData = @json($monthlyData);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Valoare comenzi (RON)',
                    data: monthlyData.values,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ro-RO') + ' RON';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return value.toLocaleString('ro-RO') + ' RON';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection 