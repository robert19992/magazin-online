<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conexiuni') }}
            </h2>
            <a href="{{ route('connections.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Adaugă conexiune nouă') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if(auth()->user()->isSupplier())
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Client') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Connect ID') }}
                                        </th>
                                    @else
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Furnizor') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Connect ID') }}
                                        </th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Data creării') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Acțiuni') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($connections as $connection)
                                    <tr>
                                        @if(auth()->user()->isSupplier())
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $connection->client->company_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $connection->client->connect_id }}
                                            </td>
                                        @else
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $connection->supplier->company_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $connection->supplier->connect_id }}
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($connection->status === 'active') bg-green-100 text-green-800
                                                @elseif($connection->status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                @if($connection->status === 'active') {{ __('Activ') }}
                                                @elseif($connection->status === 'pending') {{ __('În așteptare') }}
                                                @else {{ __('Inactiv') }} @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $connection->created_at->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if(auth()->user()->isSupplier())
                                                @if($connection->status === 'pending')
                                                    <form action="{{ route('connections.update-status', $connection) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="is_active" value="1">
                                                        <button type="submit" class="text-green-600 hover:text-green-900 mr-3">
                                                            {{ __('Activează') }}
                                                        </button>
                                                    </form>
                                                @elseif($connection->status === 'active')
                                                    <form action="{{ route('connections.update-status', $connection) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="is_active" value="0">
                                                        <button type="submit" class="text-red-600 hover:text-red-900 mr-3">
                                                            {{ __('Dezactivează') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                            <form action="{{ route('connections.destroy', $connection) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('Sigur doriți să ștergeți această conexiune?') }}')">
                                                    {{ __('Șterge') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $connections->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 