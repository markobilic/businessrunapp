<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Organizer;
use App\Models\Reservation;
use App\Models\Captain;
use App\Models\Race;
use App\Models\CaptainAddress;
use App\Models\Inventory;
use App\Models\PromoCode;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Services\MailService;

new class extends Component {

    public $organizerId;
    public Organizer $currentOrganizer;

    public ?int $captain_id = null, $race_id = null, $captain_address_id = null;
    public ?int $reserved_places = null;
    public ?string $promo_code = null;
    public bool $acceptTerms = false;

    public $captains, $races, $captainAddresses;

    public Captain $selectedCaptain;
    public Race $selectedRace;

    public ?Inventory $extraInventory = null;
    public ?int $extraInventoryAmount = null;

    public ?int $raceId = null;

    public $akontacija = [], $extra = [], $total = [];

    protected $listeners = ['resetError'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount(?int $raceId = null)
    {
        $this->raceId = $raceId ?? request()->route('raceId');
        $currentOrganizer = request()->attributes->get('current_organizer');

        $this->currentOrganizer = $currentOrganizer;

        if($this->currentOrganizer)
        {
            $this->organizerId = $currentOrganizer->id;

            if(auth()->user()->hasRole(['superadmin', 'organizer']))
            {
                $this->captains = Captain::where('organizer_id', $this->organizerId)->orderBy('company_name', 'ASC')->get();
                $this->races = Race::where('locked', false)
                    ->whereDate('starting_date', '>=', now())
                    ->whereDate('application_start', '<=', now())
                    ->where('organizer_id', $this->organizerId)
                    ->orderBy('name', 'ASC')
                    ->get();
            }
            else
            {          
                $this->selectedCaptain = auth()->user()->captain;
                $this->captain_id = $this->selectedCaptain->id;
                $this->captainAddresses = auth()->user()->captain->captainAddresses;

                if($this->raceId)
                {         
                    $this->race_id = $this->raceId;                   
                    $race = Race::findOrFail($this->race_id);

                    if($race)
                    {
                        $extraInventory = $race->inventories()
                            ->whereHas('inventoryType', function ($query) {
                                $query->where('inventory_type_name', '=', 'Extra');
                            })
                            ->orderBy('order', 'ASC')
                            ->first();

                        if($extraInventory)
                        {
                            $this->extraInventory = $extraInventory;
                        }
                    }                    
                }   
                else
                {
                    $this->races = Race::where('locked', false)
                        ->whereDate('application_end', '>=', now())
                        ->whereDate('application_start', '<=', now())
                        ->whereRaw('(SELECT COALESCE(SUM(reserved_places), 0) FROM reservations WHERE reservations.race_id = races.id  AND reservations.deleted_at IS null) < races.startplaces')
                        ->where('organizer_id', $this->organizerId)
                        ->orderBy('name', 'ASC')
                        ->get();
                }           
            }
        }
        else
        {
            $this->organizerId = null;
            return redirect()->route('reservations.list');
        }   
    }

    public function updatedCaptainId()
    {
        if($this->captain_id)
        {
            $captain = Captain::findOrFail($this->captain_id);

            if($captain)
            {
                $this->selectedCaptain = $captain;
                $captainAddresses = $captain->captainAddresses;

                if($captainAddresses)
                {
                    $this->captainAddresses = $captainAddresses;
                }
            }
        }
    }

    public function updatedRaceId()
    {
        if($this->race_id)
        {
            $race = Race::findOrFail($this->race_id);

            if($race)
            {
                $extraInventory = $race->inventories()
                    ->whereHas('inventoryType', function ($query) {
                        $query->where('inventory_type_name', '=', 'Extra');
                    })
                    ->orderBy('order', 'ASC')
                    ->first();

                if($extraInventory)
                {
                    $this->extraInventory = $extraInventory;
                }
            }          
        }
        
        $this->calculatePrice();
    }

    public function insert(MailService $mailService)
    {
        if ($this->getErrorBag()->isNotEmpty())
        {
            return;
        }

        if($this->acceptTerms)
        {
            if($this->promo_code)
            {
                $promoCode = PromoCode::where('promo_code', $this->promo_code)->where('race_id', $this->race_id)->first();

                if(!$promoCode)
                {
                    $this->promo_code = null;
                }
            }
                
            $reservation = $this->selectedCaptain->reservations()->create([
                'race_id' => $this->race_id,
                'payment_status' => 0,
                'locked' => 0,
                'legal_entity' => 1,
                'invoice_status' => 0,
                'promo_code' => $this->promo_code,
                'reserved_places' => $this->reserved_places,
                'captain_address_id' => $this->captain_address_id,
                'crf' => 0,
                'note' => ''
            ]);

            if($this->extraInventory && $this->extraInventoryAmount > 0)
            {
                $reservation->reservationIntervals()->create([
                    'inventory_id' => $this->extraInventory->id,
                    'amount' => $this->extraInventoryAmount
                ]);
            }

            for($i = 0; $i<$reservation->reserved_places; $i++)
            {
                $reservation->runnerReservations()->create([
                    'runner_id' => null,
                    'spot' => $i+1
                ]);
            }
            
            if($this->organizerId == 2)
            {
                event(new \App\Events\NewReservation($reservation));
            }

            $mailService->sendReservationCreatedCaptainNotice($reservation);

            session()->flash('message', 'Reservation created successfully.');
            return redirect()->to(route('reservations.show', $reservation->id) . '?tab=runners');
        }
    }

    public function calculatePrice()
    {
        $this->akontacija = [];
        $this->extra = [];
        $this->total = [];

        if($this->race_id && $this->reserved_places > 0)
        {    
            $race = Race::findOrFail($this->race_id);

            if($race)
            {
                $this->total['amount'] = 0;
                $this->total['vat'] = 0;
            
                $currentDate = Carbon::today();

                $inventory = $race->inventories()
                    ->with(['inventoryIntervals' => function($q) use ($currentDate) {
                        $q->where('start_date', '<=', $currentDate)
                        ->where('end_date', '>=', $currentDate);
                    }])
                    ->whereHas('inventoryIntervals', function($q) use ($currentDate) {
                        $q->where('start_date', '<=', $currentDate)
                        ->where('end_date', '>=', $currentDate);
                    })
                    ->whereHas('inventoryType', function ($query) {
                        $query->where('inventory_type_name', 'Akontacija');
                    })
                    ->first();
                    
                if($inventory)
                {
                    $inventoryInterval = $inventory->inventoryIntervals->first();
                }
                else
                {
                    $inventoryInterval = null;
                }
                
                if($this->promo_code)
                {
                    $promoCode = PromoCode::with('promoCodeCondition')->where('promo_code', $this->promo_code)->where('race_id', $this->race_id)->first();
                
                    if($promoCode)
                    {
                        $cond = $promoCode->promoCodeCondition;

                        if($cond) 
                        {
                            $isSponsor = $cond->sponsor && $this->selectedCaptain->sponsor;
                            $isPartner = $cond->partner && $this->selectedCaptain->partner;
                            $min = (int) $cond->min_runners;
    
                            if($cond->sponsor && $min === 0) 
                            {
                                if(!$isSponsor) 
                                {
                                    $promoCode = null;
                                }
                            }
    
                            if($cond->partner && $min === 0) 
                            {
                                if(!$isPartner) 
                                {
                                    $promoCode = null;
                                }
                            }
    
                            if($min > 0) 
                            {
                                $exempt = ($cond->sponsor && $isSponsor) || ($cond->partner && $isPartner);
                        
                                if (!$exempt && $this->reserved_places < $min) 
                                {
                                    $promoCode = null;
                                }
                            }
                        }
                    }   
                    else
                    {
                        $promoCode = null;
                    }                    
                }
                else
                {
                    $promoCode = null;
                }

                if($inventoryInterval && $this->reserved_places > 0)
                {                   
                    if($promoCode)
                    {
                        if($promoCode && $promoCode->promoType->promo_type_name == 'fixed price')
                        {              
                            $this->akontacija['promo_amount'] = $this->reserved_places;          
                            $this->akontacija['promo_price'] = $promoCode->price;
                            $this->akontacija['promo_total'] = ($inventoryInterval->price * $this->reserved_places) - ($promoCode->price * $this->reserved_places);
                        }
                        else
                        {                         
                            $this->akontacija['promo_amount'] = $promoCode->amount;
                            $this->akontacija['promo_price'] = $inventoryInterval->price;
                            $this->akontacija['promo_total'] = $promoCode->amount * $inventoryInterval->price;
                        }

                        $this->akontacija['promo_name'] = $promoCode->promo_code . " (" . $promoCode->description . ")";
                        
                        $this->total['amount'] -= $this->akontacija['promo_total'];
                    }            

                    $this->akontacija['interval_name'] = $inventoryInterval->inventory->name . " - " . $inventoryInterval->name;
                    $this->akontacija['interval_amount'] = $this->reserved_places;
                    $this->akontacija['interval_price'] = $inventoryInterval->price;
                    $this->akontacija['interval_total'] = $inventoryInterval->price * $this->reserved_places;             
                    
                    $this->total['amount'] += $this->akontacija['interval_total'];
                }

                if($this->extraInventory && $this->extraInventoryAmount > 0)
                {
                    $this->extra['interval_name'] = $this->extraInventory->name . " (" . $this->extraInventory->description . ")";
                    $this->extra['interval_amount'] = $this->extraInventoryAmount;
                    $this->extra['interval_price'] = $this->extraInventory->inventoryIntervals->last()->price;
                    $this->extra['interval_total'] = $this->extraInventory->inventoryIntervals->last()->price * $this->extraInventoryAmount;    
                    
                    $this->total['amount'] += $this->extra['interval_total'];

                    if($promoCode && $promoCode->promoType->promo_type_name == 'other')
                    {
                        $this->extra['promo_name'] = $promoCode->promo_code . " (" . $promoCode->description . ")";
                        $this->extra['promo_amount'] = $promoCode->amount;
                        $this->extra['promo_price'] = $this->extraInventory->inventoryIntervals->last()->price;
                        $this->extra['promo_total'] = $promoCode->amount * $this->extraInventory->inventoryIntervals->last()->price;
                        
                        $this->total['amount'] -= $this->extra['promo_total'];
                    }    
                }
                
                $this->total['vat'] = $this->total['amount'] * ($this->currentOrganizer->countryData->vat_percent / 100);
            }   
        }
    }
    
    public function updatedReservedPlaces()
    {
        $this->calculatePrice();
    }

    public function updatedExtraInventoryAmount()
    {
        $this->calculatePrice();
    }

    public function updatedPromoCode()
    {
        $this->resetErrorBag('promo_code');
        
        if($this->promo_code)
        {
            $promoCode = PromoCode::with('promoCodeCondition')->where('promo_code', $this->promo_code)->where('race_id', $this->race_id)->first();

            if(!$promoCode)
            {
                $this->addError(
                    'promo_code', 
                    __('You have entered wrong promo code.')
                ); 
            }
            else
            {
               $cond = $promoCode->promoCodeCondition;

                if($cond) 
                {
                    $isSponsor = $cond->sponsor && $this->selectedCaptain->sponsor;
                    $isPartner = $cond->partner && $this->selectedCaptain->partner;
                    $min = (int) $cond->min_runners;
    
                    if($cond->sponsor && $min === 0) 
                    {
                        if(!$isSponsor) 
                        {
                            $this->addError(
                                'promo_code', 
                                __('You have entered wrong promo code.')
                            ); 
                        }
                    }
    
                    if($cond->partner && $min === 0) 
                    {
                        if(!$isPartner) 
                        {
                            $this->addError(
                                'promo_code', 
                                __('You have entered wrong promo code.')
                            ); 
                        }
                    }
    
                    if($min > 0) 
                    {
                        $exempt = ($cond->sponsor && $isSponsor) || ($cond->partner && $isPartner);
                
                        if (!$exempt && $this->reserved_places < $min) 
                        {
                            $this->addError(
                                'promo_code', 
                                __('You have entered wrong promo code.')
                            ); 
                        }
                    }
                } 
            }
        }

        $this->calculatePrice();
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
    <div class="mt-6 rounded bg-white p-4">
        <form wire:submit.prevent="insert" class="space-y-6">
            <div class="grid lg:grid-cols-5 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    @if(auth()->user()->hasRole(['superadmin', 'organizer']))
                        <div>
                            <x-input-label for="captain_id" :value="__('Captain')" required/>
                            <select wire:model.live="captain_id" name="captain_id" id="captain_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">{{ __('Choose option...') }}</option>
                                @foreach($captains as $captain)                                            
                                    <option value="{{ $captain->id }}">{{ $captain->company_name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('captain_id')" />
                        </div>
                    @endif 
                    @if($selectedCaptain)
                        <div>
                            <x-input-label for="captain_address_id" :value="__('Payment address')"/>
                            <select wire:model.live="captain_address_id" name="captain_address_id" id="captain_address_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option wire:key="captain-{{$captain_id}}" value="">{{ $selectedCaptain->billing_company }}, {{ $selectedCaptain->billing_address }}, {{ $selectedCaptain->billing_city }}, {{ $selectedCaptain->billing_postcode }}</option>
                                @if($captainAddresses)
                                    @foreach($captainAddresses as $captainAddress)                                            
                                        <option value="{{ $captainAddress->id }}">{{ $captainAddress->company_name }}, {{ $captainAddress->address }}, {{ $captainAddress->city }}, {{ $captainAddress->postal_code }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('captain_address_id')" />
                            <small class="text-gray-500">{{ __('Company data which accepts payment') }}</small>
                        </div>
                    @endif
                    @if(auth()->user()->hasRole(['superadmin', 'organizer']) || !$raceId)
                        <div>
                            <x-input-label for="race_id" :value="__('Race')" required/>
                            <select wire:model.live="race_id" name="race_id" id="race_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">{{ __('Choose option...') }}</option>
                                @foreach($races as $race)                                            
                                    <option value="{{ $race->id }}">{{ $race->name }} {{ Carbon::parse($race->starting_date)->format('d.m.Y.') }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('race_id')" />
                        </div>
                    @endif           
                    <div>
                        <x-input-label for="reserved_places" :value="__('Reserved places') . ' - ' . __('Number of runners/walkers')" required/>
                        <x-text-input type="number" step="1" min="0" max="9999" wire:model.blur="reserved_places" name="reserved_places" id="reserved_places" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('reserved_places')" />
                    </div>
                    @if($extraInventory)
                        <div>
                            <x-input-label for="extraInventoryAmount" :value="$extraInventory->name.' ('.$extraInventory->description.')' . ' - ' . __('Number of fans')"/>
                            <x-text-input type="number" step="1" min="0" max="9999" wire:model.blur="extraInventoryAmount" name="extraInventoryAmount" id="extraInventoryAmount" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                            <x-input-error class="mt-2" :messages="$errors->get('extraInventoryAmount')" />
                        </div>
                    @endif
                    <div>
                        <x-input-label for="promo_code" :value="__('Promo code')"/>
                        <x-text-input type="text" wire:model.blur="promo_code" name="promo_code" id="promo_code" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                        <x-input-error class="mt-2" :messages="$errors->get('promo_code')" />
                    </div>
                    <div class="flex-shrink flex items-center mt-4">
                        <input wire:model.live="acceptTerms" id="acceptTerms" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300" required>
                        <label for="acceptTerms" class="ml-3 block text-sm font-medium text-gray-700">
                            <a class="underline text-blue-500" href="{{$currentOrganizer->tos_link}}" target="_blank">{{ __('Accept terms') }}</a>
                        </label>
                        <x-input-error :messages="$errors->get('acceptTerms')" class="mt-2" />
                    </div> 
                    <div class="mt-6 flex justify-end">
                        <x-primary-button class="ms-3">
                            {{ __('Reserve') }}
                        </x-primary-button>
                    </div>
                </div>
                <div class="lg:col-span-3 rounded-sm border border-dashed border-dark-green shadow-md bg-gray-50 lg:p-4">
                    <table class="lg:mt-2 w-full text-xs lg:text-sm text-left rtl:text-right text-gray-500" wire:key="{{$race_id}}{{$reserved_places}}{{$extraInventoryAmount}}{{$promo_code}}">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr class="text-left">
                                <th scope="col" class="p-2 lg:px-6 lg:py-3">{{__('Item')}}</th>                                    
                                <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{__('Quantity')}}</th>
                                <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{__('Price')}}</th>
                                <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{__('Subtotal')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($akontacija['interval_name']))
                                <tr class="bg-white border-b border-gray-200">                                
                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $akontacija['interval_name'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ $akontacija['interval_amount'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format($akontacija['interval_price'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format($akontacija['interval_total'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                </tr>
                            @endif
                            @if(isset($akontacija['promo_name']))
                                <tr class="bg-white border-b border-gray-200">                                
                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $akontacija['promo_name'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ $akontacija['promo_amount'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format(-$akontacija['promo_price'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format(-$akontacija['promo_total'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                </tr>
                            @endif
                            @if(isset($extra['interval_name']))
                                <tr class="bg-white border-b border-gray-200">
                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $extra['interval_name'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ $extra['interval_amount'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format($extra['interval_price'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format($extra['interval_total'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                </tr>
                            @endif
                            @if(isset($extra['promo_name']))
                                <tr class="bg-white border-b border-gray-200">
                                    <td scope="row" class="p-2 lg:px-6 lg:py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $extra['promo_name'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ $extra['promo_amount'] }}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                         {{ number_format(-$extra['promo_price'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                    <td class="p-2 lg:px-6 lg:py-4 text-right">
                                        {{ number_format(-$extra['promo_total'], 2, '.', ',') }} {{$currentOrganizer->countryData->currency}}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="text-gray-700 uppercase bg-gray-50">
                            @if(isset($total['vat']))
                                <tr class="text-left">
                                    <th colspan="3" scope="col" class="p-2 lg:px-6 lg:py-3">{{$currentOrganizer->countryData->vat_label}}</th>   
                                    <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{number_format($total['vat'], 2, '.', ',')}} {{$currentOrganizer->countryData->currency}} ({{$currentOrganizer->countryData->vat_percent}}%)</th>
                                </tr>
                            @endif
                            @if(isset($total['amount']))
                                <tr class="text-left">
                                    <th colspan="3" scope="col" class="p-2 lg:px-6 lg:py-3">{{__('Total')}}</th>  
                                    <th scope="col" class="p-2 lg:px-6 lg:py-3 text-right">{{number_format($total['amount'] + $total['vat'], 2, '.', ',')}} {{$currentOrganizer->countryData->currency}}</th>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </form>     
    </div>
</div>
