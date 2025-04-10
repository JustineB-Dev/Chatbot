<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/chatbot/message', function (\Illuminate\Http\Request $request) {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer sk-4cDwAjGUXaxCmg6-f6EGTyXICqST7WuajfH-BNEZDPs',
        'Content-Type' => 'application/json'
    ])->post('http://127.0.0.1:7860/api/v1/run/babac7b5-6cbd-47a4-9d00-2fbf4ed2019b', [
        'input_value' => $request->input('message'),
        'output_type' => 'chat',
        'input_type' => 'chat',
        'session_id' => $request->input('username'),
    ]);

    // Debug: Log the response to confirm data structure
    Log::debug('Langflow Response:', $response->json());

    return response()->json($response->json()); // Return the entire response
});
