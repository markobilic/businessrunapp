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

<div class="p-3 w-1/2 mx-auto md:w-full rounded bg-soft-green">
    @if($organizer->id == 2)
        <img alt="BRS" src="{{asset('images/guest-brs.png')}}">
    @else
        <img alt="BRS" src="{{asset('images/brs-image-start.png')}}">
    @endif
</div>