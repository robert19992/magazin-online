<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organizații') }}
            </h2>
            <a href="{{ route('organizations.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Adaugă Organizație') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Filtre -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('organizations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="search" :value="__('Caută')" />
                                <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="request('search')" placeholder="{{ __('Nume sau CUI') }}" />
                            </div>

                            <div>
                                <x-input-label for="type" :value="__('Tip')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">{{ __('Toate') }}</option>
                                    <option value="supplier" {{ request('type') === 'supplier' ? 'selected' : '' }}>{{ __('Furnizor') }}</option>
                                    <option value="customer" {{ request('type') === 'customer' ? 'selected' : '' }}>{{ __('Client') }}</option>
                                </select>
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">{{ __('Toate') }}</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <x-primary-button>
                                    {{ __('Filtrează') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Lista Organizații -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Nume') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('CUI') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Tip') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Email') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Telefon') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Acțiuni') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($organizations as $organization)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $organization->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $organization->tax_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $organization->type === 'supplier' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $organization->type === 'supplier' ? __('Furnizor') : __('Client') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $organization->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $organization->phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $organization->is_active ? __('Activ') : __('Inactiv') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('organizations.show', $organization) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                {{ __('Vezi') }}
                                            </a>
                                            <a href="{{ route('organizations.edit', $organization) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                                {{ __('Editează') }}
                                            </a>
                                            @if ($organization->canBeDeleted())
                                                <form action="{{ route('organizations.destroy', $organization) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('Sigur doriți să ștergeți această organizație?') }}')">
                                                        {{ __('Șterge') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginare -->
                    <div class="mt-4">
                        {{ $organizations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 