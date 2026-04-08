<?php

use App\Livewire\Forms\LoginForm;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $currentOrganizer = request()->attributes->get('current_organizer');

        $user = User::where('email', $this->form->email)->first();

        if (! $user || ! Hash::check($this->form->password, $user->password)) {
            $this->addError('form.email', __('These credentials do not match our records.'));
            return;
        }
        else
        {
            if ($user->hasRole(['superadmin'])) 
            {
                Auth::login($user);
    
                Session::regenerate();
        
                $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
            }
            elseif ($user->hasRole(['organizer'])) 
            {
                if (! $user->organizer || $user->organizer->id !== $currentOrganizer->id) {
                    $this->addError('form.email', __('You are not the organizer for this subdomain.'));
                    return;
                }
                else
                {
                    Auth::login($user);
    
                    Session::regenerate();
            
                    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
                }
            }
            elseif ($user->hasRole(['partner', 'collaborator'])) 
            {
                if (! $user->organizerCollaborator ) 
                {
                    $this->addError('form.email', __('You are not the collaborator or partner for this subdomain.'));
                    return;
                }
                else
                {
                    Auth::login($user);
    
                    Session::regenerate();
            
                    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
                }
            }
            elseif ($user->hasRole('captain')) 
            {
                if (! $user->captain || $user->captain->organizer_id !== $currentOrganizer->id) 
                {
                    $this->addError('form.email', __('You are not a captain for this organizer.'));
                    return;
                }
                else
                {
                    Auth::login($user);
    
                    Session::regenerate();
            
                    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
                }
            }  
            else
            {
                $this->addError('form.email', __('You are not the user for this subdomain.'));
                return;
            }
        }        
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <h1 class="text-center font-bold text-2xl">{{__('Login')}}</h1>
    <!--<div class="p-2 my-2 bg-red-50">
        <p>Obaveštavamo vas da smo prešli na novi sistem kako bismo unapredili vaše korisničko iskustvo. Sistem je još u ranoj fazi i moguće je da će se pojaviti određene greške u radu.</p>
        <p>Ukoliko naiđete na bilo kakve probleme ili nepravilnosti, slobodno nas kontaktirajte – vaša povratna informacija nam je veoma važna kako bismo sve što pre otklonili.</p>
        <p><b>Hvala vam na razumevanju i podršci!</b></p>
    </div>-->
    <form class="mt-4" wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" required />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" required />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between mt-4">
            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>

            <div class="mt-4 md:mt-0 flex flex-col pe-4">
                @if (Route::has('register'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}" wire:navigate>
                        {{ __('Dont have an account? Register') }}
                    </a>
                @endif

                @if (Route::has('password.request'))
                    <a class="mt-2 underline text-sm text-gray-600 hover:text-gray-900 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
                <a class="mt-2 underline text-sm text-gray-600 hover:text-gray-900 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('email.change') }}" wire:navigate>
                    {{ __('Change captain email') }}
                </a>
            </div>          
        </div>
    </form>
</div>
