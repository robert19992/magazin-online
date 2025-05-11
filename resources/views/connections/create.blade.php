<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adaugă conexiune nouă') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600">
                                {{ __('Ups! Au apărut următoarele erori:') }}
                        </div>

                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('connections.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="connect_id" :value="__('Connect ID')" />
                            <x-text-input id="connect_id" name="connect_id" type="text" class="mt-1 block w-full" :value="old('connect_id')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('connect_id')" />
                            <p class="mt-2 text-sm text-gray-500">
                                {{ __('Introduceți Connect ID-ul partenerului cu care doriți să vă conectați.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Adaugă conexiune') }}</x-primary-button>
                            <a href="{{ route('connections.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Anulează') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 