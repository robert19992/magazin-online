<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Ștergere cont') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Odată ce contul dvs. este șters, toate resursele și datele sale vor fi șterse definitiv. Vă rugăm să introduceți parola pentru a confirma că doriți să ștergeți definitiv contul dvs.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Șterge cont') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Sigur doriți să ștergeți contul?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Vă rugăm să introduceți parola pentru a confirma că doriți să ștergeți definitiv contul dvs.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Parolă') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Parolă') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Anulează') }}
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    {{ __('Șterge cont') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
