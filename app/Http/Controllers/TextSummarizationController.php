<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TextSummarizationController extends Controller
{
    public function summarize(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:30',
        ]);

        try {
            $response = Http::post('http://127.0.0.1:5001/summarize', [
                'text' => $request->input('text'),
            ]);
            $data = $response->json();

            if (!isset($data['summary'])) {
                return response()->json([
                    'error' => 'الاستجابة من الخادم لم تحتوي على ملخص.',
                    'raw_response' => $data,
                ], 500);
            }

            return response()->json([
                'summary' => $data['summary'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection to summarization server failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
