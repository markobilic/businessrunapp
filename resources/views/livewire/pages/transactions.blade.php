<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\BankTransaction;
use App\Models\Reservation;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

new class extends Component {

    public BankTransaction $selectedBankTransaction;
    public $bankTransactions, $bankTransactionId;
    public $selectedBankTransactions;

    public ?int $reservation_id = null;

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
            $this->organizerId = $currentOrganizer->id;
        }
        else
        {
            $this->organizerId = null;
        }

        $this->bankTransactions = BankTransaction::where('organizer_id', $this->organizerId)->orderBy('created_at', 'DESC')->get();
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
                $this->dispatch('pg:eventRefresh-approvedBankTransactionsTable');
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

            $this->dispatch('pg:eventRefresh-approvedBankTransactionsTable');
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

        $this->dispatch('pg:eventRefresh-approvedBankTransactionsTable');
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
    <div class="mt-6 rounded bg-white p-4" wire:ignore>
        <ul class="rounded-t-sm flex shadow-md flex-row space-x-4 text-sm font-medium bg-gray-50" 
            id="transactions-tab" 
            data-tabs-toggle="#transactions-tab-content" 
            role="tablist" 
            data-tabs-active-classes="bg-light-green text-white" 
            data-tabs-inactive-classes="bg-gray-50 hover:text-black hover:bg-yellow-green">
            <li role="presentation">
                <button id="disapproved-tab" type="button" data-tabs-target="#disapproved" role="tab" aria-controls="disapproved" aria-selected="true"
                    class="inline-flex items-center px-4 py-3 rounded-t-sm active w-full" aria-current="disapproved">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    {{__('For processing')}}
                </button>
            </li>
            <li role="presentation">
                <button id="approved-tab" type="button" data-tabs-target="#approved" role="tab" aria-controls="approved" aria-selected="false"
                    class="inline-flex items-center px-4 py-3 rounded-t-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    {{__('Processed')}}
                </button>
            </li> 
        </ul>
        <div id="transactions-tab-content" class="bg-gray-50 text-medium rounded-b-sm w-full">
            <div class="p-4 bg-white shadow rounded-b-sm" id="disapproved" role="tabpanel" aria-labelledby="disapproved-tab">
                <h2 class="text-xl py-2 font-bold">{{__('For processing')}}</h2>
                <livewire:disapproved-bank-transactions-table/>
            </div>
            <div class="p-4 bg-white shadow rounded-b-sm" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                <h2 class="text-xl py-2 font-bold">{{__('Processed')}}</h2>
                <livewire:approved-bank-transactions-table/>
            </div>
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

            Livewire.on('confirmDuplication', (data) => {
                if (confirm('{{__('Are you sure you want to duplicate this bank transaction?')}}')) {
                    Livewire.dispatch('duplicateBankTransactionConfirmed');
                }
            });
        });
    </script>
</div>