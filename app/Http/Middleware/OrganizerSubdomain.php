<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organizer;
use Illuminate\Support\Facades\App;

class OrganizerSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost(); 
        $parts = explode('.', $host); 

        if (count($parts) < 3) {
            // That likely means there's no subdomain, or it's something like 'localhost'
            // Decide what to do here: maybe abort(404) or skip subdomain logic
            abort(404, 'No subdomain detected');
        }

        $subdomain = $parts[0];

        // Attempt to find the organizer in the DB
        $organizer = Organizer::where('subdomain', $subdomain)->first();

        if (! $organizer) {
            // Optionally throw 404 or redirect somewhere
            abort(404, 'Organizer Not Found');
        }

        // Store the organizer on the request for later use
        // Option 1: store in request attribute
        $request->attributes->set('current_organizer', $organizer);

        if ($organizer && $organizer->countryData && $organizer->countryData->language) 
        {
            App::setLocale($organizer->countryData->language);
        } 
        else 
        {
            // optional: set a default fallback locale if no organizer or language found
            App::setLocale(config('app.locale')); 
        }

        // Option 2: bind globally (e.g. service container singleton)
        app()->singleton('current_organizer', function () use ($organizer) {
            return $organizer;
        });

        return $next($request);
    }
}
