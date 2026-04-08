<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $last_name = '';
    public string $phone = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->email = Auth::user()->email;
        
        if(Auth::user()->captain)
        {
            $captain = Auth::user()->captain;
            
            $this->name = $captain->name;
            $this->last_name = $captain->last_name;
            $this->phone = $captain->phone;
        }
        else
        {
            $fullName = Auth::user()->name;
            [$first, $last] = array_pad(explode(' ', trim($fullName), 2), 2, '');
            
            $this->name = $first;
            $this->last_name = $last;
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'name' => ['sometimes', 'required', 'string'],
            'last_name' => ['sometimes', 'required', 'string'],
            'phone' => ['sometimes', 'string']
        ]);

        $user->update([
            'email' => $validated['email'],
            'name' => $validated['name'] . " " . $validated['last_name']
        ]);

        if ($user->isDirty('email')) 
        {
            $user->email_verified_at = null;
        }

        $user->save();

        if($user->captain)
        {
            $user->captain()->update([
                'email' => $this->email,
                'name' => $this->name,
                'last_name' => $this->last_name,
                'phone' => $this->phone
            ]);
        }

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            @if(Auth::user()->captain)
                {{ __('Captain data') }}
            @else
                {{ __('Profile Information') }}
            @endif
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            @if(Auth::user()->captain)
                {{ __("Change captain") }}
            @else
                {{ __("Update your account's email address.") }}
            @endif
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
        
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required/>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        
        <div>
            <x-input-label for="last_name" :value="__('Last name')" />
            <x-text-input wire:model="last_name" id="last_name" name="last_name" type="text" class="mt-1 block w-full" required/>
            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
        </div>
        
        @if(Auth::user()->captain)
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input wire:model="phone" id="phone" name="phone" type="text" class="mt-1 block w-full" required/>
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3 text-mid-green" on="profile-updated">
                {{ __('You have successfully changed the captain data') }}
            </x-action-message>
        </div>
    </form>
</section>
