<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'webhook' => [
        'external_url' => env('WEBHOOK_URL', 'https://eo9x81g9w4s8j42.m.pipedream.net'),
    ],

    'kancelarko' => [
        'external_url' => env('KANCELARKO_URL', 'https://k9.paragraf.rs/'),
        'general_token' => env('KANCELARKO_GENERAL', 'BF5257265213EFAA94E0B9AEF90E32FEC1C8D53F'),
        'access_token' => env('KANCELARKO_ACCESS', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjA2YjIzOWQzMjM0ZTliZmUyOTgzZWFkMWYxMmJjNDE0NWNhMWE0NDFmNzgxMzIzYmQ3YWVkYzQ3ZTEwYWFkOGRmNjI2NzUzMDY4M2IwNGI1In0.eyJhdWQiOiIxIiwianRpIjoiMDZiMjM5ZDMyMzRlOWJmZTI5ODNlYWQxZjEyYmM0MTQ1Y2ExYTQ0MWY3ODEzMjNiZDdhZWRjNDdlMTBhYWQ4ZGY2MjY3NTMwNjgzYjA0YjUiLCJpYXQiOjE3NDE2ODQ0NzAsIm5iZiI6MTc0MTY4NDQ3MCwiZXhwIjoxNzczMjIwNDcwLCJzdWIiOiIxIiwic2NvcGVzIjpbXSwidXNlcm5hbWUiOiJtYXJpbmFAdHJjYW5qZS5ycyIsImxpY2Vuc2VJRCI6MjcxfQ.T55LOxqotyh6ExrSi-EEYSt2_KaGpqn97Gv5Ol2yrDKNo5jwg_vKft0qODsXt5VEHNVsfbDiklcoAkqh2jEYOovFewGZ7MtDfcH_1t-x5njR_YDEJgmJ5pz_Atl55681CT5KYB2_HUxB9sAufz8PvA_KO-dF7IYIQVfi-QMlJNe2bJrpqZ4I8Y4gTqDFfvwsFXFUMLdayjUks31dnMXRLGK62Kiesn64V3uUdn23uE6YvilX3OaEqN4o46kT_AlGnBeVLA-nILxWv6BDs_Fe5A_Bg3cc_wiHNcb23vBOjF3Tmcqsbq86k-gXaCl-I3-ZTDi-QCoLgYjvfRsUZUZ_4kLAKgvxIlLWO-wP8-fTxVTog-YxdBDr_liz5md3MmY1Ooc-IEkQgb2Nz2LPJJxyWlb6NHlZSdR2u34BAPi-KwATvAB8tfwe4tkSCvCbdR_wfyOThO7sslY7onaeVak_Xzb6QnwyMEo2E0MDglEaqpwurUI-W3EquWi3_bLAOO-lRoSvoQd44CMRQGYVFqwJLfHJMAuGecuIHibfq9D-oJqAqoYabcyZjWpX3xFvj12c2g--T_cI5pkohCTdcYD_zCx1VtsZ-bziT0u67iV6jRudTMNiGdlJdSp18l2S-29ASiegr1F83BnoPRIOl8qDIqm1f2G0MXsRgydmDWKPMHU'),
    ],

];
