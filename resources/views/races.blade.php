<x-app-layout>
    <x-slot name="header">
        <div class="px-2 flex flex-row flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Races') }}
            </h2>
            @if(auth()->user()->hasRole(['superadmin', 'organizer']))
                <div>
                    <a class="px-4 py-2 bg-mid-green rounded block hover:bg-dark-green flex items-center text-white uppercase" href="{{route('races.create')}}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="ms-1">{{__('Add race')}}</span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <livewire:pages.race.list-races/>
</x-app-layout>
