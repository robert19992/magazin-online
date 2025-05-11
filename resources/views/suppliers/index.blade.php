@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">
                        {{ __('Selectează Furnizor') }}
                    </h2>
                </div>

                @if($suppliers->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500">{{ __('Nu există furnizori disponibili momentan.') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($suppliers as $supplier)
                            <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $supplier->name }}
                                    </h3>
                                    
                                    @if($supplier->organization)
                                        <div class="text-sm text-gray-600 mb-4">
                                            <p class="mb-1">
                                                <span class="font-medium">{{ __('Companie') }}:</span> 
                                                {{ $supplier->organization->name }}
                                            </p>
                                            @if($supplier->organization->tax_id)
                                                <p class="mb-1">
                                                    <span class="font-medium">{{ __('CUI') }}:</span> 
                                                    {{ $supplier->organization->tax_id }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-4 flex justify-end">
                                        <a href="{{ route('orders.create', ['supplier_id' => $supplier->id]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition">
                                            {{ __('Comandă Nouă') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $suppliers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 