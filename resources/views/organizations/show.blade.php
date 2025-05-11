<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalii Organizație') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('organizations.edit', $organization) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    {{ __('Editează') }}
                </a>
                @if ($organization->canBeDeleted())
                    <form action="{{ route('organizations.destroy', $organization) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700" onclick="return confirm('{{ __('Sigur doriți să ștergeți această organizație?') }}')">
                            {{ __('Șterge') }}
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informații Generale -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Informații Generale') }}</h3>
                                <dl class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Nume') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->name }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('CUI') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->tax_id }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Nr. Reg. Com.') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->registration_number }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Tip') }}</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $organization->type === 'supplier' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $organization->type === 'supplier' ? __('Furnizor') : __('Client') }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                                        <dd class="text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $organization->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $organization->is_active ? __('Activ') : __('Inactiv') }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Monedă Implicită') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->default_currency }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Contact') }}</h3>
                                <dl class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->email }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Telefon') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->phone }}</dd>
                                    </div>
                                    @if ($organization->website)
                                        <div class="flex justify-between py-2 border-b">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Website') }}</dt>
                                            <dd class="text-sm text-gray-900">
                                                <a href="{{ $organization->website }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $organization->website }}
                                                </a>
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Adresă și Statistici -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Adresă') }}</h3>
                                <dl class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Adresă') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->address }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Oraș') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->city }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Județ') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->county }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Cod Poștal') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->postal_code }}</dd>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Țară') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->country }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Statistici') }}</h3>
                                <dl class="grid grid-cols-1 gap-2">
                                    @if ($organization->type === 'supplier')
                                        <div class="flex justify-between py-2 border-b">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Produse Active') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $organization->products_count }}</dd>
                                        </div>
                                        <div class="flex justify-between py-2 border-b">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Comenzi Primite') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $organization->received_orders_count }}</dd>
                                        </div>
                                    @else
                                        <div class="flex justify-between py-2 border-b">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Comenzi Plasate') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $organization->placed_orders_count }}</dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between py-2 border-b">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Ultima Actualizare') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $organization->updated_at->format('d.m.Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Activitate Recentă -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Activitate Recentă') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Data') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Tip') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Detalii') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Utilizator') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($organization->recentActivity as $activity)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $activity->created_at->format('d.m.Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $activity->type === 'order' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ __($activity->type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $activity->description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $activity->user->name }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 