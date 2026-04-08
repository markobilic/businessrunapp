<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Mail\CaptainEmailChangeMailable;
use App\Models\User;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';
    public string $email_new = '';
    public string $name = '';
    public string $last_name = '';
    public string $phone = '';
    
    /**
     * Send a password reset link to the provided email address.
     */
    public function changeCaptainEmail(): void
    {
        $throttleKey = 'change-captain-email:' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) 
        {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __("Too many attempts. Please try again in :seconds seconds.", ['seconds' => $seconds]),
            ]);
        }
    
        // Count this attempt (expires in 1 hour = 3600s)
        RateLimiter::hit($throttleKey, 3600);
    
        $this->validate([
            'email' => ['required', 'string', 'exists:users,email'],
            'email_new' => ['required', 'string', 'email:rfc,dns', 'unique:'.User::class.',email'],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\d{9,15}$/'],
        ]);

        Mail::to('noreply@businessrun.net')
            ->send(new CaptainEmailChangeMailable($this->email, $this->email_new));

        $status = 'You have successfully changed the captain data';
        
        $user = User::where('email', $this->email)->first();
        
        if($user->hasRole(['captain']))
        {
            $user->email = $this->email_new;
            $user->name = $this->name . " " . $this->last_name;
            $user->save();
            
            $user->captain()->update([
                'email' => $this->email_new,
                'name' => $this->name,
                'last_name' => $this->last_name,
                'phone' => $this->phone
            ]);
        }
        
        RateLimiter::clear($throttleKey);

        $this->reset(['email', 'email_new', 'name', 'last_name', 'phone']);
        
        session()->flash('status', __($status));
    }
}; ?>

<div>
    <h1 class="text-center font-bold text-2xl">{{__('Change captain mail')}}</h1>
    <div class="mt-4 mb-4 text-sm text-gray-600">
        {{ __('The captains e-mail is changed automatically, after filling in all fields. If you do not know the old e-mail, contact us.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="changeCaptainEmail">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Old email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <small>{{__('If you do not know who was the captain of your team before you, contact us on phone number 0600870489 so that we can assist you.')}}</small>
        </div>

        <div class="mt-4">
            <x-input-label for="email_new" :value="__('New email')" />
            <x-text-input wire:model="email_new" id="email_new" class="block mt-1 w-full" type="email" name="email_new" required />
            <x-input-error :messages="$errors->get('email_new')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Last name')" />
            <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full" type="text" name="last_name" required />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="text" name="phone" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Change email') }}
            </x-primary-button>
        </div>
    </form>
</div>
