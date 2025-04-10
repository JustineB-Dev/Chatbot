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
            'Authorization' => 'Bearer AstraCS:lZHQnNbvrGjllNNkGsaMvuZI:e19201759cf4caacb73f2d6f02fcb9e983889e6a1d4928f5ac5b9b3e26e23bc8',
            'Content-Type' => 'application/json',
        ])->post('https://api.langflow.astra.datastax.com/lf/7f5567d9-edf6-4f3c-83ea-f291f714e6a6/api/v1/run/b4a9c9ff-cef6-4ca0-9568-13f9d7ea2062', [
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
