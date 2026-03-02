<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl text-sm text-gray-700 space-y-3">
                    <p>
                        {{ __('Profile management via Livewire has been removed.') }}
                    </p>
                    <p>
                        {{ __('You can wire up traditional controller-based profile forms here (update name, email, password, and delete account) when you are ready, without Livewire.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
