<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(auth()->user()->isSupplier())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <a href="{{ route('products.index') }}" class="block p-6 bg-blue-100 rounded-lg hover:bg-blue-200">
                                <h3 class="text-lg font-semibold mb-2">Produse</h3>
                                <p>Gestionează produsele tale</p>
                            </a>
                            
                            <a href="{{ route('orders.index') }}" class="block p-6 bg-green-100 rounded-lg hover:bg-green-200">
                                <h3 class="text-lg font-semibold mb-2">Comenzi</h3>
                                <p>Vezi și gestionează comenzile</p>
                            </a>
                            
                            <a href="{{ route('connections.index') }}" class="block p-6 bg-purple-100 rounded-lg hover:bg-purple-200">
                                <h3 class="text-lg font-semibold mb-2">Conexiuni</h3>
                                <p>Gestionează conexiunile cu clienții</p>
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="{{ route('orders.index') }}" class="block p-6 bg-green-100 rounded-lg hover:bg-green-200">
                                <h3 class="text-lg font-semibold mb-2">Comenzi</h3>
                                <p>Vezi comenzile tale</p>
                            </a>
                            <a href="{{ route('connections.index') }}" class="block p-6 bg-purple-100 rounded-lg hover:bg-purple-200">
                                <h3 class="text-lg font-semibold mb-2">Conexiuni</h3>
                                <p>Gestionează conexiunile cu furnizorii</p>
                            </a>
                            <a href="{{ route('orders.report') }}" class="block p-6 bg-cyan-100 rounded-lg hover:bg-cyan-200">
                                <h3 class="text-lg font-semibold mb-2">Raport comenzi</h3>
                                <p>Vezi statistici și detalii despre toate comenzile tale</p>
                            </a>
                            <a href="{{ route('orders.create') }}" class="block p-6 bg-yellow-100 rounded-lg hover:bg-yellow-200">
                                <h3 class="text-lg font-semibold mb-2">Plasează comandă</h3>
                                <p>Plasează o comandă nouă către furnizorii tăi</p>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
