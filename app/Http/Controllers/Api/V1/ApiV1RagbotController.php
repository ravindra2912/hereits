<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiV1RagbotController extends Controller
{
    /**
     * RAG Chat Proxy Endpoint
     */
    public function ragChat(Request $request)
    {
        try {
            $apiUrl = env('RAGBOT_API_URL', 'http://130.210.17.238:8000/api/chat');

            $response = Http::timeout(60)
                ->post($apiUrl, $request->all());

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('ApiV1RagbotController ragChat Error: ' . $e->getMessage());

            return response()->json([
                'detail' => 'Chatbot proxy error: ' . $e->getMessage()
            ], 500);
        }
    }
}
