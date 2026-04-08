<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Organizer;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Models\Captain;
use App\Models\Race;
use App\Models\Reservation;
use App\Models\PromoCode;
use App\Models\BankTransaction;

new class extends Component {

    public $organizerId;
    public Organizer $currentOrganizer;
    public $lastRegisteredCaptains;
    public $lastRaces;

    public BankTransaction $selectedBankTransaction;
    public $bankTransactions, $bankTransactionId;
    public $selectedBankTransactions;

    public $emptyReservations = [];
    public $unconfirmedReservations = [];
    public $captainActiveRaces = [];
    public $earlyBirdRaces = [];

    protected $listeners = ['resetError', 'sendPrebillResponse' => 'getPrebillResponse', 'duplicateSelectedBankTransaction', 'duplicateBankTransactionConfirmed', 'deleteSelectedBankTransaction', 'deleteBankTransactionConfirmed', 'multipleDeleteBankTransactions', 'updateMultipleBankTransactions', 'deleteBankTransactionsConfirmed'];

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
            
            if($this->organizerId == 7)
            {
                if(Auth::user()->hasRole(['organizer', 'superadmin', 'collaborator', 'partner']))
                {
                    return $this->redirect(route('reservations.list'));
                }
            }
        }
        else
        {
            $this->organizerId = null;
        }
        
        $this->bankTransactions = BankTransaction::where('organizer_id', $this->organizerId)->orderBy('created_at', 'DESC')->get();
        
        if(Auth::user()->hasRole(['captain']))
        {
            $captain = auth::user()->captain;
            $today = now()->startOfDay();
            
            $this->unconfirmedReservations = $captain->reservations()
                ->where('payment_status', 0)
                ->whereHas('race', function ($query) use ($today) {
                    $query->where('starting_date', '>', $today);
                })
                ->get();

            $this->emptyReservations = $captain->reservations()
                ->whereHas('runnerReservations', function ($query) {
                    $query->whereNull('runner_id')->orWhere('runner_id', 0);
                })
                ->whereHas('race', function ($query) use ($today) {
                    $query->where('starting_date', '>', $today);
                })
                ->withCount([
                    'runnerReservations as empty_spots_count' => function ($query) {
                        $query->whereNull('runner_id')->orWhere('runner_id', 0);
                    }
                ])
                ->get();

            $today = now()->startOfDay();

            $this->captainActiveRaces = Race::where('application_end', '>=', $today)
                ->where('starting_date', '>', $today)
                ->whereHas('reservations', function ($query) use ($captain) {
                    $query->where('captain_id', $captain->id);
                })
                ->get()
                ->map(function ($race) {
                    $race->days_until_application_end = (int) now()->startOfDay()->diffInDays(Carbon::parse($race->application_end)->startOfDay(), true);
                    return $race;
                });
                
            $earlyBirdRaces = Race::query()
                ->where('starting_date', '>', $today)
                ->whereHas('reservations', fn($q) => 
                    $q->where('captain_id', $captain->id)
                )
                ->whereHas('inventories.inventoryIntervals', fn($q) => 
                    $q->where('end_date', '>=', $today)
                )
                ->whereDoesntHave('inventories.inventoryIntervals', fn($q) => 
                    $q->where('end_date', '<', $today)
                )
                ->with(['inventories' => function ($q) {
                    $q->whereHas('inventoryType', fn($sub) =>
                        $sub->where('inventory_type_name', 'Akontacija')
                    )
                    ->with(['inventoryIntervals' => function ($iq) {
                        $iq->orderBy('start_date');
                    }]);
                }])
                ->get()
                ->map(function ($race) use($today) {
                    $first = $race->inventories
                        ->flatMap->inventoryIntervals
                        ->first();
            
                    if($today > $first->end_date)
                    {
                        $race->days_until_first_interval = null;
                    }
                    else
                    {
                        $race->days_until_first_interval = $first
                        ? $today->diffInDays(
                            Carbon::parse($first->end_date)->startOfDay(),
                            true
                          )
                        : null;
                    }
            
                    return $race;
                });

            $this->earlyBirdRaces = $earlyBirdRaces;
        }
    }
    
    public function getPrebillResponse($response)
    {
        if (isset($response['status']) && $response['status'] === 'failed') 
        {
            $errorMessage = is_array($response['error'])
                ? collect($response['error'])->flatten()->first()
                : $response['error'];
    
            $this->addError('error', $errorMessage ?? __('Unknown error occurred.'));
        }
    }
    
    public function duplicateSelectedBankTransaction($bankTransactionId)
    {
        $this->bankTransactionId = $bankTransactionId;
        $this->dispatch('confirmDuplication', ['bankTransactionId' => $bankTransactionId]);
    }

    public function duplicateBankTransactionConfirmed()
    {
        if($this->bankTransactionId)
        {
            $bankTransaction = BankTransaction::findOrFail($this->bankTransactionId);

            if($bankTransaction)
            {
                $newTransaction = $bankTransaction->replicate();
                $newTransaction->save();
                
                $this->dispatch('pg:eventRefresh-disapprovedBankTransactionsTable');
                session()->flash('message', 'Bank transaction duplicated successfully.');                
            }            
        }

        $this->reset(['bankTransactionId', 'selectedBankTransaction']);
    }

    public function deleteSelectedBankTransaction($bankTransactionId)
    {
        $this->bankTransactionId = $bankTransactionId;
        $this->dispatch('confirmDeletion', ['bankTransactionId' => $bankTransactionId]);
    }
    
    public function deleteBankTransactionConfirmed()
    {
        if($this->bankTransactionId)
        {
            $bankTransaction = BankTransaction::findOrFail($this->bankTransactionId);

            if($bankTransaction)
            {
                $bankTransaction->delete();
                $this->dispatch('pg:eventRefresh-disapprovedBankTransactionsTable');
                session()->flash('message', 'Bank transaction deleted successfully.');                
            }            
        }

        $this->reset(['bankTransactionId', 'selectedBankTransaction']);
    }

    public function multipleDeleteBankTransactions($selectedRows)
    {
        $this->reset(['selectedBankTransactions']);

        if($selectedRows)
        {
            $selectedBankTransactions = [];

            foreach($selectedRows as $row)
            {
                $foundBankTransaction = BankTransaction::find($row);

                if($foundBankTransaction)
                {                    
                    $selectedBankTransactions[] = $foundBankTransaction->id;
                }
            }

            if($selectedBankTransactions)
            {
                $this->selectedBankTransactions = $selectedBankTransactions;

                $this->dispatch('confirmBulkDeletion', ['selectedBankTransactions' => $selectedBankTransactions]);
            }
        }
        else
        {
            $this->addError('error', 'To use this function, you must first select at least one bank transaction from the list.');            
        }
    }

    public function deleteBankTransactionsConfirmed()
    {
        if($this->selectedBankTransactions)
        {
            foreach($this->selectedBankTransactions as $sbt)
            {
                $foundBankTransaction = BankTransaction::find($sbt);

                if($foundBankTransaction)
                {
                    $foundBankTransaction->delete();
                }                
            }

            $this->dispatch('pg:eventRefresh-disapprovedBankTransactionsTable');
            session()->flash('message', 'Bank transactions deleted successfully.');              
        }

        $this->reset(['selectedBankTransactions']);
    }

    public function updateMultipleBankTransactions($selectedRows)
    {
        $this->reset(['selectedBankTransactions']);

        if($selectedRows)
        {
            $selectedBankTransactions = [];

            foreach($selectedRows as $row)
            {
                $foundBankTransaction = BankTransaction::findOrFail($row);

                if($foundBankTransaction)
                {                    
                    $selectedBankTransactions[] = $foundBankTransaction->id;
                }
            }

            if($selectedBankTransactions)
            {
                $this->selectedBankTransactions = $selectedBankTransactions;

                $this->dispatch('open-modal', 'choose-reservation-modal');
            }
        }
        else
        {
            $this->addError('error', 'To use this function, you must first select at least one bank transaction from the list.');            
        }
    }

    public function setBulkReservation()
    {
        if($this->reservation_id && $this->reservation_id > 0)
        {
            $reservation = Reservation::findOrFail($this->reservation_id);

            if(!$reservation)
            {
                $this->reservation_id = null;
            }
        }
        else
        {
            $this->reservation_id = null;
        }

        foreach($this->selectedBankTransactions as $sbt)
        {
            $bankTransaction = BankTransaction::findOrFail($sbt);

            if($bankTransaction)
            {
                $bankTransaction->reservation_id = $this->reservation_id;
                $bankTransaction->save();
            }
        }

        $this->dispatch('pg:eventRefresh-disapprovedBankTransactionsTable');
        session()->flash('message', 'Bank transactions updated successfully.');        
        $this->dispatch('close-modal', 'choose-reservation-modal');
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
    @if(Auth::user()->hasRole(['superadmin', 'organizer']))    
        @if($organizerId == 2)
            <div class="mt-6">
                <div class="rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2">
                    <div class="border-b border-b-black">
                        <h3 class="py-2 font-bold">{{__('For processing')}}</h>
                    </div>            
                    <livewire:disapproved-bank-transactions-table/>
                </div>
            </div>
        @endif
    @elseif(Auth::user()->hasRole(['captain']))       
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2 w-full lg:w-2/3">
                <div>
                    <div class="border-b border-b-black">
                        <h3 class="text-xl py-2 font-bold">{{__('Welcome')}}</h>
                    </div>            
                    <p class="mt-2">{{ __('Congratulations on this responsible role in your company team!') }}</p>
                    <p>{{ __('This page is an introduction to your teams application process and contains the most important information you need to get you there as smoothly as possible') }}</p>
                </div>
                @if($organizerId == 2)
                    <div class="p-4 my-2 bg-green-50">
                        <h4 class="text-lg font-bold">{{ __('Useful links and introductory steps') }}</h4>
                        <ul class="list-inside list-disc">
                            <li>{{ __('Detailed video and written instructions for application are available') }} <a class="uppercase text-blue-500 underline" href="https://serbiabusinessrun.com/uputstvo/" target="_blank">{{ __('Here') }}</a></li>
                            <li>{{ __('You can register your team through the Reservations tab on your left.') }}</li>
                            <li>{{ __('Change company data through the Company Data tab on your left') }}</li>
                            <li>{{ __('Change the captains data and change the password by clicking on the icon of your account in the upper right corner') }}</li>
                            <li>{{ __('The conditions of participation are found') }} <a class="uppercase text-blue-500 underline" href="https://serbiabusinessrun.com/uslovi/" target="_blank">{{ __('Here') }}</a></li>
                            <li>{{ __('All the details of the Series can be found on the official website') }} <a class="uppercase text-blue-500 underline" href="https://serbiabusinessrun.com/" target="_blank">{{ __('Here') }}</a></li>
                            <li><a class="font-bold text-blue-500 underline" href="http://serbiabusinessrun.com/faq" target="_blank">{{ __('SBR FAQ - Frequently asked questions and answers') }}</a></li>
                            <li>{{ __('A guide for you as a team captain is located') }} <a class="uppercase text-blue-500 underline" href="https://www.notion.so/SBR-Vodi-za-kapitene-ce7c3791571d478d9907b0f963e19c92?pvs=4" target="_blank">{{ __('Here') }}</a></li>
                        </ul>
                    </div>
                @endif
                <p>{{ __('Good luck to you and your team!') }}</p>
               
            </div>
            <div class="w-full lg:w-1/3 flex flex-col gap-4">
                @if($emptyReservations && count($emptyReservations) > 0)
                    <div class="rounded p-3 bg-white shadow-md">
                        <h3 class="text-xl py-2 font-bold">{{__('Empty reservations')}}</h3>
                        <p>{{ __('You have a reservation, but you do not have all the contestants filled. You still have :count blank spots, add contestants or tell your colleagues to apply.', ['count' => $emptyReservations->sum('empty_spots_count')]) }}</p>
                        <ul class="list-inside list-disc p-2 bg-orange-50 mt-2">
                            @foreach ($emptyReservations as $er)
                                <li><a href="{{route('reservations.show', ['reservationId' => $er->id])}}">{{ __('Reservation') }} #{{ $er->id }}</a> <small>({{ __('Filled') }}: {{ $er->reserved_places - $er->empty_spots_count }}/{{ $er->reserved_places }})</small></li>
                            @endforeach
                        </ul>
                    </div>  
                @endif
                @if($unconfirmedReservations && count($unconfirmedReservations) > 0)
                    <div class="rounded p-3 bg-white shadow-md">
                        <h3 class="text-xl py-2 font-bold">{{__('Unconfirmed reservations')}}</h3>
                        <p>{{ __('You have reservation, but your reservation has not been confirmed because we have not recorded the payment.') }}</p>
                        <ul class="list-inside list-disc p-2 bg-red-50 mt-2">
                            @foreach ($unconfirmedReservations as $ur)
                                <li><a href="{{route('reservations.show', ['reservationId' => $ur->id])}}">{{ __('Reservation') }} #{{ $ur->id }}</a></li>
                            @endforeach
                        </ul>
                    </div>  
                @endif
                @if($captainActiveRaces && count($captainActiveRaces) >0)
                    <div class="rounded p-3 bg-white shadow-md">
                        <h3 class="text-xl py-2 font-bold">{{__('Active races')}}</h3>
                        <ul class="list-inside list-disc p-2 bg-gray-50">
                            @foreach ($captainActiveRaces as $car)
                                <li>
                                    <span class="font-bold">{{ $car->location }}</span> <small>({{ __('Race date') }}: {{ Carbon::parse($car->starting_date)->format('d.m.Y.') }})</small>
                                    <br/>
                                    {{ __(':days more days until registration closes.', ['days' => $car->days_until_application_end]) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>  
                @endif
                @if($earlyBirdRaces && count($earlyBirdRaces) >0)
                    <div class="rounded p-3 bg-white shadow-md">
                        <h3 class="text-xl py-2 font-bold">{{__('Early bird')}}</h3>
                        <ul class="list-inside list-disc p-2 bg-gray-50">
                            @foreach ($earlyBirdRaces as $ebr)
                                <li>
                                    <span class="font-bold">{{ $ebr->location }}</span> <small>({{ __('Race date') }}: {{ Carbon::parse($ebr->starting_date)->format('d.m.Y.') }})</small>
                                    <br/>
                                    {{ __(':days more days until early bird end.', ['days' => $ebr->days_until_first_interval]) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>  
                @endif
            </div>
        </div>
    @endif
    @if($organizerId == 2)
        <x-modal name="choose-reservation-modal">
            @if($selectedBankTransactions)
                <div>                
                    <div class="px-6 py-2">
                        <button type="button" x-on:click="$dispatch('close')" class="text-black absolute top-0 right-0 px-2 py-0">
                            <span class="text-3xl">&times;</span>
                        </button>
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-black dark:text-gray-100">{{__('Choose reservation')}}</h2>                    
                        </div>
                    </div>
                    <form wire:submit.prevent="setBulkReservation" class="px-6 py-3 space-y-6">
                        <div>
                            <x-input-label for="reservation_id" :value="__('Reservation')"/>
                            <x-text-input type="text" wire:model="reservation_id" name="reservation_id" id="reservation_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                            <x-input-error class="mt-2" :messages="$errors->get('reservation_id')" />
                        </div>
                        <div class="mt-6 flex justify-end">                        
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button class="ms-3">
                                {{ __('Save') }}
                            </x-primary-button>
                        </div>
                    </form>                                
                </div>
            @endif
        </x-modal>
        <script>
            document.addEventListener('livewire:init', function () {
                Livewire.on('confirmDeletion', (data) => {
                    if (confirm('{{__('Are you sure you want to delete this bank transaction?')}}')) {
                        Livewire.dispatch('deleteBankTransactionConfirmed');
                    }
                });
    
                Livewire.on('confirmBulkDeletion', (data) => {
                    if (confirm('{{__('Are you sure you want to delete these bank transactions?')}}')) {
                        Livewire.dispatch('deleteBankTransactionsConfirmed');
                    }
                });
                
                Livewire.on('confirmDuplication', (data) => {
                    if (confirm('{{__('Are you sure you want to duplicate this bank transaction?')}}')) {
                        Livewire.dispatch('duplicateBankTransactionConfirmed');
                    }
                });
            });
        </script>
    @endif
</div>