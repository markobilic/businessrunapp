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

    protected $listeners = ['resetError', 'deleteSelectedBankTransaction', 'deleteBankTransactionConfirmed', 'multipleDeleteBankTransactions', 'updateMultipleBankTransactions'];

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

        $this->lastRegisteredCaptains = Captain::select('team_name', 'created_at')->where('organizer_id', $this->organizerId)->orderBy('created_at', 'DESC')->take(5)->get();

        $this->lastRaces = Race::with([
            'reservations' => function ($query) {
                if (auth()->user()->hasRole('captain')) {
                    $query->where('captain_id', auth()->user()->captain->id);
                }
                $query->take(10);
                $query->orderBy('id', 'DESC');
            }
        ])
        ->where('organizer_id', $this->organizerId)
        ->when(auth()->user()->hasRole('captain'), function ($query) {
            $query->whereHas('reservations', function ($r) {
                $r->where('captain_id', auth()->user()->captain->id);
            });
        })
        ->where('locked', false)
        ->orderByDesc('starting_date')
        ->get();      
        
        $this->bankTransactions = BankTransaction::where('organizer_id', $this->organizerId)->orderBy('created_at', 'DESC')->get();
    }

    public function openReservation($reservationId)
    {
        if($reservationId)
        {
            $reservation = Reservation::findOrFail($reservationId);

            if($reservation)
            {
                return redirect()->route('reservations.show', ['reservationId' => $reservationId]);
            }
        }
    }

    public function calculateTotalEstimate($reservation): float
    {
        $totalAmount = 0;

        $promoCode = PromoCode::where('promo_code', $reservation->promo_code);

        if($promoCode)
        {
            $promoCode = $promoCode->first();
        }
        else
        {
            $promoCode = null;
        }

        $reservationInventory = $reservation->race->inventories()
            ->with('inventoryIntervals')
            ->whereHas('inventoryType', function ($query) {
                $query->where('inventory_type_name', 'Akontacija');
            })
            ->withMin('inventoryIntervals', 'start_date')
            ->orderBy('inventory_intervals_min_start_date', 'ASC')
            ->get();    

        if ($reservation->reserved_places > 0 && $reservationInventory && $reservationInventory->first()->inventoryIntervals->isNotEmpty()) 
        {       
            $filteredInterval = $reservationInventory->first()->inventoryIntervals->filter(function($ii) use ($reservation) {
                $intervalStart = Carbon::parse($ii->start_date);
                $intervalEnd   = Carbon::parse($ii->end_date);
                $now = Carbon::now();
                
                $lockedDate = $reservation->locked_date ? Carbon::parse($reservation->locked_date) : null;
                $paymentDate = $reservation->payment_date ? Carbon::parse($reservation->payment_date) : null;
            
                $lockedInInterval = $lockedDate && $lockedDate->betweenDates($intervalStart, $intervalEnd);
                $paidInInterval   = $paymentDate && $paymentDate->betweenDates($intervalStart, $intervalEnd);
                $nowInInterval    = $now->betweenDates($intervalStart, $intervalEnd);

                return $lockedInInterval || $paidInInterval || $nowInInterval;
            })->first();
            
            if (!$filteredInterval) 
            {
                $filteredInterval = $reservationInventory->first()->inventoryIntervals->last();
            }

            if($promoCode && $promoCode->promoType->promo_type_name == 'fixed price')
            {                        
                $totalAmount = $reservation->reserved_places * $promoCode->price;
            }
            else
            {
                $totalAmount = $reservation->reserved_places * $filteredInterval->price;

                if($promoCode && $promoCode->promoType->promo_type_name == 'free')
                {
                    $totalAmount -= $promoCode->amount * $filteredInterval->price;
                }    
            }
        }

        foreach ($reservation->reservationIntervals as $ri) 
        {
            if ($ri->inventory && $ri->inventory->inventoryIntervals) 
            {
                $intervalPrice = $ri->inventory->inventoryIntervals->last()->price;
                $totalAmount += $ri->amount * $intervalPrice;

                if($promoCode && $promoCode->promoType->promo_type_name == 'other')
                {
                    $totalAmount -= $promoCode->amount * $reservation->reservationIntervals->first()->inventory->inventoryIntervals->first()->price;
                }    
            }
        }

        $vatPercent = $reservation->captain->organizer->countryData->vat_percent ?? 0;
        $totalVat = $totalAmount * ($vatPercent / 100);

        $totalTotal = $totalAmount + $totalVat;    

        return $totalTotal;
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
                $foundBankTransaction = BankTransaction::findOrFail($row);

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
                $sbt->delete();
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
        <div x-data="{ showNotification: true }" x-show="showNotification" x-init="setTimeout(() => { showNotification = false; }, 10000)" class="fixed bottom-4 left-1/2 z-50 transform -translate-x-1/2">
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
        <div x-data="{ showNotification: true }" x-show="showNotification" x-on:click="showNotification = false; $wire.dispatch('resetError')" class="fixed bottom-4 left-1/2 z-50 transform -translate-x-1/2">
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
        <div class="mt-6" wire:ignore>
            <div class="w-1/4 rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2">
                <div class="border-b border-b-black">
                    <h3 class="py-2 font-bold">{{__('Last registered teams')}}</h>
                </div>            
                @foreach($lastRegisteredCaptains as $lrc)
                    <p class="inline-flex justify-between">
                        {{$lrc->team_name}}
                        <span>
                            {{$lrc->created_at->format('d.m.Y.')}}
                        </span>
                    </p>                
                @endforeach
            </div>
        </div>
        <div class="mt-6">
            <div class="rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2">
                <div class="border-b border-b-black">
                    <h3 class="py-2 font-bold">{{__('For processing')}}</h>
                </div>            
                <livewire:disapproved-bank-transactions-table/>
            </div>
        </div>
    @endif
    <div class="mt-6" wire:ignore>
        <div class="rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2">
            <div class="border-b border-b-black">
                <h3 class="py-2 font-bold">{{__('Reservations')}}</h>
            </div>            
            @foreach($lastRaces as $lr)                
                <div class="p-3">
                    <h4 class="text-xl font-bold">{{ $lr->name }}</h4>
                    <p>
                        <span><b>{{__('Location')}}</b>: {{ $lr->location }}</span>
                        <span class="ms-4"><b>{{__('Date')}}</b>: {{ Carbon::parse($lr->starting_date)->format('d.m.Y.') }}</span>
                    </p>
                    @if($lr->reservations->isEmpty())
                        <p>{{__('No reservations found for this race.')}}</p>
                    @else
                        <table class="mt-2 w-full text-sm text-left rtl:text-right text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr class="text-left">
                                    <th scope="col" class="px-6 py-3">{{__('Reservation number')}}</th>                                    
                                    <th scope="col" class="px-6 py-3">{{__('Reservation date')}}</th>
                                    <th scope="col" class="px-6 py-3">{{__('Team name')}}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{__('Reserved places')}}</th>
                                    <th scope="col" class="px-6 py-3">{{__('Status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lr->reservations as $reservation)
                                    <tr class="hover:bg-gray-100 bg-white border-b border-gray-200 cursor-pointer" wire:click="openReservation({{$reservation->id}})">
                                        <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">#{{ $reservation->id }}</td>                                        
                                        <td class="px-6 py-4">{{ $reservation->created_at->format('d.m.Y.') }}</td>
                                        <td class="px-6 py-4">{{ optional($reservation->captain)->team_name ?? '' }}</td>
                                        <td class="px-6 py-4 text-right">{{count($reservation->runnerReservations()->whereNotNull('runner_id')->where('runner_id', '>', 0)->get()) ?? 0}} / {{ $reservation->reserved_places }}</td>
                                        <td class="px-6 py-4 text-white">
                                            @php
                                                $total_price = $this->calculateTotalEstimate($reservation);
                                                $status = 0;

                                                if($total_price == $reservation->paid)
                                                {
                                                    $status = 1;
                                                }
                                                elseif($reservation->paid > 0)
                                                {
                                                    $status = 2;
                                                }
                                                else
                                                {
                                                    $status = 0;
                                                }
                                            @endphp
                                            @if($status == 1)
                                                <span class="rounded p-2 bg-light-green">{{__('Paid')}}<span>
                                            @elseif($status == 2)
                                                <span class="rounded p-2 bg-yellow-green">{{__('Partially paid')}}<span>
                                            @else
                                                <span class="rounded p-2 bg-red-500">{{__('Not paid')}}<span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div> 
                @if(!$loop->last)
                    <hr class="my-2 border-light-green"/>
                @endif
            @endforeach
        </div>
    </div>
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
        });
    </script>
</div>