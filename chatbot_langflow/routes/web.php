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
        'Authorization' => 'Bearer sk-yfmDxEBMywtwwRfZwlN3Fi4uLwcK6tCaRN7-SLFq61M',
        'Content-Type' => 'application/json'
    ])->post('http://127.0.0.1:7860/api/v1/run/8e9ce934-6b67-4085-88c4-316626c3888e?stream=false', [
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
        'pdfFiles.*' => 'file|mimes:pdf|max:10240', // max 10MB
    ]);

    $files = $request->file('pdfFiles');
    $userId = auth()->id() ?? 'guest';

    // Paths
    $faissUserPath = storage_path("faiss_data/pdfs/{$userId}");
    $faissRoot = storage_path("faiss_data");

    // Delete old FAISS files
    $deletedFiles = ['{{ index_name }}.faiss', '{{ index_name }}.pkl'];
    foreach ($deletedFiles as $fileName) {
        $filePath = "{$faissRoot}/{$fileName}";
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Prepare user folder
    if (File::exists($faissUserPath)) {
        File::cleanDirectory($faissUserPath);
    } else {
        mkdir($faissUserPath, 0777, true);
    }

    // Convert PDFs to tagged text
    foreach ($files as $file) {
        $originalName = $file->getClientOriginalName();
        $timestampedName = time() . '_' . $originalName;
        $pdfPath = "{$faissUserPath}/{$timestampedName}";

        $file->move($faissUserPath, $timestampedName);
        $pdfText = (new Parser())->parseFile($pdfPath)->getText();
        $taggedText = "DOCUMENT NAME: {$originalName}\n\n{$pdfText}";

        $textFilename = pathinfo($timestampedName, PATHINFO_FILENAME) . '.txt';
        file_put_contents("{$faissUserPath}/{$textFilename}", $taggedText);

        unlink($pdfPath); // Remove original PDF
    }

    // Path to user text directory
    $realPath = realpath($faissUserPath);
    $safePath = str_replace('\\', '/', $realPath); // Ensure forward slashes

    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('http://127.0.0.1:7860/api/v1/run/335c1045-6531-49cf-9368-3ae17359f2e3?stream=false', [
            'input_value' => $safePath,
            'output_type' => 'text',
            'input_type' => 'text',
            'tweaks' => [
                "Directory-aMlxQ" => [],
                "SplitText-p3Lxr" => [],
                "FAISS-xWfkL" => [],
                "OllamaEmbeddings-pK6q7" => []
            ]
        ]);

        Log::debug('Langflow FAISS Ingestion Response:', $response->json());
    } catch (\Exception $e) {
        Log::error('Langflow ingestion error: ' . $e->getMessage());
        return response()->json(['error' => 'Langflow ingestion failed'], 500);
    }

    return response()->json([
        'success' => true,
        'message' => '✅ PDF(s) processed and FAISS index regenerated.',
        'path_used' => $safePath
    ]);
});