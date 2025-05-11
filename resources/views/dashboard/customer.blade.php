<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 1. Comandă/Cerere Ofertă -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">1. Comandă/Cerere Ofertă</h3>
                        <p class="text-gray-600 mb-4">Plasați comenzi sau solicitați oferte de la furnizorii selectați.</p>
                        <div class="space-y-2">
                            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Plasează Comandă
                            </a>
                            <a href="{{ route('orders.quote') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Cerere Ofertă
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Comenzi Active -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">2. Comenzi Active</h3>
                        <p class="text-gray-600 mb-4">Urmăriți statusul comenzilor și verificați livrările.</p>
                        <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                            Vezi Comenzile
                        </a>
                    </div>
                </div>

                <!-- 3. Raport Comenzi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">3. Raport Comenzi</h3>
                        <p class="text-gray-600 mb-4">Vizualizați statistici și rapoarte pentru comenzile plasate.</p>
                        <a href="{{ route('reports.orders') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                            Vezi Rapoarte
                        </a>
                    </div>
                </div>

                <!-- 4. Arhivă Documente -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">4. Arhivă Documente</h3>
                        <p class="text-gray-600 mb-4">Accesați avizele de livrare și facturile primite.</p>
                        <a href="{{ route('documents.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Vezi Documente
                        </a>
                    </div>
                </div>

                <!-- 5. Connect -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">5. Connect</h3>
                        <p class="text-gray-600 mb-4">Gestionați relațiile cu furnizorii și adăugați furnizori noi.</p>
                        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Gestionare Furnizori
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 