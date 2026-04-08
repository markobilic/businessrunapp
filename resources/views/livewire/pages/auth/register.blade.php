<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\CompanyType;
use App\Models\BusinessType;
use App\Models\TotalEmployeeType;
use App\Models\Captain;
use App\Models\Organizer;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $company_name = '';
    public ?int $company_type_id = null;
    public string $address = '';
    public string $city = '';
    public string $postcode = '';
    public string $team_name = '';
    public string $phone = '';
    public string $pin = '';
    public string $identification_number = '';
    public ?int $total_employee_type_id = null;
    public ?int $business_type_id = null;

    public string $billing_company = '';
    public string $billing_address = '';
    public string $billing_city = '';
    public string $billing_postcode = '';
    public string $billing_phone = '';
    public string $billing_pin = '';
    public string $billing_identification_number = '';

    public $companyTypes;
    public $businessTypes;
    public $totalEmployeeTypes;

    public Organizer $currentOrganizer;
    public $organizerId;

    public bool $differentBillingAddress = false;
    public bool $acceptTerms = false;

    public function mount()
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
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);
        
        if($this->organizerId == 2)
        {
            $validated2 = $this->validate([            
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'company_type_id' => ['required', 'integer', 'exists:company_types,id'],
                'address' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'postcode' => ['required', 'string', 'max:255'],
                'team_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'regex:/^\d{9,15}$/'],
                'pin' => ['required', 'regex:/^\d{9}$/', 'unique:captains,pin'],
                'identification_number' => ['required', 'string', 'regex:/^\d{8}$/', 'unique:captains,identification_number'],
                'business_type_id' => ['required', 'integer', 'exists:business_types,id'],
                'total_employee_type_id' => ['required', 'integer', 'exists:total_employee_types,id'],
                'billing_company' => ['sometimes', 'string', 'max:255'],
                'billing_address' => ['sometimes', 'string', 'max:255'],
                'billing_city' => ['sometimes', 'string', 'max:255'],
                'billing_postcode' => ['sometimes', 'string', 'max:255'],
                'billing_phone' => ['sometimes', 'string', 'regex:/^\d{9,15}$/'],
                'billing_pin' => ['sometimes', 'string', 'regex:/^\d{9}$/', 'unique:captains,billing_pin'],
                'billing_identification_number' => ['sometimes', 'string', 'regex:/^\d{8}$/'],
                'acceptTerms' => ['required', 'accepted']
            ]);
        }
        else
        {
            $validated2 = $this->validate([            
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'company_type_id' => ['required', 'integer', 'exists:company_types,id'],
                'address' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'postcode' => ['required', 'string', 'max:255'],
                'team_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string'],
                'pin' => ['required', 'unique:captains,pin'],
                'identification_number' => ['required', 'string', 'unique:captains,identification_number'],
                'business_type_id' => ['required', 'integer', 'exists:business_types,id'],
                'total_employee_type_id' => ['required', 'integer', 'exists:total_employee_types,id'],
                'billing_company' => ['sometimes', 'string', 'max:255'],
                'billing_address' => ['sometimes', 'string', 'max:255'],
                'billing_city' => ['sometimes', 'string', 'max:255'],
                'billing_postcode' => ['sometimes', 'string', 'max:255'],
                'billing_phone' => ['sometimes', 'string'],
                'billing_pin' => ['sometimes', 'string', 'unique:captains,billing_pin'],
                'billing_identification_number' => ['sometimes', 'string'],
                'acceptTerms' => ['required', 'accepted']
            ]);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['name'] = $validated['email'];
        $validated['organizer_id'] = $this->organizerId;

        event(new Registered($user = User::create($validated)));

        $user->assignRole('captain');

        $captain = new Captain();
        $captain->organizer_id = $this->organizerId;
        $captain->email = $user->email;
        $captain->user_id = $user->id;
        $captain->name = $validated2['first_name'];
        $captain->last_name = $validated2['last_name'];
        $captain->company_name = $validated2['company_name'];
        $captain->company_type_id = $validated2['company_type_id'];
        $captain->address = $validated2['address'];
        $captain->city = $validated2['city'];
        $captain->postcode = $validated2['postcode'];
        $captain->team_name = $validated2['team_name'];
        $captain->phone = $validated2['phone'];
        $captain->pin = $validated2['pin'];
        $captain->identification_number = $validated2['identification_number'];
        $captain->business_type_id = $validated2['business_type_id'];
        $captain->total_employee_type_id = $validated2['total_employee_type_id'];

        if($this->differentBillingAddress == true)
        {
            $captain->billing_company = $validated2['billing_company'];
            $captain->billing_address = $validated2['billing_address'];
            $captain->billing_city = $validated2['billing_city'];
            $captain->billing_postcode = $validated2['billing_postcode'];
            $captain->billing_phone = $validated2['billing_phone'];
            $captain->billing_pin = $validated2['billing_pin'];
            $captain->billing_identification_number = $validated2['billing_identification_number'];
        }
        else
        {
            $captain->billing_company = $validated2['company_name'];
            $captain->billing_address = $validated2['address'];
            $captain->billing_city = $validated2['city'];
            $captain->billing_postcode = $validated2['postcode'];
            $captain->billing_phone = $validated2['phone'];
            $captain->billing_pin = $validated2['pin'];
            $captain->billing_identification_number = $validated2['identification_number'];
        }

        $captain->save();
        
        if($this->organizerId == 2)
        {
            event(new \App\Events\NewCompany($captain));
        }
        
        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-center font-bold text-2xl">{{__('Register')}}</h1>
    <form class="mt-4" wire:submit="register">
        
        <h2 class="py-2 font-semibold text-xl text-gray-800 leading-tight">{{__('Captain data')}}</h2>
        <p class="text-sm">{{__('The captain is the person who administers your account and communicates with us. Not necessarily the person in charge of your racing team.')}}</p>
        
        <div class="mt-4">
            <x-input-label for="first_name" :value="__('Captain first name')" required />
            <x-text-input wire:model="first_name" id="first_name" class="block mt-1 w-full" type="text" name="first_name" required autocomplete="first_name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Captain last name')" required />
            <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full" type="text" name="last_name" required autocomplete="last_name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>
        
        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" required/>
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone')" required />
            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="text" name="phone" required autocomplete="phone" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" required />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" required />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        
        <hr class="mt-4 border-light-green"/>

        <h2 class="py-2 font-semibold text-xl text-gray-800 leading-tight">{{__('Company data')}}</h2>
        <p class="text-sm">{{__('Please enter the same information found on the APR')}}</p>

        <div class="mt-4">
            <x-input-label for="company_name" :value="__('Company name')" required />
            <x-text-input wire:model="company_name" id="company_name" class="block mt-1 w-full" type="text" name="company_name" required autocomplete="company_name" />
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="pin" :value="__('UID')" required />
            <x-text-input wire:model="pin" id="pin" class="block mt-1 w-full" type="text" name="pin" required autocomplete="pin" />
            <x-input-error :messages="$errors->get('pin')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="identification_number" :value="__('IDN')" required />
            <x-text-input wire:model="identification_number" id="identification_number" class="block mt-1 w-full" type="text" name="identification_number" required autocomplete="identification_number" />
            <x-input-error :messages="$errors->get('identification_number')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="company_type_id" :value="__('Company type')" required/>
            <select wire:model="company_type_id" name="company_type_id" id="company_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                <option value="">{{ __('Choose option...') }}</option>
                @foreach($companyTypes as $companyType)                                            
                    <option value="{{ $companyType->id }}">{{ $companyType->company_type_name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('company_type_id')" />
        </div>

        <div class="mt-4">
            <x-input-label for="address" :value="__('Address')" required />
            <x-text-input wire:model="address" id="address" class="block mt-1 w-full" type="text" name="address" required autocomplete="address" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="city" :value="__('City')" required />
            <x-text-input wire:model="city" id="city" class="block mt-1 w-full" type="text" name="city" required autocomplete="city" />
            <x-input-error :messages="$errors->get('city')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="postcode" :value="__('Postal code')" required />
            <x-text-input wire:model="postcode" id="postcode" class="block mt-1 w-full" type="text" name="postcode" required autocomplete="postcode" />
            <x-input-error :messages="$errors->get('postcode')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="team_name" :value="__('Team name')" required />
            <x-text-input wire:model="team_name" id="team_name" class="block mt-1 w-full" type="text" name="team_name" required autocomplete="team_name" />
            <x-input-error :messages="$errors->get('team_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="total_employee_type_id" :value="__('Total employee')" required />
            <select wire:model="total_employee_type_id" name="total_employee_type_id" id="total_employee_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                <option value="">{{ __('Choose option...') }}</option>
                @foreach($totalEmployeeTypes as $totalEmployeeType)                                            
                    <option value="{{ $totalEmployeeType->id }}">{{ $totalEmployeeType->total_employee_type_name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('total_employee_type_id')" />
        </div>

        <div class="mt-4">
            <x-input-label for="business_type_id" :value="__('Business type')" required />
            <select wire:model="business_type_id" name="business_type_id" id="business_type_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm sm:text-sm" required>
                <option value="">{{ __('Choose option...') }}</option>
                @foreach($businessTypes as $businessType)                                            
                    <option value="{{ $businessType->id }}">{{ $businessType->business_type_name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('business_type_id')" />
        </div>

        <hr class="mt-4 border-light-green"/>

        <div class="flex-shrink flex items-center mt-4">
            <input wire:model.live="differentBillingAddress" id="differentBillingAddress" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300">
            <label for="differentBillingAddress" class="ml-3 block text-sm font-medium text-gray-700">
                {{ __('Use different billing address') }}
            </label>
        </div> 

        @if($differentBillingAddress)
            <div class="mt-4">
                <x-input-label for="billing_company" :value="__('Company name')" required />
                <x-text-input wire:model="billing_company" id="billing_company" class="block mt-1 w-full" type="text" name="billing_company" required autocomplete="billing_company" />
                <x-input-error :messages="$errors->get('billing_company')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="billing_address" :value="__('Address')" required />
                <x-text-input wire:model="billing_address" id="billing_address" class="block mt-1 w-full" type="text" name="billing_address" required autocomplete="billing_address" />
                <x-input-error :messages="$errors->get('billing_address')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="billing_city" :value="__('City')" required />
                <x-text-input wire:model="billing_city" id="billing_city" class="block mt-1 w-full" type="text" name="billing_city" required autocomplete="billing_city" />
                <x-input-error :messages="$errors->get('billing_city')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="billing_postcode" :value="__('Postal code')" required />
                <x-text-input wire:model="billing_postcode" id="billing_postcode" class="block mt-1 w-full" type="text" name="billing_postcode" required autocomplete="billing_postcode" />
                <x-input-error :messages="$errors->get('billing_postcode')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="billing_phone" :value="__('Phone')" required />
                <x-text-input wire:model="billing_phone" id="billing_phone" class="block mt-1 w-full" type="text" name="billing_phone" required autocomplete="billing_phone" />
                <x-input-error :messages="$errors->get('billing_phone')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="billing_pin" :value="__('UID')" required />
                <x-text-input wire:model="billing_pin" id="billing_pin" class="block mt-1 w-full" type="text" name="billing_pin" required autocomplete="billing_pin" />
                <x-input-error :messages="$errors->get('billing_pin')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="billing_identification_number" :value="__('IDN')" required />
                <x-text-input wire:model="billing_identification_number" id="billing_identification_number" class="block mt-1 w-full" type="text" name="billing_identification_number" required autocomplete="billing_identification_number" />
                <x-input-error :messages="$errors->get('billing_identification_number')" class="mt-2" />
            </div>
        @endif

        <div class="flex-shrink flex items-center mt-4">
            <input wire:model.live="acceptTerms" id="acceptTerms" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300" required>
            <label for="acceptTerms" class="ml-3 block text-sm font-medium text-gray-700">
                <a class="underline text-blue-500" href="{{$currentOrganizer->tos_link}}" target="_blank">{{ __('Accept terms') }}</a>
            </label>
            <x-input-error :messages="$errors->get('acceptTerms')" class="mt-2" />
        </div> 

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
