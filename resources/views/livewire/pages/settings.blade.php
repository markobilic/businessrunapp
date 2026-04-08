<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Race;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

new class extends Component {
    
    public ?User $selectedUser = null;
    public ?Race $selectedRace = null;
    public $users, $userId;
    public $organizerId;
    
    public string $name = '', $email = '';
    public ?int $role = null;

    protected $listeners = ['resetError', 'deleteSelectedUser', 'deleteUserConfirmed', 'editSelectedUser', 'createUser', 'editSelectedRace'];

    protected function rules()
    {
        $emailRules = ['required', 'email'];

        if ($this->selectedUser) 
        {
            $emailRules[] = Rule::unique('users', 'email')->ignore($this->selectedUser->id);
        } 
        else 
        {
            $emailRules[] = Rule::unique('users', 'email');
        }

        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'role'  => ['required', 'in:5,6'],
        ];
    }
    
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
    }
    
    public function deleteSelectedUser($userId)
    {
        $this->userId = $userId;
        $this->dispatch('confirmDeletion', ['userId' => $userId]);
    }
    
    public function deleteUserConfirmed()
    {
        if($this->userId)
        {
            $user = User::findOrFail($this->userId);

            if($user)
            {
                $user->delete();
                $this->dispatch('pg:eventRefresh-usersTable');
                session()->flash('message', 'User deleted successfully.');                
            }            
        }

        $this->reset(['userId', 'selectedUser']);
    }
    
    public function editSelectedUser($userId)
    {
        $this->reset(['name', 'email', 'userId', 'selectedUser', 'role']);

        if($userId)
        {
            $user = User::find($userId);

            if($user)
            {
                $this->userId = $userId;
                $this->selectedUser = $user;

                $this->name = $this->selectedUser->name;
                $this->email = $this->selectedUser->email;
                $this->role  = $this->selectedUser->roles->first()?->id;
                
                $this->resetErrorBag();
                $this->dispatch('open-modal', 'edit-user-modal');    
            }
        }
    }
    
    public function updateUser()
    {
        $this->validate();

        if($this->selectedUser)
        {
            $user = $this->selectedUser;

            $user->update([
                'name'  => $this->name,
                'email' => $this->email,
            ]);
    
            $roleName = Role::findOrFail($this->role)->name;
            $user->syncRoles($roleName);
    
            $this->reset(['name', 'email', 'userId', 'selectedUser', 'role']);
            $this->dispatch('pg:eventRefresh-usersTable');
            session()->flash('message', 'User updated successfully.');        
            $this->dispatch('close-modal', 'edit-user-modal');
        }
    }
    
    public function createUser()
    {
        $this->reset(['name', 'email', 'userId', 'selectedUser', 'role']);
        $this->dispatch('open-modal', 'create-user-modal');   
    }
    
    public function insertUser()
    {
        $this->validate();

        $tempPassword = Str::random(8);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($tempPassword),
            'organizer_id' => auth()->user()->organizer_id
        ]);

        $roleName = Role::findOrFail($this->role)->name;
        $user->assignRole($roleName);
        
        $userOC = $user->organizerCollaborator()->create([
            'organizer_id' => auth()->user()->organizer_id
        ]);
        
        $this->reset(['name', 'email', 'userId', 'selectedUser', 'role']);
        $this->dispatch('pg:eventRefresh-usersTable');
        session()->flash('message', 'User created successfully.');        
        $this->dispatch('close-modal', 'create-user-modal');
    }
    
    public function editSelectedRace($raceId)
    {
        $this->reset(['selectedRace', 'userId']);
        
        if($raceId)
        {
            $race = Race::find($raceId);

            if($race)
            {
                if($race->user_id)
                {
                    $this->userId = $race->user_id;    
                }
                
                $this->users = User::where('organizer_id', $this->organizerId)->role(['partner'])->get();
                $this->selectedRace = $race;
                $this->dispatch('open-modal', 'edit-race-modal');    
            }
        }
    }
    
    public function updateRaceOrganizer()
    {
        if($this->selectedRace && $this->userId)
        {
            $this->selectedRace->update([
                'user_id'  => $this->userId
            ]);
    
            $this->reset(['selectedRace', 'userId']);
            $this->dispatch('pg:eventRefresh-raceOrganizersTable');
            session()->flash('message', 'Race organizer updated successfully.');        
            $this->dispatch('close-modal', 'edit-race-modal');
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
    @if(Auth::user()->hasRole(['superadmin', 'organizer']))       
        <div class="mt-6">
            <div class="rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2">
                <div class="border-b border-b-black">
                    <h3 class="py-2 font-bold">{{__('Users')}}</h>
                </div>            
                <livewire:users-table/>
            </div>
        </div>
        <div class="mt-6">
            <div class="rounded p-3 bg-white shadow-md grid grid-cols-1 gap-2">
                <div class="border-b border-b-black">
                    <h3 class="py-2 font-bold">{{__('Race organizers')}}</h>
                </div>            
                <livewire:race-organizers-table/>
            </div>
        </div>
        <x-modal name="edit-user-modal">
            @if($selectedUser)
                <div>                
                    <div class="px-6 py-2">
                        <button type="button" x-on:click="$dispatch('close')" class="text-black absolute top-0 right-0 px-2 py-0">
                            <span class="text-3xl">&times;</span>
                        </button>
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-black dark:text-gray-100">{{__('Edit user')}}</h2>                    
                        </div>
                    </div>
                    <form wire:submit.prevent="updateUser" class="px-6 py-3 space-y-6">
                        <div>
                            <x-input-label for="name" :value="__('Name')" required/>
                            <x-text-input type="text" wire:model="name" name="name" id="name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="email" :value="__('Email')" required/>
                            <x-text-input type="email" wire:model="email" name="email" id="email" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-input-label :value="__('Role')" required/>
                            <div class="mt-2 space-y-2">
                                <label class="inline-flex items-center">
                                    <input 
                                       type="radio"
                                       wire:model="role"
                                       value="5"
                                       class="form-radio h-4 w-4 text-indigo-600"
                                    />
                                    <span class="ml-2">{{__('CollaboratorRole')}}</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input 
                                       type="radio"
                                       wire:model="role"
                                       value="6"
                                       class="form-radio h-4 w-4 text-indigo-600"
                                    />
                                    <span class="ml-2">{{__('PartnerRole')}}</span>
                                </label>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('role')" />
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
        <x-modal name="create-user-modal">
            <div>                
                <div class="px-6 py-2">
                    <button type="button" x-on:click="$dispatch('close')" class="text-black absolute top-0 right-0 px-2 py-0">
                        <span class="text-3xl">&times;</span>
                    </button>
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-black dark:text-gray-100">{{__('Create user')}}</h2>                    
                    </div>
                </div>
                <form wire:submit.prevent="insertUser" class="px-6 py-3 space-y-6">
                    <div>
                        <x-input-label for="name" :value="__('Name')" required/>
                        <x-text-input type="text" wire:model="name" name="name" id="name" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" required/>
                        <x-text-input type="email" wire:model="email" name="email" id="email" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label :value="__('Role')" required/>
                        <div class="mt-2 space-y-2">
                            <label class="inline-flex items-center">
                                <input 
                                   type="radio"
                                   wire:model="role"
                                   value="5"
                                   class="form-radio h-4 w-4 text-indigo-600"
                                />
                                <span class="ml-2">{{__('CollaboratorRole')}}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input 
                                   type="radio"
                                   wire:model="role"
                                   value="6"
                                   class="form-radio h-4 w-4 text-indigo-600"
                                />
                                <span class="ml-2">{{__('PartnerRole')}}</span>
                            </label>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('role')" />
                    </div>
                    <div class="mt-6 flex justify-end">                        
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <x-primary-button class="ms-3">
                            {{ __('Create') }}
                        </x-primary-button>
                    </div>
                </form>                                
            </div>
        </x-modal>
        <x-modal name="edit-race-modal">
            @if($selectedRace)
                <div>                
                    <div class="px-6 py-2">
                        <button type="button" x-on:click="$dispatch('close')" class="text-black absolute top-0 right-0 px-2 py-0">
                            <span class="text-3xl">&times;</span>
                        </button>
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-black dark:text-gray-100">{{__('Edit race organizer')}}</h2>                    
                        </div>
                    </div>
                    <form wire:submit.prevent="updateRaceOrganizer" class="px-6 py-3 space-y-6">
                        <h3 class="text-lg font-bold text-black dark:text-gray-100">{{$selectedRace->name}}</h3>   
                          <div>
                            <x-input-label for="userId" :value="__('Race organizer')" required/>
                            <select wire:model="userId" name="userId" id="userId" class="dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-sky-600 dark:focus:ring-sky-600 mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="">{{ __('Choose option...') }}</option>
                                @foreach($users as $user)                                            
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('userId')" />
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
    @endif
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('confirmDeletion', (data) => {
                if (confirm('{{__('Are you sure you want to delete this user?')}}')) {
                    Livewire.dispatch('deleteUserConfirmed');
                }
            });
        });
    </script>
</div>