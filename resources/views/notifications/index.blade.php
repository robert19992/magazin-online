<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notificări') }}
            </h2>
            <div class="flex space-x-4">
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Marchează toate ca citite
                    </button>
                </form>
                <form action="{{ route('notifications.clear-all') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                        Șterge toate
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($notifications->isEmpty())
                        <div class="text-center text-gray-500 py-8">
                            Nu aveți notificări.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="flex items-start justify-between p-4 {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }} rounded-lg border {{ $notification->read_at ? 'border-gray-200' : 'border-blue-200' }}">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            @if(!$notification->read_at)
                                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                            @endif
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $notification->data['message'] }}
                                            </p>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if(!$notification->read_at)
                                            <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                                                    Marchează ca citită
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                                Șterge
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 