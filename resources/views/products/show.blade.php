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
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Înapoi la Listă
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 