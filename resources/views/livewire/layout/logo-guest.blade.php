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
        <x-application-logo class="h-8 fill-current" />
    @else
        <img class="h-8 fill-current" alt="Logo" src="{{asset('images/brs-logo-f.png')}}">
    @endif
</a>