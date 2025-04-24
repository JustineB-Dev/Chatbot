<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
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
        'Authorization' => 'Bearer sk-ZLDPrRilEm3-q3umR0zUDEsaSMuKHnjAUQWgofqZVUg',
        'Content-Type' => 'application/json'
    ])->post('http://127.0.0.1:7860/api/v1/run/6638ca2b-2409-4366-83db-2199804b3cc1', [
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



Route::post('/upload-pdf', function (Request $request) {
    $request->validate([
        'pdfFiles' => 'required|array',
        'pdfFiles.*' => 'file|mimes:pdf|max:10240', // max 10MB each
    ]);

    $files = $request->file('pdfFiles');
    $userId = auth()->id() ?? 'guest';

    // Define directories
    $faissPath = storage_path("faiss_data/pdfs/{$userId}");
    $faissRoot = storage_path("faiss_data");
    $faissIndexFiles = [
        "{$faissRoot}/langflow_chatbot.faiss",
        "{$faissRoot}/langflow_chatbot.pkl"
    ];

    // ✅ 1. Delete previous FAISS index files
    foreach ($faissIndexFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // ✅ 2. Clean up all previously ingested TXT files
    if (File::exists($faissPath)) {
        File::cleanDirectory($faissPath);
    } else {
        mkdir($faissPath, 0777, true);
    }

    // ✅ 3. Save new PDF(s) as text files
    foreach ($files as $file) {
        $originalName = $file->getClientOriginalName();
        $timestampedName = time() . '_' . $originalName;

        $file->move($faissPath, $timestampedName);

        $pdfText = (new Parser())->parseFile("{$faissPath}/{$timestampedName}")->getText();
        $taggedText = "DOCUMENT NAME: {$originalName}\n\n{$pdfText}";

        $textFile = pathinfo($timestampedName, PATHINFO_FILENAME) . '.txt';
        file_put_contents("{$faissPath}/{$textFile}", $taggedText);

        unlink("{$faissPath}/{$timestampedName}"); // delete original PDF after processing
    }

    // ✅ 4. Ingest new data into Langflow
    Http::post('http://localhost:7860/api/ingest', [
        'index_name' => 'langflow_chatbot',
        'directory' => $faissPath,
    ]);

    return back()->with('success', 'Previous FAISS data replaced. New files uploaded and ingested!');
});

