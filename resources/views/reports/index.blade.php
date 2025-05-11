<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rapoarte') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Raport Vânzări -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Raport Vânzări') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Analizează vânzările, comenzile și performanța produselor într-o perioadă specificată.') }}
                        </p>
                        <form action="{{ route('reports.sales') }}" method="GET" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="start_date" :value="__('Data început')" />
                                    <x-text-input id="start_date" type="date" name="start_date" 
                                                :value="old('start_date', now()->subMonth()->format('Y-m-d'))"
                                                class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="end_date" :value="__('Data sfârșit')" />
                                    <x-text-input id="end_date" type="date" name="end_date" 
                                                :value="old('end_date', now()->format('Y-m-d'))"
                                                class="mt-1 block w-full" required />
                                </div>
                            </div>
                            <x-primary-button class="w-full justify-center">
                                {{ __('Generează Raport') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>

                <!-- Raport Stoc -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Raport Stoc') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Vizualizează situația stocului, produsele cu stoc redus și valoarea totală a inventarului.') }}
                        </p>
                        <form action="{{ route('reports.inventory') }}" method="GET">
                            <x-primary-button class="w-full justify-center">
                                {{ __('Generează Raport') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>

                <!-- Raport Financiar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Raport Financiar') }}</h3>
                        <p class="text-gray-600 mb-4">
                            {{ __('Analizează situația facturilor, încasărilor și plăților restante.') }}
                        </p>
                        <form action="{{ route('reports.financial') }}" method="GET" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="start_date" :value="__('Data început')" />
                                    <x-text-input id="start_date" type="date" name="start_date" 
                                                :value="old('start_date', now()->subMonth()->format('Y-m-d'))"
                                                class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="end_date" :value="__('Data sfârșit')" />
                                    <x-text-input id="end_date" type="date" name="end_date" 
                                                :value="old('end_date', now()->format('Y-m-d'))"
                                                class="mt-1 block w-full" required />
                                </div>
                            </div>
                            <x-primary-button class="w-full justify-center">
                                {{ __('Generează Raport') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 