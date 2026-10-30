<?php

use App\Http\Controllers\PLTestController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/pipedream', [WebhookController::class, 'handle']);
Route::get('pl-test', [PLTestController::class, 'PreuzmiPodatkeOPrivrednomSubjektuTest']);
