<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\Runner;
use App\Models\Captain;
use App\Models\WorkPosition;
use App\Models\WorkSector;
use App\Models\WeekRunning;
use App\Models\LongestRace;
use App\Models\SocksSize;
use App\Models\ShirtSize;
use Spatie\Permission\Models\Role;

new class extends Component {

    public $organizerId;
    public ?int $captain_id = null, $work_position_id = null, $work_sector_id = null, $week_running_id = null, $longest_race_id = null, $socks_size_id = null, $shirt_size_id = null;
    public string $name = '', $last_name = '', $email = '', $phone = '', $sex = '';
    public $date_of_birth;
    
    public $captains;
    public $workPositions, $workSectors, $weekRunnings, $longestRaces, $socksSizes, $shirtSizes;
    
    public function rules()
    {
        if($this->organizerId == 2)
        {
            $rules = [
                'captain_id' => 'required|integer|exists:captains,id',
                'work_position_id' => 'sometimes|integer|exists:work_positions,id',
                'work_sector_id' => 'sometimes|integer|exists:work_sectors,id',
                'week_running_id' => 'sometimes|integer|exists:week_runnings,id',
                'longest_race_id' => 'sometimes|integer|exists:longest_races,id',
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email:rfc,dns|max:255',
                'phone' => 'required|string|max:255',
                'sex' => 'required|string|in:Male,Female',
                'date_of_birth' => 'required',
                'socks_size_id' => 'sometimes|nullable|integer|exists:socks_sizes,id',
            ];
        }
        else
        {
            $rules = [
                'captain_id' => 'required|integer|exists:captains,id',
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email:rfc,dns|max:255',
                'phone' => 'required|string|max:255',
                'sex' => 'required|string|in:Male,Female',
                'date_of_birth' => 'required',
                'shirt_size_id' => 'sometimes|nullable|integer|exists:shirt_sizes,id',
            ];
        }
    
        return $rules;
    }    

    protected $listeners = ['resetError'];

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

        if(auth()->user()->hasRole(['superadmin', 'organizer']))
        {
            $this->captains = Captain::where('organizer_id', $this->organizerId)->orderBy('company_name', 'ASC')->get();
        }
        else
        {
            $this->captain_id = auth()->user()->captain->id;
        }

        $this->workPositions = WorkPosition::where('organizer_id', $this->organizerId)->orderBy('work_position_name', 'ASC')->get();
        $this->workSectors = WorkSector::where('organizer_id', $this->organizerId)->orderBy('work_sector_name', 'ASC')->get();
        $this->weekRunnings = WeekRunning::where('organizer_id', $this->organizerId)->orderBy('week_running_name', 'ASC')->get();
        $this->longestRaces = LongestRace::where('organizer_id', $this->organizerId)->orderBy('longest_race_name', 'ASC')->get();
        $this->socksSizes = SocksSize::where('organizer_id', $this->organizerId)->get();
        $this->shirtSizes = ShirtSize::where('organizer_id', $this->organizerId)->get();
    }

    public function insert()
    {
        $validatedData = $this->validate();

        $runner = Runner::create([
            'name' => $validatedData['name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'sex' => $validatedData['sex'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'work_position_id' => $validatedData['work_position_id'] ?? null,
            'work_sector_id' => $validatedData['work_sector_id'] ?? null,
            'week_running_id' => $validatedData['week_running_id'] ?? null,
            'longest_race_id' => $validatedData['longest_race_id'] ?? null,
            'captain_id' => $validatedData['captain_id'],
            'socks_size_id' => $validatedData['socks_size_id'] ?? null,
            'shirt_size_id' => $validatedData['shirt_size_id'] ?? null,
        ]);
        
        if($this->organizerId == 2)
        {
            event(new \App\Events\NewRunner($runner));
        }

        session()->flash('message', 'Runner created successfully.');
        return redirect()->route('runners.list');
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
            @if(auth()->user()->hasRole(['superadmin', 'organizer']))
                <div>
                    <x-input-label for="captain_id" :value="__('Captain')"/>
                    <select wire:model="captain_id" name="captain_id" id="captain_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($captains as $captain)                                            
                            <option value="{{ $captain->id }}">{{ $captain->company_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('captain_id')" />
                </div>
            @else
                <x-text-input type="hidden" wire:model="captain_id" name="captain_id" id="captain_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
            @endif           
            <div>
                <x-input-label for="name" :value="__('First name')" required/>
                <x-text-input type="text" wire:model="name" name="name" id="name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Last name')" required/>
                <x-text-input type="text" wire:model="last_name" name="last_name" id="last_name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email')" required/>
                <x-text-input type="email" wire:model="email" name="email" id="email" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="date_of_birth" :value="__('Date of birth')" required/>
                <x-text-input type="date" wire:model="date_of_birth" name="date_of_birth" id="date_of_birth" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
            </div>
            <div>
                <x-input-label for="sex" :value="__('Sex')" required/>
                <select wire:model="sex" name="sex" id="sex" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>      
                    <option value="">{{ __('Choose option...') }}</option>
                    <option value="Male">{{ __('Male') }}</option>
                    <option value="Female">{{ __('Female') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('sex')" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" required/>
                <x-text-input type="text" wire:model="phone" name="phone" id="phone" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
            @if($organizerId == 2)
                <div>
                    <x-input-label for="work_position_id" :value="__('Work position')" :required="$organizerId == 2" />
                    <select wire:model="work_position_id" name="work_position_id" id="work_position_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :required="$organizerId == 2" >
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($workPositions as $workPosition)                                            
                            <option value="{{ $workPosition->id }}">{{ $workPosition->work_position_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('work_position_id')" />
                </div>
                <div>
                    <x-input-label for="work_sector_id" :value="__('Work sector')" :required="$organizerId == 2" />
                    <select wire:model="work_sector_id" name="work_sector_id" id="work_sector_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :required="$organizerId == 2" >
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($workSectors as $workSector)                                            
                            <option value="{{ $workSector->id }}">{{ $workSector->work_sector_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('work_sector_id')" />
                </div>
                <div>
                    <x-input-label for="week_running_id" :value="__('Week runnings')" :required="$organizerId == 2" />
                    <select wire:model="week_running_id" name="week_running_id" id="week_running_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :required="$organizerId == 2" >
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($weekRunnings as $weekRunning)                                            
                            <option value="{{ $weekRunning->id }}">{{ $weekRunning->week_running_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('week_running_id')" />
                </div>
                <div>
                    <x-input-label for="longest_race_id" :value="__('Longest race')" :required="$organizerId == 2" />
                    <select wire:model="longest_race_id" name="longest_race_id" id="longest_race_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" :required="$organizerId == 2" >
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($longestRaces as $longestRace)                                            
                            <option value="{{ $longestRace->id }}">{{ $longestRace->longest_race_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('longest_race_id')" />
                </div>
                <div>
                    <x-input-label for="socks_size_id" :value="__('Socks size')"/>
                    <select wire:model="socks_size_id" name="socks_size_id" id="socks_size_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($socksSizes as $socksSize)                                            
                            <option value="{{ $socksSize->id }}">{{ $socksSize->socks_size_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('socks_size_id')" />
                </div>
            @else
                <div>
                    <x-input-label for="shirt_size_id" :value="__('Shirt size')"/>
                    <select wire:model="shirt_size_id" name="shirt_size_id" id="shirt_size_id" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">{{ __('Choose option...') }}</option>
                        @foreach($shirtSizes as $shirtSize)                                            
                            <option value="{{ $shirtSize->id }}">{{ $shirtSize->shirt_size_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('shirt_size_id')" />
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
