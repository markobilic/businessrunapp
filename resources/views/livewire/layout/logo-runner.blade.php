<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\Organizer;

new class extends Component
{
    public Organizer $organizer;

    public function mount()
    {
        $this->organizer = request()->attributes->get('current_organizer');
    }
}; ?>

<a href="/" wire:navigate>
    @if($organizer->id == 2)
        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
    @else
        <img class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" alt="Logo" src="{{asset('images/brs-logo-f.png')}}">
    @endif
</a>