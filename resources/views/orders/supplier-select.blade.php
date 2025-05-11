@props(['suppliers'])

<div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Selectează Furnizorul') }}</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($suppliers as $supplier)
            <div class="border rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition-colors duration-200 
                {{ $attributes->get('value') == $supplier->id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}"
                x-on:click="$wire.selectSupplier({{ $supplier->id }})">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $supplier->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $supplier->city }}, {{ $supplier->country }}</p>
                    </div>
                    @if($attributes->get('value') == $supplier->id)
                        <svg class="h-5 w-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div> 