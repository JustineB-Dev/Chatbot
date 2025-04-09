<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Client;
use OpenAI;

class OpenAIChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'usrname' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        // Use the correct factory method
        $client = OpenAI::client('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');

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4', // 'gpt-4-mini' is not officially supported yet
                'messages' => [
                    ['role' => 'user', 'content' => $validated['comment']],
                ],
            ]);

            $chatbotResponse = $response->choices[0]->message->content;

            return response()->json([
                'response' => $chatbotResponse,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get response from OpenAI: ' . $e->getMessage(),
            ], 500);
        }
    }
}
