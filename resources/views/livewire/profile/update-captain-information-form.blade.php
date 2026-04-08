<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use App\Models\CompanyType;
use App\Models\BusinessType;
use App\Models\TotalEmployeeType;
use App\Models\Organizer;

new class extends Component
{
    public $companyTypes;
    public $businessTypes;
    public $totalEmployeeTypes;

    public string $company_name = '', $address = '', $city = '', $postcode = '', $team_name = '', $pin = '', $identification_number = '';
    public string $billing_company = '', $billing_address = '', $billing_city = '', $billing_postcode = '', $billing_phone = '', $billing_pin = '', $billing_identification_number = '';
    public ?int $company_type_id = null, $business_type_id = null, $total_employee_type_id = null;

    public Organizer $currentOrganizer;
    public $organizerId;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $currentOrganizer = request()->attributes->get('current_organizer');

        $this->currentOrganizer = $currentOrganizer;

        if($this->currentOrganizer)
        {
            $this->organizerId = $currentOrganizer->id;
        }        

        $this->companyTypes = CompanyType::where('organizer_id', $currentOrganizer->id)->orderBy('company_type_name', 'ASC')->get();
        $this->businessTypes = BusinessType::where('organizer_id', $currentOrganizer->id)->orderBy('business_type_name', 'ASC')->get();
        $this->totalEmployeeTypes = TotalEmployeeType::where('organizer_id', $currentOrganizer->id)->orderBy('min_employee', 'ASC')->get();

        $this->company_name = Auth::user()->captain->company_name ?? '';
        $this->address = Auth::user()->captain->address ?? '';
        $this->city = Auth::user()->captain->city ?? '';
        $this->postcode = Auth::user()->captain->postcode ?? '';
        $this->team_name = Auth::user()->captain->team_name ?? '';
        $this->pin = Auth::user()->captain->pin ?? '';
        $this->identification_number = Auth::user()->captain->identification_number ?? '';
        $this->billing_company = Auth::user()->captain->billing_company ?? '';
        $this->billing_address = Auth::user()->captain->billing_address ?? '';
        $this->billing_city = Auth::user()->captain->billing_city ?? '';
        $this->billing_postcode = Auth::user()->captain->billing_postcode ?? '';
        $this->billing_phone = Auth::user()->captain->billing_phone ?? '';
        $this->billing_pin = Auth::user()->captain->billing_pin ?? '';
        $this->billing_identification_number = Auth::user()->captain->billing_identification_number ?? '';
        $this->company_type_id = Auth::user()->captain->company_type_id;
        $this->business_type_id = Auth::user()->captain->business_type_id;
        $this->total_employee_type_id = Auth::user()->captain->total_employee_type_id;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateCaptainInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([            
            'company_name' => ['required', 'string', 'max:255'],
            'company_type_id' => ['required', 'integer', 'exists:company_types,id'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
            'team_name' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'string', 'max:255'],
            'identification_number' => ['required', 'string', 'max:255'],
            'business_type_id' => ['required', 'integer', 'exists:business_types,id'],
            'total_employee_type_id' => ['required', 'integer', 'exists:total_employee_types,id'],
            'billing_company' => ['sometimes', 'string', 'max:255'],
            'billing_address' => ['sometimes', 'string', 'max:255'],
            'billing_city' => ['sometimes', 'string', 'max:255'],
            'billing_postcode' => ['sometimes', 'string', 'max:255'],
            'billing_phone' => ['sometimes', 'string', 'max:255'],
            'billing_pin' => ['sometimes', 'string', 'max:255'],
            'billing_identification_number' => ['sometimes', 'string', 'max:255'],
        ]);

        $user->captain->fill($validated);
        $user->captain->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Company data') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your company information.") }}
        </p>
    </header>

    <form wire:submit="updateCaptainInformation" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <div class="space-y-6">
                <div>
                    <x-input-label for="company_name" :value="__('Company name')" />
                    <x-text-input wire:model="company_name" id="company_name" class="block mt-1 w-full" type="text" name="company_name" required autocomplete="company_name" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                </div>
                
                <div>
                    <x-input-label for="team_name" :value="__('Team name')" />
                    <x-text-input wire:model="team_name" id="team_name" class="block mt-1 w-full" type="text" name="team_name" required autocomplete="team_name" />
                    <x-input-error :messages="$errors->get('team_name')" class="mt-2" />
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
                    <x-input-label for="address" :value="__('Address')" />
                    <x-text-input wire:model="address" id="address" class="block mt-1 w-full" type="text" name="address" required autocomplete="address" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="city" :value="__('City')" />
                    <x-text-input wire:model="city" id="city" class="block mt-1 w-full" type="text" name="city" required autocomplete="city" />
                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="postcode" :value="__('Postal code')" />
                    <x-text-input wire:model="postcode" id="postcode" class="block mt-1 w-full" type="text" name="postcode" required autocomplete="postcode" />
                    <x-input-error :messages="$errors->get('postcode')" class="mt-2" />
                </div>                                       
            </div>
            <div class="space-y-6">
                <div>
                    <x-input-label for="pin" :value="__('UID')" />
                    <x-text-input wire:model="pin" id="pin" class="block mt-1 w-full" type="text" name="pin" required autocomplete="pin" />
                    <x-input-error :messages="$errors->get('pin')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="identification_number" :value="__('IDN')" />
                    <x-text-input wire:model="identification_number" id="identification_number" class="block mt-1 w-full" type="text" name="identification_number" required autocomplete="identification_number" />
                    <x-input-error :messages="$errors->get('identification_number')" class="mt-2" />
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
            </div>
            <div class="col-span-2 space-y-6 rounded-sm border border-dashed border-dark-green shadow-md bg-gray-50 p-4">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Billing data') }}
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <div>
                            <x-input-label for="billing_company" :value="__('Company name')" />
                            <x-text-input wire:model="billing_company" id="billing_company" class="block mt-1 w-full" type="text" name="billing_company" required autocomplete="billing_company" />
                            <x-input-error :messages="$errors->get('billing_company')" class="mt-2" />
                        </div>
        
                        <div>
                            <x-input-label for="billing_address" :value="__('Address')" />
                            <x-text-input wire:model="billing_address" id="billing_address" class="block mt-1 w-full" type="text" name="billing_address" required autocomplete="billing_address" />
                            <x-input-error :messages="$errors->get('billing_address')" class="mt-2" />
                        </div>
        
                        <div>
                            <x-input-label for="billing_city" :value="__('City')" />
                            <x-text-input wire:model="billing_city" id="billing_city" class="block mt-1 w-full" type="text" name="billing_city" required autocomplete="billing_city" />
                            <x-input-error :messages="$errors->get('billing_city')" class="mt-2" />
                        </div>
        
                        <div>
                            <x-input-label for="billing_postcode" :value="__('Postal code')" />
                            <x-text-input wire:model="billing_postcode" id="billing_postcode" class="block mt-1 w-full" type="text" name="billing_postcode" required autocomplete="billing_postcode" />
                            <x-input-error :messages="$errors->get('billing_postcode')" class="mt-2" />
                        </div>
                    </div>
                    <div class="space-y-6">
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
        
                        <div>
                            <x-input-label for="billing_identification_number" :value="__('IDN')" />
                            <x-text-input wire:model="billing_identification_number" id="billing_identification_number" class="block mt-1 w-full" type="text" name="billing_identification_number" required autocomplete="billing_identification_number" />
                            <x-input-error :messages="$errors->get('billing_identification_number')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3 text-mid-green" on="profile-updated">
                {{ __('You have successfully changed the company data') }}
            </x-action-message>
        </div>
    </form>
</section>
