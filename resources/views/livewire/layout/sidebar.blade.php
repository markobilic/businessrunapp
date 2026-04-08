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

<aside :class="isSidebarOpen ? 'w-72' : 'w-16'" id="logo-sidebar" class="fixed top-0 left-0 z-40 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 " aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
        <button 
            x-data="{ hover: false }" 
            @click="isSidebarOpen = !isSidebarOpen; $persist(isSidebarOpen)" 
            @mouseenter="hover = true" 
            @mouseleave="hover = false"
            class="w-full top-5 right-full mr-2 p-2 text-gray-500 hover:text-mid-green">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="m-auto w-6 h-6">
                <path x-show="(isSidebarOpen && hover)" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                <path x-show="(!isSidebarOpen && hover)" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                <path x-show="(!isSidebarOpen && !hover)" stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                <path x-show="(isSidebarOpen && !hover)" stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/>
            </svg>
        </button>
        <ul class="space-y-2 font-bold">
            @if($organizer->id == 2)
                @if((auth()->user()->hasRole(['captain']) && $organizer->id == 7) || (auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner', 'captain']) && $organizer->id != 7))       
                    <li title="{{ __('page that contains the most important information for using the system') }}">
                        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3V6ZM3 15.75a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-2.25Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3v-2.25Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">
                                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))       
                                    {{ __('Dashboard') }}
                                @else
                                    {{ __('Welcome') }}
                                @endif                    
                            </span>
                        </x-nav-link>
                    </li> 
                @endif
                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner', 'captain']))       
                    <li title="{{ __('here you register your team for races, add and change team members, track your payments and download documentation.') }}">
                        <x-nav-link href="{{ route('reservations.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'reservations')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M7.491 5.992a.75.75 0 0 1 .75-.75h12a.75.75 0 1 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM7.49 11.995a.75.75 0 0 1 .75-.75h12a.75.75 0 0 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM7.491 17.994a.75.75 0 0 1 .75-.75h12a.75.75 0 1 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM2.24 3.745a.75.75 0 0 1 .75-.75h1.125a.75.75 0 0 1 .75.75v3h.375a.75.75 0 0 1 0 1.5H2.99a.75.75 0 0 1 0-1.5h.375v-2.25H2.99a.75.75 0 0 1-.75-.75ZM2.79 10.602a.75.75 0 0 1 0-1.06 1.875 1.875 0 1 1 2.652 2.651l-.55.55h.35a.75.75 0 0 1 0 1.5h-2.16a.75.75 0 0 1-.53-1.281l1.83-1.83a.375.375 0 0 0-.53-.53.75.75 0 0 1-1.062 0ZM2.24 15.745a.75.75 0 0 1 .75-.75h1.125a1.875 1.875 0 0 1 1.501 2.999 1.875 1.875 0 0 1-1.501 3H2.99a.75.75 0 0 1 0-1.501h1.125a.375.375 0 0 0 .036-.748H3.74a.75.75 0 0 1-.75-.75v-.002a.75.75 0 0 1 .75-.75h.411a.375.375 0 0 0-.036-.748H2.99a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Reservations') }}</span>
                        </x-nav-link>
                    </li>            
                    <li title="{{ __('here you change the data of colleagues that have already been entered into the system') }}">
                        <x-nav-link href="{{ route('runners.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'runners')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">
                                @if(auth()->user()->hasRole(['captain']))         
                                    {{ __('Your colleagues') }}
                                @else
                                    {{ __('Information about runners') }}
                                @endif
                            </span>
                        </x-nav-link>
                    </li>                  
                @endif
                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner', 'captain']))                 
                    <li title="{{ __('here you can find details of current races, prices, dates and number of available seats') }}">
                        <x-nav-link href="{{ route('races.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'races')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Races and products') }}</span>
                        </x-nav-link>
                    </li>  
                @endif         
    
                @if(auth()->user()->hasRole(['captain']))  
                    <li title="{{ __('here you change the information of your company and add the information of the companies that take over the payment on your behalf') }}">
                        <x-nav-link href="{{ route('company') }}" :active="request()->routeIs('company')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                <path d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Company data') }}</span>
                        </x-nav-link>
                    </li>  
                @endif
    
                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))                 
                    <li>
                        <x-nav-link href="{{ route('teams.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'teams')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
                                <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Teams') }}</span>
                        </x-nav-link>
                    </li>  
                @endif 
                
                @if(auth()->user()->hasRole(['superadmin', 'organizer']))      
                    @if($organizer->id == 2)
                        <li>
                            <x-nav-link href="{{ route('analytics.show') }}" :active="Str::startsWith(Route::currentRouteName(), 'analytics')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                    <path fill-rule="evenodd" d="M2.25 2.25a.75.75 0 0 0 0 1.5H3v10.5a3 3 0 0 0 3 3h1.21l-1.172 3.513a.75.75 0 0 0 1.424.474l.329-.987h8.418l.33.987a.75.75 0 0 0 1.422-.474l-1.17-3.513H18a3 3 0 0 0 3-3V3.75h.75a.75.75 0 0 0 0-1.5H2.25Zm6.54 15h6.42l.5 1.5H8.29l.5-1.5Zm8.085-8.995a.75.75 0 1 0-.75-1.299 12.81 12.81 0 0 0-3.558 3.05L11.03 8.47a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l2.47-2.47 1.617 1.618a.75.75 0 0 0 1.146-.102 11.312 11.312 0 0 1 3.612-3.321Z" clip-rule="evenodd" />
                                </svg>
                                <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Analytics') }}</span>
                            </x-nav-link>
                        </li> 
                    @endif
                    <li>
                        <x-nav-link href="{{ route('settings.show') }}" :active="Str::startsWith(Route::currentRouteName(), 'settings')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M11.828 2.25c-.916 0-1.699.663-1.85 1.567l-.091.549a.798.798 0 0 1-.517.608 7.45 7.45 0 0 0-.478.198.798.798 0 0 1-.796-.064l-.453-.324a1.875 1.875 0 0 0-2.416.2l-.243.243a1.875 1.875 0 0 0-.2 2.416l.324.453a.798.798 0 0 1 .064.796 7.448 7.448 0 0 0-.198.478.798.798 0 0 1-.608.517l-.55.092a1.875 1.875 0 0 0-1.566 1.849v.344c0 .916.663 1.699 1.567 1.85l.549.091c.281.047.508.25.608.517.06.162.127.321.198.478a.798.798 0 0 1-.064.796l-.324.453a1.875 1.875 0 0 0 .2 2.416l.243.243c.648.648 1.67.733 2.416.2l.453-.324a.798.798 0 0 1 .796-.064c.157.071.316.137.478.198.267.1.47.327.517.608l.092.55c.15.903.932 1.566 1.849 1.566h.344c.916 0 1.699-.663 1.85-1.567l.091-.549a.798.798 0 0 1 .517-.608 7.52 7.52 0 0 0 .478-.198.798.798 0 0 1 .796.064l.453.324a1.875 1.875 0 0 0 2.416-.2l.243-.243c.648-.648.733-1.67.2-2.416l-.324-.453a.798.798 0 0 1-.064-.796c.071-.157.137-.316.198-.478.1-.267.327-.47.608-.517l.55-.091a1.875 1.875 0 0 0 1.566-1.85v-.344c0-.916-.663-1.699-1.567-1.85l-.549-.091a.798.798 0 0 1-.608-.517 7.507 7.507 0 0 0-.198-.478.798.798 0 0 1 .064-.796l.324-.453a1.875 1.875 0 0 0-.2-2.416l-.243-.243a1.875 1.875 0 0 0-2.416-.2l-.453.324a.798.798 0 0 1-.796.064 7.462 7.462 0 0 0-.478-.198.798.798 0 0 1-.517-.608l-.091-.55a1.875 1.875 0 0 0-1.85-1.566h-.344ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Settings') }}</span>
                        </x-nav-link>
                    </li>        
                    @if($organizer->id == 2)
                        <li>
                            <x-nav-link href="{{ route('transactions.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'transactions')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                    <path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                                    <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule="evenodd" />
                                    <path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" />
                                </svg>
                                <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Payments') }}</span>
                            </x-nav-link>
                        </li>
                    @endif
                @endif 
            @else
                @if((auth()->user()->hasRole(['captain']) && $organizer->id == 7) || (auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner', 'captain']) && $organizer->id != 7))       
                    <li title="{{ __('page that contains the most important information for using the system') }}">
                        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3V6ZM3 15.75a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-2.25Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3v-2.25Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">
                                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))       
                                    {{ __('Dashboard') }}
                                @else
                                    {{ __('Welcome') }}
                                @endif                    
                            </span>
                        </x-nav-link>
                    </li> 
                @endif
                
                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner', 'captain']))                 
                    <li title="{{ __('here you can find details of current races, prices, dates and number of available seats') }}">
                        <x-nav-link href="{{ route('races.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'races')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M3 2.25a.75.75 0 0 1 .75.75v.54l1.838-.46a9.75 9.75 0 0 1 6.725.738l.108.054A8.25 8.25 0 0 0 18 4.524l3.11-.732a.75.75 0 0 1 .917.81 47.784 47.784 0 0 0 .005 10.337.75.75 0 0 1-.574.812l-3.114.733a9.75 9.75 0 0 1-6.594-.77l-.108-.054a8.25 8.25 0 0 0-5.69-.625l-2.202.55V21a.75.75 0 0 1-1.5 0V3A.75.75 0 0 1 3 2.25Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Races and products') }}</span>
                        </x-nav-link>
                    </li>  
                @endif 
                
                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner']))                 
                    <li>
                        <x-nav-link href="{{ route('teams.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'teams')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
                                <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Teams') }}</span>
                        </x-nav-link>
                    </li>  
                @endif 
                
                @if(auth()->user()->hasRole(['superadmin', 'organizer', 'collaborator', 'partner', 'captain']))       
                    <li title="{{ __('here you change the data of colleagues that have already been entered into the system') }}">
                        <x-nav-link href="{{ route('runners.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'runners')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">
                                @if(auth()->user()->hasRole(['captain']))         
                                    {{ __('Your colleagues') }}
                                @else
                                    {{ __('Information about runners') }}
                                @endif
                            </span>
                        </x-nav-link>
                    </li>          
                    <li title="{{ __('here you register your team for races, add and change team members, track your payments and download documentation.') }}">
                        <x-nav-link href="{{ route('reservations.list') }}" :active="Str::startsWith(Route::currentRouteName(), 'reservations')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M7.491 5.992a.75.75 0 0 1 .75-.75h12a.75.75 0 1 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM7.49 11.995a.75.75 0 0 1 .75-.75h12a.75.75 0 0 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM7.491 17.994a.75.75 0 0 1 .75-.75h12a.75.75 0 1 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM2.24 3.745a.75.75 0 0 1 .75-.75h1.125a.75.75 0 0 1 .75.75v3h.375a.75.75 0 0 1 0 1.5H2.99a.75.75 0 0 1 0-1.5h.375v-2.25H2.99a.75.75 0 0 1-.75-.75ZM2.79 10.602a.75.75 0 0 1 0-1.06 1.875 1.875 0 1 1 2.652 2.651l-.55.55h.35a.75.75 0 0 1 0 1.5h-2.16a.75.75 0 0 1-.53-1.281l1.83-1.83a.375.375 0 0 0-.53-.53.75.75 0 0 1-1.062 0ZM2.24 15.745a.75.75 0 0 1 .75-.75h1.125a1.875 1.875 0 0 1 1.501 2.999 1.875 1.875 0 0 1-1.501 3H2.99a.75.75 0 0 1 0-1.501h1.125a.375.375 0 0 0 .036-.748H3.74a.75.75 0 0 1-.75-.75v-.002a.75.75 0 0 1 .75-.75h.411a.375.375 0 0 0-.036-.748H2.99a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Reservations') }}</span>
                        </x-nav-link>
                    </li>           
                @endif
                
                @if(auth()->user()->hasRole(['captain']))  
                    <li title="{{ __('here you change the information of your company and add the information of the companies that take over the payment on your behalf') }}">
                        <x-nav-link href="{{ route('company') }}" :active="request()->routeIs('company')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                <path d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Company data') }}</span>
                        </x-nav-link>
                    </li>  
                @endif
                
                @if(auth()->user()->hasRole(['superadmin', 'organizer']))      
                    <li>
                        <x-nav-link href="{{ route('settings.show') }}" :active="Str::startsWith(Route::currentRouteName(), 'settings')" class="flex items-center p-1 text-gray-900 rounded hover:bg-gray-100 group">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ml-0.5 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900">
                                <path fill-rule="evenodd" d="M11.828 2.25c-.916 0-1.699.663-1.85 1.567l-.091.549a.798.798 0 0 1-.517.608 7.45 7.45 0 0 0-.478.198.798.798 0 0 1-.796-.064l-.453-.324a1.875 1.875 0 0 0-2.416.2l-.243.243a1.875 1.875 0 0 0-.2 2.416l.324.453a.798.798 0 0 1 .064.796 7.448 7.448 0 0 0-.198.478.798.798 0 0 1-.608.517l-.55.092a1.875 1.875 0 0 0-1.566 1.849v.344c0 .916.663 1.699 1.567 1.85l.549.091c.281.047.508.25.608.517.06.162.127.321.198.478a.798.798 0 0 1-.064.796l-.324.453a1.875 1.875 0 0 0 .2 2.416l.243.243c.648.648 1.67.733 2.416.2l.453-.324a.798.798 0 0 1 .796-.064c.157.071.316.137.478.198.267.1.47.327.517.608l.092.55c.15.903.932 1.566 1.849 1.566h.344c.916 0 1.699-.663 1.85-1.567l.091-.549a.798.798 0 0 1 .517-.608 7.52 7.52 0 0 0 .478-.198.798.798 0 0 1 .796.064l.453.324a1.875 1.875 0 0 0 2.416-.2l.243-.243c.648-.648.733-1.67.2-2.416l-.324-.453a.798.798 0 0 1-.064-.796c.071-.157.137-.316.198-.478.1-.267.327-.47.608-.517l.55-.091a1.875 1.875 0 0 0 1.566-1.85v-.344c0-.916-.663-1.699-1.567-1.85l-.549-.091a.798.798 0 0 1-.608-.517 7.507 7.507 0 0 0-.198-.478.798.798 0 0 1 .064-.796l.324-.453a1.875 1.875 0 0 0-.2-2.416l-.243-.243a1.875 1.875 0 0 0-2.416-.2l-.453.324a.798.798 0 0 1-.796.064 7.462 7.462 0 0 0-.478-.198.798.798 0 0 1-.517-.608l-.091-.55a1.875 1.875 0 0 0-1.85-1.566h-.344ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap leading-tight uppercase" x-show="isSidebarOpen">{{ __('Settings') }}</span>
                        </x-nav-link>
                    </li>        
                @endif 
                
            @endif
        </ul>
        @if($organizer)
            
            <div :class="isSidebarOpen ? 'block' : 'hidden'" class="absolute bottom-4 left-3 right-3 text-sm">
                <p><small>{{ __('Phone') }}: <b><a href="tel:{{ $organizer->phone }}">{{ $organizer->phone }}</a></b></small></p>
                <p><small>{{ __('Email') }}: <b><a href="mailto:{{ $organizer->email }}">{{ $organizer->email }}</a></b></small></p>
                <p><small>{{ __('Web') }}: <b><a href="https://{{ $organizer->website }}" target="_blank">{{ $organizer->website }}</a></b></small></p>
            </div>
           
        @endif
    </div>
</aside>