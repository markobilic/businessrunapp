<x-app-layout>
    @if(Auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))       
        <x-slot name="header">
            <div class="px-2 py-2 flex flex-row flex-wrap justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <div class="inline-flex">
                    <div>
                        <strong>{{ __("Hello, :name", ['name' => Auth::user()->name]) }}</strong>
                    </div>
                </div>
            </div>
        </x-slot>
    @endif

    <livewire:pages.dashboard/>
</x-app-layout>
