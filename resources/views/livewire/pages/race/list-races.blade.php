<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Race;
use App\Models\Organizer;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

new class extends Component {

    public Race $selectedRace;
    public $races, $raceId;
    public $organizerId;
    public Organizer $currentOrganizer;
    public $raceYears;
    public $year;

    protected $listeners = ['resetError', 'deleteSelectedRace', 'deleteRaceConfirmed', 'lockRaceConfirmed', 'unlockRaceConfirmed'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount()
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

        $this->races = Race::with(['inventories.inventoryType', 'reservations'])->where('organizer_id', $this->organizerId)
            ->when(auth()->user()->hasRole('captain'), function ($query) {
                return $query->where('locked', false)
                    ->whereDate('application_end', '>=', now())
                    ->whereRaw('(SELECT COALESCE(SUM(reserved_places), 0) FROM reservations WHERE reservations.race_id = races.id  AND reservations.deleted_at IS null) < races.startplaces');
            })
            ->when(auth()->user()->hasRole('partner'), function ($query) {
                return $query->where('user_id', auth()->user()->id);
            })
            ->orderBy('starting_date', 'DESC')
            ->get();

        $this->raceYears = Race::selectRaw('YEAR(starting_date) as year')
            ->distinct()
            ->where('organizer_id', $this->organizerId)
            ->orderBy('year', 'ASC')
            ->pluck('year');

        $this->year = $this->raceYears->last();
    }

    public function filterByYear($year)
    {
        $query = Race::with(['inventories.inventoryType', 'reservations'])
            ->where('organizer_id', $this->organizerId)
            ->orderBy('starting_date', 'DESC');

        if (auth()->user()->hasRole('captain')) {
            $query->where('locked', false);
        }

        $query->whereYear('starting_date', $year);

        $this->races = $query->get();
        $this->year = $year;
    }

    public function resetYearFilter()
    {
        $this->races = Race::with(['inventories.inventoryType', 'reservations'])->where('organizer_id', $this->organizerId)
            ->when(auth()->user()->hasRole('captain'), function ($query) {
                return $query->where('locked', false);
            })
            ->orderBy('starting_date', 'DESC')
            ->get();

        $this->year = null;
    }

    public function deleteSelectedRace($raceId)
    {
        $this->raceId = $raceId;
        $this->dispatch('confirmDeletion', ['raceId' => $raceId]);
    }
    
    public function deleteRaceConfirmed()
    {
        if($this->raceId)
        {
            $race = Race::findOrFail($this->raceId);

            if($race)
            {
                $race->delete();
                $this->dispatch('pg:eventRefresh-racesTable');
                session()->flash('message', 'Race deleted successfully.');                
            }            
        }

        $this->reset(['raceId', 'selectedRace']);
    }

    public function lockRace($raceId)
    {
        $this->raceId = $raceId;
        $this->dispatch('confirmLock', ['raceId' => $raceId]);
    }

    public function lockRaceConfirmed()
    {
        if($this->raceId)
        {
            $race = Race::findOrFail($this->raceId);

            if($race)
            {
                $race->locked = true;
                $race->save();
                session()->flash('message', 'Race locked successfully.');
                
                $this->races = Race::with(['inventories.inventoryType', 'reservations'])->where('organizer_id', $this->organizerId)
                    ->when(auth()->user()->hasRole('captain'), function ($query) {
                        return $query->where('locked', false);
                    })
                    ->orderBy('starting_date', 'DESC')
                    ->get();
            }            
        }

        $this->reset(['raceId', 'selectedRace']);
    }

    public function unlockRace($raceId)
    {
        $this->raceId = $raceId;
        $this->dispatch('confirmUnlock', ['raceId' => $raceId]);
    }

    public function unlockRaceConfirmed()
    {
        if($this->raceId)
        {
            $race = Race::findOrFail($this->raceId);

            if($race)
            {
                $race->locked = false;
                $race->save();
                session()->flash('message', 'Race unlocked successfully.');         
                
                $this->races = Race::with(['inventories.inventoryType', 'reservations'])->where('organizer_id', $this->organizerId)
                    ->when(auth()->user()->hasRole('captain'), function ($query) {
                        return $query->where('locked', false);
                    })
                    ->orderBy('starting_date', 'DESC')
                    ->get();
            }            
        }

        $this->reset(['raceId', 'selectedRace']);
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
    <div class="mt-6">
        @if(auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))
            <div class="text-center">
                @foreach($raceYears as $ry)
                    <button type="button" 
                            wire:click="filterByYear({{ $ry }})" 
                            class="px-2 py-1 bg-mid-green hover:bg-dark-green rounded text-white mx-1" :class="(@json($year) == @json($ry))  ? '!bg-light-green' : ''">
                        {{ $ry }}
                    </button>
                @endforeach
                <button type="button" 
                        wire:click="resetYearFilter()" 
                        class="align-top p-2 bg-red-500 hover:bg-red-800 rounded-full text-white mx-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                </button>
            </div>
        @endif
        <div class="@if(auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner'])) mt-6 @endif grid grid-cols-1 sm:grid-cols-4 gap-4">
            @foreach($races as $race)
                @php
                    $carbonDate = Carbon::parse($race->starting_date);
                    $day = $carbonDate->day;
                    $month = $carbonDate->month;
                    $year = $carbonDate->year;
                @endphp
                <div class="rounded shadow-md border border-gray-500 p-3 bg-gray-800">
                    <div class="flex flex-row justify-between">
                        <h3 class="m-auto text-center text-xl font-bold uppercase text-white"><a href="{{ $race->web }}" target="_blank">{{$race->name}}</a></h3>  
                        @if(auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))                      
                            <span>
                                <button id="dropdownMenuIconButton-{{$race->id}}" data-dropdown-toggle="dropdownDots-{{$race->id}}" class="w-full right-full text-white hover:text-mid-green">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="m-auto w-6 h-6">                            
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/>
                                    </svg>
                                </button>
                                <div id="dropdownDots-{{$race->id}}" class="z-10 hidden bg-white divide-y divide-gray-100 rounded shadow-sm w-44">
                                    <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownMenuIconButton">
                                        @if(auth::user()->hasRole(['superadmin', 'organizer']))
                                            <li>
                                                <a href="{{route('races.edit', ['raceId' => $race->id])}}" class="block px-4 py-2 hover:bg-gray-100">{{__('Edit race')}}</a>
                                            </li>
                                        @endif
                                        @if(auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))
                                            <li>
                                                <a href="{{route('races.start-list', ['raceId' => $race->id])}}" class="block px-4 py-2 hover:bg-gray-100">{{__('Start list')}}</a>
                                            </li>
                                        @endif
                                        @if(auth::user()->hasRole(['superadmin', 'organizer']))
                                            <li>
                                                <button wire:click="lockRace({{$race->id}})" class="block px-4 py-2 hover:bg-gray-100">{{__('Lock race')}}</button>
                                            </li>
                                            <li>
                                                <a href="{{route('races.financial-report', ['raceId' => $race->id])}}" class="block px-4 py-2 hover:bg-gray-100">{{__('Financial report')}}</a>
                                            </li>
                                            <li>
                                                <a href="{{route('all-runners', ['year' => $year, 'raceId' => $race->id])}}" target="_blank" class="block px-4 py-2 hover:bg-gray-100">{{__('Public list of runners')}}</a>
                                            </li>
                                            <li>
                                                <a href="{{route('all-captains', ['year' => $year, 'raceId' => $race->id])}}" target="_blank" class="block px-4 py-2 hover:bg-gray-100">{{__('Public list of companies')}}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </span>     
                        @endif                   
                    </div>
                    <div class="mt-2 flex flex-row justify-between items-center text-center">
                        <a href="{{ $race->web }}" target="_blank">
                            <img class="w-1/2" src="{{asset('storage/'.$race->logo)}}">
                        </a>
                        <div class="text-center mx-auto">
                            <p class="inline-flex items-center">                                
                                <span class="text-white">
                                    {{$day}}
                                </span>
                                <span class="text-light-green px-2 font-black text-2xl"> · </span>
                                <span class="text-white">
                                    {{$month}}
                                </span>
                                <span class="text-light-green px-2 font-black text-2xl"> · </span>
                                <span class="text-white">
                                    {{$year}}
                                </span>
                            </p>
                            <p class="text-white text-center border-t border-t-white mt-2 pt-2">
                                {{$race->location}}
                            </p>
                        </div>                        
                    </div>
                    @if(auth::user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))
                        <div class="my-6 text-center">
                            <p class="inline-block w-auto m-auto text-white p-2 rounded bg-yellow-green">
                                <span>{{__('Applications')}}: </span>
                                <span class="ms-2 font-bold">
                                    {{
                                    $race->reservations
                                        ->sum(fn($res) => 
                                             $res->runnerReservations
                                                 ->whereNotNull('runner_id')
                                                 ->count()
                                        ) 
                                     }}
                                </span>
                                <span class="px-1 text-light-green"> / </span>
                                <span class="font-bold">{{$race->reservations()->sum('reserved_places')}}</span>
                                <span class="px-1 text-light-green"> / </span>
                                <span class="font-bold">{{$race->startplaces}}</span>
                            </p>
                        </div>
                    @endif
                    @if(auth::user()->hasRole(['captain']))
                        <div class="my-6">
                            <p class="text-white">{{__('Total places on race')}}: <b>{{$race->startplaces}}</b></p>
                            <p class="text-white">{{__('Reserved places on race')}}: <b>{{$race->reservations()->sum('reserved_places')}}</b></p>
                            <p class="text-white">{{__('Remaining places on race')}}: <b>{{$race->startplaces - $race->reservations()->sum('reserved_places')}}</b></p>
                        </div>
                        <div class="text-center my-6 mx-auto">
                            <a href="{{route('reservations.create', ['raceId' => $race->id])}}" class="mb-2 bg-mid-green text-white block px-4 py-2 hover:bg-dark-green">{{__('Create reservation')}}</a>
                            <a href="{{route('all-captains', ['year' => $year, 'raceId' => $race->id])}}" target="_blank" class="block text-white underline">{{__('View all registered companies')}}</a>
                            <a href="{{route('all-runners', ['year' => $year, 'raceId' => $race->id])}}" target="_blank" class="block text-white underline">{{__('View all runners')}}</a>
                        </div>
                    @endif
                    <div class="mt-2">
                        <p class="text-white">
                            <span class="font-bold">{{__('Applications')}}: </span>
                            <span>{{ Carbon::parse($race->application_start)->format('d.m.Y') }} - {{ Carbon::parse($race->application_end)->format('d.m.Y') }}</span>
                        </p>
                    </div>
                    <div class="mt-2 text-center">
                        <h4 class="text-lg font-bold text-white">{{__('Intervals')}}:</h4>
                    </div>
                    <div class="mt-1">
                        <table>
                            <thead>
                                <tr class="text-white">
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('From')}}</th>
                                    <th>{{__('Till')}}</th>
                                    <th>{{__('Price')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $inventories = $race->inventories()
                                    ->whereHas('inventoryType', function ($query) {
                                        $query->where('inventory_type_name', '=', 'Akontacija');
                                    })
                                    ->orderBy('order', 'ASC')
                                    ->get();
                                @endphp
                                @foreach($inventories as $inventory)
                                    @foreach($inventory->inventoryIntervals as $interval)
                                        <tr class="text-white text-sm border-t border-t-white">
                                            <td scope="col" class="py-2 font-bold">{{$interval->name}}</td>
                                            <td>{{Carbon::parse($interval->start_date)->format('d.m.Y')}}</td>
                                            <td>{{Carbon::parse($interval->end_date)->format('d.m.Y')}}</td>
                                            <td>{{$interval->price}} {{$currentOrganizer->countryData->currency}}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <h4 class="text-lg font-bold text-white">{{__('Extra')}}:</h4>
                    </div>
                    <div class="mt-1">
                        <table>
                            <thead>
                                <tr class="text-white">
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Description')}}</th>
                                    <th>{{__('Price')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $inventories = $race->inventories()
                                    ->whereHas('inventoryType', function ($query) {
                                        $query->where('inventory_type_name', '=', 'Extra');
                                    })
                                    ->orderBy('order', 'ASC')
                                    ->get();
                                @endphp
                                @foreach($inventories as $inventory)
                                    @foreach($inventory->inventoryIntervals as $interval)
                                        <tr class="text-white text-sm border-t border-t-white">
                                            <td scope="col" class="py-2 font-bold">{{$interval->name}}</td>
                                            <td>{{$inventory->description}}</td>
                                            <td>{{$interval->price}} {{$currentOrganizer->countryData->currency}}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>        
    </div>
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this race?')}}')) {
                    Livewire.dispatch('deleteRaceConfirmed');
                }
            });

            Livewire.on('confirmLock', (data) => {
                if (confirm('{{__('Are you sure you want to lock this race?')}}')) {
                    Livewire.dispatch('lockRaceConfirmed');
                }
            });

            Livewire.on('confirmUnlock', (data) => {
                if (confirm('{{__('Are you sure you want to unlock this race?')}}')) {
                    Livewire.dispatch('unlockRaceConfirmed');
                }
            });
        });
    </script>
</div>
