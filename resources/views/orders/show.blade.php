@extends('layouts.app')

@section('content')
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalii comandă') }} #{{ $order->id }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Înapoi la comenzi') }}
                </a>
                @if(auth()->user()->isSupplier() && $order->status === 'pending')
                    <form action="{{ route('orders.update-status', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Procesează comanda') }}
                        </button>
                    </form>
                            @endif
                @if(auth()->user()->isSupplier() && $order->status === 'processing')
                    <form action="{{ route('orders.update-status', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Marchează ca livrat') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Informații comandă -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Informații comandă') }}</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($order->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                               ($order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('Data plasării') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('d.m.Y H:i') }}</dd>
                                </div>
                                @if($order->notes)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Note') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $order->notes }}</dd>
                            </div>
                                @endif
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Informații contact') }}</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                @if(auth()->user()->isSupplier())
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Client') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $order->client->company_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Adresă') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $order->client->street }} {{ $order->client->street_number }}<br>
                                            {{ $order->client->city }}, {{ $order->client->county }}
                                        </dd>
                                    </div>
                                @else
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Furnizor') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $order->supplier->company_name }}</dd>
                                    </div>
                                @endif
                            </dl>
                </div>
            </div>

                    <!-- Produse comandate -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Produse comandate') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Cod produs') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Descriere') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Cantitate') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Preț unitar') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Subtotal') }}
                                        </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->items as $item)
                                    <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->product->part_number }}
                                        </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->product->description }}
                                        </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->quantity }}
                                        </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($item->price, 2) }} RON
                                        </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($item->quantity * $item->price, 2) }} RON
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                                <tfoot>
                                <tr>
                                        <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            {{ __('Total comandă') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($order->total, 2) }} RON
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@endsection 