<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalii Produs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium mb-4">Informații Produs</h3>
                            <dl class="grid grid-cols-2 gap-4">
                                <dt class="font-medium">Cod Produs:</dt>
                                <dd>{{ $product->code }}</dd>
                                
                                <dt class="font-medium">Descriere:</dt>
                                <dd>{{ $product->description }}</dd>
                                
                                <dt class="font-medium">Producător Mașină:</dt>
                                <dd>{{ $product->car_manufacturer }}</dd>
                                
                                <dt class="font-medium">Greutate (kg):</dt>
                                <dd>{{ $product->weight }}</dd>
                                
                                <dt class="font-medium">Preț:</dt>
                                <dd>{{ number_format($product->price, 2) }} RON</dd>
                                
                                <dt class="font-medium">Stoc:</dt>
                                <dd>{{ $product->stock }}</dd>
                                
                                <dt class="font-medium">Data Introducerii:</dt>
                                <dd>{{ $product->created_at->format('d.m.Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Editare
                        </a>
                        <button type="button" 
                            onclick="openStockModal({{ $product->id }}, '{{ $product->code }}')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Actualizare Stoc
                        </button>
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Înapoi la Listă
                        </a>
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