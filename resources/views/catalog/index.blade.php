<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Catalog Produse') }}
            </h2>
            @if(auth()->user()->account_type === 'supplier')
                <a href="{{ route('catalog.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Adaugă Produs
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cod</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descriere</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producător</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Greutate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preț</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stoc</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Furnizor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->code }}</td>
                                        <td class="px-6 py-4">{{ $product->description }}</td>
                                        <td class="px-6 py-4">{{ $product->manufacturer }}</td>
                                        <td class="px-6 py-4">{{ $product->weight }} kg</td>
                                        <td class="px-6 py-4">{{ number_format($product->price, 2) }} RON</td>
                                        <td class="px-6 py-4">{{ $product->stock }}</td>
                                        <td class="px-6 py-4">{{ $product->supplier->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            @if(auth()->id() === $product->supplier_id)
                                                <a href="{{ route('catalog.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                                    Editează
                                                </a>
                                                <button type="button" 
                                                    class="text-blue-600 hover:text-blue-900"
                                                    onclick="openStockModal({{ $product->id }}, '{{ $product->code }}')">
                                                    Actualizare Stoc
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pentru actualizare stoc -->
    <div id="stockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 50;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="productTitle">Actualizare Stoc</h3>
                <div class="mt-2 px-7 py-3">
                    <form id="stockForm" method="POST" action="">
                        @csrf
                        <p class="text-sm text-gray-500 mb-4">
                            Introduceți cantitatea pe care doriți să o adăugați sau scădeți din stoc.
                            Folosiți valori pozitive pentru adăugare și negative pentru scădere.
                        </p>
                        <div class="mb-4">
                            <x-input-label for="quantity" :value="__('Ajustare Cantitate')" />
                            <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                        </div>
                        <div class="flex justify-between mt-4">
                            <x-secondary-button type="button" onclick="closeStockModal()">
                                Anulează
                            </x-secondary-button>
                            <x-primary-button type="submit">
                                Actualizează
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openStockModal(productId, productCode) {
            document.getElementById('productTitle').textContent = `Actualizare Stoc: ${productCode}`;
            document.getElementById('stockForm').action = `/products/${productId}/stock`;
            document.getElementById('quantity').value = '';
            document.getElementById('stockModal').classList.remove('hidden');
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout> 