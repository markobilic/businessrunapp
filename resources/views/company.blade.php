<x-app-layout>     
    <x-slot name="header">
        <div class="px-2 py-2 flex flex-row flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Company data') }}
            </h2>
        </div>
    </x-slot>

    @if(auth()->user()->hasRole('captain'))
        <div class="mt-6 grid grid-cols-1 gap-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded">
                <div class="w-full">
                    <livewire:profile.update-captain-information-form />
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded">
                <div class="w-full">
                    <livewire:profile.update-captain-addresses-form />
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
