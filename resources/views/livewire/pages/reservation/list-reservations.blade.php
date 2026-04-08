<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Reservation;
use App\Models\Race;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Services\MailService;

new class extends Component {

    public Reservation $selectedReservation;
    public $reservations, $reservationId;
    public $organizerId;
    public $races;
    public $raceYears;
    public $raceId;
    public $raceYear;
    public $raceStatus = 1;
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
            ->where('organizer_id', $this->organizerId)
            ->orderBy('year', 'ASC')
            ->pluck('year');

        $userId = Auth::id();

        $filters = Cache::get("reservationFilters_{$userId}");

        if ($filters) {
            $this->raceYear   = $filters['raceYear'] ?? null;
            $this->raceId     = $filters['raceId'] ?? null;
            $this->raceStatus = $filters['raceStatus'] ?? 1;
        }

        if(!$this->raceYear)
        {
            $this->raceYear = null;
        }

        if(!$this->raceId)
        {
            $this->raceId = null;
        }
    }

    public function filterReservations()
    {
        $userId = Auth::id();

        if(!$this->raceYear)
        {
            $this->raceYear = null;
        }

        if(!$this->raceId)
        {
            $this->raceId = null;
        }

        $filters = [
            'raceYear'   => $this->raceYear,
            'raceId'     => $this->raceId,
            'raceStatus' => $this->raceStatus,
        ];

        Cache::forever("reservationFilters_{$userId}", $filters);
    }

    public function resetFilter()
    {
        $this->reset(['raceYear','raceId','raceStatus']);
        
        $userId = Auth::id();
        Cache::forget("reservationFilters_{$userId}");
    }

    public function deleteSelectedReservation($reservationId)
    {
        $this->reservationId = $reservationId;
        $this->dispatch('confirmDeletion', ['reservationId' => $reservationId]);
    }
    
    public function deleteReservationConfirmed(MailService $mailService)
    {
        if($this->reservationId)
        {
            $reservation = Reservation::findOrFail($this->reservationId);

            if($reservation)
            {
                if($this->organizerId == 2)
                {
                    event(new \App\Events\DeleteReservation($reservation));
                }

                $mailService->deletedReservation($reservation->id, $reservation->captain->team_name, $reservation->reserved_places, $reservation->captain->organizer);
                
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
        <div x-data="{ showNotification: true }" x-show="showNotification" x-on:click="showNotification = false;" x-init="setTimeout(() => { showNotification = false; }, 10000)" class="fixed top-4 left-1/2 z-50 transform -translate-x-1/2 shadow-md">
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
        <div x-data="{ showNotification: true }" x-show="showNotification" x-on:click="showNotification = false; $wire.dispatch('resetError')" x-init="setTimeout(() => { showNotification = false; $wire.dispatch('resetError'); }, 10000)" class="fixed top-4 left-1/2 z-50 transform -translate-x-1/2 shadow-md">
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
    <div class="mt-6" wire:loading.class="opacity-50">
        <form wire:submit.prevent="filterReservations" class="bg-white shadow-md rounded-sm p-4 flex flex-col lg:flex-row items-center gap-4">
            <div class="inline-flex items-center gap-4">
                <div>
                    <x-input-label for="raceStatus" :value="__('Status')"/>
                    <select wire:model="raceStatus" name="raceStatus" id="raceStatus" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">      
                        @foreach ($raceStatuses as $key => $rs)
                            <option value="{{ $key }}">{{ __($rs) }}</option>
                        @endforeach
                    </select>
                </div>  
                <div>
                    <x-input-label for="raceYear" :value="__('Year')"/>
                    <select wire:model="raceYear" name="raceYear" id="raceYear" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">      
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach ($raceYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>  
                <div>
                    <x-input-label for="raceId" :value="__('Race')"/>
                    <select wire:model="raceId" name="raceId" id="raceId" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">      
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach ($races as $race)
                            <option value="{{ $race->id }}">{{ $race->name }}</option>
                        @endforeach
                    </select>
                </div> 
            </div>                           
            <div class="lg:mt-6 flex justify-end">                       
                <x-primary-button>
                    {{ __('Filter') }}
                </x-primary-button>
                <x-secondary-button class="ms-3" wire:click.prevent="resetFilter()">
                    {{ __('Reset') }}
                </x-secondary-button>
            </div>
        </form>  
        <div class="mt-6 rounded bg-white p-4" wire:loading.class="opacity-50">
            <livewire:reservations-table wire:key="reservations-{{$raceYear}}-{{$raceId}}-{{ $raceStatus }}" :race-id="$raceId" :race-year="$raceYear" :race-status="$raceStatus"/>
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
