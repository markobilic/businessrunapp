<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use App\Models\Organizer;
use App\Models\CaptainAddress;

new class extends Component
{
    public Organizer $currentOrganizer;
    public $organizerId;
    
    public $captainAddresses = [];

    public $captainAddressId;
    public CaptainAddress $selectedCaptainAddress;

    public string $company_name = '', $city = '', $address = '', $postal_code = '', $phone_number = '', $pin ='', $identification_number = '';
    public ?string $jbkjs = null;

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'postal_code' => 'required|string|max:255',
        'phone_number' => 'required|string|max:255',
        'pin' => 'required|string|max:255',
        'identification_number' => 'required|string|max:255',
        'jbkjs' => 'sometimes|nullable|string|max:255',
    ];

    protected $listeners = ['deleteCaptainAddressConfirmed'];

    /**
     * Mount the component.
     */
    public function mount(): void
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
            
        $this->captainAddresses = Auth::user()->captain->captainAddresses;
    }

    public function addCaptainAddress()
    {
        $this->reset(['company_name', 'city', 'address', 'postal_code', 'phone_number', 'pin', 'jbkjs', 'identification_number']);
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'create-captain-address-modal');    
    }

    public function editCaptainAddress($captainAddressId)
    {
        $this->reset(['company_name', 'city', 'address', 'postal_code', 'phone_number', 'pin', 'jbkjs', 'identification_number']);

        if($captainAddressId)
        {
            $captain = CaptainAddress::find($captainAddressId);

            if($captain)
            {
                $this->captainAddressId = $captainAddressId;
                $this->selectedCaptainAddress = $captain;

                $this->company_name = $this->selectedCaptainAddress->company_name;
                $this->city = $this->selectedCaptainAddress->city;
                $this->address = $this->selectedCaptainAddress->address;
                $this->postal_code = $this->selectedCaptainAddress->postal_code;
                $this->phone_number = $this->selectedCaptainAddress->phone_number;
                $this->pin = $this->selectedCaptainAddress->pin;
                $this->jbkjs = $this->selectedCaptainAddress->jbkjs;
                $this->identification_number = $this->selectedCaptainAddress->identification_number;
                
                $this->resetErrorBag();
                $this->dispatch('open-modal', 'edit-captain-address-modal');    
            }
        }
    }

    public function deleteCaptainAddress($captainAddressId)
    {
        if($captainAddressId)
        {
            $captain = CaptainAddress::find($captainAddressId);

            if($captain)
            {
                $this->captainAddressId = $captainAddressId;
                $this->selectedCaptainAddress = $captain;

                $this->dispatch('confirmDeletion', ['captainAddressId' => $captainAddressId]);
            }
            else
            {
                $this->captainAddressId = null;
                $this->selectedCaptainAddress = null;
            }
        }        
    }

    public function deleteCaptainAddressConfirmed()
    {       
        if($this->selectedCaptainAddress)
        {
            $this->selectedCaptainAddress->delete();
            $this->captainAddresses = Auth::user()->captain->captainAddresses;
            session()->flash('message', 'Captain address deleted successfully.');             
        }

        $this->reset(['captainAddressId', 'selectedCaptainAddress']);
    }

    public function update()
    {
        $validatedData = $this->validate();

        if($this->selectedCaptainAddress)
        {
            $this->selectedCaptainAddress->company_name = $validatedData['company_name'];
            $this->selectedCaptainAddress->city = $validatedData['city'];
            $this->selectedCaptainAddress->address = $validatedData['address'];
            $this->selectedCaptainAddress->postal_code = $validatedData['postal_code'];
            $this->selectedCaptainAddress->phone_number = $validatedData['phone_number'];
            $this->selectedCaptainAddress->pin = $validatedData['pin'];
            $this->selectedCaptainAddress->jbkjs = $validatedData['jbkjs'];
            $this->selectedCaptainAddress->identification_number = $validatedData['identification_number'];

            $this->selectedCaptainAddress->save();
        }

        $this->captainAddresses = Auth::user()->captain->captainAddresses;

        $this->reset(['company_name', 'city', 'address', 'postal_code', 'phone_number', 'pin', 'jbkjs', 'identification_number', 'captainAddressId', 'selectedCaptainAddress']);
        $this->dispatch('close-modal', 'edit-captain-address-modal');
        session()->flash('message', 'Captain address successfully created.');
    }

    public function insert()
    {
        $validatedData = $this->validate();

        $captainAddress = auth()->user()->captain->captainAddresses()->create([
            'company_name' => $validatedData['company_name'],
            'city' => $validatedData['city'],
            'address' => $validatedData['address'],
            'postal_code' => $validatedData['postal_code'],
            'phone_number' => $validatedData['phone_number'],
            'pin' => $validatedData['pin'],
            'jbkjs' => $validatedData['jbkjs'],
            'identification_number' => $validatedData['identification_number'],
        ]);

        $this->captainAddresses = Auth::user()->captain->captainAddresses;

        $this->reset(['company_name', 'city', 'address', 'postal_code', 'phone_number', 'pin', 'jbkjs', 'identification_number', 'captainAddressId', 'selectedCaptainAddress']);
        $this->dispatch('close-modal', 'create-captain-address-modal');
        session()->flash('message', 'Captain address successfully updated.');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Additional company addresses') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your additional company addresses.") }}
        </p>
    </header>
    
    <div class="mt-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-12">
            @foreach ($captainAddresses as $ca)
                <div class="rounded-sm border border-dashed border-dark-green shadow-md bg-gray-50 p-4">
                    <h3 class="font-bold text-xl uppercase">{{ $ca->company_name }}, {{ $ca->address }}, {{ $ca->postal_code }} {{ $ca->city }}</h3>
                    <br/>
                    <p><b>{{ __('Phone') }}</b>: {{ $ca->phone_number }}</p>
                    <p><b>{{ __('UID') }}</b>: {{ $ca->pin }}</p>
                    @if($organizerId == 2)
                        <p><b>{{ __('JBKJS') }}</b>: {{ $ca->jbkjs }}</p>
                    @endif
                    <p><b>{{ __('IDN') }}</b>: {{ $ca->identification_number }}</p>
                    <div class="mt-2 flex inline-flex">
                        <button class="p-2 bg-yellow-green hover:bg-mid-green text-white rounded-sm" wire:click.prevent="editCaptainAddress({{ $ca->id }})">{{ __('Edit') }}</button>
                        <button class="ms-2 p-2 bg-red-500 hover:bg-red-800 text-white rounded-sm" wire:click.prevent="deleteCaptainAddress({{ $ca->id }})">{{ __('Delete') }}</button>
                    </div>                    
                </div>            
            @endforeach
        </div>
        <button class="mt-2 p-2 bg-mid-green hover:bg-dark-green text-white rounded-sm" wire:click.prevent="addCaptainAddress">{{ __('Add address') }}</button>
    </div>
    <x-modal name="create-captain-address-modal"> 
        <div>
            <div class="px-6 py-2 bg-gray-800">
                <button type="button" x-on:click="$dispatch('close')" class="absolute top-0 right-0 px-2 py-0 text-white">
                    <span class="text-3xl">&times;</span>
                </button>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-medium text-white">{{__('Create additional company address')}}</h2>                        
                </div>                   
            </div> 
            <form wire:submit.prevent="insert" class="mt-2 space-y-6 p-6">
                <div>
                    <x-input-label for="company_name" :value="__('Company name')" required/>
                    <x-text-input type="text" wire:model="company_name" name="company_name" id="company_name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>
                <div>
                    <x-input-label for="city" :value="__('City')" required/>
                    <x-text-input type="text" wire:model="city" name="city" id="city" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>
                <div>
                    <x-input-label for="address" :value="__('Address')" required/>
                    <x-text-input type="text" wire:model="address" name="address" id="address" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>
                <div>
                    <x-input-label for="postal_code" :value="__('Postal code')" required/>
                    <x-text-input type="text" wire:model="postal_code" name="postal_code" id="postal_code" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                </div>
                <div>
                    <x-input-label for="phone_number" :value="__('Phone number')" required/>
                    <x-text-input type="text" wire:model="phone_number" name="phone_number" id="phone_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                </div>
                <div>
                    <x-input-label for="pin" :value="__('UID')" required/>
                    <x-text-input type="text" wire:model="pin" name="pin" id="pin" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('pin')" />
                </div>
                @if($organizerId == 2)
                    <div>
                        <x-input-label for="jbkjs" :value="__('JBKJS')"/>
                        <x-text-input type="text" wire:model="jbkjs" name="jbkjs" id="jbkjs" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                        <x-input-error class="mt-2" :messages="$errors->get('jbkjs')" />
                    </div>
                @endif
                <div>
                    <x-input-label for="identification_number" :value="__('IDN')" required/>
                    <x-text-input type="text" wire:model="identification_number" name="identification_number" id="identification_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('identification_number')" />
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
    </x-modal>
    <x-modal name="edit-captain-address-modal"> 
        <div>
            <div class="px-6 py-2 bg-gray-800">
                <button type="button" x-on:click="$dispatch('close')" class="absolute top-0 right-0 px-2 py-0 text-white">
                    <span class="text-3xl">&times;</span>
                </button>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-medium text-white">{{__('Edit additional company address')}}</h2>                        
                </div>                   
            </div> 
            <form wire:submit.prevent="update" class="mt-2 space-y-6 p-6">
                <div>
                    <x-input-label for="company_name" :value="__('Company name')" required/>
                    <x-text-input type="text" wire:model="company_name" name="company_name" id="company_name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>
                <div>
                    <x-input-label for="city" :value="__('City')" required/>
                    <x-text-input type="text" wire:model="city" name="city" id="city" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>
                <div>
                    <x-input-label for="address" :value="__('Address')" required/>
                    <x-text-input type="text" wire:model="address" name="address" id="address" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>
                <div>
                    <x-input-label for="postal_code" :value="__('Postal code')" required/>
                    <x-text-input type="text" wire:model="postal_code" name="postal_code" id="postal_code" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                </div>
                <div>
                    <x-input-label for="phone_number" :value="__('Phone number')" required/>
                    <x-text-input type="text" wire:model="phone_number" name="phone_number" id="phone_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                </div>
                <div>
                    <x-input-label for="pin" :value="__('UID')" required/>
                    <x-text-input type="text" wire:model="pin" name="pin" id="pin" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('pin')" />
                </div>
                @if($organizerId == 2)
                    <div>
                        <x-input-label for="jbkjs" :value="__('JBKJS')"/>
                        <x-text-input type="text" wire:model="jbkjs" name="jbkjs" id="jbkjs" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"/>
                        <x-input-error class="mt-2" :messages="$errors->get('jbkjs')" />
                    </div>
                @endif
                <div>
                    <x-input-label for="identification_number" :value="__('IDN')" required/>
                    <x-text-input type="text" wire:model="identification_number" name="identification_number" id="identification_number" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('identification_number')" />
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
    </x-modal>
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this captain address?')}}')) {
                    Livewire.dispatch('deleteCaptainAddressConfirmed');
                }
            });
        });
    </script>
</section>
