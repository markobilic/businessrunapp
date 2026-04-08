<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Organizer;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Models\Captain;
use App\Models\Race;
use App\Models\Runner;

new class extends Component {

    public $organizerId;
    public Organizer $currentOrganizer;

    public Race $race;

    public $captains;
    public $raceId = null, $year = null;

    public function mount($year = null, $raceId = null)
    {
        $currentOrganizer = request()->attributes->get('current_organizer');
        
        if($currentOrganizer)
        {
            $this->currentOrganizer = $currentOrganizer;
            $this->organizerId = $currentOrganizer->id;
        }
        else
        {
            $this->organizerId = null;
        }

        if($raceId)
        {
            $this->raceId = $raceId;

            $this->race = Race::findOrFail($raceId);
        }

        if($year)
        {
            $this->year = $year;
        }
    }
}; ?>

<div>
<h1 class="text-xl uppercase font-bold my-4 text-center">
        @if($raceId)
            {{ __('All companies for') }} {{ $race->name }}
        @else
            {{ __('All companies') }} {{ $year }}
        @endif
    </h1>
    <div class="bg-white p-4">
        <livewire:all-captains-table :race-id="$raceId" :year="$year"/>
    </div>    
</div>