<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/chatbot/message', function (\Illuminate\Http\Request $request) {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer AstraCS:lKARvMWJWfDpDWHEnRmhoABf:1f774215346d76322da5c74b97775874a2ff7d3a19a988a7cce4ea9d4d2036b4',
        'Content-Type' => 'application/json'
    ])->post('https://api.langflow.astra.datastax.com/lf/0a85b5d6-ab56-469a-98f9-c6626a8e5cc2/api/v1/run/7b7766c2-5b23-47be-bec0-ea90a92510bb', [
        'input_value' => $request->input('message'),
        'output_type' => 'chat',
        'input_type' => 'chat',
        'session_id' => $request->input('username'),
    ]);

    // Debug: Log the response to confirm data structure
    Log::debug('Langflow Response:', $response->json());

    return response()->json($response->json()); // Return the entire response
});
