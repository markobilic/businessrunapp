<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Captain;
use App\Models\CaptainAddress;
use App\Models\Organizer;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

new class extends Component {

    public Organizer $currentOrganizer;
    public $organizerId;

    public Captain $selectedCaptain;
    public $captainId;
    
    public CaptainAddress $selectedCaptainAddress;
    public $captainAddressId;

    public ?string $phone_number = '', $company_name = '', $pin = '', $jbkjs = '', $identification_number = '', $city = '', $address = '', $postal_code = '';

    protected function rules()
    {
        return [
            'phone_number' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'pin' => [
                'required',
                'string',
                'max:255'
            ],
            'jbkjs' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],
            'identification_number' => [
                'required',
                'string',
                'max:255'
            ],
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
        ];
    }

    protected $listeners = ['resetError'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount($teamId, $addressId)
    {
        if($teamId && $addressId)
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
            
            $captain = Captain::findOrFail($teamId);

            if($captain)
            {
                $this->selectedCaptain = $captain;
                $this->captainId = $teamId;
                
                $captainAddress = CaptainAddress::findOrFail($addressId);
                
                if($captainAddress)
                {
                    $this->selectedCaptainAddress = $captainAddress;
                    $this->captainAddressId = $addressId;
                    
                    $this->company_name = $this->selectedCaptainAddress->company_name;
                    $this->address = $this->selectedCaptainAddress->address;
                    $this->city = $this->selectedCaptainAddress->city;
                    $this->postal_code = $this->selectedCaptainAddress->postal_code;
                    $this->phone_number = $this->selectedCaptainAddress->phone_number;
                    $this->pin = $this->selectedCaptainAddress->pin;
                    $this->jbkjs = $this->selectedCaptainAddress->jbkjs;
                    $this->identification_number = $this->selectedCaptainAddress->identification_number;
                }
            }
            else
            {
                $this->organizerId = null;
                return redirect()->route('teams.list');
            }
        }        
    }

    public function edit()
    {
        $this->validate();

        $this->selectedCaptainAddress->company_name = $this->company_name;
        $this->selectedCaptainAddress->address = $this->address;
        $this->selectedCaptainAddress->city = $this->city;
        $this->selectedCaptainAddress->postal_code = $this->postal_code;
        $this->selectedCaptainAddress->phone_number = $this->phone_number;
        $this->selectedCaptainAddress->pin = $this->pin;
        $this->selectedCaptainAddress->jbkjs = $this->jbkjs;
        $this->selectedCaptainAddress->identification_number = $this->identification_number;
        $this->selectedCaptainAddress->save();

        session()->flash('message', 'Address updated successfully.');
        return redirect()->route('teams.show', ['teamId' => $this->captainId]);
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
        <form wire:submit.prevent="edit" class="space-y-6">         
            <div>
                <x-input-label for="phone_number" :value="__('Phone')" />
                <x-text-input wire:model="phone_number" id="phone_number" class="block mt-1 w-full" type="text" name="phone_number" required autocomplete="phone_number" />
                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="company_name" :value="__('Company name')" />
                <x-text-input wire:model="company_name" id="company_name" class="block mt-1 w-full" type="text" name="company_name" required autocomplete="company_name" />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="pin" :value="__('UID')" />
                <x-text-input wire:model="pin" id="pin" class="block mt-1 w-full" type="text" name="pin" required autocomplete="pin" />
                <x-input-error :messages="$errors->get('pin')" class="mt-2" />
            </div>
            
            @if($organizerId == 2)
                <div>
                    <x-input-label for="jbkjs" :value="__('JBKJS')" />
                    <x-text-input wire:model="jbkjs" id="jbkjs" class="block mt-1 w-full" type="text" name="jbkjs" autocomplete="jbkjs" />
                    <x-input-error :messages="$errors->get('jbkjs')" class="mt-2" />
                </div>
            @endif

            <div>
                <x-input-label for="identification_number" :value="__('IDN')" />
                <x-text-input wire:model="identification_number" id="identification_number" class="block mt-1 w-full" type="text" name="identification_number" required autocomplete="identification_number" />
                <x-input-error :messages="$errors->get('identification_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input wire:model="city" id="city" class="block mt-1 w-full" type="text" name="city" required autocomplete="city" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="address" :value="__('Address')" />
                <x-text-input wire:model="address" id="address" class="block mt-1 w-full" type="text" name="address" required autocomplete="address" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>            

            <div>
                <x-input-label for="postal_code" :value="__('Postal code')" />
                <x-text-input wire:model="postal_code" id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" required autocomplete="postal_code" />
                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
            </div>
            
            <div class="mt-6 flex justify-end">
                <x-primary-button class="ms-3">
                    {{ __('Save') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
