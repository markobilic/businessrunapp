<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\Organizer;

new class extends Component
{
    public Organizer $organizer;

    public function mount()
    {
        $this->organizer = request()->attributes->get('current_organizer');
    }
    
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav class="fixed top-0 z-50 w-full bg-dark-green border-b border-gray-200">
  <div class="px-3 py-2 lg:px-5 lg:pl-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center justify-start rtl:justify-end">
        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-white rounded sm:hidden hover:bg-gray-500">
            <span class="sr-only">{{ __('Open sidebar') }}</span>
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
               <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
            </svg>
         </button>
         <div class="shrink-0 flex items-center">
              <a href="{{ route('dashboard') }}">
                  @if($organizer->id == 2)
                    <x-application-logo class="block h-8 fill-current text-gray-800" />
                  @else
                    <img class="block h-8 fill-current text-gray-800" alt="Logo" src="{{asset('images/brs-logo-f.png')}}">
                  @endif
              </a>
          </div>
      </div>
      <div class="flex items-center">
          <div class="flex items-center ms-3">
            <div>
              <button type="button" class="flex text-sm rounded-full border-2 border-mid-green" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                <span class="sr-only">{{ __('Open user menu') }}</span>
                <div class="capitalize w-6 h-6 rounded-full bg-gray-500 flex items-center justify-center text-white text-sm font-semibold hover:bg-mid-green hover:text-white">
                  {{ Auth::user()->name[0] ?? '' }}
                </div>
              </button>
            </div>
            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow" id="dropdown-user">
              <div class="px-4 py-3" role="none">
                <p class="text-sm text-gray-900 uppercase" role="none" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name;"></p>
                <p class="text-sm font-medium text-gray-900 truncate" role="none" x-data="{{ json_encode(['email' => auth()->user()->email]) }}" x-text="email" x-on:profile-updated.window="email = $event.detail.email"></p>
              </div>
              <ul class="py-1" role="none">
                <li>
                  <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                      {{ __('Profile') }}
                  </a>
                </li>
                <li>
                  <button wire:click="logout" class="w-full text-start" role="menuitem">
                        <x-dropdown-link>
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
    </div>
  </div>
</nav>