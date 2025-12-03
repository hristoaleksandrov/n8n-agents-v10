<?php

use App\Http\Controllers\AdScriptController;
use Illuminate\Support\Facades\Route;

Route::post('/ad-scripts', [AdScriptController::class, 'store']);
Route::get('/ad-scripts/{task}', [AdScriptController::class, 'show']);
Route::post('/ad-scripts/{task}/result', [AdScriptController::class, 'result'])
    ->middleware('n8n.signature')
    ->name('ad-scripts.result');
