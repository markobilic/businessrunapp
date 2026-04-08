<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">
        <div class="min-h-screen h-full">
            <div class="bg-dark-green p-2 flex flex-col md:flex-row items-center justify-between">
                <div class="w-full md:w-1/4">
                    <livewire:layout.logo-guest />
                </div>
                <div class="mt-4 md:mt-0 flex flex-row gap-2">
                    <a class="inline-flex items-center p-2 md:px-4 md:py-2 rounded font-semibold text-xs uppercase tracking-widest shadow-sm disabled:opacity-25 transition ease-in-out duration-150 text-white bg-yellow-green hover:bg-green-800" href="{{request()->attributes->get('current_organizer')->support_link}}" target="_blank">
                        {{ __('Support & reservation instructions') }}
                    </a>
                    @guest
                        @if(!request()->routeIs('register'))
                            @if (Route::has('register'))
                                <a class="inline-flex items-center p-2 md:px-4 md:py-2 rounded font-semibold text-xs uppercase tracking-widest shadow-sm disabled:opacity-25 transition ease-in-out duration-150 md:ml-2 text-white bg-light-green hover:bg-green-800" href="{{ route('register') }}" wire:navigate>
                                    {{ __('Register') }}
                                </a>
                            @endif
                        @endif
                        @if(!request()->routeIs('login'))
                        <a class="inline-flex items-center p-2 md:px-4 md:py-2 rounded font-semibold text-xs uppercase tracking-widest shadow-sm disabled:opacity-25 transition ease-in-out duration-150 md:ml-2 text-white bg-light-green hover:bg-green-800" href="{{ route('login') }}" wire:navigate>
                            {{ __('Login') }}
                        </a>
                        @endif
                    @endguest
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center p-2 md:px-4 md:py-2 rounded font-semibold text-xs uppercase tracking-widest shadow-sm disabled:opacity-25 transition ease-in-out duration-150 md:ml-2 text-white bg-light-green hover:bg-green-800">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
            <div class="my-auto h-max flex flex-col sm:justify-center md:py-12 items-center bg-gray-100">
                <div class="w-full md:w-2/3 max-w-8xl h-1/3 bg-white flex:col md:inline-flex p-6 rounded shadow-md justify-between">
                    <livewire:layout.image-start />
                    <div class="p-3 md:ps-12 w-full overflow-hidden">
                        {{ $slot }}
                    </div>
                </div>          
            </div>
        </div>        
    </body>
</html>
