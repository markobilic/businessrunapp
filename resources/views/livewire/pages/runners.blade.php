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

    public $runners;
    public ?int $captainId = null, $raceId = null, $year = null;

    public function mount($year = null, $raceId = null, $captainId = null)
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

        if($captainId)
        {
            $this->captainId = $captainId;
        }

        if($year)
        {
            $this->year = $year;
        }
        
        $this->runners = Runner::query()
            ->whereHas('runnerReservations', function ($rrQuery) {
                $rrQuery->whereHas('reservation', function ($resQuery) {
                    $resQuery->whereHas('race', function ($r) {
                        $r->where('organizer_id', $this->organizerId);
                    });
        
                    $resQuery->when($this->raceId, function ($q) {
                        $q->where('race_id', $this->raceId);
                    });

                    $resQuery->when($this->captainId, function ($q) {
                        $q->where('captain_id', $this->captainId);
                    });
        
                    $resQuery->when($this->year, function ($q) {
                        $q->whereHas('race', function ($r) {
                            $r->whereYear('starting_date', $this->year);
                        });
                    });
                });
            })
            ->with(['runnerReservations.reservation' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->get();
    }
}; ?>

<div>
    <h1 class="text-xl uppercase font-bold my-4 text-center">
        @if($raceId)
            {{ __('All runners for') }} {{ $race->name }}
        @else
            {{ __('All runners') }} {{ $year }}
        @endif
    </h1>
    <div class="bg-white p-4">
        <livewire:all-runners-table :captain-id="$captainId" :race-id="$raceId" :year="$year"/>
    </div>
</div>