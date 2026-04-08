<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\PLTestController;

Route::post('/webhook/pipedream', [WebhookController::class, 'handle']);
Route::get('pl-test', [PLTestController::class, 'PreuzmiPodatkeOPrivrednomSubjektuTest']);