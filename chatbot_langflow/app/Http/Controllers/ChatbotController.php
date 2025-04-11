<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request)
    {
        $response = Http::withHeaders(headers: [
            'Authorization' => 'Bearer AstraCS:gtdtmrSNusHHbikqbYlZAIZX:629d6191053021033491369d466e7d150a9d2fad0593ee193c58e25fa4866db6',
            'Content-Type' => 'application/json',
        ])->post('https://api.langflow.astra.datastax.com/lf/0a85b5d6-ab56-469a-98f9-c6626a8e5cc2/api/v1/run/7b7766c2-5b23-47be-bec0-ea90a92510bb', [
            'input_value' => $request->input('message'),
            'output_type' => 'chat',
            'input_type' => 'chat',
            'session_id' => $request->input('username'),
        ]);
    
        $result = $response->json();
    
        // Log the full raw response to check structure
        Log::info('Langflow Full Response:', ['response' => $response->body()]);
    
        // Try to extract the output safely
        $aiResponse = null;

        if (isset($result['outputs']) && is_array($result['outputs']) && count($result['outputs']) > 0) {
            // Log outputs directly
            Log::info('Langflow Outputs:', $result['outputs']);

            foreach ($result['outputs'] as $output) {
                if (isset($output['value']) && !empty($output['value'])) {
                    $aiResponse = $output['value'];
                    break;
                }
            }
        }
    
        // Log what we extracted
        Log::info('Langflow Extracted Response:', ['aiResponse' => $aiResponse]);
    
        return response()->json([
            'response' => $aiResponse ?? 'No output from AI.',
        ]);
    }
}
