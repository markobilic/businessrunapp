<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Reservation;
use App\Models\Race;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

new class extends Component {

    public Reservation $selectedReservation;
    public $reservations, $reservationId;
    public $organizerId;
    public $races;
    public $raceYears;
    public $raceId;
    public $year;
    public $raceStatus;
    public $raceStatuses = ['all', 'active', 'archived'];

    protected $listeners = ['resetError', 'deleteSelectedReservation', 'deleteReservationConfirmed', 'lockSelectedReservation', 'lockReservationConfirmed', 'unlockSelectedReservation', 'unlockReservationConfirmed'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount()
    {
        $currentOrganizer = request()->attributes->get('current_organizer');

        if($currentOrganizer)
        {
            $this->organizerId = $currentOrganizer->id;
        }
        else
        {
            $this->organizerId = null;
        }

        $this->races = Race::with(['inventories.inventoryType', 'reservations'])->where('organizer_id', $this->organizerId)
            ->when(auth()->user()->hasRole('captain'), function ($query) {
                return $query->where('locked', false);
            })
            ->when(auth()->user()->hasRole('partner'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->orderBy('starting_date', 'DESC')
            ->get();

        $this->raceYears = Race::selectRaw('YEAR(starting_date) as year')
            ->distinct()
            ->orderBy('year', 'ASC')
            ->pluck('year');
        
        $this->year = $this->raceYears->last();
    }

    public function resetFilter()
    {
        $this->year = null;
        $this->raceId = null;
        $this->raceStatus = null;
    }

    public function filterByYear($year)
    {
        $this->year = $year;
        $this->raceId = null;
        $this->raceStatus = null;
    }    

    public function filterByRace($raceId)
    {
        $this->raceId = $raceId;
        $this->year = null;
        $this->raceStatus = null;
    }

    public function filterByRaceStatus($status)
    {
        $this->raceStatus = $status;
        $this->year = null;
        $this->raceId = null;
    }

    public function deleteSelectedReservation($reservationId)
    {
        $this->reservationId = $reservationId;
        $this->dispatch('confirmDeletion', ['reservationId' => $reservationId]);
    }
    
    public function deleteReservationConfirmed()
    {
        if($this->reservationId)
        {
            $reservation = Reservation::findOrFail($this->reservationId);

            if($reservation)
            {
                $reservation->delete();
                $this->dispatch('pg:eventRefresh-reservationsTable');
                session()->flash('message', 'Reservation deleted successfully.');                
            }            
        }

        $this->reset(['reservationId', 'selectedReservation']);
    }

    public function lockSelectedReservation($reservationId)
    {
        $this->reservationId = $reservationId;
        $this->dispatch('confirmLock', ['reservationId' => $reservationId]);
    }

    public function lockReservationConfirmed()
    {
        if($this->reservationId)
        {
            $reservation = Reservation::findOrFail($this->reservationId);

            if($reservation)
            {
                $reservation->locked = true;
                $reservation->locked_date = Carbon::now();
                $reservation->save();
                $this->dispatch('pg:eventRefresh-reservationsTable');
                session()->flash('message', 'Reservation locked successfully.');               
            }            
        }

        $this->reset(['reservationId', 'selectedReservation']);
    }

    public function unlockSelectedReservation($reservationId)
    {
        $this->reservationId = $reservationId;
        $this->dispatch('confirmUnlock', ['reservationId' => $reservationId]);
    }

    public function unlockReservationConfirmed()
    {
        if($this->reservationId)
        {
            $reservation = Reservation::findOrFail($this->reservationId);

            if($reservation)
            {
                $reservation->locked = false;
                $reservation->locked_date = null;
                $reservation->save();
                $this->dispatch('pg:eventRefresh-reservationsTable');
                session()->flash('message', 'Reservation unlocked successfully.');   
            }            
        }

        $this->reset(['reservationId', 'selectedReservation']);
    }
}; ?>

<div>
    @if (session()->has('message'))
        <div x-data="{ showNotification: true }" x-show="showNotification" x-init="setTimeout(() => { showNotification = false; }, 10000)" class="fixed bottom-4 right-4 z-50">
            <div class="bg-gray-100 border-t-4 border-gray-500 rounded text-gray-900 px-4 py-3 shadow-md" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-gray-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                    <p class="font-bold">{{__('Message')}}</p>
                    <p class="text-sm">{{ __(session('message')) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if($errors->has('error'))
        <div x-data="{ showNotification: true }" x-show="showNotification" x-init="setTimeout(() => { showNotification = false; $wire.dispatch('resetError'); }, 10000)" class="fixed bottom-4 right-4 z-50">
            <div class="bg-red-100 border-t-4 border-red-500 rounded-sm text-red-900 px-4 py-3 shadow-md" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                        <p class="font-bold">{{ __('Error') }}</p>
                        <p class="text-sm">{{ __($errors->first('error')) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="mt-6">
        <div class="text-center">
            @foreach($raceYears as $ry)
                <button type="button" 
                        wire:click="filterByYear({{ $ry }})" 
                        class="px-2 py-1 bg-mid-green hover:bg-dark-green rounded text-white mx-1" :class="(@json($year) == @json($ry))  ? '!bg-light-green' : ''">
                    {{ $ry }}
                </button>
            @endforeach
            <button type="button" 
                    wire:click="resetFilter()" 
                    class="align-top p-2 bg-red-500 hover:bg-red-800 rounded-full text-white mx-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
            </button>
        </div>
        @if(Auth::user()->hasRole(['superadmin', 'organizer']))
            <div class="mt-6 text-center">
                @foreach($raceStatuses as $index => $rs)
                    <button type="button" 
                            wire:click="filterByRaceStatus({{ $index }})" 
                            class="px-2 py-1 bg-mid-green hover:bg-dark-green rounded text-white mx-1" :class="(@json($raceStatus) == @json($index))  ? '!bg-light-green' : ''">
                        {{ __($rs) }}
                    </button>
                @endforeach
            </div>
        @endif
        <div class="mt-6 text-center w-full overflow-x-auto flex">
            <div class="flex flex-row">
                @foreach($races as $race)
                    <div class="mx-1 h-full {{ $races->count() < 12 ? 'flex-1' : 'flex-none w-1/12' }}">
                        <button type="button" 
                                wire:click="filterByRace({{ $race->id }})" 
                                class="h-full w-full rounded shadow-md border border-gray-500 p-3 bg-gray-800 text-white flex flex-col text-center justify-between"
                                :class="(@json($raceId) == @json($race->id))  ? '!border-light-green border-2' : ''">
                            <span class="mx-auto text-lg font-bold uppercase">{{ $race->name }}</span>
                            <div class="mx-auto">
                                <div class="flex flex-row justify-between gap-2 items-center">
                                    <img class="w-1/2" src="{{asset('storage/'.$race->logo)}}">
                                    <div class="text-end">
                                        <small class="text-light-green">{{$race->location}}</small>
                                        <br/>
                                        <span>{{Carbon::parse($race->starting_date)->format('d.m.Y.')}}</span>
                                    </div>
                                </div>                                
                            </div>                            
                        </button>
                    </div>
                @endforeach    
            </div>        
        </div>
        <div class="mt-6 rounded bg-white p-4"  wire:loading.class="opacity-50">
            <livewire:reservations-table wire:key="reservations-{{$year}}-{{$raceId}}-{{ $raceStatus }}" :race-id="$raceId" :year="$year" :race-status="$raceStatus"/>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this reservation?')}}')) {
                    Livewire.dispatch('deleteReservationConfirmed');
                }
            });

            Livewire.on('confirmLock', (data) => {
                if (confirm('{{__('Are you sure you want to lock this reservation?')}}')) {
                    Livewire.dispatch('lockReservationConfirmed');
                }
            });

            Livewire.on('confirmUnlock', (data) => {
                if (confirm('{{__('Are you sure you want to unlock this reservation?')}}')) {
                    Livewire.dispatch('unlockReservationConfirmed');
                }
            });
        });
    </script>
</div>
