<x-app-layout>
    <x-slot name="header">
        <h2 class="px-2 py-2 font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payments') }}
        </h2>
    </x-slot>

    <livewire:pages.transactions/>
</x-app-layout>
