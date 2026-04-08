<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Captain;
use App\Models\Organizer;
use App\Models\Runner;
use App\Models\CaptainAddress;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Services\ExportService;
use App\Services\ReservationService_v2;

new class extends Component {

    public Captain $selectedCaptain;
    public $captainId;
    public $organizerId;
    public Organizer $currentOrganizer;

    public Runner $selectedRunner;
    public $runners, $runnerId;
    
    public CaptainAddress $selectedAddress;
    public $addresses, $addressId;

    protected $listeners = ['resetError', 'deleteSelectedRunner', 'deleteRunnerConfirmed', 'selectedAddressDelete', 'confirmedAddressDeletion'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount($teamId)
    {
        if($teamId)
        {
            $currentOrganizer = request()->attributes->get('current_organizer');

            if($currentOrganizer)
            {
                $this->organizerId = $currentOrganizer->id;
                $this->currentOrganizer = $currentOrganizer;
            }
            else
            {
                $this->organizerId = null;
            }   
            
            $captain = Captain::findOrFail($teamId);

            if($captain)
            {
                $this->selectedCaptain = $captain;
                $this->captainId = $teamId;
            }
            else
            {
                abort(404, 'Invalid team.');
            }
        }        
    }

    public function deleteSelectedRunner($runnerId)
    {
        $this->runnerId = $runnerId;
        $this->dispatch('confirmDeletion', ['runnerId' => $runnerId]);
    }
    
    public function deleteRunnerConfirmed()
    {
        if($this->runnerId)
        {
            $runner = Runner::findOrFail($this->runnerId);

            if($runner)
            {
                $runner->delete();
                $this->dispatch('pg:eventRefresh-captainRunnersTable');
                session()->flash('message', 'Runner deleted successfully.');                
            }            
        }

        $this->reset(['runnerId', 'selectedRunner']);
    }
    
    public function selectedAddressDelete($addressId)
    {
        $this->addressId = $addressId;
        $this->dispatch('addressConfirmDeletion', ['addressId' => $addressId]);
    }
    
    public function confirmedAddressDeletion()
    {
        if($this->addressId)
        {
            $captainAddress = CaptainAddress::findOrFail($this->addressId);

            if($captainAddress)
            {
                $captainAddress->delete();
                $this->dispatch('pg:eventRefresh-captainAddressesTable');
                session()->flash('message', 'Address deleted successfully.');                
            }            
        }

        $this->reset(['addressId', 'selectedAddress']);
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
            id="captain-tab" 
            data-tabs-toggle="#captain-tab-content" 
            role="tablist" 
            data-tabs-active-classes="bg-light-green text-white" 
            data-tabs-inactive-classes="bg-gray-50 hover:text-black hover:bg-yellow-green">
            <li role="presentation">
                <button id="contact-tab" type="button" data-tabs-target="#contact" role="tab" aria-controls="contact" aria-selected="true"
                    class="inline-flex items-center px-4 py-3 rounded-t-sm active w-full" aria-current="contact">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    {{__('Contact')}}
                </button>
            </li>
            <li role="presentation">
                <button id="reservations-tab" type="button" data-tabs-target="#reservations" role="tab" aria-controls="reservations" aria-selected="false"
                    class="inline-flex items-center px-4 py-3 rounded-t-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    {{__('Reservations')}}
                </button>
            </li> 
            <li role="presentation">
                <button id="runners-tab" type="button" data-tabs-target="#runners" role="tab" aria-controls="runners" aria-selected="false"
                    class="inline-flex items-center px-4 py-3 rounded-t-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    {{__('Runners')}}
                </button>
            </li>  
            <li role="presentation">
                <button id="addresses-tab" type="button" data-tabs-target="#addresses" role="tab" aria-controls="addresses" aria-selected="false"
                    class="inline-flex items-center px-4 py-3 rounded-t-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 me-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    {{__('Addresses')}}
                </button>
            </li>           
        </ul>
        <div id="captain-tab-content" class="bg-gray-50 text-medium rounded-b-sm w-full">
            <div class="p-4 bg-white shadow rounded-b-sm" id="contact" role="tabpanel" aria-labelledby="contact-tab">                
                <div class="grid grid-cols-2 gap-12">                    
                    <div>
                        <h2 class="text-xl py-2 font-bold">{{__('Contact')}}</h2>
                        <p>
                            <b>{{__('Company name')}}</b>: 
                            {{$selectedCaptain->company_name}}                
                        </p>
                        <p>
                            <b>{{__('Address')}}</b>: 
                            {{$selectedCaptain->address}}, {{ $selectedCaptain->postcode }} {{ $selectedCaptain->city }}
                        </p>  
                        <p>
                            <b>{{__('Phone')}}</b>: 
                            {{$selectedCaptain->phone}}                
                        </p>  
                        <p>
                            <b>{{__('UID')}}</b>: 
                            {{$selectedCaptain->pin}}                
                        </p>  
                        <p>
                            <b>{{__('JBKJS')}}</b>: 
                            {{$selectedCaptain->jbkjs}}                
                        </p>  
                        <p>
                            <b>{{__('IDN')}}</b>: 
                            {{$selectedCaptain->identification_number}}                
                        </p>  
                        <hr class="mt-4 py-2"/>
                        <p>
                            <b>{{__('Team name')}}</b>: 
                            {{$selectedCaptain->team_name}}                
                        </p>  
                        <p>
                            <b>{{__('Captain')}}</b>: 
                            {{$selectedCaptain->name}} {{ $selectedCaptain->last_name }} ({{ $selectedCaptain->email }})            
                        </p>   
                        <p>
                            <b>{{__('Total employee')}}</b>: 
                            {{$selectedCaptain->totalEmployeeType->total_employee_type_name}}                
                        </p>  
                        <p>
                            <b>{{__('Company type')}}</b>: 
                            {{$selectedCaptain->companyType->company_type_name}}                
                        </p>   
                        <p>
                            <b>{{__('Business type')}}</b>: 
                            {{$selectedCaptain->businessType->business_type_name}}                
                        </p>                        
                    </div>        
                    <div>
                        <h2 class="text-xl py-2 font-bold">{{__('Billing')}}</h2>
                        <p>
                            <b>{{__('Company name')}}</b>: 
                            {{$selectedCaptain->billing_company}}                
                        </p>
                        <p>
                            <b>{{__('Address')}}</b>: 
                            {{$selectedCaptain->billing_address}}, {{ $selectedCaptain->billing_postcode }} {{ $selectedCaptain->billing_city }}
                        </p>  
                        <p>
                            <b>{{__('Phone')}}</b>: 
                            {{$selectedCaptain->billing_phone}}                
                        </p>  
                        <p>
                            <b>{{__('UID')}}</b>: 
                            {{$selectedCaptain->billing_pin}}                
                        </p>  
                        <p>
                            <b>{{__('JBKJS')}}</b>: 
                            {{$selectedCaptain->billing_jbkjs}}                
                        </p>  
                        <p>
                            <b>{{__('IDN')}}</b>: 
                            {{$selectedCaptain->billing_identification_number}}                
                        </p>  
                    </div>                
                </div>               
            </div>
            <div class="p-4 bg-white shadow rounded-b-sm" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
                <h2 class="text-xl py-2 font-bold">{{__('Reservations')}}</h2>
                <div class="mt-6">
                    <livewire:captain-reservations-table :captain-id="$captainId"/>
                </div>
            </div>
            <div class="p-4 bg-white shadow rounded-b-sm" id="runners" role="tabpanel" aria-labelledby="runners-tab">
                <h2 class="text-xl py-2 font-bold">{{__('Runners')}}</h2>                
                <div class="mt-6">
                    <livewire:captain-runners-table :captain-id="$captainId"/>
                </div>
            </div>     
            <div class="p-4 bg-white shadow rounded-b-sm" id="addresses" role="tabpanel" aria-labelledby="addresses-tab">
                <h2 class="text-xl py-2 font-bold">{{__('Addresses')}}</h2>                
                <div class="mt-6">
                    <livewire:captain-addresses-table :captain-id="$captainId"/>
                </div>
            </div>    
        </div>
    </div>  
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this runner?')}}')) {
                    Livewire.dispatch('deleteRunnerConfirmed');
                }
            });
            
            Livewire.on('addressConfirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this address?')}}')) {
                    Livewire.dispatch('confirmedAddressDeletion');
                }
            });
        });
    </script>
</div>
