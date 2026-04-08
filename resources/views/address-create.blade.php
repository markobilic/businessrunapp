<x-app-layout>
    <x-slot name="header">
        <div class="px-2 flex flex-row flex-wrap justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create address') }}
            </h2>
            <div>
                <a class="px-4 py-2 bg-gray-800 rounded block hover:bg-gray-500 flex items-center text-white uppercase" href="{{url()->previous()}}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18" />
                    </svg>
                    <span class="ms-1">{{__('Back')}}</span>
                </a>
            </div>
        </div>
    </x-slot>
    
    <livewire:pages.team.new-address :team-id="$teamId"/>
</x-app-layout>
