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
        @livewireStyles
    </head>
    <body class="font-sans antialiased h-screen">
        <div class="flex flex-col h-full min-h-screen bg-gray-100">
            <div class="bg-dark-green p-2 inline-flex items-center justify-between">
                <div class="shrink-0 flex items-center">
                    <livewire:layout.logo-runner />
                </div>
                <div>
                    <a class="inline-flex items-center px-4 py-2 rounded font-semibold text-xs uppercase tracking-widest shadow-sm disabled:opacity-25 transition ease-in-out duration-150 ml-2 text-white bg-light-green hover:bg-green-800" href="{{request()->attributes->get('current_organizer')->support_link}}" target="_blank">
                        {{ __('Support & reservation instructions') }}
                    </a>
                </div>
            </div>
            <div>
                <div class="bg-gray-100 flex-1 flex flex-col pt-16 pb-4 px-4 min-h-min h-fit max-h-max">
                    <div class="flex-1 flex flex-col min-h-min h-fit max-h-max">
                        <main class="flex-1 flex flex-col min-h-min h-fit max-h-max bg-white rounded-md shadow-md p-4 w-1/2 m-auto">
                            {{ $slot }}
                        </main>
                    </div>
                    <footer class="py-2 mt-2 m-auto">                        
                    </footer>
                </div>
            </div>
        </div>
        @livewireScripts     
    </body>
</html>
