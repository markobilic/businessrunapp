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
        <div class="flex flex-col h-full min-h-screen bg-gray-100" x-data="{ isSidebarOpen: $persist(true) }">
            <livewire:layout.navbar />
            <div>
                <livewire:layout.sidebar />
                
                <div :class="isSidebarOpen ? 'sm:ml-72' : 'sm:ml-16'" class="bg-gray-100 flex-1 flex flex-col pt-16 pb-4 px-4 min-h-min h-fit max-h-max">
                    <div class="flex-1 flex flex-col min-h-min h-fit max-h-max">
                        <!-- Page Heading -->
                        @if (isset($header))
                            <header class="rounded bg-white shadow">
                                <div class="p-2">
                                    {{ $header }}
                                </div>
                            </header>
                        @endif

                        <!-- Page Content -->
                        <main class="flex-1 flex flex-col min-h-min h-fit max-h-max">
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
