@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">
                            {{ __('Produse') }} - {{ $supplier->name }}
                        </h2>
                        @if($supplier->organization)
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $supplier->organization->name }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('suppliers.index') }}" class="text-blue-600 hover:text-blue-900">
                            &larr; {{ __('Înapoi la furnizori') }}
                        </a>

                        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                            @csrf
                            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                    id="submitOrder"
                                    disabled>
                                {{ __('Plasează Comanda') }}
                                (<span id="selectedCount">0</span>)
                            </button>
                        </form>
                    </div>
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500">{{ __('Nu există produse disponibile momentan.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="w-8 px-6 py-3">
                                        <input type="checkbox" 
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                               id="selectAll">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Cod') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Descriere') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Preț') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Cantitate') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($products as $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" 
                                                   name="items[{{ $product->id }}][product_id]"
                                                   value="{{ $product->id }}"
                                                   form="orderForm"
                                                   class="product-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $product->code }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $product->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($product->price, 2) }} RON
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <input type="number" 
                                                   name="items[{{ $product->id }}][quantity]"
                                                   min="1"
                                                   value="1"
                                                   form="orderForm"
                                                   class="product-quantity mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                   disabled>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const quantityInputs = document.querySelectorAll('.product-quantity');
    const submitButton = document.getElementById('submitOrder');
    const selectedCountSpan = document.getElementById('selectedCount');

    function updateSubmitButton() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        submitButton.disabled = checkedBoxes.length === 0;
        selectedCountSpan.textContent = checkedBoxes.length;
    }

    selectAll.addEventListener('change', function() {
        productCheckboxes.forEach((checkbox, index) => {
            checkbox.checked = selectAll.checked;
            quantityInputs[index].disabled = !selectAll.checked;
        });
        updateSubmitButton();
    });

    productCheckboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            quantityInputs[index].disabled = !checkbox.checked;
            selectAll.checked = [...productCheckboxes].every(cb => cb.checked);
            updateSubmitButton();
        });
    });
});
</script>
@endpush
@endsection 