@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-4">Bun venit, {{ auth()->user()->name }}!</h2>
                
                <!-- Statistici rapide -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-blue-900">Comenzi Active</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $activeOrders }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-green-900">Comenzi Finalizate</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $completedOrders }}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-purple-900">Furnizori Conectați</h3>
                        <p class="text-3xl font-bold text-purple-600">{{ $connectedSuppliers }}</p>
                    </div>
                </div>

                <!-- Acțiuni rapide -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white border rounded-lg p-4">
                        <h3 class="text-lg font-medium mb-2">Acțiuni Rapide</h3>
                        <div class="space-y-2">
                            <a href="{{ route('orders.create') }}" class="block w-full text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Nouă Comandă
                            </a>
                            <a href="{{ route('orders.request') }}" class="block w-full text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                Cerere Ofertă
                            </a>
                        </div>
                    </div>
                    <div class="bg-white border rounded-lg p-4">
                        <h3 class="text-lg font-medium mb-2">Comenzi Recente</h3>
                        @if($recentOrders->count() > 0)
                            <div class="space-y-2">
                                @foreach($recentOrders as $order)
                                    <div class="flex justify-between items-center border-b pb-2">
                                        <span>Comanda #{{ $order->id }}</span>
                                        <span class="text-sm text-gray-500">{{ $order->created_at->format('d.m.Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">Nu există comenzi recente</p>
                        @endif
                    </div>
                </div>

                <!-- Documente recente -->
                <div class="bg-white border rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-2">Documente Recente</h3>
                    @if($recentDocuments->count() > 0)
                        <div class="space-y-2">
                            @foreach($recentDocuments as $document)
                                <div class="flex justify-between items-center border-b pb-2">
                                    <span>{{ $document->type }} pentru Comanda #{{ $document->order_id }}</span>
                                    <a href="{{ route('documents.download', $document) }}" class="text-blue-500 hover:text-blue-700">
                                        Descarcă
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Nu există documente recente</p>
                    @endif
                </div>

                <!-- Carduri principale dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-blue-100 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-1">Produse</h3>
                        <p class="mb-2">Vezi produsele disponibile</p>
                    </div>
                    <div class="bg-green-100 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-1">Comenzi</h3>
                        <p class="mb-2">Vezi comenzile tale</p>
                    </div>
                    <div class="bg-purple-100 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-1">Conexiuni</h3>
                        <p class="mb-2">Gestionează conexiunile cu furnizorii</p>
                    </div>
                    <div class="bg-yellow-100 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-1">Plasează comandă</h3>
                        <p class="mb-2">Plasează o comandă nouă către furnizorii tăi</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-cyan-100 p-6 rounded-lg">
                        <h3 class="font-bold text-lg mb-1">Raport comenzi</h3>
                        <p class="mb-2">Vezi statistici și detalii despre toate comenzile tale</p>
                        <a href="{{ route('orders.report') }}" class="inline-block mt-2 px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">Accesează raportul</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 