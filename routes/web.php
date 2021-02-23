<?php

use Illuminate\Support\Facades\Route;

use MartinMulder\EventSentry\Laravel\Controllers\EventSentryController;

Route::get('/eventsentry/{$report}', [EventSentryController::class, 'showReport'])->name('eventsentry.report');
Route::get('/eventsentry/', [EventSentryController::class, 'index'])->name('eventsentry.index');

