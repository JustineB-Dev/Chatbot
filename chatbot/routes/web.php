<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAIChatbotController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/chat', [OpenAIChatbotController::class, 'chat']);
