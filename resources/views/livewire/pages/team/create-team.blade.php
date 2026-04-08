<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Captain;
use App\Models\Organizer;
use App\Models\CompanyType;
use App\Models\BusinessType;
use App\Models\TotalEmployeeType;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

new class extends Component {

    public Organizer $currentOrganizer;
    public $organizerId;

    public ?int $company_type_id = null, $total_employee_type_id = null, $business_type_id = null;
    public string $name = '', $last_name = '', $email = '', $phone = '', $company_name = '', $team_name = '', 
    $pin = '', $jbkjs = '', $identification_number = '', $city = '', $address = '', $postcode = '',
    $billing_company = '', $billing_city = '', $billing_address = '', $billing_phone = '', $billing_pin = '',
    $billing_jbkjs = '', $billing_identification_number = '', $billing_postcode = '';
    
    public $companyTypes, $totalEmployeeTypes, $businessTypes;

    public bool $differentBillingAddress = false;

    protected $rules = [
        'company_type_id' => 'required|integer|exists:company_types,id',
        'total_employee_type_id' => 'integer|exists:total_employee_types,id',
        'business_type_id' => 'integer|exists:business_types,id',
        'name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email:rfc,dns|unique:captains,email|max:255',
        'phone' => 'required|string|max:255',
        'company_name' => 'required|string|max:255',
        'team_name' => 'required|string|max:255',
        'pin' => 'required|string|unique:captains,pin|max:255',
        'jbkjs' => 'nullable|string|unique:captains,jbkjs|max:255',
        'identification_number' => 'required|string|unique:captains,identification_number|max:255',
        'city' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'postcode' => 'required|string|max:255',
        'billing_company' => 'sometimes|string|max:255',
        'billing_city' => 'sometimes|string|max:255',
        'billing_address' => 'sometimes|string|max:255',
        'billing_phone' => 'sometimes|string|max:255',
        'billing_pin' => 'sometimes|string|unique:captains,billing_pin|max:255',
        'billing_jbkjs' => 'sometimes|nullable|string|unique:captains,billing_jbkjs|max:255',
        'billing_identification_number' => 'sometimes|string|unique:captains,billing_identification_number|max:255',
        'billing_postcode' => 'sometimes|string|max:255',
    ];

    protected $listeners = ['resetError'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount()
    {
        $currentOrganizer = request()->attributes->get('current_organizer');

        $this->currentOrganizer = $currentOrganizer;

        if($currentOrganizer)
        {
            $this->organizerId = $currentOrganizer->id;

            $this->companyTypes = CompanyType::where('organizer_id', $currentOrganizer->id)->orderBy('company_type_name', 'ASC')->get();
            $this->businessTypes = BusinessType::where('organizer_id', $currentOrganizer->id)->orderBy('business_type_name', 'ASC')->get();
            $this->totalEmployeeTypes = TotalEmployeeType::where('organizer_id', $currentOrganizer->id)->orderBy('total_employee_type_name', 'ASC')->get();
        }
        else
        {
            $this->organizerId = null;
            return redirect()->route('teams.list');
        }   
    }

    public function insert()
    {
        $validatedData = $this->validate();

        $user = new User();
        $user->name = explode('@', $validatedData['email'])[0];
        $user->email = $validatedData['email'];
        $user->password = Hash::make(Str::random(8));
        $user->organizer_id = auth()->user()->organizer_id;
        $user->save();

        $user->assignRole('captain');

        $captain = new Captain();
        $captain->organizer_id = $this->organizerId;
        $captain->email = $user->email;
        $captain->user_id = $user->id;
        $captain->name = $validatedData['name'];
        $captain->last_name = $validatedData['last_name'];
        $captain->company_name = $validatedData['company_name'];
        $captain->company_type_id = $validatedData['company_type_id'];
        $captain->address = $validatedData['address'];
        $captain->city = $validatedData['city'];
        $captain->postcode = $validatedData['postcode'];
        $captain->team_name = $validatedData['team_name'];
        $captain->phone = $validatedData['phone'];
        $captain->pin = $validatedData['pin'];
        $captain->identification_number = $validatedData['identification_number'];
        $captain->business_type_id = $validatedData['business_type_id'];
        $captain->total_employee_type_id = $validatedData['total_employee_type_id'];

        if($this->differentBillingAddress == true)
        {
            $captain->billing_company = $validatedData['billing_company'];
            $captain->billing_address = $validatedData['billing_address'];
            $captain->billing_city = $validatedData['billing_city'];
            $captain->billing_postcode = $validatedData['billing_postcode'];
            $captain->billing_phone = $validatedData['billing_phone'];
            $captain->billing_pin = $validatedData['billing_pin'];
            $captain->billing_identification_number = $validatedData['billing_identification_number'];
        }
        else
        {
            $captain->billing_company = $validatedData['company_name'];
            $captain->billing_address = $validatedData['address'];
            $captain->billing_city = $validatedData['city'];
            $captain->billing_postcode = $validatedData['postcode'];
            $captain->billing_phone = $validatedData['phone'];
            $captain->billing_pin = $validatedData['pin'];
            $captain->billing_identification_number = $validatedData['identification_number'];
        }

        $captain->save();

        if($this->organizerId == 2)
        {
            event(new \App\Events\NewCompany($captain));
        }
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
            <div>
                <x-input-label for="name" :value="__('First name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full" type="text" name="last_name" required autocomplete="last_name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="email" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="text" name="phone" required autocomplete="phone" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="company_name" :value="__('Company name')" />
                <x-text-input wire:model="company_name" id="company_name" class="block mt-1 w-full" type="text" name="company_name" required autocomplete="company_name" />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="company_type_id" :value="__('Company type')"/>
                <select wire:model="company_type_id" name="company_type_id" id="company_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                    <option value="">{{ __('Choose option...') }}</option>
                    @foreach($companyTypes as $companyType)                                            
                        <option value="{{ $companyType->id }}">{{ $companyType->company_type_name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('company_type_id')" />
            </div>

            <div>
                <x-input-label for="team_name" :value="__('Team name')" />
                <x-text-input wire:model="team_name" id="team_name" class="block mt-1 w-full" type="text" name="team_name" required autocomplete="team_name" />
                <x-input-error :messages="$errors->get('team_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="total_employee_type_id" :value="__('Total employee')"/>
                <select wire:model="total_employee_type_id" name="total_employee_type_id" id="total_employee_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                    <option value="">{{ __('Choose option...') }}</option>
                    @foreach($totalEmployeeTypes as $totalEmployeeType)                                            
                        <option value="{{ $totalEmployeeType->id }}">{{ $totalEmployeeType->total_employee_type_name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('total_employee_type_id')" />
            </div>

            <div>
                <x-input-label for="business_type_id" :value="__('Business type')"/>
                <select wire:model="business_type_id" name="business_type_id" id="business_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                    <option value="">{{ __('Choose option...') }}</option>
                    @foreach($businessTypes as $businessType)                                            
                        <option value="{{ $businessType->id }}">{{ $businessType->business_type_name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('business_type_id')" />
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
                <x-input-label for="postcode" :value="__('Postal code')" />
                <x-text-input wire:model="postcode" id="postcode" class="block mt-1 w-full" type="text" name="postcode" required autocomplete="postcode" />
                <x-input-error :messages="$errors->get('postcode')" class="mt-2" />
            </div>

            <hr class="border-light-green"/>

            <div class="flex-shrink flex items-center">
                <input wire:model.live="differentBillingAddress" id="differentBillingAddress" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300">
                <label for="differentBillingAddress" class="ml-3 block text-sm font-medium text-gray-700">
                    {{ __('Use different billing address') }}
                </label>
            </div> 

            @if($differentBillingAddress)
                <div>
                    <x-input-label for="billing_company" :value="__('Company name')" />
                    <x-text-input wire:model="billing_company" id="billing_company" class="block mt-1 w-full" type="text" name="billing_company" required autocomplete="billing_company" />
                    <x-input-error :messages="$errors->get('billing_company')" class="mt-2" />
                </div>
                
                <div>
                    <x-input-label for="billing_city" :value="__('City')" />
                    <x-text-input wire:model="billing_city" id="billing_city" class="block mt-1 w-full" type="text" name="billing_city" required autocomplete="billing_city" />
                    <x-input-error :messages="$errors->get('billing_city')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="billing_address" :value="__('Address')" />
                    <x-text-input wire:model="billing_address" id="billing_address" class="block mt-1 w-full" type="text" name="billing_address" required autocomplete="billing_address" />
                    <x-input-error :messages="$errors->get('billing_address')" class="mt-2" />
                </div>
                
                <div>
                    <x-input-label for="billing_postcode" :value="__('Postal code')" />
                    <x-text-input wire:model="billing_postcode" id="billing_postcode" class="block mt-1 w-full" type="text" name="billing_postcode" required autocomplete="billing_postcode" />
                    <x-input-error :messages="$errors->get('billing_postcode')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="billing_phone" :value="__('Phone')" />
                    <x-text-input wire:model="billing_phone" id="billing_phone" class="block mt-1 w-full" type="text" name="billing_phone" required autocomplete="billing_phone" />
                    <x-input-error :messages="$errors->get('billing_phone')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="billing_pin" :value="__('UID')" />
                    <x-text-input wire:model="billing_pin" id="billing_pin" class="block mt-1 w-full" type="text" name="billing_pin" required autocomplete="billing_pin" />
                    <x-input-error :messages="$errors->get('billing_pin')" class="mt-2" />
                </div>
                @if($organizerId == 2)
                    <div>
                        <x-input-label for="billing_jbkjs" :value="__('JBKJS')" />
                        <x-text-input wire:model="billing_jbkjs" id="billing_jbkjs" class="block mt-1 w-full" type="text" name="billing_jbkjs" autocomplete="billing_jbkjs" />
                        <x-input-error :messages="$errors->get('billing_jbkjs')" class="mt-2" />
                    </div>
                @endif
                <div>
                    <x-input-label for="billing_identification_number" :value="__('IDN')" />
                    <x-text-input wire:model="billing_identification_number" id="billing_identification_number" class="block mt-1 w-full" type="text" name="billing_identification_number" required autocomplete="billing_identification_number" />
                    <x-input-error :messages="$errors->get('billing_identification_number')" class="mt-2" />
                </div>
            @endif
            <div class="mt-6 flex justify-end">
                <x-primary-button class="ms-3">
                    {{ __('Save') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
