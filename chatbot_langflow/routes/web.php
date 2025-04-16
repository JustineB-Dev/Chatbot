<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/chatbot/message', function (Request $request) {
    $username = $request->input('username');
    $message = $request->input('message');
    $response = Http::withHeaders([
        'Authorization' => 'Bearer sk-AQopZAHmlmXVLp3Ew6FUzjSYeAij2LibJExT06KW940',
        'Content-Type' => 'application/json'
    ])->post('http://127.0.0.1:7860/api/v1/run/babac7b5-6cbd-47a4-9d00-2fbf4ed2019b', [
        'input_value' => $request->input('message'),
        'output_type' => 'chat',
        'input_type' => 'chat',
        'session_id' => $request->input('username'),
        'context_documents' => [
            // Optional: send a list of retrieved document chunks here
        ]
    ]);

    // Debug: Log the response to confirm data structure
    Log::debug('Langflow Response:', $response->json());

    return response()->json($response->json()); // Return the entire response
});

Route::post('/upload-pdf', function (Request $request) {
    $request->validate([
        'pdfFiles' => 'required|array',
        'pdfFiles.*' => 'file|mimes:pdf|max:10240', // max 10MB each
    ]);
    $files = $request->file('pdfFiles');
    $userId = auth()->id() ?? 'guest';

    // Define the FAISS persist directory
    $faissPath = storage_path("faiss_data/pdfs/{$userId}");
    $textPath = storage_path("faiss_data/pdf_texts");

    foreach ($files as $file) {
        $originalName = $file->getClientOriginalName();
        $timestampedName = time() . '_' . $originalName;
    
        if (!file_exists($faissPath)) {
            mkdir($faissPath, 0777, true);
        }
    
        // Move original PDF to faiss directory
        $file->move($faissPath, $timestampedName);
    
        // Parse PDF content
        $pdfText = (new Parser())->parseFile("{$faissPath}/{$timestampedName}")->getText();
    
        // ✨ Add filename to beginning of the text
        $taggedText = "DOCUMENT NAME: {$originalName}\n\n{$pdfText}";
    
        // Save as .txt with same filename
        $textFile = pathinfo($timestampedName, PATHINFO_FILENAME) . '.txt';
        file_put_contents("{$faissPath}/{$textFile}", $taggedText);
    
        // Optional: delete original PDF to only ingest txt
        unlink("{$faissPath}/{$timestampedName}");

        Http::post('http://localhost:7860/api/ingest', [
            'index_name' => 'langflow_chatbot',
            'directory' => storage_path('faiss_data/pdfs'),
        ]);
    }


    return back()->with('success', 'PDF uploaded and moved to Langflow folder!');
});

