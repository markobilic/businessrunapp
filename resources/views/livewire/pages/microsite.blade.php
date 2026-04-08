<?php

use Livewire\Volt\Component;
use App\Models\Reservation;
use App\Models\Captain;
use App\Models\Organizer;
use App\Models\Race;
use App\Models\Runner;
use App\Models\WorkPosition;
use App\Models\WorkSector;
use App\Models\WeekRunning;
use App\Models\LongestRace;
use App\Models\SocksSize;
use App\Models\ShirtSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\MailService;

new class extends Component {
    public Reservation $currentReservation;
    public Captain $currentCaptain;
    public Race $currentRace;
    public Organizer $currentOrganizer;

    public $organizerId;

    public ?int $work_position_id = null, $work_sector_id = null, $week_running_id = null, $longest_race_id = null, $socks_size_id = null, $shirt_size_id = null;
    public string $name = '', $last_name = '', $email = '', $phone = '', $sex = '';
    public $date_of_birth;

    public bool $acceptTerms = false;
    public bool $emailChecked = false;
    public bool $existingRunner = false;
    
    public string $email_check = '';
    
    public ?Runner $runnerFromEmail = null;

    public $workPositions, $workSectors, $weekRunnings, $longestRaces, $socksSizes, $shirtSizes;

    public bool $applied = false;

    public function rules()
    {
        if($this->organizerId == 2)
        {
            $rules = [
                'work_position_id'  => 'sometimes|integer|exists:work_positions,id',
                'work_sector_id'    => 'sometimes|required|integer|exists:work_sectors,id',
                'week_running_id'   => 'sometimes|required|integer|exists:week_runnings,id',
                'longest_race_id'   => 'sometimes|required|integer|exists:longest_races,id',
                'name'              => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'phone'             => 'required|string|max:255',
                'sex'               => 'required|string|in:Male,Female',
                'date_of_birth'     => [
                    'required',
                    'date',
                    'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                ],
                'socks_size_id' => 'sometimes|nullable|integer|exists:socks_sizes,id',
            ];
        }
        else
        {
            $rules = [
                'name'              => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'phone'             => 'required|string|max:255',
                'sex'               => 'required|string|in:Male,Female',
                'date_of_birth'     => [
                    'required',
                    'date',
                    'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                ],
                'shirt_size_id' => 'sometimes|nullable|integer|exists:shirt_sizes,id',
            ];
        }
    
        if ($this->existingRunner) 
        {
            $rules['email'] = [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('runners', 'email')
                    ->ignore($this->runnerFromEmail->id),
            ];
        } 
        else 
        {
            $rules['email'] = [
                'required',
                'email:rfc,dns',
                'unique:runners,email',
                'max:255',
            ];
        }
    
        return $rules;
    }    

    protected $listeners = ['resetError'];

    public function resetError()
    {
        $this->resetErrorBag('error');
    }

    public function mount($reservationHash)
    {
        $decoded = base64_decode($reservationHash);
        $data = json_decode($decoded, true);

        if (!$data) 
        {
            abort(404, 'Invalid reservation hash.');
        }
    
        $reservationId = $data['reservationId'] ?? null;
        $captainId     = $data['captainId'] ?? null;
        $raceId        = $data['raceId'] ?? null;

        if($reservationId && $captainId && $raceId)
        {
            $reservation = Reservation::findOrFail($reservationId);
            $captain = Captain::findOrFail($captainId);
            $race = Race::findOrFail($raceId);

            if($reservation && $captain && $race)
            {
                $this->currentReservation = $reservation;
                $this->currentCaptain = $captain;
                $this->currentRace = $race;

                $organizer = Organizer::findOrFail($this->currentRace->organizer_id);

                if($organizer)
                {
                    $this->organizerId = $organizer->id;
                    $this->currentOrganizer = $organizer;

                    $this->workPositions = WorkPosition::where('organizer_id', $this->organizerId)->get();
                    $this->workSectors = WorkSector::where('organizer_id', $this->organizerId)->orderBy('work_sector_name', 'ASC')->get();
                    $this->weekRunnings = WeekRunning::where('organizer_id', $this->organizerId)->orderBy('week_running_name', 'ASC')->get();
                    $this->longestRaces = LongestRace::where('organizer_id', $this->organizerId)->get();
                    $this->socksSizes = SocksSize::where('organizer_id', $this->organizerId)->get();
                    $this->shirtSizes = ShirtSize::where('organizer_id', $this->organizerId)->get();
                }   
                else
                {
                    abort(404, 'Invalid reservation hash.');
                }            
            }
            else
            {
                abort(404, 'Invalid reservation hash.');
            }
        }
    }

    public function insert(MailService $mailService)
    {
        $validatedData = $this->validate();
        
        if($this->runnerFromEmail)
        {
            $this->runnerFromEmail->update([
                'name' => $validatedData['name'],
                'last_name' => $validatedData['last_name'],
                'phone' => $validatedData['phone'],
                'sex' => $validatedData['sex'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'work_position_id' => $validatedData['work_position_id'] ?? null,
                'work_sector_id' => $validatedData['work_sector_id'] ?? null,
                'week_running_id' => $validatedData['week_running_id'] ?? null,
                'longest_race_id' => $validatedData['longest_race_id'] ?? null,
                'shirt_size_id' => $validatedData['shirt_size_id'] ?? null,
                'socks_size_id' => $validatedData['socks_size_id'] ?? null,
            ]);
            
            $idForReservation = $this->runnerFromEmail->id;
        }
        else
        {
            $newRunner = Runner::create([
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
                'shirt_size_id' => $validatedData['shirt_size_id'] ?? null,
                'socks_size_id' => $validatedData['socks_size_id'] ?? null,
                'captain_id' => $this->currentCaptain->id,
            ]);
            
            if($this->organizerId == 2)
            {
                event(new \App\Events\NewRunner($newRunner));
            }
            
            $idForReservation = $newRunner->id;
        }

        $reservationRunner = $this->currentReservation->runnerReservations()
            ->whereNull('runner_id')
            ->first();

        if ($reservationRunner) 
        {
            $reservationRunner->update([
                'runner_id' => $idForReservation,
            ]);
            
            if($this->organizerId == 2)
            {
                event(new \App\Events\NewRunnerReservation($reservationRunner));
            }
        }
        
        if($reservationRunner->runner)
        {
            $mailService->sendRunnerRegistrationConfirmation($reservationRunner->runner, $reservationRunner->reservation_id);
        }

        $this->applied = true;
        //return redirect()->route('runners.list');
    }
    
    public function checkRunnerData()
    {
        $this->reset(['existingRunner', 'emailChecked', 'runnerFromEmail']);
        
        if($this->email_check)
        {
            $this->runnerFromEmail = Runner::where('email', $this->email_check)->where('captain_id', $this->currentCaptain->id)->first();
            
            if($this->runnerFromEmail)
            {
                $this->name = $this->runnerFromEmail->name;
                $this->last_name = $this->runnerFromEmail->last_name;
                $this->email = $this->runnerFromEmail->email;
                $this->phone = $this->runnerFromEmail->phone;
                $this->sex = $this->runnerFromEmail->sex;
                $this->date_of_birth = $this->runnerFromEmail->date_of_birth;
                $this->work_position_id = $this->runnerFromEmail->work_position_id;
                $this->work_sector_id = $this->runnerFromEmail->work_sector_id;
                $this->week_running_id = $this->runnerFromEmail->week_running_id;
                $this->longest_race_id = $this->runnerFromEmail->longest_race_id;
                $this->shirt_size_id = $this->runnerFromEmail->shirt_size_id;
                $this->socks_size_id = $this->runnerFromEmail->socks_size_id;
                
                $this->existingRunner = true;
                
                session()->flash('message', 'Hey, we already have your information, which means that you have already participated in one of our races. We are glad to see you again.  Here is what we have from your data. Are you changing something? If everything is ok, save and register for this years race.');
            }
            else
            {
                $this->reset(['name', 'last_name', 'phone', 'sex', 'date_of_birth', 'work_position_id', 'work_sector_id', 'week_running_id', 'longest_race_id', 'shirt_size_id', 'socks_size_id']);
                
                $this->existingRunner = false;
            }
            
            $this->emailChecked = true;
            $this->email = $this->email_check;
        }
    }
    
    public function updatedEmail()
    {
        $this->email_check = $this->email;
        $this->checkRunnerData();
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
    <div>
        <h1 class="text-center font-bold text-2xl uppercase">{{$currentRace->name}}</h1>
        <h2 class="text-center font-bold text-lg mt-2">{{$currentCaptain->team_name}}</h2>
        <p class="text-center my-6">
            {{__('Pozdrav kolege, popunjavanjem podataka u nastavku postajete deo :team tima za :race. Želim vam svima lake noge i vetar u leđa!', [
                'team' => $currentCaptain->team_name,
                'race' => $currentRace->name,
            ])}}
        </p>
        @if(!$applied)
            @if(!$emailChecked)
                <div>
                    <x-input-label for="email_check" :value="__('Email')" required/>
                    <x-text-input type="email" wire:model.live.debounce.250ms="email_check" name="email_check" id="email_check" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                    <x-input-error class="mt-2" :messages="$errors->get('email_check')" />
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button class="ms-3" wire:click.prevent="checkRunnerData" :disabled="$email_check === ''">
                        {{ __('Continue') }}
                    </x-secondary-button>
                </div>
            @else
                <form wire:submit.prevent="insert" class="space-y-6">             
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
                        <x-text-input type="email" wire:model.blur="email" name="email" id="email" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label for="date_of_birth" :value="__('Date of birth')" required/>
                        <x-text-input type="date" wire:model="date_of_birth" name="date_of_birth" id="date_of_birth" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Phone')" required/>
                        <x-text-input type="text" wire:model="phone" name="phone" id="phone" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
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
                    <div class="flex-shrink flex items-center mt-4">
                        <input wire:model.live="acceptTerms" id="acceptTerms" type="checkbox" value="1" class="h-4 w-4 text-mid-green border-gray-300" required>
                        <label for="acceptTerms" class="ml-3 block text-sm font-medium text-gray-700">
                            <a class="underline text-blue-500" href="{{$currentOrganizer->tos_link}}" target="_blank">{{ __('Accept terms') }}</a>
                        </label>
                        <x-input-error :messages="$errors->get('acceptTerms')" class="mt-2" />
                    </div> 
                    <div class="mt-6 flex justify-end">
                        <x-primary-button class="ms-3">
                            {{ __('Save') }}
                        </x-primary-button>
                    </div>
                </form>   
            @endif
        @else
            <h2 class="text-center text-mid-green font-bold text-lg mt-2">{{__('Your registration is successful! You can close this page.')}}</h2>
        @endif  
    </div>
</div>