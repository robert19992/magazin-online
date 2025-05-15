<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import Catalog Produse') }}
            </h2>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Înapoi la Catalog') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="mb-4 px-4 py-2 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Instrucțiuni -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Instrucțiuni Import') }}</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-2">{{ __('Fișierul CSV trebuie să conțină următoarele coloane, separate prin virgulă:') }}</p>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Cod Produs (obligatoriu, unic)</li>
                                <li>Descriere (obligatoriu)</li>
                                <li>Producator Masina (obligatoriu)</li>
                                <li>Greutate (obligatoriu, în kg)</li>
                                <li>Pret (obligatoriu)</li>
                                <li>Stoc (obligatoriu)</li>
                                <li>Data Introducere pe piata (obligatoriu, format: YYYY-MM-DD)</li>
                            </ul>
                            <p class="text-sm text-gray-600 mt-3 font-medium">Notă: Se folosește VIRGULĂ ca delimitator.</p>
                        </div>
                    </div>

                    <!-- Formular Import -->
                    <form method="POST" action="{{ route('products.process-import') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="csv_file" :value="__('Fișier CSV (delimitat prin virgulă)')" />
                            <input type="file" 
                                   name="csv_file" 
                                   id="csv_file" 
                                   accept=".csv"
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100"
                                   required />
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="has_header" 
                                   id="has_header" 
                                   value="1" 
                                   checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                            <x-input-label for="has_header" :value="__('Fișierul conține antet')" class="ml-2" />
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="update_existing" 
                                   id="update_existing" 
                                   value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                            <x-input-label for="update_existing" :value="__('Actualizează produsele existente')" class="ml-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button onclick="window.history.back()" type="button" class="mr-4">
                                {{ __('Anulează') }}
                            </x-secondary-button>
                            
                            <x-primary-button>
                                {{ __('Importă Produse') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Template Download -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Descarcă Template') }}</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            {{ __('Descarcă un template CSV pentru a vedea formatul corect al datelor.') }}
                        </p>
                        <a href="{{ route('products.template.download') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            {{ __('Descarcă Template CSV') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 