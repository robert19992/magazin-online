@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-4">Raport Comenzi</h2>

                <!-- Filtre Perioadă -->
                <div class="mb-6">
                    <form action="{{ route('reports.orders') }}" method="GET" class="flex gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Data Început</label>
                            <input type="date" name="start_date" id="start_date" 
                                value="{{ request('start_date') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">Data Sfârșit</label>
                            <input type="date" name="end_date" id="end_date" 
                                value="{{ request('end_date') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Filtrează
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statistici Generale -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-blue-900">Total Comenzi</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $totalOrders }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-green-900">Valoare Totală</h3>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($totalValue, 2) }} RON</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-purple-900">Comenzi Livrate</h3>
                        <p class="text-3xl font-bold text-purple-600">{{ $deliveredOrders }}</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-yellow-900">Comenzi în Așteptare</h3>
                        <p class="text-3xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                    </div>
                </div>

                <!-- Grafice -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Grafic Comenzi pe Lună -->
                    <div class="bg-white border rounded-lg p-4">
                        <h3 class="text-lg font-medium mb-4">Comenzi pe Lună</h3>
                        <canvas id="ordersByMonth"></canvas>
                    </div>

                    <!-- Grafic Valoare pe Furnizor -->
                    <div class="bg-white border rounded-lg p-4">
                        <h3 class="text-lg font-medium mb-4">Valoare pe Furnizor</h3>
                        <canvas id="valueBySupplier"></canvas>
                    </div>
                </div>

                <!-- Tabel Detalii Comenzi -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lună
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nr. Comenzi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valoare Totală
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Comenzi Livrate
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valoare Medie/Comandă
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($monthlyStats as $stat)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $stat->month }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->total_orders }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($stat->total_value, 2) }} RON
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->delivered_orders }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($stat->average_value, 2) }} RON
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafic Comenzi pe Lună
    const ordersCtx = document.getElementById('ordersByMonth').getContext('2d');
    new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyStats->pluck('month')) !!},
            datasets: [{
                label: 'Număr Comenzi',
                data: {!! json_encode($monthlyStats->pluck('total_orders')) !!},
                borderColor: 'rgb(59, 130, 246)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Grafic Valoare pe Furnizor
    const valueCtx = document.getElementById('valueBySupplier').getContext('2d');
    new Chart(valueCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($supplierStats->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($supplierStats->pluck('total_value')) !!},
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(139, 92, 246)',
                    'rgb(236, 72, 153)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
});
</script>
@endpush
@endsection 